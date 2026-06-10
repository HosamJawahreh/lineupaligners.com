<?php

namespace App\Notifications;

use App\Models\Patient;
use App\Models\User;
use App\Support\LineUpMailBranding;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class LineUpAlert extends Notification
{
    use Queueable;

    /**
     * @param  array{type: string, title: string, body: string, url: string, icon?: string, open_tab?: string|null, mfg_stage?: int|null, patient_id?: int|null}  $payload
     */
    public function __construct(
        public array $payload
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $name = $notifiable instanceof User
            ? $notifiable->displayName()
            : ($notifiable->name ?? 'there');

        return (new MailMessage)
            ->subject(LineUpMailBranding::patientCaseEmailSubject($this->resolvePatientName()))
            ->markdown('mail.lineup-alert', [
                'title' => $this->payload['title'],
                'body' => $this->payload['body'],
                'url' => $this->absoluteUrl($this->payload['url']),
                'name' => $name,
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => $this->payload['type'],
            'title' => $this->payload['title'],
            'body' => $this->payload['body'],
            'url' => $this->payload['url'],
            'icon' => $this->payload['icon'] ?? 'zmdi-notifications',
            'open_tab' => $this->payload['open_tab'] ?? null,
            'mfg_stage' => $this->payload['mfg_stage'] ?? null,
            'patient_id' => $this->payload['patient_id'] ?? null,
        ];
    }

    protected function resolvePatientName(): string
    {
        $patientName = trim((string) ($this->payload['patient_name'] ?? ''));

        if ($patientName === '' && ! empty($this->payload['patient_id'])) {
            $patient = Patient::query()
                ->select(['id', 'first_name', 'last_name'])
                ->find($this->payload['patient_id']);

            $patientName = trim($patient?->fullName() ?? '');
        }

        return $patientName;
    }

    protected function shouldSendMail(object $notifiable): bool
    {
        if (! config('lineup-notifications.email.enabled', true)) {
            return false;
        }

        if (! $notifiable instanceof User) {
            return false;
        }

        if (! in_array($notifiable->role, [User::ROLE_ADMIN, User::ROLE_DOCTOR], true)) {
            return false;
        }

        return filled($notifiable->email);
    }

    protected function absoluteUrl(string $url): string
    {
        if (Str::startsWith($url, ['http://', 'https://'])) {
            return $url;
        }

        return url($url);
    }
}
