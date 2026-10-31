@section('title', $title)
@section('description', $description)
@extends('layout.app')
@section('content')
    @php $refundable = $payment->refundableAmount(); @endphp
    <div class="container-fluid">
        <div class="breadcrumb-main">
            <h4 class="text-capitalize breadcrumb-title">{{ $title }}</h4>
        </div>

        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        <div class="row">
            <div class="col-lg-5">
                <div class="card mb-4">
                    <div class="card-header"><h6><i class="uil uil-receipt me-2"></i>Pembayaran</h6></div>
                    <div class="card-body">
                        <table class="table table-sm mb-0">
                            <tr><th>No. Invoice</th><td><code>{{ $payment->invoice_number }}</code></td></tr>
                            <tr><th>Pasien</th><td>{{ $payment->patient?->name }}</td></tr>
                            <tr><th>Tanggal</th><td>{{ $payment->created_at->format('d/m/Y H:i') }}</td></tr>
                            <tr><th>Metode</th><td>{{ strtoupper($payment->payment_method) }}</td></tr>
                            <tr><th>Total</th><td>Rp {{ number_format($payment->total, 0, ',', '.') }}</td></tr>
                            <tr><th>Sudah di-refund</th><td>Rp {{ number_format($payment->refunded_amount, 0, ',', '.') }}</td></tr>
                            <tr><th>Sisa bisa di-refund</th><td class="fw-bold text-danger">Rp {{ number_format($refundable, 0, ',', '.') }}</td></tr>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="card mb-4">
                    <div class="card-header"><h6><i class="uil uil-money-insert me-2"></i>Form Refund</h6></div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('clinic.payments.refund', [app()->getLocale(), $payment->id]) }}"
                            onsubmit="return confirm('Proses refund ini? Refund tidak bisa dibatalkan.')">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">Jumlah Refund (Rp) <span class="text-danger">*</span></label>
                                <input type="number" name="amount" id="refundAmount" class="form-control" min="1" step="1"
                                    max="{{ $refundable }}" value="{{ old('amount', (int) $refundable) }}" required>
                                <small class="text-muted">Isi penuh untuk refund total, atau lebih kecil untuk refund sebagian.</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Metode Pengembalian <span class="text-danger">*</span></label>
                                <select name="refund_method" class="form-select" required>
                                    @foreach(['cash' => 'Cash', 'transfer' => 'Transfer', 'debit' => 'Debit', 'credit' => 'Credit Card', 'bpjs' => 'BPJS'] as $value => $label)
                                        <option value="{{ $value }}" @selected(old('refund_method', $payment->payment_method) === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Alasan Refund <span class="text-danger">*</span></label>
                                <textarea name="reason" class="form-control" rows="3" required minlength="5" maxlength="500"
                                    placeholder="Contoh: salah input layanan, pasien batal ambil obat">{{ old('reason') }}</textarea>
                            </div>
                            @if($hasMedicines && ! $alreadyRestocked)
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" name="restock" value="1" id="restock" @checked(old('restock'))>
                                    <label class="form-check-label" for="restock">
                                        Kembalikan semua obat di pembayaran ini ke stok
                                        <small class="text-muted d-block">Hanya untuk refund penuh. Tercatat di Kartu Stok sebagai retur.</small>
                                    </label>
                                </div>
                            @endif
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-danger"><i class="uil uil-money-insert me-1"></i>Proses Refund</button>
                                <a href="{{ route('clinic.payments.show', [app()->getLocale(), $payment->id]) }}" class="btn btn-light">Batal</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
