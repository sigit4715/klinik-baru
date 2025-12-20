@section('title', $title)
@section('description', $description)
@extends('layout.app')
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="breadcrumb-main">
                    <h4 class="text-capitalize breadcrumb-title">{{ $title }}</h4>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12 mb-4">
                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('clinic.reports.daily', app()->getLocale()) }}" method="GET" class="row align-items-end">
                            <div class="col-md-4">
                                <label class="form-label">Pilih Tanggal</label>
                                <input type="date" name="date" class="form-control" 
                                    value="{{ $selectedDate->format('Y-m-d') }}">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="uil uil-search me-1"></i>Tampilkan
                                </button>
                            </div>
                            <div class="col-md-6 text-end">
                                <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
                                    <i class="uil uil-print me-1"></i>Cetak Laporan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card bg-success text-white">
                    <div class="card-body text-center">
                        <h6>Total Pendapatan</h6>
                        <h3 class="mb-0">Rp {{ number_format($totalIncome, 0, ',', '.') }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card bg-info text-white">
                    <div class="card-body text-center">
                        <h6>Total Transaksi</h6>
                        <h3 class="mb-0">{{ $totalTransactions }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card bg-warning text-white">
                    <div class="card-body text-center">
                        <h6>Rata-rata/Transaksi</h6>
                        <h3 class="mb-0">Rp {{ $totalTransactions > 0 ? number_format($totalIncome / $totalTransactions, 0, ',', '.') : 0 }}</h3>
                    </div>
                </div>
            </div>
        </div>

        @if($paymentMethods->count() > 0)
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">Metode Pembayaran</div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($paymentMethods as $method => $amount)
                            <div class="col-md-3 mb-2">
                                <div class="border rounded p-3 text-center">
                                    <h6 class="text-muted mb-1">{{ strtoupper($method) }}</h6>
                                    <h5 class="mb-0 text-primary">Rp {{ number_format($amount, 0, ',', '.') }}</h5>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        Daftar Transaksi - {{ $selectedDate->translatedFormat('d F Y') }}
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Waktu</th>
                                        <th>Invoice</th>
                                        <th>Pasien</th>
                                        <th>Metode</th>
                                        <th class="text-end">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($payments as $index => $payment)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $payment->created_at->format('H:i') }}</td>
                                        <td><code>{{ $payment->invoice_number }}</code></td>
                                        <td>{{ $payment->patient->name }}</td>
                                        <td><span class="badge bg-secondary">{{ strtoupper($payment->payment_method) }}</span></td>
                                        <td class="text-end">Rp {{ number_format($payment->total, 0, ',', '.') }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">
                                            Tidak ada transaksi pada tanggal ini
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                                @if($payments->count() > 0)
                                <tfoot>
                                    <tr class="fw-bold bg-light">
                                        <td colspan="5" class="text-end">TOTAL</td>
                                        <td class="text-end text-success">Rp {{ number_format($totalIncome, 0, ',', '.') }}</td>
                                    </tr>
                                </tfoot>
                                @endif
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
