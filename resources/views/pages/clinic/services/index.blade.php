@section('title', $title)
@section('description', $description)
@extends('layout.app')
@section('content')
    <div class="container-fluid">
        <div class="breadcrumb-main add-contact justify-content-sm-between">
            <div class="d-flex flex-wrap justify-content-center breadcrumb-main__wrapper">
                <h4 class="text-capitalize fw-500 breadcrumb-title">{{ $title }}</h4>
                @can('services.manage')
                    <div class="action-btn mt-sm-0 mt-15 ms-3">
                        <a href="{{ route('clinic.services.create', app()->getLocale()) }}" class="btn px-20 btn-primary">
                            <i class="las la-plus fs-16"></i>Tambah Layanan
                        </a>
                    </div>
                @endcan
            </div>
            <form action="{{ route('clinic.services.index', app()->getLocale()) }}" method="GET"
                class="d-flex align-items-center bg-white rounded-pill px-3 py-2 shadow-sm mt-sm-0 mt-15"
                style="border: 1px solid #e5e7eb;">
                <i class="uil uil-search text-muted me-2"></i>
                <input type="search" name="search" placeholder="Cari layanan..." value="{{ request('search') }}"
                    class="border-0" style="outline: none; width: 180px;">
            </form>
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
                                <th>Kode</th>
                                <th>Nama Layanan</th>
                                <th>Kategori</th>
                                <th>Harga</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($services as $service)
                                <tr>
                                    <td><code>{{ $service->code }}</code></td>
                                    <td><strong>{{ $service->name }}</strong></td>
                                    <td>{{ $service->categoryLabel() }}@if($service->poli)<br><small class="text-muted">{{ $service->poli->name }}</small>@endif</td>
                                    <td>Rp {{ number_format($service->price, 0, ',', '.') }}</td>
                                    <td><span
                                            class="badge bg-{{ $service->status == 'active' ? 'success' : 'secondary' }}">{{ $service->status == 'active' ? 'Aktif' : 'Tidak Aktif' }}</span>
                                    </td>
                                    <td>
                                        @can('services.manage')
                                            <a href="{{ route('clinic.services.edit', [app()->getLocale(), $service->id]) }}"
                                                class="btn btn-sm btn-outline-primary"><i class="uil uil-edit"></i></a>
                                        @endcan
                                        @can('services.delete')
                                            <a href="#"
                                                onclick="event.preventDefault(); if(confirm('Hapus?')) document.getElementById('del-{{ $service->id }}').submit();"
                                                class="btn btn-sm btn-outline-danger"><i class="uil uil-trash"></i></a>
                                            <form id="del-{{ $service->id }}"
                                                action="{{ route('clinic.services.destroy', [app()->getLocale(), $service->id]) }}"
                                                method="POST" class="d-none">@csrf @method('DELETE')</form>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4">Belum ada layanan</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $services->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
@endsection