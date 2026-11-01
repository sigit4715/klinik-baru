@section('title', $title)
@section('description', $description)
@extends('layout.app')
@section('content')
    @php
        $defaultLines = $purchase
            ? $purchase->items->map(fn ($i) => ['medicine_id' => $i->medicine_id, 'qty' => '', 'price' => (float) $i->buy_price])->all()
            : [['medicine_id' => '', 'qty' => '', 'price' => '']];
        $lines = old('items', $defaultLines);
        $options = $purchase ? $purchase->items->pluck('medicine')->filter()->unique('id') : $medicines;
    @endphp
    <div class="container-fluid">
        <div class="breadcrumb-main"><h4 class="breadcrumb-title">Retur ke Supplier @if($purchase)<small class="text-muted">dari {{ $purchase->purchase_number }}</small>@endif</h4></div>
        @if($errors->any())
            <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif
        <form method="POST" action="{{ route('clinic.purchase-returns.store', app()->getLocale()) }}">
            @csrf
            @if($purchase)<input type="hidden" name="purchase_id" value="{{ $purchase->id }}">@endif
            <div class="card mb-3">
                <div class="card-body row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Supplier <span class="text-danger">*</span></label>
                        @if($purchase)
                            <input type="hidden" name="supplier_id" value="{{ $purchase->supplier_id }}">
                            <input type="text" class="form-control" value="{{ $purchase->supplier?->name }}" readonly>
                        @else
                            <select name="supplier_id" class="form-select" required>
                                <option value="">Pilih supplier...</option>
                                @foreach($suppliers as $s)<option value="{{ $s->id }}" @selected(old('supplier_id') == $s->id)>{{ $s->name }}</option>@endforeach
                            </select>
                        @endif
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="form-label">Tanggal <span class="text-danger">*</span></label>
                        <input type="date" name="return_date" class="form-control" required value="{{ old('return_date', today()->toDateString()) }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Alasan <span class="text-danger">*</span></label>
                        <input type="text" name="reason" class="form-control" maxlength="500" required placeholder="Rusak / kedaluwarsa / salah kirim" value="{{ old('reason') }}">
                    </div>
                </div>
            </div>
            <div class="card mb-3">
                <div class="card-header">Barang Diretur</div>
                <div class="card-body">
                    <table class="table align-middle" id="lines">
                        <thead class="bg-light"><tr><th style="width:45%">Obat</th><th>Qty</th><th>Harga</th><th></th></tr></thead>
                        <tbody>
                            @foreach($lines as $i => $line)
                                <tr class="line">
                                    <td><select name="items[{{ $i }}][medicine_id]" class="form-select form-select-sm">
                                        <option value="">Pilih obat...</option>
                                        @foreach($options as $m)<option value="{{ $m->id }}" data-price="{{ (float) $m->buy_price }}" @selected(($line['medicine_id'] ?? '') == $m->id)>{{ $m->name }} (stok {{ $m->stock }} {{ $m->unit }})</option>@endforeach
                                    </select></td>
                                    <td><input type="number" name="items[{{ $i }}][qty]" class="form-control form-control-sm" min="1" value="{{ $line['qty'] ?? '' }}"></td>
                                    <td><input type="number" name="items[{{ $i }}][price]" class="form-control form-control-sm price" min="0" step="1" value="{{ $line['price'] ?? '' }}"></td>
                                    <td><button type="button" class="btn btn-sm btn-outline-danger remove">&times;</button></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="addLine"><i class="uil uil-plus"></i> Tambah Baris</button>
                    <p class="small text-muted mt-2 mb-0">Baris dengan qty kosong dihapus sebelum disimpan. Stok berkurang dan tercatat di Kartu Stok sebagai "Retur ke supplier".</p>
                </div>
            </div>
            <button class="btn btn-danger" onclick="return confirm('Simpan retur? Stok akan langsung berkurang.')"><i class="uil uil-check me-1"></i>Simpan Retur</button>
            <a href="{{ route('clinic.purchase-returns.index', app()->getLocale()) }}" class="btn btn-light">Batal</a>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            const tbody = document.querySelector('#lines tbody');
            const template = tbody.querySelector('.line').cloneNode(true);
            let index = {{ count($lines) }};
            document.getElementById('addLine').addEventListener('click', () => {
                const row = template.cloneNode(true);
                row.querySelectorAll('input, select').forEach(el => {
                    el.name = el.name.replace(/items\[\d+\]/, `items[${index}]`);
                    if (el.tagName === 'SELECT') el.selectedIndex = 0; else el.value = '';
                });
                index++;
                tbody.appendChild(row);
            });
            tbody.addEventListener('change', e => {
                if (e.target.tagName === 'SELECT') {
                    const price = e.target.closest('tr').querySelector('.price');
                    if (!price.value) price.value = e.target.selectedOptions[0]?.dataset.price || '';
                }
            });
            tbody.addEventListener('click', e => {
                if (e.target.classList.contains('remove') && tbody.querySelectorAll('.line').length > 1) e.target.closest('tr').remove();
            });
            // Drop rows without a quantity so a receipt with many items can be partly returned
            document.querySelector('form[action*="purchase-returns"]').addEventListener('submit', () => {
                tbody.querySelectorAll('.line').forEach(row => {
                    const qty = row.querySelector('input[name$="[qty]"]');
                    if (!qty.value && tbody.querySelectorAll('.line').length > 1) row.remove();
                });
            });
        })();
    </script>
@endpush
