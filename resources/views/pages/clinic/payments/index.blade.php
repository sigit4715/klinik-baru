@section('title', $title)
@section('description', $description)
@extends('layout.app')
@section('content')
    <div class="container-fluid">
        <div class="breadcrumb-main add-contact justify-content-sm-between">
            <div class="d-flex flex-wrap justify-content-center breadcrumb-main__wrapper">
                <h4 class="text-capitalize fw-500 breadcrumb-title">{{ $title }}</h4>
                <div class="action-btn mt-sm-0 mt-15 ms-3">
                    <a href="{{ route('clinic.payments.report', app()->getLocale()) }}" class="btn btn-info"><i
                            class="uil uil-chart me-1"></i>Laporan</a>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button"
                    class="btn-close" data-bs-dismiss="alert"></button></div>
        @endif

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="bg-light">
                            <tr>
                                <th>No. Invoice</th>
                                <th>Tanggal</th>
                                <th>Pasien</th>
                                <th>Total</th>
                                <th>Metode</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($payments as $payment)
                                <tr>
                                    <td><code>{{ $payment->invoice_number }}</code></td>
                                    <td>{{ $payment->created_at->format('d/m/Y H:i') }}</td>
                                    <td>{{ $payment->patient->name }}</td>
                                    <td><strong>Rp {{ number_format($payment->total, 0, ',', '.') }}</strong></td>
                                    <td>{{ strtoupper($payment->payment_method) }}</td>
                                    <td>
                                        @if($payment->status == 'paid')<span class="badge bg-success">Lunas</span>
                                        @elseif($payment->status == 'pending')<span class="badge bg-warning">Pending</span>
                                        @else<span class="badge bg-secondary">{{ $payment->status }}</span>@endif
                                    </td>
                                    <td>
                                        <a href="{{ route('clinic.payments.show', [app()->getLocale(), $payment->id]) }}"
                                            class="btn btn-sm btn-outline-info"><i class="uil uil-eye"></i></a>
                                        <a href="{{ route('clinic.payments.invoice', [app()->getLocale(), $payment->id]) }}"
                                            class="btn btn-sm btn-outline-secondary" target="_blank"><i
                                                class="uil uil-print"></i></a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4">Belum ada pembayaran</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $payments->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
@endsection