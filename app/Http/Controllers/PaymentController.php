<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Validator;
use App\Models\Payment;
use App\Models\PaymentRefund;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\MedicalRecord;
use App\Models\Medicine;
use App\Models\Service;
use App\Models\ClinicSetting;

class PaymentController extends Controller
{
    public function index()
    {
        $per_page = session('pagination_per_page') ?? 20;
        $payments = Payment::with(['patient', 'appointment'])
            ->orderBy('id', 'DESC')
            ->paginate($per_page);
        $title = "Daftar Pembayaran";
        $description = "Kelola pembayaran klinik";

        return view('pages.clinic.payments.index', compact('title', 'description', 'payments'));
    }

    public function create($language, $appointmentId = null)
    {
        $title = "Buat Pembayaran";
        $description = "Form pembayaran pasien";

        $appointment = null;
        $patient = null;
        $medicalRecord = null;
        $items = [];

        if ($appointmentId) {
            $appointment = Appointment::with(['patient', 'medicalRecord'])->findOrFail($appointmentId);
            $patient = $appointment->patient;
            $medicalRecord = $appointment->medicalRecord;

            if ($medicalRecord) {
                // Calculate items from medical record
                foreach ($medicalRecord->getPrescriptionDetails() as $prescription) {
                    $items[] = [
                        'type' => 'medicine',
                        'id' => $prescription['medicine']->id,
                        'name' => $prescription['medicine']->name,
                        'qty' => $prescription['qty'],
                        'price' => $prescription['medicine']->sell_price,
                        'subtotal' => $prescription['qty'] * $prescription['medicine']->sell_price,
                    ];
                }

                foreach ($medicalRecord->getServiceDetails() as $serviceDetail) {
                    $items[] = [
                        'type' => 'service',
                        'id' => $serviceDetail['service']->id,
                        'name' => $serviceDetail['service']->name,
                        'qty' => $serviceDetail['qty'],
                        'price' => $serviceDetail['service']->price,
                        'subtotal' => $serviceDetail['qty'] * $serviceDetail['service']->price,
                    ];
                }
            }
        }

        $medicines = Medicine::active()->get();
        $services = Service::active()->get();
        $invoice_number = Payment::generateInvoiceNumber();

        return view('pages.clinic.payments.create', compact(
            'title',
            'description',
            'appointment',
            'patient',
            'medicalRecord',
            'items',
            'medicines',
            'services',
            'invoice_number'
        ));
    }

    public function store(Request $request)
    {
        $validators = Validator::make($request->all(), [
            'appointment_id' => 'required|exists:appointments,id',
            'payment_method' => 'required|in:cash,debit,credit,transfer,bpjs',
            'items' => 'required|array|min:1',
            'items.*.type' => 'required|in:medicine,service',
            'items.*.id' => 'required|integer',
            'items.*.qty' => 'required|integer|min:1|max:10000',
            'discount' => 'nullable|numeric|min:0',
            'cash_received' => 'nullable|numeric|min:0',
        ], [
            'items.required' => 'Tidak ada item pembayaran.',
            'items.min' => 'Tidak ada item pembayaran.',
        ]);

        if ($validators->fails()) {
            return redirect()->back()->withErrors($validators)->withInput();
        }

        try {
            $payment = DB::transaction(function () use ($request) {
                // Lock the appointment so the same visit cannot be paid twice concurrently
                $appointment = Appointment::lockForUpdate()->findOrFail($request->appointment_id);

                if ($appointment->status === 'cancelled') {
                    throw ValidationException::withMessages(['appointment_id' => 'Antrian ini sudah dibatalkan.']);
                }

                // A cancelled or fully refunded payment allows billing the visit again
                $alreadyPaid = Payment::where('appointment_id', $appointment->id)
                    ->whereNotIn('status', ['cancelled', 'refunded'])
                    ->exists();
                if ($alreadyPaid) {
                    throw ValidationException::withMessages(['appointment_id' => 'Antrian ini sudah memiliki pembayaran.']);
                }

                // Rebuild items with prices from the database; client prices are ignored
                $items = [];
                $subtotal = 0;
                foreach ($request->input('items', []) as $item) {
                    $model = $item['type'] === 'medicine'
                        ? Medicine::withTrashed()->find($item['id'])
                        : Service::withTrashed()->find($item['id']);

                    if (! $model) {
                        throw ValidationException::withMessages(['items' => 'Item pembayaran tidak ditemukan.']);
                    }

                    $qty = (int) $item['qty'];
                    $price = (float) ($item['type'] === 'medicine' ? $model->sell_price : $model->price);
                    $lineTotal = round($qty * $price, 2);
                    $subtotal += $lineTotal;

                    $items[] = [
                        'type' => $item['type'],
                        'id' => $model->id,
                        'name' => $model->name,
                        'qty' => $qty,
                        'price' => $price,
                        'subtotal' => $lineTotal,
                    ];
                }

                $discount = round((float) $request->input('discount', 0), 2);
                if ($discount > $subtotal) {
                    throw ValidationException::withMessages(['discount' => 'Diskon tidak boleh melebihi subtotal.']);
                }

                $tax = 0;
                $total = round($subtotal - $discount + $tax, 2);

                if ($request->payment_method === 'cash') {
                    $cashReceived = round((float) $request->input('cash_received', 0), 2);
                    if ($cashReceived < $total) {
                        throw ValidationException::withMessages(['cash_received' => 'Uang yang diterima kurang dari total pembayaran.']);
                    }
                } else {
                    $cashReceived = $total;
                }

                $payment = new Payment();
                $payment->appointment_id = $appointment->id;
                $payment->patient_id = $appointment->patient_id;
                $payment->subtotal = $subtotal;
                $payment->discount = $discount;
                $payment->tax = $tax;
                $payment->total = $total;
                $payment->cash_received = $cashReceived;
                $payment->change_amount = round($cashReceived - $total, 2);
                $payment->payment_method = $request->payment_method;
                $payment->status = 'paid';
                $payment->items = $items;
                $payment->saveWithUniqueInvoiceNumber();

                return $payment;
            });
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        }

        return redirect()->route('clinic.payments.show', [app()->getLocale(), $payment->id])
            ->with('success', 'Pembayaran berhasil disimpan!');
    }

    public function show($language, $id)
    {
        $payment = Payment::with(['patient', 'appointment', 'refunds.user'])->findOrFail($id);
        $title = "Detail Pembayaran";
        $description = "Informasi pembayaran";

        return view('pages.clinic.payments.show', compact('title', 'description', 'payment'));
    }

    public function invoice($language, $id)
    {
        $payment = Payment::with(['patient', 'appointment.patient', 'appointment.doctor'])->findOrFail($id);
        $clinicSettings = ClinicSetting::getAllSettings();
        return view('pages.clinic.payments.invoice', compact('payment', 'clinicSettings'));
    }

    public function updateStatus(Request $request, $language, $id)
    {
        $request->validate([
            'status' => 'required|in:' . implode(',', Payment::STATUSES),
        ]);

        $payment = Payment::findOrFail($id);

        // pending -> paid/cancelled. A paid payment is reversed with a refund, not by changing its status.
        $allowed = [
            'pending' => ['paid', 'cancelled'],
            'paid' => [],
            'cancelled' => [],
            'refunded' => [],
        ];
        if (! in_array($request->status, $allowed[$payment->status] ?? [], true)) {
            return response()->json([
                'success' => false,
                'message' => $payment->status === 'paid'
                    ? 'Pembayaran lunas tidak bisa diubah statusnya. Gunakan Refund.'
                    : "Status pembayaran tidak bisa diubah dari {$payment->status} ke {$request->status}.",
            ], 422);
        }

        $payment->status = $request->status;
        $payment->save();

        return response()->json(['success' => true, 'status' => $payment->status]);
    }

    public function destroy($language, $id)
    {
        $payment = Payment::findOrFail($id);

        if (in_array($payment->status, ['paid', 'refunded'], true)) {
            return redirect()->back()
                ->with('error', 'Pembayaran yang sudah lunas tidak bisa dihapus. Gunakan Refund bila uang dikembalikan.');
        }

        $payment->delete();
        return redirect()->route('clinic.payments.index', app()->getLocale())
            ->with('success', 'Pembayaran berhasil dihapus!');
    }

    public function report(Request $request)
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $startDate = $request->start_date ? \Carbon\Carbon::parse($request->start_date)->startOfDay() : today()->startOfMonth();
        // Include the whole last day (today() alone is 00:00 and would drop it)
        $endDate = $request->end_date ? \Carbon\Carbon::parse($request->end_date)->endOfDay() : today()->endOfDay();

        $payments = Payment::with(['patient'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->revenue()
            ->get();

        // Refunds count on the day the money went back
        $refunds = PaymentRefund::with(['payment.patient', 'user'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderByDesc('created_at')
            ->get();

        $grossRevenue = $payments->sum('total');
        $totalRefunds = $refunds->sum('amount');
        $totalRevenue = $grossRevenue - $totalRefunds;
        $title = "Laporan Pembayaran";
        $description = "Laporan pendapatan klinik";

        return view('pages.clinic.payments.report', compact(
            'title',
            'description',
            'payments',
            'refunds',
            'grossRevenue',
            'totalRefunds',
            'totalRevenue',
            'startDate',
            'endDate'
        ));
    }

    public function refundForm($language, $id)
    {
        $payment = Payment::with(['patient', 'refunds.user'])->findOrFail($id);

        if ($payment->refundableAmount() <= 0) {
            return redirect()->route('clinic.payments.show', [app()->getLocale(), $payment->id])
                ->with('error', 'Pembayaran ini tidak bisa di-refund (belum lunas atau sudah di-refund penuh).');
        }

        $title = 'Refund Pembayaran';
        $description = 'Pengembalian dana ' . $payment->invoice_number;
        $hasMedicines = collect($payment->items)->contains(fn ($item) => ($item['type'] ?? null) === 'medicine');
        $alreadyRestocked = $payment->refunds->contains('restocked', true);

        return view('pages.clinic.payments.refund', compact('title', 'description', 'payment', 'hasMedicines', 'alreadyRestocked'));
    }

    /**
     * Full or partial refund. Recorded with reason and user; optional medicine restock on a full refund.
     */
    public function refund(Request $request, $language, $id)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:1',
            'reason' => 'required|string|min:5|max:500',
            'refund_method' => 'required|in:cash,debit,credit,transfer,bpjs',
            'restock' => 'nullable|boolean',
        ], [
            'reason.required' => 'Alasan refund wajib diisi.',
            'reason.min' => 'Alasan refund minimal 5 karakter.',
        ]);

        try {
            $refund = DB::transaction(function () use ($validated, $request, $id) {
                $payment = Payment::lockForUpdate()->findOrFail($id);
                $refundable = $payment->refundableAmount();
                $amount = round((float) $validated['amount'], 2);

                if ($refundable <= 0) {
                    throw ValidationException::withMessages(['amount' => 'Pembayaran ini tidak bisa di-refund.']);
                }
                if ($amount > $refundable) {
                    throw ValidationException::withMessages([
                        'amount' => 'Jumlah refund melebihi sisa yang bisa dikembalikan (Rp ' . number_format($refundable, 0, ',', '.') . ').',
                    ]);
                }

                $isFull = abs($amount - $refundable) < 0.01;
                $restock = $request->boolean('restock');
                if ($restock && ! $isFull) {
                    throw ValidationException::withMessages([
                        'restock' => 'Obat hanya bisa dikembalikan ke stok pada refund penuh. Untuk retur sebagian, catat lewat Kartu Stok.',
                    ]);
                }
                if ($restock && $payment->refunds()->where('restocked', true)->exists()) {
                    throw ValidationException::withMessages(['restock' => 'Obat dari pembayaran ini sudah pernah dikembalikan ke stok.']);
                }

                $refund = new PaymentRefund([
                    'payment_id' => $payment->id,
                    'amount' => $amount,
                    'reason' => $validated['reason'],
                    'refund_method' => $validated['refund_method'],
                    'restocked' => $restock,
                ]);
                $refund->user_id = auth()->id();
                $refund->save();

                $payment->refunded_amount = round((float) $payment->refunded_amount + $amount, 2);
                if ($isFull) {
                    $payment->status = 'refunded';
                }
                $payment->save();

                if ($restock) {
                    foreach ($payment->items as $item) {
                        if (($item['type'] ?? null) === 'medicine' && (int) ($item['qty'] ?? 0) > 0) {
                            Medicine::moveStock((int) $item['id'], (int) $item['qty'], 'return', $refund,
                                "Retur dari refund {$payment->invoice_number}");
                        }
                    }
                }

                return $refund;
            });
        } catch (ValidationException $e) {
            return back()->withInput()->withErrors($e->errors());
        }

        return redirect()->route('clinic.payments.show', [app()->getLocale(), $refund->payment_id])
            ->with('success', 'Refund Rp ' . number_format($refund->amount, 0, ',', '.') . ' berhasil dicatat.');
    }

    public function receipt($language, $id)
    {
        $payment = Payment::with(['patient', 'appointment.patient', 'appointment.doctor'])
            ->findOrFail($id);

        $clinicSettings = ClinicSetting::getAllSettings();

        return view('pages.clinic.payments.receipt', compact('payment', 'clinicSettings'));
    }
}
