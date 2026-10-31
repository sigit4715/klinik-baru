@section('title', $title)
@section('description', $description)
@extends('layout.app')
@section('content')
    <div class="container-fluid">
        <div class="breadcrumb-main d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h4 class="text-capitalize breadcrumb-title">{{ $title }}: {{ $medicine->name }}</h4>
            <a href="{{ route('clinic.medicines.index', app()->getLocale()) }}" class="btn btn-light btn-sm">
                <i class="uil uil-arrow-left me-1"></i>Daftar Obat
            </a>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="uil uil-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif
        @if($medicine->trashed())
            <div class="alert alert-warning">Obat ini ada di Arsip. Kartu stok hanya bisa dilihat.</div>
        @endif

        <div class="row">
            <div class="col-lg-4">
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="text-muted small">{{ $medicine->code }} &middot; {{ $medicine->category }}</div>
                        <h2 class="my-2 {{ $medicine->stock <= $medicine->min_stock ? 'text-danger' : 'text-success' }}">
                            {{ $medicine->stock }} <small class="fs-6 text-muted">{{ $medicine->unit }}</small>
                        </h2>
                        <div class="small">Stok minimum: {{ $medicine->min_stock }} {{ $medicine->unit }}</div>
                        <div class="small">Kedaluwarsa: {{ $medicine->expired_date?->format('d/m/Y') ?? '-' }}</div>
                    </div>
                </div>

                @can('medicines.stock')
                    @unless($medicine->trashed())
                        <div class="card mb-4">
                            <div class="card-header"><h6><i class="uil uil-plus-circle me-2"></i>Catat Perubahan Stok</h6></div>
                            <div class="card-body">
                                <form method="POST" action="{{ route('clinic.medicines.stock-movements.store', [app()->getLocale(), $medicine->id]) }}">
                                    @csrf
                                    <div class="mb-3">
                                        <label class="form-label">Jenis <span class="text-danger">*</span></label>
                                        <select name="type" id="movementType" class="form-select" required>
                                            @foreach($manualTypes as $key => $label)
                                                <option value="{{ $key }}" @selected(old('type', 'in') === $key)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label" id="quantityLabel">Jumlah</label>
                                        <input type="number" name="quantity" class="form-control" min="0" required value="{{ old('quantity') }}">
                                        <small class="text-muted" id="quantityHelp"></small>
                                    </div>
                                    <div class="mb-3 only-in">
                                        <label class="form-label">Tanggal kedaluwarsa batch baru</label>
                                        <input type="date" name="expired_date" class="form-control" value="{{ old('expired_date') }}">
                                    </div>
                                    <div class="mb-3 only-in">
                                        <label class="form-label">Harga beli baru (Rp)</label>
                                        <input type="number" name="buy_price" class="form-control" min="0" step="1" value="{{ old('buy_price') }}">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Keterangan <span class="text-danger">*</span></label>
                                        <input type="text" name="notes" class="form-control" maxlength="250" required
                                            value="{{ old('notes') }}" placeholder="No. faktur, supplier, atau alasan">
                                    </div>
                                    <button type="submit" class="btn btn-primary w-100"><i class="uil uil-check me-1"></i>Simpan</button>
                                </form>
                            </div>
                        </div>
                    @endunless
                @endcan
            </div>

            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <form method="GET" class="row g-2 mb-3">
                            <div class="col-md-3"><input type="date" name="from" class="form-control form-control-sm" value="{{ request('from') }}" title="Dari"></div>
                            <div class="col-md-3"><input type="date" name="to" class="form-control form-control-sm" value="{{ request('to') }}" title="Sampai"></div>
                            <div class="col-md-4">
                                <select name="type" class="form-select form-select-sm">
                                    <option value="">Semua jenis</option>
                                    @foreach($types as $key => $label)
                                        <option value="{{ $key }}" @selected(request('type') === $key)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2"><button class="btn btn-sm btn-primary w-100" type="submit">Filter</button></div>
                        </form>

                        <div class="table-responsive">
                            <table class="table table-hover table-sm align-middle">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Jenis</th>
                                        <th class="text-end">Masuk</th>
                                        <th class="text-end">Keluar</th>
                                        <th class="text-end">Saldo</th>
                                        <th>Keterangan</th>
                                        <th>Oleh</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($movements as $movement)
                                        <tr>
                                            <td class="text-nowrap">{{ $movement->created_at->format('d/m/Y H:i') }}</td>
                                            <td>{{ $movement->typeLabel() }}</td>
                                            <td class="text-end text-success">{{ $movement->quantity > 0 ? $movement->quantity : '' }}</td>
                                            <td class="text-end text-danger">{{ $movement->quantity < 0 ? abs($movement->quantity) : '' }}</td>
                                            <td class="text-end fw-bold">{{ $movement->stock_after }}</td>
                                            <td>
                                                {{ $movement->notes }}
                                                @if($movement->reference instanceof \App\Models\MedicalRecord)
                                                    @can('medical_records.view')
                                                        <a href="{{ route('clinic.medical-records.show', [app()->getLocale(), $movement->reference_id]) }}" class="small">(rekam medis)</a>
                                                    @endcan
                                                @elseif($movement->reference instanceof \App\Models\PaymentRefund)
                                                    @can('payments.view')
                                                        <a href="{{ route('clinic.payments.show', [app()->getLocale(), $movement->reference->payment_id]) }}" class="small">(pembayaran)</a>
                                                    @endcan
                                                @endif
                                            </td>
                                            <td class="small">{{ $movement->user?->name ?? 'Sistem' }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="7" class="text-center py-4 text-muted">Belum ada pergerakan stok</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        {{ $movements->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const type = document.getElementById('movementType');
            if (!type) return;
            const label = document.getElementById('quantityLabel');
            const help = document.getElementById('quantityHelp');
            const texts = {
                in: ['Jumlah masuk', 'Stok bertambah sebanyak jumlah ini.'],
                return: ['Jumlah retur', 'Obat yang dikembalikan ke stok.'],
                adjustment: ['Stok fisik hasil hitung', 'Isi jumlah sebenarnya di rak; selisih dengan stok sistem dicatat otomatis.'],
                disposal: ['Jumlah dibuang', 'Stok berkurang (rusak/kedaluwarsa).'],
            };
            function update() {
                const t = texts[type.value] || texts.in;
                label.textContent = t[0];
                help.textContent = t[1];
                document.querySelectorAll('.only-in').forEach(el => el.style.display = type.value === 'in' ? '' : 'none');
            }
            type.addEventListener('change', update);
            update();
        });
    </script>
@endsection
