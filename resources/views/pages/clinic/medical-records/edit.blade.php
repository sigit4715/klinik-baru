@section('title', $title)
@section('description', $description)
@extends('layout.app')

@section('content')
    <div class="container-fluid">
        <div class="breadcrumb-main">
            <h4 class="breadcrumb-title">Edit Rekam Medis <small class="text-muted">{{ $appointment?->queue_number }}</small></h4>
        </div>
        <div class="row">
            <div class="col-lg-4">
                @include('pages.clinic.medical-records._patient_card')
                @include('pages.clinic.medical-records._nurse_card')
                @if($appointment)
                    @include('pages.clinic.support._card', ['appointment' => $appointment])
                @endif
                @include('pages.clinic.medical-records._history_card')
            </div>
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header"><i class="uil uil-edit me-2"></i>Koreksi Rekam Medis
                        <span class="text-muted ms-2">{{ $record->doctor?->name ?? '-' }}, {{ $record->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                    <div class="card-body">
                        @if($record->edited_at)
                            <div class="alert alert-info py-2">Terakhir diedit oleh {{ $record->editor?->name ?? '-' }} pada {{ $record->edited_at->format('d/m/Y H:i') }}.</div>
                        @endif
                        @if($errors->any())
                            <div class="alert alert-danger" role="alert">
                                <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                            </div>
                        @endif
                        <form action="{{ route('clinic.medical-records.update', [app()->getLocale(), $record->id]) }}" method="POST">
                            @csrf @method('PUT')

                            @include('pages.clinic.medical-records._form')

                            <p class="text-muted small">Perubahan jumlah obat otomatis dicatat di Kartu Stok (keluar/retur). Semua perubahan tercatat di Audit Log.</p>
                            <div class="mt-3">
                                <button type="submit" class="btn btn-primary"><i class="uil uil-check me-1"></i>Simpan Perubahan</button>
                                <a href="{{ route('clinic.medical-records.show', [app()->getLocale(), $record->id]) }}" class="btn btn-light">Batal</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
