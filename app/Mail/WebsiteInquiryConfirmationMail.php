<?php

namespace App\Mail;

use App\Support\LineUpMailBranding;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WebsiteInquiryConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $inquirerName,
        public string $inquiryMessage,
    ) {}

    public function envelope(): Envelope
    {
        $replyTo = LineUpMailBranding::replyToAddress();

        return new Envelope(
            from: new Address(
                LineUpMailBranding::fromAddress(),
                LineUpMailBranding::fromName(),
            ),
            subject: LineUpMailBranding::subjectPrefix('We received your message'),
            replyTo: $replyTo
                ? [new Address($replyTo, LineUpMailBranding::fromName())]
                : [],
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.website-inquiry-confirmation',
            with: [
                'inquirerName' => $this->inquirerName,
                'inquiryMessage' => $this->inquiryMessage,
                'clinicName' => LineUpMailBranding::fromName(),
            ],
        );
    }
}
