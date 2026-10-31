@section('title', $title)
@section('description', $description)
@extends('layout.app')
@section('content')
    <div class="container-fluid">
        <div class="breadcrumb-main">
            <h4 class="text-capitalize breadcrumb-title">{{ $title }}</h4>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="uil uil-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="alert alert-info">
            <i class="uil uil-info-circle me-1"></i>
            Data yang dihapus tidak benar-benar hilang. Riwayat kunjungan, rekam medis, dan pembayaran tetap tersimpan.
            Klik <strong>Pulihkan</strong> untuk mengembalikan data ke daftar.
        </div>

        <ul class="nav nav-tabs mb-3">
            @foreach($types as $key => $label)
                <li class="nav-item">
                    <a class="nav-link {{ $type === $key ? 'active' : '' }}"
                        href="{{ route('clinic.archive.index', [app()->getLocale(), 'type' => $key]) }}">
                        {{ $label }} <span class="badge bg-secondary ms-1">{{ $counts[$key] }}</span>
                    </a>
                </li>
            @endforeach
        </ul>

        <div class="card">
            <div class="card-body">
                <form method="GET" class="d-flex gap-2 mb-3">
                    <input type="hidden" name="type" value="{{ $type }}">
                    <input type="search" name="search" class="form-control" style="max-width: 320px;"
                        placeholder="Cari..." value="{{ $search }}">
                    <button class="btn btn-primary btn-sm" type="submit"><i class="uil uil-search"></i></button>
                </form>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="bg-light">
                            <tr>
                                @switch($type)
                                    @case('patients')
                                        <th>No. RM</th><th>Nama</th><th>NIK</th>
                                        @break
                                    @case('doctors')
                                        <th>SIP</th><th>Nama</th><th>Spesialisasi</th>
                                        @break
                                    @case('appointments')
                                        <th>No. Antrian</th><th>Pasien</th><th>Dokter</th>
                                        @break
                                    @case('polis')
                                        <th>Kode</th><th>Nama</th><th>Jenis</th>
                                        @break
                                    @default
                                        <th>Kode</th><th>Nama</th><th>{{ $type === 'medicines' ? 'Stok' : 'Harga' }}</th>
                                @endswitch
                                <th>Dihapus pada</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($items as $item)
                                <tr>
                                    @switch($type)
                                        @case('patients')
                                            <td><code>{{ $item->no_rm }}</code></td><td>{{ $item->name }}</td><td>{{ $item->nik }}</td>
                                            @break
                                        @case('doctors')
                                            <td>{{ $item->sip_number }}</td><td>{{ $item->name }}</td><td>{{ $item->specialization }}</td>
                                            @break
                                        @case('appointments')
                                            <td><code>{{ $item->queue_number }}</code></td><td>{{ $item->patient?->name }}</td><td>{{ $item->doctor?->name ?? '-' }}</td>
                                            @break
                                        @case('medicines')
                                            <td><code>{{ $item->code }}</code></td><td>{{ $item->name }}</td><td>{{ $item->stock }} {{ $item->unit }}</td>
                                            @break
                                        @case('polis')
                                            <td><code>{{ $item->code }}</code></td><td>{{ $item->name }}</td><td>{{ $item->typeLabel() }}</td>
                                            @break
                                        @default
                                            <td><code>{{ $item->code }}</code></td><td>{{ $item->name }}</td><td>Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                                    @endswitch
                                    <td>{{ $item->deleted_at->format('d/m/Y H:i') }}</td>
                                    <td class="text-end">
                                        <form method="POST" class="d-inline"
                                            action="{{ route('clinic.archive.restore', [app()->getLocale(), $type, $item->id]) }}"
                                            onsubmit="return confirm('Pulihkan data ini?')">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-success">
                                                <i class="uil uil-history me-1"></i>Pulihkan
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">Arsip {{ strtolower($types[$type]) }} kosong</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $items->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
@endsection
