<?php

namespace App\Services;

use App\Models\Patient;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\LineUpAlert;
use App\Notifications\LineUpMailAlert;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification;

class LineUpNotifier
{
    public function caseCreated(Patient $patient, User $actor): void
    {
        $patient->loadMissing('doctor.user');

        $caseLabel = $patient->display_patient_id.' — '.$patient->fullName();
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
        $caseLabel = $patient->display_patient_id.' — '.$patient->fullName();
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

    public function planUploaded(Patient $patient, User $admin, ?int $stageNumber = null, bool $isRevisionUpload = false): void
    {
        $doctorUser = $patient->doctor?->user;
        if (! $doctorUser) {
            return;
        }

        $stageText = $stageNumber ? " (stage {$stageNumber})" : '';
        $tab = 'manufacture-plan';

        if ($isRevisionUpload) {
            $this->notifyUser($doctorUser, [
                'type' => 'plan_revised',
                'title' => 'Revised plan uploaded',
                'body' => "LineUp uploaded a revised treatment plan{$stageText} for {$patient->display_patient_id}. Please review and approve.",
                'url' => $this->caseUrl($patient, $tab, $stageNumber),
                'icon' => 'zmdi-refresh-sync',
                'open_tab' => $tab,
                'mfg_stage' => $stageNumber,
                'patient_id' => $patient->id,
            ]);

            return;
        }

        $this->notifyUser($doctorUser, [
            'type' => 'plan_uploaded',
            'title' => 'Treatment plan ready',
            'body' => "LineUp uploaded a treatment plan{$stageText} for {$patient->display_patient_id}. Review it or request changes.",
            'url' => $this->caseUrl($patient, $tab, $stageNumber),
            'icon' => 'zmdi-assignment-check',
            'open_tab' => $tab,
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
            'title' => $approved ? 'Plan approved for manufacture' : 'Modification ordered',
            'body' => $approved
                ? "Dr. {$doctor->displayName()} approved the plan{$stageText} for {$patient->display_patient_id}."
                : "Dr. {$doctor->displayName()} ordered a modification{$stageText} for {$patient->display_patient_id}.",
            'url' => $this->caseUrl($patient, $approved ? 'manufacture-plan' : 'modification', $stageNumber),
            'icon' => $approved ? 'zmdi-check-circle' : 'zmdi-refresh-sync',
            'open_tab' => $approved ? 'manufacture-plan' : 'modification',
            'mfg_stage' => $stageNumber,
            'patient_id' => $patient->id,
        ], excludeUserId: $doctor->id);
    }

    public function modificationRequested(Patient $patient, User $doctor): void
    {
        $this->notifyAdmins([
            'type' => 'modification_requested',
            'title' => 'Modification requested',
            'body' => "Dr. {$doctor->displayName()} requested a modification for {$patient->display_patient_id}.",
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
            'body' => "All plans approved for {$patient->display_patient_id} ({$patient->fullName()}). Mark the case as manufactured on the Treatment Plan tab.",
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
            'body' => "Case {$patient->display_patient_id} ({$patient->fullName()}) is marked manufactured. Order refinement when the patient returns.",
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
            'body' => "Dr. {$doctor->displayName()} ordered a refinement for {$patient->display_patient_id}.",
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
        $payload = $this->enrichCasePayload($payload);

        $user->notify(new LineUpAlert($payload));
        $this->queueEmailAlert($user, $payload);
    }

    /**
     * @param  Collection<int, User>|iterable<User>  $users
     */
    public function notifyUsers(iterable $users, array $payload): void
    {
        foreach ($users as $user) {
            $this->notifyUser($user, $payload);
        }
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    protected function enrichCasePayload(array $payload): array
    {
        $patientId = $payload['patient_id'] ?? null;

        if (! $patientId) {
            return $payload;
        }

        if (filled($payload['patient_name'] ?? null)) {
            return $payload;
        }

        $patient = Patient::query()
            ->select(['id', 'first_name', 'last_name', 'patient_id'])
            ->find($patientId);

        if ($patient === null) {
            return $payload;
        }

        $payload['patient_name'] = $patient->fullName();
        $payload['case_number'] = $payload['case_number'] ?? $patient->display_patient_id;

        return $payload;
    }

    protected function queueEmailAlert(User $user, array $payload): void
    {
        if (! $this->shouldEmailUser($user)) {
            return;
        }

        if (config('lineup-notifications.email.queue', true)) {
            $user->notify(new LineUpMailAlert($payload));

            return;
        }

        dispatch(static function () use ($user, $payload) {
            Notification::sendNow($user, new LineUpMailAlert($payload), ['mail']);
        })->afterResponse();
    }

    protected function shouldEmailUser(User $user): bool
    {
        if (! Setting::notificationEmailEnabled()) {
            return false;
        }

        if (! config('lineup-notifications.email.enabled', true)) {
            return false;
        }

        if (! filled($user->email)) {
            return false;
        }

        return in_array($user->role, [User::ROLE_ADMIN, User::ROLE_DOCTOR], true);
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
