<!DOCTYPE html>
<html lang="id">
{{--
    Invoice and receipt in one template.
    $paper: 58 | 80 | a4, $showItems: item lines or a single total line, $showDoctor
--}}
@php
    $items = is_array($payment->items) ? $payment->items : (json_decode($payment->items ?? '[]', true) ?: []);
    $rp = fn ($v) => 'Rp ' . number_format((float) $v, 0, ',', '.');
    $thermal = $paper !== 'a4';
    $patient = $payment->appointment?->patient ?? $payment->patient;
    $doctorName = $payment->appointment?->doctor?->name;
    $statusLabel = ['paid' => 'LUNAS', 'pending' => 'BELUM LUNAS', 'cancelled' => 'DIBATALKAN', 'refunded' => 'REFUND'][$payment->status] ?? strtoupper($payment->status);
    $typeLabel = fn ($t) => ['medicine' => 'Obat', 'service' => 'Layanan', 'racikan' => 'Racikan'][$t] ?? '';
    $url = fn (array $q) => request()->url() . '?' . http_build_query(array_merge(['paper' => $paper, 'items' => $showItems ? 1 : 0], $q));
@endphp
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $thermal ? 'Struk' : 'Invoice' }} {{ $payment->invoice_number }}</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; background: #e5e7eb; color: #000; font-family: {{ $thermal ? "'Courier New', monospace" : 'Arial, Helvetica, sans-serif' }}; }
        .toolbar { max-width: 760px; margin: 12px auto; padding: 10px; background: #fff; border-radius: 8px; display: flex; flex-wrap: wrap; gap: 8px; align-items: center; justify-content: center; font-family: Arial, sans-serif; font-size: 13px; }
        .toolbar a, .toolbar button { padding: 6px 12px; border-radius: 6px; border: 1px solid #d1d5db; background: #fff; color: #111; text-decoration: none; cursor: pointer; font-size: 13px; }
        .toolbar .active { background: #10b981; border-color: #10b981; color: #fff; }
        .toolbar .primary { background: #2563eb; border-color: #2563eb; color: #fff; }
        .doc { background: #fff; margin: 0 auto 24px; padding: {{ $thermal ? '8px' : '28px 32px' }};
            width: {{ $paper === '58' ? '58mm' : ($paper === '80' ? '80mm' : '210mm') }};
            font-size: {{ $paper === '58' ? '10px' : ($paper === '80' ? '12px' : '13px') }}; }
        .center { text-align: center; }
        .right { text-align: right; }
        .muted { color: #555; }
        .divider { border-top: 1px dashed #000; margin: 6px 0; }
        .row { display: flex; justify-content: space-between; gap: 6px; }
        .row > span:last-child { text-align: right; }
        .bold { font-weight: bold; }
        h1 { margin: 0; font-size: {{ $thermal ? '1.3em' : '20px' }}; }
        table { width: 100%; border-collapse: collapse; }
        .a4-items th, .a4-items td { border-bottom: 1px solid #e5e7eb; padding: 6px 4px; text-align: left; }
        .a4-items th { background: #f3f4f6; }
        .badge { display: inline-block; padding: 1px 6px; border: 1px solid #000; font-size: .85em; }
        @page { size: {{ $paper === 'a4' ? 'A4' : ($paper === '58' ? '58mm auto' : '80mm auto') }}; margin: {{ $thermal ? '0' : '15mm' }}; }
        @media print {
            body { background: #fff; }
            .toolbar { display: none; }
            .doc { margin: 0; width: 100%; padding: {{ $thermal ? '2mm' : '0' }}; }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <span>Kertas:</span>
        @foreach(['58' => '58 mm', '80' => '80 mm', 'a4' => 'A4'] as $value => $label)
            <a href="{{ $url(['paper' => $value]) }}" class="{{ $paper === $value ? 'active' : '' }}">{{ $label }}</a>
        @endforeach
        <span>|</span>
        <a href="{{ $url(['items' => $showItems ? 0 : 1]) }}">{{ $showItems ? 'Sembunyikan rincian' : 'Tampilkan rincian' }}</a>
        <button class="primary" onclick="window.print()">Cetak</button>
        <button onclick="window.close()">Tutup</button>
    </div>

    <div class="doc">
        @if($thermal)
            <div class="center">
                <h1>{{ $clinicSettings['clinic_name'] ?? 'KLINIK' }}</h1>
                <div>{{ $clinicSettings['clinic_address'] ?? '' }}</div>
                <div>
                    @if(! empty($clinicSettings['clinic_phone']))Tel: {{ $clinicSettings['clinic_phone'] }}@endif
                    @if(! empty($clinicSettings['clinic_whatsapp'])) WA: {{ $clinicSettings['clinic_whatsapp'] }}@endif
                </div>
            </div>
            <div class="divider"></div>
            <div class="row"><span>No</span><span>{{ $payment->invoice_number }}</span></div>
            <div class="row"><span>Tanggal</span><span>{{ $payment->created_at->format('d/m/Y H:i') }}</span></div>
            <div class="row"><span>Kasir</span><span>{{ $payment->cashier?->name ?? '-' }}</span></div>
            <div class="divider"></div>
            <div class="row"><span>No. RM</span><span>{{ $patient?->no_rm }}</span></div>
            <div class="row"><span>Pasien</span><span>{{ $patient?->name }}</span></div>
            @if($showDoctor && $doctorName)
                <div class="row"><span>Dokter</span><span>{{ $doctorName }}</span></div>
            @endif
            <div class="divider"></div>

            @if($showItems && $items)
                @foreach($items as $item)
                    <div class="bold">{{ $item['name'] ?? 'Item' }}</div>
                    <div class="row">
                        <span>{{ $item['qty'] ?? 1 }} x {{ $rp($item['price'] ?? 0) }}</span>
                        <span>{{ $rp($item['subtotal'] ?? (($item['qty'] ?? 1) * ($item['price'] ?? 0))) }}</span>
                    </div>
                @endforeach
            @else
                <div class="row"><span>Biaya Pemeriksaan &amp; Pengobatan</span><span>{{ $rp($payment->subtotal ?? $payment->total) }}</span></div>
            @endif
            <div class="divider"></div>
            @if($showItems || (float) $payment->discount > 0)
                <div class="row"><span>Subtotal</span><span>{{ $rp($payment->subtotal ?? $payment->total) }}</span></div>
            @endif
            @if((float) $payment->discount > 0)
                <div class="row"><span>Diskon</span><span>- {{ $rp($payment->discount) }}</span></div>
            @endif
            <div class="row bold" style="font-size: 1.15em;"><span>TOTAL</span><span>{{ $rp($payment->total) }}</span></div>
            @if((float) $payment->cash_received > 0 && $payment->payment_method === 'cash')
                <div class="row"><span>Tunai</span><span>{{ $rp($payment->cash_received) }}</span></div>
                <div class="row bold"><span>Kembali</span><span>{{ $rp($payment->change_amount) }}</span></div>
            @endif
            @if((float) $payment->refunded_amount > 0)
                <div class="row"><span>Refund</span><span>- {{ $rp($payment->refunded_amount) }}</span></div>
            @endif
            <div class="divider"></div>
            <div class="row"><span>Metode</span><span>{{ strtoupper($payment->payment_method ?? '-') }}</span></div>
            <div class="row"><span>Status</span><span class="bold">{{ $statusLabel }}</span></div>
            <div class="divider"></div>
            <div class="center">{{ $clinicSettings['receipt_footer'] ?? 'Terima kasih atas kunjungan Anda!' }}</div>
            <div class="center muted">Dicetak {{ now()->format('d/m/Y H:i') }}</div>
        @else
            <table>
                <tr>
                    <td>
                        <h1>{{ $clinicSettings['clinic_name'] ?? 'KLINIK' }}</h1>
                        <div class="muted">{{ $clinicSettings['clinic_address'] ?? '' }}</div>
                        <div class="muted">
                            @if(! empty($clinicSettings['clinic_phone']))Telp. {{ $clinicSettings['clinic_phone'] }}@endif
                            @if(! empty($clinicSettings['clinic_whatsapp'])) | WA {{ $clinicSettings['clinic_whatsapp'] }}@endif
                            @if(! empty($clinicSettings['clinic_email'])) | {{ $clinicSettings['clinic_email'] }}@endif
                        </div>
                    </td>
                    <td class="right" style="vertical-align: top;">
                        <div style="font-size: 22px; font-weight: bold;">INVOICE</div>
                        <div>{{ $payment->invoice_number }}</div>
                        <div class="badge">{{ $statusLabel }}</div>
                    </td>
                </tr>
            </table>
            <hr>
            <table style="margin-bottom: 16px;">
                <tr>
                    <td style="vertical-align: top; width: 50%;">
                        <div class="muted">Ditagihkan kepada</div>
                        <div class="bold">{{ $patient?->name }}</div>
                        <div>No. RM {{ $patient?->no_rm }}</div>
                        @if($patient?->phone)<div>{{ $patient->phone }}</div>@endif
                    </td>
                    <td class="right" style="vertical-align: top;">
                        <div>Tanggal: {{ $payment->created_at->format('d/m/Y H:i') }}</div>
                        <div>Kasir: {{ $payment->cashier?->name ?? '-' }}</div>
                        @if($showDoctor && $doctorName)<div>Dokter: {{ $doctorName }}</div>@endif
                        <div>Metode: {{ strtoupper($payment->payment_method ?? '-') }}</div>
                    </td>
                </tr>
            </table>

            <table class="a4-items">
                <thead>
                    <tr><th style="width: 50%;">Item</th><th>Jenis</th><th class="right">Qty</th><th class="right">Harga</th><th class="right">Subtotal</th></tr>
                </thead>
                <tbody>
                    @if($showItems && $items)
                        @foreach($items as $item)
                            <tr>
                                <td>{{ $item['name'] ?? 'Item' }}</td>
                                <td>{{ $typeLabel($item['type'] ?? '') }}</td>
                                <td class="right">{{ $item['qty'] ?? 1 }}</td>
                                <td class="right">{{ $rp($item['price'] ?? 0) }}</td>
                                <td class="right">{{ $rp($item['subtotal'] ?? (($item['qty'] ?? 1) * ($item['price'] ?? 0))) }}</td>
                            </tr>
                        @endforeach
                    @else
                        <tr><td colspan="4">Biaya Pemeriksaan &amp; Pengobatan</td><td class="right">{{ $rp($payment->subtotal ?? $payment->total) }}</td></tr>
                    @endif
                </tbody>
            </table>
            <table style="width: 45%; margin-left: auto; margin-top: 12px;">
                <tr><td>Subtotal</td><td class="right">{{ $rp($payment->subtotal ?? $payment->total) }}</td></tr>
                @if((float) $payment->discount > 0)<tr><td>Diskon</td><td class="right">- {{ $rp($payment->discount) }}</td></tr>@endif
                <tr class="bold" style="font-size: 16px;"><td>TOTAL</td><td class="right">{{ $rp($payment->total) }}</td></tr>
                @if((float) $payment->cash_received > 0 && $payment->payment_method === 'cash')
                    <tr><td>Tunai</td><td class="right">{{ $rp($payment->cash_received) }}</td></tr>
                    <tr><td>Kembali</td><td class="right">{{ $rp($payment->change_amount) }}</td></tr>
                @endif
                @if((float) $payment->refunded_amount > 0)
                    <tr><td>Refund</td><td class="right">- {{ $rp($payment->refunded_amount) }}</td></tr>
                @endif
            </table>
            <p class="muted" style="margin-top: 32px;">{{ $clinicSettings['receipt_footer'] ?? 'Terima kasih atas kunjungan Anda!' }}</p>
            <p class="muted" style="font-size: 11px;">Dicetak {{ now()->format('d/m/Y H:i') }}</p>
        @endif
    </div>
</body>
</html>
