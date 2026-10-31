{{--
    Attendance rows from AttendanceService::reportRows().
    Expects $rows, $showUser (bool), $canCorrect (bool)
--}}
@php $days = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab']; @endphp
<div class="table-responsive">
    <table class="table table-sm table-hover mb-0 align-middle">
        <thead class="bg-light">
            <tr>
                <th class="ps-3">Tanggal</th>
                @if($showUser)<th>Karyawan</th>@endif
                <th>Shift</th>
                <th>Masuk</th>
                <th>Pulang</th>
                <th>Status</th>
                <th>Terlambat</th>
                <th>Pulang Cepat</th>
                <th>Jarak</th>
                <th>Keterangan</th>
                @if($canCorrect)<th></th>@endif
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                @php $a = $row['attendance']; @endphp
                <tr>
                    <td class="ps-3 text-nowrap">{{ $days[$row['date']->dayOfWeek] }}, {{ $row['date']->format('d/m/Y') }}</td>
                    @if($showUser)
                        <td>{{ $row['user']?->name ?? '-' }}<br><small class="text-muted">{{ $row['user']?->roleLabel() }}</small></td>
                    @endif
                    <td class="text-nowrap">{{ $row['shift'] ?? '-' }}
                        @if($row['start'])<br><small class="text-muted">{{ $row['start']->format('H:i') }} - {{ $row['end']?->format('H:i') }}</small>@endif
                    </td>
                    <td>{{ $a?->check_in_at?->format('H:i') ?? '-' }}</td>
                    <td>
                        {{ $a?->check_out_at?->format('H:i') ?? '-' }}
                        @if($row['missing_checkout'])<br><span class="badge bg-danger">Tidak absen pulang</span>@endif
                    </td>
                    <td>
                        @if($row['status'] === 'upcoming')
                            <span class="badge bg-light text-dark">Belum</span>
                        @else
                            <span class="badge bg-{{ \App\Models\Attendance::STATUS_BADGES[$row['status']] ?? 'secondary' }}">{{ \App\Models\Attendance::STATUS_LABELS[$row['status']] ?? $row['status'] }}</span>
                        @endif
                        @if($a?->is_manual)<br><small class="text-muted" title="Dicatat oleh {{ $a->recorder?->name }}">manual</small>@endif
                    </td>
                    <td>{{ $a && $a->late_minutes ? $a->late_minutes . ' mnt' : '-' }}</td>
                    <td>{{ $a && $a->early_leave_minutes ? $a->early_leave_minutes . ' mnt' : '-' }}</td>
                    <td class="small text-nowrap">
                        @if($a?->check_in_distance !== null) M {{ $a->check_in_distance }} m @endif
                        @if($a?->check_out_distance !== null)<br>P {{ $a->check_out_distance }} m @endif
                    </td>
                    <td class="small">{{ $a?->notes }}</td>
                    @if($canCorrect)
                        <td class="text-nowrap">
                            @if($row['user'])
                                <a href="{{ route('clinic.attendance-report.edit', [app()->getLocale(), $row['user']->id, $row['date']->format('Y-m-d')]) }}"
                                    class="btn btn-sm btn-outline-primary" title="Koreksi / Izin"><i class="uil uil-edit"></i></a>
                            @endif
                        </td>
                    @endif
                </tr>
            @empty
                <tr><td colspan="11" class="text-center text-muted py-4">Belum ada data.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
