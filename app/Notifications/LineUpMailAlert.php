<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class LineUpMailAlert extends LineUpAlert implements ShouldQueue
{
    use Queueable;

    public function via(object $notifiable): array
    {
        if (! $this->shouldSendMail($notifiable)) {
            return [];
        }

        return ['mail'];
    }
}
