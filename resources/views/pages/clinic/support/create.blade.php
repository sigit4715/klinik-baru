@section('title', $title)
@section('description', $description)
@extends('layout.app')
@section('content')
    <div class="container-fluid">
        <div class="breadcrumb-main">
            <h4 class="breadcrumb-title">Order Penunjang <small class="text-muted">{{ $appointment->queue_number }} - {{ $appointment->patient?->name }}</small></h4>
        </div>
        <div class="row">
            <div class="col-lg-8">
                @if($errors->any())
                    <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
                @endif
                <div class="card">
                    <div class="card-body">
                        @if($services->isEmpty())
                            <p class="text-muted mb-0">Belum ada layanan berkategori <em>Penunjang</em>. Tambahkan di Master Data → Layanan/Tindakan (kategori Penunjang).</p>
                        @else
                            <form method="POST" action="{{ route('clinic.support-orders.store', [app()->getLocale(), $appointment->id]) }}">
                                @csrf
                                <input type="hidden" name="return_to" value="{{ old('return_to', url()->previous()) }}">
                                <label class="form-label">Pilih Pemeriksaan <span class="text-danger">*</span></label>
                                @foreach($services->groupBy(fn ($s) => $s->poli?->name ?? 'Lainnya') as $group => $items)
                                    <div class="mb-3">
                                        <div class="fw-500 text-muted small mb-1">{{ $group }}</div>
                                        @foreach($items as $service)
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="services[]" value="{{ $service->id }}" id="svc{{ $service->id }}"
                                                    @checked(in_array($service->id, old('services', [])))>
                                                <label class="form-check-label" for="svc{{ $service->id }}">{{ $service->name }} <span class="text-muted">- Rp {{ number_format($service->price, 0, ',', '.') }}</span></label>
                                            </div>
                                        @endforeach
                                    </div>
                                @endforeach
                                <div class="mb-3">
                                    <label class="form-label">Catatan / Indikasi</label>
                                    <textarea name="notes" class="form-control" rows="2" placeholder="Contoh: curiga DHF, cek trombosit">{{ old('notes') }}</textarea>
                                </div>
                                <button class="btn btn-primary"><i class="uil uil-check me-1"></i>Pesan</button>
                                <a href="{{ url()->previous() }}" class="btn btn-light">Batal</a>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                @include('pages.clinic.support._card', ['appointment' => $appointment, 'showOrderButton' => false])
            </div>
        </div>
    </div>
@endsection
