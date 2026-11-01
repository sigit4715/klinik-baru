{{-- Racikan of a medical record. Expects $record --}}
@php $compounds = $record->getCompoundDetails(); @endphp
@if($compounds)
    <div class="mb-3">
        <strong>Obat Racikan</strong>
        @foreach($compounds as $racik)
            <div class="border rounded p-2 mt-1">
                <div><strong>{{ $racik['name'] }}</strong> &times; {{ $racik['qty'] }}
                    @if($racik['dosage']) &middot; {{ $racik['dosage'] }} @endif
                    @if($racik['notes']) &middot; <span class="text-muted">{{ $racik['notes'] }}</span> @endif
                </div>
                <div class="small">
                    @foreach($racik['components'] as $component)
                        {{ $component['medicine']->name }} {{ $component['qty'] }} {{ $component['medicine']->unit }}{{ $loop->last ? '' : ',' }}
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
@endif
