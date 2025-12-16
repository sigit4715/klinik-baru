@section('title', $title)
@section('description', $description)
@extends('layout.app')
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="breadcrumb-main">
                    <h4 class="text-capitalize breadcrumb-title">{{ $title }}</h4>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                    </div>
                @endif

                <div class="card">
                    <div class="card-header"><i class="uil uil-user-md me-2"></i>Form Tambah Dokter</div>
                    <div class="card-body">
                        <form action="{{ route('clinic.doctors.store', app()->getLocale()) }}" method="POST"
                            enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Nomor SIP <span class="text-danger">*</span></label>
                                    <input type="text" name="sip_number" class="form-control"
                                        value="{{ old('sip_number') }}" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Spesialisasi <span class="text-danger">*</span></label>
                                    <input type="text" name="specialization" class="form-control"
                                        value="{{ old('specialization') }}" placeholder="Contoh: Umum, Gigi, Anak" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Status</label>
                                    <select name="status" class="form-select">
                                        <option value="active">Aktif</option>
                                        <option value="inactive">Tidak Aktif</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" name="email" class="form-control" value="{{ old('email') }}"
                                        required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">No. Telepon <span class="text-danger">*</span></label>
                                    <input type="text" name="phone" class="form-control" value="{{ old('phone') }}"
                                        required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Alamat</label>
                                <textarea name="address" class="form-control" rows="2">{{ old('address') }}</textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Foto</label>
                                <input type="file" name="photo" class="form-control" accept="image/*">
                            </div>
                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary"><i
                                        class="uil uil-check me-1"></i>Simpan</button>
                                <a href="{{ route('clinic.doctors.index', app()->getLocale()) }}"
                                    class="btn btn-light">Batal</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection