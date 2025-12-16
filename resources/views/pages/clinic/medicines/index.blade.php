@section('title', $title)
@section('description', $description)
@extends('layout.app')
@section('content')
    <div class="container-fluid">
        <div class="breadcrumb-main add-contact justify-content-sm-between">
            <div class="d-flex flex-wrap justify-content-center breadcrumb-main__wrapper">
                <h4 class="text-capitalize fw-500 breadcrumb-title">{{ $title }}</h4>
                <div class="action-btn mt-sm-0 mt-15 ms-3">
                    <a href="{{ route('clinic.medicines.create', app()->getLocale()) }}" class="btn px-20 btn-primary"><i
                            class="las la-plus fs-16"></i>Tambah Obat</a>
                </div>
            </div>
            <div class="d-flex gap-2">
                @if($lowStockCount > 0)
                    <a href="{{ route('clinic.medicines.low-stock', app()->getLocale()) }}" class="btn btn-warning"><i
                            class="uil uil-exclamation-triangle me-1"></i>Stok Rendah ({{ $lowStockCount }})</a>
                @endif
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
                                <th>Kode</th>
                                <th>Nama Obat</th>
                                <th>Kategori</th>
                                <th>Stok</th>
                                <th>Harga Jual</th>
                                <th>Kadaluarsa</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($medicines as $medicine)
                                <tr>
                                    <td><code>{{ $medicine->code }}</code></td>
                                    <td><strong>{{ $medicine->name }}</strong></td>
                                    <td>{{ $medicine->category }}</td>
                                    <td>
                                        @if($medicine->stock <= $medicine->min_stock)
                                            <span class="badge bg-danger">{{ $medicine->stock }} {{ $medicine->unit }}</span>
                                        @else
                                            <span class="badge bg-success">{{ $medicine->stock }} {{ $medicine->unit }}</span>
                                        @endif
                                    </td>
                                    <td>Rp {{ number_format($medicine->sell_price, 0, ',', '.') }}</td>
                                    <td>
                                        @if($medicine->expired_date)
                                            @if($medicine->expired_date->isPast())
                                                <span class="text-danger">{{ $medicine->expired_date->format('d/m/Y') }}</span>
                                            @elseif($medicine->expired_date->diffInDays(now()) <= 30)
                                                <span class="text-warning">{{ $medicine->expired_date->format('d/m/Y') }}</span>
                                            @else
                                                {{ $medicine->expired_date->format('d/m/Y') }}
                                            @endif
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td><span
                                            class="badge bg-{{ $medicine->status == 'active' ? 'success' : 'secondary' }}">{{ $medicine->status == 'active' ? 'Aktif' : 'Tidak Aktif' }}</span>
                                    </td>
                                    <td>
                                        <a href="{{ route('clinic.medicines.edit', [app()->getLocale(), $medicine->id]) }}"
                                            class="btn btn-sm btn-outline-primary"><i class="uil uil-edit"></i></a>
                                        <a href="#"
                                            onclick="event.preventDefault(); if(confirm('Hapus?')) document.getElementById('del-{{ $medicine->id }}').submit();"
                                            class="btn btn-sm btn-outline-danger"><i class="uil uil-trash"></i></a>
                                        <form id="del-{{ $medicine->id }}"
                                            action="{{ route('clinic.medicines.destroy', [app()->getLocale(), $medicine->id]) }}"
                                            method="POST" class="d-none">@csrf @method('DELETE')</form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4">Belum ada obat</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $medicines->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
@endsection