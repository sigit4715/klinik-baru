<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use App\Models\Patient;

class PatientController extends Controller
{
    public function index()
    {
        $per_page = session('pagination_per_page') ?? 20;
        $patients = Patient::orderBy('id', 'DESC')->paginate($per_page);
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
            'birth_date' => 'required|date',
            'birth_place' => 'required|string|max:100',
            'phone' => 'required|string|max:20',
            'address' => 'required|string',
            'emergency_contact' => 'required|string|max:100',
            'emergency_phone' => 'required|string|max:20',
        ]);

        if ($validators->fails()) {
            return redirect()->route('clinic.patients.create', app()->getLocale())
                ->withErrors($validators)->withInput();
        }

        $patient = new Patient();
        $patient->no_rm = Patient::generateNoRM();
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
            'birth_date' => 'required|date',
            'birth_place' => 'required|string|max:100',
            'phone' => 'required|string|max:20',
            'address' => 'required|string',
            'emergency_contact' => 'required|string|max:100',
            'emergency_phone' => 'required|string|max:20',
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
        $patient->delete();
        return redirect()->route('clinic.patients.index', app()->getLocale())
            ->with('success', 'Pasien berhasil dihapus!');
    }

    public function search(Request $request)
    {
        $query = $request->get('q');
        $patients = Patient::where('name', 'like', "%{$query}%")
            ->orWhere('no_rm', 'like', "%{$query}%")
            ->orWhere('nik', 'like', "%{$query}%")
            ->limit(10)
            ->get();
        return response()->json($patients);
    }
}
