{{-- Previous visits of the patient. Expects $history (collection of MedicalRecord). --}}
<div class="card mb-3">
    <div class="card-header"><i class="uil uil-history me-1"></i>Riwayat Kunjungan</div>
    <div class="card-body small">
        @forelse($history as $item)
            <div class="{{ $loop->last ? '' : 'border-bottom pb-2 mb-2' }}">
                <div class="d-flex justify-content-between">
                    <strong>{{ $item->created_at->format('d/m/Y') }}</strong>
                    @can('medical_records.view')
                        <a href="{{ route('clinic.medical-records.show', [app()->getLocale(), $item->id]) }}" target="_blank">Detail</a>
                    @endcan
                </div>
                <div class="text-muted">{{ $item->doctor?->name ?? '-' }}</div>
                <div>{{ Str::limit($item->diagnosis, 80) }}</div>
            </div>
        @empty
            <p class="text-muted mb-0">Belum ada kunjungan sebelumnya.</p>
        @endforelse
    </div>
</div>
