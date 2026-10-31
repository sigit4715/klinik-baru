@section('title', $title)
@section('description', $description)
@extends('layout.app')

@section('content')
    <div class="container-fluid">
        <div class="breadcrumb-main">
            <h4 class="breadcrumb-title">Pemeriksaan Dokter <small class="text-muted">{{ $appointment->queue_number }}</small></h4>
        </div>
        <div class="row">
            <div class="col-lg-4">
                @include('pages.clinic.medical-records._patient_card')
                @include('pages.clinic.medical-records._nurse_card')
                @include('pages.clinic.medical-records._history_card')
            </div>
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header"><i class="uil uil-stethoscope me-2"></i>Form Pemeriksaan
                        @if($appointment->doctor)
                            <span class="text-muted ms-2">{{ $appointment->poli?->name }} - {{ $appointment->doctor?->name ?? '-' }}</span>
                        @endif
                    </div>
                    <div class="card-body">
                        @if($errors->any())
                            <div class="alert alert-danger" role="alert">
                                <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                            </div>
                        @endif
                        <form action="{{ route('clinic.medical-records.store', app()->getLocale()) }}" method="POST">
                            @csrf
                            <input type="hidden" name="appointment_id" value="{{ $appointment->id }}">

                            @include('pages.clinic.medical-records._form', ['record' => null])

                            <div class="mt-4">
                                <button type="submit" class="btn btn-success"><i class="uil uil-check me-1"></i>Simpan Rekam Medis</button>
                                <a href="{{ route('clinic.medical-records.queue', app()->getLocale()) }}" class="btn btn-light">Batal</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
