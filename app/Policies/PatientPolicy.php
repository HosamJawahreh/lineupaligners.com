<?php

namespace App\Policies;

use App\Models\Patient;
use App\Models\User;

class PatientPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->doctorCan('view_cases');
    }

    public function view(User $user, Patient $patient): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->doctorCan('view_cases') && $user->ownsPatient($patient);
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->doctorCan('create_cases');
    }

    public function update(User $user, Patient $patient): bool
    {
        return $user->isAdmin()
            || ($user->doctorCan('edit_cases') && $user->ownsPatient($patient));
    }

    public function delete(User $user, Patient $patient): bool
    {
        return $user->isAdmin()
            || ($user->doctorCan('delete_cases') && $user->ownsPatient($patient));
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
            && $user->doctorCan('case_chat');
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

        return $this->assignedDoctorWithWorkflowAccess($user, $patient, 'review_plans');
    }

    /** Request case modification with new 3D scans (assigned doctor). */
    public function requestModification(User $user, Patient $patient): bool
    {
        if ($user->isAdmin()) {
            return false;
        }

        return $this->assignedDoctorWithWorkflowAccess($user, $patient, 'request_modification');
    }

    /** Order refinement after the case is fully manufactured (returning patient, new cycle). */
    public function requestRefinement(User $user, Patient $patient): bool
    {
        if ($user->isAdmin()) {
            return false;
        }

        return $this->assignedDoctorWithPermission($user, $patient, 'request_refinement');
    }

    /** Confirm physical manufacturing is complete (LineUp admin, after doctor approval). */
    public function markAsManufactured(User $user, Patient $patient): bool
    {
        return $user->isAdmin() && $this->view($user, $patient);
    }

    private function assignedDoctorWithPermission(User $user, Patient $patient, string $permission): bool
    {
        return $this->view($user, $patient)
            && $user->isDoctor()
            && $patient->doctor_id
            && $user->doctor
            && $patient->doctor_id === $user->doctor->id
            && $user->doctorCan($permission);
    }

    /**
     * Assigned doctors who can view a case may always review plans and request modifications.
     * Granular role keys still apply to other workflow actions (e.g. refinement).
     */
    private function assignedDoctorWithWorkflowAccess(User $user, Patient $patient, string $permission): bool
    {
        if (! $this->view($user, $patient)
            || ! $user->isDoctor()
            || ! $patient->doctor_id
            || ! $user->doctor
            || $patient->doctor_id !== $user->doctor->id) {
            return false;
        }

        if (in_array($permission, ['review_plans', 'request_modification'], true)) {
            return $user->doctorCan($permission) || $user->doctorCan('view_cases');
        }

        return $user->doctorCan($permission);
    }
}
