@section('title', $title)
@section('description', $description)
@extends('layout.app')

@push('css')
    <style>
        .medical-header {
            background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%);
            border-radius: 16px;
            padding: 28px 32px;
            margin-bottom: 24px;
            color: white;
            box-shadow: 0 10px 40px rgba(139, 92, 246, 0.3);
        }

        .medical-header h4 {
            margin: 0;
            font-size: 22px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .medical-header .subtitle {
            opacity: 0.9;
            font-size: 14px;
            margin-top: 6px;
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

        .medical-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.06);
            overflow: hidden;
            border: 1px solid #e5e7eb;
        }

        .medical-card .card-header {
            background: #f8fafc;
            border-bottom: 1px solid #e5e7eb;
            padding: 16px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .patient-card {
            background: white;
            border-radius: 14px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.06);
            margin-bottom: 12px;
            border: 1px solid #e5e7eb;
            overflow: hidden;
            transition: all 0.3s;
        }

        .patient-card:hover {
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
            border-color: #8b5cf6;
        }

        .patient-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 18px 20px;
            cursor: pointer;
        }

        .patient-info {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .patient-avatar {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 18px;
        }

        .patient-details h6 {
            margin: 0;
            font-size: 15px;
            font-weight: 600;
            color: #1f2937;
        }

        .patient-details .meta {
            font-size: 13px;
            color: #6b7280;
            margin-top: 3px;
        }

        .record-count {
            background: linear-gradient(135deg, #ddd6fe 0%, #c4b5fd 100%);
            color: #6d28d9;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
        }

        .last-visit {
            text-align: right;
        }

        .last-visit label {
            font-size: 11px;
            color: #9ca3af;
            text-transform: uppercase;
            display: block;
        }

        .last-visit .date {
            font-size: 14px;
            font-weight: 600;
            color: #4b5563;
        }

        .action-btn {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            transition: all 0.2s;
            font-size: 18px;
        }

        .action-btn.view {
            background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%);
            color: white;
        }

        .action-btn.view:hover {
            transform: scale(1.1);
            box-shadow: 0 4px 12px rgba(139, 92, 246, 0.4);
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
    </style>
@endpush

@section('content')
    <div class="container-fluid">
        <!-- Header -->
        <div class="medical-header">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                <div>
                    <h4><i class="uil uil-file-medical"></i> {{ $title }}</h4>
                    <div class="subtitle">Riwayat rekam medis semua pasien</div>
                </div>
                <form action="{{ route('clinic.medical-records.index', app()->getLocale()) }}" method="GET"
                    class="search-box">
                    <i class="uil uil-search"></i>
                    <input type="search" name="search" placeholder="Cari nama/no.RM..." value="{{ request('search') }}">
                    @if(isset($sort))
                        <input type="hidden" name="sort" value="{{ $sort }}">
                    @endif
                </form>
            </div>
        </div>

        <!-- Sort Options -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <span class="text-muted">Total: {{ $patients->total() }} pasien</span>
            <div class="d-flex align-items-center gap-2">
                <label class="mb-0 text-muted">Urutkan:</label>
                <select class="form-select form-select-sm" style="width: auto;" onchange="window.location.href='{{ route('clinic.medical-records.index', app()->getLocale()) }}?sort=' + this.value + '{{ request('search') ? '&search=' . request('search') : '' }}'">
                    <option value="latest" {{ ($sort ?? 'latest') == 'latest' ? 'selected' : '' }}>Terbaru</option>
                    <option value="most_visits" {{ ($sort ?? '') == 'most_visits' ? 'selected' : '' }}>Kunjungan Terbanyak</option>
                    <option value="name" {{ ($sort ?? '') == 'name' ? 'selected' : '' }}>Nama A-Z</option>
                    <option value="oldest" {{ ($sort ?? '') == 'oldest' ? 'selected' : '' }}>Terlama</option>
                </select>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="uil uil-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Patient List -->
        <div class="medical-card">
            <div class="card-header">
                <h6 style="margin: 0; font-weight: 600;">
                    <i class="uil uil-users-alt me-2"></i>Pasien dengan Rekam Medis
                </h6>
                <span style="color: #6b7280; font-size: 13px;">{{ $patients->total() }} pasien</span>
            </div>
            <div class="p-3">
                @forelse($patients as $patient)
                    <div class="patient-card">
                        <a href="{{ route('clinic.medical-records.patient-history', [app()->getLocale(), $patient->id]) }}"
                            class="patient-card-header text-decoration-none">
                            <div class="patient-info">
                                <div class="patient-avatar">
                                    {{ strtoupper(substr($patient->name, 0, 1)) }}
                                </div>
                                <div class="patient-details">
                                    <h6>{{ $patient->name }}</h6>
                                    <div class="meta">
                                        <span class="me-3"><i class="uil uil-file-alt me-1"></i>{{ $patient->no_rm }}</span>
                                        <span><i class="uil uil-phone me-1"></i>{{ $patient->phone }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-4">
                                <span class="record-count">
                                    <i class="uil uil-clipboard-notes me-1"></i>{{ $patient->medical_records_count }} Kunjungan
                                </span>
                                @if($patient->medicalRecords->first())
                                    <div class="last-visit">
                                        <label>Kunjungan Terakhir</label>
                                        @php
                                            $days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                                            $months = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                                            $lastVisit = $patient->medicalRecords->first()->created_at;
                                            $dayName = $days[$lastVisit->dayOfWeek];
                                            $monthName = $months[$lastVisit->month];
                                        @endphp
                                        <div class="date">{{ $dayName }}, {{ $lastVisit->day }} {{ $monthName }}
                                            {{ $lastVisit->year }}</div>
                                    </div>
                                @endif
                                <div class="action-btn view">
                                    <i class="uil uil-angle-right"></i>
                                </div>
                            </div>
                        </a>
                    </div>
                @empty
                    <div class="empty-state">
                        <i class="uil uil-file-medical-alt"></i>
                        <p>Belum ada rekam medis</p>
                    </div>
                @endforelse
            </div>

            @if($patients->hasPages())
                <div class="d-flex justify-content-end p-3 border-top">
                    {{ $patients->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>
@endsection