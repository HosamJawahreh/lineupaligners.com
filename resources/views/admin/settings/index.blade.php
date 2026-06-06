@extends('layouts.settings')

@section('title', 'System Settings')
@section('settings-heading', 'System Settings')
@section('settings-subheading', 'Manage clinic & admin configuration')

@section('settings-tabs')
    <li class="nav-item">
        <a class="nav-link active" data-toggle="tab" href="#tab-branding" role="tab">
            <i class="zmdi zmdi-image"></i> Branding
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-toggle="tab" href="#tab-profile" role="tab">
            <i class="zmdi zmdi-account"></i> Admin Profile
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-toggle="tab" href="#tab-clinic" role="tab">
            <i class="zmdi zmdi-hospital"></i> Clinic
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-toggle="tab" href="#tab-appearance" role="tab">
            <i class="zmdi zmdi-palette"></i> Appearance
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-toggle="tab" href="#tab-doctor-roles" role="tab">
            <i class="zmdi zmdi-assignment-account"></i> Doctor Roles
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-toggle="tab" href="#tab-notifications" role="tab">
            <i class="zmdi zmdi-notifications"></i> Notifications
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-toggle="tab" href="#tab-workflow" role="tab">
            <i class="zmdi zmdi-refresh-sync"></i> Workflow
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-toggle="tab" href="#tab-system" role="tab">
            <i class="zmdi zmdi-globe"></i> System
        </a>
    </li>
@endsection

@section('settings-panels')
    {{-- Branding --}}
    <div class="tab-pane active" id="tab-branding" role="tabpanel">
        <div class="row clearfix">
            <div class="col-lg-7 col-md-12">
                <p class="settings-section-title">Project Branding</p>
                <div class="settings-logo-box">
                    <img src="{{ $logoUrl }}" id="logo-preview" alt="Logo">
                </div>
                <div class="form-group">
                    <label>Project Name</label>
                    <input type="text" name="project_name" class="form-control" form="settings-form" value="{{ old('project_name', $settings['project_name'] ?? config('app.name')) }}" required>
                    <small class="text-muted">Displayed in the navbar and browser title</small>
                </div>
                <div class="form-group form-file-field">
                    <label>Upload Logo</label>
                    <input type="file" name="logo" id="logo-input" form="settings-form" accept="image/jpeg,image/png,image/svg+xml,image/webp">
                    <small class="text-muted d-block m-t-5">PNG, JPG, SVG or WebP — max 2MB</small>
                </div>
                @if(! empty($settings['logo']))
                <div class="checkbox">
                    <input type="checkbox" name="remove_logo" id="remove_logo" value="1" form="settings-form">
                    <label for="remove_logo">Remove current logo and use default</label>
                </div>
                @endif
                @include('admin.settings.partials.brand-colors', ['settings' => $settings])
            </div>
            <div class="col-lg-5 col-md-12">
                <div class="settings-summary-card">
                    <p class="settings-section-title m-b-15">Live Preview</p>
                    <div class="d-flex align-items-center p-3 bg-white rounded border">
                        <img src="{{ $logoUrl }}" width="36" height="36" alt="Logo" class="m-r-10">
                        <strong>{{ old('project_name', $settings['project_name'] ?? config('app.name')) }}</strong>
                    </div>
                    <p class="text-muted small m-t-15 m-b-0">This is how your brand appears in the top navigation bar.</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Admin Profile --}}
    <div class="tab-pane" id="tab-profile" role="tabpanel">
        <div class="row clearfix">
            <div class="col-lg-3 col-md-4 text-center m-b-20">
                <img src="{{ $admin->photoUrl() }}" id="profile-photo-preview" class="settings-profile-avatar" alt="Admin">
                <p class="m-t-10 m-b-0"><strong>{{ $admin->name }}</strong></p>
                <small class="text-muted">Administrator</small>
                <div class="form-group form-file-field text-left m-t-20">
                    <label>Profile Picture</label>
                    <input type="file" name="photo" id="profile-photo-input" form="settings-form" accept="image/jpeg,image/png,image/webp">
                    <small class="text-muted d-block m-t-5">PNG, JPG or WebP — max 2MB</small>
                </div>
                @if($admin->photo)
                <div class="checkbox text-left">
                    <input type="checkbox" name="remove_photo" id="remove_photo" value="1" form="settings-form">
                    <label for="remove_photo">Remove profile picture</label>
                </div>
                @endif
            </div>
            <div class="col-lg-9 col-md-8">
                <p class="settings-section-title">Account Details</p>
                <div class="row clearfix">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Full Name</label>
                            <input type="text" name="name" class="form-control" form="settings-form" value="{{ old('name', $admin->name) }}" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control" form="settings-form" value="{{ old('email', $admin->email) }}" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Phone</label>
                            <input type="text" name="phone" class="form-control" form="settings-form" value="{{ old('phone', $admin->phone) }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>New Password</label>
                            <input type="password" name="password" class="form-control" form="settings-form" autocomplete="new-password" placeholder="Leave blank to keep current">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Confirm Password</label>
                            <input type="password" name="password_confirmation" class="form-control" form="settings-form" autocomplete="new-password">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Clinic --}}
    <div class="tab-pane" id="tab-clinic" role="tabpanel">
        <p class="settings-section-title">Clinic Information</p>
        <div class="row clearfix">
            <div class="col-md-6">
                <div class="form-group">
                    <label>Clinic Name</label>
                    <input type="text" name="clinic_name" class="form-control" form="settings-form" value="{{ old('clinic_name', $settings['clinic_name']) }}" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Contact Email</label>
                    <input type="email" name="clinic_email" class="form-control" form="settings-form" value="{{ old('clinic_email', $settings['clinic_email']) }}">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Phone</label>
                    <input type="text" name="clinic_phone" class="form-control" form="settings-form" value="{{ old('clinic_phone', $settings['clinic_phone']) }}">
                </div>
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <label>Address</label>
                    <textarea name="clinic_address" class="form-control" form="settings-form" rows="3">{{ old('clinic_address', $settings['clinic_address']) }}</textarea>
                </div>
            </div>
        </div>
    </div>

    {{-- Appearance --}}
    <div class="tab-pane" id="tab-appearance" role="tabpanel">
        @php
            $selectedFont = old('dashboard_font', $settings['dashboard_font'] ?? 'cairo');
            $fontPreviewUrl = 'https://fonts.googleapis.com/css2?family=';
            $fontPreviewUrl .= collect(config('settings.fonts', []))->map(function ($font) {
                $family = str_replace(' ', '+', $font['family']);
                return $family.':wght@400;600;700';
            })->implode('&family=');
            $fontPreviewUrl .= '&display=swap';
        @endphp
        <link rel="stylesheet" href="{{ $fontPreviewUrl }}">
        <div class="settings-panel-grid">
            <div class="inner-card settings-font-card">
                <h6>Dashboard Font</h6>
                <p class="text-muted small m-b-15">Choose the typeface used across the admin dashboard, menus, tables, and forms.</p>
                <ul class="settings-font-picker list-unstyled m-b-0">
                    @foreach(config('settings.fonts', []) as $fontKey => $font)
                    <li @class(['active' => $selectedFont === $fontKey])>
                        <input type="radio"
                               name="dashboard_font"
                               id="page-font-{{ $fontKey }}"
                               value="{{ $fontKey }}"
                               class="d-none"
                               form="settings-form"
                               data-font-family="{{ $font['family'] }}"
                               data-font-stack="{{ $font['stack'] }}"
                               @checked($selectedFont === $fontKey)>
                        <label for="page-font-{{ $fontKey }}" class="settings-font-option m-b-0">
                            <span class="settings-font-option__name" style="font-family: {{ $font['stack'] }};">{{ $font['label'] }}</span>
                            <span class="settings-font-option__sample" style="font-family: {{ $font['stack'] }};">Patient cases, treatment plans, and clinical notes.</span>
                        </label>
                    </li>
                    @endforeach
                </ul>
                <div class="settings-font-live-preview m-t-20" id="settings-font-live-preview" aria-live="polite">
                    <span class="settings-font-live-preview__label">Preview</span>
                    <p class="settings-font-live-preview__title" id="settings-font-preview-title" style="font-family: {{ config('settings.fonts.'.$selectedFont.'.stack', config('settings.fonts.cairo.stack')) }};">Dashboard heading</p>
                    <p class="settings-font-live-preview__body" id="settings-font-preview-body" style="font-family: {{ config('settings.fonts.'.$selectedFont.'.stack', config('settings.fonts.cairo.stack')) }};">This is how body text, buttons, and tables will look after you save.</p>
                </div>
            </div>
            <div class="inner-card settings-color-mode-card">
                <h6>Dashboard Color Mode</h6>
                <p class="text-muted small m-b-15">Switch the entire dashboard between light and dark. Everyone sees the saved default; users can also toggle from the top bar (saved in this browser).</p>
                @php $colorMode = old('dashboard_color_mode', $settings['dashboard_color_mode'] ?? 'light'); @endphp
                <ul class="settings-color-mode-picker list-unstyled m-b-0">
                    <li @class(['active' => $colorMode === 'light'])>
                        <input type="radio"
                               name="dashboard_color_mode"
                               id="page-color-light"
                               value="light"
                               class="d-none"
                               form="settings-form"
                               @checked($colorMode === 'light')>
                        <label for="page-color-light" class="settings-color-mode-option m-b-0">
                            <span class="settings-color-mode-option__swatch settings-color-mode-option__swatch--light" aria-hidden="true"></span>
                            <span class="settings-color-mode-option__title">Light</span>
                            <span class="settings-color-mode-option__hint">Bright backgrounds for daytime use.</span>
                        </label>
                    </li>
                    <li @class(['active' => $colorMode === 'dark'])>
                        <input type="radio"
                               name="dashboard_color_mode"
                               id="page-color-dark"
                               value="dark"
                               class="d-none"
                               form="settings-form"
                               @checked($colorMode === 'dark')>
                        <label for="page-color-dark" class="settings-color-mode-option m-b-0">
                            <span class="settings-color-mode-option__swatch settings-color-mode-option__swatch--dark" aria-hidden="true"></span>
                            <span class="settings-color-mode-option__title">Dark</span>
                            <span class="settings-color-mode-option__hint">Dim surfaces that are easier on the eyes.</span>
                        </label>
                    </li>
                </ul>
            </div>
            <div class="inner-card">
                <h6>Theme Skins</h6>
                <p class="text-muted small m-b-15">Primary accent when no custom primary color is set in Branding.</p>
                @php $selectedSkin = old('theme_skin', $settings['theme_skin'] ?? 'cyan'); @endphp
                <ul class="choose-skin list-unstyled settings-skin-picker skin-swatches m-b-0">
                    @foreach(config('settings.skins') as $skin => $meta)
                        <li data-theme="{{ $skin }}" @class(['active' => $selectedSkin === $skin])>
                            <input type="radio" name="theme_skin" id="page-skin-{{ $skin }}" value="{{ $skin }}" class="d-none" form="settings-form" @checked($selectedSkin === $skin)>
                            <label for="page-skin-{{ $skin }}" class="skin-swatch-label m-b-0">
                                <span class="skin-swatch" style="background-color: {{ $meta['color'] }};"></span>
                                <small class="skin-swatch-name">{{ $meta['label'] }}</small>
                            </label>
                        </li>
                    @endforeach
                </ul>
            </div>
            <div class="inner-card theme-light-dark">
                <h6>Sidebar Style</h6>
                <p class="text-muted small m-b-15">Sidebar look in light mode. Dark dashboard mode uses a dark sidebar automatically.</p>
                @php $menuStyle = old('left_menu_style', $settings['left_menu_style'] ?? 'light'); @endphp
                <label class="btn btn-default btn-simple btn-round btn-block @if($menuStyle === 'light') active @endif">
                    <input type="radio" name="left_menu_style" value="light" class="d-none" form="settings-form" @checked($menuStyle === 'light')> Light
                </label>
                <label class="btn btn-default btn-round btn-block @if($menuStyle === 'dark') active @endif">
                    <input type="radio" name="left_menu_style" value="dark" class="d-none" form="settings-form" @checked($menuStyle === 'dark')> Dark
                </label>
                <label class="btn btn-primary btn-round btn-block @if($menuStyle === 'image') active @endif">
                    <input type="radio" name="left_menu_style" value="image" class="d-none" form="settings-form" @checked($menuStyle === 'image')> Sidebar Image
                </label>
            </div>
            <div class="inner-card">
                <h6>Current Theme</h6>
                <div class="summary-item"><span>Color mode</span><span>{{ ucfirst($colorMode) }}</span></div>
                <div class="summary-item"><span>Primary</span><span>{{ $brandColors['primary'] ?? '—' }}</span></div>
                <div class="summary-item"><span>Secondary</span><span>{{ $brandColors['secondary'] ?? '—' }}</span></div>
                <div class="summary-item"><span>Skin</span><span>{{ ucfirst($settings['theme_skin'] ?? 'cyan') }}</span></div>
                <div class="summary-item"><span>Font</span><span>{{ config('settings.fonts.'.$selectedFont.'.label', 'Cairo') }}</span></div>
                <div class="summary-item"><span>Sidebar</span><span>{{ ucfirst($settings['left_menu_style'] ?? 'light') }}</span></div>
                <div class="summary-item"><span>Environment</span><span>{{ config('app.env') }}</span></div>
            </div>
        </div>
    </div>

    @include('admin.settings.partials.doctor-roles', ['doctorRoles' => $doctorRoles])
    @include('admin.settings.partials.notifications-settings', ['settings' => $settings, 'notificationTypes' => $notificationTypes ?? []])
    @include('admin.settings.partials.workflow-settings', ['settings' => $settings])
    @include('admin.settings.partials.system-health', ['settings' => $settings, 'systemStats' => $systemStats])
@endsection
