@section('title', $title)
@section('description', $description)
@extends('layout.app')
@section('content')
    <div class="container-fluid">
        <div class="breadcrumb-main d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
            <h4 class="breadcrumb-title mb-0">Surat Medis</h4>
            @can('letters.manage')
                <div class="d-flex gap-2 flex-wrap">
                    @foreach(\App\Models\MedicalLetter::TYPES as $key => $label)
                        <a href="{{ route('clinic.letters.create', [app()->getLocale(), 'type' => $key]) }}" class="btn btn-sm btn-{{ \App\Models\MedicalLetter::TYPE_BADGES[$key] }}">
                            <i class="uil uil-plus me-1"></i>{{ $label }}
                        </a>
                    @endforeach
                </div>
            @endcan
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('success') }}
                @if(session('print_letter_id'))
                    <a href="{{ route('clinic.letters.print', [app()->getLocale(), session('print_letter_id')]) }}" target="_blank" class="btn btn-sm btn-success ms-2"><i class="uil uil-print me-1"></i>Cetak Surat</a>
                @endif
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif

        <div class="card">
            <div class="card-body">
                <form method="GET" class="row g-2 mb-3">
                    <div class="col-md-3">
                        <select name="type" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">Semua jenis</option>
                            @foreach(\App\Models\MedicalLetter::TYPES as $key => $label)
                                <option value="{{ $key }}" @selected($type === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <input type="search" name="search" value="{{ $search }}" class="form-control form-control-sm" placeholder="Cari nomor surat / nama / NIK / No. RM">
                    </div>
                    <div class="col-md-2"><button class="btn btn-sm btn-primary w-100">Cari</button></div>
                </form>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="bg-light">
                            <tr><th>Nomor</th><th>Tanggal</th><th>Jenis</th><th>Pasien</th><th>Dokter</th><th>Keterangan</th><th>Aksi</th></tr>
                        </thead>
                        <tbody>
                            @forelse($letters as $letter)
                                <tr class="{{ $letter->isVoid() ? 'text-muted' : '' }}">
                                    <td><code>{{ $letter->letter_number }}</code>
                                        @if($letter->isVoid())<br><span class="badge bg-danger" title="{{ $letter->void_reason }}">Dibatalkan</span>@endif
                                    </td>
                                    <td>{{ $letter->issued_date->format('d/m/Y') }}</td>
                                    <td><span class="badge bg-{{ \App\Models\MedicalLetter::TYPE_BADGES[$letter->type] ?? 'secondary' }}">{{ $letter->typeLabel() }}</span></td>
                                    <td>{{ $letter->patient?->name }}<br><small class="text-muted">{{ $letter->patient?->no_rm }}</small></td>
                                    <td>{{ $letter->doctor?->name }}</td>
                                    <td class="small">
                                        @if($letter->type === 'healthy') {{ $letter->purpose }}
                                        @elseif($letter->type === 'sick') Istirahat {{ $letter->rest_days }} hari ({{ $letter->rest_from?->format('d/m') }} - {{ $letter->rest_until?->format('d/m/Y') }})
                                        @else Ke {{ $letter->referred_to }}{{ $letter->referred_department ? ' - ' . $letter->referred_department : '' }}
                                        @endif
                                    </td>
                                    <td class="text-nowrap">
                                        <a href="{{ route('clinic.letters.print', [app()->getLocale(), $letter->id]) }}" target="_blank" class="btn btn-sm btn-outline-secondary" title="Cetak"><i class="uil uil-print"></i></a>
                                        @can('letters.manage')
                                            @unless($letter->isVoid())
                                                <a href="{{ route('clinic.letters.edit', [app()->getLocale(), $letter->id]) }}" class="btn btn-sm btn-outline-primary" title="Edit"><i class="uil uil-edit"></i></a>
                                                <form method="POST" action="{{ route('clinic.letters.void', [app()->getLocale(), $letter->id]) }}" class="d-inline"
                                                    onsubmit="const r = prompt('Alasan pembatalan surat {{ $letter->letter_number }}:'); if (!r) return false; this.void_reason.value = r; return true;">
                                                    @csrf
                                                    <input type="hidden" name="void_reason">
                                                    <button class="btn btn-sm btn-outline-danger" title="Batalkan"><i class="uil uil-ban"></i></button>
                                                </form>
                                            @endunless
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="text-center text-muted py-4">Belum ada surat.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $letters->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
@endsection
