<?php

namespace App\Services;

use App\Models\Patient;
use App\Models\Setting;
use App\Models\User;

class CaseChatContacts
{
    public function lineupAdmin(string $logoUrl): array
    {
        return [
            'name' => Setting::get('clinic_name', config('app.name', 'LineUp Aligners')),
            'role' => 'LineUp Admin',
            'avatar' => $logoUrl,
        ];
    }

    public function assignedDoctor(Patient $patient, string $logoUrl): ?array
    {
        $doctor = $patient->relationLoaded('doctor')
            ? $patient->doctor
            : $patient->doctor()->with('user')->first();

        if (! $doctor) {
            return null;
        }

        $doctorUser = $doctor->relationLoaded('user')
            ? $doctor->user
            : $doctor->user()->first();

        if (! $doctorUser) {
            return null;
        }

        return [
            'name' => $doctor->fullName(),
            'role' => 'Doctor',
            'avatar' => $doctorUser->photoUrl(),
        ];
    }

    /** @return array{lineup: array, doctor: array|null} */
    public function participants(Patient $patient, string $logoUrl): array
    {
        return [
            'lineup' => $this->lineupAdmin($logoUrl),
            'doctor' => $this->assignedDoctor($patient, $logoUrl),
        ];
    }

    public function counterpartyFor(User $viewer, Patient $patient, string $logoUrl): array
    {
        $participants = $this->participants($patient, $logoUrl);

        if ($viewer->isAdmin()) {
            return $participants['doctor'] ?? [
                'name' => 'No doctor assigned',
                'role' => 'Doctor',
                'avatar' => $logoUrl,
            ];
        }

        return $participants['lineup'];
    }

    public function assignedDoctorChatLabel(Patient $patient): ?string
    {
        $doctor = $this->assignedDoctor($patient, Setting::logoUrl());

        return $doctor ? 'Dr. '.$doctor['name'] : null;
    }

    public function messageAuthor(?User $user, Patient $patient, string $logoUrl): array
    {
        if (! $user) {
            return [
                'name' => 'Unknown',
                'avatar' => $logoUrl,
                'role' => 'System',
                'role_short' => '',
            ];
        }

        if ($user->isAdmin()) {
            $lineup = $this->lineupAdmin($logoUrl);

            return [
                'name' => $lineup['name'],
                'avatar' => $lineup['avatar'],
                'role' => 'LineUp Admin',
                'role_short' => 'LINEUP ADMIN',
            ];
        }

        return [
            'name' => $user->displayName(),
            'avatar' => $user->photoUrl(),
            'role' => $this->doctorRoleLabel($user, $patient),
            'role_short' => 'DOCTOR',
        ];
    }

    public function doctorRoleLabel(User $user, Patient $patient): string
    {
        if ($user->isDoctor() && $patient->doctor_id && $user->doctor?->id === $patient->doctor_id) {
            return 'Assigned Doctor';
        }

        return 'Doctor';
    }
}
