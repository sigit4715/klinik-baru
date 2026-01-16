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
                                <td>
                                    {{ $record->patient->birth_place }},
                                    @php
                                        $days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                                        $months = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                                        $birthDate = $record->patient->birth_date;
                                        $dayName = $days[$birthDate->dayOfWeek];
                                        $monthName = $months[$birthDate->month];
                                    @endphp
                                    {{ $dayName }}, {{ $birthDate->day }} {{ $monthName }} {{ $birthDate->year }}
                                </td>
                            </tr>
                            <tr>
                                <td>Umur</td>
                                <td>
                                    @php
                                        if ($record->patient->birth_date) {
                                            $now = \Carbon\Carbon::now();
                                            $diff = $birthDate->diff($now);
                                            
                                            $ageStr = '';
                                            if ($diff->y > 0) {
                                                $ageStr .= $diff->y . ' tahun ';
                                            }
                                            if ($diff->m > 0) {
                                                $ageStr .= $diff->m . ' bulan ';
                                            }
                                            if ($diff->d > 0) {
                                                $ageStr .= $diff->d . ' hari';
                                            }
                                            if (empty(trim($ageStr))) {
                                                $ageStr = '0 hari';
                                            }
                                            echo trim($ageStr);
                                        } else {
                                            echo '-';
                                        }
                                    @endphp
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

                        @if($record->blood_pressure || $record->body_temperature || $record->pulse_rate || $record->respiratory_rate || $record->height || $record->weight)
                            <hr>
                            <h6><i class="uil uil-heartbeat me-1"></i>Tanda-Tanda Vital</h6>
                            <div class="row">
                                @if($record->blood_pressure)
                                    <div class="col-md-4 mb-2">
                                        <small class="text-muted">Tekanan Darah</small>
                                        <p class="mb-0 fw-500">{{ $record->blood_pressure }} mmHg</p>
                                    </div>
                                @endif
                                @if($record->body_temperature)
                                    <div class="col-md-4 mb-2">
                                        <small class="text-muted">Suhu Badan</small>
                                        <p class="mb-0 fw-500">{{ $record->body_temperature }} °C</p>
                                    </div>
                                @endif
                                @if($record->pulse_rate)
                                    <div class="col-md-4 mb-2">
                                        <small class="text-muted">Denyut Nadi</small>
                                        <p class="mb-0 fw-500">{{ $record->pulse_rate }} x/menit</p>
                                    </div>
                                @endif
                                @if($record->respiratory_rate)
                                    <div class="col-md-4 mb-2">
                                        <small class="text-muted">Pernapasan</small>
                                        <p class="mb-0 fw-500">{{ $record->respiratory_rate }} x/menit</p>
                                    </div>
                                @endif
                                @if($record->height)
                                    <div class="col-md-4 mb-2">
                                        <small class="text-muted">Tinggi Badan</small>
                                        <p class="mb-0 fw-500">{{ $record->height }} cm</p>
                                    </div>
                                @endif
                                @if($record->weight)
                                    <div class="col-md-4 mb-2">
                                        <small class="text-muted">Berat Badan</small>
                                        <p class="mb-0 fw-500">{{ $record->weight }} kg</p>
                                    </div>
                                @endif
                            </div>
                        @endif

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

                <!-- Patient History -->
                @if(isset($patientHistory) && $patientHistory->count() > 0)
                    <div class="card mt-3">
                        <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                            <span><i class="uil uil-history me-2"></i>Riwayat Berobat Sebelumnya</span>
                            <a href="{{ route('clinic.medical-records.patient-history', [app()->getLocale(), $record->patient_id]) }}" 
                               class="btn btn-sm btn-light">Lihat Semua</a>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Tanggal</th>
                                            <th>Dokter</th>
                                            <th>Diagnosis</th>
                                            <th>Tindakan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($patientHistory as $history)
                                            <tr onclick="window.location='{{ route('clinic.medical-records.show', [app()->getLocale(), $history->id]) }}'" 
                                                style="cursor: pointer;" 
                                                title="Klik untuk lihat detail">
                                                <td>
                                                    @php $hd = $history->created_at; @endphp
                                                    {{ $days[$hd->dayOfWeek] }}, {{ $hd->day }} {{ $months[$hd->month] }} {{ $hd->year }}
                                                </td>
                                                <td>{{ $history->doctor->name ?? '-' }}</td>
                                                <td>{{ Str::limit($history->diagnosis, 40) }}</td>
                                                <td>{{ Str::limit($history->treatment ?? '-', 30) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif

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