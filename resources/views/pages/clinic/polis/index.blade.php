@section('title', $title)
@section('description', $description)
@extends('layout.app')
@section('content')
    <div class="container-fluid">
        <div class="breadcrumb-main">
            <h4 class="text-capitalize fw-500 breadcrumb-title">Master Poli</h4>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button"
                    class="btn-close" data-bs-dismiss="alert"></button></div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button"
                    class="btn-close" data-bs-dismiss="alert"></button></div>
        @endif

        <div class="row">
            <div class="col-lg-8 mb-4">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Kode</th>
                                        <th>Nama Poli</th>
                                        <th>Jenis</th>
                                        <th>Dokter</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($polis as $item)
                                        <tr class="{{ $poli->exists && $poli->id === $item->id ? 'table-active' : '' }}">
                                            <td><code>{{ $item->code }}</code></td>
                                            <td><strong>{{ $item->name }}</strong></td>
                                            <td>{{ $item->typeLabel() }}</td>
                                            <td>{{ $item->doctors_count }}</td>
                                            <td><span class="badge bg-{{ $item->status == 'active' ? 'success' : 'secondary' }}">{{ $item->status == 'active' ? 'Aktif' : 'Tidak Aktif' }}</span></td>
                                            <td class="text-nowrap">
                                                <a href="{{ route('clinic.polis.edit', [app()->getLocale(), $item->id]) }}"
                                                    class="btn btn-sm btn-outline-primary" title="Edit"><i class="uil uil-edit"></i></a>
                                                <form action="{{ route('clinic.polis.destroy', [app()->getLocale(), $item->id]) }}"
                                                    method="POST" class="d-inline"
                                                    onsubmit="return confirm(@js('Pindahkan poli ' . $item->name . ' ke Arsip?'))">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus"><i class="uil uil-trash"></i></button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="6" class="text-center py-4">Belum ada poli</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <p class="text-muted small mb-0">Hubungkan dokter ke poli lewat menu Dokter → Edit. Perawat memilih poli lalu dokter saat pemeriksaan awal.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">{{ $poli->exists ? 'Edit Poli' : 'Tambah Poli' }}</div>
                    <div class="card-body">
                        @if($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                            </div>
                        @endif
                        <form method="POST"
                            action="{{ $poli->exists ? route('clinic.polis.update', [app()->getLocale(), $poli->id]) : route('clinic.polis.store', app()->getLocale()) }}">
                            @csrf
                            @if($poli->exists) @method('PUT') @endif
                            <div class="mb-3">
                                <label class="form-label">Kode <span class="text-danger">*</span></label>
                                <input type="text" name="code" class="form-control" maxlength="20" placeholder="UMUM"
                                    value="{{ old('code', $poli->code) }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Nama Poli <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" maxlength="100" placeholder="Poli Umum"
                                    value="{{ old('name', $poli->name) }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Jenis</label>
                                <select name="type" class="form-select">
                                    @foreach(\App\Models\Poli::TYPES as $value => $label)
                                        <option value="{{ $value }}" @selected(old('type', $poli->type) === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="active" @selected(old('status', $poli->status) === 'active')>Aktif</option>
                                    <option value="inactive" @selected(old('status', $poli->status) === 'inactive')>Tidak Aktif</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary"><i class="uil uil-check me-1"></i>Simpan</button>
                            @if($poli->exists)
                                <a href="{{ route('clinic.polis.index', app()->getLocale()) }}" class="btn btn-light">Batal</a>
                            @endif
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
