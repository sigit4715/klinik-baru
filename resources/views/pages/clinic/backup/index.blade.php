@section('title', $title)
@section('description', $description)
@extends('layout.app')
@section('content')
    <div class="container-fluid">
        <div class="breadcrumb-main d-flex justify-content-between align-items-center mb-4">
            <h4 class="breadcrumb-title mb-0">{{ $title }}</h4>
            <form action="{{ route('clinic.backup.create', app()->getLocale()) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-success"
                    onclick="return confirm('Yakin ingin membuat backup database sekarang?')">
                    <i class="uil uil-database me-1"></i>Buat Backup Sekarang
                </button>
            </form>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="uil uil-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="uil uil-history me-2"></i>Daftar Backup</h6>
            </div>
            <div class="card-body">
                @if(count($backups) > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="bg-light">
                                <tr>
                                    <th>Nama File</th>
                                    <th>Ukuran</th>
                                    <th>Tanggal</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($backups as $backup)
                                    <tr>
                                        <td>
                                            <i class="uil uil-file text-primary me-2"></i>
                                            {{ $backup['name'] }}
                                        </td>
                                        <td>{{ $backup['size'] }}</td>
                                        <td>
                                            @php
                                                $date = \Carbon\Carbon::parse($backup['date']);
                                                $days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                                                $months = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                                            @endphp
                                            {{ $days[$date->dayOfWeek] }}, {{ $date->day }} {{ $months[$date->month] }}
                                            {{ $date->year }} {{ $date->format('H:i') }}
                                        </td>
                                        <td>
                                            <a href="{{ route('clinic.backup.download', [app()->getLocale(), $backup['name']]) }}"
                                                class="btn btn-sm btn-primary" title="Download">
                                                <i class="uil uil-download-alt"></i> Download
                                            </a>
                                            <form
                                                action="{{ route('clinic.backup.destroy', [app()->getLocale(), $backup['name']]) }}"
                                                method="POST" class="d-inline"
                                                onsubmit="return confirm('Yakin ingin menghapus backup ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus">
                                                    <i class="uil uil-trash-alt"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="uil uil-database text-muted" style="font-size: 60px;"></i>
                        <p class="mt-3 text-muted">Belum ada backup database</p>
                        <p class="text-muted small">Klik tombol "Buat Backup Sekarang" untuk membuat backup pertama</p>
                    </div>
                @endif
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header bg-warning text-dark">
                <h6 class="mb-0"><i class="uil uil-exclamation-triangle me-2"></i>Penting!</h6>
            </div>
            <div class="card-body">
                <ul class="mb-0">
                    <li>Backup database secara rutin untuk menghindari kehilangan data.</li>
                    <li>Simpan file backup di lokasi yang aman (cloud storage, external drive, dll).</li>
                    <li>Backup disimpan di folder <code>storage/app/backups/</code></li>
                </ul>
            </div>
        </div>
    </div>
@endsection