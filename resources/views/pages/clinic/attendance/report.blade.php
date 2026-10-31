@section('title', $title)
@section('description', $description)
@extends('layout.app')
@section('content')
    @php $fmt = fn ($m) => \App\Models\Attendance::formatMinutes((int) $m); @endphp
    <div class="container-fluid">
        <div class="breadcrumb-main d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
            <h4 class="breadcrumb-title mb-0">Laporan Absensi</h4>
            <a href="{{ route('clinic.attendance-report.export', array_merge(['language' => app()->getLocale()], request()->only(['from', 'to', 'user_id', 'role']))) }}"
                class="btn btn-outline-success btn-sm"><i class="uil uil-file-download me-1"></i>Export Excel (CSV)</a>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        @endif

        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('clinic.attendance-report.index', app()->getLocale()) }}" class="row g-2 align-items-end">
                    <div class="col-md-2">
                        <label class="form-label small">Dari</label>
                        <input type="date" name="from" class="form-control form-control-sm" value="{{ $from->toDateString() }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">Sampai</label>
                        <input type="date" name="to" class="form-control form-control-sm" value="{{ $to->toDateString() }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Bagian</label>
                        <select name="role" class="form-select form-select-sm">
                            <option value="">Semua bagian</option>
                            @foreach($roles as $r)
                                <option value="{{ $r->name }}" @selected($role === $r->name)>{{ $r->label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Karyawan</label>
                        <select name="user_id" class="form-select form-select-sm">
                            <option value="">Semua karyawan</option>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}" @selected($userId == $employee->id)>{{ $employee->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-primary btn-sm w-100"><i class="uil uil-filter me-1"></i>Tampilkan</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">Rekap per Karyawan ({{ $from->format('d/m/Y') }} - {{ $to->format('d/m/Y') }})</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0 align-middle">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-3">Karyawan</th>
                                <th class="text-center">Jadwal</th>
                                <th class="text-center">Hadir</th>
                                <th class="text-center">Terlambat</th>
                                <th class="text-center">Izin</th>
                                <th class="text-center">Sakit</th>
                                <th class="text-center">Cuti</th>
                                <th class="text-center">Alpa</th>
                                <th class="text-center">Tidak Absen Pulang</th>
                                <th>Total Terlambat</th>
                                <th>Total Pulang Cepat</th>
                                <th>Total Jam Kerja</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($summary as $s)
                                <tr>
                                    <td class="ps-3">
                                        <a href="{{ route('clinic.attendance-report.index', [app()->getLocale(), 'from' => $from->toDateString(), 'to' => $to->toDateString(), 'user_id' => $s['user']->id]) }}">{{ $s['user']->name }}</a>
                                        <br><small class="text-muted">{{ $s['user']->roleLabel() }}</small>
                                    </td>
                                    <td class="text-center">{{ $s['scheduled'] }}</td>
                                    <td class="text-center text-success fw-bold">{{ $s['present'] + $s['late'] }}</td>
                                    <td class="text-center text-warning">{{ $s['late'] }}</td>
                                    <td class="text-center">{{ $s['permission'] }}</td>
                                    <td class="text-center">{{ $s['sick'] }}</td>
                                    <td class="text-center">{{ $s['leave'] }}</td>
                                    <td class="text-center text-danger fw-bold">{{ $s['absent'] }}</td>
                                    <td class="text-center">{{ $s['missing_checkout'] }}</td>
                                    <td>{{ $fmt($s['late_minutes']) }}</td>
                                    <td>{{ $fmt($s['early_minutes']) }}</td>
                                    <td>{{ $fmt($s['worked_minutes']) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="12" class="text-center text-muted py-4">Belum ada jadwal atau absensi pada periode ini.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">Detail Harian</div>
            <div class="card-body p-0">
                @include('pages.clinic.attendance._rows', ['rows' => $rows, 'showUser' => true, 'canCorrect' => true])
            </div>
        </div>
        <p class="text-muted small mt-2">Hadir = tepat waktu + terlambat. Alpa = ada jadwal, shift sudah lewat, tanpa absen atau keterangan. Gunakan tombol koreksi untuk mencatat izin, sakit, cuti, atau absen manual.</p>
    </div>
@endsection
