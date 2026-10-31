{{--
    Registration fields shared by create/edit.
    Expects: $nurses, $selectedPatient (nullable), $appointment (nullable)
--}}
@php
    $currentNurseId = old('nurse_id', $appointment->nurse_id ?? null);
    $currentDate = old('appointment_date', isset($appointment) ? $appointment->appointment_date->format('Y-m-d') : date('Y-m-d'));
@endphp

<div class="mb-3">
    <label class="form-label">Pasien <span class="text-danger">*</span></label>
    <div class="d-flex gap-2">
        <div class="flex-grow-1">
            <select name="patient_id" id="patient_id" class="form-select select2-patient" required>
                @if($selectedPatient)
                    <option value="{{ $selectedPatient->id }}" selected>
                        {{ $selectedPatient->name }} | NIK {{ $selectedPatient->nik ?: '-' }} | {{ $selectedPatient->no_rm }}
                    </option>
                @endif
            </select>
        </div>
        @can('patients.manage')
            <a href="{{ route('clinic.patients.create', [app()->getLocale(), 'from' => 'registration']) }}" class="btn btn-outline-primary text-nowrap"
                title="Daftarkan pasien baru">
                <i class="uil uil-user-plus me-1"></i>Pasien Baru
            </a>
        @endcan
    </div>
    <small class="text-muted">Ketik minimal 2 huruf/angka: nama, NIK, atau No. RM.</small>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">Perawat @if($nurses->isNotEmpty())<span class="text-danger">*</span>@endif</label>
        <select name="nurse_id" class="form-select" {{ $nurses->isNotEmpty() ? 'required' : '' }}>
            <option value="">Pilih perawat...</option>
            @foreach($nurses as $nurse)
                <option value="{{ $nurse->id }}" @selected($currentNurseId == $nurse->id)>{{ $nurse->name }}</option>
            @endforeach
        </select>
        @if($nurses->isEmpty())
            <small class="text-warning">Belum ada user perawat. Tambahkan user dengan role Perawat di Manajemen User.</small>
        @endif
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">Tanggal Kunjungan <span class="text-danger">*</span></label>
        <input type="date" name="appointment_date" class="form-control" value="{{ $currentDate }}" required>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">Jam Daftar</label>
        <input type="text" class="form-control"
            value="{{ isset($appointment) && $appointment->appointment_time ? $appointment->appointment_time->format('H:i') : 'Otomatis saat disimpan' }}"
            readonly>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">Petugas Pendaftaran</label>
        <input type="text" class="form-control"
            value="{{ isset($appointment) ? ($appointment->registeredBy?->name ?? '-') : auth()->user()->name }}" readonly>
    </div>
</div>

@push('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <style>
        .select2-container--bootstrap-5 .select2-selection { min-height: 44px; padding: 6px 12px; }
        .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered { line-height: 30px; }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(function () {
            $('.select2-patient').select2({
                theme: 'bootstrap-5',
                placeholder: 'Cari nama / NIK / No. RM...',
                allowClear: true,
                width: '100%',
                minimumInputLength: 2,
                language: {
                    inputTooShort: () => 'Ketik minimal 2 karakter',
                    noResults: () => 'Pasien tidak ditemukan',
                    searching: () => 'Mencari...'
                },
                ajax: {
                    url: @js(route('clinic.patients.search', app()->getLocale())),
                    dataType: 'json',
                    delay: 300,
                    data: params => ({ q: params.term }),
                    processResults: data => ({
                        results: data.map(p => ({
                            id: p.id,
                            text: `${p.name} | NIK ${p.nik || '-'} | ${p.no_rm}`
                        }))
                    })
                }
            });
        });
    </script>
@endpush
