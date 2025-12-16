<div class="sidebar__menu-group">
    <ul class="sidebar_nav">
        <li class="menu-title">
            <span>Klinik</span>
        </li>
        <li>
            <a href="{{ route('clinic.dashboard', app()->getLocale()) }}"
                class="{{ Request::is(app()->getLocale() . '/clinic') ? 'active' : '' }}">
                <span class="nav-icon uil uil-estate"></span>
                <span class="menu-text">Dashboard</span>
            </a>
        </li>
        <li>
            <a href="{{ route('clinic.appointments.today', app()->getLocale()) }}"
                class="{{ Request::is(app()->getLocale() . '/clinic/appointments/today') ? 'active' : '' }}">
                <span class="nav-icon uil uil-list-ol"></span>
                <span class="menu-text">Antrian Hari Ini</span>
            </a>
        </li>
        <li class="has-child {{ Request::is(app()->getLocale() . '/clinic/patients*') ? 'open' : '' }}">
            <a href="#" class="{{ Request::is(app()->getLocale() . '/clinic/patients*') ? 'active' : '' }}">
                <span class="nav-icon uil uil-users-alt"></span>
                <span class="menu-text">Pasien</span>
                <span class="toggle-icon"></span>
            </a>
            <ul>
                <li><a class="{{ Request::is(app()->getLocale() . '/clinic/patients') ? 'active' : '' }}"
                        href="{{ route('clinic.patients.index', app()->getLocale()) }}">Daftar Pasien</a></li>
                <li><a class="{{ Request::is(app()->getLocale() . '/clinic/patients/create') ? 'active' : '' }}"
                        href="{{ route('clinic.patients.create', app()->getLocale()) }}">Tambah Pasien</a></li>
            </ul>
        </li>
        <li
            class="has-child {{ Request::is(app()->getLocale() . '/clinic/appointments*') && !Request::is(app()->getLocale() . '/clinic/appointments/today') ? 'open' : '' }}">
            <a href="#" class="{{ Request::is(app()->getLocale() . '/clinic/appointments*') ? 'active' : '' }}">
                <span class="nav-icon uil uil-calendar-alt"></span>
                <span class="menu-text">Pendaftaran</span>
                <span class="toggle-icon"></span>
            </a>
            <ul>
                <li><a class="{{ Request::is(app()->getLocale() . '/clinic/appointments') ? 'active' : '' }}"
                        href="{{ route('clinic.appointments.index', app()->getLocale()) }}">Semua Antrian</a></li>
                <li><a class="{{ Request::is(app()->getLocale() . '/clinic/appointments/create') ? 'active' : '' }}"
                        href="{{ route('clinic.appointments.create', app()->getLocale()) }}">Buat Antrian</a></li>
            </ul>
        </li>

        <li class="menu-title mt-30">
            <span>Tindakan Dokter</span>
        </li>
        <li>
            <a href="{{ route('clinic.appointments.today', app()->getLocale()) }}"
                class="{{ Request::is(app()->getLocale() . '/clinic/appointments/today') ? 'active' : '' }}">
                <span class="nav-icon uil uil-stethoscope"></span>
                <span class="menu-text">Pasien Menunggu</span>
            </a>
        </li>
        <li class="has-child {{ Request::is(app()->getLocale() . '/clinic/medical-records*') ? 'open' : '' }}">
            <a href="#" class="{{ Request::is(app()->getLocale() . '/clinic/medical-records*') ? 'active' : '' }}">
                <span class="nav-icon uil uil-file-medical-alt"></span>
                <span class="menu-text">Rekam Medis</span>
                <span class="toggle-icon"></span>
            </a>
            <ul>
                <li><a class="{{ Request::is(app()->getLocale() . '/clinic/medical-records') ? 'active' : '' }}"
                        href="{{ route('clinic.medical-records.index', app()->getLocale()) }}">Semua Rekam Medis</a>
                </li>
            </ul>
        </li>
        <li class="has-child {{ Request::is(app()->getLocale() . '/clinic/doctors*') ? 'open' : '' }}">
            <a href="#" class="{{ Request::is(app()->getLocale() . '/clinic/doctors*') ? 'active' : '' }}">
                <span class="nav-icon uil uil-user-md"></span>
                <span class="menu-text">Dokter</span>
                <span class="toggle-icon"></span>
            </a>
            <ul>
                <li><a class="{{ Request::is(app()->getLocale() . '/clinic/doctors') ? 'active' : '' }}"
                        href="{{ route('clinic.doctors.index', app()->getLocale()) }}">Daftar Dokter</a></li>
                <li><a class="{{ Request::is(app()->getLocale() . '/clinic/doctors/create') ? 'active' : '' }}"
                        href="{{ route('clinic.doctors.create', app()->getLocale()) }}">Tambah Dokter</a></li>
            </ul>
        </li>

        <li class="menu-title mt-30">
            <span>Kasir</span>
        </li>
        <li>
            <a href="{{ route('clinic.appointments.today', app()->getLocale()) }}"
                class="{{ Request::is(app()->getLocale() . '/clinic/appointments/today') ? 'active' : '' }}">
                <span class="nav-icon uil uil-bill"></span>
                <span class="menu-text">Antrian Pembayaran</span>
            </a>
        </li>
        <li class="has-child {{ Request::is(app()->getLocale() . '/clinic/payments*') ? 'open' : '' }}">
            <a href="#" class="{{ Request::is(app()->getLocale() . '/clinic/payments*') ? 'active' : '' }}">
                <span class="nav-icon uil uil-receipt"></span>
                <span class="menu-text">Pembayaran</span>
                <span class="toggle-icon"></span>
            </a>
            <ul>
                <li><a class="{{ Request::is(app()->getLocale() . '/clinic/payments') ? 'active' : '' }}"
                        href="{{ route('clinic.payments.index', app()->getLocale()) }}">Daftar Pembayaran</a></li>
                <li><a class="{{ Request::is(app()->getLocale() . '/clinic/payments/report') ? 'active' : '' }}"
                        href="{{ route('clinic.payments.report', app()->getLocale()) }}">Laporan Harian</a></li>
            </ul>
        </li>

        <li class="menu-title mt-30">
            <span>Master Data</span>
        </li>
        <li>
            <a href="{{ route('clinic.services.index', app()->getLocale()) }}"
                class="{{ Request::is(app()->getLocale() . '/clinic/services*') ? 'active' : '' }}">
                <span class="nav-icon uil uil-medkit"></span>
                <span class="menu-text">Layanan/Tindakan</span>
            </a>
        </li>
        <li class="has-child {{ Request::is(app()->getLocale() . '/clinic/medicines*') ? 'open' : '' }}">
            <a href="#" class="{{ Request::is(app()->getLocale() . '/clinic/medicines*') ? 'active' : '' }}">
                <span class="nav-icon uil uil-capsule"></span>
                <span class="menu-text">Obat</span>
                <span class="toggle-icon"></span>
            </a>
            <ul>
                <li><a class="{{ Request::is(app()->getLocale() . '/clinic/medicines') ? 'active' : '' }}"
                        href="{{ route('clinic.medicines.index', app()->getLocale()) }}">Daftar Obat</a></li>
                <li><a class="{{ Request::is(app()->getLocale() . '/clinic/medicines/create') ? 'active' : '' }}"
                        href="{{ route('clinic.medicines.create', app()->getLocale()) }}">Tambah Obat</a></li>
                <li><a class="{{ Request::is(app()->getLocale() . '/clinic/medicines/low-stock') ? 'active' : '' }}"
                        href="{{ route('clinic.medicines.low-stock', app()->getLocale()) }}">Stok Rendah</a></li>
            </ul>
        </li>

        <li class="menu-title mt-30">
            <span>Pengaturan</span>
        </li>
        <li>
            <a href="{{ route('clinic.settings.profile', app()->getLocale()) }}"
                class="{{ Request::is(app()->getLocale() . '/clinic/settings/profile') ? 'active' : '' }}">
                <span class="nav-icon uil uil-building"></span>
                <span class="menu-text">Profil Klinik</span>
            </a>
        </li>
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