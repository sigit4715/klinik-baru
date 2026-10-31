<?php

namespace App\Http\Controllers;

use App\Models\WorkShift;
use Illuminate\Http\Request;

class WorkShiftController extends Controller
{
    public function index()
    {
        $title = 'Shift Kerja';
        $description = 'Jam kerja yang dipakai di jadwal karyawan';
        $shifts = WorkShift::withCount('schedules')->orderBy('start_time')->get();
        $shift = new WorkShift(['start_time' => '07:00', 'end_time' => '14:00', 'late_tolerance_minutes' => 15, 'color' => '#3b82f6', 'status' => 'active']);

        return view('pages.clinic.attendance.shifts', compact('title', 'description', 'shifts', 'shift'));
    }

    public function store(Request $request)
    {
        $shift = new WorkShift($this->validated($request));
        $shift->save();

        return redirect()->route('clinic.work-shifts.index', app()->getLocale())->with('success', "Shift {$shift->name} ditambahkan.");
    }

    public function edit($language, $id)
    {
        $title = 'Edit Shift';
        $description = 'Ubah jam kerja';
        $shift = WorkShift::findOrFail($id);
        $shifts = WorkShift::withCount('schedules')->orderBy('start_time')->get();

        return view('pages.clinic.attendance.shifts', compact('title', 'description', 'shifts', 'shift'));
    }

    public function update(Request $request, $language, $id)
    {
        $shift = WorkShift::findOrFail($id);
        $shift->fill($this->validated($request));
        $shift->save();

        return redirect()->route('clinic.work-shifts.index', app()->getLocale())
            ->with('success', "Shift {$shift->name} diperbarui. Absensi yang sudah tercatat tidak berubah.");
    }

    public function destroy($language, $id)
    {
        $shift = WorkShift::withCount('schedules')->findOrFail($id);

        if ($shift->schedules_count > 0) {
            return redirect()->route('clinic.work-shifts.index', app()->getLocale())
                ->with('error', "Shift {$shift->name} sudah dipakai di jadwal. Ubah statusnya menjadi Tidak Aktif saja.");
        }

        $shift->delete();

        return redirect()->route('clinic.work-shifts.index', app()->getLocale())->with('success', "Shift {$shift->name} dihapus.");
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => 'required|string|max:50',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'late_tolerance_minutes' => 'required|integer|min:0|max:240',
            'color' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'status' => 'required|in:active,inactive',
        ]);
        $data['start_time'] .= ':00';
        $data['end_time'] .= ':00';

        return $data;
    }
}
