<?php

namespace App\Http\Controllers;

use App\Models\ClinicSetting;
use App\Services\AttendanceService;
use Illuminate\Http\Request;

/** Clinic location and rules used to accept a check-in */
class AttendanceSettingController extends Controller
{
    public function edit(AttendanceService $service)
    {
        $title = 'Pengaturan Absensi';
        $description = 'Titik lokasi klinik dan radius absensi';
        $settings = $service->settings();

        return view('pages.clinic.attendance.settings', compact('title', 'description', 'settings'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'attendance_latitude' => 'required|numeric|between:-90,90',
            'attendance_longitude' => 'required|numeric|between:-180,180',
            'attendance_radius' => 'required|integer|min:10|max:5000',
            'attendance_max_accuracy' => 'required|integer|min:0|max:5000',
            'attendance_early_minutes' => 'required|integer|min:0|max:240',
        ], [
            'attendance_latitude.required' => 'Isi latitude lokasi klinik.',
            'attendance_longitude.required' => 'Isi longitude lokasi klinik.',
        ]);
        // Unchecked checkboxes are not submitted, so read it as a boolean explicitly
        $data['attendance_allow_unscheduled'] = $request->boolean('attendance_allow_unscheduled') ? 1 : 0;

        foreach ($data as $key => $value) {
            ClinicSetting::set($key, (string) $value);
        }

        return redirect()->route('clinic.attendance-settings.edit', app()->getLocale())
            ->with('success', 'Pengaturan absensi disimpan.');
    }
}
