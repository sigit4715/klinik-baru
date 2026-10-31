{{-- Patient identity card. Expects $patient and optionally $appointment. --}}
@php
    $age = '-';
    if ($patient?->birth_date) {
        $diff = \Carbon\Carbon::parse($patient->birth_date)->diff(now());
        $age = trim(($diff->y ? $diff->y . ' th ' : '') . ($diff->m ? $diff->m . ' bln ' : '') . (! $diff->y && $diff->d ? $diff->d . ' hr' : '')) ?: '0 hr';
    }
@endphp
<div class="card mb-3">
    <div class="card-header bg-primary text-white"><i class="uil uil-user me-1"></i>Info Pasien</div>
    <div class="card-body">
        <h5 class="mb-1">{{ $patient?->name }}</h5>
        <p class="text-muted mb-2"><code>{{ $patient?->no_rm }}</code> | NIK {{ $patient?->nik ?: '-' }}</p>
        <p class="mb-2 small">
            {{ $patient?->gender == 'L' ? 'Laki-laki' : 'Perempuan' }}, {{ $age }}
            @if($patient?->blood_type) | Gol. darah {{ $patient->blood_type }} @endif
        </p>
        @if($patient?->allergy)
            <div class="alert alert-danger py-2 mb-2"><i class="uil uil-exclamation-triangle me-1"></i>Alergi: {{ $patient->allergy }}</div>
        @endif
        @isset($appointment)
            <hr class="my-2">
            <p class="small mb-1"><strong>No. Antrian:</strong> {{ $appointment->queue_number }}</p>
            <p class="small mb-1"><strong>Jam Daftar:</strong> {{ $appointment->appointment_time?->format('H:i') ?? '-' }}
                @if($appointment->registeredBy) oleh {{ $appointment->registeredBy->name }} @endif</p>
            <p class="small mb-0"><strong>Perawat:</strong> {{ $appointment->nurse?->name ?? '-' }}</p>
        @endisset
    </div>
</div>
