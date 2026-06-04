<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DoctorRole;
use App\Models\Patient;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
            'doctorRoles' => DoctorRole::withCount('doctors')->orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $admin = auth()->user();
        $skinKeys = array_keys(config('settings.skins'));

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
            'theme_skin' => ['required', 'in:'.implode(',', $skinKeys)],
            'left_menu_style' => ['required', 'in:light,dark,image'],
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
            'report_panel_usage' => $request->boolean('report_panel_usage'),
            'email_redirect' => $request->boolean('email_redirect'),
            'notifications' => $request->boolean('notifications'),
            'auto_updates' => $request->boolean('auto_updates'),
            'offline' => $request->boolean('offline'),
            'location_permission' => $request->boolean('location_permission'),
            'theme_skin' => $data['theme_skin'],
            'left_menu_style' => $data['left_menu_style'],
        ]);

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
            $cores = (int) trim((string) @shell_exec('nproc 2>/dev/null')) ?: 1;
            $cpuPercent = min(100, (int) round(($load / $cores) * 100));
        }

        $dailyTraffic = Patient::whereDate('created_at', today())->count()
            + Patient::whereDate('last_visit', today())->count();

        return [
            'memory_mb' => $memoryMb,
            'cpu_percent' => $cpuPercent,
            'daily_traffic' => max($dailyTraffic, Patient::count()),
            'disk_percent' => $diskUsedPercent,
        ];
    }
}
