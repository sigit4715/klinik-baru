{{-- Nurse findings shown to the doctor. Expects $assessment (nullable) and $appointment. --}}
<div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="uil uil-heart-medical me-1"></i>Data Perawat</span>
        @if($assessment?->triage_level)
            <span class="badge bg-{{ $assessment->triageBadge() }}">{{ $assessment->triageLabel() }}</span>
        @endif
    </div>
    <div class="card-body small">
        @if($assessment)
            <p class="mb-2"><strong>Keluhan Utama:</strong><br>{{ $assessment->chief_complaint ?: '-' }}</p>
            <p class="mb-2"><strong>Anamnesa:</strong><br>{{ $assessment->anamnesa ?: '-' }}</p>
            @if($assessment->allergy_note)
                <p class="mb-2 text-danger"><strong>Alergi:</strong> {{ $assessment->allergy_note }}</p>
            @endif
            <table class="table table-sm table-borderless mb-2">
                <tr><td class="ps-0">Tekanan Darah</td><td>{{ $assessment->blood_pressure ?? '-' }} mmHg</td></tr>
                <tr><td class="ps-0">Suhu</td><td>{{ $assessment->body_temperature ?? '-' }} °C</td></tr>
                <tr><td class="ps-0">Nadi</td><td>{{ $assessment->pulse_rate ?? '-' }} x/menit</td></tr>
                <tr><td class="ps-0">Pernapasan</td><td>{{ $assessment->respiratory_rate ?? '-' }} x/menit</td></tr>
                <tr><td class="ps-0">SpO2</td><td>{{ $assessment->oxygen_saturation ?? '-' }} %</td></tr>
                <tr><td class="ps-0">TB / BB</td><td>{{ $assessment->height ?? '-' }} cm / {{ $assessment->weight ?? '-' }} kg</td></tr>
            </table>
            <p class="mb-1"><strong>Poli / Dokter:</strong> {{ $assessment->poli?->name ?? '-' }} / {{ $assessment->doctor?->name ?? '-' }}</p>
            @if($assessment->notes)
                <p class="mb-1"><strong>Catatan:</strong> {{ $assessment->notes }}</p>
            @endif
            <p class="text-muted mb-0">Oleh {{ $assessment->nurse?->name ?? '-' }}, {{ $assessment->updated_at->format('d/m/Y H:i') }}</p>
        @else
            <p class="text-muted mb-1">Belum ada data dari perawat.</p>
            @if($appointment?->complaint)
                <p class="mb-0"><strong>Keluhan:</strong> {{ $appointment->complaint }}</p>
            @endif
        @endif
    </div>
</div>
