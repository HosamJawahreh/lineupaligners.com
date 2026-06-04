<?php

return [
    /**
     * Case study progress bar (5 steps). Manufactured is shown on Approved;
     * modification is shown on Treatment Plan until a plan is pending review.
     */
    'progress_steps' => [
        ['key' => 'created', 'label' => 'Case Created'],
        ['key' => 'waiting_plan', 'label' => 'Treatment Plan'],
        ['key' => 'case_status', 'label' => 'Doctor Review'],
        ['key' => 'approved', 'label' => 'Approved'],
        ['key' => 'refinement', 'label' => 'Refinement'],
    ],

    /** Badge colors in cases list (all internal stages) */
    'badge_classes' => [
        'created' => 'workflow-created',
        'waiting_plan' => 'workflow-waiting',
        'case_status' => 'workflow-review',
        'approved' => 'workflow-approved',
        'manufactured' => 'workflow-manufactured',
        'modification' => 'workflow-modification',
        'refinement' => 'workflow-refinement',
    ],

    'default_stage' => 'created',

    /** Fallback when case_workflow_stage is empty (legacy rows) */
    'status_fallback' => [
        'pending' => 'waiting_plan',
        'approved' => 'approved',
        'rejected' => 'waiting_plan',
    ],
];
