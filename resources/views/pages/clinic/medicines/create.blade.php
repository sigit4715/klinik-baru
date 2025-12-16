@section('title', $title)
@section('description', $description)
@extends('layout.app')
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">Tambah Obat Baru</div>
                    <div class="card-body">
                        <form action="{{ route('clinic.medicines.store', app()->getLocale()) }}" method="POST">
                            @csrf
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Kode</label>
                                    <input type="text" class="form-control" value="{{ $code }}" readonly>
                                </div>
                                <div class="col-md-8 mb-3">
                                    <label class="form-label">Nama Obat <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Kategori <span class="text-danger">*</span></label>
                                    <select name="category" class="form-select" required>
                                        <option value="">Pilih...</option>
                                        @foreach($categories as $cat)
                                            <option value="{{ $cat }}" {{ old('category') == $cat ? 'selected' : '' }}>{{ $cat }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Satuan <span class="text-danger">*</span></label>
                                    <select name="unit" class="form-select" required>
                                        <option value="">Pilih...</option>
                                        @foreach($units as $unit)
                                            <option value="{{ $unit }}" {{ old('unit') == $unit ? 'selected' : '' }}>{{ $unit }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Stok <span class="text-danger">*</span></label>
                                    <input type="number" name="stock" class="form-control" value="{{ old('stock', 0) }}"
                                        min="0" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Stok Minimum</label>
                                    <input type="number" name="min_stock" class="form-control"
                                        value="{{ old('min_stock', 10) }}" min="0">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Tanggal Kadaluarsa</label>
                                    <input type="date" name="expired_date" class="form-control"
                                        value="{{ old('expired_date') }}">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Harga Beli (Rp) <span class="text-danger">*</span></label>
                                    <input type="number" name="buy_price" class="form-control"
                                        value="{{ old('buy_price') }}" min="0" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Harga Jual (Rp) <span class="text-danger">*</span></label>
                                    <input type="number" name="sell_price" class="form-control"
                                        value="{{ old('sell_price') }}" min="0" required>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">Simpan</button>
                            <a href="{{ route('clinic.medicines.index', app()->getLocale()) }}"
                                class="btn btn-light">Batal</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection