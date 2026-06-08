<?php

namespace App\Mail;

use App\Models\WebsiteContactInquiry;
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
        public WebsiteContactInquiry $inquiry,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(
                LineUpMailBranding::fromAddress(),
                LineUpMailBranding::fromName(),
            ),
            subject: LineUpMailBranding::subjectPrefix('We received your message'),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.website-inquiry-confirmation',
            with: [
                'inquirerName' => $this->inquiry->name,
                'inquiryMessage' => $this->inquiry->message,
            ],
        );
    }
}
