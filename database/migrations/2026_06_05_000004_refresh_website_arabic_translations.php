<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $translations = config('website-i18n-ar', []);

        if ($translations === []) {
            return;
        }

        Setting::set('website_i18n_ar', json_encode($translations, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    public function down(): void
    {
        // Intentionally left blank — previous Arabic overlay may have been customized.
    }
};
