<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Email notifications
    |--------------------------------------------------------------------------
    |
    | When enabled, every in-app LineUp alert also sends email to the
    | recipient's login address (doctors and administrators only).
    | Configure SMTP via MAIL_* variables in .env.
    |
    */

    'email' => [
        'enabled' => env('LINEUP_MAIL_NOTIFICATIONS', true),

        /** Queue outbound mail (requires: php artisan queue:work) */
        'queue' => env('LINEUP_MAIL_QUEUE', true),
    ],

    /** In-app notification sound (under public/assets/sounds/) */
    'sound' => 'assets/sounds/notification.mp3',

    /** In-app + email alert types (see LineUpNotifier) */
    'types' => [
        'case_created',
        'case_message',
        'plan_uploaded',
        'plan_approved',
        'plan_rejected',
        'modification_requested',
        'case_ready_for_manufacture',
        'case_manufactured',
        'refinement_requested',
    ],

];
