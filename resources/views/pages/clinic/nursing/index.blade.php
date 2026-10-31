@section('title', $title)
@section('description', $description)
@extends('layout.app')

@section('content')
    <div class="container-fluid">
        <div class="breadcrumb-main d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
            <h4 class="breadcrumb-title mb-0">{{ $title }}</h4>
            <div class="d-flex gap-2 align-items-center flex-wrap">
                <div class="btn-group">
                    <a href="{{ route('clinic.nursing.index', app()->getLocale()) }}"
                        class="btn btn-sm {{ $mine ? 'btn-outline-primary' : 'btn-primary' }}">Semua Pasien</a>
                    <a href="{{ route('clinic.nursing.index', [app()->getLocale(), 'mine' => 1]) }}"
                        class="btn btn-sm {{ $mine ? 'btn-primary' : 'btn-outline-primary' }}">Pasien Saya</a>
                </div>
                @can('appointments.call')
                    <button type="button" class="btn btn-success" id="btnCallNext" data-stage="nurse"
                        @if($mine) data-filter-select="#mineNurse" data-filter-name="nurse_id" @endif>
                        <i class="uil uil-megaphone me-1"></i>Panggil Berikutnya
                    </button>
                    @if($mine)
                        <input type="hidden" id="mineNurse" value="{{ auth()->id() }}">
                    @endif
                @endcan
                <button type="button" class="btn btn-secondary" id="btnRepeat" style="display: none;">
                    <i class="uil uil-redo me-1"></i>Ulangi Suara
                </button>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button"
                    class="btn-close" data-bs-dismiss="alert"></button></div>
        @endif

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="bg-light">
                            <tr>
                                <th>No. Antrian</th>
                                <th>Pasien</th>
                                <th>Perawat</th>
                                <th>Status</th>
                                <th>Tujuan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($appointments as $apt)
                                @php $assessment = $apt->nursingAssessment; @endphp
                                <tr data-queue="{{ $apt->queue_number }}">
                                    <td class="fw-bold text-primary">
                                        {{ $apt->queue_number }}
                                        @if($apt->appointment_time)
                                            <br><small class="text-muted fw-normal">Daftar {{ $apt->appointment_time->format('H:i') }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $apt->patient?->name }}<br>
                                        <small class="text-muted">{{ $apt->patient?->no_rm }} | NIK {{ $apt->patient?->nik ?: '-' }}</small>
                                    </td>
                                    <td>{{ $apt->nurse?->name ?? '-' }}</td>
                                    <td>
                                        <span class="badge bg-{{ $apt->statusBadge() }}">{{ $apt->statusLabel() }}</span>
                                        @if($assessment?->triage_level)
                                            <span class="badge bg-{{ $assessment->triageBadge() }}">{{ ucfirst($assessment->triage_level) }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($apt->status === 'ready')
                                            {{ $apt->poli?->name }}<br><small class="text-muted">{{ $apt->doctor?->name ?? '-' }}</small>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1 flex-wrap">
                                            @if($apt->isAtNurseStage())
                                                @can('appointments.call')
                                                    <button type="button" class="btn btn-sm btn-outline-primary btn-call-specific"
                                                        data-id="{{ $apt->id }}" data-stage="nurse">
                                                        <i class="uil uil-megaphone"></i> {{ $apt->status === 'screening' ? 'Panggil Ulang' : 'Panggil' }}
                                                    </button>
                                                @endcan
                                            @endif
                                            <a href="{{ route('clinic.nursing.edit', [app()->getLocale(), $apt->id]) }}"
                                                class="btn btn-sm {{ $apt->status === 'ready' ? 'btn-outline-secondary' : 'btn-primary' }}">
                                                <i class="uil uil-heart-medical"></i> {{ $apt->status === 'ready' ? 'Ubah Data' : 'Periksa' }}
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <i class="uil uil-heart-medical text-muted" style="font-size:60px;"></i>
                                        <p class="mt-3">Tidak ada pasien yang menunggu perawat</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <p class="text-muted small mb-0">Pasien berstatus <strong>Menunggu Dokter</strong> masih bisa diubah datanya sampai dokter menyimpan rekam medis.</p>
            </div>
        </div>
    </div>

    @include('pages.clinic.appointments._call_modal')
@endsection

@push('scripts')
    @include('pages.clinic.appointments._call_script')
@endpush
