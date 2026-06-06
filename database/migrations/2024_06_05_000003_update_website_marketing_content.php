<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $aboutBody = "Lineup Aligner is dedicated to providing high-quality and affordable orthodontic solutions.\n\n"
            ."Our team prioritizes innovation and precision engineering, setting a new standard in orthodontic care. "
            ."We aim to empower doctors with the tools and support needed for orthodontic excellence.\n\n"
            ."Our clear aligner treatments have a high success rate, disproving industry skepticism. "
            ."Doctors can trust Lineup Aligner to deliver effective and reliable clear aligner solutions.";

        Setting::setMany([
            'website_hero_eyebrow' => 'Welcome dentists',
            'website_hero_title' => 'Clear aligner manufacturing — take advantage of our expertise',
            'website_hero_subtitle' => 'Welcome dentists to our clear aligner manufacturing. Introduce your patients to the future of clear aligners — our cutting-edge solutions deliver exceptional results.',
            'website_hero_slides' => json_encode(config('website.default_hero_slides'), JSON_UNESCAPED_UNICODE),
            'website_about_subtitle' => 'Your winning smile fuels our passion',
            'website_about_title' => 'High-quality, affordable orthodontic solutions',
            'website_about_body' => $aboutBody,
            'website_about_highlights' => json_encode(config('website.default_about_highlights'), JSON_UNESCAPED_UNICODE),
            'website_platform_subtitle' => 'Why LINEUP',
            'website_platform_title' => 'What distinguishes LINEUP from others?',
            'website_platform_intro' => '',
            'website_features' => json_encode(config('website.default_features'), JSON_UNESCAPED_UNICODE),
            'website_treatments_subtitle' => config('website.default_treatments.subtitle'),
            'website_treatments_title' => config('website.default_treatments.title'),
            'website_treatments_intro' => config('website.default_treatments.intro'),
            'website_treatable_subtitle' => config('website.default_treatable_cases.subtitle'),
            'website_treatable_title' => config('website.default_treatable_cases.title'),
            'website_treatable_intro' => config('website.default_treatable_cases.intro'),
            'website_treatable_items' => json_encode(config('website.default_treatable_cases.items'), JSON_UNESCAPED_UNICODE),
            'website_faq_subtitle' => config('website.default_faq.subtitle'),
            'website_faq_title' => config('website.default_faq.title'),
            'website_faq_items' => json_encode(config('website.default_faq.items'), JSON_UNESCAPED_UNICODE),
            'website_footer_tagline' => 'Your winning smile fuels our passion. Lineup Aligner — dedicated to high-quality, affordable orthodontic solutions.',
        ]);

        $sections = json_decode((string) Setting::get('website_section_visibility', ''), true) ?: [];
        $sections['treatable_cases'] = true;
        Setting::set('website_section_visibility', json_encode(array_merge(config('website.default_sections'), $sections)));
    }

    public function down(): void
    {
        // Content migration — no automatic rollback.
    }
};
