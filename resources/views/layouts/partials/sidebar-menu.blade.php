@php
    $user = auth()->user();
    $menuItems = config('menu.'.$user->role, []);
@endphp
<ul class="list">
    <li>
        <div class="user-info">
            <div class="image">
                <a href="{{ $user->isAdmin() ? route('settings.index') : route('patients.index') }}">
                    <img src="{{ $user->photoUrl() }}" alt="User">
                </a>
            </div>
            <div class="detail">
                <h4>{{ $user->displayName() }}</h4>
                <small>{{ $user->displayTitle() }}</small>
            </div>
        </div>
    </li>
    @if($user->isDoctor())
        <li class="header">MAIN</li>
    @endif

    @foreach($menuItems as $item)
        @if(($item['type'] ?? null) === 'header')
            <li class="header">{{ $item['label'] }}</li>
            @continue
        @endif

        @if(($item['type'] ?? null) === 'widgets')
            <li>
                <div class="progress-container progress-primary m-t-10">
                    <span class="progress-badge">Traffic this Month</span>
                    <div class="progress">
                        <div class="progress-bar progress-bar-warning" role="progressbar" style="width: 67%;">
                            <span class="progress-value">67%</span>
                        </div>
                    </div>
                </div>
                <div class="progress-container progress-info">
                    <span class="progress-badge">Server Load</span>
                    <div class="progress">
                        <div class="progress-bar progress-bar-warning" role="progressbar" style="width: 86%;">
                            <span class="progress-value">86%</span>
                        </div>
                    </div>
                </div>
            </li>
            @continue
        @endif

        @php
            $activePattern = $item['active'] ?? ($item['route'] ?? '');
            $isActive = $activePattern ? request()->routeIs($activePattern) : false;
            $hasActiveChild = false;
            if (! empty($item['children'])) {
                foreach ($item['children'] as $child) {
                    $childPattern = $child['active'] ?? ($child['route'] ?? '');
                    if ($childPattern && request()->routeIs($childPattern)) {
                        $hasActiveChild = true;
                        break;
                    }
                }
            }
            $open = $isActive || $hasActiveChild;
        @endphp

        @if(! empty($item['children']))
            <li @class(['active open' => $open])>
                <a href="javascript:void(0);" class="menu-toggle">
                    <i class="zmdi {{ $item['icon'] }}"></i>
                    <span>{{ $item['label'] }}</span>
                </a>
                <ul class="ml-menu">
                    @foreach($item['children'] as $child)
                        @php
                            $childPattern = $child['active'] ?? ($child['route'] ?? '');
                            $childActive = $childPattern ? request()->routeIs($childPattern) : false;
                            $childHref = isset($child['route']) ? route($child['route']) : ($child['url'] ?? 'javascript:void(0);');
                        @endphp
                        <li @class(['active' => $childActive])>
                            <a href="{{ $childHref }}">{{ $child['label'] }}</a>
                        </li>
                    @endforeach
                </ul>
            </li>
        @else
            <li @class(['active' => $isActive])>
                <a href="{{ route($item['route']) }}">
                    <i class="zmdi {{ $item['icon'] }}"></i>
                    <span>{{ $item['label'] }}</span>
                </a>
            </li>
        @endif
    @endforeach
</ul>
