@section('title', $title)
@section('description', $description)
@extends('layout.app')
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">Edit Antrian</div>
                    <div class="card-body">
                        <form action="{{ route('clinic.appointments.update', [app()->getLocale(), $appointment->id]) }}"
                            method="POST">
                            @csrf @method('PUT')
                            <div class="mb-3">
                                <label class="form-label">No. Antrian</label>
                                <input type="text" class="form-control" value="{{ $appointment->queue_number }}" readonly>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Pasien <span class="text-danger">*</span></label>
                                    <select name="patient_id" class="form-select" required>
                                        @foreach($patients as $patient)
                                            <option value="{{ $patient->id }}" {{ $appointment->patient_id == $patient->id ? 'selected' : '' }}>{{ $patient->no_rm }} - {{ $patient->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Dokter <span class="text-danger">*</span></label>
                                    <select name="doctor_id" class="form-select" required>
                                        @foreach($doctors as $doctor)
                                            <option value="{{ $doctor->id }}" {{ $appointment->doctor_id == $doctor->id ? 'selected' : '' }}>{{ $doctor->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Tanggal <span class="text-danger">*</span></label>
                                    <input type="date" name="appointment_date" class="form-control"
                                        value="{{ $appointment->appointment_date->format('Y-m-d') }}" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Waktu</label>
                                    <input type="time" name="appointment_time" class="form-control"
                                        value="{{ $appointment->appointment_time }}">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Keluhan <span class="text-danger">*</span></label>
                                <textarea name="complaint" class="form-control" rows="3"
                                    required>{{ $appointment->complaint }}</textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Simpan</button>
                            <a href="{{ route('clinic.appointments.index', app()->getLocale()) }}"
                                class="btn btn-light">Batal</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection