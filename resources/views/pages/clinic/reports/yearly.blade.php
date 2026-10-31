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
                        <form action="{{ route('clinic.reports.yearly', app()->getLocale()) }}" method="GET"
                            class="row align-items-end">
                            <div class="col-md-4">
                                <label class="form-label">Pilih Tahun</label>
                                <select name="year" class="form-select">
                                    @foreach($availableYears as $y)
                                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                                    @endforeach
                                </select>
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
                        <h6>Total Pendapatan {{ $year }}</h6>
                        <h3 class="mb-0">Rp {{ number_format($totalIncome, 0, ',', '.') }}</h3>
                        @if($totalRefunds > 0)
                            <small>Bersih, sudah dikurangi refund Rp {{ number_format($totalRefunds, 0, ',', '.') }}</small>
                        @endif
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
                        <h6>Rata-rata/Bulan</h6>
                        <h3 class="mb-0">Rp {{ number_format($totalIncome / 12, 0, ',', '.') }}</h3>
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
                        Ringkasan Bulanan - Tahun {{ $year }}
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Bulan</th>
                                        <th class="text-center">Transaksi</th>
                                        <th class="text-end">Total Pendapatan</th>
                                        <th class="text-center">Grafik</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $maxTotal = max($monthlyData->max('total'), 1); @endphp
                                    @foreach($monthlyData as $month)
                                        <tr>
                                            <td>{{ $month['month_name'] }}</td>
                                            <td class="text-center">{{ $month['transactions'] }}</td>
                                            <td class="text-end">
                                                Rp {{ number_format($month['total'], 0, ',', '.') }}
                                                @if($month['refund'] > 0)
                                                    <small class="text-danger d-block">refund Rp {{ number_format($month['refund'], 0, ',', '.') }}</small>
                                                @endif
                                            </td>
                                            <td style="width: 30%;">
                                                <div class="progress" style="height: 20px;">
                                                    <div class="progress-bar bg-success" role="progressbar"
                                                        style="width: {{ max(0, ($month['total'] / $maxTotal) * 100) }}%">
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="fw-bold bg-light">
                                        <td>TOTAL {{ $year }}</td>
                                        <td class="text-center">{{ $totalTransactions }}</td>
                                        <td class="text-end text-success">Rp {{ number_format($totalIncome, 0, ',', '.') }}
                                        </td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection