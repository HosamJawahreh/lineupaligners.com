<?php

namespace App\Mail;

use App\Models\Patient;
use App\Models\User;
use App\Support\LineUpMailBranding;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PatientCaseLastUpdateMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  array<string, mixed>  $event
     */
    public function __construct(
        public Patient $patient,
        public array $event,
        public User $sender,
        public string $clinicName,
    ) {}

    public function envelope(): Envelope
    {
        $replyTo = LineUpMailBranding::replyToAddress();

        return new Envelope(
            from: new Address(
                LineUpMailBranding::fromAddress(),
                LineUpMailBranding::fromName(),
            ),
            subject: LineUpMailBranding::patientCaseEmailSubject($this->patient->fullName()),
            replyTo: $replyTo
                ? [new Address($replyTo, LineUpMailBranding::fromName())]
                : [],
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.patient-case-last-update',
            with: [
                'patientName' => $this->patient->fullName(),
                'caseId' => $this->patient->display_patient_id,
                'caseType' => $this->patient->caseTypeLabel(),
                'workflowStatus' => $this->patient->workflowStageLabel(),
                'eventTitle' => $this->event['title'] ?? 'Case update',
                'eventSummary' => $this->event['summary'] ?? null,
                'eventBody' => $this->event['body'] ?? null,
                'eventDate' => trim(($this->event['date_label'] ?? '').' · '.($this->event['time_label'] ?? '')),
                'actorName' => $this->event['actor_name'] ?? null,
                'actorRole' => $this->event['actor_role'] ?? null,
                'senderName' => $this->sender->displayName(),
                'clinicName' => $this->clinicName,
            ],
        );
    }
}
