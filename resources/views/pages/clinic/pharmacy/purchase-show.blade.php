@section('title', $title)
@section('description', $description)
@extends('layout.app')
@section('content')
    @php $rp = fn ($v) => 'Rp ' . number_format((float) $v, 0, ',', '.'); @endphp
    <div class="container-fluid">
        <div class="breadcrumb-main d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <h4 class="breadcrumb-title mb-0">Penerimaan {{ $purchase->purchase_number }}</h4>
            <div class="d-flex gap-2 no-print">
                @can('purchases.return')
                    <a href="{{ route('clinic.purchase-returns.create', [app()->getLocale(), 'purchase_id' => $purchase->id]) }}" class="btn btn-outline-danger btn-sm"><i class="uil uil-redo me-1"></i>Retur dari Faktur Ini</a>
                @endcan
                <button onclick="window.print()" class="btn btn-outline-secondary btn-sm"><i class="uil uil-print me-1"></i>Cetak</button>
                <a href="{{ route('clinic.purchases.index', app()->getLocale()) }}" class="btn btn-light btn-sm">Kembali</a>
            </div>
        </div>
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        @endif
        <div class="card">
            <div class="card-body">
                <div class="row mb-3 small">
                    <div class="col-md-4"><strong>Supplier:</strong> {{ $purchase->supplier?->name }}</div>
                    <div class="col-md-3"><strong>No. Faktur:</strong> {{ $purchase->invoice_number ?: '-' }}</div>
                    <div class="col-md-2"><strong>Tanggal:</strong> {{ $purchase->purchase_date->format('d/m/Y') }}</div>
                    <div class="col-md-3"><strong>Diterima oleh:</strong> {{ $purchase->creator?->name ?? '-' }}</div>
                    @if($purchase->notes)<div class="col-12 mt-1"><strong>Catatan:</strong> {{ $purchase->notes }}</div>@endif
                </div>
                <table class="table table-sm">
                    <thead class="bg-light"><tr><th>Obat</th><th class="text-end">Qty</th><th class="text-end">Harga Beli</th><th>Kedaluwarsa</th><th>Batch</th><th class="text-end">Subtotal</th></tr></thead>
                    <tbody>
                        @foreach($purchase->items as $item)
                            <tr>
                                <td><a href="{{ route('clinic.medicines.stock-card', [app()->getLocale(), $item->medicine_id]) }}">{{ $item->medicine?->name }}</a></td>
                                <td class="text-end">{{ $item->qty }} {{ $item->medicine?->unit }}</td>
                                <td class="text-end">{{ $rp($item->buy_price) }}</td>
                                <td>{{ $item->expired_date?->format('d/m/Y') ?? '-' }}</td>
                                <td>{{ $item->batch_number ?: '-' }}</td>
                                <td class="text-end">{{ $rp($item->subtotal) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot><tr class="fw-bold"><td colspan="5" class="text-end">Total</td><td class="text-end">{{ $rp($purchase->total) }}</td></tr></tfoot>
                </table>
                @if($purchase->returns->isNotEmpty())
                    <p class="small mb-0"><strong>Retur dari faktur ini:</strong>
                        @foreach($purchase->returns as $r)
                            <a href="{{ route('clinic.purchase-returns.show', [app()->getLocale(), $r->id]) }}">{{ $r->return_number }}</a> ({{ $rp($r->total) }}){{ $loop->last ? '' : ',' }}
                        @endforeach
                    </p>
                @endif
            </div>
        </div>
    </div>
@endsection
