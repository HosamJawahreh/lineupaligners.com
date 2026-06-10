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
        'plan_revised',
        'plan_approved',
        'plan_rejected',
        'modification_requested',
        'case_ready_for_manufacture',
        'case_manufactured',
        'refinement_requested',
    ],

    'type_labels' => [
        'case_created' => 'Case created / submitted',
        'case_message' => 'Case chat message',
        'plan_uploaded' => 'Treatment plan uploaded',
        'plan_revised' => 'Revised plan uploaded (after modification)',
        'plan_approved' => 'Plan approved by doctor',
        'plan_rejected' => 'Plan rejected by doctor',
        'modification_requested' => 'Modification requested',
        'case_ready_for_manufacture' => 'Ready to mark manufactured',
        'case_manufactured' => 'Case marked manufactured',
        'refinement_requested' => 'Refinement ordered',
    ],

    'type_recipients' => [
        'case_created' => 'Admin & assigned doctor',
        'case_message' => 'Admin ↔ assigned doctor',
        'plan_uploaded' => 'Assigned doctor',
        'plan_revised' => 'Assigned doctor',
        'plan_approved' => 'Admin',
        'plan_rejected' => 'Admin',
        'modification_requested' => 'Admin',
        'case_ready_for_manufacture' => 'Admin',
        'case_manufactured' => 'Assigned doctor',
        'refinement_requested' => 'Admin',
    ],

];
