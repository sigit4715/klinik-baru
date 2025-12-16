@section('title', $title)
@section('description', $description)
@extends('layout.app')
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="breadcrumb-main add-contact justify-content-sm-between">
                    <div class="d-flex flex-wrap justify-content-center breadcrumb-main__wrapper">
                        <div class="d-flex align-items-center add-contact__title justify-content-center me-sm-25">
                            <h4 class="text-capitalize fw-500 breadcrumb-title">{{ $title }}</h4>
                        </div>
                        <div class="action-btn mt-sm-0 mt-15">
                            <a href="{{ route('clinic.doctors.create', app()->getLocale()) }}"
                                class="btn px-20 btn-primary">
                                <i class="las la-plus fs-16"></i>Tambah Dokter
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row">
            @forelse($doctors as $doctor)
                <div class="col-xxl-3 col-lg-4 col-md-6 mb-25">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center">
                            <div class="avatar-wrapper mb-3">
                                @if($doctor->photo)
                                    <img src="{{ asset('storage/' . $doctor->photo) }}" alt="{{ $doctor->name }}"
                                        class="rounded-circle" style="width: 80px; height: 80px; object-fit: cover;">
                                @else
                                    <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center mx-auto"
                                        style="width: 80px; height: 80px;">
                                        <i class="uil uil-user-md text-white" style="font-size: 40px;"></i>
                                    </div>
                                @endif
                            </div>
                            <h5 class="mb-1">{{ $doctor->name }}</h5>
                            <p class="text-muted mb-2">{{ $doctor->specialization }}</p>
                            <span class="badge bg-{{ $doctor->status == 'active' ? 'success' : 'secondary' }}">
                                {{ $doctor->status == 'active' ? 'Aktif' : 'Tidak Aktif' }}
                            </span>

                            <hr>

                            <div class="text-start small">
                                <p class="mb-1"><i class="uil uil-envelope me-2"></i>{{ $doctor->email }}</p>
                                <p class="mb-1"><i class="uil uil-phone me-2"></i>{{ $doctor->phone }}</p>
                                <p class="mb-0"><i class="uil uil-file-medical me-2"></i>{{ $doctor->sip_number }}</p>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent border-0 d-flex justify-content-center gap-2">
                            <a href="{{ route('clinic.doctors.show', [app()->getLocale(), $doctor->id]) }}"
                                class="btn btn-sm btn-outline-info">
                                <i class="uil uil-eye"></i>
                            </a>
                            <a href="{{ route('clinic.doctors.edit', [app()->getLocale(), $doctor->id]) }}"
                                class="btn btn-sm btn-outline-primary">
                                <i class="uil uil-edit"></i>
                            </a>
                            <a href="#" class="btn btn-sm btn-outline-danger"
                                onclick="event.preventDefault(); if(confirm('Yakin hapus?')) document.getElementById('delete-{{ $doctor->id }}').submit();">
                                <i class="uil uil-trash-alt"></i>
                            </a>
                            <form id="delete-{{ $doctor->id }}"
                                action="{{ route('clinic.doctors.destroy', [app()->getLocale(), $doctor->id]) }}" method="POST"
                                class="d-none">
                                @csrf @method('DELETE')
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <i class="uil uil-user-md text-muted" style="font-size: 60px;"></i>
                            <p class="text-muted mt-3">Belum ada data dokter</p>
                            <a href="{{ route('clinic.doctors.create', app()->getLocale()) }}" class="btn btn-primary">Tambah
                                Dokter</a>
                        </div>
                    </div>
                </div>
            @endforelse
        </div>

        <div class="d-flex justify-content-center">
            {{ $doctors->links('pagination::bootstrap-5') }}
        </div>
    </div>
@endsection