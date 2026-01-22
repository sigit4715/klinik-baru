<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Antrian - {{ $appointment->queue_number }}</title>
    <style>
        @page {
            size: 80mm auto;
            margin: 0;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            width: 76mm;
            padding: 3mm;
            margin: 0 auto;
            background: #fff;
        }

        .header {
            text-align: center;
            margin-bottom: 10px;
            border-bottom: 1px dashed #000;
            padding-bottom: 10px;
        }

        .header h1 {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 2px;
        }

        .header h2 {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 2px;
        }

        .header h3 {
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            margin-top: 8px;
        }

        .info-table {
            width: 100%;
            margin-bottom: 10px;
        }

        .info-table tr td {
            vertical-align: top;
            padding: 2px 0;
        }

        .info-table .label {
            width: 40%;
            font-weight: normal;
        }

        .info-table .separator {
            width: 5%;
            text-align: center;
        }

        .info-table .value {
            width: 55%;
        }

        .divider {
            border-top: 1px dashed #000;
            margin: 8px 0;
        }

        .queue-section {
            text-align: center;
            margin: 15px 0;
            padding: 10px 0;
            border-top: 1px dashed #000;
            border-bottom: 1px dashed #000;
        }

        .queue-label {
            font-size: 12px;
            margin-bottom: 5px;
        }

        .queue-number {
            font-size: 48px;
            font-weight: bold;
            line-height: 1;
        }

        .obat-section {
            margin-top: 10px;
        }

        .obat-title {
            font-weight: bold;
            margin-bottom: 5px;
        }

        .obat-lines {
            border-bottom: 1px solid #000;
            height: 20px;
            margin-bottom: 3px;
        }

        .footer {
            text-align: center;
            margin-top: 15px;
            font-size: 10px;
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

            body {
                width: 76mm;
                padding: 2mm;
            }
        }

        @media screen {
            body {
                max-width: 300px;
                margin: 20px auto;
                padding: 15px;
                border: 1px solid #ddd;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
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

    <div class="struk-content">
        <!-- Header Klinik -->
        <div class="header">
            <h1>{{ $settings['clinic_name'] ?? 'PMB' }}</h1>
            <h2>{{ $appointment->doctor->name }}</h2>
            <h3>ANTRIAN PENDAFTARAN</h3>
        </div>

        <!-- Informasi Pendaftaran -->
        <table class="info-table">
            <tr>
                <td class="label">No Daftar</td>
                <td class="separator">:</td>
                <td class="value">{{ $appointment->queue_number }}</td>
            </tr>
            <tr>
                <td class="label">Nomor RM</td>
                <td class="separator">:</td>
                <td class="value">{{ $appointment->patient->no_rm }}</td>
            </tr>
            <tr>
                <td class="label">Nama Pasien</td>
                <td class="separator">:</td>
                <td class="value">{{ $appointment->patient->name }}</td>
            </tr>
            <tr>
                <td class="label">Tgl Daftar</td>
                <td class="separator">:</td>
                <td class="value">
                    @php
                        $days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                        $months = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                        $date = $appointment->appointment_date;
                        $dayName = $days[$date->dayOfWeek];
                        $monthName = $months[$date->month];
                    @endphp
                    {{ $dayName }}, {{ $date->day }} {{ $monthName }} {{ $date->year }}
                </td>
            </tr>

            <tr>
                <td class="label">Alamat</td>
                <td class="separator">:</td>
                <td class="value">{{ $appointment->patient->address ?? '-' }}</td>
            </tr>
        </table>

        <div class="divider"></div>

        <table class="info-table">
            <tr>
                <td class="label">Nama Keluarga</td>
                <td class="separator">:</td>
                <td class="value">{{ $appointment->patient->spouse_name ?? $appointment->patient->parent_name ?? '-' }}
                </td>
            </tr>
            <tr>
                <td class="label">Keluhan Pasien</td>
                <td class="separator">:</td>
                <td class="value">{{ $appointment->complaint ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Umur</td>
                <td class="separator">:</td>
                <td class="value">
                    @php
                        if ($appointment->patient->birth_date) {
                            $birthDate = \Carbon\Carbon::parse($appointment->patient->birth_date);
                            $now = \Carbon\Carbon::now();
                            $diff = $birthDate->diff($now);

                            $ageStr = '';
                            if ($diff->y > 0) {
                                $ageStr .= $diff->y . ' tahun ';
                            }
                            if ($diff->m > 0) {
                                $ageStr .= $diff->m . ' bulan ';
                            }
                            if ($diff->d > 0) {
                                $ageStr .= $diff->d . ' hari';
                            }
                            if (empty(trim($ageStr))) {
                                $ageStr = '0 hari';
                            }
                            echo trim($ageStr);
                        } else {
                            echo '-';
                        }
                    @endphp
                </td>
            </tr>
        </table>



        <!-- Daftar Obat -->
        <div class="obat-section">
            <div class="obat-title">Daftar Obat</div>
            <div class="obat-lines"></div>
            <div class="obat-lines"></div>
            <div class="obat-lines"></div>
            <div class="obat-lines"></div>
            <div class="obat-lines"></div>
            <div class="obat-lines"></div>
        </div>

        <!-- Footer -->
        <div class="footer">
            @if(!empty($settings['receipt_footer']))
                {{ $settings['receipt_footer'] }}
            @else
                Terima kasih atas kunjungan Anda
            @endif
        </div>
    </div>

    <script>
        // Auto print when page loads (optional - uncomment to enable)
        // window.onload = function() { window.print(); }
    </script>
</body>

</html>