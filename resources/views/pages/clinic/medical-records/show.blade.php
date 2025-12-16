@section('title', $title)
@section('description', $description)
@extends('layout.app')
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header bg-primary text-white">Info Pasien</div>
                    <div class="card-body">
                        <h5>{{ $record->patient->name }}</h5>
                        <p><code>{{ $record->patient->no_rm }}</code></p>
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td>TTL</td>
                                <td>{{ $record->patient->birth_place }}, {{ $record->patient->birth_date->format('d/m/Y') }}
                                </td>
                            </tr>
                            <tr>
                                <td>Gender</td>
                                <td>{{ $record->patient->gender == 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
                            </tr>
                            <tr>
                                <td>Alergi</td>
                                <td class="text-danger">{{ $record->patient->allergy ?? '-' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span><i class="uil uil-file-medical me-2"></i>Detail Rekam Medis</span>
                        <a href="{{ route('clinic.medical-records.print', [app()->getLocale(), $record->id]) }}"
                            class="btn btn-sm btn-outline-secondary" target="_blank"><i
                                class="uil uil-print me-1"></i>Cetak</a>
                    </div>
                    <div class="card-body">
                        <p><strong>Tanggal:</strong> {{ $record->created_at->format('d M Y H:i') }}</p>
                        <p><strong>Dokter:</strong> {{ $record->doctor->name }} ({{ $record->doctor->specialization }})</p>
                        <hr>
                        <p><strong>Anamnesa:</strong><br>{{ $record->anamnesa ?? '-' }}</p>
                        <p><strong>Diagnosis:</strong><br>{{ $record->diagnosis }}</p>
                        <p><strong>Tindakan:</strong><br>{{ $record->treatment ?? '-' }}</p>

                        @if(count($prescriptionDetails) > 0)
                            <hr>
                            <h6>Resep Obat</h6>
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Obat</th>
                                        <th>Qty</th>
                                        <th>Dosis</th>
                                        <th>Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($prescriptionDetails as $p)
                                        <tr>
                                            <td>{{ $p['medicine']->name }}</td>
                                            <td>{{ $p['qty'] }}</td>
                                            <td>{{ $p['dosage'] }}</td>
                                            <td>{{ $p['notes'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif

                        @if(count($serviceDetails) > 0)
                            <hr>
                            <h6>Layanan</h6>
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Layanan</th>
                                        <th>Qty</th>
                                        <th>Harga</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($serviceDetails as $s)
                                        <tr>
                                            <td>{{ $s['service']->name }}</td>
                                            <td>{{ $s['qty'] }}</td>
                                            <td>Rp {{ number_format($s['service']->price, 0, ',', '.') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif

                        @if($record->notes)
                            <hr>
                            <p><strong>Catatan:</strong><br>{{ $record->notes }}</p>
                        @endif
                    </div>
                </div>
                <div class="mt-3">
                    @if($record->appointment && !$record->appointment->payment)
                        <a href="{{ route('clinic.payments.create', [app()->getLocale(), $record->appointment_id]) }}"
                            class="btn btn-warning"><i class="uil uil-bill me-1"></i>Buat Pembayaran</a>
                    @endif
                    <a href="{{ route('clinic.medical-records.index', app()->getLocale()) }}"
                        class="btn btn-light">Kembali</a>
                </div>
            </div>
        </div>
    </div>
@endsection