@section('title', $title)
@section('description', $description)
@extends('layout.app')
@section('content')
    <div class="container-fluid">
        <div class="breadcrumb-main add-contact justify-content-sm-between">
            <div class="d-flex flex-wrap justify-content-center breadcrumb-main__wrapper">
                <h4 class="text-capitalize fw-500 breadcrumb-title">{{ $title }}</h4>
                <div class="action-btn mt-sm-0 mt-15 ms-3">
                    @can('appointments.manage')
                        <a href="{{ route('clinic.appointments.create', app()->getLocale()) }}" class="btn px-20 btn-primary"><i
                                class="las la-plus fs-16"></i>Buat Antrian</a>
                    @endcan
                    <a href="{{ route('clinic.appointments.today', app()->getLocale()) }}" class="btn btn-info ms-2"><i
                            class="uil uil-calendar-alt me-1"></i>Hari Ini</a>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button"
                    class="btn-close" data-bs-dismiss="alert"></button></div>
        @endif

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="bg-light">
                            <tr>
                                <th>No. Antrian</th>
                                <th>Tanggal</th>
                                <th>Pasien</th>
                                <th>Dokter</th>
                                <th>Keluhan</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($appointments as $apt)
                                <tr>
                                    <td><code>{{ $apt->queue_number }}</code></td>
                                    <td>{{ $apt->appointment_date->format('d/m/Y') }}</td>
                                    <td><a
                                            href="{{ route('clinic.patients.show', [app()->getLocale(), $apt->patient->id]) }}">{{ $apt->patient->name }}</a>
                                    </td>
                                    <td>{{ $apt->doctor->name }}</td>
                                    <td>{{ Str::limit($apt->complaint, 30) }}</td>
                                    <td>
                                        @if($apt->status == 'waiting')
                                            <span class="badge bg-warning">Menunggu</span>
                                        @elseif($apt->status == 'in_progress')
                                            <span class="badge bg-info">Sedang Diperiksa</span>
                                        @elseif($apt->status == 'completed')
                                            <span class="badge bg-success">Selesai</span>
                                        @else
                                            <span class="badge bg-secondary">{{ $apt->status }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('clinic.appointments.show', [app()->getLocale(), $apt->id]) }}"
                                            class="btn btn-sm btn-outline-info"><i class="uil uil-eye"></i></a>
                                        @if($apt->status == 'waiting')
                                            @can('appointments.manage')
                                                <a href="{{ route('clinic.appointments.edit', [app()->getLocale(), $apt->id]) }}"
                                                    class="btn btn-sm btn-outline-primary"><i class="uil uil-edit"></i></a>
                                            @endcan
                                        @endif
                                        @if(in_array($apt->status, ['waiting', 'in_progress']) && !$apt->medicalRecord)
                                            @can('medical_records.manage')
                                                <a href="{{ route('clinic.medical-records.create', [app()->getLocale(), $apt->id]) }}"
                                                    class="btn btn-sm btn-outline-success" title="Periksa"><i class="uil uil-file-medical"></i></a>
                                            @endcan
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4">Belum ada antrian</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $appointments->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
@endsection