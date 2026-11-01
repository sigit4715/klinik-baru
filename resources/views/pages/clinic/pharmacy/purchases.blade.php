@section('title', $title)
@section('description', $description)
@extends('layout.app')
@section('content')
    <div class="container-fluid">
        <div class="breadcrumb-main d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <h4 class="breadcrumb-title mb-0">Penerimaan Barang</h4>
            <div class="d-flex gap-2">
                @can('purchases.return')
                    <a href="{{ route('clinic.purchase-returns.index', app()->getLocale()) }}" class="btn btn-outline-secondary btn-sm"><i class="uil uil-redo me-1"></i>Retur ke Supplier</a>
                @endcan
                <a href="{{ route('clinic.purchases.create', app()->getLocale()) }}" class="btn btn-primary btn-sm"><i class="uil uil-plus me-1"></i>Terima Barang</a>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <form method="GET" class="row g-2 align-items-end">
                    <div class="col-md-3"><label class="form-label small">Dari</label><input type="date" name="from" value="{{ $from->toDateString() }}" class="form-control form-control-sm"></div>
                    <div class="col-md-3"><label class="form-label small">Sampai</label><input type="date" name="to" value="{{ $to->toDateString() }}" class="form-control form-control-sm"></div>
                    <div class="col-md-4"><label class="form-label small">Supplier</label>
                        <select name="supplier_id" class="form-select form-select-sm">
                            <option value="">Semua supplier</option>
                            @foreach($suppliers as $s)<option value="{{ $s->id }}" @selected($supplierId === $s->id)>{{ $s->name }}</option>@endforeach
                        </select></div>
                    <div class="col-md-2"><button class="btn btn-sm btn-primary w-100">Tampilkan</button></div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="bg-light"><tr><th>No.</th><th>Tanggal</th><th>Supplier</th><th>No. Faktur</th><th>Item</th><th class="text-end">Total</th><th>Oleh</th><th></th></tr></thead>
                        <tbody>
                            @forelse($purchases as $p)
                                <tr>
                                    <td><code>{{ $p->purchase_number }}</code></td>
                                    <td>{{ $p->purchase_date->format('d/m/Y') }}</td>
                                    <td>{{ $p->supplier?->name }}</td>
                                    <td>{{ $p->invoice_number ?: '-' }}</td>
                                    <td>{{ $p->items_count }}</td>
                                    <td class="text-end">Rp {{ number_format((float) $p->total, 0, ',', '.') }}</td>
                                    <td class="small">{{ $p->creator?->name }}</td>
                                    <td><a href="{{ route('clinic.purchases.show', [app()->getLocale(), $p->id]) }}" class="btn btn-sm btn-outline-primary"><i class="uil uil-eye"></i></a></td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="text-center text-muted py-4">Belum ada penerimaan pada periode ini.</td></tr>
                            @endforelse
                        </tbody>
                        @if($purchases->count())
                            <tfoot><tr class="fw-bold"><td colspan="5">Total halaman ini</td><td class="text-end">Rp {{ number_format($purchases->sum('total'), 0, ',', '.') }}</td><td colspan="2"></td></tr></tfoot>
                        @endif
                    </table>
                </div>
                {{ $purchases->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
@endsection
