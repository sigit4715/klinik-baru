<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search'));
        $suppliers = Supplier::withCount('purchases')->withSum('purchases', 'total')
            ->when($search !== '', fn ($q) => $q->where('name', 'like', '%' . addcslashes($search, '%_\\') . '%'))
            ->orderBy('name')->get();
        $supplier = new Supplier();

        $title = 'Supplier';
        $description = 'Data pemasok obat dan alat kesehatan';

        return view('pages.clinic.pharmacy.suppliers', compact('title', 'description', 'suppliers', 'supplier', 'search'));
    }

    public function store(Request $request)
    {
        $supplier = Supplier::create($this->validated($request));

        return redirect()->route('clinic.suppliers.index', app()->getLocale())->with('success', "Supplier {$supplier->name} ditambahkan.");
    }

    public function edit($language, $id)
    {
        $supplier = Supplier::findOrFail($id);
        $suppliers = Supplier::withCount('purchases')->withSum('purchases', 'total')->orderBy('name')->get();
        $search = '';
        $title = 'Edit Supplier';
        $description = $supplier->name;

        return view('pages.clinic.pharmacy.suppliers', compact('title', 'description', 'suppliers', 'supplier', 'search'));
    }

    public function update(Request $request, $language, $id)
    {
        $supplier = Supplier::findOrFail($id);
        $supplier->fill($this->validated($request))->save();

        return redirect()->route('clinic.suppliers.index', app()->getLocale())->with('success', "Supplier {$supplier->name} diperbarui.");
    }

    public function destroy($language, $id)
    {
        $supplier = Supplier::findOrFail($id);
        $supplier->delete(); // soft delete: purchase history keeps the name

        return redirect()->route('clinic.suppliers.index', app()->getLocale())->with('success', "Supplier {$supplier->name} diarsipkan.");
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:30',
            'address' => 'nullable|string|max:1000',
            'notes' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive',
        ]);
    }
}
