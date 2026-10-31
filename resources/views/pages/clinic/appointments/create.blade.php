@section('title', $title)
@section('description', $description)
@extends('layout.app')

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
                                <small class="text-muted">Nomor final ditetapkan saat disimpan.</small>
                            </div>

                            @include('pages.clinic.appointments._registration_fields', ['appointment' => null])

                            <div class="alert alert-info py-2">
                                <i class="uil uil-info-circle me-1"></i>
                                Setelah disimpan, pasien masuk ke antrian <strong>Perawat</strong>. Keluhan, tanda vital, poli, dan dokter diisi oleh perawat.
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
