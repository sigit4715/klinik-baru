@section('title', $title)
@section('description', $description)
@extends('layout.app')

@push('styles')
    <style>
        .queue-calling {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%) !important;
            animation: pulse-row 0.5s ease-in-out infinite;
        }

        @keyframes pulse-row {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.01);
            }
        }

        .btn-call {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            transition: all 0.3s ease;
        }

        .btn-call:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
            color: white;
        }

        .btn-call-next {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border: none;
            color: white;
            font-size: 1.1rem;
            padding: 12px 30px;
        }

        .btn-call-next:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(16, 185, 129, 0.4);
            color: white;
        }

        .queue-number-cell {
            font-size: 1.2rem;
            font-weight: 700;
            color: #667eea;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid">
        <div class="breadcrumb-main">
            <h4 class="breadcrumb-title">{{ $title }}</h4>
            <div class="d-flex gap-2">
                <a href="{{ route('clinic.appointments.display', app()->getLocale()) }}" class="btn btn-outline-primary"
                    target="_blank">
                    <i class="uil uil-tv-retro me-1"></i>Buka Display TV
                </a>
                <button type="button" class="btn btn-call-next" id="btnCallNext">
                    <i class="uil uil-megaphone me-1"></i>Panggil Antrian Berikutnya
                </button>
                <button type="button" class="btn btn-secondary" id="btnRepeat" style="display: none;">
                    <i class="uil uil-redo me-1"></i>Panggil Ulang
                </button>
            </div>
        </div>

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
                                        <tr data-queue="{{ $apt->queue_number }}" data-id="{{ $apt->id }}">
                                            <td class="queue-number-cell">{{ $apt->queue_number }}</td>
                                            <td>
                                                {{ $apt->patient->name }}<br>
                                                <small class="text-muted">{{ $apt->patient->no_rm }}</small>
                                            </td>
                                            <td>{{ $apt->doctor->name }}</td>
                                            <td>{{ Str::limit($apt->complaint, 40) }}</td>
                                            <td>
                                                @if($apt->status == 'waiting')
                                                    <span class="badge bg-warning">Menunggu</span>
                                                @elseif($apt->status == 'in_progress')
                                                    <span class="badge bg-info">Diperiksa</span>
                                                @else
                                                    <span class="badge bg-success">Selesai</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="d-flex gap-1">
                                                    @if($apt->status == 'waiting')
                                                        <button type="button" class="btn btn-sm btn-call btn-call-specific"
                                                            data-id="{{ $apt->id }}" data-queue="{{ $apt->queue_number }}"
                                                            data-patient="{{ $apt->patient->name }}"
                                                            data-doctor="{{ $apt->doctor->name }}">
                                                            <i class="uil uil-megaphone"></i> Panggil
                                                        </button>
                                                        <a href="{{ route('clinic.medical-records.create', [app()->getLocale(), $apt->id]) }}"
                                                            class="btn btn-sm btn-success">
                                                            <i class="uil uil-stethoscope"></i> Periksa
                                                        </a>
                                                    @elseif($apt->status == 'in_progress')
                                                        <button type="button" class="btn btn-sm btn-outline-info btn-speak"
                                                            data-queue="{{ $apt->queue_number }}"
                                                            data-patient="{{ $apt->patient->name }}"
                                                            data-doctor="{{ $apt->doctor->name }}">
                                                            <i class="uil uil-volume-up"></i>
                                                        </button>
                                                        <a href="{{ route('clinic.medical-records.create', [app()->getLocale(), $apt->id]) }}"
                                                            class="btn btn-sm btn-info">
                                                            <i class="uil uil-file-medical"></i> Rekam Medis
                                                        </a>
                                                    @elseif($apt->status == 'completed' && !$apt->payment)
                                                        <a href="{{ route('clinic.payments.create', [app()->getLocale(), $apt->id]) }}"
                                                            class="btn btn-sm btn-warning">
                                                            <i class="uil uil-receipt"></i> Pembayaran
                                                        </a>
                                                    @else
                                                        <a href="{{ route('clinic.appointments.show', [app()->getLocale(), $apt->id]) }}"
                                                            class="btn btn-sm btn-outline-info">
                                                            <i class="uil uil-eye"></i> Detail
                                                        </a>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-5">
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

    <!-- Modal Panggilan -->
    <div class="modal fade" id="queueCallModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 20px; overflow: hidden;">
                <div class="modal-body text-center p-5"
                    style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <i class="uil uil-megaphone text-white mb-3" style="font-size: 4rem;"></i>
                    <h2 class="text-white mb-2">Memanggil Antrian</h2>
                    <div class="modal-queue-number text-white" style="font-size: 4rem; font-weight: 800;"></div>
                    <div class="modal-patient-name text-white" style="font-size: 1.5rem;"></div>
                    <div class="modal-doctor-name text-white-50 mt-2" style="font-size: 1rem;"></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/queue-voice.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Initialize Queue Voice
            QueueVoice.init();

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            const callNextUrl = '{{ route('clinic.appointments.call-next', app()->getLocale()) }}';
            const callSpecificUrl = '{{ route('clinic.appointments.call-specific', [app()->getLocale(), '__ID__']) }}';

            const btnRepeat = document.getElementById('btnRepeat');

            // Call Next button
            document.getElementById('btnCallNext').addEventListener('click', async function () {
                this.disabled = true;
                this.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Memanggil...';

                const success = await QueueVoice.callNext(callNextUrl, csrfToken);

                if (success) {
                    btnRepeat.style.display = 'inline-block';
                }

                setTimeout(() => {
                    this.disabled = false;
                    this.innerHTML = '<i class="uil uil-megaphone me-1"></i>Panggil Antrian Berikutnya';
                }, 3000);
            });

            // Repeat button
            btnRepeat.addEventListener('click', function () {
                QueueVoice.repeatLast();
            });

            // Call specific buttons
            document.querySelectorAll('.btn-call-specific').forEach(btn => {
                btn.addEventListener('click', async function () {
                    const id = this.dataset.id;
                    const queue = this.dataset.queue;
                    const patient = this.dataset.patient;
                    const doctor = this.dataset.doctor;

                    this.disabled = true;
                    this.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

                    const success = await QueueVoice.callSpecific(callSpecificUrl, id, csrfToken);

                    if (success) {
                        btnRepeat.style.display = 'inline-block';
                    }

                    setTimeout(() => {
                        this.disabled = false;
                        this.innerHTML = '<i class="uil uil-megaphone"></i> Panggil';
                    }, 3000);
                });
            });

            // Speak buttons (for already called queues)
            document.querySelectorAll('.btn-speak').forEach(btn => {
                btn.addEventListener('click', function () {
                    const queue = this.dataset.queue;
                    const patient = this.dataset.patient;
                    const doctor = this.dataset.doctor;

                    QueueVoice.speak(queue, patient, doctor);
                    btnRepeat.style.display = 'inline-block';
                });
            });
        });
    </script>
@endpush