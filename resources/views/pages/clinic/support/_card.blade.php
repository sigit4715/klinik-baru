{{--
    Support exams of a visit, with results and files.
    Expects $appointment (with supportOrders.service/files); optional $showOrderButton (default true)
--}}
@php
    $orders = $appointment->supportOrders ?? collect();
    $showOrderButton = $showOrderButton ?? true;
@endphp
<div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="uil uil-flask me-1"></i>Penunjang (Lab/Radiologi/EKG)</span>
        @if($showOrderButton && $appointment->status !== 'cancelled')
            @can('support.order')
                <a href="{{ route('clinic.support-orders.create', [app()->getLocale(), $appointment->id]) }}" class="btn btn-sm btn-outline-primary"><i class="uil uil-plus"></i> Order</a>
            @endcan
        @endif
    </div>
    <div class="card-body small">
        @forelse($orders as $order)
            <div class="{{ $loop->last ? '' : 'border-bottom pb-2 mb-2' }} {{ $order->status === 'cancelled' ? 'text-muted' : '' }}">
                <div class="d-flex justify-content-between">
                    <strong>{{ $order->service?->name }}</strong>
                    <span class="badge bg-{{ $order->statusBadge() }}">{{ $order->statusLabel() }}</span>
                </div>
                @if($order->notes)<div class="text-muted">Catatan: {{ $order->notes }}</div>@endif
                @if($order->status === 'cancelled')
                    <div>Batal: {{ $order->cancel_reason }}</div>
                @elseif($order->result_text)
                    <div class="mt-1"><strong>Hasil:</strong> {!! nl2br(e($order->result_text)) !!}</div>
                @endif
                @if($order->files->isNotEmpty())
                    @canany(['support.view', 'medical_records.view'])
                        <div class="mt-1">
                            @foreach($order->files as $file)
                                <a href="{{ route('clinic.support-files.show', [app()->getLocale(), $file->id]) }}" target="_blank" class="me-2">
                                    <i class="uil {{ $file->isImage() ? 'uil-image' : 'uil-file' }}"></i>{{ Str::limit($file->original_name, 30) }}
                                </a>
                            @endforeach
                        </div>
                    @endcanany
                @endif
                <div class="d-flex gap-1 mt-1">
                    @can('support.manage')
                        @if($order->status !== 'cancelled')
                            <a href="{{ route('clinic.support-orders.edit', [app()->getLocale(), $order->id]) }}" class="btn btn-xs btn-sm py-0 btn-outline-primary">Hasil</a>
                        @endif
                    @endcan
                    @canany(['support.order', 'support.manage'])
                        @if($order->isOpen())
                            <form method="POST" action="{{ route('clinic.support-orders.cancel', [app()->getLocale(), $order->id]) }}"
                                onsubmit="const r = prompt('Alasan pembatalan pemeriksaan:'); if (!r) return false; this.cancel_reason.value = r; return true;">
                                @csrf <input type="hidden" name="cancel_reason">
                                <button class="btn btn-sm py-0 btn-outline-danger">Batal</button>
                            </form>
                        @endif
                    @endcanany
                </div>
            </div>
        @empty
            <p class="text-muted mb-0">Belum ada pemeriksaan penunjang.</p>
        @endforelse
    </div>
</div>
