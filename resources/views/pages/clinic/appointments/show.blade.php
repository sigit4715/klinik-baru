@section('title', $title)
@section('description', $description)
@extends('layout.app')
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header bg-primary text-white">Info Antrian</div>
                    <div class="card-body">
                        <h3 class="text-center mb-3"><code>{{ $appointment->queue_number }}</code></h3>
                        <table class="table table-borderless">
                            <tr>
                                <td>Tanggal</td>
                                <td>{{ $appointment->appointment_date->format('d M Y') }}</td>
                            </tr>
                            <tr>
                                <td>Waktu</td>
                                <td>{{ $appointment->appointment_time ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td>Status</td>
                                <td>
                                    @if($appointment->status == 'waiting')<span class="badge bg-warning">Menunggu</span>
                                    @elseif($appointment->status == 'in_progress')<span
                                        class="badge bg-info">Diperiksa</span>
                                    @elseif($appointment->status == 'completed')<span
                                        class="badge bg-success">Selesai</span>
                                    @else<span class="badge bg-secondary">{{ $appointment->status }}</span>@endif
                                </td>
                            </tr>
                        </table>
                        <hr>
                        <h6>Pasien</h6>
                        <p><strong>{{ $appointment->patient->name }}</strong><br><small
                                class="text-muted">{{ $appointment->patient->no_rm }}</small></p>
                        <hr>
                        <h6>Dokter</h6>
                        <p><strong>{{ $appointment->doctor->name }}</strong><br><small
                                class="text-muted">{{ $appointment->doctor->specialization }}</small></p>
                        <hr>
                        <h6>Keluhan</h6>
                        <p>{{ $appointment->complaint }}</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-8">
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

                @if($appointment->medicalRecord)
                    <div class="card">
                        <div class="card-header">Rekam Medis</div>
                        <div class="card-body">
                            <p><strong>Diagnosis:</strong> {{ $appointment->medicalRecord->diagnosis }}</p>
                            <p><strong>Tindakan:</strong> {{ $appointment->medicalRecord->treatment ?? '-' }}</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection