{{-- Refunds in the report period. Expects $refunds (collection of PaymentRefund). --}}
@if($refunds->isNotEmpty())
    <div class="card mt-4">
        <div class="card-header">
            <h6 class="mb-0 text-danger"><i class="uil uil-money-insert me-2"></i>Refund pada periode ini</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>Tanggal Refund</th>
                            <th>No. Invoice</th>
                            <th>Pasien</th>
                            <th>Metode</th>
                            <th>Alasan</th>
                            <th class="text-end">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($refunds as $refund)
                            <tr>
                                <td>{{ $refund->created_at->format('d/m/Y H:i') }}</td>
                                <td><code>{{ $refund->payment?->invoice_number }}</code></td>
                                <td>{{ $refund->payment?->patient?->name }}</td>
                                <td>{{ strtoupper($refund->refund_method) }}</td>
                                <td>{{ \Illuminate\Support\Str::limit($refund->reason, 60) }}</td>
                                <td class="text-end text-danger">- Rp {{ number_format($refund->amount, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="fw-bold">
                            <td colspan="5" class="text-end">Total Refund</td>
                            <td class="text-end text-danger">- Rp {{ number_format($refunds->sum('amount'), 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
@endif
