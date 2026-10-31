@section('title', $title)
@section('description', $description)
@extends('layout.app')
@section('content')
    <div class="container-fluid">
        <div class="breadcrumb-main">
            <h4 class="breadcrumb-title">{{ $title }}</h4>
        </div>

        <div class="alert alert-warning">
            <i class="uil uil-exclamation-triangle me-2"></i>
            <strong>Perhatian!</strong> Obat-obat berikut memiliki stok yang kurang dari batas minimum.
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="bg-light">
                            <tr>
                                <th>Kode</th>
                                <th>Nama Obat</th>
                                <th>Stok Saat Ini</th>
                                <th>Stok Minimum</th>
                                <th>Kekurangan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($medicines as $medicine)
                                <tr>
                                    <td><code>{{ $medicine->code }}</code></td>
                                    <td><strong>{{ $medicine->name }}</strong></td>
                                    <td><span class="badge bg-danger">{{ $medicine->stock }} {{ $medicine->unit }}</span></td>
                                    <td>{{ $medicine->min_stock }} {{ $medicine->unit }}</td>
                                    <td class="text-danger fw-bold">{{ $medicine->min_stock - $medicine->stock }}
                                        {{ $medicine->unit }}</td>
                                    <td>
                                        <a href="{{ route('clinic.medicines.stock-card', [app()->getLocale(), $medicine->id]) }}"
                                            class="btn btn-sm btn-primary">
                                            <i class="uil uil-plus me-1"></i>{{ auth()->user()->can('medicines.stock') ? 'Tambah Stok' : 'Kartu Stok' }}
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4">
                                        <i class="uil uil-check-circle text-success" style="font-size: 40px;"></i>
                                        <p class="mt-2 text-success">Semua stok obat dalam kondisi baik!</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="mt-3">
            <a href="{{ route('clinic.medicines.index', app()->getLocale()) }}" class="btn btn-light">
                <i class="uil uil-arrow-left me-1"></i>Kembali ke Daftar Obat
            </a>
        </div>
    </div>
@endsection