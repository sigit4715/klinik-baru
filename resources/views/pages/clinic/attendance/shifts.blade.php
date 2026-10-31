@section('title', $title)
@section('description', $description)
@extends('layout.app')
@section('content')
    <div class="container-fluid">
        <div class="breadcrumb-main">
            <h4 class="text-capitalize fw-500 breadcrumb-title">Shift Kerja</h4>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        @endif

        <div class="row">
            <div class="col-lg-8 mb-4">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="bg-light">
                                    <tr><th>Shift</th><th>Jam</th><th>Toleransi Terlambat</th><th>Dipakai</th><th>Status</th><th>Aksi</th></tr>
                                </thead>
                                <tbody>
                                    @forelse($shifts as $item)
                                        <tr class="{{ $shift->exists && $shift->id === $item->id ? 'table-active' : '' }}">
                                            <td><span class="badge" style="background: {{ $item->color }}">{{ $item->name }}</span></td>
                                            <td>{{ $item->timeLabel() }}</td>
                                            <td>{{ $item->late_tolerance_minutes }} menit</td>
                                            <td>{{ $item->schedules_count }} jadwal</td>
                                            <td><span class="badge bg-{{ $item->status === 'active' ? 'success' : 'secondary' }}">{{ $item->status === 'active' ? 'Aktif' : 'Tidak Aktif' }}</span></td>
                                            <td class="text-nowrap">
                                                <a href="{{ route('clinic.work-shifts.edit', [app()->getLocale(), $item->id]) }}" class="btn btn-sm btn-outline-primary"><i class="uil uil-edit"></i></a>
                                                <form action="{{ route('clinic.work-shifts.destroy', [app()->getLocale(), $item->id]) }}" method="POST" class="d-inline"
                                                    onsubmit="return confirm(@js('Hapus shift ' . $item->name . '?'))">
                                                    @csrf @method('DELETE')
                                                    <button class="btn btn-sm btn-outline-danger"><i class="uil uil-trash"></i></button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="6" class="text-center py-4">Belum ada shift</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <p class="text-muted small mb-0">Jam selesai lebih kecil dari jam mulai berarti shift berakhir keesokan hari (contoh shift malam 21:00 - 07:00).</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">{{ $shift->exists ? 'Edit Shift' : 'Tambah Shift' }}</div>
                    <div class="card-body">
                        @if($errors->any())
                            <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
                        @endif
                        <form method="POST" action="{{ $shift->exists ? route('clinic.work-shifts.update', [app()->getLocale(), $shift->id]) : route('clinic.work-shifts.store', app()->getLocale()) }}">
                            @csrf
                            @if($shift->exists) @method('PUT') @endif
                            <div class="mb-3">
                                <label class="form-label">Nama Shift <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" maxlength="50" value="{{ old('name', $shift->name) }}" required>
                            </div>
                            <div class="row">
                                <div class="col-6 mb-3">
                                    <label class="form-label">Mulai</label>
                                    <input type="time" name="start_time" class="form-control" value="{{ old('start_time', substr((string) $shift->start_time, 0, 5)) }}" required>
                                </div>
                                <div class="col-6 mb-3">
                                    <label class="form-label">Selesai</label>
                                    <input type="time" name="end_time" class="form-control" value="{{ old('end_time', substr((string) $shift->end_time, 0, 5)) }}" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Toleransi Terlambat (menit)</label>
                                <input type="number" name="late_tolerance_minutes" class="form-control" min="0" max="240" value="{{ old('late_tolerance_minutes', $shift->late_tolerance_minutes) }}" required>
                            </div>
                            <div class="row">
                                <div class="col-6 mb-3">
                                    <label class="form-label">Warna</label>
                                    <input type="color" name="color" class="form-control form-control-color w-100" value="{{ old('color', $shift->color) }}">
                                </div>
                                <div class="col-6 mb-3">
                                    <label class="form-label">Status</label>
                                    <select name="status" class="form-select">
                                        <option value="active" @selected(old('status', $shift->status) === 'active')>Aktif</option>
                                        <option value="inactive" @selected(old('status', $shift->status) === 'inactive')>Tidak Aktif</option>
                                    </select>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary"><i class="uil uil-check me-1"></i>Simpan</button>
                            @if($shift->exists)
                                <a href="{{ route('clinic.work-shifts.index', app()->getLocale()) }}" class="btn btn-light">Batal</a>
                            @endif
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
