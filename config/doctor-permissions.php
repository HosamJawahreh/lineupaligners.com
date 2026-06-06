<?php

return [

    'groups' => [
        'cases' => 'Case management',
        'workflow' => 'Treatment workflow',
    ],

    'permissions' => [
        'view_cases' => [
            'label' => 'View assigned cases',
            'group' => 'cases',
            'hint' => 'Open case study, scans, and treatment history for cases assigned to this doctor.',
        ],
        'create_cases' => [
            'label' => 'Submit new cases',
            'group' => 'cases',
            'hint' => 'Create aligner cases with patient details, scans, and photos.',
        ],
        'edit_cases' => [
            'label' => 'Edit case details',
            'group' => 'cases',
            'hint' => 'Update patient info, scans, and clinical photos on assigned cases.',
        ],
        'delete_cases' => [
            'label' => 'Delete cases',
            'group' => 'cases',
            'hint' => 'Permanently remove cases from the system.',
        ],
        'case_chat' => [
            'label' => 'Case messaging',
            'group' => 'workflow',
            'hint' => 'Exchange messages with LineUp admin on assigned cases.',
        ],
        'review_plans' => [
            'label' => 'Review treatment plans',
            'group' => 'workflow',
            'hint' => 'Approve or reject manufacture plans uploaded by LineUp.',
        ],
        'request_modification' => [
            'label' => 'Request modification',
            'group' => 'workflow',
            'hint' => 'Request plan changes with new 3D scans after initial approval.',
        ],
        'request_refinement' => [
            'label' => 'Order refinement',
            'group' => 'workflow',
            'hint' => 'Start a refinement cycle after the case is manufactured.',
        ],
    ],

    /** Maps legacy permission keys (pre-workflow roles) to current keys. */
    'legacy_map' => [
        'manage_patients' => ['view_cases', 'case_chat'],
        'create_patients' => ['create_cases'],
        'edit_patients' => ['edit_cases'],
        'delete_patients' => ['delete_cases'],
        'view_all_patients' => [],
    ],

];
