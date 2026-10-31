@section('title', $title)
@section('description', $description)
@extends('layout.app')
@section('content')
    @php
        $money = fn ($field) => old($field, $profile->{$field} !== null ? (float) $profile->{$field} : 0);
    @endphp
    <div class="container-fluid">
        <div class="breadcrumb-main">
            <h4 class="breadcrumb-title">Pengaturan Gaji: {{ $employee->name }} <small class="text-muted">{{ $employee->roleLabel() }}</small></h4>
        </div>
        <div class="row">
            <div class="col-lg-7">
                @if($errors->any())
                    <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
                @endif
                <div class="card">
                    <div class="card-body">
                        <form method="POST" action="{{ route('clinic.salary-profiles.update', [app()->getLocale(), $employee->id]) }}">
                            @csrf @method('PUT')
                            <h6 class="mb-3">Pendapatan</h6>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Gaji Pokok per Bulan</label>
                                    <div class="input-group"><span class="input-group-text">Rp</span>
                                        <input type="number" step="1" min="0" name="base_salary" class="form-control" value="{{ $money('base_salary') }}"></div>
                                    <small class="text-muted">Isi 0 jika dokter hanya dibayar uang duduk + fee.</small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Uang Duduk per Shift Hadir</label>
                                    <div class="input-group"><span class="input-group-text">Rp</span>
                                        <input type="number" step="1" min="0" name="sitting_fee" class="form-control" value="{{ $money('sitting_fee') }}"></div>
                                    <small class="text-muted">Dihitung dari absensi (hadir atau terlambat tetap penuh).</small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Tunjangan Tetap per Bulan</label>
                                    <div class="input-group"><span class="input-group-text">Rp</span>
                                        <input type="number" step="1" min="0" name="fixed_allowance" class="form-control" value="{{ $money('fixed_allowance') }}"></div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Keterangan Tunjangan</label>
                                    <input type="text" name="allowance_note" class="form-control" maxlength="255" placeholder="Transport + makan" value="{{ old('allowance_note', $profile->allowance_note) }}">
                                </div>
                            </div>
                            <div class="form-check form-switch mb-4">
                                <input type="checkbox" class="form-check-input" id="earnsFee" name="earns_service_fee" value="1" @checked(old('earns_service_fee', $profile->earns_service_fee))>
                                <label class="form-check-label" for="earnsFee">Dapat fee tindakan (dokter/bidan)</label>
                                <div class="small text-muted">
                                    @if($employee->doctor)
                                        Terhubung ke data dokter <strong>{{ $employee->doctor->name }}</strong>. Tarif diatur di <a href="{{ route('clinic.service-fees.index', app()->getLocale()) }}">Tarif Fee Tindakan</a>.
                                    @else
                                        <span class="text-warning">Akun ini belum dihubungkan ke data dokter (Manajemen User → Edit), jadi fee tindakan belum bisa dihitung.</span>
                                    @endif
                                </div>
                            </div>

                            <h6 class="mb-3">Potongan</h6>
                            <div class="form-check form-switch mb-3">
                                <input type="checkbox" class="form-check-input" id="deductAbsence" name="deduct_absence" value="1" @checked(old('deduct_absence', $profile->deduct_absence))>
                                <label class="form-check-label" for="deductAbsence">Potong gaji pokok untuk Alpa</label>
                                <div class="small text-muted">Potongan = gaji pokok ÷ jumlah shift terjadwal × shift alpa. Izin, sakit, dan cuti tidak dipotong.</div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Potongan Terlambat per Kejadian</label>
                                    <div class="input-group"><span class="input-group-text">Rp</span>
                                        <input type="number" step="1" min="0" name="late_fee_per_incident" class="form-control" value="{{ $money('late_fee_per_incident') }}"></div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Potongan Terlambat per Menit</label>
                                    <div class="input-group"><span class="input-group-text">Rp</span>
                                        <input type="number" step="1" min="0" name="late_fee_per_minute" class="form-control" value="{{ $money('late_fee_per_minute') }}"></div>
                                </div>
                            </div>
                            <div class="mb-4">
                                <label class="form-label">Catatan</label>
                                <input type="text" name="notes" class="form-control" maxlength="255" value="{{ old('notes', $profile->notes) }}">
                            </div>

                            <button type="submit" class="btn btn-primary"><i class="uil uil-check me-1"></i>Simpan</button>
                            <a href="{{ route('clinic.salary-profiles.index', app()->getLocale()) }}" class="btn btn-light">Batal</a>
                        </form>
                        @if($profile->exists)
                            <form method="POST" action="{{ route('clinic.salary-profiles.update', [app()->getLocale(), $employee->id]) }}" class="mt-3"
                                onsubmit="return confirm('Hapus pengaturan gaji karyawan ini? Slip yang sudah dikunci tidak berubah.')">
                                @csrf @method('PUT')
                                <input type="hidden" name="remove" value="1">
                                <button class="btn btn-sm btn-outline-danger">Hapus Pengaturan Gaji</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
