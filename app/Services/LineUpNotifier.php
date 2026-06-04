<?php

namespace App\Services;

use App\Models\Patient;
use App\Models\User;
use App\Notifications\LineUpAlert;
use Illuminate\Support\Collection;

class LineUpNotifier
{
    public function caseCreated(Patient $patient, User $actor): void
    {
        $patient->loadMissing('doctor.user');

        $caseLabel = $patient->patient_id.' — '.$patient->fullName();
        $doctorName = $patient->doctor?->fullName() ?? 'Unassigned';
        $doctorUser = $this->doctorUserForPatient($patient);

        $doctorPayload = [
            'type' => 'case_created',
            'title' => 'Case created',
            'body' => "Case {$caseLabel} was created and assigned to you.",
            'url' => $this->caseUrl($patient, 'view-data'),
            'icon' => 'zmdi-folder-star',
            'open_tab' => 'view-data',
            'patient_id' => $patient->id,
        ];

        if ($actor->isDoctor()) {
            $this->notifyAdmins([
                'type' => 'case_created',
                'title' => 'New case submitted',
                'body' => "Dr. {$actor->displayName()} submitted case {$caseLabel}.",
                'url' => $this->caseUrl($patient, 'manufacture-plan'),
                'icon' => 'zmdi-folder-star',
                'open_tab' => 'manufacture-plan',
                'patient_id' => $patient->id,
            ]);

            if ($doctorUser) {
                $this->notifyUser($doctorUser, [
                    ...$doctorPayload,
                    'title' => 'Case created',
                    'body' => "Your case {$caseLabel} was submitted successfully.",
                ]);
            }
        } else {
            if ($doctorUser && (int) $doctorUser->id !== (int) $actor->id) {
                $this->notifyUser($doctorUser, $doctorPayload);
            }

            $this->notifyAdmins([
                'type' => 'case_created',
                'title' => 'New case created',
                'body' => "Case {$caseLabel} was created (Dr. {$doctorName}).",
                'url' => $this->caseUrl($patient, 'manufacture-plan'),
                'icon' => 'zmdi-folder-star',
                'open_tab' => 'manufacture-plan',
                'patient_id' => $patient->id,
            ], excludeUserId: $actor->id);
        }
    }

    public function caseMessage(Patient $patient, User $sender, ?string $preview = null): void
    {
        $caseLabel = $patient->patient_id.' — '.$patient->fullName();
        $snippet = $preview ? mb_strimwidth($preview, 0, 120, '…') : 'New message';

        if ($sender->isDoctor()) {
            $this->notifyAdmins([
                'type' => 'case_message',
                'title' => 'Message from doctor',
                'body' => "Dr. {$sender->displayName()} on {$caseLabel}: {$snippet}",
                'url' => $this->caseUrl($patient, 'messages'),
                'icon' => 'zmdi-comments',
                'open_tab' => 'messages',
                'patient_id' => $patient->id,
            ]);
        } else {
            $doctorUser = $patient->doctor?->user;
            if ($doctorUser) {
                $this->notifyUser($doctorUser, [
                    'type' => 'case_message',
                    'title' => 'Message from LineUp',
                    'body' => "On {$caseLabel}: {$snippet}",
                    'url' => $this->caseUrl($patient, 'messages'),
                    'icon' => 'zmdi-comments',
                    'open_tab' => 'messages',
                    'patient_id' => $patient->id,
                ]);
            }
        }
    }

    public function planUploaded(Patient $patient, User $admin, ?int $stageNumber = null): void
    {
        $doctorUser = $patient->doctor?->user;
        if (! $doctorUser) {
            return;
        }

        $stageText = $stageNumber ? " (stage {$stageNumber})" : '';
        $this->notifyUser($doctorUser, [
            'type' => 'plan_uploaded',
            'title' => 'Treatment plan ready',
            'body' => "LineUp uploaded a manufacture plan{$stageText} for {$patient->patient_id}. Please review.",
            'url' => $this->caseUrl($patient, 'manufacture-plan', $stageNumber),
            'icon' => 'zmdi-assignment-check',
            'open_tab' => 'manufacture-plan',
            'mfg_stage' => $stageNumber,
            'patient_id' => $patient->id,
        ]);
    }

    public function planReviewed(Patient $patient, User $doctor, string $decision, ?int $stageNumber = null): void
    {
        $approved = $decision === 'approved';
        $stageText = $stageNumber ? " (stage {$stageNumber})" : '';

        $this->notifyAdmins([
            'type' => $approved ? 'plan_approved' : 'plan_rejected',
            'title' => $approved ? 'Plan approved for manufacture' : 'Plan rejected',
            'body' => "Dr. {$doctor->displayName()} {$decision} the plan{$stageText} for {$patient->patient_id}.",
            'url' => $this->caseUrl($patient, 'manufacture-plan', $stageNumber),
            'icon' => $approved ? 'zmdi-check-circle' : 'zmdi-close-circle',
            'open_tab' => 'manufacture-plan',
            'mfg_stage' => $stageNumber,
            'patient_id' => $patient->id,
        ], excludeUserId: $doctor->id);
    }

    public function modificationRequested(Patient $patient, User $doctor): void
    {
        $this->notifyAdmins([
            'type' => 'modification_requested',
            'title' => 'Modification requested',
            'body' => "Dr. {$doctor->displayName()} requested a modification for {$patient->patient_id}.",
            'url' => $this->caseUrl($patient, 'modification'),
            'icon' => 'zmdi-refresh-sync',
            'open_tab' => 'modification',
            'patient_id' => $patient->id,
        ], excludeUserId: $doctor->id);
    }

    public function caseReadyForManufacture(Patient $patient): void
    {
        $this->notifyAdmins([
            'type' => 'case_ready_for_manufacture',
            'title' => 'Ready to mark manufactured',
            'body' => "All plans approved for {$patient->patient_id} ({$patient->fullName()}). Mark the case as manufactured on the Manufacture Case Plan tab.",
            'url' => $this->caseUrl($patient, 'manufacture-plan'),
            'icon' => 'zmdi-assignment-check',
            'open_tab' => 'manufacture-plan',
            'patient_id' => $patient->id,
        ]);
    }

    public function caseMarkedManufactured(Patient $patient, User $admin): void
    {
        $doctorUser = $patient->doctor?->user;
        if (! $doctorUser) {
            return;
        }

        $this->notifyUser($doctorUser, [
            'type' => 'case_manufactured',
            'title' => 'Case manufactured',
            'body' => "Case {$patient->patient_id} ({$patient->fullName()}) is marked manufactured. Order refinement when the patient returns.",
            'url' => $this->caseUrl($patient, 'order-refinement'),
            'icon' => 'zmdi-check-circle',
            'open_tab' => 'order-refinement',
            'patient_id' => $patient->id,
        ]);
    }

    public function refinementRequested(Patient $patient, User $doctor): void
    {
        $this->notifyAdmins([
            'type' => 'refinement_requested',
            'title' => 'Refinement ordered',
            'body' => "Dr. {$doctor->displayName()} ordered a refinement for {$patient->patient_id}.",
            'url' => $this->caseUrl($patient, 'order-refinement'),
            'icon' => 'zmdi-redo',
            'open_tab' => 'order-refinement',
            'patient_id' => $patient->id,
        ], excludeUserId: $doctor->id);
    }

    public function notifyAdmins(array $payload, ?int $excludeUserId = null): void
    {
        $query = User::query()->where('role', User::ROLE_ADMIN);

        if ($excludeUserId) {
            $query->where('id', '!=', $excludeUserId);
        }

        $this->notifyUsers($query->get(), $payload);
    }

    public function notifyUser(User $user, array $payload): void
    {
        $user->notify(new LineUpAlert($payload));
    }

    /**
     * @param  Collection<int, User>|iterable<User>  $users
     */
    public function notifyUsers(iterable $users, array $payload): void
    {
        foreach ($users as $user) {
            $user->notify(new LineUpAlert($payload));
        }
    }

    protected function doctorUserForPatient(Patient $patient): ?User
    {
        $doctor = $patient->doctor;
        if (! $doctor) {
            return null;
        }

        if ($doctor->relationLoaded('user') && $doctor->user) {
            return $doctor->user;
        }

        if ($doctor->user_id) {
            return $doctor->user()->first();
        }

        if (filled($doctor->email)) {
            return User::query()
                ->where('role', User::ROLE_DOCTOR)
                ->where('email', $doctor->email)
                ->first();
        }

        return null;
    }

    public function caseUrl(Patient $patient, ?string $tab = null, ?int $mfgStage = null): string
    {
        $params = array_filter([
            'tab' => $tab,
            'stage' => $mfgStage,
        ], fn ($v) => $v !== null && $v !== '');

        $url = route('patients.show', $patient);

        return $params === [] ? $url : $url.'?'.http_build_query($params);
    }
}
