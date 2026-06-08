<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $defaults = config('website.default_contact_page', []);
        $stored = json_decode((string) Setting::get('website_contact_page', ''), true);

        if (is_array($stored)) {
            $stored = $this->replaceLegacyFormCopy($stored, $defaults);
            Setting::set('website_contact_page', json_encode($stored, JSON_UNESCAPED_UNICODE));
        }

        $arabic = json_decode((string) Setting::get('website_i18n_ar', ''), true);

        if (is_array($arabic) && isset($arabic['contact']['page']) && is_array($arabic['contact']['page'])) {
            $arabicDefaults = config('website-i18n-ar.contact.page', []);
            $arabic['contact']['page'] = $this->replaceLegacyFormCopy($arabic['contact']['page'], $arabicDefaults);
            Setting::set('website_i18n_ar', json_encode($arabic, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        }
    }

    /** @param  array<string, string>  $page
     * @param  array<string, string>  $defaults
     * @return array<string, string>
     */
    private function replaceLegacyFormCopy(array $page, array $defaults): array
    {
        $legacyFormTitles = [
            'book an appointment',
            'book appointment',
            'schedule an appointment',
            'احجز موعداً',
            'احجز موعد',
        ];

        $legacyFormIntros = [
            'call us with any emergency or to schedule an appointment.',
            'call us to schedule an appointment',
            'call us to schedule an appointment.',
            'call us in any emergency or to schedule an appointment.',
            'اتصل بنا في أي حالة طارئة أو لحجز موعد.',
            'اتصل بنا لحجز موعد',
        ];

        $formTitle = strtolower(trim($page['form_title'] ?? ''));
        foreach ($legacyFormTitles as $legacyTitle) {
            if ($formTitle === strtolower($legacyTitle) || str_contains($formTitle, 'appointment')) {
                $page['form_title'] = $defaults['form_title'] ?? '';
                break;
            }
        }

        $formIntro = strtolower(trim($page['form_intro'] ?? ''));
        foreach ($legacyFormIntros as $legacyIntro) {
            if ($formIntro === strtolower($legacyIntro) || str_contains($formIntro, 'schedule an appointment') || str_contains($formIntro, 'emergency or to schedule')) {
                $page['form_intro'] = $defaults['form_intro'] ?? '';
                break;
            }
        }

        return $page;
    }

    public function down(): void
    {
        // Content migration — no automatic rollback.
    }
};
