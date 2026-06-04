@extends('layouts.app')

@section('title', $patient->patient_id.' — Case Study')

@section('body-class', 'patient-case-study-page')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/patient-case-study.css') }}?v=9">
@endpush

@section('content')
<section class="content case-study-page">
    <div class="container-fluid case-study-wrap">
        {{-- Workflow progress --}}
        <nav class="case-workflow" aria-label="Case workflow progress">
            <ol class="case-workflow-track">
                @foreach($workflowSteps as $step)
                <li class="case-workflow-step case-workflow-step--{{ $step['state'] }}{{ !empty($step['variant']) ? ' case-workflow-step--'.$step['variant'] : '' }}" data-workflow-key="{{ $step['key'] }}">
                    <span class="case-workflow-node" aria-hidden="true"></span>
                    <span class="case-workflow-label">{{ $step['label'] }}</span>
                </li>
                @endforeach
            </ol>
            <div class="case-study-breadcrumb">
                <a href="{{ route('patients.index') }}">Patient</a>
                <span class="sep">/</span>
                <span>Patient Case Study</span>
            </div>
        </nav>

        {{-- Compact case summary (meta bar) --}}
        <div class="case-summary-card">
            <div class="case-meta-bar" role="list">
                <div class="case-meta-item" role="listitem">
                    <span class="case-meta-label">Case Number</span>
                    <span class="case-meta-value">{{ $patient->patient_id }}</span>
                </div>
                <div class="case-meta-item" role="listitem">
                    <span class="case-meta-label">Workflow</span>
                    <span class="case-meta-value">{{ $patient->workflowStageLabel() }}</span>
                </div>
                <div class="case-meta-item" role="listitem">
                    <span class="case-meta-label">Patient</span>
                    <span class="case-meta-value">{{ $patient->fullName() }}</span>
                </div>
                <div class="case-meta-item" role="listitem">
                    <span class="case-meta-label">Case Type</span>
                    <span class="case-meta-value">{{ $patient->caseTypeLabel() }}</span>
                </div>
                <div class="case-meta-item" role="listitem">
                    <span class="case-meta-label">Phone</span>
                    <span class="case-meta-value">{{ $patient->phone ?? '—' }}</span>
                </div>
                <div class="case-meta-item" role="listitem">
                    <span class="case-meta-label">Email</span>
                    <span class="case-meta-value">{{ $patient->email ?? '—' }}</span>
                </div>
                @if($patient->doctor)
                <div class="case-meta-item" role="listitem">
                    <span class="case-meta-label">Doctor</span>
                    <span class="case-meta-value">Dr. {{ $patient->doctor->fullName() }}</span>
                </div>
                @endif
                <div class="case-meta-item" role="listitem">
                    <span class="case-meta-label">Clinic</span>
                    <span class="case-meta-value">{{ $clinicName }}</span>
                </div>
                <div class="case-meta-item case-meta-item--notes" role="listitem">
                    <span class="case-meta-label">Notes</span>
                    <span class="case-meta-value" title="{{ $patient->notes }}">{{ $patient->notes ?: '—' }}</span>
                </div>
                <div class="case-meta-item case-meta-item--actions" role="listitem">
                    <span class="case-meta-label">Actions</span>
                    <div class="case-meta-actions">
                        @if($patient->email)
                        <a href="mailto:{{ $patient->email }}" class="case-meta-action case-meta-action--mail" title="Email patient">
                            <i class="zmdi zmdi-email"></i>
                        </a>
                        @endif
                        <a href="{{ route('patients.edit', $patient) }}" class="case-meta-action case-meta-action--edit">
                            <i class="zmdi zmdi-edit"></i> Edit
                        </a>
                    </div>
                </div>
            </div>
        </div>

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
                        'scanFiles' => $caseScanFiles ?? [],
                        'caseScanSets' => $caseScanSets ?? [],
                    ])
                @elseif($tab['id'] === 'manufacture-plan')
                    @include('theme.pages.partials.case-manufacture-plan', [
                        'canMarkManufactured' => $canMarkManufactured ?? false,
                        'patient' => $patient,
                        'canUploadTreatmentPlan' => $canUploadTreatmentPlan ?? false,
                        'canReviewTreatmentPlan' => $canReviewTreatmentPlan ?? false,
                        'fullTreatmentPlan' => $fullTreatmentPlan ?? null,
                        'visibleFullTreatmentPlans' => $visibleFullTreatmentPlans ?? collect(),
                        'canAdminUploadFullPlan' => $canAdminUploadFullPlan ?? false,
                        'stageTreatmentPlans' => $stageTreatmentPlans ?? collect(),
                        'treatmentPlanStageNumbers' => $treatmentPlanStageNumbers ?? collect(),
                    ])
                @elseif($tab['id'] === 'modification')
                    @include('theme.pages.partials.case-modification-request', [
                        'patient' => $patient,
                        'canRequestModification' => $canRequestModification ?? false,
                    ])
                @elseif($tab['id'] === 'order-refinement')
                    @include('theme.pages.partials.case-order-refinement', [
                        'patient' => $patient,
                        'canRequestRefinement' => $canRequestRefinement ?? false,
                        'refinementsEnabled' => $refinementsEnabled ?? true,
                        'activeRefinement' => $activeRefinement ?? null,
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
                        'chatParticipants' => $chatParticipants,
                        'logoUrl' => $logoUrl,
                        'latestSeenOwnMessageId' => $latestSeenOwnMessageId ?? 0,
                    ])
                @else
                    <div class="case-panel-placeholder">
                        <i class="zmdi {{ $tab['icon'] }}"></i>
                        <h3>{{ $tab['label'] }}</h3>
                        <p>This section will be connected in the next phase. Use the Messages tab to contact the other party on this case.</p>
                    </div>
                @endif
            </div>
            @endforeach
        </div>
    </div>
</section>
@endsection

@push('scripts')
@if(!empty($caseScanSets) || !empty($caseScanFiles))
<script type="importmap">
{
    "imports": {
        "three": "https://cdn.jsdelivr.net/npm/three@0.160.0/build/three.module.js",
        "three/addons/": "https://cdn.jsdelivr.net/npm/three@0.160.0/examples/jsm/"
    }
}
</script>
<script type="module" src="{{ asset('assets/js/case-scan-viewer.js') }}?v=3"></script>
@endif
<script src="{{ asset('assets/js/patient-case-study.js') }}"></script>
<script src="{{ asset('assets/js/case-manufacture-plan.js') }}"></script>
@if(session('open_tab'))
<script>window.CASE_STUDY_OPEN_TAB = @json(session('open_tab'));</script>
@endif
@if(session('mfg_active_stage'))
<script>window.CASE_STUDY_MFG_STAGE = @json((int) session('mfg_active_stage'));</script>
@endif
@endpush
