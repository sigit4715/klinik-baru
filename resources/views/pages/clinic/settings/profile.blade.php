@section('title', $title)
@section('description', $description)
@extends('layout.app')
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="breadcrumb-main">
                    <h4 class="text-capitalize breadcrumb-title">Profil Klinik</h4>
                    <div class="breadcrumb-action justify-content-center flex-wrap">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ route('clinic.dashboard', app()->getLocale()) }}"><i
                                            class="uil uil-estate"></i>Dashboard</a></li>
                                <li class="breadcrumb-item active">Profil Klinik</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h6><i class="uil uil-building me-2"></i>Informasi Klinik</h6>
                    </div>
                    <div class="card-body">
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="uil uil-check-circle me-2"></i>{{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <form action="{{ route('clinic.settings.profile.update', app()->getLocale()) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="mb-4">
                                <label class="form-label fw-500">Nama Klinik <span class="text-danger">*</span></label>
                                <input type="text" name="clinic_name" class="form-control form-control-lg"
                                    value="{{ old('clinic_name', $settings['clinic_name'] ?? '') }}"
                                    placeholder="Masukkan nama klinik" required>
                                <small class="text-muted">Nama ini akan tampil di struk pembayaran</small>
                                @error('clinic_name')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-500">Alamat Lengkap <span class="text-danger">*</span></label>
                                <textarea name="clinic_address" class="form-control" rows="3"
                                    placeholder="Masukkan alamat lengkap klinik"
                                    required>{{ old('clinic_address', $settings['clinic_address'] ?? '') }}</textarea>
                                @error('clinic_address')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <label class="form-label fw-500">No. Telepon</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="uil uil-phone"></i></span>
                                        <input type="text" name="clinic_phone" class="form-control"
                                            value="{{ old('clinic_phone', $settings['clinic_phone'] ?? '') }}"
                                            placeholder="021-1234567">
                                    </div>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <label class="form-label fw-500">No. WhatsApp</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="uil uil-whatsapp"></i></span>
                                        <input type="text" name="clinic_whatsapp" class="form-control"
                                            value="{{ old('clinic_whatsapp', $settings['clinic_whatsapp'] ?? '') }}"
                                            placeholder="08123456789">
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-500">Email</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="uil uil-envelope"></i></span>
                                    <input type="email" name="clinic_email" class="form-control"
                                        value="{{ old('clinic_email', $settings['clinic_email'] ?? '') }}"
                                        placeholder="info@klinik.com">
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-500">Footer Struk</label>
                                <textarea name="receipt_footer" class="form-control" rows="2"
                                    placeholder="Pesan yang tampil di bagian bawah struk">{{ old('receipt_footer', $settings['receipt_footer'] ?? '') }}</textarea>
                                <small class="text-muted">Contoh: "Terima kasih atas kunjungan Anda. Semoga lekas
                                    sembuh!"</small>
                            </div>

                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="uil uil-save me-2"></i>Simpan Perubahan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h6><i class="uil uil-eye me-2"></i>Preview Struk</h6>
                    </div>
                    <div class="card-body text-center p-4" style="background: #f8f9fa;">
                        <div class="receipt-preview p-4 bg-white border rounded">
                            <h5 class="mb-1" id="preview-name">{{ $settings['clinic_name'] ?? 'Nama Klinik' }}</h5>
                            <p class="small text-muted mb-2" id="preview-address">
                                {{ $settings['clinic_address'] ?? 'Alamat Klinik' }}</p>
                            <p class="small mb-3">
                                <span id="preview-phone">{{ $settings['clinic_phone'] ?? '-' }}</span> |
                                WA: <span id="preview-wa">{{ $settings['clinic_whatsapp'] ?? '-' }}</span>
                            </p>
                            <hr class="dashed">
                            <p class="text-start small mb-1"><strong>No. Invoice:</strong> INV-XXXXX</p>
                            <p class="text-start small mb-1"><strong>Tanggal:</strong> {{ date('d/m/Y H:i') }}</p>
                            <p class="text-start small mb-3"><strong>Pasien:</strong> Nama Pasien</p>
                            <hr class="dashed">
                            <p class="text-start small">Konsultasi Umum x1 = Rp 50.000</p>
                            <p class="text-start small">Paracetamol x2 = Rp 10.000</p>
                            <hr class="dashed">
                            <p class="text-end fw-bold">Total: Rp 60.000</p>
                            <hr class="dashed">
                            <p class="small text-muted mt-3" id="preview-footer">
                                {{ $settings['receipt_footer'] ?? 'Terima kasih!' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .receipt-preview {
            font-family: 'Courier New', monospace;
            font-size: 12px;
        }

        hr.dashed {
            border-style: dashed;
            border-color: #ccc;
        }
    </style>

    @push('scripts')
        <script>
            $(document).ready(function () {
                $('input[name="clinic_name"]').on('input', function () {
                    $('#preview-name').text($(this).val() || 'Nama Klinik');
                });
                $('textarea[name="clinic_address"]').on('input', function () {
                    $('#preview-address').text($(this).val() || 'Alamat Klinik');
                });
                $('input[name="clinic_phone"]').on('input', function () {
                    $('#preview-phone').text($(this).val() || '-');
                });
                $('input[name="clinic_whatsapp"]').on('input', function () {
                    $('#preview-wa').text($(this).val() || '-');
                });
                $('textarea[name="receipt_footer"]').on('input', function () {
                    $('#preview-footer').text($(this).val() || 'Terima kasih!');
                });
            });
        </script>
    @endpush
@endsection