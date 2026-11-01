<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Poli;
use App\Models\Service;
use App\Models\SupportOrder;
use App\Models\SupportOrderFile;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Poli penunjang: order (pendaftaran/perawat/dokter), worklist, results and result files.
 * Files live on the private disk and are only served after a permission check.
 */
class SupportOrderController extends Controller
{
    public const DISK = 'private';
    public const MAX_FILE_KB = 10240;

    public function index(Request $request)
    {
        $date = $request->query('date') && preg_match('/^\d{4}-\d{2}-\d{2}$/', $request->query('date'))
            ? Carbon::parse($request->query('date')) : today();
        $poliId = $request->integer('poli_id') ?: null;
        $status = array_key_exists($request->query('status'), SupportOrder::STATUS_LABELS) ? $request->query('status') : null;

        $orders = SupportOrder::with(['patient', 'service', 'poli', 'appointment', 'orderedBy', 'files'])
            ->whereDate('created_at', $date->toDateString())
            ->when($poliId, fn ($q) => $q->where('poli_id', $poliId))
            ->when($status, fn ($q) => $q->where('status', $status), fn ($q) => $q->where('status', '!=', 'cancelled'))
            ->orderByRaw("CASE status WHEN 'in_progress' THEN 1 WHEN 'ordered' THEN 2 WHEN 'done' THEN 3 ELSE 4 END")
            ->orderBy('id')
            ->get();

        $polis = Poli::where('type', 'penunjang')->orderBy('name')->get();
        $title = 'Poli Penunjang';
        $description = 'Daftar pemeriksaan lab, radiologi, EKG';

        return view('pages.clinic.support.index', compact('title', 'description', 'orders', 'polis', 'date', 'poliId', 'status'));
    }

    public function create($language, $appointmentId)
    {
        $appointment = Appointment::with(['patient', 'supportOrders.service'])->findOrFail($appointmentId);
        if ($message = $this->orderBlockedReason($appointment)) {
            return back()->with('error', $message);
        }

        $services = Service::active()->support()->with('poli')->orderBy('name')->get();
        $title = 'Order Penunjang';
        $description = $appointment->queue_number . ' - ' . $appointment->patient?->name;

        return view('pages.clinic.support.create', compact('title', 'description', 'appointment', 'services'));
    }

    public function store(Request $request, $language, $appointmentId)
    {
        $data = $request->validate([
            'services' => 'required|array|min:1',
            'services.*' => ['integer', Rule::exists('services', 'id')->where('category', 'penunjang')->where('status', 'active')->whereNull('deleted_at')],
            'notes' => 'nullable|string|max:1000',
            'return_to' => 'nullable|string|max:500',
        ], ['services.required' => 'Pilih minimal satu pemeriksaan.']);

        $count = DB::transaction(function () use ($request, $data, $appointmentId) {
            $appointment = Appointment::lockForUpdate()->findOrFail($appointmentId);
            if ($message = $this->orderBlockedReason($appointment)) {
                throw ValidationException::withMessages(['services' => $message]);
            }

            $services = Service::whereIn('id', $data['services'])->get();
            foreach ($services as $service) {
                $order = new SupportOrder([
                    'appointment_id' => $appointment->id,
                    'patient_id' => $appointment->patient_id,
                    'service_id' => $service->id,
                    'poli_id' => $service->poli_id,
                    'qty' => 1,
                    'ordered_from' => $this->source($request),
                    'notes' => $data['notes'] ?? null,
                ]);
                $order->ordered_by = $request->user()->id;
                $order->save();
            }

            return $services->count();
        });

        $back = $this->safeReturn($request->input('return_to')) ?? route('clinic.appointments.show', [app()->getLocale(), $appointmentId]);

        return redirect($back)->with('success', "{$count} pemeriksaan penunjang dipesan.");
    }

    public function edit($language, $id)
    {
        $order = SupportOrder::with(['patient', 'service', 'poli', 'appointment.nursingAssessment', 'orderedBy', 'resultBy', 'files.uploader'])->findOrFail($id);
        $title = 'Hasil Penunjang';
        $description = $order->service?->name . ' - ' . $order->patient?->name;

        return view('pages.clinic.support.edit', compact('title', 'description', 'order'));
    }

    public function update(Request $request, $language, $id)
    {
        $data = $request->validate([
            'status' => 'required|in:in_progress,done',
            'result_text' => 'nullable|required_if:status,done|string|max:10000',
            'files' => 'nullable|array|max:10',
            'files.*' => 'file|mimes:pdf,jpg,jpeg,png|mimetypes:application/pdf,image/jpeg,image/png|max:' . self::MAX_FILE_KB,
        ], [
            'result_text.required_if' => 'Isi hasil/kesimpulan sebelum menandai selesai.',
            'files.*.mimes' => 'File hasil harus PDF, JPG, atau PNG.',
            'files.*.mimetypes' => 'File hasil harus PDF, JPG, atau PNG.',
            'files.*.max' => 'Ukuran file maksimal 10 MB.',
        ]);

        $order = SupportOrder::findOrFail($id);
        if ($order->status === 'cancelled') {
            return back()->with('error', 'Pemeriksaan ini sudah dibatalkan.');
        }

        $stored = [];
        try {
            DB::transaction(function () use ($request, $data, $order, &$stored) {
                foreach ($request->file('files', []) as $upload) {
                    // Random name; the original name is kept only in the database
                    $path = $upload->store('support-results/' . now()->format('Y/m'), self::DISK);
                    if (! $path) {
                        throw ValidationException::withMessages(['files' => 'File gagal disimpan.']);
                    }
                    $stored[] = $path;
                    $file = new SupportOrderFile([
                        'original_name' => mb_substr($upload->getClientOriginalName(), 0, 255),
                        'path' => $path,
                        'mime' => $upload->getMimeType(),
                        'size' => $upload->getSize(),
                    ]);
                    $file->support_order_id = $order->id;
                    $file->uploaded_by = $request->user()->id;
                    $file->save();
                }

                $order->result_text = $data['result_text'] ?? $order->result_text;
                $order->status = $data['status'];
                $order->result_by = $request->user()->id;
                if ($data['status'] === 'done') {
                    $order->result_at = $order->result_at ?? now();
                }
                $order->save();
            });
        } catch (\Throwable $e) {
            Storage::disk(self::DISK)->delete($stored);
            throw $e;
        }

        return redirect()->route('clinic.support-orders.edit', [app()->getLocale(), $order->id])
            ->with('success', $data['status'] === 'done' ? 'Hasil disimpan dan pemeriksaan ditandai selesai.' : 'Hasil sementara disimpan.');
    }

    public function cancel(Request $request, $language, $id)
    {
        $data = $request->validate(['cancel_reason' => 'required|string|max:255'], ['cancel_reason.required' => 'Isi alasan pembatalan.']);

        $order = SupportOrder::with('appointment')->findOrFail($id);
        if ($order->status === 'done') {
            return back()->with('error', 'Pemeriksaan yang sudah selesai tidak bisa dibatalkan.');
        }
        if ($order->appointment && $this->hasActivePayment($order->appointment)) {
            return back()->with('error', 'Kunjungan ini sudah memiliki pembayaran. Lakukan refund terlebih dahulu.');
        }

        $order->status = 'cancelled';
        $order->cancel_reason = $data['cancel_reason'];
        $order->save();

        return back()->with('success', 'Pemeriksaan penunjang dibatalkan.');
    }

    /** Stream a result file; never exposed through a public URL */
    public function download(Request $request, $language, $id)
    {
        $file = SupportOrderFile::findOrFail($id);
        abort_unless(Storage::disk(self::DISK)->exists($file->path), 404);

        $headers = ['Content-Type' => $file->mime, 'X-Content-Type-Options' => 'nosniff', 'Cache-Control' => 'private, no-store'];

        return $request->boolean('download')
            ? Storage::disk(self::DISK)->download($file->path, $file->original_name, $headers)
            : Storage::disk(self::DISK)->response($file->path, $file->original_name, $headers);
    }

    public function destroyFile($language, $id)
    {
        $file = SupportOrderFile::findOrFail($id);
        $orderId = $file->support_order_id;
        Storage::disk(self::DISK)->delete($file->path);
        $file->delete();

        return redirect()->route('clinic.support-orders.edit', [app()->getLocale(), $orderId])->with('success', 'File dihapus.');
    }

    /**
     * A visit that only came for support exams (no doctor) is closed here so the cashier can bill it.
     */
    public function completeVisit($language, $appointmentId)
    {
        $result = DB::transaction(function () use ($appointmentId) {
            $appointment = Appointment::lockForUpdate()->with('supportOrders')->findOrFail($appointmentId);

            if (! in_array($appointment->status, Appointment::OPEN_STATUSES, true)) {
                return 'Kunjungan ini sudah selesai atau dibatalkan.';
            }
            $active = $appointment->supportOrders->where('status', '!=', 'cancelled');
            if ($active->isEmpty()) {
                return 'Kunjungan ini tidak punya pemeriksaan penunjang.';
            }
            if ($active->contains(fn ($o) => $o->isOpen())) {
                return 'Masih ada pemeriksaan penunjang yang belum selesai.';
            }

            $appointment->status = 'completed';
            $appointment->save();

            return null;
        });

        if ($result) {
            return back()->with('error', $result);
        }

        return back()->with('success', 'Kunjungan selesai. Pasien bisa langsung ke kasir.');
    }

    /** Why new orders are not allowed for this visit, or null */
    private function orderBlockedReason(Appointment $appointment): ?string
    {
        if ($appointment->status === 'cancelled') {
            return 'Antrian ini sudah dibatalkan.';
        }
        if ($this->hasActivePayment($appointment)) {
            return 'Kunjungan ini sudah dibayar. Daftarkan kunjungan baru untuk pemeriksaan tambahan.';
        }

        return null;
    }

    private function hasActivePayment(Appointment $appointment): bool
    {
        return $appointment->payments()->whereIn('status', ['pending', 'paid'])->exists();
    }

    private function source(Request $request): string
    {
        $from = $request->input('from');
        if (array_key_exists($from, SupportOrder::SOURCES)) {
            return $from;
        }
        $user = $request->user();

        return match (true) {
            $user->can('medical_records.manage') && (bool) $user->doctor_id => 'dokter',
            $user->can('nursing.manage') => 'perawat',
            default => 'pendaftaran',
        };
    }

    /** Only redirect back to our own app paths */
    private function safeReturn(?string $url): ?string
    {
        if (! $url) {
            return null;
        }
        $base = rtrim(url('/'), '/');

        return str_starts_with($url, $base . '/') ? $url : null;
    }
}
