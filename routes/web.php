<?php

use App\Http\Controllers\Admin\DoctorRoleController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\WebsiteController;
use App\Http\Controllers\PublicWebsiteController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ResetPasswordController;
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
use App\Http\Middleware\SetWebsiteLocale;
use Illuminate\Support\Facades\Route;

Route::get('sitemap.xml', \App\Http\Controllers\PublicWebsiteSitemapController::class)->name('website.sitemap');
Route::get('robots.txt', function () {
    $lines = [
        'User-agent: *',
        'Allow: /',
        'Disallow: /admin',
        'Disallow: /dashboard',
        'Disallow: /login',
        'Disallow: /patients',
        'Disallow: /profile',
        '',
        'Sitemap: '.url('/sitemap.xml'),
    ];

    return response(implode("\n", $lines), 200, ['Content-Type' => 'text/plain']);
})->name('website.robots');

$smilizPages = require __DIR__.'/smiliz-pages.php';

Route::middleware(SetWebsiteLocale::class)->group(function () use ($smilizPages) {
    Route::post('website/inquiry', [\App\Http\Controllers\PublicWebsiteInquiryController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('website.inquiry.store');
    Route::get('/', [PublicWebsiteController::class, 'show'])->name('website.home');
    $smilizPages('', 'website.page.');
    Route::get('services/{slug}', [PublicWebsiteController::class, 'serviceDetail'])
        ->where('slug', '[a-z0-9\-]+')
        ->name('website.service');
    Route::get('blog/{slug}', [PublicWebsiteController::class, 'blogDetail'])
        ->where('slug', '[a-z0-9\-]+')
        ->name('website.blog');
    Route::get('case-studies/{slug}', [PublicWebsiteController::class, 'caseStudyDetail'])
        ->where('slug', '[a-z0-9\-]+')
        ->name('website.case-study');
});

Route::prefix('ar')->middleware(SetWebsiteLocale::class)->group(function () use ($smilizPages) {
    Route::post('website/inquiry', [\App\Http\Controllers\PublicWebsiteInquiryController::class, 'store'])
        ->middleware('throttle:6,1')
        ->defaults('locale', 'ar')
        ->name('website.ar.inquiry.store');
    Route::get('/', [PublicWebsiteController::class, 'show'])
        ->defaults('locale', 'ar')
        ->name('website.ar.home');
    $smilizPages('', 'website.ar.page.', 'ar');
    Route::get('services/{slug}', [PublicWebsiteController::class, 'serviceDetail'])
        ->where('slug', '[a-z0-9\-]+')
        ->defaults('locale', 'ar')
        ->name('website.ar.service');
    Route::get('blog/{slug}', [PublicWebsiteController::class, 'blogDetail'])
        ->where('slug', '[a-z0-9\-]+')
        ->defaults('locale', 'ar')
        ->name('website.ar.blog');
    Route::get('case-studies/{slug}', [PublicWebsiteController::class, 'caseStudyDetail'])
        ->where('slug', '[a-z0-9\-]+')
        ->defaults('locale', 'ar')
        ->name('website.ar.case-study');
});

Route::redirect('/site', '/', 301);

Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [LoginController::class, 'login']);

    Route::get('forgot-password', [ForgotPasswordController::class, 'show'])->name('pages.forgot-password');
    Route::post('forgot-password', [ForgotPasswordController::class, 'send'])
        ->middleware('throttle:6,1');

    Route::get('reset-password/{token}', [ResetPasswordController::class, 'show'])->name('password.reset');
    Route::post('reset-password', [ResetPasswordController::class, 'update'])->name('password.update')
        ->middleware('throttle:6,1');
});

Route::post('logout', [LoginController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

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
        Route::get('patients/{patient}/photos/download-all', [PatientController::class, 'downloadAllPhotos'])
            ->name('patients.photos.download-all');
        Route::get('patients/{patient}/photos/{photo}/download', [PatientController::class, 'downloadPhoto'])
            ->name('patients.photos.download');
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
        Route::post('patients/{patient}/mark-stage-manufactured', [PatientManufacturingController::class, 'markStageManufactured'])
            ->name('patients.mark-stage-manufactured');
        Route::post('patients/{patient}/modifications', [PatientCaseModificationController::class, 'store'])
            ->name('patients.modifications.store');
        Route::get('patients/{patient}/modifications/{modification}/scans/{scan}', [PatientCaseModificationController::class, 'downloadScan'])
            ->whereIn('scan', ['upper', 'lower'])
            ->name('patients.modifications.scans.download');
        Route::post('patients/{patient}/refinements', [PatientCaseRefinementController::class, 'store'])
            ->name('patients.refinements.store');
        Route::post('patients/{patient}/send-last-update', [PatientController::class, 'sendLastUpdate'])
            ->name('patients.send-last-update');
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
        Route::get('website', [WebsiteController::class, 'index'])->name('admin.website.index');
        Route::put('website/content', [WebsiteController::class, 'updateContent'])->name('admin.website.content.update');
        Route::put('website/pages', [WebsiteController::class, 'updatePages'])->name('admin.website.pages.update');
        Route::put('website/main-menu', [WebsiteController::class, 'updateMainMenu'])->name('admin.website.main-menu.update');
        Route::post('website/showcases', [WebsiteController::class, 'storeShowcase'])->name('admin.website.showcases.store');
        Route::put('website/showcases/{showcase}', [WebsiteController::class, 'updateShowcase'])->name('admin.website.showcases.update');
        Route::delete('website/showcases/{showcase}', [WebsiteController::class, 'destroyShowcase'])->name('admin.website.showcases.destroy');

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
