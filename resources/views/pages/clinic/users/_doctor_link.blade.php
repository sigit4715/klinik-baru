{{-- Optional link between a login and a doctor profile; "Pasien Menunggu" then shows only that doctor's patients --}}
<div class="mb-3">
    <label class="form-label">Terhubung ke Data Dokter</label>
    <select name="doctor_id" class="form-select @error('doctor_id') is-invalid @enderror">
        <option value="">- Bukan dokter -</option>
        @foreach($doctors as $doctorOption)
            <option value="{{ $doctorOption->id }}" @selected(old('doctor_id', $user->doctor_id ?? null) == $doctorOption->id)>
                {{ $doctorOption->name }} ({{ $doctorOption->specialization }})
            </option>
        @endforeach
    </select>
    <small class="text-muted">Isi untuk akun dokter, supaya daftar Pasien Menunggu hanya menampilkan pasien dokter tersebut.</small>
    @error('doctor_id')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
