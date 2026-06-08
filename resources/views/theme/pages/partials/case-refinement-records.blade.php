@php
    $records = $refinementRecords ?? collect();
    $defaultVersion = (int) ($records->max('version') ?? $records->last()?->version ?? 1);
    $hasMultiple = $records->count() > 1;
    $navKey = $navKey ?? 'refinements';
@endphp

@if($records->isEmpty())
<div class="case-modification__history-empty">
    <i class="zmdi zmdi-time-restore" aria-hidden="true"></i>
    <p>No refinement cycles yet. Ordered refinements and their status will appear here.</p>
</div>
@else
<section class="case-cycle-records case-cycle-records--refinement" aria-label="Refinement history">
    @if($hasMultiple)
    <nav class="mfg-plan__stage-nav mfg-plan__stage-nav--versions case-cycle-records__nav"
         aria-label="Refinement versions"
         data-cycle-version-nav="{{ $navKey }}">
        <span class="mfg-plan__stage-nav-label">Refinement</span>
        <div class="mfg-plan__stage-nav-buttons" role="tablist">
            @foreach($records as $ref)
            @php $refPlan = $ref->treatmentPlans->firstWhere('is_current', true) ?? $ref->treatmentPlans->sortByDesc('version')->first(); @endphp
            <button type="button"
                    role="tab"
                    class="mfg-plan__stage-btn @if((int) $ref->version === $defaultVersion) is-active @endif"
                    data-cycle-version-btn
                    data-version="{{ $ref->version }}"
                    aria-selected="{{ (int) $ref->version === $defaultVersion ? 'true' : 'false' }}"
                    aria-controls="cycle-panel-{{ $navKey }}-{{ $ref->version }}"
                    id="cycle-tab-{{ $navKey }}-{{ $ref->version }}">
                <span class="mfg-plan__stage-btn-num">
                    #{{ $ref->version }}
                    @if($ref->is_current)
                    <span class="mfg-plan__version-current-tag">· Active</span>
                    @endif
                </span>
                <span class="mfg-plan__stage-btn-status mfg-plan__status mfg-plan__status--{{ $refPlan?->review_status ?? 'pending' }}">{{ $ref->statusLabel() }}</span>
            </button>
            @endforeach
        </div>
        <span class="mfg-plan__stage-nav-hint">{{ $records->count() }} cycle{{ $records->count() === 1 ? '' : 's' }}</span>
    </nav>
    @endif

    <div class="@if($hasMultiple) case-cycle-records__panels @endif" @if($hasMultiple) data-cycle-version-panels="{{ $navKey }}" @endif>
        @foreach($records as $ref)
        @php $refPlan = $ref->treatmentPlans->firstWhere('is_current', true) ?? $ref->treatmentPlans->sortByDesc('version')->first(); @endphp
        <article class="case-cycle-record @if($hasMultiple) case-cycle-records__panel @if((int) $ref->version === $defaultVersion) is-active @endif @endif"
                 @if($hasMultiple)
                 id="cycle-panel-{{ $navKey }}-{{ $ref->version }}"
                 role="tabpanel"
                 aria-labelledby="cycle-tab-{{ $navKey }}-{{ $ref->version }}"
                 data-cycle-version-panel="{{ $ref->version }}"
                 data-cycle-version-nav="{{ $navKey }}"
                 @if((int) $ref->version !== $defaultVersion) hidden @endif
                 @endif>
            <header class="case-cycle-record__head">
                <div>
                    <h4 class="case-cycle-record__title">{{ $ref->scopeLabel() }}</h4>
                    <p class="case-cycle-record__meta">
                        Ordered {{ $ref->created_at?->format('M j, Y g:i A') ?? '—' }}
                        @if($ref->requester)
                        · {{ $ref->requester->displayName() }}
                        @endif
                    </p>
                </div>
                <span class="case-cycle-record__status case-cycle-record__status--{{ $ref->is_current ? 'active' : 'done' }}">{{ $ref->statusLabel() }}</span>
            </header>

            @if(filled($ref->notes))
            <div class="case-cycle-record__block">
                <h5 class="case-cycle-record__label">Notes</h5>
                <p class="case-cycle-record__text">{{ $ref->notes }}</p>
            </div>
            @endif

            @php $scanFiles = $ref->caseScanFiles(); @endphp
            @if(count($scanFiles) > 0)
            <div class="case-cycle-record__block">
                <h5 class="case-cycle-record__label">3D scans</h5>
                <ul class="case-cycle-record__files">
                    @foreach($scanFiles as $file)
                    <li>
                        <a href="{{ $file['download_url'] }}" class="case-cycle-record__file" download>
                            <i class="zmdi zmdi-file" aria-hidden="true"></i>
                            <span>{{ $file['label'] }} · {{ $file['name'] }}</span>
                        </a>
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif

            @if($ref->photos->isNotEmpty())
            <div class="case-cycle-record__block">
                <h5 class="case-cycle-record__label">Photos ({{ $ref->photos->count() }})</h5>
                <div class="case-cycle-record__photos">
                    @foreach($ref->photos as $photo)
                    <a href="{{ route('patients.photos.download', [$patient, $photo]) }}"
                       class="case-cycle-record__photo"
                       download
                       title="{{ $photo->downloadFilename() }}">
                        <img src="{{ $photo->url() }}" alt="" loading="lazy">
                    </a>
                    @endforeach
                </div>
            </div>
            @endif

            @if($refPlan)
            <div class="case-cycle-record__block">
                <h5 class="case-cycle-record__label">Treatment plan · {{ $refPlan->reviewStatusLabel() }}</h5>
                <p class="case-cycle-record__hint">This refinement cycle plan is also on the <strong>Treatment Plan</strong> tab while active.</p>
                <div class="case-cycle-record__canvas-wrap">
                    <iframe src="{{ $refPlan->plan_url }}"
                            title="Refinement #{{ $ref->version }} treatment plan"
                            class="case-cycle-record__canvas"
                            loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade"
                            allowfullscreen></iframe>
                </div>
            </div>
            @elseif($ref->is_current)
            <p class="case-cycle-record__pending"><i class="zmdi zmdi-time"></i> LineUp will upload the treatment plan on the Treatment Plan tab.</p>
            @endif
        </article>
        @endforeach
    </div>
</section>
@endif

