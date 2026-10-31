@section('title', $title)
@section('description', $description)
@extends('layout.app')
@section('content')
    <div class="container-fluid">
        <div class="breadcrumb-main d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
            <h4 class="breadcrumb-title mb-0">Penggajian</h4>
            <div class="d-flex gap-2">
                <a href="{{ route('clinic.salary-profiles.index', app()->getLocale()) }}" class="btn btn-outline-primary btn-sm"><i class="uil uil-setting me-1"></i>Pengaturan Gaji</a>
                <a href="{{ route('clinic.service-fees.index', app()->getLocale()) }}" class="btn btn-outline-primary btn-sm"><i class="uil uil-medkit me-1"></i>Tarif Fee Tindakan</a>
            </div>
        </div>

        @if($errors->any())
            <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif

        <div class="row">
            <div class="col-lg-4 mb-4">
                <div class="card">
                    <div class="card-header">Hitung Gaji</div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('clinic.payroll.store', app()->getLocale()) }}">
                            @csrf
                            <label class="form-label">Bulan</label>
                            <input type="month" name="period" class="form-control mb-3" value="{{ old('period', now()->subMonthNoOverflow()->format('Y-m')) }}" required>
                            <button class="btn btn-primary w-100"><i class="uil uil-calculator me-1"></i>Hitung Gaji</button>
                        </form>
                        <p class="small text-muted mt-3 mb-0">Gaji dihitung dari absensi (Laporan Absensi) dan tindakan dokter yang sudah lunas pada bulan itu. Hasilnya masih draft dan bisa dihitung ulang sampai dikunci.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="bg-light">
                                    <tr><th>Periode</th><th>Status</th><th>Karyawan</th><th>Total Gaji Bersih</th><th>Dihitung</th><th></th></tr>
                                </thead>
                                <tbody>
                                    @forelse($periods as $period)
                                        <tr>
                                            <td><strong>{{ $period->label() }}</strong></td>
                                            <td><span class="badge bg-{{ $period->statusBadge() }}">{{ $period->statusLabel() }}</span></td>
                                            <td>{{ $period->payslips_count }}</td>
                                            <td>{{ \App\Models\Payslip::rupiah($period->payslips_sum_net_total ?? 0) }}</td>
                                            <td class="small">{{ $period->calculated_at?->format('d/m/Y H:i') ?? '-' }}</td>
                                            <td><a href="{{ route('clinic.payroll.show', [app()->getLocale(), $period->id]) }}" class="btn btn-sm btn-outline-primary">Buka</a></td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="6" class="text-center text-muted py-4">Belum ada penggajian.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
