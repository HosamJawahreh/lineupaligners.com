@php
    $user = auth()->user();
    $menuItems = config('menu-lineup.'.$user->role, []);
@endphp
<ul class="lineup-nav">
    @foreach($menuItems as $item)
        @php
            $activePattern = $item['active'] ?? ($item['route'] ?? '');
            $isActive = $activePattern ? request()->routeIs($activePattern) : false;
        @endphp
        <li @class(['active' => $isActive])>
            <a href="{{ route($item['route']) }}" title="{{ $item['label'] }}">
                <i class="zmdi {{ $item['icon'] }}"></i>
                <span>{{ $item['label'] }}</span>
                @if(!empty($item['badge']))
                <span class="lineup-nav__badge" id="{{ $item['route'] === 'notifications.index' ? 'lineup-sidebar-notify-badge' : '' }}" hidden>0</span>
                @endif
            </a>
        </li>
    @endforeach
</ul>
