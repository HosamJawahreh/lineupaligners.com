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
        Setting::set('email_redirect', '1');
        Setting::set('notifications', '1');
        Setting::set('auto_updates', '1');
        Setting::set('theme_skin', 'cyan');
        Setting::set('offline', '1');
        Setting::set('location_permission', '1');
        Setting::set('left_menu_style', 'light');

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
                'description' => 'Standard aligner provider with full patient access for assigned cases.',
                'permissions' => [
                    'manage_patients',
                    'create_patients',
                    'edit_patients',
                    'delete_patients',
                    'manage_appointments',
                ],
                'is_active' => true,
                'sort_order' => 1,
            ]
        );

        DoctorRole::updateOrCreate(
            ['slug' => 'clinic-lead'],
            [
                'name' => 'Clinic Lead',
                'description' => 'Senior doctor with clinic-wide patient visibility.',
                'permissions' => array_keys(config('doctor-permissions', [])),
                'is_active' => true,
                'sort_order' => 2,
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
                ['patient_id' => 'LA '.str_pad((string) ($index + 1), 5, '0', STR_PAD_LEFT)],
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
