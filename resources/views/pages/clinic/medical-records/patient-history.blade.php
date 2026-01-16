@section('title', $title)
@section('description', $description)
@extends('layout.app')
@section('content')
    <div class="container-fluid">
        <div class="breadcrumb-main">
            <h4 class="breadcrumb-title">{{ $title }}</h4>
        </div>
        <div class="card mb-3">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h5>{{ $patient->name }}</h5>
                        <p><code>{{ $patient->no_rm }}</code> | {{ $patient->gender == 'L' ? 'Laki-laki' : 'Perempuan' }},
                            @php
                                if ($patient->birth_date) {
                                    $birthDate = \Carbon\Carbon::parse($patient->birth_date);
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
                        </p>
                    </div>
                    <div class="col-md-6 text-end">
                        <a href="{{ route('clinic.patients.show', [app()->getLocale(), $patient->id]) }}"
                            class="btn btn-outline-primary btn-sm">Lihat Profil</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                @forelse($records as $record)
                    <div class="border-start border-4 border-primary ps-3 mb-4">
                        <div class="d-flex justify-content-between">
                            <h6 class="mb-1">
                                @php
                                    $days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                                    $months = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                                    $date = $record->created_at;
                                    $dayName = $days[$date->dayOfWeek];
                                    $monthName = $months[$date->month];
                                @endphp
                                {{ $dayName }}, {{ $date->day }} {{ $monthName }} {{ $date->year }}
                            </h6>
                            <a href="{{ route('clinic.medical-records.show', [app()->getLocale(), $record->id]) }}"
                                class="btn btn-sm btn-outline-info">Detail</a>
                        </div>
                        <p class="text-muted small mb-2">Dr. {{ $record->doctor->name }}</p>
                        <p class="mb-1"><strong>Diagnosis:</strong> {{ $record->diagnosis }}</p>
                        <p class="mb-0"><strong>Tindakan:</strong> {{ $record->treatment ?? '-' }}</p>
                    </div>
                @empty
                    <p class="text-center text-muted py-4">Belum ada riwayat rekam medis</p>
                @endforelse
            </div>
        </div>
    </div>
@endsection