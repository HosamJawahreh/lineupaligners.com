<?php

/**
 * Icon sidebar navigation (cloud-style layout)
 */
return [
    'admin' => [
        ['label' => 'Dashboard', 'icon' => 'zmdi-view-dashboard', 'route' => 'dashboard', 'active' => 'dashboard'],
        ['label' => 'Cases', 'icon' => 'zmdi-folder', 'route' => 'patients.index', 'active' => 'patients.*'],
        ['label' => 'Doctors', 'icon' => 'zmdi-account', 'route' => 'doctors.index', 'active' => 'doctors.*'],
        ['label' => 'Notifications', 'icon' => 'zmdi-notifications', 'route' => 'notifications.index', 'active' => 'notifications.*', 'badge' => true],
        ['label' => 'Contact Requests', 'icon' => 'zmdi-email', 'route' => 'admin.contact-requests.index', 'active' => 'admin.contact-requests.*', 'badge' => true, 'badge_id' => 'lineup-sidebar-contact-badge'],
        ['label' => 'Website', 'icon' => 'zmdi-globe-alt', 'route' => 'admin.website.index', 'active' => 'admin.website.*'],
        ['label' => 'Profile', 'icon' => 'zmdi-account-circle', 'route' => 'profile.edit', 'active' => 'profile.*'],
        ['label' => 'Settings', 'icon' => 'zmdi-settings', 'route' => 'settings.index', 'active' => 'settings.*'],
    ],
    'doctor' => [
        ['label' => 'Dashboard', 'icon' => 'zmdi-view-dashboard', 'route' => 'dashboard', 'active' => 'dashboard'],
        ['label' => 'Cases', 'icon' => 'zmdi-folder', 'route' => 'patients.index', 'active' => 'patients.*'],
        ['label' => 'New Case', 'icon' => 'zmdi-plus-circle-o', 'route' => 'patients.create', 'active' => 'patients.create'],
        ['label' => 'Notifications', 'icon' => 'zmdi-notifications', 'route' => 'notifications.index', 'active' => 'notifications.*', 'badge' => true],
        ['label' => 'Profile', 'icon' => 'zmdi-account-circle', 'route' => 'profile.edit', 'active' => 'profile.*'],
        ['label' => 'Settings', 'icon' => 'zmdi-settings', 'route' => 'doctor.clinic-settings.edit', 'active' => 'doctor.clinic-settings.*'],
    ],
];
