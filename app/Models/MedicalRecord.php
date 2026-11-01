<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicalRecord extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'appointment_id',
        'patient_id',
        'doctor_id',
        'anamnesa',
        'diagnosis',
        'blood_pressure',
        'body_temperature',
        'pulse_rate',
        'respiratory_rate',
        'height',
        'weight',
        'treatment',
        'prescriptions',
        'services_used',
        'notes',
    ];

    protected $casts = [
        'prescriptions' => 'array',
        'services_used' => 'array',
        'edited_at' => 'datetime',
    ];

    public function editor()
    {
        return $this->belongsTo(User::class, 'edited_by');
    }

    // withTrashed: history stays readable after a patient, doctor or visit is archived
    public function appointment()
    {
        return $this->belongsTo(Appointment::class)->withTrashed();
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class)->withTrashed();
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class)->withTrashed();
    }

    public function getPrescriptionDetails()
    {
        if (!$this->prescriptions)
            return [];

        // One query for all lines; archived medicines are still shown on old records
        $medicines = Medicine::withTrashed()
            ->whereIn('id', array_filter(array_column($this->prescriptions, 'medicine_id')))
            ->get()
            ->keyBy('id');

        $details = [];
        foreach ($this->prescriptions as $prescription) {
            $medicine = $medicines->get($prescription['medicine_id'] ?? null);
            if ($medicine) {
                $details[] = [
                    'medicine' => $medicine,
                    'qty' => $prescription['qty'] ?? 0,
                    'dosage' => $prescription['dosage'] ?? '',
                    'notes' => $prescription['notes'] ?? '',
                ];
            }
        }
        return $details;
    }

    /**
     * Racikan lines: {type: racikan, name, qty (bungkus/kapsul), dosage, notes, fee, components: [{medicine_id, qty}]}.
     * Price = sum(component qty x current sell price) + the compounding fee saved on the line.
     */
    public function getCompoundDetails(): array
    {
        $lines = array_values(array_filter($this->prescriptions ?? [], fn ($l) => ($l['type'] ?? null) === 'racikan'));
        if (! $lines) {
            return [];
        }

        $ids = collect($lines)->flatMap(fn ($l) => array_column($l['components'] ?? [], 'medicine_id'))->filter()->unique()->all();
        $medicines = Medicine::withTrashed()->whereIn('id', $ids)->get()->keyBy('id');

        $details = [];
        foreach ($this->prescriptions ?? [] as $index => $line) {
            if (($line['type'] ?? null) !== 'racikan') {
                continue;
            }
            $components = [];
            $price = (float) ($line['fee'] ?? 0);
            foreach ($line['components'] ?? [] as $component) {
                $medicine = $medicines->get($component['medicine_id'] ?? null);
                if ($medicine) {
                    $components[] = ['medicine' => $medicine, 'qty' => (int) $component['qty']];
                    $price += (int) $component['qty'] * (float) $medicine->sell_price;
                }
            }
            $details[] = [
                'index' => $index,
                'name' => $line['name'] ?? 'Racikan',
                'qty' => (int) ($line['qty'] ?? 1),
                'dosage' => $line['dosage'] ?? '',
                'notes' => $line['notes'] ?? '',
                'fee' => (float) ($line['fee'] ?? 0),
                'components' => $components,
                'price' => round($price, 2),
            ];
        }

        return $details;
    }

    /** Total quantity per medicine id over single lines and racikan components */
    public static function medicineTotals(array $lines): array
    {
        $totals = [];
        foreach ($lines as $line) {
            if (($line['type'] ?? null) === 'racikan') {
                foreach ($line['components'] ?? [] as $component) {
                    if (! empty($component['medicine_id'])) {
                        $totals[(int) $component['medicine_id']] = ($totals[(int) $component['medicine_id']] ?? 0) + (int) $component['qty'];
                    }
                }
            } elseif (! empty($line['medicine_id'])) {
                $totals[(int) $line['medicine_id']] = ($totals[(int) $line['medicine_id']] ?? 0) + (int) ($line['qty'] ?? 0);
            }
        }

        return $totals;
    }

    public function getServiceDetails()
    {
        if (!$this->services_used)
            return [];

        $services = Service::withTrashed()
            ->whereIn('id', array_filter(array_column($this->services_used, 'service_id')))
            ->get()
            ->keyBy('id');

        $details = [];
        foreach ($this->services_used as $serviceUsed) {
            $service = $services->get($serviceUsed['service_id'] ?? null);
            if ($service) {
                $details[] = [
                    'service' => $service,
                    'qty' => $serviceUsed['qty'] ?? 1,
                ];
            }
        }
        return $details;
    }
}
