@section('title', $title)
@section('description', $description)
@extends('layout.app')

@section('content')
    <div class="container-fluid">
        <div class="breadcrumb-main">
            <h4 class="breadcrumb-title">Pemeriksaan Perawat <small class="text-muted">{{ $appointment->queue_number }}</small></h4>
        </div>
        <div class="row">
            <div class="col-lg-4">
                @include('pages.clinic.medical-records._patient_card', ['patient' => $appointment->patient])

                @include('pages.clinic.support._card', ['appointment' => $appointment])

                @if($lastRecord)
                    <div class="card mb-3">
                        <div class="card-header"><i class="uil uil-history me-1"></i>Kunjungan Terakhir ({{ $lastRecord->created_at->format('d/m/Y') }})</div>
                        <div class="card-body small">
                            <p class="mb-1"><strong>Diagnosis:</strong> {{ Str::limit($lastRecord->diagnosis, 80) }}</p>
                            <p class="mb-0">TD {{ $lastRecord->blood_pressure ?? '-' }} | Suhu {{ $lastRecord->body_temperature ?? '-' }} |
                                BB {{ $lastRecord->weight ?? '-' }} kg | TB {{ $lastRecord->height ?? '-' }} cm</p>
                        </div>
                    </div>
                @endif

                @if($assessment->exists)
                    <div class="alert alert-info small">
                        Diisi oleh {{ $assessment->nurse?->name ?? '-' }} pada {{ $assessment->created_at->format('d/m/Y H:i') }}.
                        Perubahan tercatat di Audit Log.
                    </div>
                @endif
            </div>
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header"><i class="uil uil-heart-medical me-2"></i>Form Perawat</div>
                    <div class="card-body">
                        @if($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                            </div>
                        @endif
                        <form action="{{ route('clinic.nursing.update', [app()->getLocale(), $appointment->id]) }}" method="POST">
                            @csrf @method('PUT')

                            <div class="mb-3">
                                <label class="form-label">Keluhan Utama <span class="text-danger">*</span></label>
                                <textarea name="chief_complaint" class="form-control" rows="2" required>{{ old('chief_complaint', $assessment->chief_complaint ?? $appointment->complaint) }}</textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Anamnesa (Riwayat Penyakit Sekarang / Dahulu)</label>
                                <textarea name="anamnesa" class="form-control" rows="3">{{ old('anamnesa', $assessment->anamnesa) }}</textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Catatan Alergi</label>
                                <input type="text" name="allergy_note" class="form-control" maxlength="255"
                                    value="{{ old('allergy_note', $assessment->allergy_note) }}" placeholder="Kosongkan bila tidak ada">
                            </div>

                            <h6 class="mt-4 mb-3"><i class="uil uil-heartbeat me-1"></i>Tanda-Tanda Vital</h6>
                            @include('pages.clinic.nursing._vital_fields', ['vitals' => $assessment])

                            <h6 class="mt-3 mb-2"><i class="uil uil-traffic-light me-1"></i>Triage <span class="text-danger">*</span></h6>
                            <div class="d-flex flex-wrap gap-3 mb-4">
                                @foreach(\App\Models\NursingAssessment::TRIAGE as $value => $label)
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="triage_level" id="triage_{{ $value }}"
                                            value="{{ $value }}" required
                                            @checked(old('triage_level', $assessment->triage_level ?? 'hijau') === $value)>
                                        <label class="form-check-label" for="triage_{{ $value }}">
                                            <span class="badge bg-{{ \App\Models\NursingAssessment::TRIAGE_BADGE[$value] }}">{{ $label }}</span>
                                        </label>
                                    </div>
                                @endforeach
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Poli Tujuan <span class="text-danger">*</span></label>
                                    <select name="poli_id" id="poli_id" class="form-select" required>
                                        <option value="">Pilih poli...</option>
                                        @foreach($polis as $poli)
                                            <option value="{{ $poli->id }}" @selected(old('poli_id', $assessment->poli_id ?? $appointment->poli_id) == $poli->id)>{{ $poli->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Dokter Tujuan <span class="text-danger">*</span></label>
                                    <select name="doctor_id" id="doctor_id" class="form-select" required>
                                        <option value="">Pilih dokter...</option>
                                        @foreach($doctors as $doctor)
                                            <option value="{{ $doctor->id }}" data-poli="{{ $doctor->poli_id }}"
                                                @selected(old('doctor_id', $assessment->doctor_id ?? $appointment->doctor_id) == $doctor->id)>
                                                {{ $doctor->name }} ({{ $doctor->specialization }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">Hanya dokter di poli terpilih (atau dokter tanpa poli) yang ditampilkan.</small>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Catatan untuk Dokter</label>
                                <textarea name="notes" class="form-control" rows="2">{{ old('notes', $assessment->notes) }}</textarea>
                            </div>

                            <button type="submit" class="btn btn-success"><i class="uil uil-message me-1"></i>Simpan & Kirim ke Dokter</button>
                            <a href="{{ route('clinic.nursing.index', app()->getLocale()) }}" class="btn btn-light">Batal</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const poli = document.getElementById('poli_id');
            const doctor = document.getElementById('doctor_id');

            function filterDoctors() {
                const poliId = poli.value;
                Array.from(doctor.options).forEach(option => {
                    if (!option.value) return;
                    const fits = !poliId || !option.dataset.poli || option.dataset.poli === poliId;
                    option.hidden = !fits;
                    option.disabled = !fits;
                });
                if (doctor.selectedOptions[0]?.disabled) doctor.value = '';
                // One matching doctor: pick it automatically
                const available = Array.from(doctor.options).filter(o => o.value && !o.disabled);
                if (!doctor.value && available.length === 1) doctor.value = available[0].value;
            }

            poli.addEventListener('change', filterDoctors);
            filterDoctors();
        });
    </script>
@endpush
