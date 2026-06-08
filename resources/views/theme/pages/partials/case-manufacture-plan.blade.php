@php
    $isDivided = $patient->isDividedStages();
    $contexts = $treatmentPlanContexts ?? $patient->treatmentPlanContextsForViewer();
    $defaultContextKey = $defaultTreatmentPlanContextKey ?? ($contexts[0]['key'] ?? 'original');
    $hasMultipleContexts = count($contexts) > 1;
@endphp

<div class="mfg-plan" id="case-manufacture-plan">
    <header class="mfg-plan__head">
        <div class="mfg-plan__head-inline" aria-label="Treatment plan controls">
            <div class="mfg-plan__head-start">
                <h3 class="mfg-plan__title @if(!$isDivided) mfg-plan__title--solo @endif">Treatment Plan</h3>
            </div>
            @if(count($contexts) > 0)
            @include('theme.pages.partials.case-treatment-plan-context-switcher', [
                'contexts' => $contexts,
                'defaultContextKey' => $defaultContextKey,
                'selectId' => 'mfg-plan-context-select',
            ])
            @endif
            <span class="mfg-plan__type-badge">{{ $patient->caseTypeLabel() }}</span>
        </div>
        @if($isDivided)
        <p class="mfg-plan__subtitle">
            Each stage covers a step range (from–to) with its own viewer link. Every stage follows a full approve / modification cycle, like a full case.
        </p>
        @endif
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
            'treatmentPlanContexts' => $contexts,
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
            Stages are reviewed in order. Approve or order a modification on the current stage only. Modification notes are required.
        @else
            Review the plan below. Ordering a modification requires notes for LineUp admin.
        @endif
    </p>
    @endif
</div>
