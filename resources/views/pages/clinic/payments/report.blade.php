@section('title', $title)
@section('description', $description)
@extends('layout.app')
@section('content')
    <div class="container-fluid">
        <div class="breadcrumb-main">
            <h4 class="breadcrumb-title">{{ $title }}</h4>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <form action="{{ route('clinic.payments.report', app()->getLocale()) }}" method="GET" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Dari Tanggal</label>
                        <input type="date" name="start_date" class="form-control"
                            value="{{ $startDate instanceof \Carbon\Carbon ? $startDate->format('Y-m-d') : $startDate }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Sampai Tanggal</label>
                        <input type="date" name="end_date" class="form-control"
                            value="{{ $endDate instanceof \Carbon\Carbon ? $endDate->format('Y-m-d') : $endDate }}">
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary"><i class="uil uil-filter me-1"></i>Filter</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card bg-success text-white">
                    <div class="card-body text-center">
                        <h3>Rp {{ number_format($totalRevenue, 0, ',', '.') }}</h3>
                        <p class="mb-0">Total Pendapatan{{ $totalRefunds > 0 ? ' Bersih' : '' }}</p>
                        @if($totalRefunds > 0)
                            <small>Kotor Rp {{ number_format($grossRevenue, 0, ',', '.') }} &minus; refund Rp {{ number_format($totalRefunds, 0, ',', '.') }}</small>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-primary text-white">
                    <div class="card-body text-center">
                        <h3>{{ $payments->count() }}</h3>
                        <p class="mb-0">Jumlah Transaksi</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-info text-white">
                    <div class="card-body text-center">
                        <h3>Rp
                            {{ $payments->count() > 0 ? number_format($totalRevenue / $payments->count(), 0, ',', '.') : 0 }}
                        </h3>
                        <p class="mb-0">Rata-rata Transaksi</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="bg-light">
                            <tr>
                                <th>No. Invoice</th>
                                <th>Tanggal</th>
                                <th>Pasien</th>
                                <th>Metode</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($payments as $payment)
                                <tr>
                                    <td><code>{{ $payment->invoice_number }}</code></td>
                                    <td>{{ $payment->created_at->format('d/m/Y') }}</td>
                                    <td>{{ $payment->patient->name }}</td>
                                    <td>{{ strtoupper($payment->payment_method) }}</td>
                                    <td>
                                        Rp {{ number_format($payment->total, 0, ',', '.') }}
                                        @if((float) $payment->refunded_amount > 0)
                                            <small class="text-danger d-block">{{ $payment->statusLabel() }}</small>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4">Tidak ada data</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        @include('pages.clinic.reports._refunds', ['refunds' => $refunds])
    </div>
@endsection