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
                            - {{ $appointment->appointment_time->format('H:i') }}
                        @endif
                    </p>
                    <div class="mt-2">
                        <span class="badge bg-{{ $appointment->statusBadge() }} fs-6">{{ $appointment->statusLabel() }}</span>
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
                    <h6><i class="uil uil-stethoscope me-1"></i>Poli & Dokter</h6>
                    <h5 class="mb-1">{{ $appointment->doctor?->name ?? '-' }}</h5>
                    <p class="text-muted mb-2">{{ $appointment->poli?->name ?? 'Poli belum ditentukan' }} | {{ $appointment->doctor?->specialization ?? '-' }}</p>
                    <table class="table table-sm table-borderless mb-0 small">
                        <tr><td class="text-muted ps-0" width="40%">Perawat</td><td>{{ $appointment->nurse?->name ?? '-' }}</td></tr>
                        <tr><td class="text-muted ps-0">Petugas Pendaftaran</td><td>{{ $appointment->registeredBy?->name ?? '-' }}</td></tr>
                    </table>
                </div>

                @if($appointment->nursingAssessment)
                    @include('pages.clinic.medical-records._nurse_card', ['assessment' => $appointment->nursingAssessment])
                @endif

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
                    @if($appointment->isAtNurseStage() && $appointment->appointment_date->isToday())
                        @can('nursing.manage')
                            <a href="{{ route('clinic.nursing.edit', [app()->getLocale(), $appointment->id]) }}"
                                class="btn btn-primary"><i class="uil uil-heart-medical me-1"></i>Pemeriksaan Perawat</a>
                        @endcan
                    @endif
                    @if(in_array($appointment->status, ['ready', 'in_progress']) && !$appointment->medicalRecord)
                        @can('medical_records.manage')
                            <a href="{{ route('clinic.medical-records.create', [app()->getLocale(), $appointment->id]) }}"
                                class="btn btn-success"><i class="uil uil-stethoscope me-1"></i>Mulai Periksa</a>
                        @endcan
                    @endif
                    @if($appointment->status == 'completed' && $appointment->medicalRecord)
                        @can('medical_records.view')
                            <a href="{{ route('clinic.medical-records.show', [app()->getLocale(), $appointment->medicalRecord->id]) }}"
                                class="btn btn-info"><i class="uil uil-file-medical me-1"></i>Lihat Rekam Medis</a>
                        @endcan
                    @endif
                    @if($appointment->status == 'completed' && $appointment->needsPayment())
                        @can('payments.manage')
                            <a href="{{ route('clinic.payments.create', [app()->getLocale(), $appointment->id]) }}"
                                class="btn btn-warning"><i class="uil uil-bill me-1"></i>Buat Pembayaran</a>
                        @endcan
                    @endif
                    @if($appointment->payment)
                        @can('payments.view')
                            <a href="{{ route('clinic.payments.show', [app()->getLocale(), $appointment->payment->id]) }}"
                                class="btn btn-outline-success"><i class="uil uil-receipt me-1"></i>Lihat Invoice</a>
                        @endcan
                    @endif
                    <a href="{{ route('clinic.appointments.index', app()->getLocale()) }}" class="btn btn-light">Kembali</a>
                </div>

                <!-- Medical Record -->
                @if($appointment->medicalRecord)
                    @php $mr = $appointment->medicalRecord; @endphp
                    <div class="card mb-3">
                        <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                            <span><i class="uil uil-file-medical-alt me-2"></i>Rekam Medis Kunjungan Ini</span>
                            <a href="{{ route('clinic.medical-records.show', [app()->getLocale(), $mr->id]) }}"
                                class="btn btn-sm btn-light">Detail Lengkap</a>
                        </div>
                        <div class="card-body">
                            <!-- Anamnesa & Diagnosis -->
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="p-3 bg-light rounded">
                                        <small class="text-muted text-uppercase">Anamnesa</small>
                                        <p class="mb-0 mt-1">{{ $mr->anamnesa ?? '-' }}</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-3 rounded" style="background-color: #e0f2fe;">
                                        <small class="text-muted text-uppercase">Diagnosis</small>
                                        <p class="mb-0 mt-1 fw-bold" style="color: #0369a1;">{{ $mr->diagnosis }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Treatment -->
                            @if($mr->treatment)
                                <div class="p-3 rounded mb-3" style="background-color: #dcfce7;">
                                    <small class="text-muted text-uppercase">Tindakan / Treatment</small>
                                    <p class="mb-0 mt-1" style="color: #166534;">{{ $mr->treatment }}</p>
                                </div>
                            @endif

                            <!-- Vital Signs -->
                            @if($mr->blood_pressure || $mr->body_temperature || $mr->pulse_rate || $mr->respiratory_rate || $mr->height || $mr->weight)
                                <div class="mb-3">
                                    <small class="text-muted text-uppercase d-block mb-2">Tanda-Tanda Vital</small>
                                    <div class="row g-2">
                                        @if($mr->blood_pressure)
                                            <div class="col-md-4 col-6">
                                                <div class="p-2 border rounded text-center">
                                                    <i class="uil uil-heartbeat text-danger"></i>
                                                    <div class="fw-bold">{{ $mr->blood_pressure }} mmHg</div>
                                                    <small class="text-muted">Tekanan Darah</small>
                                                </div>
                                            </div>
                                        @endif
                                        @if($mr->body_temperature)
                                            <div class="col-md-4 col-6">
                                                <div class="p-2 border rounded text-center">
                                                    <i class="uil uil-temperature text-warning"></i>
                                                    <div class="fw-bold">{{ $mr->body_temperature }} °C</div>
                                                    <small class="text-muted">Suhu Badan</small>
                                                </div>
                                            </div>
                                        @endif
                                        @if($mr->pulse_rate)
                                            <div class="col-md-4 col-6">
                                                <div class="p-2 border rounded text-center">
                                                    <i class="uil uil-heart-rate text-danger"></i>
                                                    <div class="fw-bold">{{ $mr->pulse_rate }} bpm</div>
                                                    <small class="text-muted">Denyut Nadi</small>
                                                </div>
                                            </div>
                                        @endif
                                        @if($mr->respiratory_rate)
                                            <div class="col-md-4 col-6">
                                                <div class="p-2 border rounded text-center">
                                                    <i class="uil uil-wind text-info"></i>
                                                    <div class="fw-bold">{{ $mr->respiratory_rate }} /mnt</div>
                                                    <small class="text-muted">Pernapasan</small>
                                                </div>
                                            </div>
                                        @endif
                                        @if($mr->height)
                                            <div class="col-md-4 col-6">
                                                <div class="p-2 border rounded text-center">
                                                    <i class="uil uil-ruler text-secondary"></i>
                                                    <div class="fw-bold">{{ $mr->height }} cm</div>
                                                    <small class="text-muted">Tinggi Badan</small>
                                                </div>
                                            </div>
                                        @endif
                                        @if($mr->weight)
                                            <div class="col-md-4 col-6">
                                                <div class="p-2 border rounded text-center">
                                                    <i class="uil uil-weight text-secondary"></i>
                                                    <div class="fw-bold">{{ $mr->weight }} kg</div>
                                                    <small class="text-muted">Berat Badan</small>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            <!-- Prescriptions -->
                            @if($mr->prescriptions && count($mr->prescriptions) > 0)
                                <div class="mb-3">
                                    <small class="text-muted text-uppercase d-block mb-2"><i class="uil uil-capsule me-1"></i>Resep
                                        Obat</small>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered mb-0">
                                            <thead class="bg-light">
                                                <tr>
                                                    <th>Obat</th>
                                                    <th>Jumlah</th>
                                                    <th>Dosis</th>
                                                    <th>Keterangan</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php $prescriptionDetails = $mr->getPrescriptionDetails(); @endphp
                                                @foreach($prescriptionDetails as $prescription)
                                                    <tr>
                                                        <td>{{ $prescription['medicine']->name ?? 'Unknown' }}</td>
                                                        <td>{{ $prescription['qty'] }}</td>
                                                        <td>{{ $prescription['dosage'] ?? '-' }}</td>
                                                        <td>{{ $prescription['notes'] ?? '-' }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endif

                            <!-- Services Used -->
                            @if($mr->services_used && count($mr->services_used) > 0)
                                <div class="mb-3">
                                    <small class="text-muted text-uppercase d-block mb-2"><i
                                            class="uil uil-medical-square me-1"></i>Layanan/Tindakan</small>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered mb-0">
                                            <thead class="bg-light">
                                                <tr>
                                                    <th>Layanan</th>
                                                    <th>Jumlah</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php $serviceDetails = $mr->getServiceDetails(); @endphp
                                                @foreach($serviceDetails as $service)
                                                    <tr>
                                                        <td>{{ $service['service']->name ?? 'Unknown' }}</td>
                                                        <td>{{ $service['qty'] }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endif

                            <!-- Notes -->
                            @if($mr->notes)
                                <div class="p-3 rounded" style="background-color: #fef9c3;">
                                    <small class="text-muted text-uppercase"><i class="uil uil-notes me-1"></i>Catatan</small>
                                    <p class="mb-0 mt-1" style="color: #854d0e;">{{ $mr->notes }}</p>
                                </div>
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
                                            <small class="text-muted">{{ $history->doctor?->name ?? '-' }}</small>
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