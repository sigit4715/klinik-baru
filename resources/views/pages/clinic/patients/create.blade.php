@section('title', $title)
@section('description', $description)
@extends('layout.app')

@push('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css"
        rel="stylesheet" />
    <style>
        .patient-form-header {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            border-radius: 16px;
            padding: 28px 32px;
            margin-bottom: 24px;
            color: white;
            box-shadow: 0 10px 40px rgba(59, 130, 246, 0.3);
        }

        .patient-form-header h4 {
            margin: 0;
            font-size: 22px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .patient-form-header .subtitle {
            opacity: 0.9;
            font-size: 14px;
            margin-top: 6px;
        }

        .form-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.06);
            border: 1px solid #e5e7eb;
            overflow: hidden;
            margin-bottom: 24px;
        }

        .form-card-header {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            padding: 16px 24px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-card-header i {
            font-size: 20px;
            color: #3b82f6;
        }

        .form-card-header h6 {
            margin: 0;
            font-size: 15px;
            font-weight: 600;
            color: #1f2937;
        }

        .form-card-body {
            padding: 24px;
        }

        .form-label {
            font-weight: 500;
            font-size: 13px;
            color: #374151;
            margin-bottom: 6px;
        }

        .form-control,
        .form-select {
            border-radius: 10px;
            border: 1px solid #e5e7eb;
            padding: 10px 14px;
            font-size: 14px;
            transition: all 0.2s;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .input-group-text {
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            border-radius: 10px 0 0 10px;
            color: #6b7280;
        }

        .rm-display {
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
            border-radius: 12px;
            padding: 16px 20px;
            border: 2px solid #3b82f6;
        }

        .rm-display .rm-label {
            font-size: 12px;
            color: #1d4ed8;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .rm-display .rm-number {
            font-size: 24px;
            font-weight: 700;
            color: #1d4ed8;
            font-family: monospace;
        }

        .btn-submit {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border: none;
            padding: 14px 32px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 15px;
            color: white;
            transition: all 0.3s;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);
            color: white;
        }

        .btn-cancel {
            background: #f3f4f6;
            border: none;
            padding: 14px 32px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 15px;
            color: #6b7280;
            transition: all 0.3s;
        }

        .btn-cancel:hover {
            background: #e5e7eb;
            color: #4b5563;
        }

        .required-star {
            color: #ef4444;
            margin-left: 2px;
        }

        .optional-badge {
            background: #f3f4f6;
            color: #6b7280;
            font-size: 10px;
            padding: 2px 8px;
            border-radius: 10px;
            margin-left: 6px;
        }

        .address-detail-input {
            margin-top: 12px;
        }

        /* Select2 Custom Styling */
        .select2-container--bootstrap-5 .select2-selection {
            border-radius: 10px !important;
            border: 1px solid #e5e7eb !important;
            padding: 6px 10px !important;
            min-height: 44px !important;
        }

        .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
            padding-left: 0 !important;
            line-height: 28px !important;
        }

        .select2-container--bootstrap-5 .select2-selection--single .select2-selection__arrow {
            height: 42px !important;
        }

        .select2-container--bootstrap-5 .select2-dropdown {
            border-radius: 10px !important;
            border: 1px solid #e5e7eb !important;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1) !important;
        }

        .select2-container--bootstrap-5 .select2-results__option--highlighted {
            background-color: #3b82f6 !important;
        }

        .select2-container--bootstrap-5 .select2-search--dropdown .select2-search__field {
            border-radius: 8px !important;
            padding: 8px 12px !important;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid">
        <!-- Header -->
        <div class="patient-form-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4><i class="uil uil-user-plus"></i> {{ $title }}</h4>
                    <div class="subtitle">Lengkapi data pasien dengan benar untuk pendaftaran</div>
                </div>
                <div class="rm-display">
                    <div class="rm-label">No. Rekam Medis</div>
                    <div class="rm-number">{{ $no_rm }}</div>
                </div>
            </div>
        </div>

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show mb-4" style="border-radius: 12px;">
                <strong><i class="uil uil-exclamation-triangle me-2"></i>Terjadi Kesalahan!</strong>
                <ul class="mb-0 mt-2">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <form action="{{ route('clinic.patients.store', app()->getLocale()) }}" method="POST">
            @csrf
            @if(old('from', request('from')) === 'registration')
                <input type="hidden" name="from" value="registration">
            @endif

            <!-- Data Pribadi -->
            <div class="form-card">
                <div class="form-card-header">
                    <i class="uil uil-user"></i>
                    <h6>Data Pribadi</h6>
                </div>
                <div class="form-card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nama Lengkap <span class="required-star">*</span></label>
                            <input type="text" name="name" class="form-control" value="{{ old('name') }}"
                                placeholder="Masukkan nama lengkap" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">NIK (Nomor Induk Kependudukan) <span
                                    class="required-star">*</span></label>
                            <input type="text" name="nik" class="form-control" value="{{ old('nik') }}" maxlength="16"
                                placeholder="16 digit NIK" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Jenis Kelamin <span class="required-star">*</span></label>
                            <select name="gender" class="form-select" required>
                                <option value="">Pilih...</option>
                                <option value="L" {{ old('gender') == 'L' ? 'selected' : '' }}>Laki-laki</option>
                                <option value="P" {{ old('gender') == 'P' ? 'selected' : '' }}>Perempuan</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Golongan Darah <span class="optional-badge">Opsional</span></label>
                            <select name="blood_type" class="form-select">
                                <option value="">Pilih...</option>
                                <option value="A" {{ old('blood_type') == 'A' ? 'selected' : '' }}>A</option>
                                <option value="B" {{ old('blood_type') == 'B' ? 'selected' : '' }}>B</option>
                                <option value="AB" {{ old('blood_type') == 'AB' ? 'selected' : '' }}>AB</option>
                                <option value="O" {{ old('blood_type') == 'O' ? 'selected' : '' }}>O</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Tempat Lahir <span class="required-star">*</span></label>
                            <input type="text" name="birth_place" class="form-control" value="{{ old('birth_place') }}"
                                placeholder="Kota/Kabupaten" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Tanggal Lahir <span class="required-star">*</span></label>
                            <input type="date" name="birth_date" class="form-control" value="{{ old('birth_date') }}"
                                required>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kontak & Alamat -->
            <div class="form-card">
                <div class="form-card-header">
                    <i class="uil uil-map-marker"></i>
                    <h6>Kontak & Alamat</h6>
                </div>
                <div class="form-card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">No. Telepon/WhatsApp <span
                                    class="optional-badge">Opsional</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="uil uil-phone"></i></span>
                                <input type="text" name="phone" class="form-control" value="{{ old('phone') }}"
                                    placeholder="08xxxxxxxxxx">
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email <span class="optional-badge">Opsional</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="uil uil-envelope"></i></span>
                                <input type="email" name="email" class="form-control" value="{{ old('email') }}"
                                    placeholder="email@example.com">
                            </div>
                        </div>
                    </div>

                    <!-- Alamat Indonesia -->
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Provinsi <span class="required-star">*</span></label>
                            <select id="provinsi" name="provinsi" class="form-select" required>
                                <option value="">Pilih Provinsi...</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Kabupaten/Kota <span class="required-star">*</span></label>
                            <select id="kabupaten" name="kabupaten" class="form-select" required disabled>
                                <option value="">Pilih Kabupaten...</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Kecamatan <span class="required-star">*</span></label>
                            <select id="kecamatan" name="kecamatan" class="form-select" required disabled>
                                <option value="">Pilih Kecamatan...</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Desa/Kelurahan <span class="required-star">*</span></label>
                            <select id="kelurahan" name="kelurahan" class="form-select" required disabled>
                                <option value="">Pilih Kelurahan...</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12 mb-3">
                            <label class="form-label">Detail Alamat (RT/RW, Jalan, Nomor Rumah) <span
                                    class="required-star">*</span></label>
                            <textarea name="address" id="address" class="form-control" rows="2"
                                placeholder="Contoh: Jl. Merdeka No. 123 RT 01/RW 02"
                                required>{{ old('address') }}</textarea>
                            <input type="hidden" name="full_address" id="full_address">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Informasi Keluarga -->
            <div class="form-card">
                <div class="form-card-header">
                    <i class="uil uil-users-alt"></i>
                    <h6>Informasi Keluarga <span class="optional-badge">Opsional</span></h6>
                </div>
                <div class="form-card-body">
                    <p class="text-muted small mb-3">
                        <i class="uil uil-info-circle me-1"></i>
                        Nama suami untuk pasien hamil, Nama orang tua untuk pasien anak-anak
                    </p>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nama Suami</label>
                            <input type="text" name="spouse_name" class="form-control" value="{{ old('spouse_name') }}"
                                placeholder="Untuk pasien hamil">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nama Orang Tua</label>
                            <input type="text" name="parent_name" class="form-control" value="{{ old('parent_name') }}"
                                placeholder="Untuk pasien anak-anak">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Informasi Medis -->
            <div class="form-card">
                <div class="form-card-header">
                    <i class="uil uil-medkit"></i>
                    <h6>Informasi Medis <span class="optional-badge">Opsional</span></h6>
                </div>
                <div class="form-card-body">
                    <div class="row">
                        <div class="col-12 mb-3">
                            <label class="form-label">Riwayat Alergi</label>
                            <input type="text" name="allergy" class="form-control" value="{{ old('allergy') }}"
                                placeholder="Contoh: Penisilin, Seafood, Debu, dll">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kontak Darurat -->
            <div class="form-card">
                <div class="form-card-header">
                    <i class="uil uil-phone-alt"></i>
                    <h6>Kontak Darurat <span class="optional-badge">Opsional</span></h6>
                </div>
                <div class="form-card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nama Kontak Darurat</label>
                            <input type="text" name="emergency_contact" class="form-control"
                                value="{{ old('emergency_contact') }}" placeholder="Nama keluarga/kerabat">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">No. Telepon Kontak Darurat</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="uil uil-phone"></i></span>
                                <input type="text" name="emergency_phone" class="form-control"
                                    value="{{ old('emergency_phone') }}" placeholder="08xxxxxxxxxx">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Buttons -->
            <div class="d-flex gap-3 mb-4">
                <button type="submit" class="btn-submit">
                    <i class="uil uil-check me-2"></i>Simpan Data Pasien
                </button>
                <a href="{{ route('clinic.patients.index', app()->getLocale()) }}" class="btn-cancel">
                    <i class="uil uil-times me-2"></i>Batal
                </a>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const apiBaseUrl = 'https://www.emsifa.com/api-wilayah-indonesia/api';

            const provinsiSelect = document.getElementById('provinsi');
            const kabupatenSelect = document.getElementById('kabupaten');
            const kecamatanSelect = document.getElementById('kecamatan');
            const kelurahanSelect = document.getElementById('kelurahan');

            // Initialize Select2
            function initSelect2(selector, placeholder, tags = false) {
                $(selector).select2({
                    theme: 'bootstrap-5',
                    placeholder: placeholder,
                    allowClear: true,
                    width: '100%',
                    tags: tags // Allow manual input if true
                });
            }

            // Load Provinsi
            fetch(`${apiBaseUrl}/provinces.json`)
                .then(response => response.json())
                .then(provinces => {
                    provinces.forEach(province => {
                        const option = document.createElement('option');
                        option.value = province.name;
                        option.dataset.id = province.id;
                        option.textContent = province.name;
                        provinsiSelect.appendChild(option);
                    });
                    // Initialize Select2 after data loaded
                    initSelect2('#provinsi', 'Pilih Provinsi...');
                    initSelect2('#kabupaten', 'Pilih Kabupaten...');
                    initSelect2('#kecamatan', 'Pilih Kecamatan...');
                    initSelect2('#kelurahan', 'Pilih Kelurahan...', true); // Enable tags for Kelurahan
                })
                .catch(err => console.error('Error loading provinces:', err));

            // On Provinsi change
            $(provinsiSelect).on('change', function () {
                const selectedOption = this.options[this.selectedIndex];
                const provinceId = selectedOption ? selectedOption.dataset.id : null;

                // Reset dependent selects
                kabupatenSelect.innerHTML = '<option value="">Pilih Kabupaten...</option>';
                kecamatanSelect.innerHTML = '<option value="">Pilih Kecamatan...</option>';
                kelurahanSelect.innerHTML = '<option value="">Pilih Kelurahan...</option>';
                $('#kabupaten').trigger('change.select2');
                $('#kecamatan').trigger('change.select2');
                $('#kelurahan').trigger('change.select2');

                if (provinceId) {
                    kabupatenSelect.disabled = false;
                    fetch(`${apiBaseUrl}/regencies/${provinceId}.json`)
                        .then(response => response.json())
                        .then(regencies => {
                            regencies.forEach(regency => {
                                const option = document.createElement('option');
                                option.value = regency.name;
                                option.dataset.id = regency.id;
                                option.textContent = regency.name;
                                kabupatenSelect.appendChild(option);
                            });
                            $('#kabupaten').trigger('change.select2');
                        })
                        .catch(err => console.error('Error loading regencies:', err));
                } else {
                    kabupatenSelect.disabled = true;
                }
                updateFullAddress();
            });

            // On Kabupaten change
            $(kabupatenSelect).on('change', function () {
                const selectedOption = this.options[this.selectedIndex];
                const regencyId = selectedOption ? selectedOption.dataset.id : null;

                kecamatanSelect.innerHTML = '<option value="">Pilih Kecamatan...</option>';
                kelurahanSelect.innerHTML = '<option value="">Pilih Kelurahan...</option>';
                $('#kecamatan').trigger('change.select2');
                $('#kelurahan').trigger('change.select2');

                if (regencyId) {
                    kecamatanSelect.disabled = false;
                    fetch(`${apiBaseUrl}/districts/${regencyId}.json`)
                        .then(response => response.json())
                        .then(districts => {
                            districts.forEach(district => {
                                const option = document.createElement('option');
                                option.value = district.name;
                                option.dataset.id = district.id;
                                option.textContent = district.name;
                                kecamatanSelect.appendChild(option);
                            });
                            $('#kecamatan').trigger('change.select2');
                        })
                        .catch(err => console.error('Error loading districts:', err));
                } else {
                    kecamatanSelect.disabled = true;
                }
                updateFullAddress();
            });

            // On Kecamatan change
            $(kecamatanSelect).on('change', function () {
                const selectedOption = this.options[this.selectedIndex];
                const districtId = selectedOption ? selectedOption.dataset.id : null;

                kelurahanSelect.innerHTML = '<option value="">Pilih Kelurahan...</option>';
                $('#kelurahan').trigger('change.select2');

                if (districtId) {
                    kelurahanSelect.disabled = false;
                    fetch(`${apiBaseUrl}/villages/${districtId}.json`)
                        .then(response => response.json())
                        .then(villages => {
                            villages.forEach(village => {
                                const option = document.createElement('option');
                                option.value = village.name;
                                option.dataset.id = village.id;
                                option.textContent = village.name;
                                kelurahanSelect.appendChild(option);
                            });
                            $('#kelurahan').trigger('change.select2');
                        })
                        .catch(err => console.error('Error loading villages:', err));
                } else {
                    kelurahanSelect.disabled = true;
                }
                updateFullAddress();
            });

            $(kelurahanSelect).on('change', updateFullAddress);
            document.getElementById('address').addEventListener('input', updateFullAddress);

            function updateFullAddress() {
                const detail = document.getElementById('address').value;
                const kelurahan = kelurahanSelect.value;
                const kecamatan = kecamatanSelect.value;
                const kabupaten = kabupatenSelect.value;
                const provinsi = provinsiSelect.value;

                let fullAddress = detail;
                if (kelurahan) fullAddress += ', ' + kelurahan;
                if (kecamatan) fullAddress += ', Kec. ' + kecamatan;
                if (kabupaten) fullAddress += ', ' + kabupaten;
                if (provinsi) fullAddress += ', ' + provinsi;

                document.getElementById('full_address').value = fullAddress;
            }
        });
    </script>
@endpush