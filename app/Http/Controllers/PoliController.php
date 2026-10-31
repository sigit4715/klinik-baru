<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Poli;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PoliController extends Controller
{
    public function index()
    {
        $title = 'Master Poli';
        $description = 'Daftar poli pemeriksaan dan penunjang';
        $polis = Poli::withCount('doctors')->orderBy('type')->orderBy('name')->get();
        $poli = new Poli(['type' => 'umum', 'status' => 'active']);

        return view('pages.clinic.polis.index', compact('title', 'description', 'polis', 'poli'));
    }

    public function store(Request $request)
    {
        $poli = new Poli();
        $poli->fill($this->validated($request));
        $poli->save();

        return redirect()->route('clinic.polis.index', app()->getLocale())
            ->with('success', "Poli {$poli->name} berhasil ditambahkan.");
    }

    public function edit($language, $id)
    {
        $title = 'Edit Poli';
        $description = 'Ubah data poli';
        $poli = Poli::findOrFail($id);
        $polis = Poli::withCount('doctors')->orderBy('type')->orderBy('name')->get();

        return view('pages.clinic.polis.index', compact('title', 'description', 'polis', 'poli'));
    }

    public function update(Request $request, $language, $id)
    {
        $poli = Poli::findOrFail($id);
        $poli->fill($this->validated($request, $poli->id));
        $poli->save();

        return redirect()->route('clinic.polis.index', app()->getLocale())
            ->with('success', "Poli {$poli->name} berhasil diperbarui.");
    }

    public function destroy($language, $id)
    {
        $poli = Poli::findOrFail($id);

        if (Appointment::where('poli_id', $poli->id)->whereIn('status', Appointment::OPEN_STATUSES)->exists()) {
            return redirect()->route('clinic.polis.index', app()->getLocale())
                ->with('error', "Poli {$poli->name} masih memiliki antrian yang belum selesai.");
        }

        $poli->delete();

        return redirect()->route('clinic.polis.index', app()->getLocale())
            ->with('success', "Poli {$poli->name} dipindahkan ke Arsip.");
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        $request->merge(['code' => strtoupper(trim((string) $request->code))]);

        return $request->validate([
            // Unique among all polis, including archived ones (the DB index covers them too)
            'code' => ['required', 'string', 'max:20', 'alpha_dash', Rule::unique('polis', 'code')->ignore($ignoreId)],
            'name' => 'required|string|max:100',
            'type' => ['required', Rule::in(array_keys(Poli::TYPES))],
            'status' => 'required|in:active,inactive',
        ], [
            'code.unique' => 'Kode poli sudah dipakai. Jika poli ada di Arsip, pulihkan dari menu Arsip Data.',
        ]);
    }
}
