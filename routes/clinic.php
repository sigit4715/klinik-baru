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
use App\Http\Controllers\ReportController;
use App\Http\Controllers\PatientReportController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\ArchiveController;
use App\Http\Controllers\AuditLogController;

/************************ Clinic Routes ******************************/
/*
 | Every route requires login. Access per menu is controlled by the
 | "permission:" middleware; permission keys live in config/permissions.php
 | and are assigned to roles in Pengaturan > Manajemen Role.
 | "permission:a,b" means the user needs a OR b.
 */
Route::group(['middleware' => 'auth'], function () {
    Route::group(['prefix' => '{language}'], function () {
        Route::group(['prefix' => 'clinic', 'as' => 'clinic.'], function () {

            // Dashboard: open to every logged-in user; finance widgets are hidden without reports.finance
            Route::get('dashboard', [ClinicDashboardController::class, 'index'])->name('dashboard');

            // Patients
            Route::group(['prefix' => 'patients', 'as' => 'patients.'], function () {
                Route::get('/', [PatientController::class, 'index'])->name('index')->middleware('permission:patients.view');
                Route::get('create', [PatientController::class, 'create'])->name('create')->middleware('permission:patients.manage');
                Route::post('store', [PatientController::class, 'store'])->name('store')->middleware('permission:patients.manage');
                Route::get('{id}', [PatientController::class, 'show'])->name('show')->middleware('permission:patients.view');
                Route::get('{id}/edit', [PatientController::class, 'edit'])->name('edit')->middleware('permission:patients.manage');
                Route::put('{id}', [PatientController::class, 'update'])->name('update')->middleware('permission:patients.manage');
                Route::delete('{id}', [PatientController::class, 'destroy'])->name('destroy')->middleware('permission:patients.delete');
                Route::get('api/search', [PatientController::class, 'search'])->name('search')->middleware('permission:patients.view,appointments.manage');
            });

            // Doctors
            Route::group(['prefix' => 'doctors', 'as' => 'doctors.'], function () {
                Route::get('/', [DoctorController::class, 'index'])->name('index')->middleware('permission:doctors.view');
                Route::get('create', [DoctorController::class, 'create'])->name('create')->middleware('permission:doctors.manage');
                Route::post('store', [DoctorController::class, 'store'])->name('store')->middleware('permission:doctors.manage');
                Route::get('{id}', [DoctorController::class, 'show'])->name('show')->middleware('permission:doctors.view');
                Route::get('{id}/edit', [DoctorController::class, 'edit'])->name('edit')->middleware('permission:doctors.manage');
                Route::put('{id}', [DoctorController::class, 'update'])->name('update')->middleware('permission:doctors.manage');
                Route::delete('{id}', [DoctorController::class, 'destroy'])->name('destroy')->middleware('permission:doctors.delete');
                Route::get('api/active', [DoctorController::class, 'getActive'])->name('active')->middleware('permission:doctors.view,appointments.manage');
            });

            // Services
            Route::group(['prefix' => 'services', 'as' => 'services.'], function () {
                Route::get('/', [ServiceController::class, 'index'])->name('index')->middleware('permission:services.view');
                Route::get('create', [ServiceController::class, 'create'])->name('create')->middleware('permission:services.manage');
                Route::post('store', [ServiceController::class, 'store'])->name('store')->middleware('permission:services.manage');
                Route::get('{id}/edit', [ServiceController::class, 'edit'])->name('edit')->middleware('permission:services.manage');
                Route::put('{id}', [ServiceController::class, 'update'])->name('update')->middleware('permission:services.manage');
                Route::delete('{id}', [ServiceController::class, 'destroy'])->name('destroy')->middleware('permission:services.delete');
                Route::get('api/active', [ServiceController::class, 'getActive'])->name('active')->middleware('permission:services.view,medical_records.manage');
            });

            // Medicines
            Route::group(['prefix' => 'medicines', 'as' => 'medicines.'], function () {
                Route::get('/', [MedicineController::class, 'index'])->name('index')->middleware('permission:medicines.view');
                Route::get('create', [MedicineController::class, 'create'])->name('create')->middleware('permission:medicines.manage');
                Route::post('store', [MedicineController::class, 'store'])->name('store')->middleware('permission:medicines.manage');
                Route::get('low-stock', [MedicineController::class, 'lowStock'])->name('low-stock')->middleware('permission:medicines.view');
                Route::get('{id}/edit', [MedicineController::class, 'edit'])->name('edit')->middleware('permission:medicines.manage');
                Route::put('{id}', [MedicineController::class, 'update'])->name('update')->middleware('permission:medicines.manage');
                Route::delete('{id}', [MedicineController::class, 'destroy'])->name('destroy')->middleware('permission:medicines.delete');
                Route::get('api/active', [MedicineController::class, 'getActive'])->name('active')->middleware('permission:medicines.view,medical_records.manage');
                Route::patch('{id}/stock', [MedicineController::class, 'updateStock'])->name('update-stock')->middleware('permission:medicines.stock');
                Route::get('{id}/stock-card', [MedicineController::class, 'stockCard'])->name('stock-card')->middleware('permission:medicines.view');
                Route::post('{id}/stock-movements', [MedicineController::class, 'storeMovement'])->name('stock-movements.store')->middleware('permission:medicines.stock');
            });

            // Appointments
            Route::group(['prefix' => 'appointments', 'as' => 'appointments.'], function () {
                Route::get('/', [AppointmentController::class, 'index'])->name('index')->middleware('permission:appointments.view');
                Route::get('today', [AppointmentController::class, 'today'])->name('today')->middleware('permission:appointments.view');
                Route::get('create', [AppointmentController::class, 'create'])->name('create')->middleware('permission:appointments.manage');
                Route::get('display', [AppointmentController::class, 'display'])->name('display')->middleware('permission:queue.display');
                Route::get('waiting', [AppointmentController::class, 'getWaitingQueue'])->name('waiting')->middleware('permission:queue.display,appointments.view');
                Route::post('store', [AppointmentController::class, 'store'])->name('store')->middleware('permission:appointments.manage');
                Route::post('call-next', [AppointmentController::class, 'callNext'])->name('call-next')->middleware('permission:appointments.call');
                Route::get('{id}/print-struk', [AppointmentController::class, 'printStruk'])->name('print-struk')->middleware('permission:appointments.view');
                Route::get('{id}', [AppointmentController::class, 'show'])->name('show')->middleware('permission:appointments.view');
                Route::get('{id}/edit', [AppointmentController::class, 'edit'])->name('edit')->middleware('permission:appointments.manage');
                Route::put('{id}', [AppointmentController::class, 'update'])->name('update')->middleware('permission:appointments.manage');
                Route::delete('{id}', [AppointmentController::class, 'destroy'])->name('destroy')->middleware('permission:appointments.delete');
                Route::patch('{id}/status', [AppointmentController::class, 'updateStatus'])->name('update-status')->middleware('permission:appointments.call');
                Route::post('{id}/call', [AppointmentController::class, 'callSpecific'])->name('call-specific')->middleware('permission:appointments.call');
            });

            // Medical Records
            Route::group(['prefix' => 'medical-records', 'as' => 'medical-records.'], function () {
                Route::get('/', [MedicalRecordController::class, 'index'])->name('index')->middleware('permission:medical_records.view');
                Route::get('create/{appointmentId?}', [MedicalRecordController::class, 'create'])->name('create')->middleware('permission:medical_records.manage');
                Route::post('store', [MedicalRecordController::class, 'store'])->name('store')->middleware('permission:medical_records.manage');
                Route::get('{id}', [MedicalRecordController::class, 'show'])->name('show')->middleware('permission:medical_records.view');
                Route::get('{id}/print', [MedicalRecordController::class, 'print'])->name('print')->middleware('permission:medical_records.view');
                Route::get('patient/{patientId}/history', [MedicalRecordController::class, 'patientHistory'])->name('patient-history')->middleware('permission:medical_records.view');
            });

            // Payments
            Route::group(['prefix' => 'payments', 'as' => 'payments.'], function () {
                Route::get('/', [PaymentController::class, 'index'])->name('index')->middleware('permission:payments.view');
                Route::get('create/{appointmentId?}', [PaymentController::class, 'create'])->name('create')->middleware('permission:payments.manage');
                Route::post('store', [PaymentController::class, 'store'])->name('store')->middleware('permission:payments.manage');
                Route::get('report', [PaymentController::class, 'report'])->name('report')->middleware('permission:reports.finance');
                Route::get('{id}', [PaymentController::class, 'show'])->name('show')->middleware('permission:payments.view');
                Route::get('{id}/invoice', [PaymentController::class, 'invoice'])->name('invoice')->middleware('permission:payments.view');
                Route::get('{id}/receipt', [PaymentController::class, 'receipt'])->name('receipt')->middleware('permission:payments.view');
                Route::delete('{id}', [PaymentController::class, 'destroy'])->name('destroy')->middleware('permission:payments.cancel');
                Route::patch('{id}/status', [PaymentController::class, 'updateStatus'])->name('update-status')->middleware('permission:payments.cancel');
                Route::get('{id}/refund', [PaymentController::class, 'refundForm'])->name('refund-form')->middleware('permission:payments.refund');
                Route::post('{id}/refund', [PaymentController::class, 'refund'])->name('refund')->middleware('permission:payments.refund');
            });

            // Reports
            Route::group(['prefix' => 'reports', 'as' => 'reports.', 'middleware' => 'permission:reports.finance'], function () {
                Route::get('daily', [ReportController::class, 'daily'])->name('daily');
                Route::get('monthly', [ReportController::class, 'monthly'])->name('monthly');
                Route::get('yearly', [ReportController::class, 'yearly'])->name('yearly');
            });

            // Patient Reports (KB, Imunisasi, Ibu Hamil, Melahirkan, Umum)
            Route::group(['prefix' => 'patient-reports', 'as' => 'patient-reports.', 'middleware' => 'permission:reports.patients'], function () {
                Route::get('kb', [PatientReportController::class, 'kb'])->name('kb');
                Route::get('imunisasi', [PatientReportController::class, 'imunisasi'])->name('imunisasi');
                Route::get('ibu-hamil', [PatientReportController::class, 'ibuHamil'])->name('ibu-hamil');
                Route::get('melahirkan', [PatientReportController::class, 'melahirkan'])->name('melahirkan');
                Route::get('umum', [PatientReportController::class, 'umum'])->name('umum');
            });

            // Settings
            Route::group(['prefix' => 'settings', 'as' => 'settings.', 'middleware' => 'permission:settings.manage'], function () {
                Route::get('profile', [ClinicSettingController::class, 'index'])->name('profile');
                Route::put('profile', [ClinicSettingController::class, 'update'])->name('profile.update');
            });

            // User Management
            Route::group(['prefix' => 'users', 'as' => 'users.', 'middleware' => 'permission:users.manage'], function () {
                Route::get('/', [UserManagementController::class, 'index'])->name('index');
                Route::get('create', [UserManagementController::class, 'create'])->name('create');
                Route::post('store', [UserManagementController::class, 'store'])->name('store');
                Route::get('{id}/edit', [UserManagementController::class, 'edit'])->name('edit');
                Route::put('{id}', [UserManagementController::class, 'update'])->name('update');
                Route::delete('{id}', [UserManagementController::class, 'destroy'])->name('destroy');
            });

            // Role & permission management
            Route::group(['prefix' => 'roles', 'as' => 'roles.', 'middleware' => 'permission:roles.manage'], function () {
                Route::get('/', [RoleController::class, 'index'])->name('index');
                Route::get('create', [RoleController::class, 'create'])->name('create');
                Route::post('store', [RoleController::class, 'store'])->name('store');
                Route::get('{id}/edit', [RoleController::class, 'edit'])->name('edit');
                Route::put('{id}', [RoleController::class, 'update'])->name('update');
                Route::delete('{id}', [RoleController::class, 'destroy'])->name('destroy');
            });

            // Arsip Data (soft-deleted records)
            Route::group(['prefix' => 'archive', 'as' => 'archive.', 'middleware' => 'permission:archive.manage'], function () {
                Route::get('/', [ArchiveController::class, 'index'])->name('index');
                Route::post('{type}/{id}/restore', [ArchiveController::class, 'restore'])->name('restore')->whereNumber('id');
            });

            // Audit Log (read only)
            Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index')->middleware('permission:audit.view');

            // Backup Database
            Route::group(['prefix' => 'backup', 'as' => 'backup.', 'middleware' => 'permission:backup.manage'], function () {
                Route::get('/', [BackupController::class, 'index'])->name('index');
                Route::post('create', [BackupController::class, 'create'])->name('create');
                Route::get('download/{filename}', [BackupController::class, 'download'])->name('download');
                Route::delete('{filename}', [BackupController::class, 'destroy'])->name('destroy');
            });

        });
    });
});
