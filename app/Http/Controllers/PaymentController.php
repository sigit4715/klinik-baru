<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use App\Models\Payment;
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
            'patient_id' => 'required|exists:patients,id',
            'subtotal' => 'required|numeric|min:0',
            'total' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,debit,credit,transfer,bpjs',
        ]);

        if ($validators->fails()) {
            return redirect()->back()->withErrors($validators)->withInput();
        }

        // Process items to ensure subtotal is calculated correctly
        $items = $request->items ?? [];
        foreach ($items as $key => $item) {
            $qty = floatval($item['qty'] ?? 1);
            $price = floatval($item['price'] ?? 0);
            $items[$key]['subtotal'] = $qty * $price;
        }

        $payment = new Payment();
        $payment->invoice_number = Payment::generateInvoiceNumber();
        $payment->appointment_id = $request->appointment_id;
        $payment->patient_id = $request->patient_id;
        $payment->subtotal = $request->subtotal;
        $payment->discount = $request->discount ?? 0;
        $payment->tax = $request->tax ?? 0;
        $payment->total = $request->total;
        $payment->cash_received = $request->cash_received ?? 0;
        $payment->change_amount = max(0, ($request->cash_received ?? 0) - $request->total);
        $payment->payment_method = $request->payment_method;
        $payment->status = 'paid';
        $payment->items = $items;
        $payment->save();

        return redirect()->route('clinic.payments.show', [app()->getLocale(), $payment->id])
            ->with('success', 'Pembayaran berhasil disimpan!');
    }

    public function show($language, $id)
    {
        $payment = Payment::with(['patient', 'appointment'])->findOrFail($id);
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
        $payment = Payment::findOrFail($id);
        $payment->status = $request->status;
        $payment->save();

        return response()->json(['success' => true, 'status' => $payment->status]);
    }

    public function destroy($language, $id)
    {
        $payment = Payment::findOrFail($id);
        $payment->delete();
        return redirect()->route('clinic.payments.index', app()->getLocale())
            ->with('success', 'Pembayaran berhasil dihapus!');
    }

    public function report(Request $request)
    {
        $startDate = $request->start_date ?? today()->startOfMonth();
        $endDate = $request->end_date ?? today();

        $payments = Payment::with(['patient'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->paid()
            ->get();

        $totalRevenue = $payments->sum('total');
        $title = "Laporan Pembayaran";
        $description = "Laporan pendapatan klinik";

        return view('pages.clinic.payments.report', compact(
            'title',
            'description',
            'payments',
            'totalRevenue',
            'startDate',
            'endDate'
        ));
    }

    public function receipt($language, $id)
    {
        $payment = Payment::with(['patient', 'appointment.patient', 'appointment.doctor'])
            ->findOrFail($id);

        $clinicSettings = ClinicSetting::getAllSettings();

        return view('pages.clinic.payments.receipt', compact('payment', 'clinicSettings'));
    }
}
