@section('title', $title)
@section('description', $description)
@extends('layout.app')

@push('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
@endpush

@section('content')
    @php $lines = old('items', [['medicine_id' => '', 'qty' => '', 'buy_price' => '', 'expired_date' => '', 'batch_number' => '']]); @endphp
    <div class="container-fluid">
        <div class="breadcrumb-main"><h4 class="breadcrumb-title">Terima Barang</h4></div>
        @if($errors->any())
            <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif
        @if($suppliers->isEmpty())
            <div class="alert alert-warning">Belum ada supplier aktif. <a href="{{ route('clinic.suppliers.index', app()->getLocale()) }}">Tambah supplier</a> dulu.</div>
        @endif
        <form method="POST" action="{{ route('clinic.purchases.store', app()->getLocale()) }}">
            @csrf
            <div class="card mb-3">
                <div class="card-body row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Supplier <span class="text-danger">*</span></label>
                        <select name="supplier_id" class="form-select" required>
                            <option value="">Pilih supplier...</option>
                            @foreach($suppliers as $s)<option value="{{ $s->id }}" @selected(old('supplier_id') == $s->id)>{{ $s->name }}</option>@endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">No. Faktur Supplier</label>
                        <input type="text" name="invoice_number" class="form-control" maxlength="100" value="{{ old('invoice_number') }}">
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="form-label">Tanggal <span class="text-danger">*</span></label>
                        <input type="date" name="purchase_date" class="form-control" required value="{{ old('purchase_date', today()->toDateString()) }}">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Catatan</label>
                        <input type="text" name="notes" class="form-control" maxlength="500" value="{{ old('notes') }}">
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header">Barang Diterima</div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table align-middle" id="lines">
                            <thead class="bg-light"><tr><th style="width:32%">Obat</th><th>Qty</th><th>Harga Beli</th><th>Kedaluwarsa</th><th>No. Batch</th><th class="text-end">Subtotal</th><th></th></tr></thead>
                            <tbody>
                                @foreach($lines as $i => $line)
                                    <tr class="line">
                                        <td><select name="items[{{ $i }}][medicine_id]" class="form-select form-select-sm med-select" required>
                                            <option value="">Pilih obat...</option>
                                            @foreach($medicines as $m)<option value="{{ $m->id }}" data-price="{{ (float) $m->buy_price }}" @selected(($line['medicine_id'] ?? '') == $m->id)>{{ $m->name }} ({{ $m->unit }}, stok {{ $m->stock }})</option>@endforeach
                                        </select></td>
                                        <td><input type="number" name="items[{{ $i }}][qty]" class="form-control form-control-sm qty" min="1" required value="{{ $line['qty'] ?? '' }}"></td>
                                        <td><input type="number" name="items[{{ $i }}][buy_price]" class="form-control form-control-sm price" min="0" step="1" required value="{{ $line['buy_price'] ?? '' }}"></td>
                                        <td><input type="date" name="items[{{ $i }}][expired_date]" class="form-control form-control-sm" value="{{ $line['expired_date'] ?? '' }}"></td>
                                        <td><input type="text" name="items[{{ $i }}][batch_number]" class="form-control form-control-sm" maxlength="50" value="{{ $line['batch_number'] ?? '' }}"></td>
                                        <td class="text-end subtotal">0</td>
                                        <td><button type="button" class="btn btn-sm btn-outline-danger remove">&times;</button></td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot><tr class="fw-bold"><td colspan="5" class="text-end">Total</td><td class="text-end" id="grandTotal">0</td><td></td></tr></tfoot>
                        </table>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="addLine"><i class="uil uil-plus"></i> Tambah Baris</button>
                    <p class="small text-muted mt-2 mb-0">Stok bertambah otomatis dan tercatat di Kartu Stok. Harga beli obat diperbarui. Tanggal kedaluwarsa obat memakai yang paling dekat selama stok lama masih ada.</p>
                </div>
            </div>
            <button class="btn btn-primary" onclick="return confirm('Simpan penerimaan barang? Stok akan langsung bertambah.')"><i class="uil uil-check me-1"></i>Simpan Penerimaan</button>
            <a href="{{ route('clinic.purchases.index', app()->getLocale()) }}" class="btn btn-light">Batal</a>
        </form>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(function () {
            const tbody = document.querySelector('#lines tbody');
            const template = tbody.querySelector('.line').cloneNode(true);
            let index = {{ count($lines) }};
            const fmt = n => n.toLocaleString('id-ID');

            function init(row) {
                $(row).find('.med-select').select2({ theme: 'bootstrap-5', width: '100%', placeholder: 'Cari obat...' })
                    .on('change', function () {
                        const price = row.querySelector('.price');
                        if (!price.value) price.value = this.selectedOptions[0]?.dataset.price || '';
                        recalc();
                    });
            }
            function recalc() {
                let total = 0;
                tbody.querySelectorAll('.line').forEach(row => {
                    const sub = (parseFloat(row.querySelector('.qty').value) || 0) * (parseFloat(row.querySelector('.price').value) || 0);
                    row.querySelector('.subtotal').textContent = fmt(sub);
                    total += sub;
                });
                document.getElementById('grandTotal').textContent = 'Rp ' + fmt(total);
            }
            document.getElementById('addLine').addEventListener('click', () => {
                const row = template.cloneNode(true);
                row.querySelectorAll('input, select').forEach(el => {
                    el.name = el.name.replace(/items\[\d+\]/, `items[${index}]`);
                    if (el.tagName === 'SELECT') el.selectedIndex = 0; else el.value = '';
                });
                index++;
                tbody.appendChild(row);
                init(row);
                recalc();
            });
            tbody.addEventListener('click', e => {
                if (e.target.classList.contains('remove') && tbody.querySelectorAll('.line').length > 1) {
                    e.target.closest('tr').remove();
                    recalc();
                }
            });
            tbody.addEventListener('input', recalc);
            tbody.querySelectorAll('.line').forEach(init);
            recalc();
        });
    </script>
@endpush
