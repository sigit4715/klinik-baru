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
                        @if($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                            </div>
                        @endif
                        <form action="{{ route('clinic.appointments.update', [app()->getLocale(), $appointment->id]) }}"
                            method="POST">
                            @csrf @method('PUT')
                            <div class="mb-3">
                                <label class="form-label">No. Antrian</label>
                                <input type="text" class="form-control" value="{{ $appointment->queue_number }}" readonly>
                            </div>

                            @include('pages.clinic.appointments._registration_fields')

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
