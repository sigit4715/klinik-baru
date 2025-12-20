@section('title', $title)
@section('description', $description)
@extends('layout.app')

@push('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css"
        rel="stylesheet" />
    <style>
        .select2-container {
            width: 100% !important;
        }

        .select2-container--bootstrap-5 .select2-selection--single {
            min-height: 32px;
            padding: 2px 8px;
            font-size: 0.875rem;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header bg-primary text-white">Info Pasien</div>
                    <div class="card-body">
                        @if($patient)
                            <h5>{{ $patient->name }}</h5>
                            <p class="text-muted mb-2"><code>{{ $patient->no_rm }}</code></p>
                            <p><small>{{ $patient->gender == 'L' ? 'Laki-laki' : 'Perempuan' }}, {{ $patient->birth_date->age }}
                                    tahun</small></p>
                            @if($patient->allergy)
                                <div class="alert alert-danger py-2"><i class="uil uil-exclamation-triangle me-1"></i>Alergi:
                                    {{ $patient->allergy }}</div>
                            @endif
                            <hr>
                            <p><strong>Keluhan:</strong><br>{{ $appointment->complaint }}</p>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header"><i class="uil uil-stethoscope me-2"></i>Form Pemeriksaan</div>
                    <div class="card-body">
                        <form action="{{ route('clinic.medical-records.store', app()->getLocale()) }}" method="POST">
                            @csrf
                            <input type="hidden" name="appointment_id" value="{{ $appointment->id }}">
                            <div class="mb-3">
                                <label class="form-label">Anamnesa (Riwayat Penyakit)</label>
                                <textarea name="anamnesa" class="form-control" rows="2">{{ old('anamnesa') }}</textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Diagnosis <span class="text-danger">*</span></label>
                                <textarea name="diagnosis" class="form-control" rows="2"
                                    required>{{ old('diagnosis') }}</textarea>
                            </div>

                            <h6 class="mt-4 mb-3"><i class="uil uil-heartbeat me-1"></i>Tanda-Tanda Vital (Opsional)</h6>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Tekanan Darah</label>
                                    <div class="input-group input-group-sm">
                                        <input type="text" name="blood_pressure" class="form-control" 
                                            value="{{ old('blood_pressure') }}" placeholder="120/80">
                                        <span class="input-group-text">mmHg</span>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Suhu Badan</label>
                                    <div class="input-group input-group-sm">
                                        <input type="number" step="0.1" name="body_temperature" class="form-control" 
                                            value="{{ old('body_temperature') }}" placeholder="36.5">
                                        <span class="input-group-text">°C</span>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Denyut Nadi</label>
                                    <div class="input-group input-group-sm">
                                        <input type="number" name="pulse_rate" class="form-control" 
                                            value="{{ old('pulse_rate') }}" placeholder="80">
                                        <span class="input-group-text">x/menit</span>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Pernapasan</label>
                                    <div class="input-group input-group-sm">
                                        <input type="number" name="respiratory_rate" class="form-control" 
                                            value="{{ old('respiratory_rate') }}" placeholder="20">
                                        <span class="input-group-text">x/menit</span>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Tinggi Badan</label>
                                    <div class="input-group input-group-sm">
                                        <input type="number" step="0.1" name="height" class="form-control" 
                                            value="{{ old('height') }}" placeholder="165">
                                        <span class="input-group-text">cm</span>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Berat Badan</label>
                                    <div class="input-group input-group-sm">
                                        <input type="number" step="0.1" name="weight" class="form-control" 
                                            value="{{ old('weight') }}" placeholder="60">
                                        <span class="input-group-text">kg</span>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Tindakan/Treatment</label>
                                <textarea name="treatment" class="form-control" rows="2">{{ old('treatment') }}</textarea>
                            </div>

                            <h6 class="mt-4">Resep Obat</h6>
                            <div id="prescriptions">
                                <div class="row mb-2 prescription-row align-items-center">
                                    <div class="col-md-4">
                                        <select name="prescriptions[0][medicine_id]"
                                            class="form-select form-select-sm select2-medicine">
                                            <option value="">Pilih Obat...</option>
                                            @foreach($medicines as $med)
                                                <option value="{{ $med->id }}" data-stock="{{ $med->stock }}">{{ $med->name }}
                                                    (Stok: {{ $med->stock }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2"><input type="number" name="prescriptions[0][qty]"
                                            class="form-control form-control-sm" placeholder="Qty" min="1"></div>
                                    <div class="col-md-3"><input type="text" name="prescriptions[0][dosage]"
                                            class="form-control form-control-sm" placeholder="Dosis (3x1)"></div>
                                    <div class="col-md-3"><input type="text" name="prescriptions[0][notes]"
                                            class="form-control form-control-sm" placeholder="Ket"></div>
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-primary mb-3" onclick="addPrescription()"><i
                                    class="uil uil-plus"></i> Tambah Obat</button>

                            <h6 class="mt-4">Layanan/Tindakan</h6>
                            <div id="services">
                                <div class="row mb-2 service-row align-items-center">
                                    <div class="col-md-8">
                                        <select name="services_used[0][service_id]"
                                            class="form-select form-select-sm select2-service">
                                            <option value="">Pilih Layanan...</option>
                                            @foreach($services as $svc)
                                                <option value="{{ $svc->id }}">{{ $svc->name }} - Rp
                                                    {{ number_format($svc->price, 0, ',', '.') }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4"><input type="number" name="services_used[0][qty]"
                                            class="form-control form-control-sm" placeholder="Qty" min="1" value="1"></div>
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-primary mb-3" onclick="addService()"><i
                                    class="uil uil-plus"></i> Tambah Layanan</button>

                            <div class="mb-3">
                                <label class="form-label">Catatan</label>
                                <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
                            </div>

                            <div class="mt-4">
                                <button type="submit" class="btn btn-success"><i class="uil uil-check me-1"></i>Simpan Rekam
                                    Medis</button>
                                <a href="{{ route('clinic.appointments.index', app()->getLocale()) }}"
                                    class="btn btn-light">Batal</a>
                            </div>
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
        // Medicine options for dynamic rows
        const medicineOptions = `<option value="">Pilih Obat...</option>@foreach($medicines as $med)<option value="{{ $med->id }}">{{ $med->name }} (Stok: {{ $med->stock }})</option>@endforeach`;
        const serviceOptions = `<option value="">Pilih Layanan...</option>@foreach($services as $svc)<option value="{{ $svc->id }}">{{ $svc->name }} - Rp {{ number_format($svc->price, 0, ',', '.') }}</option>@endforeach`;

        let prescriptionIndex = 1;
        let serviceIndex = 1;

        function initSelect2() {
            $('.select2-medicine').select2({
                theme: 'bootstrap-5',
                placeholder: 'Cari obat...',
                allowClear: true
            });
            $('.select2-service').select2({
                theme: 'bootstrap-5',
                placeholder: 'Cari layanan...',
                allowClear: true
            });
        }

        function addPrescription() {
            const html = `<div class="row mb-2 prescription-row align-items-center">
                <div class="col-md-4"><select name="prescriptions[${prescriptionIndex}][medicine_id]" class="form-select form-select-sm select2-medicine">${medicineOptions}</select></div>
                <div class="col-md-2"><input type="number" name="prescriptions[${prescriptionIndex}][qty]" class="form-control form-control-sm" placeholder="Qty" min="1"></div>
                <div class="col-md-3"><input type="text" name="prescriptions[${prescriptionIndex}][dosage]" class="form-control form-control-sm" placeholder="Dosis"></div>
                <div class="col-md-3"><input type="text" name="prescriptions[${prescriptionIndex}][notes]" class="form-control form-control-sm" placeholder="Ket"></div>
            </div>`;
            document.getElementById('prescriptions').insertAdjacentHTML('beforeend', html);
            prescriptionIndex++;
            initSelect2();
        }

        function addService() {
            const html = `<div class="row mb-2 service-row align-items-center">
                <div class="col-md-8"><select name="services_used[${serviceIndex}][service_id]" class="form-select form-select-sm select2-service">${serviceOptions}</select></div>
                <div class="col-md-4"><input type="number" name="services_used[${serviceIndex}][qty]" class="form-control form-control-sm" placeholder="Qty" min="1" value="1"></div>
            </div>`;
            document.getElementById('services').insertAdjacentHTML('beforeend', html);
            serviceIndex++;
            initSelect2();
        }

        $(document).ready(function () {
            initSelect2();
        });
    </script>
@endpush