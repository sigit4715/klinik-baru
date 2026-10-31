<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use App\Models\Patient;

class PatientController extends Controller
{
    public function index(Request $request)
    {
        $query = Patient::query();

        // Search functionality
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('no_rm', 'like', "%{$search}%")
                    ->orWhere('nik', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Address filter
        if ($request->has('address') && $request->address != '') {
            $query->where('address', 'like', "%{$request->address}%");
        }

        // Gender filter
        if ($request->has('gender') && $request->gender != '') {
            $query->where('gender', $request->gender);
        }

        // Blood type filter
        if ($request->has('blood_type') && $request->blood_type != '') {
            $query->where('blood_type', $request->blood_type);
        }

        $patients = $query->orderBy('id', 'DESC')->paginate(10)->withQueryString();
        $title = "Daftar Pasien";
        $description = "Kelola data pasien klinik";

        return view('pages.clinic.patients.index', compact('title', 'description', 'patients'));
    }

    public function create()
    {
        $title = "Tambah Pasien Baru";
        $description = "Form pendaftaran pasien baru";
        $no_rm = Patient::generateNoRM();
        return view('pages.clinic.patients.create', compact('title', 'description', 'no_rm'));
    }

    public function store(Request $request)
    {
        $validators = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'nik' => 'required|string|size:16|unique:patients',
            'gender' => 'required|in:L,P',
            'birth_date' => 'required|date|before_or_equal:today',
            'birth_place' => 'required|string|max:100',
            'phone' => 'nullable|string|max:20',
            'address' => 'required|string',
            'emergency_contact' => 'nullable|string|max:100',
            'emergency_phone' => 'nullable|string|max:20',
        ]);

        $validators->setCustomMessages([
            'nik.unique' => 'NIK sudah terdaftar. Jika pasien ada di Arsip, pulihkan dari menu Arsip Data.',
        ]);

        if ($validators->fails()) {
            return redirect()->route('clinic.patients.create', app()->getLocale())
                ->withErrors($validators)->withInput();
        }

        $patient = new Patient();
        $patient->name = $request->name;
        $patient->nik = $request->nik;
        $patient->gender = $request->gender;
        $patient->birth_date = $request->birth_date;
        $patient->birth_place = $request->birth_place;
        $patient->phone = $request->phone;
        $patient->email = $request->email;
        $patient->address = $request->full_address ?: $request->address;
        $patient->blood_type = $request->blood_type;
        $patient->allergy = $request->allergy;
        $patient->emergency_contact = $request->emergency_contact;
        $patient->emergency_phone = $request->emergency_phone;
        $patient->saveWithUniqueNoRM();

        // Opened from the registration form: go straight back with the new patient selected
        if ($request->input('from') === 'registration' && $request->user()->can('appointments.manage')) {
            return redirect()->route('clinic.appointments.create', [app()->getLocale(), 'patient_id' => $patient->id])
                ->with('success', "Pasien {$patient->name} ({$patient->no_rm}) berhasil ditambahkan.");
        }

        return redirect()->route('clinic.patients.index', app()->getLocale())
            ->with('success', 'Pasien berhasil ditambahkan!');
    }

    public function show($language, $id)
    {
        $patient = Patient::with(['appointments', 'medicalRecords', 'payments'])->findOrFail($id);
        $title = "Detail Pasien";
        $description = "Informasi lengkap pasien";

        return view('pages.clinic.patients.show', compact('title', 'description', 'patient'));
    }

    public function edit($language, $id)
    {
        $patient = Patient::findOrFail($id);
        $title = "Edit Pasien";
        $description = "Ubah data pasien";

        return view('pages.clinic.patients.edit', compact('title', 'description', 'patient'));
    }

    public function update(Request $request, $language, $id)
    {
        $validators = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'nik' => 'required|string|size:16|unique:patients,nik,' . $id,
            'gender' => 'required|in:L,P',
            'birth_date' => 'required|date|before_or_equal:today',
            'birth_place' => 'required|string|max:100',
            'phone' => 'nullable|string|max:20',
            'address' => 'required|string',
            'emergency_contact' => 'nullable|string|max:100',
            'emergency_phone' => 'nullable|string|max:20',
        ]);

        $validators->setCustomMessages([
            'nik.unique' => 'NIK sudah terdaftar. Jika pasien ada di Arsip, pulihkan dari menu Arsip Data.',
        ]);

        if ($validators->fails()) {
            return redirect()->route('clinic.patients.edit', [app()->getLocale(), $id])
                ->withErrors($validators)->withInput();
        }

        $patient = Patient::findOrFail($id);
        $patient->name = $request->name;
        $patient->nik = $request->nik;
        $patient->gender = $request->gender;
        $patient->birth_date = $request->birth_date;
        $patient->birth_place = $request->birth_place;
        $patient->phone = $request->phone;
        $patient->email = $request->email;
        $patient->address = $request->address;
        $patient->blood_type = $request->blood_type;
        $patient->allergy = $request->allergy;
        $patient->emergency_contact = $request->emergency_contact;
        $patient->emergency_phone = $request->emergency_phone;
        $patient->save();

        return redirect()->route('clinic.patients.index', app()->getLocale())
            ->with('success', 'Data pasien berhasil diperbarui!');
    }

    public function destroy($language, $id)
    {
        $patient = Patient::findOrFail($id);

        if ($patient->appointments()->whereIn('status', \App\Models\Appointment::OPEN_STATUSES)->exists()) {
            return redirect()->route('clinic.patients.index', app()->getLocale())
                ->with('error', "Pasien {$patient->name} masih memiliki antrian yang belum selesai.");
        }

        // Soft delete: medical and payment history stay intact; restore from Arsip
        $patient->delete();
        return redirect()->route('clinic.patients.index', app()->getLocale())
            ->with('success', "Pasien {$patient->name} dipindahkan ke Arsip.");
    }

    public function search(Request $request)
    {
        $query = trim((string) $request->get('q'));
        if (mb_strlen($query) < 2) {
            return response()->json([]);
        }

        // Escape LIKE wildcards so "%" or "_" typed by the user match literally
        $like = '%' . addcslashes($query, '%_\\') . '%';
        $patients = Patient::where(function ($q) use ($like) {
                $q->where('name', 'like', $like)
                    ->orWhere('no_rm', 'like', $like)
                    ->orWhere('nik', 'like', $like);
            })
            ->orderBy('name')
            ->limit(15)
            // Only what a search dropdown needs; no address, phone or medical details
            ->get(['id', 'no_rm', 'name', 'nik', 'gender', 'birth_date']);
        return response()->json($patients);
    }
}
