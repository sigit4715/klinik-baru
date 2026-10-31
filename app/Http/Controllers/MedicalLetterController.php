<?php

namespace App\Http\Controllers;

use App\Models\ClinicSetting;
use App\Models\Doctor;
use App\Models\MedicalLetter;
use App\Models\MedicalRecord;
use App\Models\NursingAssessment;
use App\Models\Patient;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Surat medis: keterangan sehat, keterangan sakit, rujukan.
 */
class MedicalLetterController extends Controller
{
    private const VITAL_FIELDS = ['blood_pressure', 'body_temperature', 'pulse_rate', 'respiratory_rate', 'height', 'weight'];

    public function index(Request $request)
    {
        $type = array_key_exists($request->query('type'), MedicalLetter::TYPES) ? $request->query('type') : null;
        $search = trim((string) $request->query('search'));

        $letters = MedicalLetter::with(['patient', 'doctor'])
            ->when($type, fn ($q) => $q->where('type', $type))
            ->when($search !== '', function ($q) use ($search) {
                $like = '%' . addcslashes($search, '%_\\') . '%';
                $q->where(function ($q) use ($like) {
                    $q->where('letter_number', 'like', $like)
                        ->orWhereHas('patient', fn ($p) => $p->where('name', 'like', $like)->orWhere('no_rm', 'like', $like)->orWhere('nik', 'like', $like));
                });
            })
            ->orderByDesc('issued_date')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        $title = 'Surat Medis';
        $description = 'Surat keterangan sehat, sakit, dan rujukan';

        return view('pages.clinic.letters.index', compact('title', 'description', 'letters', 'type', 'search'));
    }

    public function create(Request $request)
    {
        $type = $request->query('type');
        abort_unless(array_key_exists($type, MedicalLetter::TYPES), 404);

        $letter = new MedicalLetter(['type' => $type, 'issued_date' => today(), 'rest_from' => today(), 'rest_days' => 1]);
        $record = null;

        if ($recordId = $request->integer('medical_record_id')) {
            $record = MedicalRecord::with(['patient', 'appointment.nursingAssessment'])->findOrFail($recordId);
            $letter->patient_id = $record->patient_id;
            $letter->doctor_id = $record->doctor_id;
            $letter->medical_record_id = $record->id;
            $letter->diagnosis = $record->diagnosis;
            $letter->findings = trim(implode("\n", array_filter([$record->appointment?->nursingAssessment?->chief_complaint, $record->anamnesa])));
            $letter->therapy = $this->therapySummary($record);
            $letter->vitals = collect(self::VITAL_FIELDS)->mapWithKeys(fn ($f) => [$f => $record->{$f}])->filter(fn ($v) => $v !== null)->all();
            $letter->issued_date = $record->created_at->isToday() ? today() : $record->created_at->startOfDay();
            $letter->rest_from = $letter->issued_date;
        } elseif ($patientId = $request->integer('patient_id')) {
            $letter->patient_id = Patient::findOrFail($patientId)->id;
        }

        $letter->doctor_id ??= $request->user()->doctor_id;

        return $this->form($letter, $record);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $letter = new MedicalLetter($data);
        $letter->created_by = $request->user()->id;
        $letter->status = 'valid';
        $letter->saveWithNumber();

        return redirect()->route('clinic.letters.index', app()->getLocale())
            ->with('success', "{$letter->typeLabel()} nomor {$letter->letter_number} dibuat.")
            ->with('print_letter_id', $letter->id);
    }

    public function edit($language, $id)
    {
        $letter = MedicalLetter::with('medicalRecord')->findOrFail($id);
        if ($letter->isVoid()) {
            return redirect()->route('clinic.letters.index', app()->getLocale())->with('error', 'Surat yang dibatalkan tidak bisa diubah.');
        }

        return $this->form($letter, $letter->medicalRecord);
    }

    public function update(Request $request, $language, $id)
    {
        $letter = MedicalLetter::findOrFail($id);
        if ($letter->isVoid()) {
            return redirect()->route('clinic.letters.index', app()->getLocale())->with('error', 'Surat yang dibatalkan tidak bisa diubah.');
        }

        $data = $this->validated($request, $letter->type);
        // Number and type stay fixed once issued
        unset($data['type']);
        $letter->fill($data);
        $letter->save();

        return redirect()->route('clinic.letters.index', app()->getLocale())
            ->with('success', "Surat {$letter->letter_number} diperbarui.")
            ->with('print_letter_id', $letter->id);
    }

    public function void(Request $request, $language, $id)
    {
        $data = $request->validate(['void_reason' => 'required|string|max:255'], ['void_reason.required' => 'Isi alasan pembatalan.']);
        $letter = MedicalLetter::findOrFail($id);
        $letter->status = 'void';
        $letter->void_reason = $data['void_reason'];
        $letter->save();

        return back()->with('success', "Surat {$letter->letter_number} dibatalkan.");
    }

    public function print($language, $id)
    {
        $letter = MedicalLetter::with(['patient', 'doctor'])->findOrFail($id);
        $clinicSettings = ClinicSetting::getAllSettings();

        return view('pages.clinic.letters.print', compact('letter', 'clinicSettings'));
    }

    private function form(MedicalLetter $letter, ?MedicalRecord $record)
    {
        $doctors = Doctor::active()->orderBy('name')->get();
        if ($letter->doctor_id && ! $doctors->contains('id', $letter->doctor_id)) {
            $doctors->push(Doctor::withTrashed()->find($letter->doctor_id));
        }
        $patient = $letter->patient_id ? Patient::withTrashed()->find($letter->patient_id) : null;

        $title = ($letter->exists ? 'Edit ' : 'Buat ') . $letter->typeLabel();
        $description = $letter->letter_number ?? 'Surat baru';

        return view('pages.clinic.letters.form', compact('title', 'description', 'letter', 'record', 'doctors', 'patient'));
    }

    private function validated(Request $request, ?string $type = null): array
    {
        $type ??= $request->input('type');
        abort_unless(array_key_exists($type, MedicalLetter::TYPES), 422);

        $rules = [
            'patient_id' => ['required', 'integer', Rule::exists('patients', 'id')],
            'doctor_id' => ['required', 'integer', Rule::exists('doctors', 'id')],
            'medical_record_id' => ['nullable', 'integer', Rule::exists('medical_records', 'id')->where('patient_id', $request->input('patient_id'))],
            'issued_date' => 'required|date|before_or_equal:' . today()->addDays(30)->toDateString(),
            'diagnosis' => ($type === 'referral' ? 'required' : 'nullable') . '|string|max:2000',
            'notes' => 'nullable|string|max:2000',
        ];
        $vitalRules = array_intersect_key(NursingAssessment::vitalRules(), array_flip(self::VITAL_FIELDS));
        foreach ($vitalRules as $field => $rule) {
            $rules["vitals.{$field}"] = $rule;
        }

        $rules += match ($type) {
            'healthy' => ['purpose' => 'required|string|max:255'],
            'sick' => ['rest_days' => 'required|integer|min:1|max:60', 'rest_from' => 'required|date'],
            'referral' => [
                'referred_to' => 'required|string|max:255',
                'referred_department' => 'nullable|string|max:255',
                'referral_reason' => 'required|string|max:2000',
                'findings' => 'nullable|string|max:3000',
                'therapy' => 'nullable|string|max:3000',
            ],
        };

        $data = $request->validate($rules, [
            'purpose.required' => 'Isi keperluan surat (contoh: melamar pekerjaan).',
            'rest_days.required' => 'Isi lama istirahat.',
            'referred_to.required' => 'Isi fasilitas kesehatan tujuan rujukan.',
            'referral_reason.required' => 'Isi alasan dirujuk.',
            'diagnosis.required' => 'Isi diagnosis (sementara).',
            'vitals.blood_pressure.regex' => 'Format tekanan darah harus seperti 120/80.',
        ]);

        $data['type'] = $type;
        $data['vitals'] = array_filter($data['vitals'] ?? [], fn ($v) => $v !== null && $v !== '');
        $data['show_diagnosis'] = $request->boolean('show_diagnosis');

        if ($type === 'sick') {
            $from = Carbon::parse($data['rest_from']);
            $data['rest_until'] = $from->copy()->addDays($data['rest_days'] - 1)->toDateString();
        }

        return $data;
    }

    /** Treatment and prescriptions of a record, for the referral letter */
    private function therapySummary(MedicalRecord $record): string
    {
        $lines = [];
        if ($record->treatment) {
            $lines[] = $record->treatment;
        }
        foreach ($record->getPrescriptionDetails() as $item) {
            $lines[] = '- ' . $item['medicine']->name . ' ' . $item['qty'] . ($item['dosage'] ? ' (' . $item['dosage'] . ')' : '');
        }
        foreach ($record->getServiceDetails() as $item) {
            $lines[] = '- ' . $item['service']->name;
        }

        return implode("\n", $lines);
    }
}
