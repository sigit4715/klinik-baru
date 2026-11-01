@section('title', $title)
@section('description', $description)
@extends('layout.app')
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">Tambah Layanan Baru</div>
                    <div class="card-body">
                        <form action="{{ route('clinic.services.store', app()->getLocale()) }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">Kode Layanan</label>
                                <input type="text" class="form-control" value="{{ $code }}" readonly>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Nama Layanan <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                            </div>
                            @include('pages.clinic.services._category_fields', ['service' => $service ?? null])
                            <div class="mb-3">
                                <label class="form-label">Deskripsi</label>
                                <textarea name="description" class="form-control"
                                    rows="2">{{ old('description') }}</textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Harga (Rp) <span class="text-danger">*</span></label>
                                <input type="number" name="price" class="form-control" value="{{ old('price') }}" min="0"
                                    required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="active">Aktif</option>
                                    <option value="inactive">Tidak Aktif</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary">Simpan</button>
                            <a href="{{ route('clinic.services.index', app()->getLocale()) }}"
                                class="btn btn-light">Batal</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection