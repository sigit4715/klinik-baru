@section('title', $title)
@section('description', $description)
@extends('layout.app')

@push('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
@endpush

@section('content')
    @php
        $type = $letter->type;
        $date = fn ($field) => old($field, $letter->{$field}?->format('Y-m-d'));
    @endphp
    <div class="container-fluid">
        <div class="breadcrumb-main">
            <h4 class="breadcrumb-title">{{ $title }} @if($letter->exists)<small class="text-muted">{{ $letter->letter_number }}</small>@endif</h4>
        </div>
        <div class="row">
            <div class="col-lg-8">
                @if($errors->any())
                    <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
                @endif
                @if($record)
                    <div class="alert alert-info py-2 small">Data diambil dari rekam medis tanggal {{ $record->created_at->format('d/m/Y') }}. Silakan periksa dan sesuaikan.</div>
                @endif
                <div class="card">
                    <div class="card-body">
                        <form method="POST" action="{{ $letter->exists ? route('clinic.letters.update', [app()->getLocale(), $letter->id]) : route('clinic.letters.store', app()->getLocale()) }}">
                            @csrf
                            @if($letter->exists) @method('PUT') @endif
                            <input type="hidden" name="type" value="{{ $type }}">
                            <input type="hidden" name="medical_record_id" value="{{ old('medical_record_id', $letter->medical_record_id) }}">

                            <div class="row">
                                <div class="col-md-8 mb-3">
                                    <label class="form-label">Pasien <span class="text-danger">*</span></label>
                                    @if($record || $letter->exists)
                                        <input type="hidden" name="patient_id" value="{{ $letter->patient_id }}">
                                        <input type="text" class="form-control" value="{{ $patient?->name }} | {{ $patient?->no_rm }}" readonly>
                                    @else
                                        <select name="patient_id" class="form-select select2-patient" required>
                                            @if($patient)
                                                <option value="{{ $patient->id }}" selected>{{ $patient->name }} | NIK {{ $patient->nik ?: '-' }} | {{ $patient->no_rm }}</option>
                                            @endif
                                        </select>
                                    @endif
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Tanggal Surat <span class="text-danger">*</span></label>
                                    <input type="date" name="issued_date" class="form-control" value="{{ $date('issued_date') }}" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Dokter / Bidan Pemeriksa <span class="text-danger">*</span></label>
                                <select name="doctor_id" class="form-select" required>
                                    <option value="">Pilih...</option>
                                    @foreach($doctors as $doctor)
                                        <option value="{{ $doctor->id }}" @selected(old('doctor_id', $letter->doctor_id) == $doctor->id)>{{ $doctor->name }} (SIP {{ $doctor->sip_number }})</option>
                                    @endforeach
                                </select>
                            </div>

                            @if($type === 'healthy')
                                <div class="mb-3">
                                    <label class="form-label">Keperluan <span class="text-danger">*</span></label>
                                    <input type="text" name="purpose" class="form-control" maxlength="255" required placeholder="Contoh: melamar pekerjaan, sekolah, pernikahan"
                                        value="{{ old('purpose', $letter->purpose) }}">
                                </div>
                            @elseif($type === 'sick')
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Lama Istirahat <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <input type="number" name="rest_days" id="restDays" class="form-control" min="1" max="60" required value="{{ old('rest_days', $letter->rest_days) }}">
                                            <span class="input-group-text">hari</span>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Mulai Tanggal <span class="text-danger">*</span></label>
                                        <input type="date" name="rest_from" id="restFrom" class="form-control" required value="{{ $date('rest_from') }}">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Sampai Tanggal</label>
                                        <input type="text" id="restUntil" class="form-control" readonly>
                                    </div>
                                </div>
                            @else
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Dirujuk ke (Faskes) <span class="text-danger">*</span></label>
                                        <input type="text" name="referred_to" class="form-control" maxlength="255" required placeholder="Contoh: RSUD Leuwiliang"
                                            value="{{ old('referred_to', $letter->referred_to) }}">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Poli / Dokter Spesialis Tujuan</label>
                                        <input type="text" name="referred_department" class="form-control" maxlength="255" placeholder="Contoh: Poli Anak / dr. Sp.A"
                                            value="{{ old('referred_department', $letter->referred_department) }}">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Anamnesa & Hasil Pemeriksaan</label>
                                    <textarea name="findings" class="form-control" rows="3">{{ old('findings', $letter->findings) }}</textarea>
                                </div>
                            @endif

                            <div class="mb-3">
                                <label class="form-label">Diagnosis{{ $type === 'referral' ? ' Sementara' : '' }} @if($type === 'referral')<span class="text-danger">*</span>@endif</label>
                                <textarea name="diagnosis" class="form-control" rows="2" {{ $type === 'referral' ? 'required' : '' }}>{{ old('diagnosis', $letter->diagnosis) }}</textarea>
                                @if($type === 'sick')
                                    <div class="form-check mt-1">
                                        <input type="checkbox" class="form-check-input" name="show_diagnosis" value="1" id="showDiagnosis" @checked(old('show_diagnosis', $letter->show_diagnosis))>
                                        <label class="form-check-label small" for="showDiagnosis">Tampilkan diagnosis di surat (bawaan disembunyikan untuk privasi pasien)</label>
                                    </div>
                                @elseif($type === 'healthy')
                                    <small class="text-muted">Opsional, tidak dicetak.</small>
                                @endif
                            </div>

                            @if($type === 'referral')
                                <div class="mb-3">
                                    <label class="form-label">Tindakan / Terapi yang Sudah Diberikan</label>
                                    <textarea name="therapy" class="form-control" rows="3">{{ old('therapy', $letter->therapy) }}</textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Alasan Dirujuk <span class="text-danger">*</span></label>
                                    <textarea name="referral_reason" class="form-control" rows="2" required placeholder="Contoh: perlu pemeriksaan penunjang / penanganan spesialis">{{ old('referral_reason', $letter->referral_reason) }}</textarea>
                                </div>
                            @endif

                            @if($type !== 'sick')
                                <h6 class="mt-3 mb-2">Tanda Vital @if($type === 'healthy')<small class="text-muted fw-normal">(dicetak di surat)</small>@endif</h6>
                                <div class="vitals-wrapper">
                                    @include('pages.clinic.nursing._vital_fields', ['vitals' => (object) old('vitals', $letter->vitals ?? []), 'withSpo2' => false])
                                </div>
                            @endif

                            <div class="mb-4">
                                <label class="form-label">Catatan Tambahan</label>
                                <textarea name="notes" class="form-control" rows="2">{{ old('notes', $letter->notes) }}</textarea>
                            </div>

                            <button type="submit" class="btn btn-primary"><i class="uil uil-check me-1"></i>Simpan</button>
                            <a href="{{ route('clinic.letters.index', app()->getLocale()) }}" class="btn btn-light">Batal</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(function () {
            // Vital sign inputs are submitted as vitals[...]
            $('.vitals-wrapper input').each(function () { this.name = 'vitals[' + this.name + ']'; });

            $('.select2-patient').select2({
                theme: 'bootstrap-5', width: '100%', minimumInputLength: 2, placeholder: 'Cari nama / NIK / No. RM...',
                language: { inputTooShort: () => 'Ketik minimal 2 karakter', noResults: () => 'Pasien tidak ditemukan', searching: () => 'Mencari...' },
                ajax: {
                    url: @js(route('clinic.patients.search', app()->getLocale())), dataType: 'json', delay: 300,
                    data: params => ({ q: params.term }),
                    processResults: data => ({ results: data.map(p => ({ id: p.id, text: `${p.name} | NIK ${p.nik || '-'} | ${p.no_rm}` })) })
                }
            });

            const days = document.getElementById('restDays');
            const from = document.getElementById('restFrom');
            const until = document.getElementById('restUntil');
            function updateUntil() {
                if (!days || !from.value || !days.value) return;
                const d = new Date(from.value + 'T00:00:00');
                d.setDate(d.getDate() + parseInt(days.value, 10) - 1);
                until.value = d.toLocaleDateString('id-ID', { day: '2-digit', month: '2-digit', year: 'numeric' });
            }
            days?.addEventListener('input', updateUntil);
            from?.addEventListener('change', updateUntil);
            updateUntil();
        });
    </script>
@endpush
