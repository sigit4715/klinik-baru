@section('title', $title)
@section('description', $description)
@extends('layout.app')
@section('content')
    <div class="crm mb-25">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    <div class="breadcrumb-main">
                        <h4 class="text-capitalize breadcrumb-title">Dashboard Klinik</h4>
                        <div class="breadcrumb-action justify-content-center flex-wrap">
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="#"><i class="uil uil-estate"></i>Home</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div>

                <!-- Welcome Banner -->
                <div class="col-xxl-12 mb-25">
                    <div class="card banner-feature--18 new d-flex bg-white">
                        <div class="container-fluid">
                            <div class="row">
                                <div class="col-xl-8">
                                    <div class="card-body px-25">
                                        <h1 class="banner-feature__heading color-dark">Selamat Datang di Sistem Klinik! 👋
                                        </h1>
                                        <p class="banner-feature__para color-dark">
                                            Kelola pasien, antrian, rekam medis, dan pembayaran dengan mudah.<br>
                                            Hari ini:
                                            <strong>{{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}</strong>
                                        </p>
                                        <div class="d-flex justify-content-sm-start justify-content-center gap-2">
                                            <a href="{{ route('clinic.appointments.create', app()->getLocale()) }}"
                                                class="btn btn-primary btn-md radius-xs fs-15">
                                                <i class="uil uil-plus me-1"></i>Buat Antrian
                                            </a>
                                            <a href="{{ route('clinic.patients.create', app()->getLocale()) }}"
                                                class="btn btn-outline-primary btn-md radius-xs fs-15">
                                                <i class="uil uil-user-plus me-1"></i>Pasien Baru
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-4 text-end d-none d-xl-block">
                                    <div class="banner-feature__shape" style="padding: 20px;">
                                        <i class="uil uil-medical-square"
                                            style="font-size: 120px; color: #8231D3; opacity: 0.3;"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Alert Cards -->
                @if($lowStockMedicines > 0)
                    <div class="col-12 mb-25">
                        <div class="alert-icon-area alert-icon-area--warning alert alert-warning fade show d-flex align-items-center"
                            role="alert">
                            <div class="alert-icon">
                                <img src="{{ asset('assets/img/svg/layers.svg') }}" alt="layers" class="svg">
                            </div>
                            <div class="alert-content">
                                <p><strong>Peringatan!</strong> Ada {{ $lowStockMedicines }} obat dengan stok rendah.
                                    <a href="{{ route('clinic.medicines.low-stock', app()->getLocale()) }}"
                                        class="alert-link">Lihat detail</a>
                                </p>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    </div>
                @endif

                @if($expiringMedicines > 0)
                    <div class="col-12 mb-25">
                        <div class="alert-icon-area alert-icon-area--danger alert alert-danger fade show d-flex align-items-center"
                            role="alert">
                            <div class="alert-icon">
                                <img src="{{ asset('assets/img/svg/alert-circle.svg') }}" alt="alert" class="svg">
                            </div>
                            <div class="alert-content">
                                <p><strong>Perhatian!</strong> Ada {{ $expiringMedicines }} obat yang hampir kadaluarsa.
                                    <a href="{{ route('clinic.medicines.index', app()->getLocale()) }}"
                                        class="alert-link">Periksa sekarang</a>
                                </p>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    </div>
                @endif

                <!-- Statistics Cards -->
                <div class="col-xxl-3 col-sm-6 mb-25">
                    <div class="ap-po-details ap-po-details--2 p-25 radius-xl d-flex justify-content-between">
                        <div class="overview-content w-100">
                            <div class="ap-po-details-content d-flex flex-wrap justify-content-between">
                                <div class="ap-po-details__titlebar">
                                    <h1>{{ $totalPatients }}</h1>
                                    <p>Total Pasien</p>
                                </div>
                                <div class="ap-po-details__icon-area">
                                    <div class="svg-icon order-bg-opacity-primary color-primary">
                                        <i class="uil uil-users-alt"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="ap-po-details-time">
                                <span class="color-primary"><i class="uil uil-database"></i></span>
                                <small>Data pasien terdaftar</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xxl-3 col-sm-6 mb-25">
                    <div class="ap-po-details ap-po-details--2 p-25 radius-xl d-flex justify-content-between">
                        <div class="overview-content w-100">
                            <div class="ap-po-details-content d-flex flex-wrap justify-content-between">
                                <div class="ap-po-details__titlebar">
                                    <h1>{{ $todayAppointments }}</h1>
                                    <p>Antrian Hari Ini</p>
                                </div>
                                <div class="ap-po-details__icon-area">
                                    <div class="svg-icon order-bg-opacity-info color-info">
                                        <i class="uil uil-calendar-alt"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="ap-po-details-time">
                                <span class="color-info"><i class="uil uil-clock"></i></span>
                                <small>{{ $waitingAppointments }} menunggu</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xxl-3 col-sm-6 mb-25">
                    <div class="ap-po-details ap-po-details--2 p-25 radius-xl d-flex justify-content-between">
                        <div class="overview-content w-100">
                            <div class="ap-po-details-content d-flex flex-wrap justify-content-between">
                                <div class="ap-po-details__titlebar">
                                    <h1>{{ $completedToday }}</h1>
                                    <p>Selesai Hari Ini</p>
                                </div>
                                <div class="ap-po-details__icon-area">
                                    <div class="svg-icon order-bg-opacity-success color-success">
                                        <i class="uil uil-check-circle"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="ap-po-details-time">
                                <span class="color-success"><i class="las la-arrow-up"></i>
                                    <strong>{{ $todayAppointments > 0 ? round(($completedToday / $todayAppointments) * 100) : 0 }}%</strong></span>
                                <small>Tingkat penyelesaian</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xxl-3 col-sm-6 mb-25">
                    <div class="ap-po-details ap-po-details--2 p-25 radius-xl d-flex justify-content-between">
                        <div class="overview-content w-100">
                            <div class="ap-po-details-content d-flex flex-wrap justify-content-between">
                                <div class="ap-po-details__titlebar">
                                    <h1>Rp {{ number_format($todayRevenue, 0, ',', '.') }}</h1>
                                    <p>Pendapatan Hari Ini</p>
                                </div>
                                <div class="ap-po-details__icon-area">
                                    <div class="svg-icon order-bg-opacity-warning color-warning">
                                        <i class="uil uil-usd-circle"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="ap-po-details-time">
                                <span class="color-warning"><i class="uil uil-chart-line"></i></span>
                                <small>Transaksi lunas</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Today's Queue Table -->
                <div class="col-xxl-8 mb-25">
                    <div class="card border-0 h-100">
                        <div class="card-header">
                            <h6>Antrian Hari Ini</h6>
                            <div class="card-extra">
                                <a href="{{ route('clinic.appointments.today', app()->getLocale()) }}"
                                    class="btn btn-sm btn-primary">
                                    <i class="uil uil-eye me-1"></i>Lihat Semua
                                </a>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-borderless">
                                    <thead>
                                        <tr class="userDatatable-header">
                                            <th><span class="userDatatable-title">No. Antrian</span></th>
                                            <th><span class="userDatatable-title">Pasien</span></th>
                                            <th><span class="userDatatable-title">Dokter</span></th>
                                            <th><span class="userDatatable-title">Status</span></th>
                                            <th><span class="userDatatable-title">Aksi</span></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($todayQueue as $queue)
                                            <tr>
                                                <td>
                                                    <div class="userDatatable-content">
                                                        <strong>{{ $queue->queue_number }}</strong>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="userDatatable-content d-flex align-items-center">
                                                        <div class="orderDatatable-avatar d-flex align-items-center me-2">
                                                            <div class="avatar avatar-circle avatar-sm bg-opacity-primary">
                                                                <span
                                                                    class="avatar-letter">{{ substr($queue->patient->name, 0, 1) }}</span>
                                                            </div>
                                                        </div>
                                                        <span>{{ $queue->patient->name }}</span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="userDatatable-content">{{ $queue->doctor->name }}</div>
                                                </td>
                                                <td>
                                                    @if($queue->status == 'waiting')
                                                        <span
                                                            class="order-bg-opacity-warning text-warning rounded-pill px-15 py-1 fw-500">Menunggu</span>
                                                    @elseif($queue->status == 'in_progress')
                                                        <span
                                                            class="order-bg-opacity-info text-info rounded-pill px-15 py-1 fw-500">Diperiksa</span>
                                                    @elseif($queue->status == 'completed')
                                                        <span
                                                            class="order-bg-opacity-success text-success rounded-pill px-15 py-1 fw-500">Selesai</span>
                                                    @else
                                                        <span
                                                            class="order-bg-opacity-secondary text-secondary rounded-pill px-15 py-1 fw-500">{{ $queue->status }}</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <a href="{{ route('clinic.appointments.show', [app()->getLocale(), $queue->id]) }}"
                                                        class="btn btn-default btn-squared btn-transparent-light">
                                                        <i class="uil uil-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center py-4">
                                                    <div class="dm-empty text-center">
                                                        <div class="dm-empty__image"><img class="svg"
                                                                src="{{ asset('assets/img/svg/empty-cart.svg') }}" alt="empty">
                                                        </div>
                                                        <div class="dm-empty__text">
                                                            <p class="text-muted">Belum ada antrian hari ini</p>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Revenue Summary -->
                <div class="col-xxl-4 mb-25">
                    <div class="card border-0 h-100">
                        <div class="card-header">
                            <h6><i class="uil uil-chart-pie-alt me-2"></i>Ringkasan Pendapatan</h6>
                        </div>
                        <div class="card-body">
                            <div class="revenue-labels d-flex flex-column gap-3">
                                <div
                                    class="revenue-labels__item d-flex align-items-center justify-content-between p-20 radius-md bg-opacity-primary">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="revenue-labels__icon bg-primary">
                                            <i class="uil uil-calendar-alt text-white"></i>
                                        </div>
                                        <div>
                                            <span class="revenue-labels__title color-dark">Hari Ini</span>
                                        </div>
                                    </div>
                                    <span class="revenue-labels__value color-primary fw-600">Rp
                                        {{ number_format($todayRevenue, 0, ',', '.') }}</span>
                                </div>
                                <div
                                    class="revenue-labels__item d-flex align-items-center justify-content-between p-20 radius-md bg-opacity-success">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="revenue-labels__icon bg-success">
                                            <i class="uil uil-chart text-white"></i>
                                        </div>
                                        <div>
                                            <span class="revenue-labels__title color-dark">Minggu Ini</span>
                                        </div>
                                    </div>
                                    <span class="revenue-labels__value color-success fw-600">Rp
                                        {{ number_format($weekRevenue ?? 0, 0, ',', '.') }}</span>
                                </div>
                                <div
                                    class="revenue-labels__item d-flex align-items-center justify-content-between p-20 radius-md bg-opacity-warning">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="revenue-labels__icon bg-warning">
                                            <i class="uil uil-invoice text-white"></i>
                                        </div>
                                        <div>
                                            <span class="revenue-labels__title color-dark">Bulan Ini</span>
                                        </div>
                                    </div>
                                    <span class="revenue-labels__value color-warning fw-600">Rp
                                        {{ number_format($monthRevenue ?? 0, 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Doctor & Status Summary -->
                <div class="col-xxl-4 col-lg-6 mb-25">
                    <div class="card border-0 h-100">
                        <div class="card-header">
                            <h6><i class="uil uil-user-md me-2"></i>Dokter Aktif</h6>
                        </div>
                        <div class="card-body">
                            @foreach($doctors as $doctor)
                                <div class="d-flex align-items-center justify-content-between mb-20 pb-20 border-bottom">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="avatar avatar-circle avatar-md bg-opacity-primary">
                                            <span class="avatar-letter text-primary">{{ substr($doctor->name, 0, 2) }}</span>
                                        </div>
                                        <div>
                                            <h6 class="mb-0 fw-500">{{ $doctor->name }}</h6>
                                            <span class="text-muted fs-12">{{ $doctor->specialization }}</span>
                                        </div>
                                    </div>
                                    <span class="badge badge-success badge-sm rounded-pill">Aktif</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Status Summary -->
                <div class="col-xxl-4 col-lg-6 mb-25">
                    <div class="card border-0 h-100">
                        <div class="card-header">
                            <h6><i class="uil uil-analytics me-2"></i>Status Antrian Hari Ini</h6>
                        </div>
                        <div class="card-body">
                            <div class="d-flex flex-column gap-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="d-flex align-items-center gap-2">
                                        <span class="color-warning"><i class="fas fa-circle fs-10"></i></span> Menunggu
                                    </span>
                                    <span class="badge bg-warning text-dark">{{ $waitingAppointments }}</span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-warning"
                                        style="width: {{ $todayAppointments > 0 ? ($waitingAppointments / $todayAppointments) * 100 : 0 }}%">
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <span class="d-flex align-items-center gap-2">
                                        <span class="color-info"><i class="fas fa-circle fs-10"></i></span> Sedang Diperiksa
                                    </span>
                                    <span class="badge bg-info text-white">{{ $inProgressAppointments ?? 0 }}</span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-info"
                                        style="width: {{ $todayAppointments > 0 ? (($inProgressAppointments ?? 0) / $todayAppointments) * 100 : 0 }}%">
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <span class="d-flex align-items-center gap-2">
                                        <span class="color-success"><i class="fas fa-circle fs-10"></i></span> Selesai
                                    </span>
                                    <span class="badge bg-success">{{ $completedToday }}</span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-success"
                                        style="width: {{ $todayAppointments > 0 ? ($completedToday / $todayAppointments) * 100 : 0 }}%">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="col-xxl-4 col-lg-6 mb-25">
                    <div class="card border-0 h-100">
                        <div class="card-header">
                            <h6><i class="uil uil-bolt-alt me-2"></i>Aksi Cepat</h6>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="{{ route('clinic.appointments.create', app()->getLocale()) }}"
                                    class="btn btn-primary btn-lg">
                                    <i class="uil uil-plus me-2"></i>Buat Antrian Baru
                                </a>
                                <a href="{{ route('clinic.patients.create', app()->getLocale()) }}"
                                    class="btn btn-outline-primary btn-lg">
                                    <i class="uil uil-user-plus me-2"></i>Daftar Pasien Baru
                                </a>
                                <a href="{{ route('clinic.appointments.today', app()->getLocale()) }}"
                                    class="btn btn-outline-info btn-lg">
                                    <i class="uil uil-list-ul me-2"></i>Lihat Antrian Hari Ini
                                </a>
                                <a href="{{ route('clinic.payments.report', app()->getLocale()) }}"
                                    class="btn btn-outline-success btn-lg">
                                    <i class="uil uil-file-graph me-2"></i>Laporan Keuangan
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection