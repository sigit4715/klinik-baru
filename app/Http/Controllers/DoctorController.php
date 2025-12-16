<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use App\Models\Doctor;
use Illuminate\Support\Facades\Storage;

class DoctorController extends Controller
{
    public function index()
    {
        $per_page = session('pagination_per_page') ?? 20;
        $doctors = Doctor::orderBy('id', 'DESC')->paginate($per_page);
        $title = "Daftar Dokter";
        $description = "Kelola data dokter klinik";

        return view('pages.clinic.doctors.index', compact('title', 'description', 'doctors'));
    }

    public function create()
    {
        $title = "Tambah Dokter Baru";
        $description = "Form pendaftaran dokter baru";
        return view('pages.clinic.doctors.create', compact('title', 'description'));
    }

    public function store(Request $request)
    {
        $validators = Validator::make($request->all(), [
            'sip_number' => 'required|string|unique:doctors',
            'name' => 'required|string|max:255',
            'specialization' => 'required|string|max:100',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|unique:doctors',
        ]);

        if ($validators->fails()) {
            return redirect()->route('clinic.doctors.create', app()->getLocale())
                ->withErrors($validators)->withInput();
        }

        $doctor = new Doctor();
        $doctor->sip_number = $request->sip_number;
        $doctor->name = $request->name;
        $doctor->specialization = $request->specialization;
        $doctor->phone = $request->phone;
        $doctor->email = $request->email;
        $doctor->address = $request->address;
        $doctor->status = $request->status ?? 'active';

        if ($request->hasFile('photo') && $request->file('photo')->isValid()) {
            $photo = $request->file('photo')->store('doctors', 'public');
            $doctor->photo = $photo;
        }

        if ($request->has('schedule')) {
            $doctor->schedule = $request->schedule;
        }

        $doctor->save();

        return redirect()->route('clinic.doctors.index', app()->getLocale())
            ->with('success', 'Dokter berhasil ditambahkan!');
    }

    public function show($language, $id)
    {
        $doctor = Doctor::with(['appointments', 'medicalRecords'])->findOrFail($id);
        $title = "Detail Dokter";
        $description = "Informasi lengkap dokter";

        return view('pages.clinic.doctors.show', compact('title', 'description', 'doctor'));
    }

    public function edit($language, $id)
    {
        $doctor = Doctor::findOrFail($id);
        $title = "Edit Dokter";
        $description = "Ubah data dokter";

        return view('pages.clinic.doctors.edit', compact('title', 'description', 'doctor'));
    }

    public function update(Request $request, $language, $id)
    {
        $validators = Validator::make($request->all(), [
            'sip_number' => 'required|string|unique:doctors,sip_number,' . $id,
            'name' => 'required|string|max:255',
            'specialization' => 'required|string|max:100',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|unique:doctors,email,' . $id,
        ]);

        if ($validators->fails()) {
            return redirect()->route('clinic.doctors.edit', [app()->getLocale(), $id])
                ->withErrors($validators)->withInput();
        }

        $doctor = Doctor::findOrFail($id);
        $doctor->sip_number = $request->sip_number;
        $doctor->name = $request->name;
        $doctor->specialization = $request->specialization;
        $doctor->phone = $request->phone;
        $doctor->email = $request->email;
        $doctor->address = $request->address;
        $doctor->status = $request->status ?? 'active';

        if ($request->hasFile('photo') && $request->file('photo')->isValid()) {
            if ($doctor->photo) {
                Storage::disk('public')->delete($doctor->photo);
            }
            $photo = $request->file('photo')->store('doctors', 'public');
            $doctor->photo = $photo;
        }

        if ($request->has('schedule')) {
            $doctor->schedule = $request->schedule;
        }

        $doctor->save();

        return redirect()->route('clinic.doctors.index', app()->getLocale())
            ->with('success', 'Data dokter berhasil diperbarui!');
    }

    public function destroy($language, $id)
    {
        $doctor = Doctor::findOrFail($id);
        if ($doctor->photo) {
            Storage::disk('public')->delete($doctor->photo);
        }
        $doctor->delete();
        return redirect()->route('clinic.doctors.index', app()->getLocale())
            ->with('success', 'Dokter berhasil dihapus!');
    }

    public function getActive()
    {
        $doctors = Doctor::active()->get();
        return response()->json($doctors);
    }
}
