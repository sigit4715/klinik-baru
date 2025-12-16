@section('title', $title)
@section('description', $description)
@extends('layout.app')
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header"><i class="uil uil-edit me-2"></i>Edit Dokter</div>
                    <div class="card-body">
                        <form action="{{ route('clinic.doctors.update', [app()->getLocale(), $doctor->id]) }}" method="POST"
                            enctype="multipart/form-data">
                            @csrf @method('PUT')
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Nomor SIP <span class="text-danger">*</span></label>
                                    <input type="text" name="sip_number" class="form-control"
                                        value="{{ old('sip_number', $doctor->sip_number) }}" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control"
                                        value="{{ old('name', $doctor->name) }}" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Spesialisasi <span class="text-danger">*</span></label>
                                    <input type="text" name="specialization" class="form-control"
                                        value="{{ old('specialization', $doctor->specialization) }}" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Status</label>
                                    <select name="status" class="form-select">
                                        <option value="active" {{ $doctor->status == 'active' ? 'selected' : '' }}>Aktif
                                        </option>
                                        <option value="inactive" {{ $doctor->status == 'inactive' ? 'selected' : '' }}>Tidak
                                            Aktif</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" name="email" class="form-control"
                                        value="{{ old('email', $doctor->email) }}" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">No. Telepon <span class="text-danger">*</span></label>
                                    <input type="text" name="phone" class="form-control"
                                        value="{{ old('phone', $doctor->phone) }}" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Alamat</label>
                                <textarea name="address" class="form-control"
                                    rows="2">{{ old('address', $doctor->address) }}</textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Foto</label>
                                @if($doctor->photo)
                                    <div class="mb-2"><img src="{{ asset('storage/' . $doctor->photo) }}" width="100"
                                            class="rounded"></div>
                                @endif
                                <input type="file" name="photo" class="form-control" accept="image/*">
                            </div>
                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary">Simpan</button>
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