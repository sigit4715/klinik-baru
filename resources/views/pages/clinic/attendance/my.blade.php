@section('title', $title)
@section('description', $description)
@extends('layout.app')

@section('content')
    @php
        $days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $action = $context['action'];
        $schedule = $context['schedule'];
        $attendance = $context['attendance'];
    @endphp
    <div class="container-fluid">
        <div class="breadcrumb-main">
            <h4 class="breadcrumb-title">Absensi Saya</h4>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        <div class="row">
            <div class="col-lg-5 mb-4">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <div class="text-muted">{{ $days[now()->dayOfWeek] }}, {{ now()->format('d/m/Y') }}</div>
                        <div id="clock" class="display-5 fw-bold my-2">{{ now()->format('H:i:s') }}</div>

                        @if($schedule?->shift)
                            <p class="mb-1">Shift <strong>{{ $schedule->shift->name }}</strong>
                                ({{ $schedule->shift->timeLabel() }})
                                @if(! $schedule->work_date->isToday())<span class="text-muted">jadwal {{ $schedule->work_date->format('d/m') }}</span>@endif
                            </p>
                        @endif

                        @if($attendance)
                            <p class="mb-1">
                                Masuk: <strong>{{ $attendance->check_in_at?->format('H:i') ?? '-' }}</strong> |
                                Pulang: <strong>{{ $attendance->check_out_at?->format('H:i') ?? '-' }}</strong>
                                <span class="badge bg-{{ $attendance->statusBadge() }}">{{ $attendance->statusLabel() }}</span>
                            </p>
                        @endif

                        @if($context['message'])
                            <p class="text-muted mb-3">{{ $context['message'] }}</p>
                        @endif

                        @if(! $configured)
                            <div class="alert alert-warning">Lokasi klinik untuk absensi belum diatur oleh admin.</div>
                        @elseif($action)
                            <form id="attendanceForm" method="POST"
                                action="{{ $action === 'check_in' ? route('clinic.attendance.check-in', app()->getLocale()) : route('clinic.attendance.check-out', app()->getLocale()) }}">
                                @csrf
                                <input type="hidden" name="latitude" id="latitude">
                                <input type="hidden" name="longitude" id="longitude">
                                <input type="hidden" name="accuracy" id="accuracy">
                                <button type="button" id="btnAttendance" class="btn btn-lg {{ $action === 'check_in' ? 'btn-success' : 'btn-warning' }} px-5">
                                    <i class="uil {{ $action === 'check_in' ? 'uil-signin' : 'uil-signout' }} me-1"></i>
                                    {{ $action === 'check_in' ? 'Absen Masuk' : 'Absen Pulang' }}
                                </button>
                            </form>
                            <div id="locationInfo" class="small text-muted mt-3">Lokasi akan dibaca saat tombol ditekan.</div>
                            <p class="small text-muted mt-2 mb-0">Absen hanya bisa dalam radius <strong>{{ $settings['radius'] }} m</strong> dari klinik. Izinkan akses lokasi (GPS) di browser.</p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-7 mb-4">
                <div class="card h-100">
                    <div class="card-header"><i class="uil uil-calendar-alt me-1"></i>Jadwal 14 Hari ke Depan</div>
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0">
                            <tbody>
                                @forelse($upcoming as $item)
                                    <tr>
                                        <td class="ps-3">{{ $days[$item->work_date->dayOfWeek] }}, {{ $item->work_date->format('d/m/Y') }}</td>
                                        <td><span class="badge" style="background: {{ $item->shift?->color }}">{{ $item->shift?->name }}</span></td>
                                        <td>{{ $item->shift?->timeLabel() }}</td>
                                        <td>{{ $item->notes }}</td>
                                    </tr>
                                @empty
                                    <tr><td class="text-center text-muted py-4">Belum ada jadwal.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="uil uil-history me-1"></i>Riwayat Absensi</span>
                <form method="GET" class="d-flex gap-2">
                    <input type="month" name="month" class="form-control form-control-sm" value="{{ $month->format('Y-m') }}" onchange="this.form.submit()">
                </form>
            </div>
            <div class="card-body p-0">
                @include('pages.clinic.attendance._rows', ['rows' => $rows, 'showUser' => false, 'canCorrect' => false])
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            const clock = document.getElementById('clock');
            // Server offset so the clock matches the time that will be recorded
            const offset = {{ now()->getTimestampMs() }} - Date.now();
            setInterval(() => {
                clock.textContent = new Date(Date.now() + offset).toLocaleTimeString('id-ID', { hour12: false });
            }, 1000);

            const btn = document.getElementById('btnAttendance');
            if (!btn) return;
            const info = document.getElementById('locationInfo');
            const clinic = { lat: {{ $settings['latitude'] ?? 'null' }}, lng: {{ $settings['longitude'] ?? 'null' }} };
            const radius = {{ $settings['radius'] }};

            function distance(lat1, lng1, lat2, lng2) {
                const r = 6371000, rad = Math.PI / 180;
                const a = Math.sin((lat2 - lat1) * rad / 2) ** 2 + Math.cos(lat1 * rad) * Math.cos(lat2 * rad) * Math.sin((lng2 - lng1) * rad / 2) ** 2;
                return Math.round(r * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a)));
            }

            btn.addEventListener('click', function () {
                if (!navigator.geolocation) {
                    info.innerHTML = '<span class="text-danger">Browser tidak mendukung lokasi. Gunakan Chrome di HP.</span>';
                    return;
                }
                btn.disabled = true;
                const label = btn.innerHTML;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Membaca lokasi...';

                navigator.geolocation.getCurrentPosition(function (pos) {
                    const { latitude, longitude, accuracy } = pos.coords;
                    document.getElementById('latitude').value = latitude;
                    document.getElementById('longitude').value = longitude;
                    document.getElementById('accuracy').value = accuracy;
                    const d = distance(clinic.lat, clinic.lng, latitude, longitude);
                    info.innerHTML = `Jarak ke klinik ± <strong>${d} m</strong> (akurasi GPS ±${Math.round(accuracy)} m)`;
                    if (d > radius) {
                        info.innerHTML += `<br><span class="text-danger">Anda di luar radius ${radius} m.</span>`;
                        btn.disabled = false;
                        btn.innerHTML = label;
                        return;
                    }
                    document.getElementById('attendanceForm').submit();
                }, function (err) {
                    const messages = { 1: 'Akses lokasi ditolak. Izinkan lokasi untuk situs ini di pengaturan browser.', 2: 'Lokasi tidak tersedia. Aktifkan GPS.', 3: 'Waktu membaca lokasi habis. Coba lagi.' };
                    info.innerHTML = `<span class="text-danger">${messages[err.code] || err.message}</span>`;
                    btn.disabled = false;
                    btn.innerHTML = label;
                }, { enableHighAccuracy: true, timeout: 20000, maximumAge: 0 });
            });
        })();
    </script>
@endpush
