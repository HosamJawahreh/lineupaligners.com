@php
    $plans = $plans ?? collect();
    $navKey = $navKey ?? 'full';
    $defaultVersion = (int) ($plans->max('version') ?? $plans->last()?->version ?? 1);
    $hasMultiple = $plans->count() > 1;
@endphp

@if($hasMultiple)
<nav class="mfg-plan__stage-nav mfg-plan__stage-nav--versions" aria-label="Treatment plan versions" data-mfg-version-nav="{{ $navKey }}">
    <span class="mfg-plan__stage-nav-label">View version</span>
    <div class="mfg-plan__stage-nav-buttons" role="tablist">
        @foreach($plans as $plan)
        <button type="button"
                role="tab"
                class="mfg-plan__stage-btn @if($plan->version === $defaultVersion) is-active @endif"
                data-mfg-version-btn
                data-version="{{ $plan->version }}"
                aria-selected="{{ $plan->version === $defaultVersion ? 'true' : 'false' }}"
                aria-controls="mfg-version-panel-{{ $navKey }}-{{ $plan->version }}"
                id="mfg-version-tab-{{ $navKey }}-{{ $plan->version }}">
            <span class="mfg-plan__stage-btn-num">
                Version {{ $plan->version }}
                @if($plan->is_current)
                <span class="mfg-plan__version-current-tag">· Current</span>
                @endif
            </span>
            <span class="mfg-plan__stage-btn-status mfg-plan__status mfg-plan__status--{{ $plan->review_status }}">{{ $plan->reviewStatusLabel() }}</span>
        </button>
        @endforeach
    </div>
    <span class="mfg-plan__stage-nav-hint">{{ $plans->count() }} version{{ $plans->count() === 1 ? '' : 's' }}</span>
</nav>
@endif

<div class="@if($hasMultiple) mfg-plan__version-panels @else mfg-plan__stage-stack @endif"
     @if($hasMultiple) data-mfg-version-panels="{{ $navKey }}" @endif>
    @foreach($plans as $plan)
        @php
            $cardTitle = $titleResolver ?? null;
            if (is_callable($cardTitle)) {
                $cardTitle = $cardTitle($plan);
            } elseif (! is_string($cardTitle)) {
                $cardTitle = $plan->version > 1
                    ? 'Treatment plan · Version '.$plan->version
                    : ($plan->stage_number ? $plan->stageLabel() : 'Treatment case plan');
            }
        @endphp
        @if($hasMultiple)
        <div class="mfg-plan__version-panel @if($plan->version === $defaultVersion) is-active @endif"
             id="mfg-version-panel-{{ $navKey }}-{{ $plan->version }}"
             role="tabpanel"
             aria-labelledby="mfg-version-tab-{{ $navKey }}-{{ $plan->version }}"
             data-mfg-version-panel="{{ $plan->version }}"
             data-version-nav="{{ $navKey }}"
             @if($plan->version !== $defaultVersion) hidden @endif>
        @endif
            @include('theme.pages.partials.case-manufacture-plan-card', [
                'plan' => $plan,
                'patient' => $patient,
                'title' => $cardTitle,
                'canReview' => $canReview ?? false,
                'canUpload' => $canUpload ?? false,
                'canMarkManufactured' => $canMarkManufactured ?? false,
                'inStagePicker' => $inStagePicker ?? false,
                'isHistorical' => ! $plan->is_current,
            ])
        @if($hasMultiple)
        </div>
        @endif
    @endforeach
</div>
