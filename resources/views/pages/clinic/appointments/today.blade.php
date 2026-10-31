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
        <div class="breadcrumb-main d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
            <h4 class="breadcrumb-title mb-0">{{ $title }}</h4>
            <div class="d-flex gap-2 align-items-center flex-wrap">
                <form action="{{ route('clinic.appointments.today', app()->getLocale()) }}" method="GET"
                    class="d-flex align-items-center bg-white rounded-pill px-3 py-2 shadow-sm"
                    style="border: 1px solid #e5e7eb;">
                    <i class="uil uil-search text-muted me-2"></i>
                    <input type="search" name="search" placeholder="Cari nama/No.RM..." value="{{ request('search') }}"
                        class="border-0" style="outline: none; width: 180px;">
                </form>
                @can('queue.display')
                    <a href="{{ route('clinic.appointments.display', app()->getLocale()) }}" class="btn btn-outline-primary"
                        target="_blank">
                        <i class="uil uil-tv-retro me-1"></i>Buka Display TV
                    </a>
                @endcan
                @can('appointments.call')
                    <select id="callDoctor" class="form-select" style="width: auto;" title="Panggil antrian untuk dokter">
                        <option value="">Semua dokter</option>
                        @foreach($doctors as $doctor)
                            <option value="{{ $doctor->id }}">{{ $doctor->name }}</option>
                        @endforeach
                    </select>
                    <button type="button" class="btn btn-call-next" id="btnCallNext">
                        <i class="uil uil-megaphone me-1"></i>Panggil Antrian Berikutnya
                    </button>
                @endcan
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
                                                <div class="d-flex gap-1 flex-wrap">
                                                    @if($apt->status == 'waiting')
                                                        @can('appointments.call')
                                                            <button type="button" class="btn btn-sm btn-call btn-call-specific"
                                                                data-id="{{ $apt->id }}" data-queue="{{ $apt->queue_number }}"
                                                                data-patient="{{ $apt->patient->name }}"
                                                                data-doctor="{{ $apt->doctor->name }}">
                                                                <i class="uil uil-megaphone"></i> Panggil
                                                            </button>
                                                        @endcan
                                                        @can('medical_records.manage')
                                                            <a href="{{ route('clinic.medical-records.create', [app()->getLocale(), $apt->id]) }}"
                                                                class="btn btn-sm btn-success">
                                                                <i class="uil uil-stethoscope"></i> Periksa
                                                            </a>
                                                        @endcan
                                                    @elseif($apt->status == 'in_progress')
                                                        <button type="button" class="btn btn-sm btn-outline-info btn-speak"
                                                            data-queue="{{ $apt->queue_number }}"
                                                            data-patient="{{ $apt->patient->name }}"
                                                            data-doctor="{{ $apt->doctor->name }}">
                                                            <i class="uil uil-volume-up"></i>
                                                        </button>
                                                        @can('medical_records.manage')
                                                            <a href="{{ route('clinic.medical-records.create', [app()->getLocale(), $apt->id]) }}"
                                                                class="btn btn-sm btn-info">
                                                                <i class="uil uil-file-medical"></i> Rekam Medis
                                                            </a>
                                                        @endcan
                                                    @elseif($apt->status == 'completed' && $apt->needsPayment())
                                                        @can('payments.manage')
                                                            <a href="{{ route('clinic.payments.create', [app()->getLocale(), $apt->id]) }}"
                                                                class="btn btn-sm btn-warning">
                                                                <i class="uil uil-receipt"></i> Pembayaran
                                                            </a>
                                                        @endcan
                                                    @elseif($apt->status == 'completed' && $apt->payment)
                                                        @can('payments.view')
                                                            <a href="{{ route('clinic.payments.receipt', [app()->getLocale(), $apt->payment->id]) }}"
                                                                class="btn btn-sm btn-warning" target="_blank" title="Cetak Struk Pembayaran">
                                                                <i class="uil uil-bill"></i> Invoice
                                                            </a>
                                                        @endcan
                                                    @endif
                                                    {{-- Struk Antrian Button - Available for all statuses --}}
                                                    <a href="{{ route('clinic.appointments.print-struk', [app()->getLocale(), $apt->id]) }}"
                                                        class="btn btn-sm btn-success" target="_blank"
                                                        title="Cetak Struk Antrian">
                                                        <i class="uil uil-receipt"></i> Struk
                                                    </a>
                                                    {{-- Detail Button --}}
                                                    <a href="{{ route('clinic.appointments.show', [app()->getLocale(), $apt->id]) }}"
                                                        class="btn btn-sm btn-outline-info" title="Detail">
                                                        <i class="uil uil-eye"></i> Detail
                                                    </a>
                                                    {{-- Delete Button --}}
                                                    @can('appointments.delete')
                                                        <form
                                                            action="{{ route('clinic.appointments.destroy', [app()->getLocale(), $apt->id]) }}"
                                                            method="POST" class="d-inline delete-form"
                                                            onsubmit="return confirm(@js('Apakah Anda yakin ingin menghapus antrian ' . $apt->queue_number . ' (' . $apt->patient->name . ')?'))">
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
            document.getElementById('btnCallNext')?.addEventListener('click', async function () {
                this.disabled = true;
                this.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Memanggil...';

                const doctorId = document.getElementById('callDoctor').value || null;
                const success = await QueueVoice.callNext(callNextUrl, csrfToken, doctorId);

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