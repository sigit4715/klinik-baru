@section('title', $title)
@section('description', $description)
@extends('layout.app')
@section('content')
    @php $rp = fn ($v) => 'Rp ' . number_format((float) $v, 0, ',', '.'); @endphp
    <div class="container-fluid">
        <div class="breadcrumb-main d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <h4 class="breadcrumb-title mb-0">Retur {{ $return->return_number }}</h4>
            <div class="d-flex gap-2">
                <button onclick="window.print()" class="btn btn-outline-secondary btn-sm"><i class="uil uil-print me-1"></i>Cetak</button>
                <a href="{{ route('clinic.purchase-returns.index', app()->getLocale()) }}" class="btn btn-light btn-sm">Kembali</a>
            </div>
        </div>
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        @endif
        <div class="card">
            <div class="card-body">
                <div class="row mb-3 small">
                    <div class="col-md-4"><strong>Supplier:</strong> {{ $return->supplier?->name }}</div>
                    <div class="col-md-3"><strong>Tanggal:</strong> {{ $return->return_date->format('d/m/Y') }}</div>
                    <div class="col-md-3"><strong>Faktur:</strong>
                        @if($return->purchase)<a href="{{ route('clinic.purchases.show', [app()->getLocale(), $return->purchase_id]) }}">{{ $return->purchase->purchase_number }}</a>@else - @endif</div>
                    <div class="col-md-2"><strong>Oleh:</strong> {{ $return->creator?->name ?? '-' }}</div>
                    <div class="col-12 mt-1"><strong>Alasan:</strong> {{ $return->reason }}</div>
                </div>
                <table class="table table-sm">
                    <thead class="bg-light"><tr><th>Obat</th><th class="text-end">Qty</th><th class="text-end">Harga</th><th class="text-end">Subtotal</th></tr></thead>
                    <tbody>
                        @foreach($return->items as $item)
                            <tr>
                                <td>{{ $item->medicine?->name }}</td>
                                <td class="text-end">{{ $item->qty }} {{ $item->medicine?->unit }}</td>
                                <td class="text-end">{{ $rp($item->price) }}</td>
                                <td class="text-end">{{ $rp($item->subtotal) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot><tr class="fw-bold"><td colspan="3" class="text-end">Total</td><td class="text-end">{{ $rp($return->total) }}</td></tr></tfoot>
                </table>
            </div>
        </div>
    </div>
@endsection
