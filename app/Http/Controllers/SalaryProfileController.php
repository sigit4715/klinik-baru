<?php

namespace App\Http\Controllers;

use App\Models\SalaryProfile;
use App\Models\User;
use Illuminate\Http\Request;

/** "Pengaturan Gaji": salary scheme per employee */
class SalaryProfileController extends Controller
{
    public function index()
    {
        $employees = User::activeWithPermission('attendance.self')
            ->merge(User::whereHas('salaryProfile')->get())
            ->unique('id')
            ->sortBy('name')
            ->values()
            ->load(['salaryProfile', 'doctor', 'roleModel']);

        $title = 'Pengaturan Gaji';
        $description = 'Skema gaji per karyawan';

        return view('pages.clinic.payroll.profiles', compact('title', 'description', 'employees'));
    }

    public function edit($language, $userId)
    {
        $employee = User::with(['salaryProfile', 'doctor', 'roleModel'])->findOrFail($userId);
        $profile = $employee->salaryProfile ?? new SalaryProfile([
            'deduct_absence' => true,
            // Doctors usually get sitting fee + service fee
            'earns_service_fee' => (bool) $employee->doctor_id,
        ]);

        $title = 'Pengaturan Gaji';
        $description = $employee->name;

        return view('pages.clinic.payroll.profile-edit', compact('title', 'description', 'employee', 'profile'));
    }

    public function update(Request $request, $language, $userId)
    {
        $employee = User::findOrFail($userId);

        if ($request->boolean('remove')) {
            $employee->salaryProfile?->delete();

            return redirect()->route('clinic.salary-profiles.index', app()->getLocale())
                ->with('success', "Pengaturan gaji {$employee->name} dihapus. Karyawan ini tidak ikut dihitung di penggajian berikutnya.");
        }

        $money = 'required|numeric|min:0|max:999999999999';
        $data = $request->validate([
            'base_salary' => $money,
            'sitting_fee' => $money,
            'fixed_allowance' => $money,
            'allowance_note' => 'nullable|string|max:255',
            'late_fee_per_incident' => $money,
            'late_fee_per_minute' => $money,
            'notes' => 'nullable|string|max:255',
        ]);
        $data['deduct_absence'] = $request->boolean('deduct_absence');
        $data['earns_service_fee'] = $request->boolean('earns_service_fee');

        $profile = $employee->salaryProfile ?? new SalaryProfile();
        $profile->fill($data);
        $profile->user_id = $employee->id;
        $profile->save();

        $warning = $data['earns_service_fee'] && ! $employee->doctor_id
            ? ' Catatan: fee tindakan baru terhitung setelah akun ini dihubungkan ke data dokter di Manajemen User.'
            : '';

        return redirect()->route('clinic.salary-profiles.index', app()->getLocale())
            ->with('success', "Pengaturan gaji {$employee->name} disimpan." . $warning);
    }
}
