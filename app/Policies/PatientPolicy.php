<?php

namespace App\Policies;

use App\Models\Patient;
use App\Models\User;

class PatientPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->doctorCan('manage_patients');
    }

    public function view(User $user, Patient $patient): bool
    {
        return $user->isAdmin() || ($user->doctorCan('manage_patients') && $user->ownsPatient($patient));
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->doctorCan('create_patients');
    }

    public function update(User $user, Patient $patient): bool
    {
        return $user->isAdmin()
            || ($user->doctorCan('edit_patients') && $user->ownsPatient($patient));
    }

    public function delete(User $user, Patient $patient): bool
    {
        return $user->isAdmin()
            || ($user->doctorCan('delete_patients') && $user->ownsPatient($patient));
    }

    /** Case chat: system administrator ↔ doctor assigned to this case only */
    public function chat(User $user, Patient $patient): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if (! $user->isDoctor() || ! $user->doctor || ! $patient->doctor_id) {
            return false;
        }

        return $patient->doctor_id === $user->doctor->id
            && $user->doctorCan('manage_patients');
    }

    /** Upload / replace treatment plan canvas link (LineUp admin). */
    public function uploadTreatmentPlan(User $user, Patient $patient): bool
    {
        return $user->isAdmin() && $this->view($user, $patient);
    }

    /** Approve or reject treatment plan (assigned doctor). */
    public function reviewTreatmentPlan(User $user, Patient $patient): bool
    {
        if ($user->isAdmin()) {
            return false;
        }

        return $this->view($user, $patient)
            && $user->isDoctor()
            && $patient->doctor_id
            && $user->doctor
            && $patient->doctor_id === $user->doctor->id;
    }

    /** Request case modification with new 3D scans (assigned doctor, after plan approval). */
    public function requestModification(User $user, Patient $patient): bool
    {
        if ($user->isAdmin()) {
            return false;
        }

        return $this->reviewTreatmentPlan($user, $patient);
    }

    /** Order refinement after the case is fully manufactured (returning patient, new cycle). */
    public function requestRefinement(User $user, Patient $patient): bool
    {
        if ($user->isAdmin()) {
            return false;
        }

        return $this->reviewTreatmentPlan($user, $patient);
    }

    /** Confirm physical manufacturing is complete (LineUp admin, after doctor approval). */
    public function markAsManufactured(User $user, Patient $patient): bool
    {
        return $user->isAdmin() && $this->view($user, $patient);
    }
}
