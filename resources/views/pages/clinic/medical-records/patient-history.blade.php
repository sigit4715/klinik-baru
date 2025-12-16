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
                            {{ $patient->birth_date->age }} tahun</p>
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
                            <h6 class="mb-1">{{ $record->created_at->format('d M Y') }}</h6>
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