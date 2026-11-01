{{--
    Doctor examination form shared by create and edit.
    Expects: $appointment, $assessment (nullable), $record (nullable), $medicines, $services, $doctors,
    $itemsLocked (bool, edit only)
--}}
@php
    $record = $record ?? null;
    $itemsLocked = $itemsLocked ?? false;
    // Create: start from the nurse's measurements; edit: from the saved record
    $source = $record ?? $assessment;
    // Single medicines and racikan are stored together; split them for the two editors
    $savedLines = $record?->prescriptions ?? [];
    $singleLines = array_values(array_filter($savedLines, fn ($l) => ($l['type'] ?? null) !== 'racikan'));
    $prescriptionLines = old('prescriptions', $singleLines ?: [[]]);
    $racikanLines = old('racikan', array_values(array_filter($savedLines, fn ($l) => ($l['type'] ?? null) === 'racikan')));
    $defaultFee = (float) (\App\Models\ClinicSetting::get('compounding_fee', 0) ?: 0);
    $serviceLines = old('services_used', $record?->services_used ?: [[]]);
@endphp

@if(! $record && ! $appointment->doctor_id)
    <div class="mb-3">
        <label class="form-label">Dokter Pemeriksa <span class="text-danger">*</span></label>
        <select name="doctor_id" class="form-select" required>
            <option value="">Pilih dokter...</option>
            @foreach($doctors as $doctor)
                <option value="{{ $doctor->id }}" @selected(old('doctor_id', auth()->user()->doctor_id) == $doctor->id)>{{ $doctor->name }}</option>
            @endforeach
        </select>
        <small class="text-muted">Pasien belum melewati perawat, jadi dokter dipilih di sini.</small>
    </div>
@endif

<div class="mb-3">
    <label class="form-label">Anamnesa (Riwayat Penyakit)</label>
    <textarea name="anamnesa" class="form-control" rows="2">{{ old('anamnesa', $source?->anamnesa) }}</textarea>
    @if(! $record && $assessment)
        <small class="text-muted">Terisi dari data perawat, bisa dikoreksi.</small>
    @endif
</div>
<div class="mb-3">
    <label class="form-label">Diagnosis <span class="text-danger">*</span></label>
    <textarea name="diagnosis" class="form-control" rows="2" required>{{ old('diagnosis', $record?->diagnosis) }}</textarea>
</div>

<h6 class="mt-4 mb-3"><i class="uil uil-heartbeat me-1"></i>Tanda-Tanda Vital
    <small class="text-muted fw-normal">{{ ! $record && $assessment ? '(dari perawat, bisa dikoreksi)' : '(opsional)' }}</small></h6>
@include('pages.clinic.nursing._vital_fields', ['vitals' => $source, 'withSpo2' => false])

<div class="mb-3">
    <label class="form-label">Tindakan/Treatment</label>
    <textarea name="treatment" class="form-control" rows="2">{{ old('treatment', $record?->treatment) }}</textarea>
</div>

@if($itemsLocked)
    <div class="alert alert-warning">
        <i class="uil uil-lock me-1"></i>Kunjungan ini sudah memiliki pembayaran, jadi <strong>obat dan layanan tidak bisa diubah</strong>.
        Bila perlu diubah, kasir melakukan Refund terlebih dahulu.
    </div>
    <h6 class="mt-3">Resep Obat</h6>
    <ul class="mb-3">
        @forelse($record->getPrescriptionDetails() as $line)
            <li>{{ $line['medicine']->name }} × {{ $line['qty'] }} {{ $line['dosage'] ? '(' . $line['dosage'] . ')' : '' }}</li>
        @empty
            <li class="text-muted">Tidak ada</li>
        @endforelse
        @foreach($record->getCompoundDetails() as $racik)
            <li>Racikan {{ $racik['name'] }} ({{ $racik['qty'] }}) {{ $racik['dosage'] ? '(' . $racik['dosage'] . ')' : '' }}:
                {{ collect($racik['components'])->map(fn ($x) => $x['medicine']->name . ' ' . $x['qty'])->implode(', ') }}</li>
        @endforeach
    </ul>
    <h6>Layanan/Tindakan</h6>
    <ul class="mb-3">
        @forelse($record->getServiceDetails() as $line)
            <li>{{ $line['service']->name }} × {{ $line['qty'] }}</li>
        @empty
            <li class="text-muted">Tidak ada</li>
        @endforelse
    </ul>
@else
    <h6 class="mt-4">Resep Obat</h6>
    <div id="prescriptions">
        @foreach($prescriptionLines as $i => $line)
            <div class="row mb-2 prescription-row align-items-center g-2">
                <div class="col-md-4">
                    <select name="prescriptions[{{ $i }}][medicine_id]" class="form-select form-select-sm select2-medicine">
                        <option value="">Pilih Obat...</option>
                        @foreach($medicines as $med)
                            <option value="{{ $med->id }}" @selected(($line['medicine_id'] ?? null) == $med->id)>
                                {{ $med->name }} (Stok: {{ $med->stock }}){{ $med->trashed() || $med->status !== 'active' ? ' - nonaktif' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2"><input type="number" name="prescriptions[{{ $i }}][qty]" value="{{ $line['qty'] ?? '' }}"
                        class="form-control form-control-sm" placeholder="Qty" min="1"></div>
                <div class="col-md-3"><input type="text" name="prescriptions[{{ $i }}][dosage]" value="{{ $line['dosage'] ?? '' }}"
                        class="form-control form-control-sm" placeholder="Dosis (3x1)"></div>
                <div class="col-md-2"><input type="text" name="prescriptions[{{ $i }}][notes]" value="{{ $line['notes'] ?? '' }}"
                        class="form-control form-control-sm" placeholder="Ket"></div>
                <div class="col-md-1"><button type="button" class="btn btn-sm btn-outline-danger btn-remove-row" title="Hapus baris">&times;</button></div>
            </div>
        @endforeach
    </div>
    <button type="button" class="btn btn-sm btn-outline-primary mb-3" onclick="addPrescription()"><i class="uil uil-plus"></i> Tambah Obat</button>

    @include('pages.clinic.medical-records._racikan')

    <h6 class="mt-4">Layanan/Tindakan</h6>
    <div id="services">
        @foreach($serviceLines as $i => $line)
            <div class="row mb-2 service-row align-items-center g-2">
                <div class="col-md-8">
                    <select name="services_used[{{ $i }}][service_id]" class="form-select form-select-sm select2-service">
                        <option value="">Pilih Layanan...</option>
                        @foreach($services as $svc)
                            <option value="{{ $svc->id }}" @selected(($line['service_id'] ?? null) == $svc->id)>
                                {{ $svc->name }} - Rp {{ number_format($svc->price, 0, ',', '.') }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3"><input type="number" name="services_used[{{ $i }}][qty]" value="{{ $line['qty'] ?? 1 }}"
                        class="form-control form-control-sm" placeholder="Qty" min="1"></div>
                <div class="col-md-1"><button type="button" class="btn btn-sm btn-outline-danger btn-remove-row" title="Hapus baris">&times;</button></div>
            </div>
        @endforeach
    </div>
    <button type="button" class="btn btn-sm btn-outline-primary mb-3" onclick="addService()"><i class="uil uil-plus"></i> Tambah Layanan</button>
@endif

<div class="mb-3">
    <label class="form-label">Catatan</label>
    <textarea name="notes" class="form-control" rows="2">{{ old('notes', $record?->notes) }}</textarea>
</div>

@push('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <style>
        .select2-container { width: 100% !important; }
        .select2-container--bootstrap-5 .select2-selection--single { min-height: 32px; padding: 2px 8px; font-size: 0.875rem; }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        const medicineOptions = @js(
            '<option value="">Pilih Obat...</option>' . $medicines->filter(fn ($m) => ! $m->trashed() && $m->status === 'active' && $m->stock > 0)
                ->map(fn ($m) => '<option value="' . $m->id . '">' . e($m->name) . ' (Stok: ' . $m->stock . ')</option>')->implode('')
        );
        const serviceOptions = @js(
            '<option value="">Pilih Layanan...</option>' . $services->filter(fn ($s) => ! $s->trashed() && $s->status === 'active')
                ->map(fn ($s) => '<option value="' . $s->id . '">' . e($s->name) . ' - Rp ' . number_format($s->price, 0, ',', '.') . '</option>')->implode('')
        );

        let prescriptionIndex = {{ count($prescriptionLines) }};
        let serviceIndex = {{ count($serviceLines) }};

        function initSelect2() {
            $('.select2-medicine').select2({ theme: 'bootstrap-5', placeholder: 'Cari obat...', allowClear: true });
            $('.select2-service').select2({ theme: 'bootstrap-5', placeholder: 'Cari layanan...', allowClear: true });
        }

        function addPrescription() {
            const i = prescriptionIndex++;
            document.getElementById('prescriptions').insertAdjacentHTML('beforeend', `<div class="row mb-2 prescription-row align-items-center g-2">
                <div class="col-md-4"><select name="prescriptions[${i}][medicine_id]" class="form-select form-select-sm select2-medicine">${medicineOptions}</select></div>
                <div class="col-md-2"><input type="number" name="prescriptions[${i}][qty]" class="form-control form-control-sm" placeholder="Qty" min="1"></div>
                <div class="col-md-3"><input type="text" name="prescriptions[${i}][dosage]" class="form-control form-control-sm" placeholder="Dosis"></div>
                <div class="col-md-2"><input type="text" name="prescriptions[${i}][notes]" class="form-control form-control-sm" placeholder="Ket"></div>
                <div class="col-md-1"><button type="button" class="btn btn-sm btn-outline-danger btn-remove-row" title="Hapus baris">&times;</button></div>
            </div>`);
            initSelect2();
        }

        function addService() {
            const i = serviceIndex++;
            document.getElementById('services').insertAdjacentHTML('beforeend', `<div class="row mb-2 service-row align-items-center g-2">
                <div class="col-md-8"><select name="services_used[${i}][service_id]" class="form-select form-select-sm select2-service">${serviceOptions}</select></div>
                <div class="col-md-3"><input type="number" name="services_used[${i}][qty]" class="form-control form-control-sm" placeholder="Qty" min="1" value="1"></div>
                <div class="col-md-1"><button type="button" class="btn btn-sm btn-outline-danger btn-remove-row" title="Hapus baris">&times;</button></div>
            </div>`);
            initSelect2();
        }

        // Remove a line; an empty line is simply ignored on save
        document.addEventListener('click', function (e) {
            const btn = e.target.closest('.btn-remove-row');
            if (btn) btn.closest('.row').remove();
        });

        $(document).ready(initSelect2);
    </script>
@endpush
