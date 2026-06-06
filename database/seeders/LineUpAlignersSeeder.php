<?php

namespace Database\Seeders;

use App\Models\Doctor;
use App\Models\DoctorRole;
use App\Models\Patient;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;

class LineUpAlignersSeeder extends Seeder
{
    public function run(): void
    {
        Setting::set('project_name', 'LineUp Aligners');
        Setting::set('clinic_name', 'LineUp Aligners');
        Setting::set('clinic_email', 'contact@lineupaligners.com');
        Setting::set('clinic_phone', '+1 555 0100');
        Setting::set('clinic_address', '123 Medical Center Dr.');
        Setting::set('system_timezone', 'UTC');
        Setting::set('scan_requirement', 'optional');
        Setting::set('notifications_enabled', '1');
        Setting::set('notification_email_enabled', '1');
        Setting::set('notification_sound_enabled', '1');
        Setting::set('theme_skin', 'cyan');
        Setting::set('brand_secondary', '#09243c');
        Setting::set('left_menu_style', 'light');
        Setting::set('dashboard_color_mode', 'light');

        User::updateOrCreate(
            ['email' => 'admin@lineup.com'],
            [
                'name' => 'System Admin',
                'password' => 'password',
                'role' => User::ROLE_ADMIN,
            ]
        );

        $orthodontistRole = DoctorRole::updateOrCreate(
            ['slug' => 'orthodontist'],
            [
                'name' => 'Orthodontist',
                'description' => 'Full treatment workflow for assigned cases: submit, review plans, modifications, and refinements.',
                'permissions' => [
                    'view_cases',
                    'create_cases',
                    'edit_cases',
                    'case_chat',
                    'review_plans',
                    'request_modification',
                    'request_refinement',
                ],
                'is_active' => true,
                'sort_order' => 1,
            ]
        );

        DoctorRole::updateOrCreate(
            ['slug' => 'clinic-lead'],
            [
                'name' => 'Clinic Lead',
                'description' => 'Senior role with full case and workflow permissions on assigned cases.',
                'permissions' => array_keys(config('doctor-permissions.permissions', [])),
                'is_active' => true,
                'sort_order' => 2,
            ]
        );

        DoctorRole::updateOrCreate(
            ['slug' => 'case-intake'],
            [
                'name' => 'Case Intake',
                'description' => 'Front-desk role: submit new cases and message LineUp on assigned cases.',
                'permissions' => [
                    'view_cases',
                    'create_cases',
                    'case_chat',
                ],
                'is_active' => true,
                'sort_order' => 3,
            ]
        );

        $doctorUser = User::updateOrCreate(
            ['email' => 'doctor@lineup.com'],
            [
                'name' => 'Dr. Sarah Johnson',
                'password' => 'password',
                'role' => User::ROLE_DOCTOR,
            ]
        );

        $doctor = Doctor::updateOrCreate(
            ['email' => 'doctor@lineup.com'],
            [
                'user_id' => $doctorUser->id,
                'doctor_role_id' => $orthodontistRole->id,
                'first_name' => 'Sarah',
                'last_name' => 'Johnson',
                'specialty' => 'Orthodontist',
                'phone' => '555-0101',
                'email' => 'doctor@lineup.com',
                'experience_years' => 12,
                'is_active' => true,
            ]
        );

        $patients = [
            ['first_name' => 'Emma', 'last_name' => 'Wilson', 'age' => 28, 'status' => 'approved', 'case_type' => 'full_case'],
            ['first_name' => 'James', 'last_name' => 'Miller', 'age' => 34, 'status' => 'approved', 'case_type' => 'divided_stages'],
            ['first_name' => 'Olivia', 'last_name' => 'Brown', 'age' => 22, 'status' => 'approved', 'case_type' => 'full_case'],
        ];

        foreach ($patients as $index => $data) {
            Patient::updateOrCreate(
                ['patient_id' => str_pad((string) ($index + 1), 5, '0', STR_PAD_LEFT)],
                array_merge($data, [
                    'doctor_id' => $doctor->id,
                    'phone' => '555-020'.$index,
                    'email' => strtolower($data['first_name']).'@example.com',
                    'address' => '742 Evergreen Terrace',
                    'last_visit' => now()->subDays($index + 2),
                ])
            );
        }
    }
}
