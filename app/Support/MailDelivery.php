<?php

namespace App\Support;

class MailDelivery
{
    /** @var list<string> */
    private const INBOX_MAILERS = [
        'smtp',
        'sendmail',
        'mailgun',
        'ses',
        'ses-v2',
        'postmark',
        'resend',
    ];

    public static function deliversToInbox(): bool
    {
        return in_array((string) config('mail.default'), self::INBOX_MAILERS, true);
    }

    public static function currentMailer(): string
    {
        return (string) config('mail.default', 'log');
    }

    public static function configurationMessage(): string
    {
        $mailer = self::currentMailer();

        if ($mailer === 'log') {
            return 'Email is not being delivered: MAIL_MAILER is set to "log", so messages are only written to storage/logs. Configure SMTP in your server .env (MAIL_HOST, MAIL_PORT, MAIL_USERNAME, MAIL_PASSWORD, MAIL_FROM_ADDRESS).';
        }

        if ($mailer === 'array') {
            return 'Email is not being delivered: MAIL_MAILER is set to "array" (test mode only).';
        }

        return 'Email could not be delivered. Verify SMTP settings in .env and that MAIL_FROM_ADDRESS matches your mail account.';
    }
}
