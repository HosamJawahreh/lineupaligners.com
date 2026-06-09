@php
    use App\Support\PhpUploadLimits;

    $refinementsEnabled = $refinementsEnabled ?? Schema::hasTable('patient_case_refinements');
    $activeRefinement = $activeRefinement ?? ($refinementsEnabled ? $patient->currentRefinement() : null);
    $canRequest = ($canRequestRefinement ?? false) && $patient->canRequestRefinement();
    $uploadLimitsOk = $scanUploadLimitsOk ?? PhpUploadLimits::isAdequateForScans();
    $uploadLimitsLabel = $scanUploadLimitsLabel ?? PhpUploadLimits::humanSummary();
@endphp

<div class="case-refinement" id="case-order-refinement">
    <header class="case-refinement__head">
        <h3 class="case-refinement__title">Order refinement</h3>
        <p class="case-refinement__subtitle">
            For patients returning after manufacture. Upload scans, photos, and notes here — view them under <strong>3D Scans &amp; Photos</strong> and the refinement plan under <strong>Treatment Plan</strong>.
        </p>
    </header>

    @if(session('success'))
    <div class="case-refinement__notice case-refinement__notice--success" role="status">
        <i class="zmdi zmdi-check-circle" aria-hidden="true"></i>
        <p>{{ session('success') }}</p>
    </div>
    @endif

    @if(session('error'))
    <div class="case-refinement__notice case-refinement__notice--error" role="alert">
        <i class="zmdi zmdi-alert-circle" aria-hidden="true"></i>
        <p>{{ session('error') }}</p>
    </div>
    @endif

    @if($errors->any())
    <div class="case-refinement__notice case-refinement__notice--error" role="alert">
        <i class="zmdi zmdi-alert-circle" aria-hidden="true"></i>
        <p>{{ $errors->first('notes') ?: $errors->first() }}</p>
    </div>
    @endif

    <div class="case-refinement__layout">
        <div class="case-refinement__main">
            @if(! $refinementsEnabled)
            <div class="case-refinement__notice case-refinement__notice--error" role="alert">
                <i class="zmdi zmdi-alert-circle" aria-hidden="true"></i>
                <p>Refinement storage is not installed. An administrator must run <code>php artisan migrate</code> on the server.</p>
            </div>
            @elseif($activeRefinement)
            <div class="case-refinement__notice case-refinement__notice--pending" role="status">
                <i class="zmdi zmdi-time" aria-hidden="true"></i>
                <div>
                    <p><strong>Refinement #{{ $activeRefinement->version }} in progress</strong> (started {{ $activeRefinement->created_at?->format('M j, Y g:i A') }}).</p>
                    <p>LineUp will upload new treatment plan(s) in the <strong>Treatment Plan</strong> tab. After you approve them, this refinement cycle ends. Previous scans and history stay in 3D Scans &amp; Photos and Full Case History.</p>
                    @if($activeRefinement->hasScans())
                    <p class="case-refinement__hint-inline">Scans attached: {{ $activeRefinement->upper_jaw_scan ? 'Upper' : '' }}{{ $activeRefinement->upper_jaw_scan && $activeRefinement->lower_jaw_scan ? ' · ' : '' }}{{ $activeRefinement->lower_jaw_scan ? 'Lower' : '' }}</p>
                    @endif
                </div>
            </div>
            @elseif($canRequest)
                @if(! $uploadLimitsOk)
                <div class="case-refinement__notice case-refinement__notice--info" role="status">
                    <i class="zmdi zmdi-info-outline" aria-hidden="true"></i>
                    <div>
                        <p>
                            <strong>Large file uploads need a higher server limit</strong> ({{ $uploadLimitsLabel }}).
                            You can still start refinement without attachments; add scans or photos later if needed.
                        </p>
                        <p class="case-refinement__hint-inline">
                            For local dev, restart with <code>php artisan serve</code> or <code>bash serve.sh</code> (128M limits).
                        </p>
                    </div>
                </div>
                @endif

                <section class="case-modification-card case-modification-card--refinement"
                         aria-labelledby="case-refinement-form-title">
                    <div class="case-modification-card__accent" aria-hidden="true"></div>

                    <header class="case-modification-card__head">
                        <span class="case-modification-card__icon" aria-hidden="true">
                            <i class="zmdi zmdi-swap-vertical"></i>
                        </span>
                        <div class="case-modification-card__head-text">
                            <p class="case-modification-card__kicker">Returning patient</p>
                            <h4 class="case-modification-card__title" id="case-refinement-form-title">Start refinement case</h4>
                            <p class="case-modification-card__lead">Notes are required. Upload updated 3D scans and photos if needed — LineUp will prepare a new treatment plan for this refinement cycle.</p>
                        </div>
                    </header>

                    <form method="post"
                          action="{{ route('patients.refinements.store', $patient) }}"
                          class="case-modification-card__form"
                          enctype="multipart/form-data"
                          data-scan-upload
                          id="case-refinement-form"
                          @if(! $uploadLimitsOk) data-upload-limits-low="1" @endif>
                        @csrf

                        <div class="case-modification-card__section">
                            <h5 class="case-modification-card__section-title">
                                <i class="zmdi zmdi-cloud-upload" aria-hidden="true"></i>
                                Scans &amp; photos
                            </h5>
                            <div class="case-modification-card__uploads case-modification-card__uploads--compact">
                                <div class="case-modification-card__upload-block case-modification-card__upload-block--photos">
                                    @include('theme.pages.partials.case-photos-upload', ['uploadId' => 'refinement-photos'])
                                    @error('photos')
                                    <span class="case-modification-card__field-error" role="alert">{{ $message }}</span>
                                    @enderror
                                    @error('photos.*')
                                    <span class="case-modification-card__field-error" role="alert">{{ $message }}</span>
                                    @enderror
                                </div>
                                @include('theme.pages.partials.case-jaw-scan-fields', [
                                    'upperInputId' => 'refinement-upper',
                                    'lowerInputId' => 'refinement-lower',
                                ])
                            </div>
                        </div>

                        <div class="case-modification-card__section">
                            <h5 class="case-modification-card__section-title">
                                <i class="zmdi zmdi-edit" aria-hidden="true"></i>
                                Refinement notes <span class="case-modification-card__required">required</span>
                            </h5>
                            <div class="case-modification-card__field">
                                <label for="refinement-notes">Clinical context for LineUp</label>
                                <textarea id="refinement-notes"
                                          name="notes"
                                          rows="5"
                                          maxlength="10000"
                                          required
                                          aria-required="true"
                                          placeholder="Why the patient is returning, what changed clinically, and what LineUp should plan for this refinement…">{{ old('notes') }}</textarea>
                                @error('notes')
                                <span class="case-modification-card__field-error" role="alert">{{ $message }}</span>
                                @enderror
                                <span class="case-modification-card__hint">Required — explain why the patient is returning and what LineUp should plan.</span>
                            </div>
                        </div>

                        <footer class="case-modification-card__foot">
                            <button type="submit" class="case-modification-card__submit" id="case-refinement-submit">
                                <i class="zmdi zmdi-upload" aria-hidden="true"></i>
                                Start refinement case
                            </button>
                        </footer>
                    </form>
                </section>
            @elseif(auth()->user()->isDoctor() && $patient->canRequestRefinement() && ! ($canRequestRefinement ?? false))
                <div class="case-refinement__notice case-refinement__notice--info">
                    <i class="zmdi zmdi-info-outline" aria-hidden="true"></i>
                    <p>
                        This case is ready for refinement, but your doctor role does not include the
                        <strong>Order refinement</strong> permission. Ask your clinic admin to enable it under
                        <strong>System Settings → Doctor Roles</strong>.
                    </p>
                </div>
            @elseif(auth()->user()->isDoctor())
                <div class="case-refinement__notice case-refinement__notice--info">
                    <i class="zmdi zmdi-info-outline" aria-hidden="true"></i>
                    <p>
                        @if($patient->hasActiveModificationForAny())
                            @if($patient->isDividedStages())
                                Refinement opens after the current modification is complete and LineUp marks manufacturing <strong>stage 1</strong> as complete.
                            @else
                                Refinement opens after the current modification is complete and LineUp marks the case as <strong>Manufactured</strong>.
                            @endif
                        @elseif($patient->isDividedStages() && $patient->manufacturingStageRecord(1) === null)
                            Refinement is available after LineUp marks manufacturing <strong>stage 1</strong> complete on the Treatment Plan tab.
                        @elseif(! $patient->hasCompletedManufacturing())
                            Refinement is available only after LineUp marks the case as <strong>Manufactured</strong> on the Treatment Plan tab.
                        @else
                            Refinement is available for this manufactured case when the patient returns for continued treatment.
                        @endif
                        Current workflow: <strong>{{ $patient->workflowStageLabel() }}</strong>.
                    </p>
                </div>
            @else
                @if(! auth()->user()->isDoctor() && $patient->canRequestRefinement())
                <div class="case-refinement__notice case-refinement__notice--info">
                    <i class="zmdi zmdi-account" aria-hidden="true"></i>
                    <p>
                        This case is ready for refinement.
                        @if($patient->doctor)
                        <strong>Dr. {{ $patient->doctor->fullName() }}</strong> can start a refinement from this tab when logged in as the assigned doctor.
                        @else
                        The assigned doctor can start a refinement from this tab.
                        @endif
                    </p>
                </div>
                @else
                <div class="case-refinement__notice case-refinement__notice--info">
                    <i class="zmdi zmdi-account" aria-hidden="true"></i>
                    <p>Only the assigned doctor can order a refinement. When active, LineUp admin uploads plans under Treatment Plan for this new cycle.</p>
                </div>
                @endif
            @endif
        </div>

        <aside class="case-refinement__aside" aria-label="Refinement history">
            @include('theme.pages.partials.case-cycle-timeline-panel', [
                'cycleTimeline' => $refinementTimeline ?? ['events' => [], 'grouped' => []],
                'panelTitle' => 'Refinement history',
                'panelSubtitle' => 'Ordered cycles, plan uploads, and doctor reviews.',
                'emptyTitle' => 'No refinements yet',
                'emptyMessage' => 'Ordered refinements and plan updates will appear here.',
                'timelineIdPrefix' => 'ref-history',
            ])
        </aside>
    </div>
</div>
