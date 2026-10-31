@section('title', $title)
@section('description', $description)
@extends('layout.app')
@section('content')
    <div class="container-fluid">
        <div class="breadcrumb-main d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
            <h4 class="breadcrumb-title mb-0">Tarif Fee Tindakan</h4>
            <form method="GET" action="{{ route('clinic.service-fees.index', app()->getLocale()) }}" class="d-flex gap-2 align-items-center">
                <label class="small text-nowrap">Tarif untuk</label>
                <select name="doctor_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">Default semua dokter</option>
                    @foreach($doctors as $d)
                        <option value="{{ $d->id }}" @selected($doctor?->id === $d->id)>Khusus {{ $d->name }}</option>
                    @endforeach
                </select>
            </form>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif

        <div class="card">
            <div class="card-body">
                <p class="small text-muted">
                    @if($doctor)
                        Tarif khusus untuk <strong>{{ $doctor->name }}</strong>. Kosongkan nilai untuk memakai tarif default.
                    @else
                        Tarif default untuk semua dokter. Kosongkan nilai jika layanan tidak memberi fee. Fee hanya dihitung dari kunjungan yang sudah lunas.
                    @endif
                </p>
                <form method="POST" action="{{ route('clinic.service-fees.update', app()->getLocale()) }}">
                    @csrf
                    @if($doctor)<input type="hidden" name="doctor_id" value="{{ $doctor->id }}">@endif
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="bg-light">
                                <tr>
                                    <th>Layanan</th>
                                    <th>Harga</th>
                                    @if($doctor)<th>Default</th>@endif
                                    <th style="width: 220px">Jenis Fee</th>
                                    <th style="width: 200px">Nilai</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($services as $service)
                                    @php
                                        $rule = $doctor ? $overrides->get($service->id) : $defaults->get($service->id);
                                        $type = old("fees.{$service->id}.type", $rule?->fee_type ?? 'fixed');
                                        $value = old("fees.{$service->id}.value", $rule ? (float) $rule->fee_value : '');
                                    @endphp
                                    <tr>
                                        <td>{{ $service->name }}
                                            @if($service->trashed() || $service->status !== 'active')<span class="badge bg-secondary">nonaktif</span>@endif
                                        </td>
                                        <td>Rp {{ number_format($service->price, 0, ',', '.') }}</td>
                                        @if($doctor)<td class="small">{{ $defaults->get($service->id)?->label() ?? '-' }}</td>@endif
                                        <td>
                                            <select name="fees[{{ $service->id }}][type]" class="form-select form-select-sm">
                                                @foreach(\App\Models\ServiceFee::TYPES as $key => $label)
                                                    <option value="{{ $key }}" @selected($type === $key)>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td><input type="number" step="0.01" min="0" name="fees[{{ $service->id }}][value]" value="{{ $value }}" class="form-control form-control-sm" placeholder="{{ $doctor ? 'pakai default' : 'tidak ada fee' }}"></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-muted py-4">Belum ada layanan.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="uil uil-check me-1"></i>Simpan Tarif</button>
                    <a href="{{ route('clinic.salary-profiles.index', app()->getLocale()) }}" class="btn btn-light">Kembali</a>
                </form>
            </div>
        </div>
    </div>
@endsection
