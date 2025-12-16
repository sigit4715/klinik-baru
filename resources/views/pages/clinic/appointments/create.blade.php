@section('title', $title)
@section('description', $description)
@extends('layout.app')

@push('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<style>
    .select2-container--bootstrap-5 .select2-selection { min-height: 44px; padding: 6px 12px; }
    .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered { line-height: 30px; }
</style>
@endpush

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header"><i class="uil uil-calendar-alt me-2"></i>Buat Antrian Baru</div>
                    <div class="card-body">
                        @if($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                            </div>
                        @endif
                        <form action="{{ route('clinic.appointments.store', app()->getLocale()) }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">No. Antrian</label>
                                <input type="text" class="form-control" value="{{ $queue_number }}" readonly>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Pasien <span class="text-danger">*</span></label>
                                    <select name="patient_id" id="patient_id" class="form-select select2-patient" required>
                                        <option value="">Pilih Pasien...</option>
                                        @foreach($patients as $patient)
                                            <option value="{{ $patient->id }}" {{ old('patient_id', request('patient_id')) == $patient->id ? 'selected' : '' }}>
                                                {{ $patient->no_rm }} - {{ $patient->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Dokter <span class="text-danger">*</span></label>
                                    <select name="doctor_id" id="doctor_id" class="form-select select2-doctor" required>
                                        <option value="">Pilih Dokter...</option>
                                        @foreach($doctors as $doctor)
                                            <option value="{{ $doctor->id }}" {{ old('doctor_id') == $doctor->id ? 'selected' : '' }}>
                                                {{ $doctor->name }} ({{ $doctor->specialization }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Tanggal <span class="text-danger">*</span></label>
                                    <input type="date" name="appointment_date" class="form-control"
                                        value="{{ old('appointment_date', date('Y-m-d')) }}" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Waktu</label>
                                    <input type="time" name="appointment_time" class="form-control"
                                        value="{{ old('appointment_time') }}">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Keluhan <span class="text-danger">*</span></label>
                                <textarea name="complaint" class="form-control" rows="3"
                                    required>{{ old('complaint') }}</textarea>
                            </div>
                            <button type="submit" class="btn btn-primary"><i class="uil uil-check me-1"></i>Simpan</button>
                            <a href="{{ route('clinic.appointments.index', app()->getLocale()) }}"
                                class="btn btn-light">Batal</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    $('.select2-patient').select2({
        theme: 'bootstrap-5',
        placeholder: 'Cari pasien...',
        allowClear: true,
        width: '100%'
    });
    
    $('.select2-doctor').select2({
        theme: 'bootstrap-5',
        placeholder: 'Pilih dokter...',
        allowClear: true,
        width: '100%'
    });
});
</script>
@endpush