@section('title', $title)
@section('description', $description)
@extends('layout.app')
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="breadcrumb-main">
                    <h4 class="text-capitalize breadcrumb-title">{{ $title }}</h4>
                    <div class="breadcrumb-action justify-content-center flex-wrap">
                        <a href="{{ route('clinic.patients.edit', [app()->getLocale(), $patient->id]) }}"
                            class="btn btn-sm btn-primary">
                            <i class="uil uil-edit me-1"></i>Edit
                        </a>
                        <a href="{{ route('clinic.medical-records.patient-history', [app()->getLocale(), $patient->id]) }}"
                            class="btn btn-sm btn-info ms-2">
                            <i class="uil uil-file-medical me-1"></i>Rekam Medis
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            {{-- Patient Info Card --}}
            <div class="col-lg-4 mb-25">
                <div class="card h-100">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0"><i class="uil uil-user me-2"></i>Informasi Pasien</h6>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-4">
                            <div class="avatar avatar-xl bg-light rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center"
                                style="width: 100px; height: 100px;">
                                <i class="uil uil-user" style="font-size: 50px; color: #5840ff;"></i>
                            </div>
                            <h5 class="mb-1">{{ $patient->name }}</h5>
                            <code class="fs-6">{{ $patient->no_rm }}</code>
                        </div>

                        <table class="table table-borderless table-sm">
                            <tr>
                                <td class="text-muted">NIK</td>
                                <td class="text-end fw-500">{{ $patient->nik }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Gender</td>
                                <td class="text-end">
                                    @if($patient->gender == 'L')
                                        <span class="badge bg-primary">Laki-laki</span>
                                    @else
                                        <span class="badge bg-pink">Perempuan</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">TTL</td>
                                <td class="text-end">
                                    {{ $patient->birth_place }},
                                    @php
                                        $daysName = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                                        $monthsName = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                                        $bd = $patient->birth_date;
                                    @endphp
                                    {{ $daysName[$bd->dayOfWeek] }}, {{ $bd->day }} {{ $monthsName[$bd->month] }} {{ $bd->year }}
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">Umur</td>
                                <td class="text-end fw-bold text-primary">
                                    @php
                                        $now = \Carbon\Carbon::now();
                                        $diff = $bd->diff($now);
                                        $ageStr = '';
                                        if ($diff->y > 0) $ageStr .= $diff->y . ' tahun ';
                                        if ($diff->m > 0) $ageStr .= $diff->m . ' bulan ';
                                        if ($diff->d > 0) $ageStr .= $diff->d . ' hari';
                                        if (empty(trim($ageStr))) $ageStr = '0 hari';
                                    @endphp
                                    {{ trim($ageStr) }}
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">Gol. Darah</td>
                                <td class="text-end">{{ $patient->blood_type ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Telepon</td>
                                <td class="text-end">{{ $patient->phone }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Email</td>
                                <td class="text-end">{{ $patient->email ?? '-' }}</td>
                            </tr>
                        </table>

                        <hr>
                        <h6 class="mb-3">Alamat</h6>
                        <p class="mb-0">{{ $patient->address }}</p>

                        @if($patient->allergy)
                            <hr>
                            <h6 class="mb-3 text-danger">
                                <i class="uil uil-exclamation-triangle me-1"></i>Alergi
                            </h6>
                            <p class="mb-0 text-danger">{{ $patient->allergy }}</p>
                        @endif

                        <hr>
                        <h6 class="mb-3">Kontak Darurat</h6>
                        <p class="mb-1"><strong>{{ $patient->emergency_contact }}</strong></p>
                        <p class="mb-0">{{ $patient->emergency_phone }}</p>
                    </div>
                </div>
            </div>

            {{-- Activity Cards --}}
            <div class="col-lg-8">
                {{-- Statistics --}}
                <div class="row mb-25">
                    <div class="col-md-4">
                        <div class="card bg-primary text-white">
                            <div class="card-body text-center">
                                <h3 class="mb-1">{{ $patient->appointments->count() }}</h3>
                                <small>Total Kunjungan</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-success text-white">
                            <div class="card-body text-center">
                                <h3 class="mb-1">{{ $patient->medicalRecords->count() }}</h3>
                                <small>Rekam Medis</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-info text-white">
                            <div class="card-body text-center">
                                <h3 class="mb-1">Rp
                                    {{ number_format($patient->payments->where('status', 'paid')->sum('total'), 0, ',', '.') }}
                                </h3>
                                <small>Total Pembayaran</small>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Recent Appointments --}}
                <div class="card mb-25">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0"><i class="uil uil-calendar-alt me-2"></i>Riwayat Kunjungan</h6>
                        @if($patient->appointments->count() > 5)
                            <a href="{{ route('clinic.medical-records.patient-history', [app()->getLocale(), $patient->id]) }}" 
                               class="btn btn-sm btn-outline-primary">Lihat Semua</a>
                        @endif
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>No. Antrian</th>
                                        <th>Dokter</th>
                                        <th>Keluhan/Diagnosis</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                                        $months = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
                                    @endphp
                                    @forelse($patient->appointments->sortByDesc('appointment_date')->take(10) as $appointment)
                                        @php 
                                            $medicalRecord = $patient->medicalRecords->where('appointment_id', $appointment->id)->first();
                                        @endphp
                                        <tr style="cursor: pointer;" 
                                            @if($medicalRecord) 
                                                onclick="window.location='{{ route('clinic.medical-records.show', [app()->getLocale(), $medicalRecord->id]) }}'" 
                                                title="Klik untuk lihat detail rekam medis"
                                            @endif>
                                            <td>
                                                @php $ad = $appointment->appointment_date; @endphp
                                                {{ $days[$ad->dayOfWeek] }}, {{ $ad->day }} {{ $months[$ad->month] }} {{ $ad->year }}
                                            </td>
                                            <td><code>{{ $appointment->queue_number }}</code></td>
                                            <td>{{ $appointment->doctor?->name ?? '-' }}</td>
                                            <td>
                                                @if($medicalRecord)
                                                    <strong>{{ Str::limit($medicalRecord->diagnosis, 25) }}</strong>
                                                    @if($medicalRecord->treatment)
                                                        <br><small class="text-muted">{{ Str::limit($medicalRecord->treatment, 30) }}</small>
                                                    @endif
                                                @else
                                                    <em class="text-muted">{{ Str::limit($appointment->complaint, 40) }}</em>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $appointment->statusBadge() }}">{{ $appointment->statusLabel() }}</span>
                                            </td>
                                            <td>
                                                @if($medicalRecord)
                                                    <a href="{{ route('clinic.medical-records.show', [app()->getLocale(), $medicalRecord->id]) }}" 
                                                       class="btn btn-sm btn-primary" onclick="event.stopPropagation();">
                                                        <i class="uil uil-eye"></i> Detail
                                                    </a>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-4 text-muted">Belum ada riwayat kunjungan</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Quick Actions --}}
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex flex-wrap gap-2">
                            <a href="{{ route('clinic.appointments.create', app()->getLocale()) }}?patient_id={{ $patient->id }}"
                                class="btn btn-primary">
                                <i class="uil uil-plus me-1"></i>Buat Antrian
                            </a>
                            <a href="{{ route('clinic.patients.edit', [app()->getLocale(), $patient->id]) }}"
                                class="btn btn-outline-primary">
                                <i class="uil uil-edit me-1"></i>Edit Data
                            </a>
                            @can('letters.manage')
                                <div class="dropdown">
                                    <button class="btn btn-outline-success dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        <i class="uil uil-file-alt me-1"></i>Buat Surat
                                    </button>
                                    <ul class="dropdown-menu">
                                        @foreach(\App\Models\MedicalLetter::TYPES as $key => $label)
                                            <li><a class="dropdown-item" href="{{ route('clinic.letters.create', [app()->getLocale(), 'type' => $key, 'patient_id' => $patient->id]) }}">{{ $label }}</a></li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endcan
                            <a href="{{ route('clinic.patients.index', app()->getLocale()) }}" class="btn btn-light">
                                <i class="uil uil-arrow-left me-1"></i>Kembali
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection