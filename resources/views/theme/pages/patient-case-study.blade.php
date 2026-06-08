@extends('layouts.app')

@section('title', $patient->display_patient_id.' — Case Study')

@section('body-class', 'patient-case-study-page')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/patient-case-study.css') }}?v=56">
@endpush

@section('content')
<section class="content case-study-page">
    <div class="container-fluid case-study-wrap">
        {{-- Workflow progress --}}
        @php
            $workflowPercent = $patient->workflowProgressPercent();
            $workflowIcons = [
                'created' => 'zmdi-folder',
                'waiting_plan' => 'zmdi-assignment',
                'case_status' => 'zmdi-account-circle',
                'modification' => 'zmdi-refresh-sync',
                'approved' => 'zmdi-check-circle',
                'refinement' => 'zmdi-redo',
            ];
        @endphp
        <nav class="case-workflow case-workflow--premium"
             aria-label="Case workflow progress"
             style="--workflow-progress: {{ $workflowPercent }}%;">
            <div class="case-workflow__shell">
                <div class="case-workflow__rail" aria-hidden="true">
                    <div class="case-workflow__rail-track"></div>
                    <div class="case-workflow__rail-fill"></div>
                    <div class="case-workflow__rail-glow"></div>
                </div>
                <ol class="case-workflow-track">
                    @foreach($workflowSteps as $step)
                    <li class="case-workflow-step case-workflow-step--{{ $step['state'] }} case-workflow-step--tone-{{ $step['tone'] }}{{ !empty($step['variant']) ? ' case-workflow-step--'.$step['variant'] : '' }}"
                        data-workflow-key="{{ $step['key'] }}"
                        data-tone="{{ $step['tone'] }}"
                        aria-current="{{ $step['state'] === 'current' ? 'step' : 'false' }}">
                        <span class="case-workflow-node" aria-hidden="true">
                            <span class="case-workflow-node__ring"></span>
                            <span class="case-workflow-node__core">
                                @if($step['state'] === 'completed')
                                <i class="zmdi zmdi-check" aria-hidden="true"></i>
                                @elseif($step['state'] === 'rejected')
                                <i class="zmdi zmdi-close" aria-hidden="true"></i>
                                @elseif(($step['variant'] ?? '') === 'no-mod')
                                <i class="zmdi zmdi-block-alt" aria-hidden="true"></i>
                                @elseif($step['state'] === 'skipped')
                                <i class="zmdi zmdi-minus" aria-hidden="true"></i>
                                @else
                                <i class="zmdi {{ $workflowIcons[$step['key']] ?? 'zmdi-dot-circle' }}" aria-hidden="true"></i>
                                @endif
                            </span>
                        </span>
                        <span class="case-workflow-label">{{ $step['label'] }}</span>
                    </li>
                    @endforeach
                </ol>
                @php
                    $currentWorkflowStep = collect($workflowSteps)->firstWhere('state', 'current');
                @endphp
                @if($currentWorkflowStep)
                <p class="case-workflow__status-text case-workflow__status-text--tone-{{ $currentWorkflowStep['tone'] }}" aria-live="polite">{{ $currentWorkflowStep['label'] }}</p>
                @endif
            </div>
        </nav>

        {{-- Case summary — formal dossier for doctors & admins --}}
        <section class="case-summary-card case-summary-card--dossier" aria-label="Case summary" data-case-summary-dossier>
            <div class="case-summary-card__glow" aria-hidden="true"></div>
            <div class="case-summary-card__accent" aria-hidden="true"></div>
            <div class="case-summary-card__inner">
                <button type="button"
                        class="case-summary-card__mobile-toggle"
                        id="case-summary-dossier-toggle"
                        aria-expanded="false"
                        aria-controls="case-summary-dossier-panel">
                    <span class="case-summary-card__mobile-toggle-text">
                        <span class="case-summary-card__mobile-toggle-kicker">Case record</span>
                        <span class="case-summary-card__mobile-toggle-name">{{ $patient->fullName() }}</span>
                    </span>
                    <i class="zmdi zmdi-chevron-down case-summary-card__mobile-toggle-icon" aria-hidden="true"></i>
                </button>
                <div class="case-summary-card__expandable" id="case-summary-dossier-panel" hidden>
                <header class="case-summary-card__header">
                    <p class="case-summary-card__kicker">Case record</p>
                    <div class="case-summary-card__id-block">
                        <span class="case-summary-card__label">Case number</span>
                        <span class="case-summary-card__case-id">{{ $patient->display_patient_id }}</span>
                    </div>
                    <div class="case-summary-card__status-block">
                        <span class="case-summary-card__label">Workflow status</span>
                        <span class="case-summary-card__workflow case-summary-card__workflow--{{ $patient->workflowStageKey() }}">
                            <span class="case-summary-card__workflow-dot" aria-hidden="true"></span>
                            {{ $patient->workflowStageLabel() }}
                        </span>
                    </div>
                    <div class="case-summary-card__actions">
                        @if($canSendCaseUpdate ?? filled($patient->email))
                        <form method="post"
                              action="{{ route('patients.send-last-update', $patient) }}"
                              class="case-summary-card__action-form"
                              data-confirm-text="Send the latest case action to {{ $patient->fullName() }}@if(filled($patient->email)) at {{ $patient->email }}@endif?">
                            @csrf
                            <button type="submit"
                                    class="case-summary-card__btn case-summary-card__btn--edit case-summary-card__btn--send-update"
                                    title="Email the patient about the latest case action">
                                <i class="zmdi zmdi-email" aria-hidden="true"></i>
                                <span>Send The Last Case Updates To Patient Email</span>
                            </button>
                        </form>
                        @endif
                        <a href="{{ route('patients.edit', $patient) }}" class="case-summary-card__btn case-summary-card__btn--edit">
                            <i class="zmdi zmdi-edit"></i>
                            <span>Edit case</span>
                        </a>
                    </div>
                </header>
                <div class="case-summary-card__body">
                    <div class="case-summary-card__grid">
                        <div class="case-summary-cell">
                            <span class="case-summary-cell__label">Patient</span>
                            <span class="case-summary-cell__value">{{ $patient->fullName() }}</span>
                        </div>
                        @if(($patientAge = $patient->patientAge()) !== null)
                        <div class="case-summary-cell">
                            <span class="case-summary-cell__label">Age</span>
                            <span class="case-summary-cell__value">{{ $patientAge }} years</span>
                        </div>
                        @endif
                        <div class="case-summary-cell">
                            <span class="case-summary-cell__label">Case type</span>
                            <span class="case-summary-cell__value">{{ $patient->caseTypeLabel() }}</span>
                        </div>
                        @if($patient->doctor)
                        <div class="case-summary-cell">
                            <span class="case-summary-cell__label">Doctor</span>
                            <span class="case-summary-cell__value">Dr. {{ $patient->doctor->fullName() }}</span>
                        </div>
                        @endif
                    </div>
                    <div class="case-summary-card__contact-row">
                        <div class="case-summary-cell case-summary-cell--compact">
                            <span class="case-summary-cell__label">Phone</span>
                            <span class="case-summary-cell__value">
                                @if($patient->phone)
                                <a href="tel:{{ preg_replace('/\s+/', '', $patient->phone) }}" class="case-summary-cell__link">{{ $patient->phone }}</a>
                                @else
                                <span class="case-summary-cell__empty">—</span>
                                @endif
                            </span>
                        </div>
                        <div class="case-summary-cell case-summary-cell--compact">
                            <span class="case-summary-cell__label">Email</span>
                            <span class="case-summary-cell__value">
                                @if($patient->email)
                                <a href="mailto:{{ $patient->email }}" class="case-summary-cell__link">{{ $patient->email }}</a>
                                @else
                                <span class="case-summary-cell__empty">—</span>
                                @endif
                            </span>
                        </div>
                        <div class="case-summary-cell case-summary-cell--notes">
                            <span class="case-summary-cell__label">Notes</span>
                            @if($patient->notes)
                            <div class="case-summary-notes" data-case-summary-notes>
                                <div class="case-summary-notes__content">{{ $patient->notes }}</div>
                                <button type="button"
                                        class="case-summary-notes__toggle"
                                        hidden
                                        aria-expanded="false">
                                    Show full notes
                                </button>
                            </div>
                            @else
                            <span class="case-summary-cell__empty">—</span>
                            @endif
                        </div>
                    </div>
                </div>
                </div>
            </div>
        </section>

        {{-- Action tabs --}}
        <div class="case-study-tabs" role="tablist" aria-label="Case study sections">
            @foreach($studyTabs as $index => $tab)
            <button type="button"
                    class="case-study-tab case-study-tab--{{ $tab['tone'] }} @if($index === 0) is-active @endif"
                    role="tab"
                    aria-selected="{{ $index === 0 ? 'true' : 'false' }}"
                    aria-controls="case-panel-{{ $tab['id'] }}"
                    id="case-tab-{{ $tab['id'] }}"
                    data-tab="{{ $tab['id'] }}">
                <i class="zmdi {{ $tab['icon'] }}"></i>
                <span>{{ $tab['label'] }}</span>
            </button>
            @endforeach
        </div>

        {{-- Tab panels --}}
        <div class="case-study-panels">
            @foreach($studyTabs as $index => $tab)
            <div class="case-study-panel @if($tab['id'] === 'messages') case-study-panel--messages @endif @if($index === 0) is-active @endif"
                 id="case-panel-{{ $tab['id'] }}"
                 role="tabpanel"
                 aria-labelledby="case-tab-{{ $tab['id'] }}"
                 @if($index !== 0) hidden @endif>
                @if($tab['id'] === 'view-data')
                    @include('theme.pages.partials.case-scan-viewer', [
                        'patient' => $patient,
                        'scanFiles' => $caseScanFiles ?? [],
                        'caseScanSets' => $caseScanSets ?? [],
                        'defaultScanSetKey' => $defaultScanSetKey ?? 'original',
                        'casePhotosBySet' => $casePhotosBySet ?? [],
                    ])
                @elseif($tab['id'] === 'manufacture-plan')
                    @include('theme.pages.partials.case-manufacture-plan', [
                        'canMarkManufactured' => $canMarkManufactured ?? false,
                        'patient' => $patient,
                        'canUploadTreatmentPlan' => $canUploadTreatmentPlan ?? false,
                        'canReviewTreatmentPlan' => $canReviewTreatmentPlan ?? false,
                        'canAdminUploadFullPlan' => $canAdminUploadFullPlan ?? false,
                        'treatmentPlanContexts' => $treatmentPlanContexts ?? [],
                        'defaultTreatmentPlanContextKey' => $defaultTreatmentPlanContextKey ?? 'original',
                    ])
                @elseif($tab['id'] === 'modification')
                    @include('theme.pages.partials.case-modification-request', [
                        'patient' => $patient,
                        'canRequestModification' => $canRequestModification ?? false,
                        'modificationRecords' => $modificationRecords ?? collect(),
                    ])
                @elseif($tab['id'] === 'order-refinement')
                    @include('theme.pages.partials.case-order-refinement', [
                        'patient' => $patient,
                        'canRequestRefinement' => $canRequestRefinement ?? false,
                        'refinementsEnabled' => $refinementsEnabled ?? true,
                        'activeRefinement' => $activeRefinement ?? null,
                        'refinementRecords' => $refinementRecords ?? collect(),
                        'scanUploadLimitsOk' => $scanUploadLimitsOk ?? true,
                        'scanUploadLimitsLabel' => $scanUploadLimitsLabel ?? '',
                    ])
                @elseif($tab['id'] === 'modification-history')
                    @include('theme.pages.partials.case-modification-history', [
                        'caseTimeline' => $caseTimeline ?? ['events' => [], 'grouped' => []],
                    ])
                @elseif($tab['id'] === 'messages')
                    @include('theme.pages.partials.case-chat-panel', [
                        'patient' => $patient,
                        'canCaseChat' => $canCaseChat,
                        'chatDoctorName' => $chatDoctorName,
                        'chatCounterparty' => $chatCounterparty,
                        'chatParticipants' => $chatParticipants,
                        'logoUrl' => $logoUrl,
                        'latestSeenOwnMessageId' => $latestSeenOwnMessageId ?? 0,
                    ])
                @else
                    <div class="case-panel-placeholder">
                        <i class="zmdi {{ $tab['icon'] }}"></i>
                        <h3>{{ $tab['label'] }}</h3>
                        <p>This section will be connected in the next phase. Use the Live Chat &amp; Files tab to contact the other party on this case.</p>
                    </div>
                @endif
            </div>
            @endforeach
        </div>
    </div>
</section>
@stack('case-photos-gallery')
@endsection

@push('scripts')
@if(collect($caseScanSets ?? [])->contains(fn ($s) => count($s['files'] ?? []) > 0))
<script type="importmap">
{
    "imports": {
        "three": "https://cdn.jsdelivr.net/npm/three@0.160.0/build/three.module.js",
        "three/addons/": "https://cdn.jsdelivr.net/npm/three@0.160.0/examples/jsm/"
    }
}
</script>
<script type="module" src="{{ asset('assets/js/case-scan-viewer.js') }}?v=17"></script>
@endif
<script src="{{ asset('assets/js/patient-case-study.js') }}?v=4"></script>
<script src="{{ asset('assets/js/case-action-confirm.js') }}?v=6"></script>
<script src="{{ asset('assets/js/case-manufacture-plan.js') }}?v=7"></script>
<script src="{{ asset('assets/js/case-photos-upload.js') }}?v=1"></script>
@if(!empty($caseScanSets))
<script>window.caseScanSetsMeta = @json($caseScanSets);</script>
@endif
@if(!empty($casePhotosBySet) && collect($casePhotosBySet)->flatten(1)->isNotEmpty())
<script src="{{ asset('assets/js/case-photos-gallery.js') }}?v=2"></script>
@endif
@if(session('open_tab'))
<script>window.CASE_STUDY_OPEN_TAB = @json(session('open_tab'));</script>
@endif
@if(session('mfg_active_stage'))
<script>window.CASE_STUDY_MFG_STAGE = @json((int) session('mfg_active_stage'));</script>
@endif
@endpush
