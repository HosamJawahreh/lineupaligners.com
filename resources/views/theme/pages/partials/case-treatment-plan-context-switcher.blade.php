@php
    $contexts = $contexts ?? [];
    $defaultContextKey = $defaultContextKey ?? ($contexts[0]['key'] ?? 'original');
    $hasMultipleContexts = count($contexts) > 1;
    $selectId = $selectId ?? 'mfg-plan-context-select';
@endphp

@if(count($contexts) > 0)
<div class="case-scan-set-switcher case-plan-context-switcher mfg-plan__context-switcher mfg-plan__context-switcher--in-card"
     role="group"
     aria-labelledby="{{ $selectId }}-label">
    <label id="{{ $selectId }}-label" for="{{ $selectId }}" class="case-scan-set-switcher__label">
        <i class="zmdi zmdi-layers" aria-hidden="true"></i>
        <span>Plan set</span>
    </label>
    <div class="case-scan-set-switcher__control">
        <select id="{{ $selectId }}"
                class="case-scan-set-switcher__select"
                data-mfg-context-select
                @if(! $hasMultipleContexts) disabled @endif
                aria-describedby="{{ $selectId }}-label">
            @foreach($contexts as $ctx)
            <option value="{{ $ctx['key'] }}" @selected($ctx['key'] === $defaultContextKey)>{{ $ctx['label'] }}</option>
            @endforeach
        </select>
    </div>
</div>
@endif
