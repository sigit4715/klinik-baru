@section('title', $title)
@section('description', $description)
@extends('layout.app')
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-body text-center">
                        @if($doctor->photo)
                            <img src="{{ asset('storage/' . $doctor->photo) }}" class="rounded-circle mb-3"
                                style="width: 120px; height: 120px; object-fit: cover;">
                        @else
                            <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center mx-auto mb-3"
                                style="width: 120px; height: 120px;">
                                <i class="uil uil-user-md text-white" style="font-size: 60px;"></i>
                            </div>
                        @endif
                        <h4>{{ $doctor->name }}</h4>
                        <p class="text-muted">{{ $doctor->specialization }}</p>
                        <span
                            class="badge bg-{{ $doctor->status == 'active' ? 'success' : 'secondary' }} mb-3">{{ $doctor->status == 'active' ? 'Aktif' : 'Tidak Aktif' }}</span>
                        <hr>
                        <table class="table table-borderless text-start">
                            <tr>
                                <td>SIP</td>
                                <td>{{ $doctor->sip_number }}</td>
                            </tr>
                            <tr>
                                <td>Email</td>
                                <td>{{ $doctor->email }}</td>
                            </tr>
                            <tr>
                                <td>Telepon</td>
                                <td>{{ $doctor->phone }}</td>
                            </tr>
                            <tr>
                                <td>Alamat</td>
                                <td>{{ $doctor->address ?? '-' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">Statistik</div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-4">
                                <h3 class="text-primary">{{ $doctor->appointments->count() }}</h3>
                                <small>Total Pasien</small>
                            </div>
                            <div class="col-4">
                                <h3 class="text-success">{{ $doctor->appointments->where('status', 'completed')->count() }}
                                </h3>
                                <small>Selesai</small>
                            </div>
                            <div class="col-4">
                                <h3 class="text-info">{{ $doctor->medicalRecords->count() }}</h3>
                                <small>Rekam Medis</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-3">
                    <a href="{{ route('clinic.doctors.edit', [app()->getLocale(), $doctor->id]) }}"
                        class="btn btn-primary">Edit</a>
                    <a href="{{ route('clinic.doctors.index', app()->getLocale()) }}" class="btn btn-light">Kembali</a>
                </div>
            </div>
        </div>
    </div>
@endsection