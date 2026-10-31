<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Pembayaran - {{ $payment->invoice_number }}</title>
    <style>
        @page {
            size: 80mm auto;
            margin: 0;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            color: #000 !important; /* Paksa warna hitam pekat agar thermal printer tidak dithering/pecah */
            font-weight: bold !important; /* Paksa semua elemen menjadi tebal */
        }

        body {
            font-family: 'Helvetica Neue', 'Helvetica', 'Arial', sans-serif; /* Font sans-serif umumnya tercetak lebih tebal dan jelas di thermal */
            font-size: 13px; /* Sedikit diperbesar */
            font-weight: 900 !important; /* Ketebalan maksimal */
            -webkit-text-stroke: 0.5px #000; /* Menambah ketebalan garis font buatan */
            width: 76mm;
            margin: 0 auto;
            padding: 3mm;
            background: #fff;
            line-height: 1.4;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
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
            color: #000 !important;
        }

        .divider {
            border-top: 1px dashed #000;
            margin: 10px 0;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
        }

        .info-row .label {
            font-weight: 900 !important;
        }

        .info-row .value {
            font-weight: 900 !important;
        }

        .items {
            margin: 10px 0;
        }

        .item {
            margin-bottom: 5px;
        }

        .item-name {
            font-weight: 900 !important;
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
            font-weight: 900 !important;
            margin-top: 5px;
        }

        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 11px;
            color: #000 !important;
        }

        .badge {
            display: inline-block;
            padding: 2px 8px;
            background: #28a745;
            color: white !important;
            font-size: 10px;
            border-radius: 3px;
        }

        @media print {
            body {
                width: 76mm;
                padding: 2mm;
            }

            .no-print {
                display: none !important;
            }
        }

        @media screen {
            body {
                max-width: 80mm;
                margin: 20px auto;
                padding: 15px;
                border: 1px solid #ddd;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            }
        }

        .print-controls {
            text-align: center;
            padding: 20px;
            margin-bottom: 20px;
            background: #f5f5f5;
            border-radius: 8px;
        }

        .btn-print {
            background: #10b981;
            color: white;
            border: none;
            padding: 12px 30px;
            font-size: 16px;
            border-radius: 8px;
            cursor: pointer;
            margin-right: 10px;
        }

        .btn-print:hover {
            background: #059669;
        }

        .btn-close {
            background: #6b7280;
            color: white;
            border: none;
            padding: 12px 30px;
            font-size: 16px;
            border-radius: 8px;
            cursor: pointer;
        }

        .btn-close:hover {
            background: #4b5563;
        }

        @media print {
            .print-controls {
                display: none !important;
            }
        }
    </style>
</head>

<body>
    <div class="print-controls">
        <button class="btn-print" onclick="window.print()">
            <i class="uil uil-print"></i> Cetak Struk
        </button>
        <button class="btn-close" onclick="window.close()">
            Tutup
        </button>
    </div>

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
            <span class="value">{{ $payment->cashier?->name ?? '-' }}</span>
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
            <span class="value">{{ $payment->appointment->doctor?->name ?? '-' }}</span>
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
                        <span>Rp
                            {{ number_format(($item['subtotal'] ?? 0) > 0 ? $item['subtotal'] : (($item['qty'] ?? 1) * ($item['price'] ?? 0)), 0, ',', '.') }}</span>
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
        @if(($payment->cash_received ?? 0) > 0)
            <div class="divider" style="margin: 8px 0;"></div>
            <div class="info-row">
                <span>Tunai</span>
                <span>Rp {{ number_format($payment->cash_received, 0, ',', '.') }}</span>
            </div>
            <div class="info-row" style="font-weight: bold;">
                <span>Kembali</span>
                <span>Rp {{ number_format($payment->change_amount ?? 0, 0, ',', '.') }}</span>
            </div>
        @endif
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

    </div>

    <script>
        // Auto print on load (optional - uncomment if desired)
        // window.onload = function() { window.print(); }
    </script>
</body>

</html>