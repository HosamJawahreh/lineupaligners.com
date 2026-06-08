<?php

namespace App\Notifications;

use App\Support\LineUpMailBranding;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends ResetPassword
{
    public function toMail($notifiable): MailMessage
    {
        $resetUrl = $this->resetUrl($notifiable);
        $expiresMinutes = (int) config(
            'auth.passwords.'.config('auth.defaults.passwords').'.expire',
            60
        );

        $message = (new MailMessage)
            ->subject(LineUpMailBranding::subjectPrefix('Reset your password'))
            ->markdown('mail.reset-password', [
                'userName' => $notifiable->displayName(),
                'resetUrl' => $resetUrl,
                'expiresMinutes' => $expiresMinutes,
                'clinicName' => LineUpMailBranding::clinicName(),
            ]);

        $replyTo = LineUpMailBranding::replyToAddress();

        if ($replyTo) {
            $message->replyTo($replyTo, LineUpMailBranding::fromName());
        }

        return $message;
    }
}
