@php
    $records = $modificationRecords ?? collect();
    $defaultVersion = (int) ($records->max('version') ?? $records->last()?->version ?? 1);
    $hasMultiple = $records->count() > 1;
    $navKey = $navKey ?? 'modifications';
@endphp

@if($records->isEmpty())
<div class="case-modification__history-empty">
    <i class="zmdi zmdi-time-restore" aria-hidden="true"></i>
    <p>No modification requests yet. Submitted requests and their status will appear here.</p>
</div>
@else
<section class="case-cycle-records case-cycle-records--modification" aria-label="Modification history">
    @if($hasMultiple)
    <nav class="mfg-plan__stage-nav mfg-plan__stage-nav--versions case-cycle-records__nav"
         aria-label="Modification versions"
         data-cycle-version-nav="{{ $navKey }}">
        <span class="mfg-plan__stage-nav-label">Modification</span>
        <div class="mfg-plan__stage-nav-buttons" role="tablist">
            @foreach($records as $mod)
            <button type="button"
                    role="tab"
                    class="mfg-plan__stage-btn @if((int) $mod->version === $defaultVersion) is-active @endif"
                    data-cycle-version-btn
                    data-version="{{ $mod->version }}"
                    aria-selected="{{ (int) $mod->version === $defaultVersion ? 'true' : 'false' }}"
                    aria-controls="cycle-panel-{{ $navKey }}-{{ $mod->version }}"
                    id="cycle-tab-{{ $navKey }}-{{ $mod->version }}">
                <span class="mfg-plan__stage-btn-num">
                    #{{ $mod->version }}
                    @if($mod->is_current)
                    <span class="mfg-plan__version-current-tag">· Active</span>
                    @endif
                </span>
                <span class="mfg-plan__stage-btn-status mfg-plan__status mfg-plan__status--{{ $mod->is_current ? 'pending' : 'approved' }}">{{ $mod->statusLabel() }}</span>
            </button>
            @endforeach
        </div>
        <span class="mfg-plan__stage-nav-hint">{{ $records->count() }} request{{ $records->count() === 1 ? '' : 's' }}</span>
    </nav>
    @endif

    <div class="@if($hasMultiple) case-cycle-records__panels @endif" @if($hasMultiple) data-cycle-version-panels="{{ $navKey }}" @endif>
        @foreach($records as $mod)
        <article class="case-cycle-record @if($hasMultiple) case-cycle-records__panel @if((int) $mod->version === $defaultVersion) is-active @endif @endif"
                 @if($hasMultiple)
                 id="cycle-panel-{{ $navKey }}-{{ $mod->version }}"
                 role="tabpanel"
                 aria-labelledby="cycle-tab-{{ $navKey }}-{{ $mod->version }}"
                 data-cycle-version-panel="{{ $mod->version }}"
                 data-cycle-version-nav="{{ $navKey }}"
                 @if((int) $mod->version !== $defaultVersion) hidden @endif
                 @endif>
            <header class="case-cycle-record__head">
                <div>
                    <h4 class="case-cycle-record__title">{{ $mod->scopeLabel() }}</h4>
                    <p class="case-cycle-record__meta">
                        Requested {{ $mod->created_at?->format('M j, Y g:i A') ?? '—' }}
                        @if($mod->requester)
                        · {{ $mod->requester->displayName() }}
                        @endif
                    </p>
                </div>
                <span class="case-cycle-record__status case-cycle-record__status--{{ $mod->is_current ? 'active' : 'done' }}">{{ $mod->statusLabel() }}</span>
            </header>

            @if(filled($mod->notes))
            <div class="case-cycle-record__block">
                <h5 class="case-cycle-record__label">Notes</h5>
                <p class="case-cycle-record__text">{{ $mod->notes }}</p>
            </div>
            @endif

            @php
                $scanFiles = $mod->caseScanFiles();
                $hasDownloads = $mod->hasCaseDataZip() || count($scanFiles) > 0;
            @endphp
            @if($hasDownloads)
            <div class="case-cycle-record__block">
                <h5 class="case-cycle-record__label">Attachments</h5>
                <ul class="case-cycle-record__files">
                    @if($mod->hasCaseDataZip())
                    <li>
                        <a href="{{ $mod->caseDataZipDownloadUrl() }}" class="case-cycle-record__file" download>
                            <i class="zmdi zmdi-archive" aria-hidden="true"></i>
                            <span>Case data archive · {{ $mod->caseDataZipDisplayName() }}</span>
                        </a>
                    </li>
                    @endif
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

            @if($mod->photos->isNotEmpty())
            <div class="case-cycle-record__block">
                <h5 class="case-cycle-record__label">Photos ({{ $mod->photos->count() }})</h5>
                <div class="case-cycle-record__photos">
                    @foreach($mod->photos as $photo)
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

            @if($mod->hasRevisedPlan())
            <div class="case-cycle-record__block">
                <h5 class="case-cycle-record__label">Revised plan canvas</h5>
                <p class="case-cycle-record__hint">Uploaded for this modification — review and approve on the <strong>Treatment Plan</strong> tab.</p>
                <div class="case-cycle-record__canvas-wrap">
                    <iframe src="{{ $mod->revised_plan_url }}"
                            title="Modification #{{ $mod->version }} treatment plan"
                            class="case-cycle-record__canvas"
                            loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade"
                            allowfullscreen></iframe>
                </div>
            </div>
            @elseif($mod->is_current)
            <p class="case-cycle-record__pending"><i class="zmdi zmdi-time"></i> LineUp will upload the revised plan canvas link on the Treatment Plan tab.</p>
            @endif
        </article>
        @endforeach
    </div>
</section>
@endif
