<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Validator;
use App\Models\Medicine;
use Illuminate\Validation\Rule;
use App\Models\StockMovement;

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
            'generic_name' => 'nullable|string|max:255',
            'dosage_form' => 'nullable|string|max:50',
            'strength' => 'nullable|string|max:50',
            'drug_class' => ['nullable', Rule::in(array_keys(Medicine::DRUG_CLASSES))],
            'rack_location' => 'nullable|string|max:50',
            'manufacturer' => 'nullable|string|max:255',
            'barcode' => 'nullable|string|max:100',
        ]);

        if ($validators->fails()) {
            return redirect()->route('clinic.medicines.create', app()->getLocale())
                ->withErrors($validators)->withInput();
        }

        DB::transaction(function () use ($request) {
            $medicine = new Medicine();
            $medicine->code = Medicine::generateCode();
            $medicine->name = $request->name;
            $medicine->category = $request->category;
            $medicine->unit = $request->unit;
            $medicine->stock = 0;
            $medicine->min_stock = $request->min_stock ?? 10;
            $medicine->buy_price = $request->buy_price;
            $medicine->sell_price = $request->sell_price;
            $medicine->expired_date = $request->expired_date;
            $medicine->status = $request->status ?? 'active';
            foreach (['generic_name', 'dosage_form', 'strength', 'drug_class', 'rack_location', 'manufacturer', 'barcode'] as $field) {
                $medicine->{$field} = $request->input($field) ?: null;
            }
            $medicine->save();

            // Opening stock goes through the stock card
            if ((int) $request->stock > 0) {
                Medicine::moveStock($medicine->id, (int) $request->stock, 'initial', null, 'Stok awal');
            }
        });

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
            'buy_price' => 'required|numeric|min:0',
            'sell_price' => 'required|numeric|min:0',
            'min_stock' => 'nullable|integer|min:0',
            'expired_date' => 'nullable|date',
            'status' => 'nullable|in:active,inactive',
            'generic_name' => 'nullable|string|max:255',
            'dosage_form' => 'nullable|string|max:50',
            'strength' => 'nullable|string|max:50',
            'drug_class' => ['nullable', Rule::in(array_keys(Medicine::DRUG_CLASSES))],
            'rack_location' => 'nullable|string|max:50',
            'manufacturer' => 'nullable|string|max:255',
            'barcode' => 'nullable|string|max:100',
        ]);

        if ($validators->fails()) {
            return redirect()->route('clinic.medicines.edit', [app()->getLocale(), $id])
                ->withErrors($validators)->withInput();
        }

        // Stock is not edited here; it changes only through the stock card so every change is traced
        $medicine = Medicine::findOrFail($id);
        $medicine->name = $request->name;
        $medicine->category = $request->category;
        $medicine->unit = $request->unit;
        $medicine->min_stock = $request->min_stock ?? 10;
        $medicine->buy_price = $request->buy_price;
        $medicine->sell_price = $request->sell_price;
        $medicine->expired_date = $request->expired_date;
        $medicine->status = $request->status ?? 'active';
        foreach (['generic_name', 'dosage_form', 'strength', 'drug_class', 'rack_location', 'manufacturer', 'barcode'] as $field) {
            $medicine->{$field} = $request->input($field) ?: null;
        }
        $medicine->save();

        return redirect()->route('clinic.medicines.index', app()->getLocale())
            ->with('success', 'Obat berhasil diperbarui!');
    }

    public function destroy($language, $id)
    {
        $medicine = Medicine::findOrFail($id);
        $medicine->delete(); // soft delete: moved to Arsip, old prescriptions keep showing it
        return redirect()->route('clinic.medicines.index', app()->getLocale())
            ->with('success', "Obat {$medicine->name} dipindahkan ke Arsip.");
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

    /**
     * Set the physical stock count (JSON). Recorded as a stock opname adjustment.
     */
    public function updateStock(Request $request, $language, $id)
    {
        $request->validate(['stock' => 'required|integer|min:0|max:1000000']);

        $medicine = Medicine::findOrFail($id);
        $change = (int) $request->stock - (int) $medicine->stock;
        if ($change !== 0) {
            Medicine::moveStock($medicine->id, $change, 'adjustment', null, 'Penyesuaian stok');
        }

        return response()->json(['success' => true, 'stock' => $medicine->fresh()->stock]);
    }

    /**
     * Kartu stok: all stock movements of one medicine with the running balance.
     */
    public function stockCard(Request $request, $language, $id)
    {
        $request->validate([
            'from' => 'nullable|date',
            'to' => 'nullable|date|after_or_equal:from',
            'type' => 'nullable|in:' . implode(',', array_keys(StockMovement::TYPES)),
        ]);

        $medicine = Medicine::withTrashed()->findOrFail($id);
        $movements = $medicine->stockMovements()
            ->with(['user', 'reference'])
            ->when($request->from, fn ($q, $from) => $q->where('created_at', '>=', Carbon::parse($from)->startOfDay()))
            ->when($request->to, fn ($q, $to) => $q->where('created_at', '<=', Carbon::parse($to)->endOfDay()))
            ->when($request->type, fn ($q, $type) => $q->where('type', $type))
            ->orderByDesc('id')
            ->paginate(50)
            ->withQueryString();

        $title = 'Kartu Stok';
        $description = 'Riwayat keluar-masuk stok ' . $medicine->name;
        $types = StockMovement::TYPES;
        $manualTypes = array_intersect_key(StockMovement::TYPES, array_flip(StockMovement::MANUAL_TYPES));

        return view('pages.clinic.medicines.stock-card', compact('title', 'description', 'medicine', 'movements', 'types', 'manualTypes'));
    }

    /**
     * Manual stock entry from the stock card page: stock in, return, opname or disposal.
     */
    public function storeMovement(Request $request, $language, $id)
    {
        $validated = $request->validate([
            'type' => 'required|in:' . implode(',', StockMovement::MANUAL_TYPES),
            // For "adjustment" this is the counted physical stock; otherwise the quantity
            'quantity' => 'required|integer|min:0|max:1000000',
            'notes' => 'required|string|max:250',
            'expired_date' => 'nullable|date',
            'buy_price' => 'nullable|numeric|min:0',
        ], [
            'notes.required' => 'Keterangan wajib diisi (mis. no. faktur, alasan penyesuaian).',
        ]);

        $medicine = Medicine::findOrFail($id);

        $change = match ($validated['type']) {
            'in', 'return' => (int) $validated['quantity'],
            'disposal' => -(int) $validated['quantity'],
            'adjustment' => (int) $validated['quantity'] - (int) $medicine->stock,
        };

        if ($change === 0) {
            return back()->withInput()->withErrors(['quantity' => $validated['type'] === 'adjustment'
                ? 'Stok fisik sama dengan stok sistem, tidak ada yang perlu disesuaikan.'
                : 'Jumlah harus lebih dari 0.']);
        }

        try {
            DB::transaction(function () use ($medicine, $change, $validated) {
                Medicine::moveStock($medicine->id, $change, $validated['type'], null, $validated['notes']);

                // A new batch can update expiry date and purchase price
                if ($validated['type'] === 'in') {
                    $fresh = Medicine::findOrFail($medicine->id);
                    if (! empty($validated['expired_date'])) {
                        $fresh->expired_date = $validated['expired_date'];
                    }
                    if (isset($validated['buy_price']) && $validated['buy_price'] !== null) {
                        $fresh->buy_price = $validated['buy_price'];
                    }
                    $fresh->save();
                }
            });
        } catch (ValidationException $e) {
            return back()->withInput()->withErrors($e->errors());
        }

        return redirect()->route('clinic.medicines.stock-card', [app()->getLocale(), $medicine->id])
            ->with('success', 'Kartu stok diperbarui. Stok sekarang: ' . Medicine::find($medicine->id)->stock . ' ' . $medicine->unit . '.');
    }
}
