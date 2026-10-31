@section('title', $title)
@section('description', $description)
@extends('layout.app')

@push('css')
    <style>
        .report-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 16px;
            padding: 24px 28px;
            margin-bottom: 24px;
            color: white;
            box-shadow: 0 10px 40px rgba(102, 126, 234, 0.3);
        }

        .report-header h4 {
            margin: 0;
            font-size: 22px;
            font-weight: 600;
        }

        .report-header .subtitle {
            opacity: 0.9;
            font-size: 14px;
            margin-top: 4px;
        }

        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.06);
            border: 1px solid #e5e7eb;
        }

        .stat-card .stat-value {
            font-size: 32px;
            font-weight: 700;
            color: #667eea;
        }

        .stat-card .stat-label {
            font-size: 13px;
            color: #6b7280;
            margin-top: 4px;
        }

        .filter-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.06);
            border: 1px solid #e5e7eb;
        }

        .period-tabs {
            display: flex;
            gap: 8px;
            margin-bottom: 16px;
            flex-wrap: wrap;
        }

        .period-tabs .btn {
            border-radius: 20px;
            padding: 8px 20px;
            font-size: 13px;
        }

        .period-tabs .btn.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-color: transparent;
            color: white;
        }

        .btn-export {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border: none;
            color: white;
        }

        .btn-export:hover {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            color: white;
        }

        .filter-section {
            display: none;
            padding: 15px;
            background: #f8fafc;
            border-radius: 8px;
            margin-top: 10px;
        }

        .filter-section.active {
            display: block;
        }

        @media print {
            .no-print, .sidebar-wrapper, .contents__top-bar, .filter-card { display: none !important; }
            .report-header { background: #667eea !important; print-color-adjust: exact; -webkit-print-color-adjust: exact; }
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid">
        <!-- Header -->
        <div class="report-header">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h4><i class="uil uil-chart-pie-alt me-2"></i>{{ $title }}</h4>
                    <div class="subtitle">{{ $description }}</div>
                </div>
                <div class="no-print d-flex gap-2">
                    <button class="btn btn-export" onclick="exportToExcel()">
                        <i class="uil uil-file-export me-1"></i>Export Excel
                    </button>
                    <button class="btn btn-light" onclick="window.print()">
                        <i class="uil uil-print me-1"></i>Cetak
                    </button>
                </div>
            </div>
        </div>

        <!-- Filter Card -->
        <div class="filter-card no-print">
            <form action="" method="GET" id="filterForm">
                <div class="period-tabs">
                    <button type="button" onclick="switchPeriod('all')" 
                        class="btn {{ $period == 'all' ? 'active' : 'btn-outline-secondary' }}" id="btn-all">
                        <i class="uil uil-apps me-1"></i>Semua Data
                    </button>
                    <button type="button" onclick="switchPeriod('daily')" 
                        class="btn {{ $period == 'daily' ? 'active' : 'btn-outline-secondary' }}" id="btn-daily">
                        <i class="uil uil-calendar-alt me-1"></i>Harian
                    </button>
                    <button type="button" onclick="switchPeriod('monthly')" 
                        class="btn {{ $period == 'monthly' ? 'active' : 'btn-outline-secondary' }}" id="btn-monthly">
                        <i class="uil uil-calender me-1"></i>Bulanan
                    </button>
                    <button type="button" onclick="switchPeriod('yearly')" 
                        class="btn {{ $period == 'yearly' ? 'active' : 'btn-outline-secondary' }}" id="btn-yearly">
                        <i class="uil uil-schedule me-1"></i>Tahunan
                    </button>
                </div>
                
                <input type="hidden" name="period" id="periodInput" value="{{ $period }}">

                <!-- Filter Harian -->
                <div class="filter-section {{ $period == 'daily' ? 'active' : '' }}" id="filter-daily">
                    <div class="row align-items-end">
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Dari Tanggal</label>
                            <input type="date" name="date_from" class="form-control" value="{{ $filters['date_from'] }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Sampai Tanggal</label>
                            <input type="date" name="date_to" class="form-control" value="{{ $filters['date_to'] }}">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="uil uil-filter me-1"></i>Tampilkan
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Filter Bulanan -->
                <div class="filter-section {{ $period == 'monthly' ? 'active' : '' }}" id="filter-monthly">
                    <div class="row align-items-end">
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Dari Bulan</label>
                            <select name="month_from" class="form-select">
                                @php
                                    $monthNames = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                                    $currentYear = date('Y');
                                @endphp
                                @for($y = $currentYear; $y >= $currentYear - 3; $y--)
                                    @for($m = 12; $m >= 1; $m--)
                                        @php $val = sprintf('%d-%02d', $y, $m); @endphp
                                        <option value="{{ $val }}" {{ $filters['month_from'] == $val ? 'selected' : '' }}>
                                            {{ $monthNames[$m-1] }} {{ $y }}
                                        </option>
                                    @endfor
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Sampai Bulan</label>
                            <select name="month_to" class="form-select">
                                @for($y = $currentYear; $y >= $currentYear - 3; $y--)
                                    @for($m = 12; $m >= 1; $m--)
                                        @php $val = sprintf('%d-%02d', $y, $m); @endphp
                                        <option value="{{ $val }}" {{ $filters['month_to'] == $val ? 'selected' : '' }}>
                                            {{ $monthNames[$m-1] }} {{ $y }}
                                        </option>
                                    @endfor
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="uil uil-filter me-1"></i>Tampilkan
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Filter Tahunan -->
                <div class="filter-section {{ $period == 'yearly' ? 'active' : '' }}" id="filter-yearly">
                    <div class="row align-items-end">
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Dari Tahun</label>
                            <select name="year_from" class="form-select">
                                @for($y = date('Y'); $y >= date('Y') - 10; $y--)
                                    <option value="{{ $y }}" {{ $filters['year_from'] == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Sampai Tahun</label>
                            <select name="year_to" class="form-select">
                                @for($y = date('Y'); $y >= date('Y') - 10; $y--)
                                    <option value="{{ $y }}" {{ $filters['year_to'] == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="uil uil-filter me-1"></i>Tampilkan
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Statistics -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-value">{{ $statistics['total'] }}</div>
                    <div class="stat-label">Total Pasien</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-value" style="color: #3b82f6;">{{ $statistics['male'] }}</div>
                    <div class="stat-label">Laki-laki</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-value" style="color: #ec4899;">{{ $statistics['female'] }}</div>
                    <div class="stat-label">Perempuan</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-value" style="color: #10b981;">{{ count($statistics['by_doctor']) }}</div>
                    <div class="stat-label">Dokter Aktif</div>
                </div>
            </div>
        </div>

        <!-- Period Info -->
        <div class="mb-3">
            <span class="badge bg-primary fs-6">
                @php
                    $days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                    $months = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                @endphp
                @if($period == 'all')
                    Semua Data
                @elseif($period == 'daily')
                    @php 
                        $df = \Carbon\Carbon::parse($filters['date_from']); 
                        $dt = \Carbon\Carbon::parse($filters['date_to']); 
                    @endphp
                    {{ $df->day }} {{ $months[$df->month] }} {{ $df->year }} - {{ $dt->day }} {{ $months[$dt->month] }} {{ $dt->year }}
                @elseif($period == 'monthly')
                    @php 
                        $mf = \Carbon\Carbon::parse($filters['month_from'] . '-01'); 
                        $mt = \Carbon\Carbon::parse($filters['month_to'] . '-01'); 
                    @endphp
                    {{ $months[$mf->month] }} {{ $mf->year }} - {{ $months[$mt->month] }} {{ $mt->year }}
                @else
                    Tahun {{ $filters['year_from'] }} - {{ $filters['year_to'] }}
                @endif
            </span>
        </div>

        <!-- Data Table -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="uil uil-list-ul me-2"></i>Data Pasien</h6>
                <span class="badge bg-secondary">{{ $records->count() }} Data</span>
            </div>
            <div class="card-body">
                @if($records->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover" id="reportTable">
                            <thead class="bg-light">
                                <tr>
                                    <th>No</th>
                                    <th>Tanggal</th>
                                    <th>No. RM</th>
                                    <th>Nama Pasien</th>
                                    <th>Gender</th>
                                    <th>Umur</th>
                                    <th>Dokter</th>
                                    <th>Diagnosis/Tindakan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($records as $index => $record)
                                    <tr onclick="window.location='{{ route('clinic.medical-records.show', [app()->getLocale(), $record->id]) }}'" style="cursor: pointer;" title="Klik untuk lihat detail rekam medis">
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            @php $rd = $record->created_at; @endphp
                                            {{ $days[$rd->dayOfWeek] }}, {{ $rd->day }} {{ $months[$rd->month] }} {{ $rd->year }}
                                        </td>
                                        <td>{{ $record->patient->no_rm }}</td>
                                        <td><strong>{{ $record->patient->name }}</strong></td>
                                        <td>{{ $record->patient->gender == 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
                                        <td>
                                            @if($record->patient->birth_date)
                                                @php
                                                    $diff = $record->patient->birth_date->diff(\Carbon\Carbon::now());
                                                    $age = $diff->y . ' th';
                                                    if ($diff->m > 0) $age .= ' ' . $diff->m . ' bl';
                                                @endphp
                                                {{ $age }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>{{ $record->doctor?->name ?? '-' }}</td>
                                        <td>{{ Str::limit(($record->diagnosis ?? '') . ' ' . ($record->treatment ?? ''), 60) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="uil uil-folder-open text-muted" style="font-size: 60px;"></i>
                        <p class="mt-3 text-muted">Tidak ada data untuk periode ini</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function switchPeriod(period) {
            // Hide all filter sections
            document.querySelectorAll('.filter-section').forEach(el => el.classList.remove('active'));
            
            // Remove active from all buttons
            document.querySelectorAll('.period-tabs .btn').forEach(el => {
                el.classList.remove('active');
                el.classList.add('btn-outline-secondary');
            });
            
            // Activate selected button
            var btn = document.getElementById('btn-' + period);
            if (btn) {
                btn.classList.add('active');
                btn.classList.remove('btn-outline-secondary');
            }
            
            // Show selected filter section
            var filterSection = document.getElementById('filter-' + period);
            if (filterSection) {
                filterSection.classList.add('active');
            }
            
            // Update hidden input
            document.getElementById('periodInput').value = period;
            
            // If 'all', submit form immediately
            if (period === 'all') {
                document.getElementById('filterForm').submit();
            }
        }

        function exportToExcel() {
            var table = document.getElementById('reportTable');
            if (!table) {
                alert('Tidak ada data untuk di-export');
                return;
            }
            
            var html = table.outerHTML;
            var url = 'data:application/vnd.ms-excel,' + encodeURIComponent(html);
            var downloadLink = document.createElement("a");
            
            document.body.appendChild(downloadLink);
            downloadLink.href = url;
            downloadLink.download = '{{ Str::slug($title) }}_{{ date("Y-m-d") }}.xls';
            downloadLink.click();
            document.body.removeChild(downloadLink);
        }
    </script>
    @endpush
@endsection