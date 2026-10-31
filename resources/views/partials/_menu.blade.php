@php
    $locale = app()->getLocale();
    $is = fn (string $path) => Request::is($locale . '/clinic/' . $path);
@endphp
{{-- Each menu item is shown only if the user's role has the matching permission (config/permissions.php). --}}
<div class="sidebar__menu-group">
    <ul class="sidebar_nav">
        <li class="menu-title">
            <span>Klinik</span>
        </li>
        <li>
            <a href="{{ route('clinic.dashboard', $locale) }}" class="{{ $is('dashboard') ? 'active' : '' }}">
                <span class="nav-icon uil uil-estate"></span>
                <span class="menu-text">Dashboard</span>
            </a>
        </li>
        @can('appointments.view')
            <li>
                <a href="{{ route('clinic.appointments.today', $locale) }}" class="{{ $is('appointments/today') ? 'active' : '' }}">
                    <span class="nav-icon uil uil-list-ol"></span>
                    <span class="menu-text">Antrian Hari Ini</span>
                </a>
            </li>
        @endcan
        @canany(['patients.view', 'patients.manage'])
            <li class="has-child {{ $is('patients*') ? 'open' : '' }}">
                <a href="#" class="{{ $is('patients*') ? 'active' : '' }}">
                    <span class="nav-icon uil uil-users-alt"></span>
                    <span class="menu-text">Pasien</span>
                    <span class="toggle-icon"></span>
                </a>
                <ul>
                    @can('patients.view')
                        <li><a class="{{ $is('patients') ? 'active' : '' }}"
                                href="{{ route('clinic.patients.index', $locale) }}">Daftar Pasien</a></li>
                    @endcan
                    @can('patients.manage')
                        <li><a class="{{ $is('patients/create') ? 'active' : '' }}"
                                href="{{ route('clinic.patients.create', $locale) }}">Tambah Pasien</a></li>
                    @endcan
                </ul>
            </li>
        @endcanany
        @canany(['appointments.view', 'appointments.manage'])
            <li class="has-child {{ $is('appointments*') && !$is('appointments/today') ? 'open' : '' }}">
                <a href="#" class="{{ $is('appointments*') ? 'active' : '' }}">
                    <span class="nav-icon uil uil-calendar-alt"></span>
                    <span class="menu-text">Pendaftaran</span>
                    <span class="toggle-icon"></span>
                </a>
                <ul>
                    @can('appointments.view')
                        <li><a class="{{ $is('appointments') ? 'active' : '' }}"
                                href="{{ route('clinic.appointments.index', $locale) }}">Semua Antrian</a></li>
                    @endcan
                    @can('appointments.manage')
                        <li><a class="{{ $is('appointments/create') ? 'active' : '' }}"
                                href="{{ route('clinic.appointments.create', $locale) }}">Buat Antrian</a></li>
                    @endcan
                    @can('queue.display')
                        <li><a href="{{ route('clinic.appointments.display', $locale) }}" target="_blank">Display TV</a></li>
                    @endcan
                </ul>
            </li>
        @endcanany

        @can('nursing.manage')
            <li class="menu-title mt-30">
                <span>Perawat</span>
            </li>
            <li>
                <a href="{{ route('clinic.nursing.index', $locale) }}" class="{{ $is('nursing*') ? 'active' : '' }}">
                    <span class="nav-icon uil uil-heart-medical"></span>
                    <span class="menu-text">Pemeriksaan Perawat</span>
                </a>
            </li>
        @endcan

        @canany(['medical_records.view', 'medical_records.manage', 'doctors.view', 'letters.view'])
            <li class="menu-title mt-30">
                <span>Tindakan Dokter</span>
            </li>
        @endcanany
        @can('medical_records.manage')
            <li>
                <a href="{{ route('clinic.medical-records.queue', $locale) }}" class="{{ $is('medical-records/queue') ? 'active' : '' }}">
                    <span class="nav-icon uil uil-stethoscope"></span>
                    <span class="menu-text">Pasien Menunggu</span>
                </a>
            </li>
        @endcan
        @can('medical_records.view')
            <li class="has-child {{ $is('medical-records*') ? 'open' : '' }}">
                <a href="#" class="{{ $is('medical-records*') ? 'active' : '' }}">
                    <span class="nav-icon uil uil-file-medical-alt"></span>
                    <span class="menu-text">Rekam Medis</span>
                    <span class="toggle-icon"></span>
                </a>
                <ul>
                    <li><a class="{{ $is('medical-records') ? 'active' : '' }}"
                            href="{{ route('clinic.medical-records.index', $locale) }}">Semua Rekam Medis</a></li>
                </ul>
            </li>
        @endcan
        @can('letters.view')
            <li>
                <a href="{{ route('clinic.letters.index', $locale) }}" class="{{ $is('letters*') ? 'active' : '' }}">
                    <span class="nav-icon uil uil-file-alt"></span>
                    <span class="menu-text">Surat Medis</span>
                </a>
            </li>
        @endcan
        @canany(['doctors.view', 'doctors.manage'])
            <li class="has-child {{ $is('doctors*') ? 'open' : '' }}">
                <a href="#" class="{{ $is('doctors*') ? 'active' : '' }}">
                    <span class="nav-icon uil uil-user-md"></span>
                    <span class="menu-text">Dokter</span>
                    <span class="toggle-icon"></span>
                </a>
                <ul>
                    @can('doctors.view')
                        <li><a class="{{ $is('doctors') ? 'active' : '' }}"
                                href="{{ route('clinic.doctors.index', $locale) }}">Daftar Dokter</a></li>
                    @endcan
                    @can('doctors.manage')
                        <li><a class="{{ $is('doctors/create') ? 'active' : '' }}"
                                href="{{ route('clinic.doctors.create', $locale) }}">Tambah Dokter</a></li>
                    @endcan
                </ul>
            </li>
        @endcanany

        @canany(['payments.view', 'payments.manage', 'reports.finance', 'reports.patients'])
            <li class="menu-title mt-30">
                <span>Kasir & Laporan</span>
            </li>
        @endcanany
        @can('payments.manage')
            <li>
                <a href="{{ route('clinic.appointments.today', $locale) }}">
                    <span class="nav-icon uil uil-bill"></span>
                    <span class="menu-text">Antrian Pembayaran</span>
                </a>
            </li>
        @endcan
        @canany(['payments.view', 'reports.finance'])
            <li class="has-child {{ $is('payments*') ? 'open' : '' }}">
                <a href="#" class="{{ $is('payments*') ? 'active' : '' }}">
                    <span class="nav-icon uil uil-receipt"></span>
                    <span class="menu-text">Pembayaran</span>
                    <span class="toggle-icon"></span>
                </a>
                <ul>
                    @can('payments.view')
                        <li><a class="{{ $is('payments') ? 'active' : '' }}"
                                href="{{ route('clinic.payments.index', $locale) }}">Daftar Pembayaran</a></li>
                    @endcan
                    @can('reports.finance')
                        <li><a class="{{ $is('payments/report') ? 'active' : '' }}"
                                href="{{ route('clinic.payments.report', $locale) }}">Laporan Harian</a></li>
                    @endcan
                </ul>
            </li>
        @endcanany
        @can('reports.finance')
            <li class="has-child {{ $is('reports*') ? 'open' : '' }}">
                <a href="#" class="{{ $is('reports*') ? 'active' : '' }}">
                    <span class="nav-icon uil uil-chart-bar"></span>
                    <span class="menu-text">Laporan Keuangan</span>
                    <span class="toggle-icon"></span>
                </a>
                <ul>
                    <li><a class="{{ $is('reports/daily') ? 'active' : '' }}"
                            href="{{ route('clinic.reports.daily', $locale) }}">Pendapatan Harian</a></li>
                    <li><a class="{{ $is('reports/monthly') ? 'active' : '' }}"
                            href="{{ route('clinic.reports.monthly', $locale) }}">Pendapatan Bulanan</a></li>
                    <li><a class="{{ $is('reports/yearly') ? 'active' : '' }}"
                            href="{{ route('clinic.reports.yearly', $locale) }}">Pendapatan Tahunan</a></li>
                </ul>
            </li>
        @endcan
        @can('reports.patients')
            <li class="has-child {{ $is('patient-reports*') ? 'open' : '' }}">
                <a href="#" class="{{ $is('patient-reports*') ? 'active' : '' }}">
                    <span class="nav-icon uil uil-chart-pie-alt"></span>
                    <span class="menu-text">Laporan Pasien</span>
                    <span class="toggle-icon"></span>
                </a>
                <ul>
                    <li><a class="{{ $is('patient-reports/kb') ? 'active' : '' }}"
                            href="{{ route('clinic.patient-reports.kb', $locale) }}">Laporan KB</a></li>
                    <li><a class="{{ $is('patient-reports/imunisasi') ? 'active' : '' }}"
                            href="{{ route('clinic.patient-reports.imunisasi', $locale) }}">Laporan Imunisasi</a></li>
                    <li><a class="{{ $is('patient-reports/ibu-hamil') ? 'active' : '' }}"
                            href="{{ route('clinic.patient-reports.ibu-hamil', $locale) }}">Laporan Ibu Hamil</a></li>
                    <li><a class="{{ $is('patient-reports/melahirkan') ? 'active' : '' }}"
                            href="{{ route('clinic.patient-reports.melahirkan', $locale) }}">Laporan Melahirkan</a></li>
                    <li><a class="{{ $is('patient-reports/umum') ? 'active' : '' }}"
                            href="{{ route('clinic.patient-reports.umum', $locale) }}">Laporan Pasien Umum</a></li>
                </ul>
            </li>
        @endcan

        @canany(['services.view', 'medicines.view', 'medicines.manage', 'polis.manage'])
            <li class="menu-title mt-30">
                <span>Master Data & Apotek</span>
            </li>
        @endcanany
        @can('polis.manage')
            <li>
                <a href="{{ route('clinic.polis.index', $locale) }}" class="{{ $is('polis*') ? 'active' : '' }}">
                    <span class="nav-icon uil uil-hospital"></span>
                    <span class="menu-text">Poli</span>
                </a>
            </li>
        @endcan
        @can('services.view')
            <li>
                <a href="{{ route('clinic.services.index', $locale) }}" class="{{ $is('services*') ? 'active' : '' }}">
                    <span class="nav-icon uil uil-medkit"></span>
                    <span class="menu-text">Layanan/Tindakan</span>
                </a>
            </li>
        @endcan
        @canany(['medicines.view', 'medicines.manage'])
            <li class="has-child {{ $is('medicines*') ? 'open' : '' }}">
                <a href="#" class="{{ $is('medicines*') ? 'active' : '' }}">
                    <span class="nav-icon uil uil-capsule"></span>
                    <span class="menu-text">Obat</span>
                    <span class="toggle-icon"></span>
                </a>
                <ul>
                    @can('medicines.view')
                        <li><a class="{{ $is('medicines') ? 'active' : '' }}"
                                href="{{ route('clinic.medicines.index', $locale) }}">Daftar Obat</a></li>
                    @endcan
                    @can('medicines.manage')
                        <li><a class="{{ $is('medicines/create') ? 'active' : '' }}"
                                href="{{ route('clinic.medicines.create', $locale) }}">Tambah Obat</a></li>
                    @endcan
                    @can('medicines.view')
                        <li><a class="{{ $is('medicines/low-stock') ? 'active' : '' }}"
                                href="{{ route('clinic.medicines.low-stock', $locale) }}">Stok Rendah</a></li>
                    @endcan
                </ul>
            </li>
        @endcanany

        @canany(['attendance.self', 'attendance.manage', 'attendance.report'])
            <li class="menu-title mt-30">
                <span>Absensi Karyawan</span>
            </li>
        @endcanany
        @can('attendance.self')
            <li>
                <a href="{{ route('clinic.attendance.my', $locale) }}" class="{{ $is('attendance') ? 'active' : '' }}">
                    <span class="nav-icon uil uil-fingerprint"></span>
                    <span class="menu-text">Absensi Saya</span>
                </a>
            </li>
        @endcan
        @can('attendance.manage')
            <li>
                <a href="{{ route('clinic.schedules.index', $locale) }}" class="{{ $is('schedules*') ? 'active' : '' }}">
                    <span class="nav-icon uil uil-calendar-alt"></span>
                    <span class="menu-text">Jadwal Karyawan</span>
                </a>
            </li>
            <li>
                <a href="{{ route('clinic.work-shifts.index', $locale) }}" class="{{ $is('work-shifts*') ? 'active' : '' }}">
                    <span class="nav-icon uil uil-clock"></span>
                    <span class="menu-text">Shift Kerja</span>
                </a>
            </li>
        @endcan
        @can('attendance.report')
            <li>
                <a href="{{ route('clinic.attendance-report.index', $locale) }}" class="{{ $is('attendance-report*') ? 'active' : '' }}">
                    <span class="nav-icon uil uil-chart-line"></span>
                    <span class="menu-text">Laporan Absensi</span>
                </a>
            </li>
        @endcan
        @can('attendance.manage')
            <li>
                <a href="{{ route('clinic.attendance-settings.edit', $locale) }}" class="{{ $is('attendance-settings') ? 'active' : '' }}">
                    <span class="nav-icon uil uil-map-marker"></span>
                    <span class="menu-text">Pengaturan Absensi</span>
                </a>
            </li>
        @endcan

        @canany(['payroll.self', 'payroll.manage'])
            <li class="menu-title mt-30">
                <span>Penggajian</span>
            </li>
        @endcanany
        @can('payroll.manage')
            <li>
                <a href="{{ route('clinic.payroll.index', $locale) }}" class="{{ $is('payroll*') || $is('payslips*') ? 'active' : '' }}">
                    <span class="nav-icon uil uil-money-bill"></span>
                    <span class="menu-text">Penggajian</span>
                </a>
            </li>
            <li>
                <a href="{{ route('clinic.salary-profiles.index', $locale) }}" class="{{ $is('salary-profiles*') ? 'active' : '' }}">
                    <span class="nav-icon uil uil-setting"></span>
                    <span class="menu-text">Pengaturan Gaji</span>
                </a>
            </li>
            <li>
                <a href="{{ route('clinic.service-fees.index', $locale) }}" class="{{ $is('service-fees*') ? 'active' : '' }}">
                    <span class="nav-icon uil uil-medkit"></span>
                    <span class="menu-text">Tarif Fee Tindakan</span>
                </a>
            </li>
        @endcan
        @can('payroll.self')
            <li>
                <a href="{{ route('clinic.payroll.mine', $locale) }}" class="{{ $is('my-payslips') ? 'active' : '' }}">
                    <span class="nav-icon uil uil-receipt-alt"></span>
                    <span class="menu-text">Slip Gaji Saya</span>
                </a>
            </li>
        @endcan

        <li class="menu-title mt-30">
            <span>Pengaturan</span>
        </li>
        @can('settings.manage')
            <li>
                <a href="{{ route('clinic.settings.profile', $locale) }}" class="{{ $is('settings/profile') ? 'active' : '' }}">
                    <span class="nav-icon uil uil-building"></span>
                    <span class="menu-text">Profil Klinik</span>
                </a>
            </li>
        @endcan
        @can('users.manage')
            <li>
                <a href="{{ route('clinic.users.index', $locale) }}" class="{{ $is('users*') ? 'active' : '' }}">
                    <span class="nav-icon uil uil-users-alt"></span>
                    <span class="menu-text">Manajemen User</span>
                </a>
            </li>
        @endcan
        @can('roles.manage')
            <li>
                <a href="{{ route('clinic.roles.index', $locale) }}" class="{{ $is('roles*') ? 'active' : '' }}">
                    <span class="nav-icon uil uil-shield-check"></span>
                    <span class="menu-text">Manajemen Role</span>
                </a>
            </li>
        @endcan
        @can('archive.manage')
            <li>
                <a href="{{ route('clinic.archive.index', $locale) }}" class="{{ $is('archive*') ? 'active' : '' }}">
                    <span class="nav-icon uil uil-archive"></span>
                    <span class="menu-text">Arsip Data</span>
                </a>
            </li>
        @endcan
        @can('audit.view')
            <li>
                <a href="{{ route('clinic.audit-logs.index', $locale) }}" class="{{ $is('audit-logs*') ? 'active' : '' }}">
                    <span class="nav-icon uil uil-history"></span>
                    <span class="menu-text">Audit Log</span>
                </a>
            </li>
        @endcan
        @can('backup.manage')
            <li>
                <a href="{{ route('clinic.backup.index', $locale) }}" class="{{ $is('backup*') ? 'active' : '' }}">
                    <span class="nav-icon uil uil-database"></span>
                    <span class="menu-text">Backup Database</span>
                </a>
            </li>
        @endcan
        <li class="has-child">
            <a href="#">
                <span class="nav-icon uil uil-window-section"></span>
                <span class="menu-text">Tampilan</span>
                <span class="toggle-icon"></span>
            </a>
            <ul>
                <li class="l_sidebar"><a href="#" data-layout="light">Mode Terang</a></li>
                <li class="l_sidebar"><a href="#" data-layout="dark">Mode Gelap</a></li>
            </ul>
        </li>
    </ul>
</div>
