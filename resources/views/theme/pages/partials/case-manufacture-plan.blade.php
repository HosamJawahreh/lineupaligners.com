@php
    $isDivided = $patient->isDividedStages();
    $contexts = $treatmentPlanContexts ?? $patient->treatmentPlanContextsForViewer();
    $defaultContextKey = $defaultTreatmentPlanContextKey ?? ($contexts[0]['key'] ?? 'original');
    $hasMultipleContexts = count($contexts) > 1;
@endphp

<div class="mfg-plan" id="case-manufacture-plan">
    <header class="mfg-plan__head mfg-plan__head--with-switcher">
        <div class="mfg-plan__head-text">
            <h3 class="mfg-plan__title @if(!$isDivided) mfg-plan__title--solo @endif">Treatment Plan</h3>
            @if($isDivided)
            <p class="mfg-plan__subtitle">
                Each stage covers a step range (from–to) with its own viewer link. Every stage follows a full approve / reject cycle, like a full case.
            </p>
            @else
            <p class="mfg-plan__subtitle">Switch between original, modification, and refinement plans. The latest action is shown by default.</p>
            @endif
        </div>
        @if(count($contexts) > 0)
        <div class="case-scan-set-switcher case-plan-context-switcher mfg-plan__context-switcher" role="group" aria-labelledby="mfg-plan-context-label">
            <label id="mfg-plan-context-label" for="mfg-plan-context-select" class="case-scan-set-switcher__label">
                <i class="zmdi zmdi-layers" aria-hidden="true"></i>
                <span>Plan set</span>
            </label>
            <div class="case-scan-set-switcher__control">
                <select id="mfg-plan-context-select"
                        class="case-scan-set-switcher__select"
                        data-mfg-context-select
                        @if(! $hasMultipleContexts) disabled @endif
                        aria-describedby="mfg-plan-context-label">
                    @foreach($contexts as $ctx)
                    <option value="{{ $ctx['key'] }}" @selected($ctx['key'] === $defaultContextKey)>{{ $ctx['label'] }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        @endif
        <span class="mfg-plan__type-badge">{{ $patient->caseTypeLabel() }}</span>
    </header>

    @if($contexts === [])
    <div class="mfg-plan__empty">
        <i class="zmdi zmdi-assignment" aria-hidden="true"></i>
        <p>No treatment plan has been uploaded for this case.</p>
        @if(($canAdminUploadFullPlan ?? false) && ($canUploadTreatmentPlan ?? false))
        <span class="mfg-plan__empty-hint">Paste the canvas link from the LineUp viewer below.</span>
        @endif
    </div>
    @else
    <div class="mfg-plan__context-panels" data-mfg-context-panels>
        @foreach($contexts as $ctx)
        @include('theme.pages.partials.case-manufacture-plan-context', [
            'patient' => $patient,
            'context' => $ctx,
            'defaultTreatmentPlanContextKey' => $defaultContextKey,
            'canUploadTreatmentPlan' => $canUploadTreatmentPlan ?? false,
            'canReviewTreatmentPlan' => $canReviewTreatmentPlan ?? false,
            'canMarkManufactured' => $canMarkManufactured ?? false,
            'canAdminUploadFullPlan' => $canAdminUploadFullPlan ?? false,
        ])
        @endforeach
    </div>
    @endif

    @if(($canReviewTreatmentPlan ?? false) && !($canUploadTreatmentPlan ?? false))
    <p class="mfg-plan__role-note"><i class="zmdi zmdi-info-outline"></i>
        @if($isDivided)
            Stages are reviewed in order. Approve or reject the current stage only. Rejection requires a comment for LineUp admin.
        @else
            Review the plan below. Rejection requires a comment for LineUp admin.
        @endif
    </p>
    @endif
</div>
