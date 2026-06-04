<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class HospitalSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@oreo.com'],
            [
                'name' => 'Dr. Charlotte',
                'password' => Hash::make('password'),
                'role' => User::ROLE_ADMIN,
            ]
        );

        $departments = collect([
            ['name' => 'Cardiology', 'description' => 'Heart and cardiovascular care.'],
            ['name' => 'Neurology', 'description' => 'Brain and nervous system treatment.'],
            ['name' => 'Gynecology', 'description' => 'Women\'s reproductive health.'],
            ['name' => 'Pediatrics', 'description' => 'Medical care for infants and children.'],
            ['name' => 'Pulmonology', 'description' => 'Respiratory system specialists.'],
        ])->map(fn (array $data) => Department::create($data));

        $doctors = collect([
            ['first_name' => 'Charlotte', 'last_name' => 'Ray', 'specialty' => 'Neurologist', 'department_id' => $departments[1]->id],
            ['first_name' => 'Michael', 'last_name' => 'Smith', 'specialty' => 'Cardiologist', 'department_id' => $departments[0]->id],
            ['first_name' => 'Sarah', 'last_name' => 'Johnson', 'specialty' => 'Pediatrician', 'department_id' => $departments[3]->id],
        ])->map(fn (array $data) => Doctor::create(array_merge($data, [
            'phone' => '404-447-6013',
            'email' => strtolower($data['first_name']).'@oreo.com',
            'experience_years' => 10,
            'is_active' => true,
        ])));

        $patients = collect([
            ['first_name' => 'Daniel', 'last_name' => 'Moore', 'age' => 32, 'country' => 'USA', 'status' => 'approved'],
            ['first_name' => 'Alexander', 'last_name' => 'Lee', 'age' => 22, 'country' => 'USA', 'status' => 'approved'],
            ['first_name' => 'Richard', 'last_name' => 'Brown', 'age' => 26, 'country' => 'India', 'status' => 'approved'],
            ['first_name' => 'Cameron', 'last_name' => 'Wilson', 'age' => 38, 'country' => 'India', 'status' => 'pending'],
        ])->map(function (array $data, int $index) {
            return Patient::create(array_merge($data, [
                'patient_id' => 'KU '.str_pad((string) ($index + 1), 5, '0', STR_PAD_LEFT),
                'phone' => '404-447-6013',
                'address' => '123 6th St. Melbourne, FL 32904',
                'last_visit' => now()->subDays(rand(1, 30)),
            ]));
        });

        foreach ($patients->take(3) as $index => $patient) {
            Appointment::create([
                'patient_id' => $patient->id,
                'doctor_id' => $doctors[$index % $doctors->count()]->id,
                'department_id' => $doctors[$index % $doctors->count()]->department_id,
                'scheduled_at' => now()->addDays($index + 1)->setHour(10),
                'status' => $index === 0 ? 'confirmed' : 'pending',
                'notes' => 'Routine checkup',
            ]);
        }
    }
}
