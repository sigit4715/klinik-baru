@section('title', $title)
@section('description', $description)
@extends('layout.app')
@section('content')
    <div class="container-fluid">
        <div class="breadcrumb-main">
            <h4 class="breadcrumb-title">Pengaturan Absensi</h4>
        </div>
        <div class="row">
            <div class="col-lg-7">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                @endif
                @if($errors->any())
                    <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
                @endif
                <div class="card">
                    <div class="card-header"><i class="uil uil-map-marker me-1"></i>Lokasi Klinik</div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('clinic.attendance-settings.update', app()->getLocale()) }}">
                            @csrf @method('PUT')
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Latitude <span class="text-danger">*</span></label>
                                    <input type="text" name="attendance_latitude" id="lat" class="form-control" placeholder="-6.5xxxxx"
                                        value="{{ old('attendance_latitude', $settings['latitude']) }}" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Longitude <span class="text-danger">*</span></label>
                                    <input type="text" name="attendance_longitude" id="lng" class="form-control" placeholder="106.6xxxxx"
                                        value="{{ old('attendance_longitude', $settings['longitude']) }}" required>
                                </div>
                            </div>
                            <div class="d-flex gap-2 flex-wrap mb-3">
                                <button type="button" id="btnUseLocation" class="btn btn-outline-primary btn-sm"><i class="uil uil-crosshair me-1"></i>Pakai Lokasi Saya Sekarang</button>
                                <a id="mapLink" href="#" target="_blank" rel="noopener" class="btn btn-outline-secondary btn-sm"><i class="uil uil-map me-1"></i>Cek di Google Maps</a>
                                <span id="locationInfo" class="small text-muted align-self-center"></span>
                            </div>
                            <p class="small text-muted">Cara paling mudah: buka halaman ini dari HP saat berada di dalam klinik, lalu tekan <em>Pakai Lokasi Saya Sekarang</em>. Bisa juga salin koordinat dari Google Maps (klik kanan titik klinik).</p>

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Radius Absen (meter)</label>
                                    <input type="number" name="attendance_radius" class="form-control" min="10" max="5000" value="{{ old('attendance_radius', $settings['radius']) }}" required>
                                    <small class="text-muted">Disarankan 50-150 m.</small>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Akurasi GPS Maks. (meter)</label>
                                    <input type="number" name="attendance_max_accuracy" class="form-control" min="0" max="5000" value="{{ old('attendance_max_accuracy', $settings['max_accuracy']) }}" required>
                                    <small class="text-muted">Lokasi dengan akurasi lebih buruk ditolak. 0 = tidak dicek.</small>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Absen Masuk Paling Awal</label>
                                    <div class="input-group">
                                        <input type="number" name="attendance_early_minutes" class="form-control" min="0" max="240" value="{{ old('attendance_early_minutes', $settings['early_minutes']) }}" required>
                                        <span class="input-group-text">menit</span>
                                    </div>
                                    <small class="text-muted">Sebelum jam mulai shift.</small>
                                </div>
                            </div>
                            <div class="form-check form-switch mb-4">
                                <input type="checkbox" class="form-check-input" name="attendance_allow_unscheduled" id="allowUnscheduled" value="1"
                                    @checked(old('attendance_allow_unscheduled', $settings['allow_unscheduled']))>
                                <label class="form-check-label" for="allowUnscheduled">Izinkan absen walau tidak ada jadwal hari itu</label>
                            </div>
                            <button type="submit" class="btn btn-primary"><i class="uil uil-check me-1"></i>Simpan</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="card">
                    <div class="card-header"><i class="uil uil-info-circle me-1"></i>Cara Kerja</div>
                    <div class="card-body small">
                        <ul class="mb-0 ps-3">
                            <li>Karyawan login dengan akunnya, buka <strong>Absensi Saya</strong>, lalu tekan Absen Masuk / Absen Pulang.</li>
                            <li>Browser membaca lokasi GPS. Absen hanya diterima bila jarak ke titik klinik tidak lebih dari radius.</li>
                            <li>Jam absen memakai jam server, bukan jam HP.</li>
                            <li>Terlambat dihitung bila absen masuk melewati jam mulai + toleransi shift.</li>
                            <li>Absen pulang sebelum jam selesai shift dicatat sebagai pulang cepat.</li>
                            <li>Lokasi butuh HTTPS (situs klinik sudah HTTPS) dan izin lokasi di browser.</li>
                            <li>Catatan: aplikasi web tidak bisa sepenuhnya mencegah aplikasi pemalsu lokasi (fake GPS). Jarak dan akurasi setiap absen disimpan di laporan untuk dicek.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            const lat = document.getElementById('lat');
            const lng = document.getElementById('lng');
            const link = document.getElementById('mapLink');
            const info = document.getElementById('locationInfo');
            const updateLink = () => { link.href = `https://www.google.com/maps?q=${encodeURIComponent(lat.value)},${encodeURIComponent(lng.value)}`; };
            lat.addEventListener('input', updateLink);
            lng.addEventListener('input', updateLink);
            updateLink();

            document.getElementById('btnUseLocation').addEventListener('click', function () {
                if (!navigator.geolocation) { info.textContent = 'Browser tidak mendukung lokasi.'; return; }
                info.textContent = 'Membaca lokasi...';
                navigator.geolocation.getCurrentPosition(pos => {
                    lat.value = pos.coords.latitude.toFixed(7);
                    lng.value = pos.coords.longitude.toFixed(7);
                    info.textContent = `Akurasi ±${Math.round(pos.coords.accuracy)} m`;
                    updateLink();
                }, err => { info.textContent = 'Gagal membaca lokasi: ' + err.message; },
                { enableHighAccuracy: true, timeout: 20000, maximumAge: 0 });
            });
        })();
    </script>
@endpush
