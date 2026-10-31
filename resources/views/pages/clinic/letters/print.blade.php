<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $letter->typeLabel() }} {{ $letter->letter_number }}</title>
    <style>
        @page { size: A4; margin: 18mm 20mm; }
        body { font-family: "Times New Roman", Times, serif; font-size: 12pt; line-height: 1.5; color: #000; background: #e5e7eb; margin: 0; }
        .sheet { width: 210mm; min-height: 297mm; margin: 16px auto; padding: 18mm 20mm; background: #fff; box-sizing: border-box; position: relative; }
        .kop { display: flex; align-items: center; border-bottom: 3px double #000; padding-bottom: 8px; margin-bottom: 18px; text-align: center; justify-content: center; }
        .kop h1 { font-size: 16pt; margin: 0; text-transform: uppercase; }
        .kop p { margin: 0; font-size: 10pt; }
        h2 { text-align: center; font-size: 13pt; text-decoration: underline; margin: 0; text-transform: uppercase; }
        .number { text-align: center; margin-bottom: 18px; }
        table.data { margin: 8px 0 8px 24px; border-collapse: collapse; }
        table.data td { padding: 1px 6px; vertical-align: top; }
        table.data td:first-child { width: 170px; }
        .sign { width: 260px; margin-left: auto; margin-top: 36px; text-align: center; }
        .sign .space { height: 72px; }
        .void { position: absolute; top: 40%; left: 10%; right: 10%; text-align: center; font-size: 48pt; color: rgba(220, 38, 38, .35); border: 6px solid rgba(220, 38, 38, .35); transform: rotate(-20deg); font-weight: bold; }
        .controls { text-align: center; margin: 12px; }
        .controls button { padding: 8px 18px; margin: 0 4px; cursor: pointer; }
        @media print {
            body { background: #fff; }
            .sheet { margin: 0; width: auto; min-height: auto; padding: 0; }
            .controls { display: none; }
        }
    </style>
</head>
<body>
    @php
        $patient = $letter->patient;
        $doctor = $letter->doctor;
        $months = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        $fmt = fn ($d) => $d ? $d->day . ' ' . $months[$d->month] . ' ' . $d->year : '-';
        $age = $patient?->birth_date ? $patient->birth_date->diff($letter->issued_date)->y . ' tahun' : '-';
        $v = $letter->vitals ?? [];
        $clinic = $clinicSettings['clinic_name'] ?? 'Klinik';
    @endphp
    <div class="controls">
        <button onclick="window.print()">Cetak</button>
        <button onclick="window.close()">Tutup</button>
    </div>
    <div class="sheet">
        @if($letter->isVoid())
            <div class="void">DIBATALKAN</div>
        @endif
        <div class="kop">
            <div>
                <h1>{{ $clinic }}</h1>
                <p>{{ $clinicSettings['clinic_address'] ?? '' }}</p>
                <p>
                    @if(! empty($clinicSettings['clinic_phone'])) Telp. {{ $clinicSettings['clinic_phone'] }} @endif
                    @if(! empty($clinicSettings['clinic_whatsapp'])) | WA {{ $clinicSettings['clinic_whatsapp'] }} @endif
                    @if(! empty($clinicSettings['clinic_email'])) | {{ $clinicSettings['clinic_email'] }} @endif
                </p>
            </div>
        </div>

        <h2>{{ $letter->type === 'referral' ? 'Surat Rujukan' : $letter->typeLabel() }}</h2>
        <div class="number">Nomor: {{ $letter->letter_number }}</div>

        @if($letter->type === 'referral')
            <p>Kepada Yth.<br>
                {{ $letter->referred_department ?: 'Teman Sejawat' }}<br>
                di {{ $letter->referred_to }}</p>
            <p>Dengan hormat,<br>Mohon pemeriksaan dan penanganan lebih lanjut terhadap pasien:</p>
        @else
            <p>Yang bertanda tangan di bawah ini, dokter/bidan pemeriksa pada {{ $clinic }}, menerangkan bahwa:</p>
        @endif

        <table class="data">
            <tr><td>Nama</td><td>: <strong>{{ $patient?->name }}</strong></td></tr>
            <tr><td>No. Rekam Medis</td><td>: {{ $patient?->no_rm }}</td></tr>
            @if($patient?->nik)<tr><td>NIK</td><td>: {{ $patient->nik }}</td></tr>@endif
            <tr><td>Tempat, Tgl. Lahir</td><td>: {{ $patient?->birth_place }}, {{ $fmt($patient?->birth_date) }}</td></tr>
            <tr><td>Umur</td><td>: {{ $age }}</td></tr>
            <tr><td>Jenis Kelamin</td><td>: {{ $patient?->gender === 'L' ? 'Laki-laki' : 'Perempuan' }}</td></tr>
            <tr><td>Alamat</td><td>: {{ $patient?->address }}</td></tr>
        </table>

        @if($letter->type === 'healthy')
            <p>Telah dilakukan pemeriksaan kesehatan pada tanggal {{ $fmt($letter->issued_date) }} dengan hasil:</p>
            <table class="data">
                <tr><td>Tinggi / Berat Badan</td><td>: {{ $v['height'] ?? '-' }} cm / {{ $v['weight'] ?? '-' }} kg</td></tr>
                <tr><td>Tekanan Darah</td><td>: {{ $v['blood_pressure'] ?? '-' }} mmHg</td></tr>
                <tr><td>Nadi</td><td>: {{ $v['pulse_rate'] ?? '-' }} x/menit</td></tr>
                <tr><td>Suhu</td><td>: {{ $v['body_temperature'] ?? '-' }} °C</td></tr>
                @if($patient?->blood_type)<tr><td>Golongan Darah</td><td>: {{ $patient->blood_type }}</td></tr>@endif
            </table>
            <p>Berdasarkan hasil pemeriksaan tersebut, yang bersangkutan dinyatakan dalam keadaan <strong>SEHAT</strong>.</p>
            <p>Surat keterangan ini dibuat untuk keperluan <strong>{{ $letter->purpose }}</strong>.</p>
        @elseif($letter->type === 'sick')
            <p>Berdasarkan hasil pemeriksaan pada tanggal {{ $fmt($letter->issued_date) }}, yang bersangkutan dalam keadaan sakit
                @if($letter->show_diagnosis && $letter->diagnosis) ({{ $letter->diagnosis }}) @endif
                dan memerlukan istirahat selama <strong>{{ $letter->rest_days }} ({{ \App\Models\MedicalLetter::terbilang((int) $letter->rest_days) }}) hari</strong>,
                terhitung mulai tanggal {{ $fmt($letter->rest_from) }} sampai dengan {{ $fmt($letter->rest_until) }}.</p>
        @else
            <table class="data">
                <tr><td>Anamnesa & Pemeriksaan</td><td>: {!! nl2br(e($letter->findings ?: '-')) !!}</td></tr>
                @if(array_filter($v))
                    <tr><td>Tanda Vital</td><td>: TD {{ $v['blood_pressure'] ?? '-' }} mmHg, Nadi {{ $v['pulse_rate'] ?? '-' }} x/mnt, RR {{ $v['respiratory_rate'] ?? '-' }} x/mnt, Suhu {{ $v['body_temperature'] ?? '-' }} °C, BB {{ $v['weight'] ?? '-' }} kg</td></tr>
                @endif
                <tr><td>Diagnosis Sementara</td><td>: {!! nl2br(e($letter->diagnosis)) !!}</td></tr>
                <tr><td>Terapi yang Diberikan</td><td>: {!! nl2br(e($letter->therapy ?: '-')) !!}</td></tr>
                <tr><td>Alasan Dirujuk</td><td>: {!! nl2br(e($letter->referral_reason)) !!}</td></tr>
            </table>
            <p>Atas bantuan dan kerja samanya, kami ucapkan terima kasih.</p>
        @endif

        @if($letter->notes)
            <p>Catatan: {!! nl2br(e($letter->notes)) !!}</p>
        @endif
        @if($letter->type !== 'referral')
            <p>Demikian surat keterangan ini dibuat untuk dipergunakan sebagaimana mestinya.</p>
        @endif

        <div class="sign">
            {{ $fmt($letter->issued_date) }}<br>
            {{ $letter->type === 'referral' ? 'Hormat kami,' : 'Dokter/Bidan Pemeriksa,' }}
            <div class="space"></div>
            <strong><u>{{ $doctor?->name }}</u></strong><br>
            SIP. {{ $doctor?->sip_number }}
        </div>
    </div>
</body>
</html>
