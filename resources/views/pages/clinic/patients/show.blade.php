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
                                <td class="text-end">{{ $patient->birth_place }},
                                    {{ $patient->birth_date->format('d M Y') }}</td>
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
                    <div class="card-header">
                        <h6 class="mb-0"><i class="uil uil-calendar-alt me-2"></i>Riwayat Kunjungan</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>No. Antrian</th>
                                        <th>Dokter</th>
                                        <th>Keluhan</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($patient->appointments->take(5) as $appointment)
                                        <tr>
                                            <td>{{ $appointment->appointment_date->format('d M Y') }}</td>
                                            <td><code>{{ $appointment->queue_number }}</code></td>
                                            <td>{{ $appointment->doctor->name }}</td>
                                            <td>{{ Str::limit($appointment->complaint, 30) }}</td>
                                            <td>
                                                @if($appointment->status == 'completed')
                                                    <span class="badge bg-success">Selesai</span>
                                                @elseif($appointment->status == 'waiting')
                                                    <span class="badge bg-warning">Menunggu</span>
                                                @else
                                                    <span class="badge bg-secondary">{{ $appointment->status }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-4 text-muted">Belum ada riwayat kunjungan</td>
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