@section('title', $title)
@section('description', $description)
@extends('layout.app')
@section('content')
    @php
        $current = $attendance
            ? (in_array($attendance->status, ['present', 'late'], true) ? 'attended' : $attendance->status)
            : ($schedule ? 'attended' : 'permission');
    @endphp
    <div class="container-fluid">
        <div class="breadcrumb-main">
            <h4 class="breadcrumb-title">Koreksi Absensi</h4>
        </div>
        <div class="row">
            <div class="col-lg-7">
                <div class="card">
                    <div class="card-header">{{ $employee->name }} - {{ $day->format('d/m/Y') }}</div>
                    <div class="card-body">
                        <p class="mb-1"><strong>Jadwal:</strong>
                            {{ $schedule?->shift ? $schedule->shift->name . ' (' . $schedule->shift->timeLabel() . ')' : 'Tidak ada jadwal' }}</p>
                        @if($attendance)
                            <p class="mb-1"><strong>Tercatat:</strong>
                                <span class="badge bg-{{ $attendance->statusBadge() }}">{{ $attendance->statusLabel() }}</span>
                                Masuk {{ $attendance->check_in_at?->format('d/m H:i') ?? '-' }},
                                Pulang {{ $attendance->check_out_at?->format('d/m H:i') ?? '-' }}
                                @if($attendance->check_in_distance !== null) | jarak masuk {{ $attendance->check_in_distance }} m (±{{ $attendance->check_in_accuracy }} m) @endif
                            </p>
                            @if($attendance->is_manual)
                                <p class="text-muted small">Terakhir dikoreksi oleh {{ $attendance->recorder?->name ?? '-' }} pada {{ $attendance->updated_at->format('d/m/Y H:i') }}.</p>
                            @endif
                        @endif
                        <hr>

                        @if($errors->any())
                            <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
                        @endif

                        <form method="POST" action="{{ route('clinic.attendance-report.update', [app()->getLocale(), $employee->id, $day->toDateString()]) }}">
                            @csrf @method('PUT')
                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" id="status" class="form-select">
                                    <option value="attended" @selected(old('status', $current) === 'attended')>Hadir (terlambat dihitung otomatis dari jam masuk)</option>
                                    <option value="permission" @selected(old('status', $current) === 'permission')>Izin</option>
                                    <option value="sick" @selected(old('status', $current) === 'sick')>Sakit</option>
                                    <option value="leave" @selected(old('status', $current) === 'leave')>Cuti</option>
                                    <option value="absent" @selected(old('status', $current) === 'absent')>Alpa</option>
                                    @if($attendance)
                                        <option value="reset" @selected(old('status') === 'reset')>Hapus catatan absensi hari ini</option>
                                    @endif
                                </select>
                            </div>
                            <div class="row" id="timeFields">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Jam Masuk</label>
                                    <input type="time" name="check_in_time" class="form-control" value="{{ old('check_in_time', $attendance?->check_in_at?->format('H:i')) }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Jam Pulang</label>
                                    <input type="time" name="check_out_time" class="form-control" value="{{ old('check_out_time', $attendance?->check_out_at?->format('H:i')) }}">
                                    <small class="text-muted">Jam pulang lebih kecil dari jam masuk dianggap keesokan hari.</small>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Keterangan / Alasan <span class="text-danger">*</span></label>
                                <input type="text" name="notes" class="form-control" maxlength="500" value="{{ old('notes', $attendance?->notes) }}"
                                    placeholder="Contoh: Lupa absen pulang, surat dokter, cuti tahunan">
                            </div>
                            <button type="submit" class="btn btn-primary"><i class="uil uil-check me-1"></i>Simpan</button>
                            <a href="{{ url()->previous() }}" class="btn btn-light">Batal</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            const status = document.getElementById('status');
            const times = document.getElementById('timeFields');
            const toggle = () => { times.style.display = status.value === 'attended' ? '' : 'none'; };
            status.addEventListener('change', toggle);
            toggle();
        })();
    </script>
@endpush
