<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\Service;
use App\Models\ServiceFee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Doctor fee per service. Without ?doctor_id the page edits the default for all doctors;
 * with it, overrides for that doctor (empty = use the default).
 */
class ServiceFeeController extends Controller
{
    public function index(Request $request)
    {
        $doctorId = $request->integer('doctor_id') ?: null;
        $doctor = $doctorId ? Doctor::findOrFail($doctorId) : null;

        $services = Service::orderBy('name')->get();
        $defaults = ServiceFee::whereNull('doctor_id')->get()->keyBy('service_id');
        $overrides = $doctor ? ServiceFee::where('doctor_id', $doctor->id)->get()->keyBy('service_id') : collect();
        $doctors = Doctor::orderBy('name')->get();

        $title = 'Tarif Fee Tindakan';
        $description = $doctor ? 'Tarif khusus ' . $doctor->name : 'Tarif default semua dokter';

        return view('pages.clinic.payroll.service-fees', compact('title', 'description', 'services', 'defaults', 'overrides', 'doctor', 'doctors'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'doctor_id' => ['nullable', 'integer', Rule::exists('doctors', 'id')],
            'fees' => 'nullable|array',
            'fees.*.type' => 'required|in:fixed,percent',
            'fees.*.value' => 'nullable|numeric|min:0|max:999999999',
        ]);
        $doctorId = $data['doctor_id'] ?? null;

        foreach ($data['fees'] ?? [] as $serviceId => $fee) {
            if (($fee['type'] ?? '') === 'percent' && (float) ($fee['value'] ?? 0) > 100) {
                return back()->withInput()->withErrors(['fees' => 'Fee persen tidak boleh lebih dari 100%.']);
            }
        }

        $serviceIds = Service::pluck('id')->all();
        DB::transaction(function () use ($data, $doctorId, $serviceIds) {
            foreach ($data['fees'] ?? [] as $serviceId => $fee) {
                if (! in_array((int) $serviceId, $serviceIds, true)) {
                    continue;
                }
                $query = ServiceFee::where('service_id', $serviceId)
                    ->when($doctorId, fn ($q) => $q->where('doctor_id', $doctorId), fn ($q) => $q->whereNull('doctor_id'));

                if (($fee['value'] ?? '') === '' || $fee['value'] === null) {
                    // Empty: no default fee / no override (falls back to the default)
                    $query->get()->each->delete();
                    continue;
                }

                $rule = $query->first() ?? new ServiceFee(['service_id' => (int) $serviceId, 'doctor_id' => $doctorId]);
                $rule->fee_type = $fee['type'];
                $rule->fee_value = $fee['value'];
                $rule->save();
            }
        });

        return redirect()->route('clinic.service-fees.index', array_filter([app()->getLocale(), 'doctor_id' => $doctorId]))
            ->with('success', 'Tarif fee tindakan disimpan. Berlaku saat gaji dihitung (slip yang sudah dikunci tidak berubah).');
    }
}
