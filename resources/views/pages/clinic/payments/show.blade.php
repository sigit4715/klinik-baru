@section('title', $title)
@section('description', $description)
@extends('layout.app')

@push('css')
    <style>
        .payment-detail-header {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border-radius: 16px;
            padding: 24px 28px;
            margin-bottom: 24px;
            color: white;
            box-shadow: 0 10px 40px rgba(16, 185, 129, 0.3);
        }

        .payment-detail-header h4 {
            margin: 0;
            font-size: 20px;
            font-weight: 600;
        }

        .payment-card {
            background: white;
            border-radius: 14px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
            margin-bottom: 24px;
            overflow: hidden;
            border: 1px solid #e5e7eb;
        }

        .payment-card .card-header {
            background: #f8fafc;
            padding: 16px 24px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            padding: 24px;
        }

        .info-item {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .info-item label {
            font-size: 12px;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
        }

        .info-item .value {
            font-size: 15px;
            font-weight: 600;
            color: #1f2937;
        }

        .items-table {
            width: 100%;
            margin: 0;
        }

        .items-table thead {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }

        .items-table thead th {
            color: white !important;
            font-weight: 500;
            padding: 14px 20px;
            font-size: 12px;
            text-transform: uppercase;
            border: none !important;
        }

        .items-table tbody td {
            padding: 16px 20px;
            vertical-align: middle;
            border: none !important;
            border-bottom: 1px solid #f3f4f6 !important;
        }

        .summary-section {
            background: #f8fafc;
            padding: 20px 24px;
            border-top: 1px solid #e5e7eb;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 16px;
        }

        .summary-item {
            text-align: center;
            padding: 16px;
            border-radius: 12px;
            background: white;
            border: 1px solid #e5e7eb;
        }

        .summary-item.total {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border: none;
            color: white;
        }

        .summary-item.cash {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            border: none;
            color: white;
        }

        .summary-item.change {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            border: none;
            color: white;
        }

        .summary-item label {
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            display: block;
            margin-bottom: 6px;
            opacity: 0.85;
        }

        .summary-item .value {
            font-size: 18px;
            font-weight: 700;
        }

        .btn-action {
            padding: 10px 20px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 14px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s;
        }

        .btn-action.primary {
            background: white;
            color: #10b981;
            border: 2px solid rgba(255, 255, 255, 0.3);
        }

        .btn-action.primary:hover {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border-color: white;
        }

        .btn-action.secondary {
            background: rgba(255, 255, 255, 0.15);
            color: white;
            border: 2px solid rgba(255, 255, 255, 0.3);
        }

        .btn-action.secondary:hover {
            background: rgba(255, 255, 255, 0.25);
        }

        .badge-status {
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge-status.paid {
            background: rgba(16, 185, 129, 0.15);
            color: #059669;
        }

        .badge-status.pending {
            background: rgba(245, 158, 11, 0.15);
            color: #d97706;
        }

        @media (max-width: 768px) {
            .summary-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .info-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid">
        <!-- Header -->
        <div class="payment-detail-header">
            <div class="d-flex justify-content-between align-items-center">
                <h4><i class="uil uil-receipt me-2"></i>Detail Pembayaran</h4>
                <div class="d-flex gap-2">
                    <a href="{{ route('clinic.payments.receipt', [app()->getLocale(), $payment->id]) }}"
                        class="btn-action primary" target="_blank">
                        <i class="uil uil-receipt"></i>Cetak Struk
                    </a>
                    <a href="{{ route('clinic.payments.invoice', [app()->getLocale(), $payment->id]) }}"
                        class="btn-action secondary" target="_blank">
                        <i class="uil uil-print"></i>Cetak Invoice
                    </a>
                </div>
            </div>
        </div>

        <!-- Info Card -->
        <div class="payment-card">
            <div class="card-header">
                <span style="font-weight: 600; color: #1f2937;">
                    <i class="uil uil-file-alt me-2"></i>Informasi Pembayaran
                </span>
                @if($payment->status == 'paid' && (float) $payment->refunded_amount == 0)
                    <span class="badge-status paid"><i class="uil uil-check me-1"></i>Lunas</span>
                @else
                    <span class="badge-status pending"><i class="uil uil-clock me-1"></i>{{ $payment->statusLabel() }}</span>
                @endif
            </div>
            <div class="info-grid">
                <div class="info-item">
                    <label>No. Invoice</label>
                    <div class="value"><code>{{ $payment->invoice_number }}</code></div>
                </div>
                <div class="info-item">
                    <label>Pasien</label>
                    <div class="value">{{ $payment->patient->name }}</div>
                </div>
                <div class="info-item">
                    <label>Tanggal</label>
                    <div class="value">{{ $payment->created_at->format('d M Y H:i') }}</div>
                </div>
                <div class="info-item">
                    <label>No. RM</label>
                    <div class="value">{{ $payment->patient->no_rm }}</div>
                </div>
                <div class="info-item">
                    <label>Metode Pembayaran</label>
                    <div class="value">{{ strtoupper($payment->payment_method) }}</div>
                </div>
                <div class="info-item">
                    <label>Kasir</label>
                    <div class="value">{{ $payment->cashier?->name ?? '-' }}</div>
                </div>
            </div>
        </div>

        <!-- Items Card -->
        <div class="payment-card">
            <div class="card-header">
                <span style="font-weight: 600; color: #1f2937;">
                    <i class="uil uil-list-ul me-2"></i>Detail Item
                </span>
            </div>
            <div class="table-responsive">
                <table class="items-table">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Qty</th>
                            <th>Harga</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($payment->items as $item)
                            <tr>
                                <td style="font-weight: 600;">{{ $item['name'] }}</td>
                                <td>{{ $item['qty'] }}</td>
                                <td>Rp {{ number_format($item['price'] ?? 0, 0, ',', '.') }}</td>
                                <td style="font-weight: 600; color: #10b981;">
                                    Rp
                                    {{ number_format($item['subtotal'] ?? ($item['qty'] * ($item['price'] ?? 0)), 0, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Summary Section -->
            <div class="summary-section">
                <div class="summary-grid">
                    <div class="summary-item">
                        <label>Subtotal</label>
                        <div class="value">Rp {{ number_format($payment->subtotal, 0, ',', '.') }}</div>
                    </div>
                    <div class="summary-item">
                        <label>Diskon</label>
                        <div class="value">Rp {{ number_format($payment->discount, 0, ',', '.') }}</div>
                    </div>
                    <div class="summary-item total">
                        <label>Total</label>
                        <div class="value">Rp {{ number_format($payment->total, 0, ',', '.') }}</div>
                    </div>
                    <div class="summary-item cash">
                        <label>Uang Diterima</label>
                        <div class="value">Rp {{ number_format($payment->cash_received ?? 0, 0, ',', '.') }}</div>
                    </div>
                    <div class="summary-item change">
                        <label>Kembalian</label>
                        <div class="value">Rp {{ number_format($payment->change_amount ?? 0, 0, ',', '.') }}</div>
                    </div>
                </div>
            </div>
        </div>

        @if($payment->refunds->isNotEmpty())
            <div class="payment-card">
                <div class="card-header">
                    <span style="font-weight: 600; color: #1f2937;">
                        <i class="uil uil-money-insert me-2"></i>Riwayat Refund
                    </span>
                    <span class="text-danger fw-bold">Total refund: Rp {{ number_format($payment->refunded_amount, 0, ',', '.') }}</span>
                </div>
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead class="bg-light">
                            <tr><th>Tanggal</th><th>Jumlah</th><th>Metode</th><th>Alasan</th><th>Stok</th><th>Oleh</th></tr>
                        </thead>
                        <tbody>
                            @foreach($payment->refunds as $refund)
                                <tr>
                                    <td>{{ $refund->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="text-danger fw-bold">Rp {{ number_format($refund->amount, 0, ',', '.') }}</td>
                                    <td>{{ strtoupper($refund->refund_method) }}</td>
                                    <td>{{ $refund->reason }}</td>
                                    <td>{{ $refund->restocked ? 'Obat dikembalikan' : '-' }}</td>
                                    <td>{{ $refund->user?->name ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <div class="mt-3 d-flex gap-2">
            <a href="{{ route('clinic.payments.index', app()->getLocale()) }}" class="btn btn-light">
                <i class="uil uil-arrow-left me-1"></i>Kembali ke Daftar
            </a>
            @can('payments.refund')
                @if($payment->refundableAmount() > 0)
                    <a href="{{ route('clinic.payments.refund-form', [app()->getLocale(), $payment->id]) }}" class="btn btn-outline-danger">
                        <i class="uil uil-money-insert me-1"></i>Refund
                    </a>
                @endif
            @endcan
        </div>
    </div>
@endsection