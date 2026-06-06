<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DoctorRole;
use App\Models\Patient;
use App\Models\Setting;
use App\Services\BrandColors;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(): View
    {
        $settings = Setting::allSettings();

        return view('admin.settings.index', [
            'settings' => $settings,
            'admin' => auth()->user(),
            'systemStats' => $this->systemStats(),
            'logoUrl' => Setting::logoUrl(),
            'brandColors' => app(BrandColors::class)->tokens(),
            'doctorRoles' => $this->doctorRolesForSettings(),
            'notificationTypes' => Setting::notificationTypeSettings(),
        ]);
    }

    private function doctorRolesForSettings()
    {
        if (! Schema::hasTable('doctor_roles')) {
            return collect();
        }

        $query = DoctorRole::query()->orderBy('sort_order')->orderBy('name');

        if (Schema::hasColumn('doctors', 'doctor_role_id')) {
            $query->withCount('doctors');
        }

        return $query->get();
    }

    public function update(Request $request): RedirectResponse
    {
        $admin = auth()->user();
        $skinKeys = array_keys(config('settings.skins'));
        $fontKeys = array_keys(config('settings.fonts', []));
        $scanKeys = array_keys(config('settings.scan_requirements', []));

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$admin->id],
            'phone' => ['nullable', 'string', 'max:50'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
            'remove_photo' => ['sometimes', 'boolean'],
            'project_name' => ['required', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'mimes:jpeg,jpg,png,svg,webp', 'max:2048'],
            'remove_logo' => ['sometimes', 'boolean'],
            'clinic_name' => ['required', 'string', 'max:255'],
            'clinic_email' => ['nullable', 'email', 'max:255'],
            'clinic_phone' => ['nullable', 'string', 'max:50'],
            'clinic_address' => ['nullable', 'string', 'max:500'],
            'system_timezone' => ['required', 'string', Rule::in(timezone_identifiers_list())],
            'scan_requirement' => ['required', 'string', 'in:'.implode(',', $scanKeys)],
            'theme_skin' => ['required', 'in:'.implode(',', $skinKeys)],
            'brand_primary' => ['nullable', 'string', 'max:7', 'regex:/^$|^#?[0-9a-fA-F]{6}$/'],
            'brand_secondary' => ['required', 'string', 'max:7', 'regex:/^#?[0-9a-fA-F]{6}$/'],
            'dashboard_font' => ['required', 'in:'.implode(',', $fontKeys)],
            'dashboard_color_mode' => ['required', 'in:light,dark'],
            'left_menu_style' => ['required', 'in:light,dark,image'],
            'notification_types' => ['nullable', 'array'],
            'notification_types.*' => ['nullable', 'array'],
            'notification_types.*.in_app' => ['sometimes', 'boolean'],
            'notification_types.*.email' => ['sometimes', 'boolean'],
        ]);

        $admin->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
        ]);

        if (! empty($data['password'])) {
            $admin->update(['password' => $data['password']]);
        }

        if ($request->hasFile('photo')) {
            $this->deleteStoredFile($admin->photo);
            $path = $request->file('photo')->store('profiles', 'public');
            $admin->update(['photo' => $path]);
        } elseif ($request->boolean('remove_photo')) {
            $this->deleteStoredFile($admin->photo);
            $admin->update(['photo' => null]);
        }

        if ($request->boolean('remove_logo')) {
            $this->deleteLogo(Setting::get('logo'));
            Setting::set('logo', '');
        } elseif ($request->hasFile('logo')) {
            $this->deleteLogo(Setting::get('logo'));
            $path = $request->file('logo')->store('settings', 'public');
            Setting::set('logo', $path);
        }

        Setting::setMany([
            'project_name' => $data['project_name'],
            'clinic_name' => $data['clinic_name'],
            'clinic_email' => $data['clinic_email'] ?? '',
            'clinic_phone' => $data['clinic_phone'] ?? '',
            'clinic_address' => $data['clinic_address'] ?? '',
            'system_timezone' => $data['system_timezone'],
            'scan_requirement' => $data['scan_requirement'],
            'notifications_enabled' => $request->boolean('notifications_enabled'),
            'notification_email_enabled' => $request->boolean('notification_email_enabled'),
            'notification_sound_enabled' => $request->boolean('notification_sound_enabled'),
            'theme_skin' => $data['theme_skin'],
            'brand_primary' => $this->normalizeBrandHex($data['brand_primary'] ?? ''),
            'brand_secondary' => $this->normalizeBrandHex($data['brand_secondary'] ?? '') ?: config('settings.defaults.brand_secondary', '#09243c'),
            'dashboard_font' => $data['dashboard_font'],
            'dashboard_color_mode' => $data['dashboard_color_mode'],
            'left_menu_style' => $data['left_menu_style'],
        ]);

        Setting::setNotificationTypeSettings($request->input('notification_types', []));

        return back()->with('success', 'Settings saved successfully.');
    }

    private function deleteLogo(?string $path): void
    {
        $this->deleteStoredFile($path);
    }

    private function deleteStoredFile(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    private function normalizeBrandHex(?string $hex): string
    {
        return app(BrandColors::class)->normalizeHex($hex ?? '') ?? '';
    }

    private function systemStats(): array
    {
        $memoryMb = round(memory_get_usage(true) / 1024 / 1024);
        $diskTotal = @disk_total_space(base_path()) ?: 0;
        $diskFree = @disk_free_space(base_path()) ?: 0;
        $diskUsedPercent = $diskTotal > 0
            ? round((($diskTotal - $diskFree) / $diskTotal) * 100, 2)
            : 0;

        $cpuPercent = 0;
        if (function_exists('sys_getloadavg')) {
            $load = sys_getloadavg()[0] ?? 0;
            $cpuPercent = min(100, (int) round($load * 100));
        }

        $casesByStage = [];
        if (Schema::hasColumn('patients', 'case_workflow_stage')) {
            $casesByStage = Patient::query()
                ->select('case_workflow_stage', DB::raw('count(*) as total'))
                ->groupBy('case_workflow_stage')
                ->pluck('total', 'case_workflow_stage')
                ->all();
        }

        $failedJobs = Schema::hasTable('failed_jobs')
            ? (int) DB::table('failed_jobs')->count()
            : 0;

        $pendingJobs = Schema::hasTable('jobs')
            ? (int) DB::table('jobs')->count()
            : 0;

        return [
            'memory_mb' => $memoryMb,
            'cpu_percent' => $cpuPercent,
            'total_cases' => Patient::count(),
            'cases_today' => Patient::whereDate('created_at', today())->count(),
            'cases_by_stage' => $casesByStage,
            'disk_percent' => $diskUsedPercent,
            'disk_free_gb' => $diskFree > 0 ? round($diskFree / 1024 / 1024 / 1024, 1) : 0,
            'failed_jobs' => $failedJobs,
            'pending_jobs' => $pendingJobs,
            'queue_connection' => config('queue.default'),
            'mail_mailer' => config('mail.default'),
            'mail_queue' => config('lineup-notifications.email.queue', true),
            'php_upload_max' => ini_get('upload_max_filesize') ?: '—',
            'php_post_max' => ini_get('post_max_size') ?: '—',
            'timezone' => Setting::timezone(),
            'timezone_offset' => now()->format('P'),
        ];
    }
}
