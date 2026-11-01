<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ClinicSetting;

class ClinicSettingController extends Controller
{
    public function index()
    {
        $title = "Profil Klinik";
        $description = "Pengaturan informasi klinik";
        $settings = ClinicSetting::getAllSettings();

        return view('pages.clinic.settings.profile', compact('title', 'description', 'settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'clinic_name' => 'required|string|max:255',
            'clinic_address' => 'required|string|max:500',
            'clinic_phone' => 'nullable|string|max:20',
            'clinic_whatsapp' => 'nullable|string|max:20',
            'clinic_email' => 'nullable|email|max:255',
            'receipt_footer' => 'nullable|string|max:500',
            'receipt_paper' => 'nullable|in:58,80,a4',
            'compounding_fee' => 'nullable|numeric|min:0|max:100000000',
            'expiry_warning_days' => 'nullable|integer|min:1|max:365',
        ]);

        $fields = ['clinic_name', 'clinic_address', 'clinic_phone', 'clinic_whatsapp', 'clinic_email', 'receipt_footer',
            'receipt_paper', 'compounding_fee', 'expiry_warning_days'];
        foreach ($fields as $field) {
            if ($request->has($field)) {
                ClinicSetting::set($field, $request->input($field));
            }
        }

        // Switches: a hidden "0" input is sent when unchecked
        foreach (['receipt_show_items', 'receipt_show_doctor'] as $field) {
            if ($request->has($field)) {
                ClinicSetting::set($field, $request->boolean($field) ? '1' : '0');
            }
        }

        return redirect()->route('clinic.settings.profile', app()->getLocale())
            ->with('success', 'Profil klinik berhasil diperbarui!');
    }
}
