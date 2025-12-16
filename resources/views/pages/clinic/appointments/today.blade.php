@section('title', $title)
@section('description', $description)
@extends('layout.app')
@section('content')
    <div class="container-fluid">
        <div class="breadcrumb-main"><h4 class="breadcrumb-title">{{ $title }}</h4></div>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="bg-light">
                                    <tr>
                                        <th>No. Antrian</th>
                                        <th>Pasien</th>
                                        <th>Dokter</th>
                                        <th>Keluhan</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($appointments as $apt)
                                    <tr>
                                        <td><strong class="fs-5">{{ $apt->queue_number }}</strong></td>
                                        <td>{{ $apt->patient->name }}<br><small class="text-muted">{{ $apt->patient->no_rm }}</small></td>
                                        <td>{{ $apt->doctor->name }}</td>
                                        <td>{{ Str::limit($apt->complaint, 40) }}</td>
                                        <td>
                                            @if($apt->status == 'waiting')<span class="badge bg-warning">Menunggu</span>
                                            @elseif($apt->status == 'in_progress')<span class="badge bg-info">Diperiksa</span>
                                            @else<span class="badge bg-success">Selesai</span>@endif
                                        </td>
                                        <td>
                                            @if($apt->status == 'waiting')
                                            <a href="{{ route('clinic.medical-records.create', [app()->getLocale(), $apt->id]) }}" class="btn btn-sm btn-success">Mulai Periksa</a>
                                            @elseif($apt->status == 'completed' && !$apt->payment)
                                            <a href="{{ route('clinic.payments.create', [app()->getLocale(), $apt->id]) }}" class="btn btn-sm btn-warning">Pembayaran</a>
                                            @else
                                            <a href="{{ route('clinic.appointments.show', [app()->getLocale(), $apt->id]) }}" class="btn btn-sm btn-outline-info">Detail</a>
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="6" class="text-center py-5"><i class="uil uil-calendar-alt text-muted" style="font-size:60px;"></i><p class="mt-3">Tidak ada antrian hari ini</p></td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
