<?php

namespace App\Mail;

use App\Support\LineUpMailBranding;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WebsiteInquiryMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  array{name: string, email: string, phone?: string|null, subject?: string|null, message: string, form_type?: string|null}  $inquiry
     */
    public function __construct(
        public array $inquiry,
        public string $ipAddress,
        public string $locale,
    ) {}

    public function envelope(): Envelope
    {
        $subject = trim((string) ($this->inquiry['subject'] ?? ''));

        return new Envelope(
            from: new Address(
                LineUpMailBranding::fromAddress(),
                LineUpMailBranding::fromName(),
            ),
            subject: LineUpMailBranding::subjectPrefix($subject !== '' ? $subject : 'New website inquiry'),
            replyTo: [
                new Address(
                    (string) $this->inquiry['email'],
                    (string) $this->inquiry['name'],
                ),
            ],
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.website-inquiry',
            with: [
                'inquirerName' => (string) $this->inquiry['name'],
                'inquirerEmail' => (string) $this->inquiry['email'],
                'inquirerPhone' => $this->inquiry['phone'] ?? null,
                'inquirySubject' => $this->inquiry['subject'] ?? null,
                'inquiryMessage' => (string) $this->inquiry['message'],
                'formLabel' => $this->formLabel(),
                'locale' => $this->locale,
                'ipAddress' => $this->ipAddress,
            ],
        );
    }

    protected function formLabel(): string
    {
        return match ($this->inquiry['form_type'] ?? 'contact') {
            'appointment' => 'Appointment request',
            'newsletter' => 'Newsletter signup',
            default => 'Contact form',
        };
    }
}
