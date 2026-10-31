{{--
    Vital sign inputs. Expects $vitals (model or null). Set $withSpo2 = false to hide SpO2
    (medical_records has no SpO2 column; the nurse form does).
--}}
@php
    $withSpo2 = $withSpo2 ?? true;
    // Works with models and plain objects that may lack some fields
    $v = fn (string $field) => old($field, $vitals->{$field} ?? null);
    $fields = [
        ['blood_pressure', 'Tekanan Darah', 'mmHg', 'text', '120/80', null],
        ['body_temperature', 'Suhu Badan', '°C', 'number', '36.5', '0.1'],
        ['pulse_rate', 'Denyut Nadi', 'x/menit', 'number', '80', '1'],
        ['respiratory_rate', 'Pernapasan', 'x/menit', 'number', '20', '1'],
        ['oxygen_saturation', 'SpO2', '%', 'number', '98', '1'],
        ['height', 'Tinggi Badan', 'cm', 'number', '165', '0.1'],
        ['weight', 'Berat Badan', 'kg', 'number', '60', '0.1'],
    ];
@endphp
<div class="row">
    @foreach($fields as [$name, $label, $unit, $type, $placeholder, $step])
        @continue(! $withSpo2 && $name === 'oxygen_saturation')
        <div class="col-md-4 col-6 mb-3">
            <label class="form-label">{{ $label }}</label>
            <div class="input-group input-group-sm">
                <input type="{{ $type }}" name="{{ $name }}" class="form-control @error($name) is-invalid @enderror"
                    value="{{ $v($name) }}" placeholder="{{ $placeholder }}" @if($step) step="{{ $step }}" @endif>
                <span class="input-group-text">{{ $unit }}</span>
            </div>
        </div>
    @endforeach
</div>
