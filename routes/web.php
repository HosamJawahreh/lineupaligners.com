<?php

use App\Http\Controllers\Admin\DoctorRoleController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\PatientCaseMessageController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\PatientCaseModificationController;
use App\Http\Controllers\PatientManufacturingController;
use App\Http\Controllers\PatientCaseRefinementController;
use App\Http\Controllers\PatientTreatmentPlanController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\DoctorClinicSettingsController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ThemePageController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [LoginController::class, 'login']);

    Route::get('forgot-password', [ForgotPasswordController::class, 'show'])->name('pages.forgot-password');
    Route::post('forgot-password', [ForgotPasswordController::class, 'send']);
});

Route::post('logout', [LoginController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('notifications/feed', [NotificationController::class, 'feed'])->name('notifications.feed');
    Route::post('notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
    Route::post('notifications/{id}/read', [NotificationController::class, 'markRead'])->name('notifications.read');

    Route::middleware('role:admin,doctor')->group(function () {
        Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');

        Route::get('patients/export', [PatientController::class, 'export'])->name('patients.export');
        Route::get('patients/{patient}/scans/{scan}', [PatientController::class, 'downloadScan'])
            ->whereIn('scan', ['upper', 'lower'])
            ->name('patients.scans.download');
        Route::get('patients/{patient}/messages', [PatientCaseMessageController::class, 'index'])
            ->name('patients.messages.index');
        Route::post('patients/{patient}/messages', [PatientCaseMessageController::class, 'store'])
            ->name('patients.messages.store');
        Route::get('patients/{patient}/messages/{message}/attachment', [PatientCaseMessageController::class, 'downloadAttachment'])
            ->name('patients.messages.attachment');
        Route::post('patients/{patient}/treatment-plan', [PatientTreatmentPlanController::class, 'storeFull'])
            ->name('patients.treatment-plan.store');
        Route::post('patients/{patient}/treatment-plan/stages', [PatientTreatmentPlanController::class, 'storeStage'])
            ->name('patients.treatment-plan.stage.store');
        Route::post('patients/{patient}/treatment-plan/review', [PatientTreatmentPlanController::class, 'review'])
            ->name('patients.treatment-plan.review');
        Route::post('patients/{patient}/mark-manufactured', [PatientManufacturingController::class, 'markManufactured'])
            ->name('patients.mark-manufactured');
        Route::post('patients/{patient}/modifications', [PatientCaseModificationController::class, 'store'])
            ->name('patients.modifications.store');
        Route::get('patients/{patient}/modifications/{modification}/scans/{scan}', [PatientCaseModificationController::class, 'downloadScan'])
            ->whereIn('scan', ['upper', 'lower'])
            ->name('patients.modifications.scans.download');
        Route::post('patients/{patient}/refinements', [PatientCaseRefinementController::class, 'store'])
            ->name('patients.refinements.store');
        Route::get('patients/{patient}/refinements/{refinement}/scans/{scan}', [PatientCaseRefinementController::class, 'downloadScan'])
            ->whereIn('scan', ['upper', 'lower'])
            ->name('patients.refinements.scans.download');
        Route::resource('patients', PatientController::class);
    });

    Route::middleware('role:doctor')->group(function () {
        Route::get('clinic-settings', [DoctorClinicSettingsController::class, 'edit'])->name('doctor.clinic-settings.edit');
        Route::put('clinic-settings', [DoctorClinicSettingsController::class, 'update'])->name('doctor.clinic-settings.update');
    });

    Route::middleware('role:admin')->group(function () {
        Route::resource('doctors', DoctorController::class);
        Route::get('settings', [SettingsController::class, 'index'])->name('settings.index');
        Route::put('settings', [SettingsController::class, 'update'])->name('settings.update');
        Route::post('doctor-roles', [DoctorRoleController::class, 'store'])->name('doctor-roles.store');
        Route::put('doctor-roles/{doctorRole}', [DoctorRoleController::class, 'update'])->name('doctor-roles.update');
        Route::delete('doctor-roles/{doctorRole}', [DoctorRoleController::class, 'destroy'])->name('doctor-roles.destroy');

        Route::resource('departments', \App\Http\Controllers\DepartmentController::class);
        Route::resource('appointments', AppointmentController::class)->except(['show']);

        foreach (config('theme-pages', []) as $slug => $page) {
            if (! str_starts_with($page['route'], 'pages.')) {
                continue;
            }

            if ($slug === 'forgot-password') {
                continue;
            }

            Route::get('pages/'.$slug, function () use ($slug) {
                return app(ThemePageController::class)->show($slug);
            })->name($page['route']);
        }
    });
});
