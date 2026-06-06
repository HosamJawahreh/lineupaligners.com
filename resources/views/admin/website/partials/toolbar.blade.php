@php
    $previewUrl = ($editLocale ?? 'en') === 'ar'
        ? url('/ar?preview=1')
        : route('website.home', ['preview' => 1]);
    $liveUrl = ($editLocale ?? 'en') === 'ar' ? url('/ar') : route('website.home');
    $localeSwitcherItems = collect($locales)->mapWithKeys(fn ($meta, $code) => [
        $code => array_merge($meta, [
            'url' => route('admin.website.index', ['locale' => $code, 'section' => request('section', 'general')]),
        ]),
    ])->all();
    $arabicCoverage = $arabicNavCoverage ?? ['total' => 0, 'translated' => 0, 'percent' => 100];
@endphp
<div class="wm-toolbar">
    <div class="wm-toolbar__left">
        <div class="wm-toolbar__status @if($content['published']) is-live @else is-draft @endif">
            <span class="wm-toolbar__dot"></span>
            {{ $content['published'] ? 'Live' : 'Draft' }}
        </div>
        <div class="wm-toolbar__readiness" title="Launch readiness">
            <span class="wm-toolbar__readiness-value">{{ $readiness['percent'] }}%</span>
            <span class="wm-toolbar__readiness-label">ready</span>
        </div>
        @if(array_key_exists('ar', $locales ?? []))
        <div class="wm-toolbar__readiness wm-toolbar__readiness--arabic" title="Arabic menu labels for visible nav links">
            <span class="wm-toolbar__readiness-value">{{ $arabicCoverage['percent'] }}%</span>
            <span class="wm-toolbar__readiness-label">AR nav</span>
        </div>
        @endif
    </div>
    <div class="wm-toolbar__center">
        @include('partials.locale-switcher-pill', [
            'items' => $localeSwitcherItems,
            'active' => $editLocale,
            'ariaLabel' => 'Editing language',
        ])
    </div>
    <div class="wm-toolbar__actions">
        <a href="{{ $previewUrl }}" target="_blank" rel="noopener" class="btn btn-default btn-sm btn-round">
            <i class="zmdi zmdi-eye"></i> Preview
        </a>
        <a href="{{ $liveUrl }}" target="_blank" rel="noopener" class="btn btn-default btn-sm btn-round @if(!$content['published']) disabled @endif">
            <i class="zmdi zmdi-open-in-new"></i> Open site
        </a>
    </div>
</div>
