<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use App\Models\Medicine;

class MedicineController extends Controller
{
    public function index(Request $request)
    {
        $query = Medicine::query();

        // Search functionality
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        // Advanced filters
        if ($request->has('category') && $request->category != '') {
            $query->where('category', $request->category);
        }

        if ($request->has('stock_status') && $request->stock_status != '') {
            if ($request->stock_status == 'low') {
                $query->whereRaw('stock <= min_stock');
            } elseif ($request->stock_status == 'normal') {
                $query->whereRaw('stock > min_stock');
            }
        }

        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        $medicines = $query->orderBy('id', 'DESC')->paginate(10)->withQueryString();
        $lowStockCount = Medicine::lowStock()->count();
        $expiringCount = Medicine::expiringSoon(30)->count();
        $categories = ['Tablet', 'Kapsul', 'Sirup', 'Injeksi', 'Salep', 'Tetes', 'Suppositoria', 'Cairan', 'Alkes', 'Inhaler', 'Lainnya'];
        $title = "Daftar Obat";
        $description = "Kelola inventaris obat klinik";

        return view('pages.clinic.medicines.index', compact('title', 'description', 'medicines', 'lowStockCount', 'expiringCount', 'categories'));
    }

    public function create()
    {
        $title = "Tambah Obat Baru";
        $description = "Form tambah obat baru";
        $code = Medicine::generateCode();
        $categories = ['Tablet', 'Kapsul', 'Sirup', 'Injeksi', 'Salep', 'Tetes', 'Suppositoria', 'Lainnya'];
        $units = ['Pcs', 'Box', 'Botol', 'Tube', 'Strip', 'Ampul', 'Vial'];
        return view('pages.clinic.medicines.create', compact('title', 'description', 'code', 'categories', 'units'));
    }

    public function store(Request $request)
    {
        $validators = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'category' => 'required|string',
            'unit' => 'required|string',
            'stock' => 'required|integer|min:0',
            'buy_price' => 'required|numeric|min:0',
            'sell_price' => 'required|numeric|min:0',
            'min_stock' => 'nullable|integer|min:0',
            'expired_date' => 'nullable|date',
            'status' => 'nullable|in:active,inactive',
        ]);

        if ($validators->fails()) {
            return redirect()->route('clinic.medicines.create', app()->getLocale())
                ->withErrors($validators)->withInput();
        }

        $medicine = new Medicine();
        $medicine->code = Medicine::generateCode();
        $medicine->name = $request->name;
        $medicine->category = $request->category;
        $medicine->unit = $request->unit;
        $medicine->stock = $request->stock;
        $medicine->min_stock = $request->min_stock ?? 10;
        $medicine->buy_price = $request->buy_price;
        $medicine->sell_price = $request->sell_price;
        $medicine->expired_date = $request->expired_date;
        $medicine->status = $request->status ?? 'active';
        $medicine->save();

        return redirect()->route('clinic.medicines.index', app()->getLocale())
            ->with('success', 'Obat berhasil ditambahkan!');
    }

    public function edit($language, $id)
    {
        $medicine = Medicine::findOrFail($id);
        $title = "Edit Obat";
        $description = "Ubah data obat";
        $categories = ['Tablet', 'Kapsul', 'Sirup', 'Injeksi', 'Salep', 'Tetes', 'Suppositoria', 'Lainnya'];
        $units = ['Pcs', 'Box', 'Botol', 'Tube', 'Strip', 'Ampul', 'Vial'];

        return view('pages.clinic.medicines.edit', compact('title', 'description', 'medicine', 'categories', 'units'));
    }

    public function update(Request $request, $language, $id)
    {
        $validators = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'category' => 'required|string',
            'unit' => 'required|string',
            'stock' => 'required|integer|min:0',
            'buy_price' => 'required|numeric|min:0',
            'sell_price' => 'required|numeric|min:0',
            'min_stock' => 'nullable|integer|min:0',
            'expired_date' => 'nullable|date',
            'status' => 'nullable|in:active,inactive',
        ]);

        if ($validators->fails()) {
            return redirect()->route('clinic.medicines.edit', [app()->getLocale(), $id])
                ->withErrors($validators)->withInput();
        }

        $medicine = Medicine::findOrFail($id);
        $medicine->name = $request->name;
        $medicine->category = $request->category;
        $medicine->unit = $request->unit;
        $medicine->stock = $request->stock;
        $medicine->min_stock = $request->min_stock ?? 10;
        $medicine->buy_price = $request->buy_price;
        $medicine->sell_price = $request->sell_price;
        $medicine->expired_date = $request->expired_date;
        $medicine->status = $request->status ?? 'active';
        $medicine->save();

        return redirect()->route('clinic.medicines.index', app()->getLocale())
            ->with('success', 'Obat berhasil diperbarui!');
    }

    public function destroy($language, $id)
    {
        $medicine = Medicine::findOrFail($id);
        $medicine->delete();
        return redirect()->route('clinic.medicines.index', app()->getLocale())
            ->with('success', 'Obat berhasil dihapus!');
    }

    public function getActive()
    {
        $medicines = Medicine::active()->where('stock', '>', 0)->get();
        return response()->json($medicines);
    }

    public function lowStock()
    {
        $medicines = Medicine::lowStock()->get();
        $title = "Obat Stok Rendah";
        $description = "Daftar obat dengan stok di bawah minimum";
        return view('pages.clinic.medicines.low-stock', compact('title', 'description', 'medicines'));
    }

    public function updateStock(Request $request, $language, $id)
    {
        $request->validate(['stock' => 'required|integer|min:0|max:1000000']);

        $medicine = Medicine::findOrFail($id);
        $medicine->stock = (int) $request->stock;
        $medicine->save();
        return response()->json(['success' => true, 'stock' => $medicine->stock]);
    }
}
