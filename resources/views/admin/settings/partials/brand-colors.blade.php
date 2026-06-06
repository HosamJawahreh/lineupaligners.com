@php
    $brand = $brandColors ?? app(\App\Services\BrandColors::class)->tokens();
    $storedPrimary = old('brand_primary', $settings['brand_primary'] ?? '');
    $storedSecondary = old('brand_secondary', $settings['brand_secondary'] ?? config('settings.defaults.brand_secondary', '#09243c'));
    $resolvedPrimary = app(\App\Services\BrandColors::class)->normalizeHex($storedPrimary) ?: $brand['primary'];
    $resolvedSecondary = app(\App\Services\BrandColors::class)->normalizeHex($storedSecondary) ?: $brand['secondary'];
@endphp
<div class="settings-brand-colors m-t-24">
    <p class="settings-section-title">Brand colors</p>
    <p class="text-muted small m-b-15">Primary drives buttons and links. Secondary adds depth to headers, dark sections, and navigation accents on the dashboard and public website.</p>
    <div class="row clearfix">
        <div class="col-md-6">
            <div class="form-group">
                <label for="brand_primary">Primary color</label>
                <div class="settings-color-field">
                    <input type="color" id="brand_primary_picker" value="{{ $resolvedPrimary }}" aria-label="Primary color picker">
                    <input type="text" name="brand_primary" id="brand_primary" class="form-control" form="settings-form" value="{{ $storedPrimary }}" placeholder="{{ $brand['primary'] }}" maxlength="7" pattern="#?[0-9a-fA-F]{6}">
                </div>
                <small class="text-muted d-block m-t-5">Leave blank to follow the theme skin selected in Appearance.</small>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="brand_secondary">Secondary color</label>
                <div class="settings-color-field">
                    <input type="color" id="brand_secondary_picker" value="{{ $resolvedSecondary }}" aria-label="Secondary color picker">
                    <input type="text" name="brand_secondary" id="brand_secondary" class="form-control" form="settings-form" value="{{ $storedSecondary }}" placeholder="#09243c" maxlength="7" pattern="#?[0-9a-fA-F]{6}" required>
                </div>
                <small class="text-muted d-block m-t-5">Used for navy panels, header icons, case results bands, and sidebar accents.</small>
            </div>
        </div>
    </div>
    <div class="settings-brand-preview" id="settings-brand-preview">
        <div class="settings-brand-preview__panel settings-brand-preview__panel--secondary">
            <span class="settings-brand-preview__eyebrow">Secondary surface</span>
            <strong>Case results &amp; footer bands</strong>
        </div>
        <div class="settings-brand-preview__panel settings-brand-preview__panel--primary">
            <span class="settings-brand-preview__eyebrow">Primary action</span>
            <button type="button" class="btn btn-sm btn-primary settings-brand-preview__btn">Doctor Portal</button>
        </div>
        <div class="settings-brand-preview__swatches">
            <span><i style="background: var(--preview-primary);"></i> Primary</span>
            <span><i style="background: var(--preview-secondary);"></i> Secondary</span>
        </div>
    </div>
</div>
