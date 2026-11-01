@section('title', $title)
@section('description', $description)
@extends('layout.app')
@section('content')
    <div class="container-fluid">
        <div class="breadcrumb-main d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <h4 class="breadcrumb-title mb-0">Retur ke Supplier</h4>
            <a href="{{ route('clinic.purchase-returns.create', app()->getLocale()) }}" class="btn btn-primary btn-sm"><i class="uil uil-plus me-1"></i>Retur Baru</a>
        </div>
        <div class="card mb-3">
            <div class="card-body">
                <form method="GET" class="row g-2 align-items-end">
                    <div class="col-md-3"><label class="form-label small">Dari</label><input type="date" name="from" value="{{ $from->toDateString() }}" class="form-control form-control-sm"></div>
                    <div class="col-md-3"><label class="form-label small">Sampai</label><input type="date" name="to" value="{{ $to->toDateString() }}" class="form-control form-control-sm"></div>
                    <div class="col-md-2"><button class="btn btn-sm btn-primary w-100">Tampilkan</button></div>
                </form>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="bg-light"><tr><th>No.</th><th>Tanggal</th><th>Supplier</th><th>Faktur</th><th>Alasan</th><th class="text-end">Total</th><th></th></tr></thead>
                        <tbody>
                            @forelse($returns as $r)
                                <tr>
                                    <td><code>{{ $r->return_number }}</code></td>
                                    <td>{{ $r->return_date->format('d/m/Y') }}</td>
                                    <td>{{ $r->supplier?->name }}</td>
                                    <td>{{ $r->purchase?->purchase_number ?? '-' }}</td>
                                    <td class="small">{{ Str::limit($r->reason, 60) }}</td>
                                    <td class="text-end">Rp {{ number_format((float) $r->total, 0, ',', '.') }}</td>
                                    <td><a href="{{ route('clinic.purchase-returns.show', [app()->getLocale(), $r->id]) }}" class="btn btn-sm btn-outline-primary"><i class="uil uil-eye"></i></a></td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="text-center text-muted py-4">Belum ada retur pada periode ini.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $returns->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
@endsection
