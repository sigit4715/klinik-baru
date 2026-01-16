@section('title', $title)
@section('description', $description)
@extends('layout.app')

@push('css')
    <style>
        .appointment-header {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border-radius: 16px;
            padding: 20px 24px;
            color: white;
            margin-bottom: 20px;
        }

        .queue-number {
            font-size: 48px;
            font-weight: 800;
            text-align: center;
            padding: 20px;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 12px;
        }

        .info-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 16px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.06);
            border: 1px solid #e5e7eb;
        }

        .info-card h6 {
            color: #6b7280;
            font-size: 12px;
            text-transform: uppercase;
            margin-bottom: 12px;
            letter-spacing: 0.5px;
        }

        .patient-info {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .patient-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
            font-weight: 700;
        }

        .history-item {
            padding: 12px;
            border-radius: 8px;
            background: #f9fafb;
            margin-bottom: 8px;
            cursor: pointer;
            transition: background 0.2s;
        }

        .history-item:hover {
            background: #f3f4f6;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid">
        @php
            $days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
            $months = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
            $ad = $appointment->appointment_date;
        @endphp

        <!-- Header -->
        <div class="appointment-header">
            <div class="row align-items-center">
                <div class="col-md-3">
                    <div class="queue-number">{{ $appointment->queue_number }}</div>
                </div>
                <div class="col-md-6">
                    <h4 class="mb-2"><i class="uil uil-calendar-alt me-2"></i>Detail Antrian</h4>
                    <p class="mb-1 opacity-90">
                        <i class="uil uil-clock me-1"></i>
                        {{ $days[$ad->dayOfWeek] }}, {{ $ad->day }} {{ $months[$ad->month] }} {{ $ad->year }}
                        @if($appointment->appointment_time)
                            - {{ $appointment->appointment_time }}
                        @endif
                    </p>
                    <div class="mt-2">
                        @if($appointment->status == 'waiting')
                            <span class="badge bg-warning fs-6">Menunggu</span>
                        @elseif($appointment->status == 'in_progress')
                            <span class="badge bg-info fs-6">Sedang Diperiksa</span>
                        @elseif($appointment->status == 'completed')
                            <span class="badge bg-light text-success fs-6">✓ Selesai</span>
                        @else
                            <span class="badge bg-secondary fs-6">{{ $appointment->status }}</span>
                        @endif
                    </div>
                </div>
                <div class="col-md-3 text-end">
                    <a href="{{ route('clinic.appointments.print-struk', [app()->getLocale(), $appointment->id]) }}"
                        class="btn btn-light" target="_blank">
                        <i class="uil uil-print me-1"></i>Cetak Struk
                    </a>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Left Column -->
            <div class="col-lg-5">
                <!-- Patient Info -->
                <div class="info-card">
                    <h6><i class="uil uil-user me-1"></i>Informasi Pasien</h6>
                    <div class="patient-info mb-3">
                        <div class="patient-avatar">
                            {{ strtoupper(substr($appointment->patient->name, 0, 1)) }}
                        </div>
                        <div>
                            <h5 class="mb-1">{{ $appointment->patient->name }}</h5>
                            <code>{{ $appointment->patient->no_rm }}</code>
                        </div>
                    </div>
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td class="text-muted" width="35%">Gender</td>
                            <td>
                                @if($appointment->patient->gender == 'L')
                                    <span class="badge bg-primary">Laki-laki</span>
                                @else
                                    <span class="badge bg-pink" style="background:#ec4899;">Perempuan</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">TTL</td>
                            <td>
                                @if($appointment->patient->birth_date)
                                    @php $bd = $appointment->patient->birth_date; @endphp
                                    {{ $appointment->patient->birth_place }}, {{ $bd->day }} {{ $months[$bd->month] }}
                                    {{ $bd->year }}
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Umur</td>
                            <td class="fw-bold text-primary">
                                @if($appointment->patient->birth_date)
                                    @php
                                        $now = \Carbon\Carbon::now();
                                        $diff = $bd->diff($now);
                                        $ageStr = '';
                                        if ($diff->y > 0)
                                            $ageStr .= $diff->y . ' tahun ';
                                        if ($diff->m > 0)
                                            $ageStr .= $diff->m . ' bulan ';
                                        if ($diff->d > 0)
                                            $ageStr .= $diff->d . ' hari';
                                        if (empty(trim($ageStr)))
                                            $ageStr = '0 hari';
                                    @endphp
                                    {{ trim($ageStr) }}
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Telepon</td>
                            <td>{{ $appointment->patient->phone ?? '-' }}</td>
                        </tr>
                        @if($appointment->patient->allergy)
                            <tr>
                                <td class="text-muted">Alergi</td>
                                <td class="text-danger fw-bold">{{ $appointment->patient->allergy }}</td>
                            </tr>
                        @endif
                    </table>
                    <div class="mt-3">
                        <a href="{{ route('clinic.patients.show', [app()->getLocale(), $appointment->patient->id]) }}"
                            class="btn btn-sm btn-outline-primary">
                            <i class="uil uil-user me-1"></i>Lihat Profil Lengkap
                        </a>
                    </div>
                </div>

                <!-- Doctor Info -->
                <div class="info-card">
                    <h6><i class="uil uil-stethoscope me-1"></i>Dokter</h6>
                    <h5 class="mb-1">{{ $appointment->doctor->name }}</h5>
                    <p class="text-muted mb-0">{{ $appointment->doctor->specialization }}</p>
                </div>

                <!-- Complaint -->
                <div class="info-card">
                    <h6><i class="uil uil-comment-alt-medical me-1"></i>Keluhan</h6>
                    <p class="mb-0">{{ $appointment->complaint ?? '-' }}</p>
                </div>
            </div>

            <!-- Right Column -->
            <div class="col-lg-7">
                <!-- Action Buttons -->
                <div class="d-flex flex-wrap gap-2 mb-3">
                    @if($appointment->status == 'waiting')
                        <a href="{{ route('clinic.medical-records.create', [app()->getLocale(), $appointment->id]) }}"
                            class="btn btn-success"><i class="uil uil-stethoscope me-1"></i>Mulai Periksa</a>
                    @endif
                    @if($appointment->status == 'completed' && $appointment->medicalRecord)
                        <a href="{{ route('clinic.medical-records.show', [app()->getLocale(), $appointment->medicalRecord->id]) }}"
                            class="btn btn-info"><i class="uil uil-file-medical me-1"></i>Lihat Rekam Medis</a>
                    @endif
                    @if($appointment->status == 'completed' && !$appointment->payment)
                        <a href="{{ route('clinic.payments.create', [app()->getLocale(), $appointment->id]) }}"
                            class="btn btn-warning"><i class="uil uil-bill me-1"></i>Buat Pembayaran</a>
                    @endif
                    @if($appointment->payment)
                        <a href="{{ route('clinic.payments.show', [app()->getLocale(), $appointment->payment->id]) }}"
                            class="btn btn-outline-success"><i class="uil uil-receipt me-1"></i>Lihat Invoice</a>
                    @endif
                    <a href="{{ route('clinic.appointments.index', app()->getLocale()) }}" class="btn btn-light">Kembali</a>
                </div>

                <!-- Medical Record -->
                @if($appointment->medicalRecord)
                    <div class="card mb-3">
                        <div class="card-header bg-success text-white">
                            <i class="uil uil-file-medical-alt me-2"></i>Rekam Medis Kunjungan Ini
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p class="mb-2">
                                        <strong>Anamnesa:</strong><br>{{ $appointment->medicalRecord->anamnesa ?? '-' }}</p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-2"><strong>Diagnosis:</strong><br>{{ $appointment->medicalRecord->diagnosis }}
                                    </p>
                                </div>
                            </div>
                            @if($appointment->medicalRecord->treatment)
                                <hr>
                                <p class="mb-0"><strong>Tindakan:</strong><br>{{ $appointment->medicalRecord->treatment }}</p>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Patient History -->
                @php
                    $patientHistory = \App\Models\MedicalRecord::with(['doctor'])
                        ->where('patient_id', $appointment->patient_id)
                        ->where('appointment_id', '!=', $appointment->id)
                        ->orderBy('created_at', 'DESC')
                        ->limit(5)
                        ->get();
                @endphp
                @if($patientHistory->count() > 0)
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span><i class="uil uil-history me-2"></i>Riwayat Berobat Sebelumnya</span>
                            <a href="{{ route('clinic.medical-records.patient-history', [app()->getLocale(), $appointment->patient_id]) }}"
                                class="btn btn-sm btn-outline-primary">Lihat Semua</a>
                        </div>
                        <div class="card-body p-2">
                            @foreach($patientHistory as $history)
                                <div class="history-item"
                                    onclick="window.location='{{ route('clinic.medical-records.show', [app()->getLocale(), $history->id]) }}'">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            @php $hd = $history->created_at; @endphp
                                            <small class="text-muted">{{ $days[$hd->dayOfWeek] }}, {{ $hd->day }}
                                                {{ $months[$hd->month] }} {{ $hd->year }}</small>
                                            <p class="mb-0 fw-bold">{{ Str::limit($history->diagnosis, 50) }}</p>
                                            <small class="text-muted">{{ $history->doctor->name ?? '-' }}</small>
                                        </div>
                                        <i class="uil uil-angle-right text-muted"></i>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection