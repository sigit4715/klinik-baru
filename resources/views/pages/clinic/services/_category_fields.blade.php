{{-- Category and poli. Expects $polis and optional $service --}}
<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">Kategori <span class="text-danger">*</span></label>
        <select name="category" class="form-select" required>
            @foreach(\App\Models\Service::CATEGORIES as $value => $label)
                <option value="{{ $value }}" @selected(old('category', $service->category ?? 'tindakan') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <small class="text-muted">Pilih <em>Penunjang</em> untuk lab, rontgen, EKG, dll.</small>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">Poli / Unit</label>
        <select name="poli_id" class="form-select">
            <option value="">- Tidak ditentukan -</option>
            @foreach($polis as $poli)
                <option value="{{ $poli->id }}" @selected(old('poli_id', $service->poli_id ?? null) == $poli->id)>{{ $poli->name }} ({{ $poli->typeLabel() }})</option>
            @endforeach
        </select>
        <small class="text-muted">Untuk penunjang: unit yang mengerjakan (Laboratorium, Radiologi, EKG).</small>
    </div>
</div>
