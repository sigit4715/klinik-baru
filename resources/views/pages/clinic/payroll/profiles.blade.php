@section('title', $title)
@section('description', $description)
@extends('layout.app')
@section('content')
    @php $rp = fn ($v) => \App\Models\Payslip::rupiah($v); @endphp
    <div class="container-fluid">
        <div class="breadcrumb-main d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
            <h4 class="breadcrumb-title mb-0">Pengaturan Gaji</h4>
            <a href="{{ route('clinic.service-fees.index', app()->getLocale()) }}" class="btn btn-outline-primary btn-sm"><i class="uil uil-medkit me-1"></i>Tarif Fee Tindakan</a>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        @endif

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="bg-light">
                            <tr>
                                <th>Karyawan</th>
                                <th>Gaji Pokok</th>
                                <th>Uang Duduk / Shift</th>
                                <th>Fee Tindakan</th>
                                <th>Tunjangan Tetap</th>
                                <th>Potongan</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($employees as $employee)
                                @php $p = $employee->salaryProfile; @endphp
                                <tr>
                                    <td>{{ $employee->name }}<br><small class="text-muted">{{ $employee->roleLabel() }}@if(! $employee->is_active) · nonaktif @endif</small></td>
                                    @if($p)
                                        <td>{{ $rp($p->base_salary) }}</td>
                                        <td>{{ $rp($p->sitting_fee) }}</td>
                                        <td>
                                            @if($p->earns_service_fee)
                                                @if($employee->doctor)
                                                    <span class="badge bg-success">Ya</span> <small class="text-muted">{{ $employee->doctor->name }}</small>
                                                @else
                                                    <span class="badge bg-warning" title="Hubungkan akun ke data dokter di Manajemen User">Belum terhubung ke dokter</span>
                                                @endif
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>{{ $rp($p->fixed_allowance) }}@if($p->allowance_note)<br><small class="text-muted">{{ $p->allowance_note }}</small>@endif</td>
                                        <td class="small">
                                            {{ $p->deduct_absence ? 'Alpa dipotong' : 'Alpa tidak dipotong' }}
                                            @if((float) $p->late_fee_per_incident > 0)<br>Telat {{ $rp($p->late_fee_per_incident) }}/kejadian @endif
                                            @if((float) $p->late_fee_per_minute > 0)<br>Telat {{ $rp($p->late_fee_per_minute) }}/menit @endif
                                        </td>
                                    @else
                                        <td colspan="5" class="text-muted">Belum diatur (tidak ikut penggajian)</td>
                                    @endif
                                    <td><a href="{{ route('clinic.salary-profiles.edit', [app()->getLocale(), $employee->id]) }}" class="btn btn-sm btn-outline-primary"><i class="uil uil-edit"></i> Atur</a></td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="text-center text-muted py-4">Belum ada karyawan.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <p class="text-muted small mb-0">Hanya karyawan yang sudah diatur gajinya yang ikut dihitung saat penggajian bulanan.</p>
            </div>
        </div>
    </div>
@endsection
