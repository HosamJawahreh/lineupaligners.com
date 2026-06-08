<?php

return [
    'source_path' => 'Smiliz HTML Files',
    'homepage_key' => 'homepage-2',

    'groups' => [
        'home' => 'Home',
        'about' => 'About',
        'services' => 'Services',
        'case_study' => 'Case Study',
        'blog' => 'Blog',
        'faq' => 'FAQ',
        'contact' => 'Contact',
        'other' => 'Other',
    ],

    'group_hints' => [
        'home' => 'Alternate homepage layouts from the template pack.',
        'about' => 'Company info, team, and appointment pages.',
        'faq' => 'Frequently asked questions — shown as its own menu link.',
        'services' => 'Service listing and detail layouts.',
        'case_study' => 'Case studies grid (4 columns) and detail page.',
        'blog' => 'Blog listing and article template.',
        'contact' => 'Contact page variants.',
        'other' => 'Miscellaneous demo pages.',
    ],

    /** Pages we suggest enabling for a typical clinic site. */
    'recommended' => [
        'about-us',
        'service-details',
        'faq',
        'portfolio-grid-col-4',
        'blog-classic',
        'contact-us-01',
        'our-history',
        'our-dentist',
        'case-study-style-1',
        'blog-single-details',
    ],

    'blog_key_pattern' => '/^blog-/',
    'case_study_key_pattern' => '/^(portfolio-|case-study-)/',

    /**
     * Demo templates hidden from admin, routes, and navigation.
     * Blog: classic listing + single article. Case studies: 4-col grid + one detail layout.
     */
    'excluded_page_patterns' => [
        '/^blog-(?!classic|single-details)/',
        '/^portfolio-(?!grid-col-4)/',
        '/^case-study-style-2$/',
    ],

    /** Copy saved page toggles when replacing a listing template. */
    'page_setting_aliases' => [
        'portfolio-grid-col-3' => 'portfolio-grid-col-4',
    ],

    /**     * Per-page overrides. Any *.html in the template folder is auto-registered;
     * missing keys use defaults (enabled, not in main nav).
     */
    'page_meta' => [
        'index' => [
            'label' => 'Homepage 01',
            'path' => 'home-1',
            'group' => 'home',
            'default_enabled' => true,
            'default_in_nav' => false,
        ],
        'about-us' => [
            'label' => 'About Us',
            'path' => 'about',
            'group' => 'about',
            'default_enabled' => true,
            'default_in_nav' => true,
        ],
        'our-history' => [
            'label' => 'Our History',
            'path' => 'about/history',
            'group' => 'about',
            'default_enabled' => true,
            'default_in_nav' => false,
        ],
        'our-dentist' => [
            'label' => 'Our Team',
            'path' => 'about/team',
            'group' => 'about',
            'default_enabled' => true,
            'default_in_nav' => false,
        ],
        'dentist-profile' => [
            'label' => 'Dentist Profile',
            'path' => 'about/team/profile',
            'group' => 'about',
            'default_enabled' => true,
            'default_in_nav' => false,
        ],
        'faq' => [
            'label' => 'FAQ',
            'path' => 'faq',
            'group' => 'faq',
            'default_enabled' => true,
            'default_in_nav' => true,
        ],
        'appointment' => [
            'label' => 'Book Appointment',
            'path' => 'appointment',
            'group' => 'about',
            'default_enabled' => true,
            'default_in_nav' => false,
        ],
        'service-details' => [
            'label' => 'Services',
            'path' => 'services',
            'group' => 'services',
            'default_enabled' => true,
            'default_in_nav' => true,
        ],
        'portfolio-grid-col-4' => [
            'label' => 'Case Studies',
            'path' => 'case-studies',
            'group' => 'case_study',
            'default_enabled' => true,
            'default_in_nav' => true,
        ],
        'case-study-style-1' => [
            'label' => 'Case Study Detail',
            'path' => 'case-studies/detail',
            'group' => 'case_study',
            'default_enabled' => true,
            'default_in_nav' => false,
        ],
        'blog-classic' => [
            'label' => 'Blog',
            'file' => 'blog-grid-col-4.html',
            'path' => 'blog',
            'group' => 'blog',
            'default_enabled' => true,
            'default_in_nav' => true,
        ],
        'blog-single-details' => [
            'label' => 'Blog Article',
            'path' => 'blog/article',
            'group' => 'blog',
            'default_enabled' => true,
            'default_in_nav' => false,
        ],
        'contact-us-01' => [
            'label' => 'Contact',
            'path' => 'contact',
            'group' => 'contact',
            'default_enabled' => true,
            'default_in_nav' => true,
        ],
        'contact-us-02' => [
            'label' => 'Contact (Alt)',
            'path' => 'contact-alt',
            'group' => 'contact',
            'default_enabled' => true,
            'default_in_nav' => false,
        ],
    ],

    'page_descriptions' => [
        'about-us' => 'Learn about LineUp Aligner — our mission, team, and approach to clear aligner treatment for partner clinics.',
        'our-history' => 'Discover the story behind LineUp Aligner and how we support doctors with digital orthodontics workflows.',
        'our-dentist' => 'Meet the clinical and support team behind LineUp Aligner partner clinics.',
        'service-details' => 'Browse all clear aligner services from LineUp Aligner — each with its own detail page.',
        'faq' => 'Answers to common questions about clear aligner treatment, partner clinics, and the LineUp platform.',
        'portfolio-grid-col-4' => 'Browse real clear aligner case results and before-and-after outcomes from partner clinics.',
        'case-study-style-1' => 'Detailed clear aligner case study with treatment timeline, goals, and clinical outcomes.',
        'blog-classic' => 'News, tips, and insights about clear aligner treatment from the LineUp Aligner team.',
        'blog-single-details' => 'Read the full article on clear aligners, clinical workflows, and patient care.',
        'contact-us-01' => 'Contact LineUp Aligner for appointments, partner inquiries, and clinic support.',
        'contact-us-02' => 'Get in touch with LineUp Aligner — phone, email, and clinic location details.',
        'appointment' => 'Book an appointment or request a consultation with a LineUp Aligner partner clinic.',
    ],

    /** Icons for public header navigation (pbmit-base, pbmit-smiliz, or themify ti-*). */
    'nav_icons' => [
        'home' => 'ti-home',
        'groups' => [
            'about' => 'ti-user',
            'services' => 'pbmit-smiliz-icon-dental-care',
            'case_study' => 'pbmit-base-icon-gallery',
            'blog' => 'pbmit-base-icon-label',
            'faq' => 'pbmit-base-icon-speech-bubble',
            'contact' => 'pbmit-base-icon-mail-1',
        ],
        'pages' => [
            'about-us' => 'ti-user',
            'our-history' => 'pbmit-base-icon-calendar-silhouette',
            'our-dentist' => 'pbmit-base-icon-user-1',
            'dentist-profile' => 'pbmit-base-icon-user-circle',
            'faq' => 'pbmit-base-icon-speech-bubble',
            'service-details' => 'pbmit-smiliz-icon-dental-care',
            'portfolio-grid-col-4' => 'pbmit-base-icon-gallery',
            'case-study-style-1' => 'pbmit-base-icon-gallery',
            'blog-classic' => 'pbmit-base-icon-label',
            'blog-single-details' => 'pbmit-base-icon-label',
            'contact-us-01' => 'pbmit-base-icon-mail-1',
            'contact-us-02' => 'pbmit-base-icon-phone-1',
            'appointment' => 'pbmit-base-icon-phone-1',
        ],
    ],
];
