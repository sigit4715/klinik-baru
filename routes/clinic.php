<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClinicDashboardController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\MedicineController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\MedicalRecordController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ClinicSettingController;

/************************ Clinic Routes ******************************/
Route::group(['middleware' => 'auth'], function () {
    Route::group(['prefix' => '{language}'], function () {
        Route::group(['prefix' => 'clinic', 'as' => 'clinic.'], function () {

            // Dashboard
            Route::get('dashboard', [ClinicDashboardController::class, 'index'])->name('dashboard');

            // Patients
            Route::group(['prefix' => 'patients', 'as' => 'patients.'], function () {
                Route::get('/', [PatientController::class, 'index'])->name('index');
                Route::get('create', [PatientController::class, 'create'])->name('create');
                Route::post('store', [PatientController::class, 'store'])->name('store');
                Route::get('{id}', [PatientController::class, 'show'])->name('show');
                Route::get('{id}/edit', [PatientController::class, 'edit'])->name('edit');
                Route::put('{id}', [PatientController::class, 'update'])->name('update');
                Route::delete('{id}', [PatientController::class, 'destroy'])->name('destroy');
                Route::get('api/search', [PatientController::class, 'search'])->name('search');
            });

            // Doctors
            Route::group(['prefix' => 'doctors', 'as' => 'doctors.'], function () {
                Route::get('/', [DoctorController::class, 'index'])->name('index');
                Route::get('create', [DoctorController::class, 'create'])->name('create');
                Route::post('store', [DoctorController::class, 'store'])->name('store');
                Route::get('{id}', [DoctorController::class, 'show'])->name('show');
                Route::get('{id}/edit', [DoctorController::class, 'edit'])->name('edit');
                Route::put('{id}', [DoctorController::class, 'update'])->name('update');
                Route::delete('{id}', [DoctorController::class, 'destroy'])->name('destroy');
                Route::get('api/active', [DoctorController::class, 'getActive'])->name('active');
            });

            // Services
            Route::group(['prefix' => 'services', 'as' => 'services.'], function () {
                Route::get('/', [ServiceController::class, 'index'])->name('index');
                Route::get('create', [ServiceController::class, 'create'])->name('create');
                Route::post('store', [ServiceController::class, 'store'])->name('store');
                Route::get('{id}/edit', [ServiceController::class, 'edit'])->name('edit');
                Route::put('{id}', [ServiceController::class, 'update'])->name('update');
                Route::delete('{id}', [ServiceController::class, 'destroy'])->name('destroy');
                Route::get('api/active', [ServiceController::class, 'getActive'])->name('active');
            });

            // Medicines
            Route::group(['prefix' => 'medicines', 'as' => 'medicines.'], function () {
                Route::get('/', [MedicineController::class, 'index'])->name('index');
                Route::get('create', [MedicineController::class, 'create'])->name('create');
                Route::post('store', [MedicineController::class, 'store'])->name('store');
                Route::get('low-stock', [MedicineController::class, 'lowStock'])->name('low-stock');
                Route::get('{id}/edit', [MedicineController::class, 'edit'])->name('edit');
                Route::put('{id}', [MedicineController::class, 'update'])->name('update');
                Route::delete('{id}', [MedicineController::class, 'destroy'])->name('destroy');
                Route::get('api/active', [MedicineController::class, 'getActive'])->name('active');
                Route::patch('{id}/stock', [MedicineController::class, 'updateStock'])->name('update-stock');
            });

            // Appointments
            Route::group(['prefix' => 'appointments', 'as' => 'appointments.'], function () {
                Route::get('/', [AppointmentController::class, 'index'])->name('index');
                Route::get('today', [AppointmentController::class, 'today'])->name('today');
                Route::get('create', [AppointmentController::class, 'create'])->name('create');
                Route::post('store', [AppointmentController::class, 'store'])->name('store');
                Route::get('{id}', [AppointmentController::class, 'show'])->name('show');
                Route::get('{id}/edit', [AppointmentController::class, 'edit'])->name('edit');
                Route::put('{id}', [AppointmentController::class, 'update'])->name('update');
                Route::delete('{id}', [AppointmentController::class, 'destroy'])->name('destroy');
                Route::patch('{id}/status', [AppointmentController::class, 'updateStatus'])->name('update-status');
                Route::post('call-next', [AppointmentController::class, 'callNext'])->name('call-next');
            });

            // Medical Records
            Route::group(['prefix' => 'medical-records', 'as' => 'medical-records.'], function () {
                Route::get('/', [MedicalRecordController::class, 'index'])->name('index');
                Route::get('create/{appointmentId?}', [MedicalRecordController::class, 'create'])->name('create');
                Route::post('store', [MedicalRecordController::class, 'store'])->name('store');
                Route::get('{id}', [MedicalRecordController::class, 'show'])->name('show');
                Route::get('{id}/print', [MedicalRecordController::class, 'print'])->name('print');
                Route::get('patient/{patientId}/history', [MedicalRecordController::class, 'patientHistory'])->name('patient-history');
            });

            // Payments
            Route::group(['prefix' => 'payments', 'as' => 'payments.'], function () {
                Route::get('/', [PaymentController::class, 'index'])->name('index');
                Route::get('create/{appointmentId?}', [PaymentController::class, 'create'])->name('create');
                Route::post('store', [PaymentController::class, 'store'])->name('store');
                Route::get('report', [PaymentController::class, 'report'])->name('report');
                Route::get('{id}', [PaymentController::class, 'show'])->name('show');
                Route::get('{id}/invoice', [PaymentController::class, 'invoice'])->name('invoice');
                Route::get('{id}/receipt', [PaymentController::class, 'receipt'])->name('receipt');
                Route::delete('{id}', [PaymentController::class, 'destroy'])->name('destroy');
                Route::patch('{id}/status', [PaymentController::class, 'updateStatus'])->name('update-status');
            });

            // Reports
            Route::group(['prefix' => 'reports', 'as' => 'reports.'], function () {
                Route::get('daily', [\App\Http\Controllers\ReportController::class, 'daily'])->name('daily');
                Route::get('monthly', [\App\Http\Controllers\ReportController::class, 'monthly'])->name('monthly');
                Route::get('yearly', [\App\Http\Controllers\ReportController::class, 'yearly'])->name('yearly');
            });

            // Settings
            Route::group(['prefix' => 'settings', 'as' => 'settings.'], function () {
                Route::get('profile', [ClinicSettingController::class, 'index'])->name('profile');
                Route::put('profile', [ClinicSettingController::class, 'update'])->name('profile.update');
            });

            // User Management
            Route::group(['prefix' => 'users', 'as' => 'users.'], function () {
                Route::get('/', [\App\Http\Controllers\UserManagementController::class, 'index'])->name('index');
                Route::get('create', [\App\Http\Controllers\UserManagementController::class, 'create'])->name('create');
                Route::post('store', [\App\Http\Controllers\UserManagementController::class, 'store'])->name('store');
                Route::get('{id}/edit', [\App\Http\Controllers\UserManagementController::class, 'edit'])->name('edit');
                Route::put('{id}', [\App\Http\Controllers\UserManagementController::class, 'update'])->name('update');
                Route::delete('{id}', [\App\Http\Controllers\UserManagementController::class, 'destroy'])->name('destroy');
            });

        });
    });
});
