<?php

namespace App\Http\Controllers;

use App\Models\Medicine;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Penerimaan barang (purchase receipts) and retur ke supplier.
 * Saved documents are not edited: stock already moved, so a mistake is corrected with a return.
 */
class PurchaseController extends Controller
{
    public function index(Request $request)
    {
        [$from, $to] = $this->range($request);
        $supplierId = $request->integer('supplier_id') ?: null;

        $purchases = Purchase::with(['supplier', 'creator'])->withCount('items')
            ->whereDate('purchase_date', '>=', $from->toDateString())->whereDate('purchase_date', '<=', $to->toDateString())
            ->when($supplierId, fn ($q) => $q->where('supplier_id', $supplierId))
            ->orderByDesc('purchase_date')->orderByDesc('id')->paginate(25)->withQueryString();
        $suppliers = Supplier::withTrashed()->orderBy('name')->get();

        $title = 'Penerimaan Barang';
        $description = 'Faktur pembelian dari supplier';

        return view('pages.clinic.pharmacy.purchases', compact('title', 'description', 'purchases', 'suppliers', 'from', 'to', 'supplierId'));
    }

    public function create()
    {
        $suppliers = Supplier::active()->orderBy('name')->get();
        $medicines = Medicine::orderBy('name')->get();
        $title = 'Terima Barang';
        $description = 'Input faktur pembelian';

        return view('pages.clinic.pharmacy.purchase-form', compact('title', 'description', 'suppliers', 'medicines'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'supplier_id' => ['required', 'integer', Rule::exists('suppliers', 'id')->whereNull('deleted_at')],
            'invoice_number' => 'nullable|string|max:100',
            'purchase_date' => 'required|date|before_or_equal:today',
            'notes' => 'nullable|string|max:500',
            'items' => 'required|array|min:1|max:200',
            'items.*.medicine_id' => ['required', 'integer', Rule::exists('medicines', 'id')->whereNull('deleted_at')],
            'items.*.qty' => 'required|integer|min:1|max:1000000',
            'items.*.buy_price' => 'required|numeric|min:0|max:1000000000',
            'items.*.expired_date' => 'nullable|date',
            'items.*.batch_number' => 'nullable|string|max:50',
        ], [
            'items.required' => 'Tambahkan minimal satu obat.',
            'items.*.medicine_id.required' => 'Pilih obat di setiap baris.',
            'items.*.qty.required' => 'Isi jumlah di setiap baris.',
            'items.*.buy_price.required' => 'Isi harga beli di setiap baris.',
        ]);

        $purchase = DB::transaction(function () use ($data, $request) {
            $purchase = new Purchase($data);
            $purchase->created_by = $request->user()->id;
            $purchase->total = 0;
            $purchase->saveWithNumber();

            $total = 0;
            foreach ($data['items'] as $line) {
                $item = new PurchaseItem([
                    'medicine_id' => $line['medicine_id'],
                    'qty' => $line['qty'],
                    'buy_price' => $line['buy_price'],
                    'expired_date' => $line['expired_date'] ?? null,
                    'batch_number' => $line['batch_number'] ?? null,
                    'subtotal' => round($line['qty'] * $line['buy_price'], 2),
                ]);
                $item->purchase_id = $purchase->id;
                $item->save();
                $total += $item->subtotal;

                $medicine = Medicine::lockForUpdate()->findOrFail($line['medicine_id']);
                $hadStock = (int) $medicine->stock > 0;
                Medicine::moveStock($medicine->id, (int) $line['qty'], 'in', $purchase,
                    "Penerimaan {$purchase->purchase_number}" . ($purchase->invoice_number ? " / faktur {$purchase->invoice_number}" : ''));

                $medicine->refresh();
                $medicine->buy_price = $line['buy_price'];
                if (! empty($line['expired_date'])) {
                    // One date per medicine: keep the earliest still-valid date while old stock remains (FEFO warning)
                    $new = Carbon::parse($line['expired_date']);
                    $old = $medicine->expired_date;
                    $keepOld = $hadStock && $old && $old->gte(today()) && $old->lt($new);
                    $medicine->expired_date = $keepOld ? $old : $new;
                }
                $medicine->save();
            }

            $purchase->total = round($total, 2);
            $purchase->save();

            return $purchase;
        });

        return redirect()->route('clinic.purchases.show', [app()->getLocale(), $purchase->id])
            ->with('success', "Penerimaan {$purchase->purchase_number} disimpan. Stok sudah bertambah.");
    }

    public function show($language, $id)
    {
        $purchase = Purchase::with(['supplier', 'creator', 'items.medicine', 'returns'])->findOrFail($id);
        $title = 'Penerimaan ' . $purchase->purchase_number;
        $description = $purchase->supplier?->name;

        return view('pages.clinic.pharmacy.purchase-show', compact('title', 'description', 'purchase'));
    }

    // ---- Retur ke supplier ----

    public function returns(Request $request)
    {
        [$from, $to] = $this->range($request);
        $returns = PurchaseReturn::with(['supplier', 'purchase', 'creator'])->withCount('items')
            ->whereDate('return_date', '>=', $from->toDateString())->whereDate('return_date', '<=', $to->toDateString())
            ->orderByDesc('return_date')->orderByDesc('id')->paginate(25)->withQueryString();

        $title = 'Retur ke Supplier';
        $description = 'Barang yang dikembalikan ke supplier';

        return view('pages.clinic.pharmacy.returns', compact('title', 'description', 'returns', 'from', 'to'));
    }

    public function createReturn(Request $request)
    {
        $purchase = $request->integer('purchase_id') ? Purchase::with(['items.medicine', 'supplier'])->findOrFail($request->integer('purchase_id')) : null;
        $suppliers = Supplier::active()->orderBy('name')->get();
        if ($purchase && ! $suppliers->contains('id', $purchase->supplier_id) && $purchase->supplier) {
            $suppliers->push($purchase->supplier);
        }
        $medicines = Medicine::where('stock', '>', 0)->orderBy('name')->get();
        $title = 'Retur ke Supplier';
        $description = $purchase ? 'Dari penerimaan ' . $purchase->purchase_number : 'Retur baru';

        return view('pages.clinic.pharmacy.return-form', compact('title', 'description', 'purchase', 'suppliers', 'medicines'));
    }

    public function storeReturn(Request $request)
    {
        $data = $request->validate([
            'supplier_id' => ['required', 'integer', Rule::exists('suppliers', 'id')],
            'purchase_id' => ['nullable', 'integer', Rule::exists('purchases', 'id')->where('supplier_id', $request->input('supplier_id'))],
            'return_date' => 'required|date|before_or_equal:today',
            'reason' => 'required|string|max:500',
            'items' => 'required|array|min:1|max:200',
            'items.*.medicine_id' => ['required', 'integer', Rule::exists('medicines', 'id')],
            'items.*.qty' => 'required|integer|min:1|max:1000000',
            'items.*.price' => 'required|numeric|min:0|max:1000000000',
        ], [
            'reason.required' => 'Isi alasan retur (rusak, kedaluwarsa, salah kirim, dll.).',
            'items.required' => 'Tambahkan minimal satu obat.',
            'purchase_id.exists' => 'Faktur tidak cocok dengan supplier.',
        ]);

        // A return tied to a receipt cannot exceed what that receipt delivered (minus earlier returns)
        if (! empty($data['purchase_id'])) {
            $delivered = PurchaseItem::where('purchase_id', $data['purchase_id'])->selectRaw('medicine_id, SUM(qty) as qty')->groupBy('medicine_id')->pluck('qty', 'medicine_id');
            $returned = PurchaseReturnItem::whereHas('purchaseReturn', fn ($q) => $q->where('purchase_id', $data['purchase_id']))
                ->selectRaw('medicine_id, SUM(qty) as qty')->groupBy('medicine_id')->pluck('qty', 'medicine_id');
            $asked = collect($data['items'])->groupBy('medicine_id')->map(fn ($rows) => $rows->sum('qty'));
            foreach ($asked as $medicineId => $qty) {
                $left = (int) ($delivered[$medicineId] ?? 0) - (int) ($returned[$medicineId] ?? 0);
                if ($qty > $left) {
                    $name = Medicine::withTrashed()->find($medicineId)?->name;
                    throw ValidationException::withMessages(['items' => "Retur {$name} melebihi jumlah di faktur (sisa yang bisa diretur: {$left})."]);
                }
            }
        }

        try {
            $return = DB::transaction(function () use ($data, $request) {
                $return = new PurchaseReturn($data);
                $return->created_by = $request->user()->id;
                $return->total = 0;
                $return->saveWithNumber();

                $total = 0;
                foreach ($data['items'] as $line) {
                    $item = new PurchaseReturnItem([
                        'medicine_id' => $line['medicine_id'],
                        'qty' => $line['qty'],
                        'price' => $line['price'],
                        'subtotal' => round($line['qty'] * $line['price'], 2),
                    ]);
                    $item->purchase_return_id = $return->id;
                    $item->save();
                    $total += $item->subtotal;

                    // Throws when stock would go below zero
                    Medicine::moveStock((int) $line['medicine_id'], -(int) $line['qty'], 'supplier_return', $return,
                        "Retur {$return->return_number}: {$data['reason']}");
                }
                $return->total = round($total, 2);
                $return->save();

                return $return;
            });
        } catch (ValidationException $e) {
            return back()->withInput()->withErrors($e->errors());
        }

        return redirect()->route('clinic.purchase-returns.show', [app()->getLocale(), $return->id])
            ->with('success', "Retur {$return->return_number} disimpan. Stok sudah dikurangi.");
    }

    public function showReturn($language, $id)
    {
        $return = PurchaseReturn::with(['supplier', 'purchase', 'creator', 'items.medicine'])->findOrFail($id);
        $title = 'Retur ' . $return->return_number;
        $description = $return->supplier?->name;

        return view('pages.clinic.pharmacy.return-show', compact('title', 'description', 'return'));
    }

    /** @return array{0: Carbon, 1: Carbon} */
    private function range(Request $request): array
    {
        $parse = fn ($v, $d) => $v && preg_match('/^\d{4}-\d{2}-\d{2}$/', $v) ? Carbon::parse($v) : $d;
        $from = $parse($request->query('from'), today()->startOfMonth());
        $to = $parse($request->query('to'), today());

        return $to->lt($from) ? [$to, $from] : [$from, $to];
    }
}
