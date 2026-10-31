@section('title', $title)
@section('description', $description)
@extends('layout.app')
@section('content')
    @php
        $eventColors = ['created' => 'success', 'updated' => 'primary', 'deleted' => 'danger', 'restored' => 'info',
            'login' => 'secondary', 'logout' => 'secondary', 'login_failed' => 'warning'];
        $formatValue = function ($value) {
            if (is_array($value)) {
                $value = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            } elseif (is_bool($value)) {
                $value = $value ? 'ya' : 'tidak';
            } elseif ($value === null || $value === '') {
                return '-';
            }
            return \Illuminate\Support\Str::limit((string) $value, 200);
        };
    @endphp
    <div class="container-fluid">
        <div class="breadcrumb-main">
            <h4 class="text-capitalize breadcrumb-title">{{ $title }}</h4>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <form method="GET" class="row g-2 align-items-end">
                    <div class="col-md-2">
                        <label class="form-label small">User</label>
                        <select name="user_id" class="form-select form-select-sm">
                            <option value="">Semua</option>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}" @selected(request('user_id') == $u->id)>{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">Aktivitas</label>
                        <select name="event" class="form-select form-select-sm">
                            <option value="">Semua</option>
                            @foreach($events as $key => $label)
                                <option value="{{ $key }}" @selected(request('event') === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">Data</label>
                        <select name="model" class="form-select form-select-sm">
                            <option value="">Semua</option>
                            @foreach($models as $class => $label)
                                <option value="{{ $class }}" @selected(request('model') === $class)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">Dari</label>
                        <input type="date" name="from" class="form-control form-control-sm" value="{{ request('from') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">Sampai</label>
                        <input type="date" name="to" class="form-control form-control-sm" value="{{ request('to') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">Cari</label>
                        <input type="search" name="search" class="form-control form-control-sm" value="{{ request('search') }}"
                            placeholder="Nama data / user">
                    </div>
                    <div class="col-12 d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm"><i class="uil uil-filter me-1"></i>Filter</button>
                        <a href="{{ route('clinic.audit-logs.index', app()->getLocale()) }}" class="btn btn-light btn-sm">Reset</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="bg-light">
                            <tr>
                                <th>Waktu</th>
                                <th>User</th>
                                <th>Aktivitas</th>
                                <th>Data</th>
                                <th>Keterangan</th>
                                <th>IP</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($logs as $log)
                                <tr>
                                    <td class="text-nowrap">{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                                    <td>{{ $log->user_name ?? 'Sistem' }}</td>
                                    <td><span class="badge bg-{{ $eventColors[$log->event] ?? 'secondary' }}">{{ $log->eventLabel() }}</span></td>
                                    <td>
                                        {{ $log->modelLabel() }}
                                        @if($log->auditable_id)
                                            <a href="{{ route('clinic.audit-logs.index', [app()->getLocale(), 'model' => $log->auditable_type, 'record_id' => $log->auditable_id]) }}"
                                                class="small text-muted" title="Lihat semua riwayat data ini">#{{ $log->auditable_id }}</a>
                                        @endif
                                    </td>
                                    <td>{{ $log->description ?? '-' }}</td>
                                    <td class="small text-muted">{{ $log->ip_address ?? '-' }}</td>
                                    <td>
                                        @if($log->old_values || $log->new_values)
                                            <button class="btn btn-sm btn-light" type="button" data-bs-toggle="collapse"
                                                data-bs-target="#log-{{ $log->id }}">Detail</button>
                                        @endif
                                    </td>
                                </tr>
                                @if($log->old_values || $log->new_values)
                                    <tr class="collapse" id="log-{{ $log->id }}">
                                        <td colspan="7" class="bg-light">
                                            <table class="table table-sm mb-0">
                                                <thead><tr><th style="width: 25%;">Kolom</th><th>Sebelum</th><th>Sesudah</th></tr></thead>
                                                <tbody>
                                                    @foreach(array_unique(array_merge(array_keys($log->old_values ?? []), array_keys($log->new_values ?? []))) as $field)
                                                        <tr>
                                                            <td><code>{{ $field }}</code></td>
                                                            <td class="text-danger">{{ $formatValue(($log->old_values ?? [])[$field] ?? null) }}</td>
                                                            <td class="text-success">{{ $formatValue(($log->new_values ?? [])[$field] ?? null) }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </td>
                                    </tr>
                                @endif
                            @empty
                                <tr><td colspan="7" class="text-center py-4 text-muted">Belum ada aktivitas</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $logs->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
@endsection
