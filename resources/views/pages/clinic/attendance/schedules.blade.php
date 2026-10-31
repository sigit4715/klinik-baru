@section('title', $title)
@section('description', $description)
@extends('layout.app')

@push('css')
    <style>
        .schedule-grid select { min-width: 110px; font-size: .85rem; }
        .schedule-grid td, .schedule-grid th { vertical-align: middle; }
        .schedule-grid .today { background: rgba(16, 185, 129, .08); }
    </style>
@endpush

@section('content')
    @php $dayNames = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab']; @endphp
    <div class="container-fluid">
        <div class="breadcrumb-main d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
            <h4 class="breadcrumb-title mb-0">Jadwal Karyawan</h4>
            <div class="d-flex gap-2 align-items-center flex-wrap">
                <a class="btn btn-sm btn-outline-secondary" href="{{ route('clinic.schedules.index', [app()->getLocale(), 'week' => $weekStart->copy()->subWeek()->toDateString(), 'role' => $role]) }}"><i class="uil uil-angle-left"></i> Minggu lalu</a>
                <strong>{{ $weekStart->format('d/m/Y') }} - {{ $weekStart->copy()->addDays(6)->format('d/m/Y') }}</strong>
                <a class="btn btn-sm btn-outline-secondary" href="{{ route('clinic.schedules.index', [app()->getLocale(), 'week' => $weekStart->copy()->addWeek()->toDateString(), 'role' => $role]) }}">Minggu depan <i class="uil uil-angle-right"></i></a>
                <a class="btn btn-sm btn-light" href="{{ route('clinic.schedules.index', [app()->getLocale(), 'role' => $role]) }}">Minggu ini</a>
                <form method="GET" action="{{ route('clinic.schedules.index', app()->getLocale()) }}">
                    <input type="hidden" name="week" value="{{ $weekStart->toDateString() }}">
                    <select name="role" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">Semua bagian</option>
                        @foreach($roles as $r)
                            <option value="{{ $r->name }}" @selected($role === $r->name)>{{ $r->label }}</option>
                        @endforeach
                    </select>
                </form>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        @endif

        <div class="card">
            <div class="card-body">
                @if($employees->isEmpty())
                    <p class="text-center text-muted py-4 mb-0">Belum ada karyawan. Karyawan adalah user aktif yang role-nya punya hak akses <em>Absen masuk/pulang</em>.</p>
                @else
                    <form method="POST" action="{{ route('clinic.schedules.update', app()->getLocale()) }}">
                        @csrf
                        <input type="hidden" name="week" value="{{ $weekStart->toDateString() }}">
                        <input type="hidden" name="role" value="{{ $role }}">
                        <div class="table-responsive">
                            <table class="table table-bordered schedule-grid mb-3">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Karyawan</th>
                                        @foreach($dates as $date)
                                            <th class="text-center {{ $date->isToday() ? 'today' : '' }}">{{ $dayNames[$date->dayOfWeek] }}<br><small>{{ $date->format('d/m') }}</small></th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($employees as $employee)
                                        <tr>
                                            <td class="text-nowrap">{{ $employee->name }}<br><small class="text-muted">{{ $employee->roleLabel() }}</small></td>
                                            @foreach($dates as $date)
                                                @php $schedule = $schedules->get($employee->id . '|' . $date->toDateString()); @endphp
                                                <td class="{{ $date->isToday() ? 'today' : '' }}">
                                                    <select name="schedule[{{ $employee->id }}][{{ $date->toDateString() }}]" class="form-select form-select-sm shift-select">
                                                        <option value="">Libur</option>
                                                        @foreach($shifts as $shift)
                                                            @if($shift->status === 'active' || $schedule?->work_shift_id == $shift->id)
                                                                <option value="{{ $shift->id }}" data-color="{{ $shift->color }}" @selected($schedule?->work_shift_id == $shift->id)>{{ $shift->name }}</option>
                                                            @endif
                                                        @endforeach
                                                    </select>
                                                    @if($schedule?->attendance)
                                                        <small class="text-success"><i class="uil uil-check"></i> {{ $schedule->attendance->statusLabel() }}</small>
                                                    @endif
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex gap-2 flex-wrap">
                            <button type="submit" class="btn btn-primary"><i class="uil uil-check me-1"></i>Simpan Jadwal</button>
                        </div>
                    </form>
                    <form method="POST" action="{{ route('clinic.schedules.copy-previous', app()->getLocale()) }}" class="mt-2"
                        onsubmit="return confirm('Salin jadwal minggu lalu ke hari yang masih kosong di minggu ini?')">
                        @csrf
                        <input type="hidden" name="week" value="{{ $weekStart->toDateString() }}">
                        <input type="hidden" name="role" value="{{ $role }}">
                        <button type="submit" class="btn btn-outline-secondary btn-sm"><i class="uil uil-copy me-1"></i>Salin dari Minggu Lalu</button>
                    </form>
                    <p class="text-muted small mt-3 mb-0">
                        Shift:
                        @foreach($shifts->where('status', 'active') as $shift)
                            <span class="badge me-1" style="background: {{ $shift->color }}">{{ $shift->name }} {{ $shift->timeLabel() }}</span>
                        @endforeach
                    </p>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Colour each cell by its shift
        document.querySelectorAll('.shift-select').forEach(select => {
            const paint = () => {
                const color = select.selectedOptions[0]?.dataset.color;
                select.style.borderLeft = color ? `6px solid ${color}` : '';
            };
            select.addEventListener('change', paint);
            paint();
        });
    </script>
@endpush
