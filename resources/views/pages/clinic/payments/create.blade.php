@section('title', $title)
@section('description', $description)
@extends('layout.app')

@push('css')
    <style>
        .payment-header {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border-radius: 16px;
            padding: 28px 32px;
            margin-bottom: 24px;
            color: white;
            box-shadow: 0 10px 40px rgba(16, 185, 129, 0.3);
        }

        .payment-header h4 {
            margin: 0;
            font-size: 22px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .payment-header .subtitle {
            opacity: 0.9;
            font-size: 14px;
            margin-top: 6px;
        }

        .info-card {
            background: white;
            border-radius: 14px;
            padding: 20px 24px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
            margin-bottom: 24px;
            border: 1px solid #e5e7eb;
        }

        .info-row {
            display: flex;
            gap: 24px;
        }

        .info-item {
            flex: 1;
        }

        .info-item label {
            font-size: 12px;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
            display: block;
        }

        .info-item .value {
            font-size: 15px;
            font-weight: 600;
            color: #1f2937;
            background: #f3f4f6;
            padding: 12px 16px;
            border-radius: 10px;
        }

        .items-card {
            background: white;
            border-radius: 14px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
            margin-bottom: 24px;
            overflow: hidden;
            border: 1px solid #e5e7eb;
        }

        .items-card .card-header {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            padding: 16px 24px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .items-card .card-header h6 {
            margin: 0;
            font-size: 15px;
            font-weight: 600;
            color: #1f2937;
            display: flex;
            align-items: center;
            gap: 8px;
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
            letter-spacing: 0.5px;
            border: none !important;
        }

        .items-table tbody tr {
            border-bottom: 1px solid #f3f4f6;
            transition: all 0.2s;
        }

        .items-table tbody tr:hover {
            background-color: #f8fafc;
        }

        .items-table tbody td {
            padding: 16px 20px;
            vertical-align: middle;
            border: none !important;
        }

        .item-name {
            font-weight: 600;
            color: #1f2937;
            font-size: 14px;
        }

        .item-type {
            font-size: 11px;
            color: #6b7280;
            margin-top: 2px;
        }

        .input-qty,
        .input-price {
            width: 90px;
            padding: 10px 12px;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 500;
            text-align: center;
            transition: all 0.2s;
        }

        .input-qty:focus,
        .input-price:focus {
            border-color: #10b981;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15);
            outline: none;
        }

        .input-price {
            width: 130px;
            text-align: right;
        }

        .subtotal-cell {
            font-weight: 700;
            color: #10b981;
            font-size: 15px;
        }

        .btn-delete-item {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            border: none;
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .btn-delete-item:hover {
            background: #ef4444;
            color: white;
        }

        .payment-section {
            background: white;
            border-radius: 14px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
            padding: 24px;
            margin-bottom: 24px;
            border: 1px solid #e5e7eb;
        }

        .payment-section h6 {
            font-size: 15px;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-group label {
            font-size: 12px;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }

        .form-control-styled {
            padding: 12px 16px;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            font-size: 14px;
            transition: all 0.2s;
        }

        .form-control-styled:focus {
            border-color: #10b981;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15);
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-top: 24px;
        }

        .summary-item {
            text-align: center;
            padding: 16px;
            border-radius: 12px;
            background: #f8fafc;
        }

        .summary-item.subtotal {
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
        }

        .summary-item.discount {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        }

        .summary-item.total {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
        }

        .summary-item.change {
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
            border: 2px solid #10b981;
        }

        .summary-item.change.insufficient {
            background: linear-gradient(135deg, #fef2f2 0%, #fecaca 100%);
            border-color: #ef4444;
        }

        .summary-item label {
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
            display: block;
        }

        .summary-item .value {
            font-size: 20px;
            font-weight: 700;
        }

        .summary-item.subtotal label {
            color: #3b82f6;
        }

        .summary-item.subtotal .value {
            color: #1d4ed8;
        }

        .summary-item.discount label {
            color: #d97706;
        }

        .summary-item.discount .value {
            color: #b45309;
        }

        .summary-item.total label {
            color: rgba(255, 255, 255, 0.85);
        }

        .summary-item.total .value {
            color: white;
        }

        .summary-item.change label {
            color: #059669;
        }

        .summary-item.change .value {
            color: #047857;
        }

        .summary-item.change.insufficient label {
            color: #dc2626;
        }

        .summary-item.change.insufficient .value {
            color: #b91c1c;
        }

        .cash-input-wrapper {
            position: relative;
        }

        .cash-input-wrapper .currency-prefix {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            font-weight: 600;
            color: #6b7280;
        }

        .cash-input-wrapper input {
            padding-left: 50px;
            font-size: 16px;
            font-weight: 600;
        }

        .btn-submit {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border: none;
            padding: 14px 32px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 15px;
            color: white;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(16, 185, 129, 0.4);
        }

        .btn-cancel {
            background: #f3f4f6;
            border: none;
            padding: 14px 28px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 15px;
            color: #6b7280;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-cancel:hover {
            background: #e5e7eb;
            color: #374151;
        }

        .empty-state {
            text-align: center;
            padding: 48px 20px;
            color: #9ca3af;
        }

        .empty-state i {
            font-size: 48px;
            margin-bottom: 12px;
        }

        @media (max-width: 768px) {
            .summary-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .info-row {
                flex-direction: column;
                gap: 16px;
            }
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid">
        <!-- Header -->
        <div class="payment-header">
            <h4><i class="uil uil-receipt"></i> Form Pembayaran</h4>
            <div class="subtitle">Proses pembayaran untuk layanan klinik</div>
        </div>

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="uil uil-exclamation-triangle me-2"></i>
                <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(($openSupport ?? 0) > 0)
            <div class="alert alert-warning">
                <i class="uil uil-flask me-1"></i>{{ $openSupport }} pemeriksaan penunjang belum selesai. Biayanya sudah ikut ditagihkan di bawah.
            </div>
        @endif

        <form action="{{ route('clinic.payments.store', app()->getLocale()) }}" method="POST" id="paymentForm">
            @csrf
            <input type="hidden" name="appointment_id" value="{{ $appointment->id ?? '' }}">
            <input type="hidden" name="patient_id" value="{{ $patient->id ?? '' }}">

            <!-- Info Card -->
            <div class="info-card">
                <div class="info-row">
                    <div class="info-item">
                        <label><i class="uil uil-file-alt me-1"></i>No. Invoice</label>
                        <div class="value">{{ $invoice_number }}</div>
                    </div>
                    <div class="info-item">
                        <label><i class="uil uil-user me-1"></i>Pasien</label>
                        <div class="value">{{ $patient->name ?? '-' }} ({{ $patient->no_rm ?? '-' }})</div>
                    </div>
                </div>
            </div>

            <!-- Items Table -->
            <div class="items-card">
                <div class="card-header">
                    <h6><i class="uil uil-list-ul"></i> Item Pembayaran</h6>
                    <span class="text-muted" style="font-size: 13px;">{{ count($items) }} item</span>
                </div>
                <div class="table-responsive">
                    <table class="items-table" id="itemsTable">
                        <thead>
                            <tr>
                                <th style="width: 40%;">Item</th>
                                <th style="width: 12%;">Qty</th>
                                <th style="width: 20%;">Harga</th>
                                <th style="width: 18%;">Subtotal</th>
                                <th style="width: 10%;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $subtotal = 0; @endphp
                            @forelse($items as $i => $item)
                                @php $subtotal += $item['subtotal']; @endphp
                                <tr class="item-row" data-index="{{ $i }}">
                                    <td>
                                        <div class="item-name">{{ $item['name'] }}</div>
                                        <div class="item-type">{{ $item['type'] == 'medicine' ? 'Obat' : 'Layanan' }}</div>
                                        <input type="hidden" name="items[{{ $i }}][name]" value="{{ $item['name'] }}">
                                        <input type="hidden" name="items[{{ $i }}][type]" value="{{ $item['type'] }}">
                                        <input type="hidden" name="items[{{ $i }}][id]" value="{{ $item['id'] }}">
                                    </td>
                                    <td>
                                        <input type="number" name="items[{{ $i }}][qty]" class="input-qty item-qty"
                                            value="{{ $item['qty'] }}" min="1" data-index="{{ $i }}">
                                    </td>
                                    <td>
                                        <input type="number" class="input-price item-price"
                                            value="{{ $item['price'] }}" min="0" data-index="{{ $i }}" readonly
                                            title="Harga mengikuti master obat/layanan">
                                    </td>
                                    <td class="subtotal-cell item-subtotal" data-index="{{ $i }}">
                                        Rp {{ number_format($item['subtotal'], 0, ',', '.') }}
                                    </td>
                                    <td>
                                        <button type="button" class="btn-delete-item" onclick="deleteItem({{ $i }})"
                                            title="Hapus Item">
                                            <i class="uil uil-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr id="emptyRow">
                                    <td colspan="5">
                                        <div class="empty-state">
                                            <i class="uil uil-box"></i>
                                            <p>Belum ada item pembayaran</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Payment Section -->
            <div class="payment-section">
                <h6><i class="uil uil-money-bill"></i> Detail Pembayaran</h6>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label>Metode Pembayaran</label>
                            <select name="payment_method" class="form-control form-control-styled" required>
                                <option value="cash">💵 Cash</option>
                                <option value="debit">💳 Debit</option>
                                <option value="credit">💳 Credit Card</option>
                                <option value="transfer">🏦 Transfer</option>
                                <option value="bpjs">🏥 BPJS</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label>Diskon (Rp)</label>
                            <input type="number" name="discount" id="discount" class="form-control form-control-styled"
                                value="0" min="0">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label>💰 Uang Diterima</label>
                            <div class="cash-input-wrapper">
                                <span class="currency-prefix">Rp</span>
                                <input type="number" name="cash_received" id="cashReceived"
                                    class="form-control form-control-styled" value="0" min="0" placeholder="0">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Summary Grid -->
                <div class="summary-grid">
                    <div class="summary-item subtotal">
                        <label>Subtotal</label>
                        <div class="value" id="subtotalDisplay">Rp {{ number_format($subtotal, 0, ',', '.') }}</div>
                        <input type="hidden" name="subtotal" id="subtotal" value="{{ $subtotal }}">
                    </div>
                    <div class="summary-item discount">
                        <label>Diskon</label>
                        <div class="value" id="discountDisplay">Rp 0</div>
                    </div>
                    <div class="summary-item total">
                        <label>Total Bayar</label>
                        <div class="value" id="totalDisplay">Rp {{ number_format($subtotal, 0, ',', '.') }}</div>
                        <input type="hidden" name="total" id="total" value="{{ $subtotal }}">
                    </div>
                    <div class="summary-item change" id="changeWrapper">
                        <label id="changeLabel">Kembalian</label>
                        <div class="value" id="changeDisplay">Rp 0</div>
                        <input type="hidden" name="change" id="change" value="0">
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="d-flex gap-3">
                <button type="submit" class="btn-submit" id="submitBtn">
                    <i class="uil uil-check me-2"></i>Simpan & Proses Pembayaran
                </button>
                <a href="{{ route('clinic.appointments.index', app()->getLocale()) }}" class="btn-cancel">
                    <i class="uil uil-times me-1"></i>Batal
                </a>
            </div>
        </form>
    </div>

    <script>
        // Initialize calculations
        document.addEventListener('DOMContentLoaded', function () {
            calculateTotal();

            // Event listeners for qty and price changes
            document.querySelectorAll('.item-qty, .item-price').forEach(el => {
                el.addEventListener('input', calculateTotal);
            });

            // Event listener for discount
            document.getElementById('discount').addEventListener('input', calculateTotal);

            // Event listener for cash received
            document.getElementById('cashReceived').addEventListener('input', calculateTotal);
        });

        function calculateTotal() {
            let subtotal = 0;

            // Calculate subtotal from all items
            document.querySelectorAll('.item-row').forEach(row => {
                const index = row.dataset.index;
                const qty = parseFloat(row.querySelector('.item-qty').value) || 0;
                const price = parseFloat(row.querySelector('.item-price').value) || 0;
                const itemSubtotal = qty * price;

                // Update item subtotal display
                row.querySelector('.item-subtotal').textContent = 'Rp ' + itemSubtotal.toLocaleString('id-ID');

                subtotal += itemSubtotal;
            });

            const discount = parseInt(document.getElementById('discount').value) || 0;
            const total = Math.max(0, subtotal - discount);
            const cashReceived = parseInt(document.getElementById('cashReceived').value) || 0;
            const change = cashReceived - total;

            // Update hidden fields
            document.getElementById('subtotal').value = subtotal;
            document.getElementById('total').value = total;
            document.getElementById('change').value = Math.max(0, change);

            // Update displays
            document.getElementById('subtotalDisplay').textContent = 'Rp ' + subtotal.toLocaleString('id-ID');
            document.getElementById('discountDisplay').textContent = 'Rp ' + discount.toLocaleString('id-ID');
            document.getElementById('totalDisplay').textContent = 'Rp ' + total.toLocaleString('id-ID');

            // Update change display with appropriate styling
            const changeWrapper = document.getElementById('changeWrapper');
            const changeLabel = document.getElementById('changeLabel');
            const changeDisplay = document.getElementById('changeDisplay');

            if (cashReceived > 0 && change < 0) {
                changeWrapper.classList.add('insufficient');
                changeLabel.textContent = 'Kurang';
                changeDisplay.textContent = 'Rp ' + Math.abs(change).toLocaleString('id-ID');
            } else {
                changeWrapper.classList.remove('insufficient');
                changeLabel.textContent = 'Kembalian';
                changeDisplay.textContent = 'Rp ' + Math.max(0, change).toLocaleString('id-ID');
            }
        }

        function deleteItem(index) {
            const row = document.querySelector(`.item-row[data-index="${index}"]`);
            if (row && confirm('Hapus item ini dari pembayaran?')) {
                row.remove();
                calculateTotal();

                // Check if no items left
                if (document.querySelectorAll('.item-row').length === 0) {
                    const tbody = document.querySelector('#itemsTable tbody');
                    tbody.innerHTML = `
                        <tr id="emptyRow">
                            <td colspan="5">
                                <div class="empty-state">
                                    <i class="uil uil-box"></i>
                                    <p>Belum ada item pembayaran</p>
                                </div>
                            </td>
                        </tr>
                    `;
                }
            }
        }

        // Form validation before submit
        document.getElementById('paymentForm').addEventListener('submit', function (e) {
            const total = parseInt(document.getElementById('total').value) || 0;
            const cashReceived = parseInt(document.getElementById('cashReceived').value) || 0;
            const paymentMethod = document.querySelector('[name="payment_method"]').value;

            // Only validate cash received for cash payment method
            if (paymentMethod === 'cash' && cashReceived < total) {
                e.preventDefault();
                alert('Uang yang diterima kurang dari total pembayaran!');
                document.getElementById('cashReceived').focus();
                return false;
            }

            if (document.querySelectorAll('.item-row').length === 0) {
                e.preventDefault();
                alert('Tidak ada item pembayaran!');
                return false;
            }
        });
    </script>
@endsection