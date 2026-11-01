@section('title', $title)
@section('description', $description)
@extends('layout.app')
@section('content')
    <div class="container-fluid">
        <div class="breadcrumb-main d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h4 class="breadcrumb-title mb-0">Supplier</h4>
            <form method="GET" action="{{ route('clinic.suppliers.index', app()->getLocale()) }}">
                <input type="search" name="search" value="{{ $search }}" class="form-control form-control-sm" placeholder="Cari supplier...">
            </form>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        @endif

        <div class="row">
            <div class="col-lg-8 mb-4">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="bg-light"><tr><th>Nama</th><th>Kontak</th><th>Pembelian</th><th>Status</th><th></th></tr></thead>
                                <tbody>
                                    @forelse($suppliers as $item)
                                        <tr class="{{ $supplier->exists && $supplier->id === $item->id ? 'table-active' : '' }}">
                                            <td><strong>{{ $item->name }}</strong>@if($item->address)<br><small class="text-muted">{{ Str::limit($item->address, 60) }}</small>@endif</td>
                                            <td class="small">{{ $item->contact_person }}<br>{{ $item->phone }}</td>
                                            <td class="small">{{ $item->purchases_count }} faktur<br>Rp {{ number_format((float) $item->purchases_sum_total, 0, ',', '.') }}</td>
                                            <td><span class="badge bg-{{ $item->status === 'active' ? 'success' : 'secondary' }}">{{ $item->status === 'active' ? 'Aktif' : 'Tidak Aktif' }}</span></td>
                                            <td class="text-nowrap">
                                                <a href="{{ route('clinic.suppliers.edit', [app()->getLocale(), $item->id]) }}" class="btn btn-sm btn-outline-primary"><i class="uil uil-edit"></i></a>
                                                <form method="POST" action="{{ route('clinic.suppliers.destroy', [app()->getLocale(), $item->id]) }}" class="d-inline" onsubmit="return confirm(@js('Arsipkan supplier ' . $item->name . '?'))">
                                                    @csrf @method('DELETE')
                                                    <button class="btn btn-sm btn-outline-danger"><i class="uil uil-trash"></i></button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="5" class="text-center text-muted py-4">Belum ada supplier.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">{{ $supplier->exists ? 'Edit Supplier' : 'Tambah Supplier' }}</div>
                    <div class="card-body">
                        @if($errors->any())
                            <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
                        @endif
                        <form method="POST" action="{{ $supplier->exists ? route('clinic.suppliers.update', [app()->getLocale(), $supplier->id]) : route('clinic.suppliers.store', app()->getLocale()) }}">
                            @csrf
                            @if($supplier->exists) @method('PUT') @endif
                            <div class="mb-3"><label class="form-label">Nama <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" maxlength="255" required value="{{ old('name', $supplier->name) }}"></div>
                            <div class="mb-3"><label class="form-label">Kontak (sales)</label>
                                <input type="text" name="contact_person" class="form-control" maxlength="255" value="{{ old('contact_person', $supplier->contact_person) }}"></div>
                            <div class="mb-3"><label class="form-label">Telepon</label>
                                <input type="text" name="phone" class="form-control" maxlength="30" value="{{ old('phone', $supplier->phone) }}"></div>
                            <div class="mb-3"><label class="form-label">Alamat</label>
                                <textarea name="address" class="form-control" rows="2">{{ old('address', $supplier->address) }}</textarea></div>
                            <div class="mb-3"><label class="form-label">Catatan</label>
                                <input type="text" name="notes" class="form-control" maxlength="255" value="{{ old('notes', $supplier->notes) }}"></div>
                            <div class="mb-3"><label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="active" @selected(old('status', $supplier->status) === 'active')>Aktif</option>
                                    <option value="inactive" @selected(old('status', $supplier->status) === 'inactive')>Tidak Aktif</option>
                                </select></div>
                            <button class="btn btn-primary"><i class="uil uil-check me-1"></i>Simpan</button>
                            @if($supplier->exists)<a href="{{ route('clinic.suppliers.index', app()->getLocale()) }}" class="btn btn-light">Batal</a>@endif
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
