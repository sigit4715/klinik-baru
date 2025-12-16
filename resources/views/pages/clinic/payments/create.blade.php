@section('title', $title)
@section('description', $description)
@extends('layout.app')
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header"><i class="uil uil-bill me-2"></i>Form Pembayaran</div>
                    <div class="card-body">
                        @if($errors->any())
                        <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
                        @endif
                        <form action="{{ route('clinic.payments.store', app()->getLocale()) }}" method="POST" id="paymentForm">
                            @csrf
                            <input type="hidden" name="appointment_id" value="{{ $appointment->id ?? '' }}">
                            <input type="hidden" name="patient_id" value="{{ $patient->id ?? '' }}">
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">No. Invoice</label>
                                    <input type="text" class="form-control" value="{{ $invoice_number }}" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Pasien</label>
                                    <input type="text" class="form-control" value="{{ $patient->name ?? '' }} ({{ $patient->no_rm ?? '' }})" readonly>
                                </div>
                            </div>

                            <h6 class="mt-4">Item Pembayaran</h6>
                            <table class="table table-sm" id="itemsTable">
                                <thead class="bg-light"><tr><th>Item</th><th>Qty</th><th>Harga</th><th>Subtotal</th></tr></thead>
                                <tbody>
                                    @php $subtotal = 0; @endphp
                                    @foreach($items as $i => $item)
                                    @php $subtotal += $item['subtotal']; @endphp
                                    <tr>
                                        <td>{{ $item['name'] }}<input type="hidden" name="items[{{ $i }}][name]" value="{{ $item['name'] }}"><input type="hidden" name="items[{{ $i }}][type]" value="{{ $item['type'] }}"><input type="hidden" name="items[{{ $i }}][id]" value="{{ $item['id'] }}"><input type="hidden" name="items[{{ $i }}][price]" value="{{ $item['price'] }}"></td>
                                        <td><input type="number" name="items[{{ $i }}][qty]" class="form-control form-control-sm item-qty" value="{{ $item['qty'] }}" min="1" style="width:70px" data-price="{{ $item['price'] }}"></td>
                                        <td>Rp {{ number_format($item['price'], 0, ',', '.') }}</td>
                                        <td class="item-subtotal">Rp {{ number_format($item['subtotal'], 0, ',', '.') }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>

                            <hr>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Metode Pembayaran</label>
                                        <select name="payment_method" class="form-select" required>
                                            <option value="cash">Cash</option>
                                            <option value="debit">Debit</option>
                                            <option value="credit">Credit Card</option>
                                            <option value="transfer">Transfer</option>
                                            <option value="bpjs">BPJS</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Diskon (Rp)</label>
                                        <input type="number" name="discount" id="discount" class="form-control" value="0" min="0">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4"><label>Subtotal</label><input type="text" class="form-control" id="subtotalDisplay" value="Rp {{ number_format($subtotal, 0, ',', '.') }}" readonly><input type="hidden" name="subtotal" id="subtotal" value="{{ $subtotal }}"></div>
                                <div class="col-md-4"><label>Diskon</label><input type="text" class="form-control" id="discountDisplay" value="Rp 0" readonly></div>
                                <div class="col-md-4"><label><strong>Total</strong></label><input type="text" class="form-control fw-bold text-primary" id="totalDisplay" value="Rp {{ number_format($subtotal, 0, ',', '.') }}" readonly><input type="hidden" name="total" id="total" value="{{ $subtotal }}"></div>
                            </div>

                            <div class="mt-4">
                                <button type="submit" class="btn btn-success"><i class="uil uil-check me-1"></i>Simpan & Bayar</button>
                                <a href="{{ route('clinic.appointments.index', app()->getLocale()) }}" class="btn btn-light">Batal</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
    document.getElementById('discount').addEventListener('input', calculateTotal);
    document.querySelectorAll('.item-qty').forEach(el => el.addEventListener('input', calculateTotal));
    function calculateTotal() {
        let subtotal = 0;
        document.querySelectorAll('.item-qty').forEach(el => {
            subtotal += el.value * el.dataset.price;
        });
        const discount = parseInt(document.getElementById('discount').value) || 0;
        const total = subtotal - discount;
        document.getElementById('subtotal').value = subtotal;
        document.getElementById('total').value = total;
        document.getElementById('subtotalDisplay').value = 'Rp ' + subtotal.toLocaleString('id');
        document.getElementById('discountDisplay').value = 'Rp ' + discount.toLocaleString('id');
        document.getElementById('totalDisplay').value = 'Rp ' + total.toLocaleString('id');
    }
    </script>
@endsection
