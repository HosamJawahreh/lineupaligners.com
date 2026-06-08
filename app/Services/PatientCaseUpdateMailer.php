<?php

namespace App\Services;

use App\Mail\PatientCaseLastUpdateMail;
use App\Models\Patient;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use RuntimeException;

class PatientCaseUpdateMailer
{
    public function __construct(
        protected CaseTimelineBuilder $timeline
    ) {}

    /**
     * @return array<string, mixed>|null
     */
    public function latestEvent(Patient $patient): ?array
    {
        return $this->timeline->latestEvent($patient);
    }

    public function send(Patient $patient, User $sender): void
    {
        if (! filled($patient->email)) {
            throw new RuntimeException('Patient has no email address.');
        }

        $event = $this->latestEvent($patient);

        if ($event === null) {
            throw new RuntimeException('No case activity is available to send yet.');
        }

        try {
            Mail::to($patient->email)->send(new PatientCaseLastUpdateMail(
                patient: $patient,
                event: $event,
                sender: $sender,
                clinicName: Setting::projectName(),
            ));
        } catch (\Throwable $e) {
            Log::warning('Patient case update email failed', [
                'patient_id' => $patient->id,
                'email' => $patient->email,
                'error' => $e->getMessage(),
            ]);

            throw new RuntimeException('Could not send the email. Please try again later.', 0, $e);
        }
    }
}
