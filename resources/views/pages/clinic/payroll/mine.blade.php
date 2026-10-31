@section('title', $title)
@section('description', $description)
@extends('layout.app')
@section('content')
    <div class="container-fluid">
        <div class="breadcrumb-main">
            <h4 class="breadcrumb-title">Slip Gaji Saya</h4>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="bg-light">
                            <tr><th>Periode</th><th>Status</th><th>Shift Hadir</th><th>Gaji Bersih</th><th></th></tr>
                        </thead>
                        <tbody>
                            @forelse($payslips as $p)
                                <tr>
                                    <td>{{ $p->period->label() }}</td>
                                    <td><span class="badge bg-{{ $p->period->statusBadge() }}">{{ $p->period->statusLabel() }}</span></td>
                                    <td>{{ $p->attended_shifts }} / {{ $p->scheduled_shifts }}</td>
                                    <td><strong>{{ \App\Models\Payslip::rupiah($p->net_total) }}</strong></td>
                                    <td><a href="{{ route('clinic.payslips.print', [app()->getLocale(), $p->id]) }}" target="_blank" class="btn btn-sm btn-outline-primary"><i class="uil uil-eye me-1"></i>Lihat / Cetak</a></td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted py-4">Belum ada slip gaji.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
