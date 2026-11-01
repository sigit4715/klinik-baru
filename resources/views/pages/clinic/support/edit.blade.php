@section('title', $title)
@section('description', $description)
@extends('layout.app')
@section('content')
    <div class="container-fluid">
        <div class="breadcrumb-main d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h4 class="breadcrumb-title mb-0">Hasil {{ $order->service?->name }}
                <span class="badge bg-{{ $order->statusBadge() }} ms-1">{{ $order->statusLabel() }}</span></h4>
            <a href="{{ route('clinic.support-orders.index', [app()->getLocale(), 'date' => $order->created_at->toDateString()]) }}" class="btn btn-light btn-sm">Kembali</a>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif

        <div class="row">
            <div class="col-lg-4">
                @include('pages.clinic.medical-records._patient_card', ['patient' => $order->patient])
                <div class="card mb-3">
                    <div class="card-body small">
                        <p class="mb-1"><strong>Unit:</strong> {{ $order->poli?->name ?? '-' }}</p>
                        <p class="mb-1"><strong>Dipesan:</strong> {{ $order->created_at->format('d/m/Y H:i') }} oleh {{ $order->orderedBy?->name ?? '-' }} ({{ \App\Models\SupportOrder::SOURCES[$order->ordered_from] ?? '-' }})</p>
                        <p class="mb-1"><strong>Catatan/indikasi:</strong> {{ $order->notes ?: '-' }}</p>
                        @if($order->appointment?->nursingAssessment?->chief_complaint)
                            <p class="mb-0"><strong>Keluhan:</strong> {{ $order->appointment->nursingAssessment->chief_complaint }}</p>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-lg-8">
                <div class="card mb-3">
                    <div class="card-body">
                        <form method="POST" action="{{ route('clinic.support-orders.update', [app()->getLocale(), $order->id]) }}" enctype="multipart/form-data">
                            @csrf @method('PUT')
                            <div class="mb-3">
                                <label class="form-label">Hasil / Kesimpulan</label>
                                <textarea name="result_text" class="form-control" rows="6" placeholder="Contoh: Hb 12,5 g/dL; Leukosit 7.800; Trombosit 98.000 (rendah)">{{ old('result_text', $order->result_text) }}</textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Upload File Hasil</label>
                                <input type="file" name="files[]" class="form-control" multiple accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png">
                                <small class="text-muted">PDF, JPG, atau PNG, maksimal 10 MB per file. File disimpan tertutup dan hanya bisa dibuka oleh user yang berhak.</small>
                            </div>
                            <div class="d-flex gap-2 flex-wrap">
                                <button name="status" value="in_progress" class="btn btn-outline-primary"><i class="uil uil-save me-1"></i>Simpan Sementara</button>
                                <button name="status" value="done" class="btn btn-success"><i class="uil uil-check me-1"></i>Simpan & Selesai</button>
                            </div>
                        </form>
                        @if($order->result_at)
                            <p class="small text-muted mt-3 mb-0">Diselesaikan {{ $order->result_at->format('d/m/Y H:i') }} oleh {{ $order->resultBy?->name ?? '-' }}.</p>
                        @endif
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">File Hasil</div>
                    <div class="card-body">
                        @forelse($order->files as $file)
                            <div class="d-flex justify-content-between align-items-center {{ $loop->last ? '' : 'border-bottom pb-2 mb-2' }}">
                                <div>
                                    <i class="uil {{ $file->isImage() ? 'uil-image' : 'uil-file' }} me-1"></i>
                                    <a href="{{ route('clinic.support-files.show', [app()->getLocale(), $file->id]) }}" target="_blank">{{ $file->original_name }}</a>
                                    <small class="text-muted">({{ $file->sizeLabel() }}, {{ $file->uploader?->name }}, {{ $file->created_at->format('d/m H:i') }})</small>
                                </div>
                                <div class="d-flex gap-1">
                                    <a href="{{ route('clinic.support-files.show', [app()->getLocale(), $file->id, 'download' => 1]) }}" class="btn btn-sm btn-outline-secondary" title="Unduh"><i class="uil uil-download-alt"></i></a>
                                    <form method="POST" action="{{ route('clinic.support-files.destroy', [app()->getLocale(), $file->id]) }}" onsubmit="return confirm('Hapus file ini?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" title="Hapus"><i class="uil uil-trash"></i></button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <p class="text-muted mb-0">Belum ada file.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
