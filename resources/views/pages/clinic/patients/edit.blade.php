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
            <div class="col-lg-12">
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="card">
                    <div class="card-header color-dark fw-500">
                        <i class="uil uil-edit me-2"></i>Edit Data Pasien
                    </div>
                    <div class="card-body">
                        <form action="{{ route('clinic.patients.update', [app()->getLocale(), $patient->id]) }}"
                            method="POST">
                            @csrf
                            @method('PUT')

                            <div class="row">
                                <div class="col-md-6 mb-25">
                                    <label class="form-label">No. Rekam Medis</label>
                                    <input type="text" class="form-control" value="{{ $patient->no_rm }}" readonly>
                                </div>
                            </div>

                            <h6 class="mb-20 mt-20">Data Pribadi</h6>
                            <hr>

                            <div class="row">
                                <div class="col-md-6 mb-25">
                                    <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control"
                                        value="{{ old('name', $patient->name) }}" required>
                                </div>
                                <div class="col-md-6 mb-25">
                                    <label class="form-label">NIK <span class="text-danger">*</span></label>
                                    <input type="text" name="nik" class="form-control"
                                        value="{{ old('nik', $patient->nik) }}" maxlength="16" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-3 mb-25">
                                    <label class="form-label">Jenis Kelamin <span class="text-danger">*</span></label>
                                    <select name="gender" class="form-select" required>
                                        <option value="L" {{ old('gender', $patient->gender) == 'L' ? 'selected' : '' }}>
                                            Laki-laki</option>
                                        <option value="P" {{ old('gender', $patient->gender) == 'P' ? 'selected' : '' }}>
                                            Perempuan</option>
                                    </select>
                                </div>
                                <div class="col-md-3 mb-25">
                                    <label class="form-label">Golongan Darah</label>
                                    <select name="blood_type" class="form-select">
                                        <option value="">Pilih...</option>
                                        <option value="A" {{ old('blood_type', $patient->blood_type) == 'A' ? 'selected' : '' }}>A</option>
                                        <option value="B" {{ old('blood_type', $patient->blood_type) == 'B' ? 'selected' : '' }}>B</option>
                                        <option value="AB" {{ old('blood_type', $patient->blood_type) == 'AB' ? 'selected' : '' }}>AB</option>
                                        <option value="O" {{ old('blood_type', $patient->blood_type) == 'O' ? 'selected' : '' }}>O</option>
                                    </select>
                                </div>
                                <div class="col-md-3 mb-25">
                                    <label class="form-label">Tempat Lahir <span class="text-danger">*</span></label>
                                    <input type="text" name="birth_place" class="form-control"
                                        value="{{ old('birth_place', $patient->birth_place) }}" required>
                                </div>
                                <div class="col-md-3 mb-25">
                                    <label class="form-label">Tanggal Lahir <span class="text-danger">*</span></label>
                                    <input type="date" name="birth_date" class="form-control"
                                        value="{{ old('birth_date', $patient->birth_date->format('Y-m-d')) }}" required>
                                </div>
                            </div>

                            <h6 class="mb-20 mt-20">Kontak & Alamat</h6>
                            <hr>

                            <div class="row">
                                <div class="col-md-6 mb-25">
                                    <label class="form-label">No. Telepon/WhatsApp <span
                                            class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="uil uil-phone"></i></span>
                                        <input type="text" name="phone" class="form-control"
                                            value="{{ old('phone', $patient->phone) }}" placeholder="08xxxxxxxxxx" required>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-25">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email" class="form-control"
                                        value="{{ old('email', $patient->email) }}">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12 mb-25">
                                    <label class="form-label">Alamat Lengkap <span class="text-danger">*</span></label>
                                    <textarea name="address" class="form-control" rows="3"
                                        required>{{ old('address', $patient->address) }}</textarea>
                                </div>
                            </div>

                            <h6 class="mb-20 mt-20">Informasi Keluarga (Opsional)</h6>
                            <small class="text-muted">Isi jika diperlukan: Nama suami untuk pasien hamil, Nama orang tua
                                untuk pasien anak-anak</small>
                            <hr>

                            <div class="row">
                                <div class="col-md-6 mb-25">
                                    <label class="form-label">Nama Suami</label>
                                    <input type="text" name="spouse_name" class="form-control"
                                        value="{{ old('spouse_name', $patient->spouse_name) }}"
                                        placeholder="Untuk pasien hamil">
                                </div>
                                <div class="col-md-6 mb-25">
                                    <label class="form-label">Nama Orang Tua</label>
                                    <input type="text" name="parent_name" class="form-control"
                                        value="{{ old('parent_name', $patient->parent_name) }}"
                                        placeholder="Untuk pasien anak-anak">
                                </div>
                            </div>

                            <h6 class="mb-20 mt-20">Informasi Medis</h6>
                            <hr>

                            <div class="row">
                                <div class="col-12 mb-25">
                                    <label class="form-label">Riwayat Alergi</label>
                                    <input type="text" name="allergy" class="form-control"
                                        value="{{ old('allergy', $patient->allergy) }}">
                                </div>
                            </div>

                            <h6 class="mb-20 mt-20">Kontak Darurat</h6>
                            <hr>

                            <div class="row">
                                <div class="col-md-6 mb-25">
                                    <label class="form-label">Nama Kontak Darurat</label>
                                    <input type="text" name="emergency_contact" class="form-control"
                                        value="{{ old('emergency_contact', $patient->emergency_contact) }}"
                                        placeholder="Opsional">
                                </div>
                                <div class="col-md-6 mb-25">
                                    <label class="form-label">No. Telepon/WhatsApp Darurat</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="uil uil-phone"></i></span>
                                        <input type="text" name="emergency_phone" class="form-control"
                                            value="{{ old('emergency_phone', $patient->emergency_phone) }}"
                                            placeholder="Opsional">
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-20">
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary me-2">
                                        <i class="uil uil-check me-1"></i>Simpan Perubahan
                                    </button>
                                    <a href="{{ route('clinic.patients.index', app()->getLocale()) }}"
                                        class="btn btn-light">
                                        <i class="uil uil-times me-1"></i>Batal
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection