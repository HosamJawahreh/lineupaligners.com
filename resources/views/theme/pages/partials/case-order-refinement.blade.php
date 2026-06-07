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
            For patients returning after months or years to continue treatment. The original case data stays on file; LineUp runs a <strong>new manufacturing cycle</strong> from your updated 3D scans and notes — like a new case on the same patient record.
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
        <p>{{ $errors->first() }}</p>
    </div>
    @endif

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
            <p>LineUp will upload new treatment plan(s) in the <strong>Treatment Plan</strong> tab. After you approve them, this refinement cycle ends. Previous scans and history stay in 3D Scans &amp; Photos and Case History.</p>
            @if($activeRefinement->hasScans())
            <p class="case-refinement__hint-inline">Scans attached: {{ $activeRefinement->upper_jaw_scan ? 'Upper' : '' }}{{ $activeRefinement->upper_jaw_scan && $activeRefinement->lower_jaw_scan ? ' · ' : '' }}{{ $activeRefinement->lower_jaw_scan ? 'Lower' : '' }}</p>
            @endif
        </div>
    </div>
    @elseif($canRequest)
        @if(! $uploadLimitsOk)
        <div class="case-refinement__notice case-refinement__notice--error" role="alert">
            <i class="zmdi zmdi-alert-circle" aria-hidden="true"></i>
            <div>
                <p>
                    <strong>Server upload limit is too low</strong> ({{ $uploadLimitsLabel }}).
                    This case is ready for refinement, but large 3D files cannot upload until you restart the dev server.
                </p>
                <p class="case-refinement__hint-inline">
                    Stop the current server (Ctrl+C), then run:
                    <code>php artisan serve</code>
                    or <code>bash serve.sh</code>
                    — both now use 128M upload limits automatically.
                </p>
            </div>
        </div>
        @endif

        <section class="case-refinement-card @if(! $uploadLimitsOk) is-disabled @endif" aria-labelledby="case-refinement-form-title">
            <div class="case-refinement-card__accent" aria-hidden="true"></div>

            <header class="case-refinement-card__head">
                <span class="case-refinement-card__icon" aria-hidden="true">
                    <i class="zmdi zmdi-redo"></i>
                </span>
                <div class="case-refinement-card__head-text">
                    <p class="case-refinement-card__kicker">Returning patient</p>
                    <h4 class="case-refinement-card__title" id="case-refinement-form-title">Start a new refinement cycle</h4>
                    <p class="case-refinement-card__lead">Upload fresh 3D scans and clinical notes. LineUp will prepare new treatment plan(s) while keeping the original case history on file.</p>
                </div>
            </header>

            <form method="post"
                  action="{{ route('patients.refinements.store', $patient) }}"
                  class="case-refinement-card__form"
                  enctype="multipart/form-data"
                  data-scan-upload
                  id="case-refinement-form"
                  @if(! $uploadLimitsOk) data-upload-blocked="1" @endif>
                @csrf

                <div class="case-refinement-card__section">
                    <h5 class="case-refinement-card__section-title">
                        <i class="zmdi zmdi-cloud-upload" aria-hidden="true"></i>
                        Scans &amp; photos
                    </h5>
                    <div class="case-refinement-card__uploads">
                        <div class="case-refinement-card__upload-block case-refinement-card__upload-block--photos">
                            @include('theme.pages.partials.case-photos-upload', ['uploadId' => 'refinement-photos'])
                        </div>
                        <div class="case-refinement-card__upload-block">
                            <label for="refinement-upper">Upper jaw 3D file</label>
                            <div class="case-refinement-card__file-wrap">
                                <span class="case-refinement-card__file-icon" aria-hidden="true"><i class="zmdi zmdi-file"></i></span>
                                <input type="file" id="refinement-upper" name="upper_jaw_scan" accept=".stl,.obj,.ply" @disabled(! $uploadLimitsOk)>
                            </div>
                            <span class="case-refinement-card__hint">STL, OBJ, or PLY — upload at least one jaw.</span>
                        </div>
                        <div class="case-refinement-card__upload-block">
                            <label for="refinement-lower">Lower jaw 3D file</label>
                            <div class="case-refinement-card__file-wrap">
                                <span class="case-refinement-card__file-icon" aria-hidden="true"><i class="zmdi zmdi-file"></i></span>
                                <input type="file" id="refinement-lower" name="lower_jaw_scan" accept=".stl,.obj,.ply" @disabled(! $uploadLimitsOk)>
                            </div>
                            <span class="case-refinement-card__hint">Optional if upper jaw is provided.</span>
                        </div>
                    </div>
                </div>

                <div class="case-refinement-card__section">
                    <h5 class="case-refinement-card__section-title">
                        <i class="zmdi zmdi-edit" aria-hidden="true"></i>
                        Clinical notes
                    </h5>
                    <div class="case-refinement-card__field">
                        <label for="refinement-notes">Refinement notes</label>
                        <textarea id="refinement-notes"
                                  name="notes"
                                  rows="5"
                                  required
                                  minlength="10"
                                  maxlength="10000"
                                  placeholder="Why the patient is returning, what changed clinically, and what LineUp should plan for this refinement…"
                                  @disabled(! $uploadLimitsOk)>{{ old('notes') }}</textarea>
                        <span class="case-refinement-card__hint">Minimum 10 characters — include return reason and treatment goals for this cycle.</span>
                    </div>
                </div>

                <footer class="case-refinement-card__foot">
                    <button type="submit" class="case-refinement-card__submit" id="case-refinement-submit" @disabled(! $uploadLimitsOk)>
                        <i class="zmdi zmdi-upload" aria-hidden="true"></i>
                        Start refinement case
                    </button>
                </footer>
            </form>
        </section>
    @elseif(auth()->user()->isDoctor())
        <div class="case-refinement__notice case-refinement__notice--info">
            <i class="zmdi zmdi-info-outline" aria-hidden="true"></i>
            <p>
                Refinement is available after LineUp marks the case as <strong>Manufactured</strong> on the Treatment Plan tab.
                Current workflow: <strong>{{ $patient->workflowStageLabel() }}</strong>.
            </p>
        </div>
    @else
        <div class="case-refinement__notice case-refinement__notice--info">
            <i class="zmdi zmdi-account" aria-hidden="true"></i>
            <p>Only the assigned doctor can order a refinement. When active, LineUp admin uploads plans under Treatment Plan for this new cycle.</p>
        </div>
    @endif
</div>
