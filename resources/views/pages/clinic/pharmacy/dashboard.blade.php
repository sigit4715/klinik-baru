@section('title', $title)
@section('description', $description)
@extends('layout.app')
@section('content')
    @php $rp = fn ($v) => 'Rp ' . number_format((float) $v, 0, ',', '.'); @endphp
    <div class="container-fluid">
        <div class="breadcrumb-main d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <h4 class="breadcrumb-title mb-0">Dashboard Farmasi</h4>
            <div class="d-flex gap-2 flex-wrap">
                @can('purchases.manage')<a href="{{ route('clinic.purchases.create', app()->getLocale()) }}" class="btn btn-primary btn-sm"><i class="uil uil-truck me-1"></i>Terima Barang</a>@endcan
                @can('purchases.return')<a href="{{ route('clinic.purchase-returns.create', app()->getLocale()) }}" class="btn btn-outline-danger btn-sm"><i class="uil uil-redo me-1"></i>Retur</a>@endcan
                <a href="{{ route('clinic.medicines.index', app()->getLocale()) }}" class="btn btn-outline-secondary btn-sm">Daftar Obat</a>
            </div>
        </div>

        <div class="row">
            @foreach([
                ['Stok Menipis', $lowStock->count(), 'warning', 'uil-arrow-down'],
                ["Kedaluwarsa ≤ {$days} hari", $expiring->count(), 'info', 'uil-clock'],
                ['Sudah Kedaluwarsa', $expired->count(), 'danger', 'uil-exclamation-triangle'],
                ['Nilai Persediaan', $rp($stockValue), 'success', 'uil-money-bill'],
            ] as [$label, $value, $color, $icon])
                <div class="col-md-3 col-6 mb-3">
                    <div class="card h-100"><div class="card-body d-flex align-items-center gap-3">
                        <i class="uil {{ $icon }} fs-1 text-{{ $color }}"></i>
                        <div><div class="fs-4 fw-bold">{{ $value }}</div><div class="small text-muted">{{ $label }}</div></div>
                    </div></div>
                </div>
            @endforeach
        </div>

        <div class="row">
            <div class="col-lg-6 mb-3">
                <div class="card h-100">
                    <div class="card-header">Perlu Perhatian</div>
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0">
                            <thead class="bg-light"><tr><th class="ps-3">Obat</th><th>Stok</th><th>Kedaluwarsa</th><th></th></tr></thead>
                            <tbody>
                                @forelse($expired->concat($expiring)->concat($lowStock)->unique('id')->take(20) as $m)
                                    <tr>
                                        <td class="ps-3">{{ $m->name }} @if($m->rack_location)<small class="text-muted">· rak {{ $m->rack_location }}</small>@endif</td>
                                        <td class="{{ $m->stock <= $m->min_stock ? 'text-danger fw-bold' : '' }}">{{ $m->stock }} / min {{ $m->min_stock }}</td>
                                        <td class="{{ $m->expired_date && $m->expired_date->lt(today()) ? 'text-danger fw-bold' : '' }}">{{ $m->expired_date?->format('d/m/Y') ?? '-' }}</td>
                                        <td><a href="{{ route('clinic.medicines.stock-card', [app()->getLocale(), $m->id]) }}" class="btn btn-sm btn-outline-info py-0">Kartu Stok</a></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center text-muted py-3">Semua stok aman.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 mb-3">
                <div class="card h-100">
                    <div class="card-header">Resep Hari Ini ({{ $todayRecords->count() }})</div>
                    <div class="card-body small">
                        @forelse($todayRecords as $record)
                            <div class="{{ $loop->last ? '' : 'border-bottom pb-2 mb-2' }}">
                                <div class="d-flex justify-content-between">
                                    <strong>{{ $record->patient?->name }}</strong>
                                    <span class="text-muted">{{ $record->created_at->format('H:i') }} · {{ $record->appointment?->queue_number }}</span>
                                </div>
                                <div class="text-muted">{{ $record->doctor?->name }}</div>
                                @foreach($record->getPrescriptionDetails() as $line)
                                    <div>- {{ $line['medicine']->name }} × {{ $line['qty'] }} {{ $line['dosage'] ? '(' . $line['dosage'] . ')' : '' }}</div>
                                @endforeach
                                @foreach($record->getCompoundDetails() as $racik)
                                    <div>- Racikan {{ $racik['name'] }} × {{ $racik['qty'] }}: {{ collect($racik['components'])->map(fn ($x) => $x['medicine']->name . ' ' . $x['qty'])->implode(', ') }}</div>
                                @endforeach
                            </div>
                        @empty
                            <p class="text-muted mb-0">Belum ada resep hari ini.</p>
                        @endforelse
                    </div>
                </div>
            </div>
            <div class="col-lg-6 mb-3">
                <div class="card h-100">
                    <div class="card-header">Obat Paling Sering Keluar (30 hari)</div>
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0">
                            <tbody>
                                @forelse($topOut as $row)
                                    <tr><td class="ps-3">{{ $row->medicine?->name }}</td><td class="text-end pe-3">{{ (int) $row->qty }} {{ $row->medicine?->unit }}</td></tr>
                                @empty
                                    <tr><td class="text-center text-muted py-3">Belum ada data.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 mb-3">
                <div class="card h-100">
                    <div class="card-header">Penerimaan Barang Terakhir</div>
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0">
                            <tbody>
                                @forelse($recentPurchases as $p)
                                    <tr>
                                        <td class="ps-3">{{ $p->purchase_date->format('d/m/Y') }}</td>
                                        <td>{{ $p->supplier?->name }}</td>
                                        <td class="text-end">{{ $rp($p->total) }}</td>
                                        <td class="pe-3">@can('purchases.manage')<a href="{{ route('clinic.purchases.show', [app()->getLocale(), $p->id]) }}">Lihat</a>@endcan</td>
                                    </tr>
                                @empty
                                    <tr><td class="text-center text-muted py-3">Belum ada penerimaan.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
