@section('title', $title)
@section('description', $description)
@extends('layout.app')
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="contact-breadcrumb">
                    <div class="breadcrumb-main add-contact justify-content-sm-between">
                        <div class="d-flex flex-wrap justify-content-center breadcrumb-main__wrapper">
                            <div class="d-flex align-items-center add-contact__title justify-content-center me-sm-25">
                                <h4 class="text-capitalize fw-500 breadcrumb-title">{{ $title }}</h4>
                            </div>
                            <div class="action-btn mt-sm-0 mt-15">
                                <a href="{{ route('clinic.patients.create', app()->getLocale()) }}"
                                    class="btn px-20 btn-primary">
                                    <i class="las la-plus fs-16"></i>Tambah Pasien
                                </a>
                            </div>
                        </div>
                        <div class="breadcrumb-main__wrapper">
                            <form action="{{ route('clinic.patients.index', app()->getLocale()) }}" method="GET"
                                class="d-flex align-items-center add-contact__form my-sm-0 my-2">
                                <img src="{{ asset('assets/img/svg/search.svg') }}" alt="search" class="svg">
                                <input class="form-control me-sm-2 border-0 box-shadow-none" type="search" name="search"
                                    placeholder="Cari nama/no.RM..." aria-label="Search" value="{{ request('search') }}">
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="row">
                <div class="col-12">
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                </div>
            </div>
        @endif

        <div class="row">
            <div class="col-lg-12 mb-30">
                <div class="card">
                    <div class="card-header color-dark fw-500">
                        Daftar Pasien
                    </div>
                    <div class="card-body">
                        <div class="userDatatable global-shadow border-light-0 w-100">
                            <div class="table-responsive">
                                <table class="table mb-0 table-borderless">
                                    <thead>
                                        <tr class="userDatatable-header">
                                            <th><span class="userDatatable-title">No. RM</span></th>
                                            <th><span class="userDatatable-title">Nama Pasien</span></th>
                                            <th><span class="userDatatable-title">NIK</span></th>
                                            <th><span class="userDatatable-title">Gender</span></th>
                                            <th><span class="userDatatable-title">Telepon</span></th>
                                            <th><span class="userDatatable-title">Tgl Lahir</span></th>
                                            <th><span class="userDatatable-title float-end">Aksi</span></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($patients as $patient)
                                            <tr>
                                                <td>
                                                    <div class="userDatatable-content">
                                                        <code>{{ $patient->no_rm }}</code>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="userDatatable-inline-title">
                                                        <a href="{{ route('clinic.patients.show', [app()->getLocale(), $patient->id]) }}"
                                                            class="text-dark fw-500">
                                                            <h6>{{ $patient->name }}</h6>
                                                        </a>
                                                        <p class="d-block mb-0 text-muted small">
                                                            {{ Str::limit($patient->address, 30) }}
                                                        </p>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="userDatatable-content">{{ $patient->nik }}</div>
                                                </td>
                                                <td>
                                                    <div class="userDatatable-content">
                                                        @if($patient->gender == 'L')
                                                            <span class="badge bg-info rounded-pill">Laki-laki</span>
                                                        @else
                                                            <span class="badge bg-danger rounded-pill">Perempuan</span>
                                                        @endif
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="userDatatable-content">{{ $patient->phone }}</div>
                                                </td>
                                                <td>
                                                    <div class="userDatatable-content">
                                                        {{ $patient->birth_date->format('d/m/Y') }}
                                                    </div>
                                                </td>
                                                <td>
                                                    <ul class="orderDatatable_actions mb-0 d-flex flex-wrap">
                                                        <li>
                                                            <a href="{{ route('clinic.patients.show', [app()->getLocale(), $patient->id]) }}"
                                                                class="view" title="Lihat">
                                                                <i class="uil uil-eye"></i>
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a href="{{ route('clinic.patients.edit', [app()->getLocale(), $patient->id]) }}"
                                                                class="edit" title="Edit">
                                                                <i class="uil uil-edit"></i>
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a href="#" class="remove" title="Hapus"
                                                                onclick="event.preventDefault(); if(confirm('Yakin ingin menghapus pasien ini?')) { document.getElementById('delete-{{ $patient->id }}').submit(); }">
                                                                <i class="uil uil-trash-alt"></i>
                                                            </a>
                                                            <form style="display:none;" id="delete-{{ $patient->id }}"
                                                                action="{{ route('clinic.patients.destroy', [app()->getLocale(), $patient->id]) }}"
                                                                method="POST">
                                                                @csrf
                                                                @method('DELETE')
                                                            </form>
                                                        </li>
                                                    </ul>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7">
                                                    <p class="text-center py-4 text-muted">Belum ada data pasien</p>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="pagination-container d-flex justify-content-end pt-25">
                            {{ $patients->links('pagination::bootstrap-5') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection