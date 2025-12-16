<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Pembayaran - {{ $payment->invoice_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            line-height: 1.4;
            width: 80mm;
            margin: 0 auto;
            padding: 10px;
            background: #fff;
        }

        .header {
            text-align: center;
            margin-bottom: 15px;
        }

        .header h2 {
            font-size: 16px;
            margin-bottom: 3px;
        }

        .header p {
            font-size: 10px;
            color: #666;
        }

        .divider {
            border-bottom: 1px dashed #333;
            margin: 10px 0;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
        }

        .info-row .label {
            font-weight: normal;
        }

        .info-row .value {
            font-weight: bold;
        }

        .items {
            margin: 10px 0;
        }

        .item {
            margin-bottom: 5px;
        }

        .item-name {
            font-weight: bold;
        }

        .item-detail {
            display: flex;
            justify-content: space-between;
            padding-left: 10px;
        }

        .total-section {
            margin-top: 10px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            font-size: 14px;
            font-weight: bold;
            margin-top: 5px;
        }

        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 10px;
            color: #666;
        }

        .badge {
            display: inline-block;
            padding: 2px 8px;
            background: #28a745;
            color: white;
            font-size: 10px;
            border-radius: 3px;
        }

        @media print {
            body {
                width: 80mm;
                margin: 0;
                padding: 5mm;
            }

            .no-print {
                display: none;
            }
        }

        .print-btn {
            position: fixed;
            bottom: 20px;
            right: 20px;
            padding: 15px 30px;
            background: #8231D3;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        }

        .print-btn:hover {
            background: #6a28a8;
        }
    </style>
</head>

<body>
    <div class="header">
        <h2>{{ $clinicSettings['clinic_name'] ?? 'KLINIK' }}</h2>
        <p>{{ $clinicSettings['clinic_address'] ?? '' }}</p>
        <p>
            @if(!empty($clinicSettings['clinic_phone']))
                Tel: {{ $clinicSettings['clinic_phone'] }}
            @endif
            @if(!empty($clinicSettings['clinic_whatsapp']))
                | WA: {{ $clinicSettings['clinic_whatsapp'] }}
            @endif
        </p>
    </div>

    <div class="divider"></div>

    <div class="invoice-info">
        <div class="info-row">
            <span class="label">No. Invoice</span>
            <span class="value">{{ $payment->invoice_number }}</span>
        </div>
        <div class="info-row">
            <span class="label">Tanggal</span>
            <span class="value">{{ $payment->created_at->format('d/m/Y H:i') }}</span>
        </div>
        <div class="info-row">
            <span class="label">Kasir</span>
            <span class="value">{{ Auth::user()->name ?? 'Admin' }}</span>
        </div>
    </div>

    <div class="divider"></div>

    <div class="patient-info">
        <div class="info-row">
            <span class="label">No. RM</span>
            <span class="value">{{ $payment->appointment->patient->no_rm }}</span>
        </div>
        <div class="info-row">
            <span class="label">Pasien</span>
            <span class="value">{{ $payment->appointment->patient->name }}</span>
        </div>
        <div class="info-row">
            <span class="label">Dokter</span>
            <span class="value">{{ $payment->appointment->doctor->name }}</span>
        </div>
    </div>

    <div class="divider"></div>

    <div class="items">
        <p style="font-weight: bold; margin-bottom: 8px;">RINCIAN:</p>

        @php $items = is_array($payment->items) ? $payment->items : json_decode($payment->items ?? '[]', true); @endphp
        @if(is_array($items) && count($items) > 0)
            @foreach($items as $item)
                <div class="item">
                    <div class="item-name">{{ $item['name'] ?? 'Item' }}</div>
                    <div class="item-detail">
                        <span>{{ $item['qty'] ?? 1 }} x Rp {{ number_format($item['price'] ?? 0, 0, ',', '.') }}</span>
                        <span>Rp {{ number_format($item['subtotal'] ?? 0, 0, ',', '.') }}</span>
                    </div>
                </div>
            @endforeach
        @else
            <div class="item">
                <div class="item-detail">
                    <span>Biaya Konsultasi</span>
                    <span>Rp {{ number_format($payment->total, 0, ',', '.') }}</span>
                </div>
            </div>
        @endif
    </div>

    <div class="divider"></div>

    <div class="total-section">
        <div class="info-row">
            <span>Subtotal</span>
            <span>Rp {{ number_format($payment->subtotal ?? $payment->total, 0, ',', '.') }}</span>
        </div>
        @if($payment->discount > 0)
            <div class="info-row">
                <span>Diskon</span>
                <span>- Rp {{ number_format($payment->discount, 0, ',', '.') }}</span>
            </div>
        @endif
        <div class="total-row">
            <span>TOTAL</span>
            <span>Rp {{ number_format($payment->total, 0, ',', '.') }}</span>
        </div>
    </div>

    <div class="divider"></div>

    <div class="payment-info">
        <div class="info-row">
            <span>Metode Bayar</span>
            <span class="badge">{{ strtoupper($payment->payment_method ?? 'TUNAI') }}</span>
        </div>
        @if($payment->status === 'paid')
            <div class="info-row">
                <span>Status</span>
                <span class="badge">LUNAS</span>
            </div>
        @endif
    </div>

    <div class="footer">
        <div class="divider"></div>
        <p style="margin-top: 10px;">{{ $clinicSettings['receipt_footer'] ?? 'Terima kasih atas kunjungan Anda!' }}</p>
        <p style="margin-top: 5px;">Dicetak: {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>

    <button class="print-btn no-print" onclick="window.print()">
        🖨️ Cetak Struk
    </button>

    <script>
        // Auto print on load (optional - uncomment if desired)
        // window.onload = function() { window.print(); }
    </script>
</body>

</html>