@section('title', $title)
@section('description', $description)
@extends('layout.app')

@push('css')
    <style>
        .patient-header {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            border-radius: 16px;
            padding: 28px 32px;
            margin-bottom: 24px;
            color: white;
            box-shadow: 0 10px 40px rgba(59, 130, 246, 0.3);
        }

        .patient-header h4 {
            margin: 0;
            font-size: 22px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .patient-header .subtitle {
            opacity: 0.9;
            font-size: 14px;
            margin-top: 6px;
        }

        .btn-add-patient {
            background: white;
            color: #3b82f6;
            border: none;
            padding: 12px 24px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 14px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(255, 255, 255, 0.2);
        }

        .btn-add-patient:hover {
            background: rgba(255, 255, 255, 0.9);
            color: #1d4ed8;
            transform: translateY(-2px);
        }

        .search-box {
            background: rgba(255, 255, 255, 0.15);
            border-radius: 12px;
            padding: 12px 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            backdrop-filter: blur(10px);
            max-width: 300px;
        }

        .search-box input {
            background: transparent;
            border: none;
            color: white;
            font-size: 14px;
            width: 100%;
        }

        .search-box input::placeholder {
            color: rgba(255, 255, 255, 0.7);
        }

        .search-box input:focus {
            outline: none;
        }

        .search-box i {
            color: rgba(255, 255, 255, 0.7);
        }

        .stats-row {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-bottom: 24px;
        }

        .stat-card {
            background: white;
            border-radius: 14px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            border-left: 4px solid;
            transition: transform 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-3px);
        }

        .stat-card.total {
            border-color: #3b82f6;
        }

        .stat-card.male {
            border-color: #06b6d4;
        }

        .stat-card.female {
            border-color: #ec4899;
        }

        .stat-card.today {
            border-color: #10b981;
        }

        .stat-card .stat-number {
            font-size: 28px;
            font-weight: 700;
            line-height: 1;
        }

        .stat-card.total .stat-number {
            color: #3b82f6;
        }

        .stat-card.male .stat-number {
            color: #06b6d4;
        }

        .stat-card.female .stat-number {
            color: #ec4899;
        }

        .stat-card.today .stat-number {
            color: #10b981;
        }

        .stat-card .stat-label {
            font-size: 13px;
            color: #6b7280;
            margin-top: 6px;
        }

        .patient-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.06);
            overflow: hidden;
            border: 1px solid #e5e7eb;
        }

        .patient-card .card-header {
            background: #f8fafc;
            border-bottom: 1px solid #e5e7eb;
            padding: 16px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .patient-card .card-header h6 {
            margin: 0;
            font-size: 15px;
            font-weight: 600;
            color: #1f2937;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .patient-table {
            margin: 0;
            width: 100%;
        }

        .patient-table thead {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
        }

        .patient-table thead th {
            color: white !important;
            font-weight: 500;
            padding: 14px 16px;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border: none !important;
            white-space: nowrap;
        }

        .patient-table tbody tr {
            border-bottom: 1px solid #f3f4f6;
            transition: all 0.2s;
        }

        .patient-table tbody tr:hover {
            background-color: #f8fafc;
        }

        .patient-table tbody td {
            padding: 14px 16px;
            vertical-align: middle;
            border: none !important;
        }

        .rm-badge {
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
            color: #1d4ed8;
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            font-family: monospace;
        }

        .patient-name {
            font-weight: 600;
            color: #1f2937;
            font-size: 14px;
            margin-bottom: 2px;
        }

        .patient-address {
            font-size: 12px;
            color: #9ca3af;
        }

        .gender-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }

        .gender-badge.male {
            background: linear-gradient(135deg, #cffafe 0%, #a5f3fc 100%);
            color: #0891b2;
        }

        .gender-badge.female {
            background: linear-gradient(135deg, #fce7f3 0%, #fbcfe8 100%);
            color: #db2777;
        }

        .action-buttons {
            display: flex;
            gap: 6px;
        }

        .action-btn {
            width: 34px;
            height: 34px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
        }

        .action-btn.view {
            background: rgba(59, 130, 246, 0.1);
            color: #3b82f6;
        }

        .action-btn.view:hover {
            background: #3b82f6;
            color: white;
        }

        .action-btn.edit {
            background: rgba(245, 158, 11, 0.1);
            color: #f59e0b;
        }

        .action-btn.edit:hover {
            background: #f59e0b;
            color: white;
        }

        .action-btn.delete {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
        }

        .action-btn.delete:hover {
            background: #ef4444;
            color: white;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }

        .empty-state i {
            font-size: 64px;
            color: #d1d5db;
            margin-bottom: 16px;
        }

        .empty-state p {
            color: #6b7280;
            font-size: 16px;
        }

        @media (max-width: 992px) {
            .stats-row {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 576px) {
            .stats-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid">
        <!-- Header -->
        <div class="patient-header">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                <div>
                    <h4><i class="uil uil-users-alt"></i> {{ $title }}</h4>
                    <div class="subtitle">Kelola data pasien klinik Anda</div>
                </div>
                <div class="d-flex flex-wrap gap-3 align-items-center">
                    <form action="{{ route('clinic.patients.index', app()->getLocale()) }}" method="GET" class="search-box">
                        <i class="uil uil-search"></i>
                        <input type="search" name="search" placeholder="Cari nama/no.RM..." value="{{ request('search') }}">
                    </form>
                    <a href="{{ route('clinic.patients.create', app()->getLocale()) }}" class="btn-add-patient">
                        <i class="uil uil-plus"></i>Tambah Pasien
                    </a>
                </div>
            </div>
        </div>

        <!-- Stats -->
        <div class="stats-row">
            <div class="stat-card total">
                <div class="stat-number">{{ $patients->total() }}</div>
                <div class="stat-label"><i class="uil uil-users-alt me-1"></i>Total Pasien</div>
            </div>
            <div class="stat-card male">
                <div class="stat-number">{{ $patients->where('gender', 'L')->count() }}</div>
                <div class="stat-label"><i class="uil uil-mars me-1"></i>Laki-laki</div>
            </div>
            <div class="stat-card female">
                <div class="stat-number">{{ $patients->where('gender', 'P')->count() }}</div>
                <div class="stat-label"><i class="uil uil-venus me-1"></i>Perempuan</div>
            </div>
            <div class="stat-card today">
                <div class="stat-number">{{ $patients->where('created_at', '>=', today())->count() }}</div>
                <div class="stat-label"><i class="uil uil-calendar-alt me-1"></i>Hari Ini</div>
            </div>
        </div>

        <!-- Advanced Filters -->
        <div class="patient-card mb-4">
            <div class="card-header" style="cursor: pointer;"
                onclick="document.getElementById('filterSection').classList.toggle('d-none')">
                <h6><i class="uil uil-filter"></i> Filter Lanjutan</h6>
                <i class="uil uil-angle-down"></i>
            </div>
            <div id="filterSection"
                class="p-3 {{ request()->hasAny(['address', 'gender', 'blood_type']) ? '' : 'd-none' }}">
                <form action="{{ route('clinic.patients.index', app()->getLocale()) }}" method="GET">
                    @if(request('search'))
                        <input type="hidden" name="search" value="{{ request('search') }}">
                    @endif
                    <div class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label small text-muted mb-1">Alamat (Provinsi/Kota/Kecamatan)</label>
                            <input type="text" name="address" class="form-control"
                                placeholder="Contoh: Jawa Barat, Bandung, Surabaya..." value="{{ request('address') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small text-muted mb-1">Jenis Kelamin</label>
                            <select name="gender" class="form-select">
                                <option value="">Semua</option>
                                <option value="L" {{ request('gender') == 'L' ? 'selected' : '' }}>Laki-laki</option>
                                <option value="P" {{ request('gender') == 'P' ? 'selected' : '' }}>Perempuan</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small text-muted mb-1">Golongan Darah</label>
                            <select name="blood_type" class="form-select">
                                <option value="">Semua</option>
                                <option value="A" {{ request('blood_type') == 'A' ? 'selected' : '' }}>A</option>
                                <option value="B" {{ request('blood_type') == 'B' ? 'selected' : '' }}>B</option>
                                <option value="AB" {{ request('blood_type') == 'AB' ? 'selected' : '' }}>AB</option>
                                <option value="O" {{ request('blood_type') == 'O' ? 'selected' : '' }}>O</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="uil uil-filter me-1"></i>Terapkan Filter
                            </button>
                            <a href="{{ route('clinic.patients.index', app()->getLocale()) }}"
                                class="btn btn-outline-secondary">
                                <i class="uil uil-redo me-1"></i>Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="uil uil-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Patient Table -->
        <div class="patient-card">
            <div class="card-header">
                <h6><i class="uil uil-list-ul"></i> Daftar Pasien</h6>
                <span style="color: #6b7280; font-size: 13px;">{{ $patients->total() }} pasien terdaftar</span>
            </div>
            <div class="table-responsive">
                <table class="patient-table">
                    <thead>
                        <tr>
                            <th>No. RM</th>
                            <th>Nama Pasien</th>
                            <th>Alamat</th>
                            <th>Gender</th>
                            <th>Telepon</th>
                            <th>Tgl Lahir</th>
                            <th style="width: 120px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($patients as $patient)
                            <tr>
                                <td>
                                    <span class="rm-badge">{{ $patient->no_rm }}</span>
                                </td>
                                <td>
                                    <div class="patient-name">{{ $patient->name }}</div>
                                    <div class="patient-address" style="color: #9ca3af; font-size: 11px;">NIK:
                                        {{ $patient->nik }}</div>
                                </td>
                                <td style="max-width: 280px;">
                                    <div style="font-size: 12px; color: #4b5563; line-height: 1.4;">
                                        {{ Str::limit($patient->address, 50) }}
                                    </div>
                                    @php
                                        // Extract address parts: Detail, Kelurahan, Kec. Kecamatan, Kota, Provinsi
                                        $addressParts = explode(', ', $patient->address);
                                        $province = null;
                                        $city = null;
                                        $kecamatan = null;
                                        $kelurahan = null;

                                        // Parse from end: Provinsi, Kota, Kec. Kecamatan, Kelurahan
                                        $count = count($addressParts);
                                        if ($count >= 1)
                                            $province = end($addressParts);
                                        if ($count >= 2)
                                            $city = $addressParts[$count - 2];
                                        if ($count >= 3) {
                                            $kec = $addressParts[$count - 3];
                                            $kecamatan = str_replace('Kec. ', '', $kec);
                                        }
                                        if ($count >= 4)
                                            $kelurahan = $addressParts[$count - 4];
                                    @endphp
                                    @if($province || $city || $kecamatan || $kelurahan)
                                        <div style="margin-top: 6px; display: flex; flex-wrap: wrap; gap: 3px;">
                                            @if($kelurahan && !str_contains($kelurahan, 'Jl.') && !str_contains($kelurahan, 'RT'))
                                                <span
                                                    style="background: #fef3c7; color: #92400e; padding: 2px 6px; border-radius: 8px; font-size: 9px;">
                                                    <i class="uil uil-home-alt" style="font-size: 9px;"></i>
                                                    {{ Str::limit($kelurahan, 15) }}
                                                </span>
                                            @endif
                                            @if($kecamatan && $kecamatan !== $kelurahan)
                                                <span
                                                    style="background: #e0e7ff; color: #3730a3; padding: 2px 6px; border-radius: 8px; font-size: 9px;">
                                                    <i class="uil uil-location-point" style="font-size: 9px;"></i>
                                                    {{ Str::limit($kecamatan, 15) }}
                                                </span>
                                            @endif
                                            @if($city && $city !== $kecamatan)
                                                <span
                                                    style="background: #dbeafe; color: #1d4ed8; padding: 2px 6px; border-radius: 8px; font-size: 9px;">
                                                    <i class="uil uil-building" style="font-size: 9px;"></i> {{ Str::limit($city, 18) }}
                                                </span>
                                            @endif
                                            @if($province && $province !== $city)
                                                <span
                                                    style="background: #dcfce7; color: #166534; padding: 2px 6px; border-radius: 8px; font-size: 9px;">
                                                    <i class="uil uil-map-marker" style="font-size: 9px;"></i>
                                                    {{ Str::limit($province, 12) }}
                                                </span>
                                            @endif
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    @if($patient->gender == 'L')
                                        <span class="gender-badge male"><i class="uil uil-mars me-1"></i>Laki-laki</span>
                                    @else
                                        <span class="gender-badge female"><i class="uil uil-venus me-1"></i>Perempuan</span>
                                    @endif
                                </td>
                                <td style="font-size: 13px; color: #4b5563;">{{ $patient->phone ?: '-' }}</td>
                                <td style="font-size: 13px; color: #4b5563;">{{ $patient->birth_date->format('d/m/Y') }}</td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="{{ route('clinic.patients.show', [app()->getLocale(), $patient->id]) }}"
                                            class="action-btn view" title="Lihat Detail">
                                            <i class="uil uil-eye"></i>
                                        </a>
                                        <a href="{{ route('clinic.patients.edit', [app()->getLocale(), $patient->id]) }}"
                                            class="action-btn edit" title="Edit">
                                            <i class="uil uil-edit"></i>
                                        </a>
                                        <a href="#" class="action-btn delete" title="Hapus"
                                            onclick="event.preventDefault(); if(confirm('Yakin ingin menghapus pasien {{ $patient->name }}?')) { document.getElementById('delete-{{ $patient->id }}').submit(); }">
                                            <i class="uil uil-trash-alt"></i>
                                        </a>
                                        <form style="display:none;" id="delete-{{ $patient->id }}"
                                            action="{{ route('clinic.patients.destroy', [app()->getLocale(), $patient->id]) }}"
                                            method="POST">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <div class="empty-state">
                                        <i class="uil uil-users-alt"></i>
                                        <p>Belum ada data pasien</p>
                                        <a href="{{ route('clinic.patients.create', app()->getLocale()) }}"
                                            class="btn btn-primary mt-3">
                                            <i class="uil uil-plus me-1"></i>Tambah Pasien Pertama
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($patients->hasPages())
                <div class="d-flex justify-content-end p-3 border-top">
                    {{ $patients->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>
@endsection