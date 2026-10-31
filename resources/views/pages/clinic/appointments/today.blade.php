@section('title', $title)
@section('description', $description)
@extends('layout.app')

@push('css')
    <style>
        .queue-calling {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%) !important;
        }

        .btn-call {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
        }

        .btn-call:hover { color: white; opacity: .9; }

        .btn-call-next {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border: none;
            color: white;
        }

        .btn-call-next:hover { color: white; opacity: .9; }

        .queue-number-cell {
            font-size: 1.1rem;
            font-weight: 700;
            color: #667eea;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid">
        <div class="breadcrumb-main d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
            <h4 class="breadcrumb-title mb-0">{{ $title }}</h4>
            <div class="d-flex gap-2 align-items-center flex-wrap">
                <form action="{{ route('clinic.appointments.today', app()->getLocale()) }}" method="GET"
                    class="d-flex align-items-center bg-white rounded-pill px-3 py-2 shadow-sm"
                    style="border: 1px solid #e5e7eb;">
                    <i class="uil uil-search text-muted me-2"></i>
                    <input type="search" name="search" placeholder="Cari nama/NIK/No.RM..." value="{{ request('search') }}"
                        class="border-0" style="outline: none; width: 200px;">
                </form>
                @can('appointments.manage')
                    <a href="{{ route('clinic.appointments.create', app()->getLocale()) }}" class="btn btn-primary">
                        <i class="uil uil-plus me-1"></i>Daftar Pasien
                    </a>
                @endcan
                @can('queue.display')
                    <a href="{{ route('clinic.appointments.display', app()->getLocale()) }}" class="btn btn-outline-primary"
                        target="_blank">
                        <i class="uil uil-tv-retro me-1"></i>Display TV
                    </a>
                @endcan
                @can('appointments.call')
                    <button type="button" class="btn btn-call-next" id="btnCallNext" data-stage="nurse">
                        <i class="uil uil-megaphone me-1"></i>Panggil Berikutnya ke Perawat
                    </button>
                @endcan
                <button type="button" class="btn btn-secondary" id="btnRepeat" style="display: none;">
                    <i class="uil uil-redo me-1"></i>Ulangi Suara
                </button>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('success') }}
                @if(session('print_appointment_id'))
                    <a href="{{ route('clinic.appointments.print-struk', [app()->getLocale(), session('print_appointment_id')]) }}"
                        target="_blank" class="btn btn-sm btn-success ms-2"><i class="uil uil-print me-1"></i>Cetak Struk Antrian</a>
                @endif
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="bg-light">
                                    <tr>
                                        <th>No. Antrian</th>
                                        <th>Pasien</th>
                                        <th>Perawat</th>
                                        <th>Poli / Dokter</th>
                                        <th>Keluhan</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($appointments as $apt)
                                        @php $triage = $apt->nursingAssessment; @endphp
                                        <tr data-queue="{{ $apt->queue_number }}" data-id="{{ $apt->id }}">
                                            <td class="queue-number-cell">
                                                {{ $apt->queue_number }}
                                                @if($apt->appointment_time)
                                                    <br><small class="text-muted fw-normal">{{ $apt->appointment_time->format('H:i') }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                {{ $apt->patient?->name }}<br>
                                                <small class="text-muted">{{ $apt->patient?->no_rm }}</small>
                                            </td>
                                            <td>{{ $apt->nurse?->name ?? '-' }}</td>
                                            <td>
                                                {{ $apt->poli?->name ?? '-' }}<br>
                                                <small class="text-muted">{{ $apt->doctor?->name ?? '' }}</small>
                                            </td>
                                            <td>{{ Str::limit($apt->complaintText() ?? '-', 40) }}</td>
                                            <td>
                                                <span class="badge bg-{{ $apt->statusBadge() }}">{{ $apt->statusLabel() }}</span>
                                                @if($triage?->triage_level)
                                                    <span class="badge bg-{{ $triage->triageBadge() }}" title="{{ $triage->triageLabel() }}">{{ ucfirst($triage->triage_level) }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="d-flex gap-1 flex-wrap">
                                                    @if($apt->isAtNurseStage())
                                                        @can('appointments.call')
                                                            <button type="button" class="btn btn-sm btn-call btn-call-specific"
                                                                data-id="{{ $apt->id }}" data-stage="nurse">
                                                                <i class="uil uil-megaphone"></i> {{ $apt->status === 'screening' ? 'Panggil Ulang' : 'Panggil' }}
                                                            </button>
                                                        @endcan
                                                        @can('nursing.manage')
                                                            <a href="{{ route('clinic.nursing.edit', [app()->getLocale(), $apt->id]) }}"
                                                                class="btn btn-sm btn-primary">
                                                                <i class="uil uil-heart-medical"></i> Perawat
                                                            </a>
                                                        @endcan
                                                    @elseif(in_array($apt->status, ['ready', 'in_progress'], true))
                                                        @can('appointments.call')
                                                            <button type="button" class="btn btn-sm btn-call btn-call-specific"
                                                                data-id="{{ $apt->id }}" data-stage="doctor">
                                                                <i class="uil uil-megaphone"></i> {{ $apt->status === 'in_progress' ? 'Panggil Ulang' : 'Panggil ke Dokter' }}
                                                            </button>
                                                        @endcan
                                                        @can('medical_records.manage')
                                                            <a href="{{ route('clinic.medical-records.create', [app()->getLocale(), $apt->id]) }}"
                                                                class="btn btn-sm btn-success">
                                                                <i class="uil uil-stethoscope"></i> Periksa
                                                            </a>
                                                        @endcan
                                                    @elseif($apt->status === 'completed' && $apt->needsPayment())
                                                        @can('payments.manage')
                                                            <a href="{{ route('clinic.payments.create', [app()->getLocale(), $apt->id]) }}"
                                                                class="btn btn-sm btn-warning">
                                                                <i class="uil uil-receipt"></i> Pembayaran
                                                            </a>
                                                        @endcan
                                                    @elseif($apt->status === 'completed' && $apt->payment)
                                                        @can('payments.view')
                                                            <a href="{{ route('clinic.payments.receipt', [app()->getLocale(), $apt->payment->id]) }}"
                                                                class="btn btn-sm btn-warning" target="_blank" title="Cetak Struk Pembayaran">
                                                                <i class="uil uil-bill"></i> Struk Bayar
                                                            </a>
                                                        @endcan
                                                    @endif
                                                    <a href="{{ route('clinic.appointments.print-struk', [app()->getLocale(), $apt->id]) }}"
                                                        class="btn btn-sm btn-outline-success" target="_blank"
                                                        title="Cetak Struk Antrian">
                                                        <i class="uil uil-receipt"></i>
                                                    </a>
                                                    <a href="{{ route('clinic.appointments.show', [app()->getLocale(), $apt->id]) }}"
                                                        class="btn btn-sm btn-outline-info" title="Detail">
                                                        <i class="uil uil-eye"></i>
                                                    </a>
                                                    @can('appointments.delete')
                                                        <form
                                                            action="{{ route('clinic.appointments.destroy', [app()->getLocale(), $apt->id]) }}"
                                                            method="POST" class="d-inline delete-form"
                                                            onsubmit="return confirm(@js('Apakah Anda yakin ingin menghapus antrian ' . $apt->queue_number . ' (' . $apt->patient?->name . ')?'))">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-outline-danger"
                                                                title="Hapus">
                                                                <i class="uil uil-trash-alt"></i>
                                                            </button>
                                                        </form>
                                                    @endcan
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-5">
                                                <i class="uil uil-calendar-alt text-muted" style="font-size:60px;"></i>
                                                <p class="mt-3">Tidak ada antrian hari ini</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('pages.clinic.appointments._call_modal')
@endsection

@push('scripts')
    @include('pages.clinic.appointments._call_script')
@endpush
