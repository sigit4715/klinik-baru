@section('title', 'Invoice - ' . $payment->invoice_number)
@section('description', 'Invoice pembayaran klinik')
@extends('layout.app')
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="shop-breadcrumb">
                    <div class="breadcrumb-main">
                        <h4 class="text-capitalize breadcrumb-title">Invoice</h4>
                        <div class="breadcrumb-action justify-content-center flex-wrap">
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a
                                            href="{{ route('clinic.dashboard', app()->getLocale()) }}"><i
                                                class="uil uil-estate"></i>Dashboard</a></li>
                                    <li class="breadcrumb-item"><a
                                            href="{{ route('clinic.payments.index', app()->getLocale()) }}">Pembayaran</a>
                                    </li>
                                    <li class="breadcrumb-item active" aria-current="page">Invoice</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="payment-invoice global-shadow radius-xl w-100 mb-30">
                    <div class="payment-invoice__body">
                        <!-- Header with Logo and Clinic Info -->
                        <div class="payment-invoice-address d-flex justify-content-sm-between flex-wrap">
                            <div class="payment-invoice-logo">
                                <h2 class="text-primary mb-1">{{ $clinicSettings['clinic_name'] ?? 'KLINIK' }}</h2>
                                <p class="text-muted fs-13">{{ $clinicSettings['clinic_address'] ?? '' }}</p>
                                <p class="text-muted fs-13">
                                    @if(!empty($clinicSettings['clinic_phone']))
                                        <i class="uil uil-phone"></i> {{ $clinicSettings['clinic_phone'] }}
                                    @endif
                                    @if(!empty($clinicSettings['clinic_whatsapp']))
                                        | <i class="uil uil-whatsapp"></i> {{ $clinicSettings['clinic_whatsapp'] }}
                                    @endif
                                </p>
                                @if(!empty($clinicSettings['clinic_email']))
                                    <p class="text-muted fs-13"><i class="uil uil-envelope"></i>
                                        {{ $clinicSettings['clinic_email'] }}</p>
                                @endif
                            </div>
                            <div class="payment-invoice-address__area text-end">
                                <span
                                    class="badge badge-{{ $payment->status === 'paid' ? 'success' : 'warning' }} badge-lg rounded-pill mb-2">
                                    {{ $payment->status === 'paid' ? 'LUNAS' : 'BELUM LUNAS' }}
                                </span>
                            </div>
                        </div>

                        <!-- Invoice Info Section -->
                        <div
                            class="payment-invoice-qr d-flex justify-content-between flex-wrap mb-40 px-xl-50 px-30 py-sm-30 py-20">
                            <div class="d-flex justify-content-center mb-lg-0 mb-25">
                                <div class="payment-invoice-qr__number">
                                    <div class="display-3 text-primary">Invoice</div>
                                    <p>No : <span class="fw-600">{{ $payment->invoice_number }}</span></p>
                                    <p>Tanggal : <span
                                            class="fw-600">{{ $payment->created_at->format('d M Y, H:i') }}</span></p>
                                </div>
                            </div>
                            <div class="d-flex justify-content-center mb-lg-0 mb-25">
                                <div class="payment-invoice-qr__address bg-opacity-primary p-25 radius-xl">
                                    <h6 class="mb-2 text-primary"><i class="uil uil-user me-1"></i>Data Pasien</h6>
                                    <p class="mb-1"><strong>{{ $payment->appointment->patient->name ?? 'N/A' }}</strong></p>
                                    <p class="mb-1 text-muted">No. RM: {{ $payment->appointment->patient->no_rm ?? '-' }}
                                    </p>
                                    <p class="mb-0 text-muted">{{ $payment->appointment->patient->phone ?? '-' }}</p>
                                </div>
                            </div>
                            <div class="d-flex justify-content-center">
                                <div class="payment-invoice-qr__address bg-opacity-success p-25 radius-xl">
                                    <h6 class="mb-2 text-success"><i class="uil uil-user-md me-1"></i>Dokter</h6>
                                    <p class="mb-1"><strong>{{ $payment->appointment->doctor?->name ?? 'N/A' }}</strong></p>
                                    <p class="mb-0 text-muted">{{ $payment->appointment->doctor?->specialization ?? '-' }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Invoice Table -->
                        <div class="payment-invoice-table">
                            <div class="table-responsive">
                                <table id="cart" class="table table-borderless">
                                    <thead>
                                        <tr class="product-cart__header">
                                            <th scope="col" style="width: 50px;">#</th>
                                            <th scope="col">Item</th>
                                            <th scope="col" class="text-end">Harga</th>
                                            <th scope="col" class="text-center">Qty</th>
                                            <th scope="col" class="text-end">Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php $items = is_array($payment->items) ? $payment->items : json_decode($payment->items ?? '[]', true);
                                        $no = 1; @endphp
                                        @forelse($items as $item)
                                            <tr>
                                                <th>{{ $no++ }}</th>
                                                <td class="Product-cart-title">
                                                    <div class="media align-items-center">
                                                        <div class="media-body">
                                                            <h6 class="mt-0 mb-1">{{ $item['name'] ?? 'Item' }}</h6>
                                                            <span
                                                                class="badge badge-sm badge-{{ ($item['type'] ?? '') === 'service' ? 'info' : 'success' }} rounded-pill">
                                                                {{ ($item['type'] ?? '') === 'service' ? 'Layanan' : 'Obat' }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="unit text-end">Rp
                                                    {{ number_format($item['price'] ?? 0, 0, ',', '.') }}</td>
                                                <td class="invoice-quantity text-center">{{ $item['qty'] ?? 1 }}</td>
                                                <td class="text-end order fw-600">Rp
                                                    {{ number_format(($item['subtotal'] ?? 0) > 0 ? $item['subtotal'] : (($item['qty'] ?? 1) * ($item['price'] ?? 0)), 0, ',', '.') }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <th>1</th>
                                                <td class="Product-cart-title">
                                                    <div class="media align-items-center">
                                                        <div class="media-body">
                                                            <h6 class="mt-0 mb-1">Biaya Konsultasi</h6>
                                                            <span class="badge badge-sm badge-info rounded-pill">Layanan</span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="unit text-end">Rp {{ number_format($payment->total, 0, ',', '.') }}
                                                </td>
                                                <td class="invoice-quantity text-center">1</td>
                                                <td class="text-end order fw-600">Rp
                                                    {{ number_format($payment->total, 0, ',', '.') }}</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="3"></td>
                                            <td class="order-summery float-right border-0">
                                                <div class="total text-end">
                                                    <div class="subtotalTotal mb-2">Subtotal :</div>
                                                    @if($payment->discount > 0)
                                                        <div class="taxes mb-2 text-danger">Diskon :</div>
                                                    @endif
                                                </div>
                                                <div class="total-money mt-3 pt-3 border-top text-end">
                                                    <h5 class="mb-0">Total :</h5>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="total-order float-right text-end fs-14 fw-500">
                                                    <p class="mb-2">Rp
                                                        {{ number_format($payment->subtotal ?? $payment->total, 0, ',', '.') }}
                                                    </p>
                                                    @if($payment->discount > 0)
                                                        <p class="mb-2 text-danger">- Rp
                                                            {{ number_format($payment->discount, 0, ',', '.') }}</p>
                                                    @endif
                                                    <h4 class="text-primary mt-3 pt-3 border-top mb-0">Rp
                                                        {{ number_format($payment->total, 0, ',', '.') }}</h4>
                                                </div>
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>

                            <!-- Payment Info -->
                            <div class="row mt-4 px-30">
                                <div class="col-md-6">
                                    <div class="card bg-opacity-primary p-20 border-0">
                                        <h6 class="text-primary mb-3"><i class="uil uil-credit-card me-2"></i>Informasi
                                            Pembayaran</h6>
                                        <p class="mb-1"><strong>Metode:</strong>
                                            {{ strtoupper($payment->payment_method ?? 'TUNAI') }}</p>
                                        <p class="mb-1"><strong>Status:</strong>
                                            <span
                                                class="badge badge-{{ $payment->status === 'paid' ? 'success' : 'warning' }}">
                                                {{ $payment->status === 'paid' ? 'LUNAS' : 'BELUM LUNAS' }}
                                            </span>
                                        </p>
                                        <p class="mb-0"><strong>Dibuat:</strong>
                                            {{ $payment->created_at->format('d/m/Y H:i') }}</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    @if(!empty($clinicSettings['receipt_footer']))
                                        <div class="card bg-opacity-success p-20 border-0">
                                            <h6 class="text-success mb-3"><i class="uil uil-comment-message me-2"></i>Catatan
                                            </h6>
                                            <p class="mb-0 fst-italic">{{ $clinicSettings['receipt_footer'] }}</p>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div
                                class="payment-invoice__btn mt-xxl-50 pt-xxl-30 d-flex justify-content-center gap-2 flex-wrap">
                                <button type="button" class="btn border rounded-pill bg-normal text-gray px-25"
                                    onclick="window.print()">
                                    <img src="{{ asset('assets/img/svg/printer.svg') }}" alt="printer"
                                        class="svg me-1">Cetak
                                </button>
                                <a href="{{ route('clinic.payments.receipt', [app()->getLocale(), $payment->id]) }}"
                                    class="btn border rounded-pill bg-normal text-gray px-25" target="_blank">
                                    <img src="{{ asset('assets/img/svg/file-text.svg') }}" alt="receipt"
                                        class="svg me-1">Struk Kasir
                                </a>
                                <a href="{{ route('clinic.payments.index', app()->getLocale()) }}"
                                    class="btn-primary btn rounded-pill px-25 text-white">
                                    <i class="uil uil-arrow-left me-1"></i>Kembali
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        @media print {

            .sidebar-wrapper,
            .header-top,
            .breadcrumb-main,
            .payment-invoice__btn,
            footer {
                display: none !important;
            }

            .contents {
                margin: 0 !important;
                padding: 0 !important;
            }

            .payment-invoice {
                box-shadow: none !important;
            }
        }
    </style>
@endsection