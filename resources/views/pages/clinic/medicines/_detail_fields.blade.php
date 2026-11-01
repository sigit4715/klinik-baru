{{-- Optional medicine details + status. Expects optional $medicine --}}
@php $m = $medicine ?? null; @endphp
<h6 class="mt-3 mb-3 text-muted">Detail Obat (opsional)</h6>
<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">Nama Generik / Kandungan</label>
        <input type="text" name="generic_name" class="form-control" maxlength="255" placeholder="Contoh: Paracetamol" value="{{ old('generic_name', $m?->generic_name) }}">
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label">Bentuk Sediaan</label>
        <select name="dosage_form" class="form-select">
            <option value="">-</option>
            @foreach(\App\Models\Medicine::DOSAGE_FORMS as $form)
                <option value="{{ $form }}" @selected(old('dosage_form', $m?->dosage_form) === $form)>{{ $form }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label">Kekuatan</label>
        <input type="text" name="strength" class="form-control" maxlength="50" placeholder="500 mg" value="{{ old('strength', $m?->strength) }}">
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">Golongan</label>
        <select name="drug_class" class="form-select">
            <option value="">-</option>
            @foreach(\App\Models\Medicine::DRUG_CLASSES as $value => $label)
                <option value="{{ $value }}" @selected(old('drug_class', $m?->drug_class) === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">Lokasi Rak</label>
        <input type="text" name="rack_location" class="form-control" maxlength="50" placeholder="A-01" value="{{ old('rack_location', $m?->rack_location) }}">
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">Barcode</label>
        <input type="text" name="barcode" class="form-control" maxlength="100" value="{{ old('barcode', $m?->barcode) }}">
    </div>
    <div class="col-md-8 mb-3">
        <label class="form-label">Pabrik / Produsen</label>
        <input type="text" name="manufacturer" class="form-control" maxlength="255" value="{{ old('manufacturer', $m?->manufacturer) }}">
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">Status</label>
        <select name="status" class="form-select">
            <option value="active" @selected(old('status', $m?->status ?? 'active') === 'active')>Aktif</option>
            <option value="inactive" @selected(old('status', $m?->status) === 'inactive')>Tidak Aktif</option>
        </select>
    </div>
</div>
