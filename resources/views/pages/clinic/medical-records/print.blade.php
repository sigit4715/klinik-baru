<!DOCTYPE html>
<html>

<head>
    <title>Rekam Medis - {{ $record->patient->name }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 20px;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .header h1 {
            margin: 0;
            font-size: 18px;
        }

        .info-row {
            display: flex;
            margin-bottom: 5px;
        }

        .info-label {
            width: 120px;
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background: #f5f5f5;
        }

        .section {
            margin-top: 20px;
        }

        .section h3 {
            font-size: 14px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }

        @media print {
            body {
                margin: 0;
            }
        }
    </style>
</head>

<body onload="window.print()">
    <div class="header">
        <h1>REKAM MEDIS</h1>
        <p>Klinik Anda | Jl. Contoh No. 123</p>
    </div>

    <div class="section">
        <h3>Data Pasien</h3>
        <div class="info-row"><span class="info-label">No. RM:</span> {{ $record->patient->no_rm }}</div>
        <div class="info-row"><span class="info-label">Nama:</span> {{ $record->patient->name }}</div>
        <div class="info-row"><span class="info-label">TTL:</span> {{ $record->patient->birth_place }},
            {{ $record->patient->birth_date->format('d/m/Y') }}</div>
        <div class="info-row"><span class="info-label">Gender:</span>
            {{ $record->patient->gender == 'L' ? 'Laki-laki' : 'Perempuan' }}</div>
    </div>

    <div class="section">
        <h3>Hasil Pemeriksaan</h3>
        <div class="info-row"><span class="info-label">Tanggal:</span> {{ $record->created_at->format('d M Y H:i') }}
        </div>
        <div class="info-row"><span class="info-label">Dokter:</span> {{ $record->doctor?->name ?? '-' }}</div>
        <div class="info-row"><span class="info-label">Anamnesa:</span> {{ $record->anamnesa ?? '-' }}</div>
        <div class="info-row"><span class="info-label">Diagnosis:</span> {{ $record->diagnosis }}</div>
        <div class="info-row"><span class="info-label">Tindakan:</span> {{ $record->treatment ?? '-' }}</div>
    </div>

    @if(count($prescriptionDetails) > 0)
        <div class="section">
            <h3>Resep Obat</h3>
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Obat</th>
                        <th>Qty</th>
                        <th>Dosis</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($prescriptionDetails as $i => $p)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>{{ $p['medicine']->name }}</td>
                            <td>{{ $p['qty'] }}</td>
                            <td>{{ $p['dosage'] }}</td>
                            <td>{{ $p['notes'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <div class="section" style="margin-top: 50px; text-align: right;">
        <p>Dokter Pemeriksa,</p>
        <br><br><br>
        <p><strong>{{ $record->doctor?->name ?? '-' }}</strong></p>
        <p>SIP: {{ $record->doctor?->sip_number ?? '-' }}</p>
    </div>
</body>

</html>