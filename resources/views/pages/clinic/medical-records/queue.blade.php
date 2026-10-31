@section('title', $title)
@section('description', $description)
@extends('layout.app')

@section('content')
    <div class="container-fluid">
        <div class="breadcrumb-main d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
            <h4 class="breadcrumb-title mb-0">{{ $title }}</h4>
            <div class="d-flex gap-2 align-items-center flex-wrap">
                @if(auth()->user()->doctor_id)
                    <div class="btn-group">
                        <a href="{{ route('clinic.medical-records.queue', app()->getLocale()) }}"
                            class="btn btn-sm {{ $showAll ? 'btn-outline-primary' : 'btn-primary' }}">Pasien Saya</a>
                        <a href="{{ route('clinic.medical-records.queue', [app()->getLocale(), 'all' => 1]) }}"
                            class="btn btn-sm {{ $showAll ? 'btn-primary' : 'btn-outline-primary' }}">Semua Dokter</a>
                    </div>
                @endif
                @if($showAll)
                    <form method="GET" action="{{ route('clinic.medical-records.queue', app()->getLocale()) }}" class="d-flex gap-2">
                        @if(auth()->user()->doctor_id)<input type="hidden" name="all" value="1">@endif
                        <select name="doctor_id" id="queueDoctor" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">Semua dokter</option>
                            @foreach($doctors as $doctor)
                                <option value="{{ $doctor->id }}" @selected($doctorFilter == $doctor->id)>{{ $doctor->name }}</option>
                            @endforeach
                        </select>
                    </form>
                @endif
                @can('appointments.call')
                    <button type="button" class="btn btn-success" id="btnCallNext" data-stage="doctor"
                        @if($doctorFilter) data-filter-select="#callDoctorId" data-filter-name="doctor_id" @endif>
                        <i class="uil uil-megaphone me-1"></i>Panggil Berikutnya
                    </button>
                    <input type="hidden" id="callDoctorId" value="{{ $doctorFilter }}">
                @endcan
                <button type="button" class="btn btn-secondary" id="btnRepeat" style="display: none;">
                    <i class="uil uil-redo me-1"></i>Ulangi Suara
                </button>
            </div>
        </div>

        @if($atNurse > 0)
            <div class="alert alert-light border py-2"><i class="uil uil-heart-medical me-1"></i>{{ $atNurse }} pasien masih di tahap perawat.</div>
        @endif

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="bg-light">
                            <tr>
                                <th>No. Antrian</th>
                                <th>Pasien</th>
                                <th>Triage</th>
                                <th>Keluhan</th>
                                <th>Poli / Dokter</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($appointments as $apt)
                                @php $assessment = $apt->nursingAssessment; @endphp
                                <tr data-queue="{{ $apt->queue_number }}">
                                    <td class="fw-bold text-primary">{{ $apt->queue_number }}</td>
                                    <td>{{ $apt->patient?->name }}<br><small class="text-muted">{{ $apt->patient?->no_rm }}</small></td>
                                    <td>
                                        @if($assessment?->triage_level)
                                            <span class="badge bg-{{ $assessment->triageBadge() }}">{{ ucfirst($assessment->triage_level) }}</span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>{{ Str::limit($apt->complaintText() ?? '-', 50) }}</td>
                                    <td>{{ $apt->poli?->name ?? '-' }}<br><small class="text-muted">{{ $apt->doctor?->name ?? '-' }}</small></td>
                                    <td><span class="badge bg-{{ $apt->statusBadge() }}">{{ $apt->statusLabel() }}</span></td>
                                    <td>
                                        <div class="d-flex gap-1 flex-wrap">
                                            @can('appointments.call')
                                                <button type="button" class="btn btn-sm btn-outline-primary btn-call-specific"
                                                    data-id="{{ $apt->id }}" data-stage="doctor">
                                                    <i class="uil uil-megaphone"></i> {{ $apt->status === 'in_progress' ? 'Panggil Ulang' : 'Panggil' }}
                                                </button>
                                            @endcan
                                            <a href="{{ route('clinic.medical-records.create', [app()->getLocale(), $apt->id]) }}"
                                                class="btn btn-sm btn-success"><i class="uil uil-stethoscope"></i> Periksa</a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <i class="uil uil-stethoscope text-muted" style="font-size:60px;"></i>
                                        <p class="mt-3">Belum ada pasien dari perawat</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @include('pages.clinic.appointments._call_modal')
@endsection

@push('scripts')
    @include('pages.clinic.appointments._call_script')
@endpush
