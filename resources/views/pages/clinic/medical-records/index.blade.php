@section('title', $title)
@section('description', $description)
@extends('layout.app')
@section('content')
    <div class="container-fluid">
        <div class="breadcrumb-main">
            <h4 class="breadcrumb-title">{{ $title }}</h4>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="bg-light">
                            <tr>
                                <th>Tanggal</th>
                                <th>Pasien</th>
                                <th>Dokter</th>
                                <th>Diagnosis</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($records as $record)
                                <tr>
                                    <td>{{ $record->created_at->format('d/m/Y') }}</td>
                                    <td><a
                                            href="{{ route('clinic.patients.show', [app()->getLocale(), $record->patient->id]) }}">{{ $record->patient->name }}</a>
                                    </td>
                                    <td>{{ $record->doctor->name }}</td>
                                    <td>{{ Str::limit($record->diagnosis, 50) }}</td>
                                    <td>
                                        <a href="{{ route('clinic.medical-records.show', [app()->getLocale(), $record->id]) }}"
                                            class="btn btn-sm btn-outline-info"><i class="uil uil-eye"></i></a>
                                        <a href="{{ route('clinic.medical-records.print', [app()->getLocale(), $record->id]) }}"
                                            class="btn btn-sm btn-outline-secondary" target="_blank"><i
                                                class="uil uil-print"></i></a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4">Belum ada rekam medis</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $records->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
@endsection