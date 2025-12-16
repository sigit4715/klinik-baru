@section('title', $title)
@section('description', $description)
@extends('layout.app')
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span><i class="uil uil-receipt me-2"></i>Detail Pembayaran</span>
                        <a href="{{ route('clinic.payments.invoice', [app()->getLocale(), $payment->id]) }}"
                            class="btn btn-sm btn-outline-secondary" target="_blank"><i
                                class="uil uil-print me-1"></i>Cetak</a>
                    </div>
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <p><strong>No. Invoice:</strong> <code>{{ $payment->invoice_number }}</code></p>
                                <p><strong>Tanggal:</strong> {{ $payment->created_at->format('d M Y H:i') }}</p>
                                <p><strong>Status:</strong>
                                    @if($payment->status == 'paid')<span class="badge bg-success">Lunas</span>
                                    @else<span class="badge bg-warning">{{ $payment->status }}</span>@endif
                                </p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Pasien:</strong> {{ $payment->patient->name }}</p>
                                <p><strong>No. RM:</strong> {{ $payment->patient->no_rm }}</p>
                                <p><strong>Metode:</strong> {{ strtoupper($payment->payment_method) }}</p>
                            </div>
                        </div>

                        <h6>Detail Item</h6>
                        <table class="table">
                            <thead class="bg-light">
                                <tr>
                                    <th>Item</th>
                                    <th>Qty</th>
                                    <th>Harga</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($payment->items as $item)
                                    <tr>
                                        <td>{{ $item['name'] }}</td>
                                        <td>{{ $item['qty'] }}</td>
                                        <td>Rp {{ number_format($item['price'], 0, ',', '.') }}</td>
                                        <td>Rp {{ number_format($item['qty'] * $item['price'], 0, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-end">Subtotal</td>
                                    <td>Rp {{ number_format($payment->subtotal, 0, ',', '.') }}</td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="text-end">Diskon</td>
                                    <td>Rp {{ number_format($payment->discount, 0, ',', '.') }}</td>
                                </tr>
                                <tr class="fw-bold">
                                    <td colspan="3" class="text-end">Total</td>
                                    <td class="text-primary">Rp {{ number_format($payment->total, 0, ',', '.') }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div class="mt-3">
                    <a href="{{ route('clinic.payments.index', app()->getLocale()) }}" class="btn btn-light">Kembali</a>
                </div>
            </div>
        </div>
    </div>
@endsection