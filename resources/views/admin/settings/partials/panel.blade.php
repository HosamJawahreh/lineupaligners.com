@php
    $prefix = $prefix ?? 'settings';
    $editable = $editable ?? true;
    if (! isset($systemStats)) {
        $diskTotal = @disk_total_space(base_path()) ?: 0;
        $diskFree = @disk_free_space(base_path()) ?: 0;
        $systemStats = [
            'memory_mb' => round(memory_get_usage(true) / 1024 / 1024),
            'cpu_percent' => function_exists('sys_getloadavg') ? min(100, (int) round((sys_getloadavg()[0] ?? 0) * 25)) : 0,
            'daily_traffic' => \App\Models\Patient::count(),
            'disk_percent' => $diskTotal > 0 ? round((($diskTotal - $diskFree) / $diskTotal) * 100, 2) : 0,
        ];
    }
    $stats = $systemStats;
    $bool = fn (string $key) => filter_var($settings[$key] ?? '0', FILTER_VALIDATE_BOOLEAN);
    $selectedSkin = old('theme_skin', $settings['theme_skin'] ?? 'cyan');
@endphp

<div class="card">
    <h6>General Settings</h6>
    <ul class="setting-list list-unstyled">
        @foreach([
            'report_panel_usage' => 'Report Panel Usage',
            'email_redirect' => 'Email Redirect',
            'notifications' => 'Notifications',
            'auto_updates' => 'Auto Updates',
        ] as $key => $label)
            <li>
                <div class="checkbox">
                    @if($editable)
                        <input id="{{ $prefix }}-{{ $key }}" name="{{ $key }}" type="checkbox" value="1" @checked(old($key, $bool($key)))>
                    @else
                        <input id="{{ $prefix }}-{{ $key }}" type="checkbox" @checked($bool($key)) disabled>
                    @endif
                    <label for="{{ $prefix }}-{{ $key }}">{{ $label }}</label>
                </div>
            </li>
        @endforeach
    </ul>
</div>

<div class="card">
    <h6>Skins</h6>
    <p class="text-muted small m-b-15">Choose the theme color for the application</p>
    <ul class="choose-skin list-unstyled settings-skin-picker skin-swatches">
        @foreach(config('settings.skins') as $skin => $meta)
            <li data-theme="{{ $skin }}" @class(['active' => $selectedSkin === $skin]) title="{{ $meta['label'] }}">
                @if($editable)
                    <input type="radio" name="theme_skin" id="{{ $prefix }}-skin-{{ $skin }}" value="{{ $skin }}" class="d-none" @checked($selectedSkin === $skin)>
                    <label for="{{ $prefix }}-skin-{{ $skin }}" class="skin-swatch-label m-b-0">
                        <span class="skin-swatch" style="background-color: {{ $meta['color'] }};"></span>
                        <small class="skin-swatch-name">{{ $meta['label'] }}</small>
                    </label>
                @else
                    <span class="skin-swatch" style="background-color: {{ $meta['color'] }};"></span>
                    <small class="skin-swatch-name">{{ $meta['label'] }}</small>
                @endif
            </li>
        @endforeach
    </ul>
</div>

<div class="card">
    <h6>Account Settings</h6>
    <ul class="setting-list list-unstyled">
        @foreach([
            'offline' => 'Offline',
            'location_permission' => 'Location Permission',
        ] as $key => $label)
            <li>
                <div class="checkbox">
                    @if($editable)
                        <input id="{{ $prefix }}-{{ $key }}" name="{{ $key }}" type="checkbox" value="1" @checked(old($key, $bool($key)))>
                    @else
                        <input id="{{ $prefix }}-{{ $key }}" type="checkbox" @checked($bool($key)) disabled>
                    @endif
                    <label for="{{ $prefix }}-{{ $key }}">{{ $label }}</label>
                </div>
            </li>
        @endforeach
    </ul>
</div>

<div class="card theme-light-dark">
    <h6>Left Menu</h6>
    @php $menuStyle = old('left_menu_style', $settings['left_menu_style'] ?? 'light'); @endphp
    @if($editable)
        <label class="btn btn-default btn-simple btn-round btn-block @if($menuStyle === 'light') active @endif">
            <input type="radio" name="left_menu_style" value="light" class="d-none" @checked($menuStyle === 'light')> Light
        </label>
        <label class="btn btn-default btn-round btn-block @if($menuStyle === 'dark') active @endif">
            <input type="radio" name="left_menu_style" value="dark" class="d-none" @checked($menuStyle === 'dark')> Dark
        </label>
        <label class="btn btn-primary btn-round btn-block @if($menuStyle === 'image') active @endif">
            <input type="radio" name="left_menu_style" value="image" class="d-none" @checked($menuStyle === 'image')> Sidebar Image
        </label>
    @else
        <button type="button" class="t-light btn btn-default btn-simple btn-round btn-block @if($menuStyle === 'light') active @endif">Light</button>
        <button type="button" class="t-dark btn btn-default btn-round btn-block @if($menuStyle === 'dark') active @endif">Dark</button>
        <button type="button" class="m_img_btn btn btn-primary btn-round btn-block @if($menuStyle === 'image') active @endif">Sidebar Image</button>
    @endif
</div>

<div class="card">
    <h6>Information Summary</h6>
    <div class="row m-b-20">
        <div class="col-7">
            <small class="displayblock">MEMORY USAGE</small>
            <h5 class="m-b-0 h6">{{ $stats['memory_mb'] }}</h5>
        </div>
        <div class="col-5">
            <div class="sparkline" data-type="bar" data-width="97%" data-height="25px" data-bar-Width="5" data-bar-Spacing="3" data-bar-Color="#00ced1">8,7,9,5,6,4,6,8</div>
        </div>
    </div>
    <div class="row m-b-20">
        <div class="col-7">
            <small class="displayblock">CPU USAGE</small>
            <h5 class="m-b-0 h6">{{ $stats['cpu_percent'] }}%</h5>
        </div>
        <div class="col-5">
            <div class="sparkline" data-type="bar" data-width="97%" data-height="25px" data-bar-Width="5" data-bar-Spacing="3" data-bar-Color="#F15F79">6,5,8,2,6,4,6,4</div>
        </div>
    </div>
    <div class="row m-b-20">
        <div class="col-7">
            <small class="displayblock">DAILY TRAFFIC</small>
            <h5 class="m-b-0 h6">{{ number_format($stats['daily_traffic']) }}</h5>
        </div>
        <div class="col-5">
            <div class="sparkline" data-type="bar" data-width="97%" data-height="25px" data-bar-Width="5" data-bar-Spacing="3" data-bar-Color="#78b83e">7,5,8,7,4,2,6,5</div>
        </div>
    </div>
    <div class="row">
        <div class="col-7">
            <small class="displayblock">DISK USAGE</small>
            <h5 class="m-b-0 h6">{{ $stats['disk_percent'] }}%</h5>
        </div>
        <div class="col-5">
            <div class="sparkline" data-type="bar" data-width="97%" data-height="25px" data-bar-Width="5" data-bar-Spacing="3" data-bar-Color="#457fca">7,5,2,5,6,7,6,4</div>
        </div>
    </div>
</div>
