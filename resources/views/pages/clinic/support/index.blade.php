@section('title', $title)
@section('description', $description)
@extends('layout.app')
@section('content')
    <div class="container-fluid">
        <div class="breadcrumb-main d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
            <h4 class="breadcrumb-title mb-0">Poli Penunjang</h4>
            <form method="GET" class="d-flex gap-2 flex-wrap">
                <input type="date" name="date" value="{{ $date->toDateString() }}" class="form-control form-control-sm" onchange="this.form.submit()">
                <select name="poli_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">Semua unit</option>
                    @foreach($polis as $poli)
                        <option value="{{ $poli->id }}" @selected($poliId === $poli->id)>{{ $poli->name }}</option>
                    @endforeach
                </select>
                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">Semua (kecuali batal)</option>
                    @foreach(\App\Models\SupportOrder::STATUS_LABELS as $key => $label)
                        <option value="{{ $key }}" @selected($status === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </form>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        @endif

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="bg-light">
                            <tr><th>Jam</th><th>Antrian</th><th>Pasien</th><th>Pemeriksaan</th><th>Dari</th><th>Status</th><th>File</th><th>Aksi</th></tr>
                        </thead>
                        <tbody>
                            @forelse($orders as $order)
                                <tr>
                                    <td>{{ $order->created_at->format('H:i') }}</td>
                                    <td><code>{{ $order->appointment?->queue_number }}</code></td>
                                    <td>{{ $order->patient?->name }}<br><small class="text-muted">{{ $order->patient?->no_rm }}</small></td>
                                    <td>{{ $order->service?->name }}<br><small class="text-muted">{{ $order->poli?->name }}</small>
                                        @if($order->notes)<br><small class="text-muted"><i class="uil uil-comment-alt"></i> {{ Str::limit($order->notes, 60) }}</small>@endif
                                    </td>
                                    <td class="small">{{ \App\Models\SupportOrder::SOURCES[$order->ordered_from] ?? '-' }}<br><span class="text-muted">{{ $order->orderedBy?->name }}</span></td>
                                    <td><span class="badge bg-{{ $order->statusBadge() }}">{{ $order->statusLabel() }}</span></td>
                                    <td>{{ $order->files->count() ?: '-' }}</td>
                                    <td class="text-nowrap">
                                        @can('support.manage')
                                            @if($order->status !== 'cancelled')
                                                <a href="{{ route('clinic.support-orders.edit', [app()->getLocale(), $order->id]) }}" class="btn btn-sm {{ $order->status === 'done' ? 'btn-outline-primary' : 'btn-primary' }}">
                                                    <i class="uil uil-flask"></i> {{ $order->status === 'done' ? 'Lihat/Ubah' : 'Input Hasil' }}
                                                </a>
                                            @endif
                                        @endcan
                                        @can('appointments.view')
                                            <a href="{{ route('clinic.appointments.show', [app()->getLocale(), $order->appointment_id]) }}" class="btn btn-sm btn-outline-info" title="Kunjungan"><i class="uil uil-eye"></i></a>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="text-center text-muted py-4">Tidak ada pemeriksaan penunjang pada tanggal ini.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
