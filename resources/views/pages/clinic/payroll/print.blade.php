<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Slip Gaji {{ $payslip->employee_name }} - {{ $payslip->period->label() }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-size: 13px; background: #f3f4f6; }
        .sheet { max-width: 760px; margin: 20px auto; background: #fff; padding: 32px; border-radius: 8px; }
        .slip-table td, .slip-table th { padding: 4px 6px; }
        @media print {
            body { background: #fff; }
            .sheet { margin: 0; padding: 0; max-width: none; border-radius: 0; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
    <div class="sheet">
        <div class="no-print text-end mb-3">
            <button class="btn btn-primary btn-sm" onclick="window.print()">Cetak</button>
            <button class="btn btn-secondary btn-sm" onclick="window.close()">Tutup</button>
        </div>
        <div class="text-center mb-3">
            <h5 class="mb-0">{{ $clinicSettings['clinic_name'] ?? 'KLINIK' }}</h5>
            <div class="small">{{ $clinicSettings['clinic_address'] ?? '' }}</div>
            <h6 class="mt-3 mb-0">SLIP GAJI</h6>
            <div>Periode {{ $payslip->period->label() }}</div>
        </div>
        <table class="table table-sm table-borderless mb-2">
            <tr><td style="width: 120px">Nama</td><td>: <strong>{{ $payslip->employee_name }}</strong></td></tr>
            <tr><td>Bagian</td><td>: {{ $payslip->role_label }}</td></tr>
            <tr><td>Status</td><td>: {{ $payslip->period->statusLabel() }}{{ $payslip->period->paid_at ? ' (' . $payslip->period->paid_at->format('d/m/Y') . ')' : '' }}</td></tr>
        </table>
        @include('pages.clinic.payroll._slip')
        <div class="row mt-5 text-center">
            <div class="col-6">Diterima oleh,<br><br><br><br>( {{ $payslip->employee_name }} )</div>
            <div class="col-6">Disetujui oleh,<br><br><br><br>( ........................ )</div>
        </div>
        <p class="text-muted small mt-4 mb-0">Dicetak {{ now()->format('d/m/Y H:i') }}</p>
    </div>
</body>
</html>
