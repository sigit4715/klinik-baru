{{-- Payslip breakdown. Expects $payslip --}}
@php $rp = fn ($v) => \App\Models\Payslip::rupiah($v); @endphp
<table class="slip-table table table-sm mb-3">
    <tbody>
        <tr><td colspan="2" class="fw-bold">Kehadiran</td></tr>
        <tr><td>Shift terjadwal</td><td class="text-end">{{ $payslip->scheduled_shifts }}</td></tr>
        <tr><td>Shift hadir</td><td class="text-end">{{ $payslip->attended_shifts }}</td></tr>
        <tr><td>Izin / sakit / cuti</td><td class="text-end">{{ $payslip->leave_shifts }}</td></tr>
        <tr><td>Alpa</td><td class="text-end">{{ $payslip->absent_shifts }}</td></tr>
        <tr><td>Terlambat</td><td class="text-end">{{ $payslip->late_count }} kali ({{ $payslip->late_minutes }} menit)</td></tr>

        <tr><td colspan="2" class="fw-bold pt-3">Pendapatan</td></tr>
        <tr><td>Gaji pokok</td><td class="text-end">{{ $rp($payslip->base_salary) }}</td></tr>
        @if((float) $payslip->sitting_fee_total > 0 || (float) $payslip->sitting_fee_rate > 0)
            <tr><td>Uang duduk ({{ $payslip->attended_shifts }} × {{ $rp($payslip->sitting_fee_rate) }})</td><td class="text-end">{{ $rp($payslip->sitting_fee_total) }}</td></tr>
        @endif
        @if((float) $payslip->service_fee_total > 0 || $payslip->service_count > 0)
            <tr><td>Fee tindakan ({{ $payslip->service_count }} tindakan)</td><td class="text-end">{{ $rp($payslip->service_fee_total) }}</td></tr>
        @endif
        @if((float) $payslip->allowance_total > 0)
            <tr><td>Tunjangan tetap</td><td class="text-end">{{ $rp($payslip->allowance_total) }}</td></tr>
        @endif
        @foreach(collect($payslip->adjustments ?? [])->where('type', 'add') as $line)
            <tr><td>{{ $line['label'] }}</td><td class="text-end">{{ $rp($line['amount']) }}</td></tr>
        @endforeach
        <tr class="fw-bold"><td>Total pendapatan</td><td class="text-end">{{ $rp($payslip->grossTotal()) }}</td></tr>

        <tr><td colspan="2" class="fw-bold pt-3">Potongan</td></tr>
        @if((float) $payslip->absence_deduction > 0)
            <tr><td>Alpa ({{ $payslip->absent_shifts }} shift)</td><td class="text-end">{{ $rp($payslip->absence_deduction) }}</td></tr>
        @endif
        @if((float) $payslip->late_deduction > 0)
            <tr><td>Terlambat</td><td class="text-end">{{ $rp($payslip->late_deduction) }}</td></tr>
        @endif
        @foreach(collect($payslip->adjustments ?? [])->where('type', 'deduct') as $line)
            <tr><td>{{ $line['label'] }}</td><td class="text-end">{{ $rp($line['amount']) }}</td></tr>
        @endforeach
        <tr class="fw-bold"><td>Total potongan</td><td class="text-end">{{ $rp($payslip->deductionTotal()) }}</td></tr>

        <tr class="fw-bold fs-5 border-top"><td class="pt-2">GAJI BERSIH</td><td class="text-end pt-2">{{ $rp($payslip->net_total) }}</td></tr>
    </tbody>
</table>

@if(! empty($payslip->service_details))
    <h6 class="mt-3">Rincian Fee Tindakan</h6>
    <table class="slip-table table table-sm table-bordered small">
        <thead><tr><th>Tanggal</th><th>Invoice</th><th>Pasien</th><th>Tindakan</th><th>Qty</th><th>Tarif</th><th class="text-end">Fee</th></tr></thead>
        <tbody>
            @foreach($payslip->service_details as $line)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($line['date'])->format('d/m') }}</td>
                    <td>{{ $line['invoice'] }}</td>
                    <td>{{ $line['patient'] }}</td>
                    <td>{{ $line['service'] }}</td>
                    <td>{{ $line['qty'] }}</td>
                    <td>{{ $line['rule'] }}</td>
                    <td class="text-end">{{ $rp($line['fee']) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif
