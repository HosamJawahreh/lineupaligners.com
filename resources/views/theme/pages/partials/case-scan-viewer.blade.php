@php
    $scanSets = $caseScanSets ?? [];
    if (empty($scanSets) && ! empty($scanFiles ?? [])) {
        $scanSets = [['key' => 'original', 'label' => 'Original case scans', 'notes' => null, 'files' => $scanFiles]];
    }
    $defaultSet = $scanSets[0] ?? null;
    $scanFiles = $defaultSet['files'] ?? [];
    $hasScans = count($scanFiles) > 0;
    $hasMultipleSets = count($scanSets) > 1;
@endphp
<section class="case-scan-section" aria-label="3D scan viewer">
    @if($hasScans)
    <div class="case-scan-viewer-root"
         id="case-scan-viewer-root"
         data-scan-sets="{{ json_encode($scanSets, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) }}"
         data-scans="{{ json_encode($scanFiles, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) }}">
        <div class="case-scan-section__head">
            <div class="case-scan-section__head-inline" aria-label="3D models and scan files">
                <h3>3D Models</h3>
                @if(count($scanSets) > 0)
                <div class="case-scan-set-switcher" role="group" aria-labelledby="case-scan-set-label">
                    <label id="case-scan-set-label" for="case-scan-set-select" class="case-scan-set-switcher__label">
                        <i class="zmdi zmdi-layers" aria-hidden="true"></i>
                        <span>Scan set</span>
                    </label>
                    <div class="case-scan-set-switcher__control">
                        <select id="case-scan-set-select"
                                class="case-scan-set-switcher__select"
                                @if(count($scanSets) < 2) disabled @endif
                                aria-describedby="case-scan-set-label">
                            @foreach($scanSets as $set)
                            <option value="{{ $set['key'] }}" @selected($loop->first)>{{ $set['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                @endif
                <ul class="case-scan-files__list case-scan-files__list--inline" id="case-scan-files-list" aria-label="Uploaded scan files">
                    @foreach($scanFiles as $file)
                    <li class="case-scan-file-card case-scan-file-card--inline" data-scan-id="{{ $file['id'] }}">
                        <div class="case-scan-file__body">
                            <span class="case-scan-file__index">{{ $loop->iteration }}</span>
                            <div class="case-scan-file__info">
                                <span class="case-scan-file__icon" aria-hidden="true">
                                    <i class="zmdi zmdi-layers"></i>
                                </span>
                                <div class="case-scan-file__details">
                                    <span class="case-scan-file__name" title="{{ $file['name'] }}">{{ $file['name'] }}</span>
                                    <span class="case-scan-file__size">@if($file['size']){{ $file['size'] }}@else{{ $file['ext'] }}@endif</span>
                                </div>
                            </div>
                            <div class="case-scan-file__actions">
                                <label class="case-scan-file__action case-scan-file__action--view"
                                       for="case-scan-vis-{{ $file['id'] }}"
                                       title="Show in viewer">
                                    <input type="checkbox"
                                           id="case-scan-vis-{{ $file['id'] }}"
                                           checked
                                           data-scan-id="{{ $file['id'] }}">
                                    <i class="zmdi zmdi-eye" aria-hidden="true"></i>
                                </label>
                                <a href="{{ $file['download_url'] }}"
                                   class="case-scan-file__action case-scan-file__action--download"
                                   download
                                   title="Download file">
                                    <i class="zmdi zmdi-download" aria-hidden="true"></i>
                                </a>
                            </div>
                        </div>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>

        <div class="case-scan-mod-notes is-hidden" id="case-scan-mod-notes" aria-live="polite">
            <span class="case-scan-mod-notes__label">Modification notes</span>
            <p class="case-scan-mod-notes__text" id="case-scan-mod-notes-text"></p>
        </div>

        <div class="case-scan-layout">
        <div class="case-scan-viewer-pane" id="case-scan-viewer-pane">
            <div class="case-scan-toolbar-wrap">
            <nav class="case-scan-toolbar" aria-label="3D viewer tools">
                <span class="case-scan-toolbar__brand">
                    <i class="zmdi zmdi-rotate-3d" aria-hidden="true"></i>
                    <span>3D Scans Viewer</span>
                </span>
                <div class="case-scan-toolbar__tools">
                <span class="case-scan-toolbar__sep" aria-hidden="true"></span>
                <div class="case-scan-toolbar__group" role="group" aria-label="View">
                    <div class="case-scan-toolbar__tool">
                        <button type="button" class="case-scan-toolbar__btn" data-scan-tool="reset-view" title="Reset layout and camera">
                            <i class="zmdi zmdi-refresh-alt"></i>
                        </button>
                        <span class="case-scan-toolbar__label">Reset</span>
                    </div>
                    <div class="case-scan-toolbar__tool">
                        <button type="button" class="case-scan-toolbar__btn" data-scan-tool="fit-view" title="Fit all visible models in view">
                            <i class="zmdi zmdi-aspect-ratio-alt"></i>
                        </button>
                        <span class="case-scan-toolbar__label">Fit</span>
                    </div>
                    <div class="case-scan-toolbar__tool">
                        <button type="button" class="case-scan-toolbar__btn" data-scan-tool="zoom-in" title="Zoom in">
                            <i class="zmdi zmdi-zoom-in"></i>
                        </button>
                        <span class="case-scan-toolbar__label">Zoom in</span>
                    </div>
                    <div class="case-scan-toolbar__tool">
                        <button type="button" class="case-scan-toolbar__btn" data-scan-tool="zoom-out" title="Zoom out">
                            <i class="zmdi zmdi-zoom-out"></i>
                        </button>
                        <span class="case-scan-toolbar__label">Zoom out</span>
                    </div>
                    <div class="case-scan-toolbar__tool">
                        <button type="button" class="case-scan-toolbar__btn" data-scan-tool="view-top" title="Top view">
                            <i class="zmdi zmdi-format-valign-top"></i>
                        </button>
                        <span class="case-scan-toolbar__label">Top</span>
                    </div>
                    <div class="case-scan-toolbar__tool">
                        <button type="button" class="case-scan-toolbar__btn" data-scan-tool="view-front" title="Front view">
                            <i class="zmdi zmdi-sign-in"></i>
                        </button>
                        <span class="case-scan-toolbar__label">Front</span>
                    </div>
                    <div class="case-scan-toolbar__tool">
                        <button type="button" class="case-scan-toolbar__btn" data-scan-tool="view-side" title="Side view">
                            <i class="zmdi zmdi-swap"></i>
                        </button>
                        <span class="case-scan-toolbar__label">Side</span>
                    </div>
                </div>
                <span class="case-scan-toolbar__sep" aria-hidden="true"></span>
                <div class="case-scan-toolbar__group" role="group" aria-label="Display">
                    <div class="case-scan-toolbar__tool">
                        <button type="button" class="case-scan-toolbar__btn" data-scan-tool="wireframe" title="Wireframe" aria-pressed="false">
                            <i class="zmdi zmdi-border-all"></i>
                        </button>
                        <span class="case-scan-toolbar__label">Wireframe</span>
                    </div>
                    <div class="case-scan-toolbar__tool">
                        <button type="button" class="case-scan-toolbar__btn" data-scan-tool="flat-shading" title="Flat shading" aria-pressed="false">
                            <i class="zmdi zmdi-layers"></i>
                        </button>
                        <span class="case-scan-toolbar__label">Flat</span>
                    </div>
                    <div class="case-scan-toolbar__tool">
                        <button type="button" class="case-scan-toolbar__btn" data-scan-tool="axes" title="Axes" aria-pressed="false">
                            <i class="zmdi zmdi-chart"></i>
                        </button>
                        <span class="case-scan-toolbar__label">Axes</span>
                    </div>
                    <div class="case-scan-toolbar__tool">
                        <button type="button" class="case-scan-toolbar__btn is-active" data-scan-tool="grid" title="Grid" aria-pressed="true">
                            <i class="zmdi zmdi-view-quilt"></i>
                        </button>
                        <span class="case-scan-toolbar__label">Grid</span>
                    </div>
                </div>
                <span class="case-scan-toolbar__sep" aria-hidden="true"></span>
                <div class="case-scan-toolbar__group" role="group" aria-label="Navigation">
                    <div class="case-scan-toolbar__tool">
                        <button type="button" class="case-scan-toolbar__btn is-active" data-scan-tool="toggle-pan" title="Pan with right mouse or two fingers" aria-pressed="true">
                            <i class="zmdi zmdi-arrows"></i>
                        </button>
                        <span class="case-scan-toolbar__label">Pan</span>
                    </div>
                    <div class="case-scan-toolbar__tool">
                        <button type="button" class="case-scan-toolbar__btn" data-scan-tool="move-model" title="Move model" aria-pressed="false">
                            <i class="zmdi zmdi-transform"></i>
                        </button>
                        <span class="case-scan-toolbar__label">Move</span>
                    </div>
                    <div class="case-scan-toolbar__tool">
                        <button type="button" class="case-scan-toolbar__btn" data-scan-tool="auto-rotate" title="Auto rotate" aria-pressed="false">
                            <i class="zmdi zmdi-refresh-sync"></i>
                        </button>
                        <span class="case-scan-toolbar__label">Rotate</span>
                    </div>
                </div>
                <span class="case-scan-toolbar__sep" aria-hidden="true"></span>
                <div class="case-scan-toolbar__group" role="group" aria-label="Models visibility">
                    <div class="case-scan-toolbar__tool">
                        <button type="button" class="case-scan-toolbar__btn" data-scan-tool="show-all" title="Show all models">
                            <i class="zmdi zmdi-eye"></i>
                        </button>
                        <span class="case-scan-toolbar__label">Show all</span>
                    </div>
                    <div class="case-scan-toolbar__tool">
                        <button type="button" class="case-scan-toolbar__btn" data-scan-tool="hide-all" title="Hide all models">
                            <i class="zmdi zmdi-eye-off"></i>
                        </button>
                        <span class="case-scan-toolbar__label">Hide all</span>
                    </div>
                </div>
                <span class="case-scan-toolbar__sep" aria-hidden="true"></span>
                <div class="case-scan-toolbar__group" role="group" aria-label="Scene">
                    <div class="case-scan-toolbar__tool">
                        <button type="button" class="case-scan-toolbar__btn" data-scan-tool="brightness" title="Lighting">
                            <i class="zmdi zmdi-brightness-6"></i>
                        </button>
                        <span class="case-scan-toolbar__label">Light</span>
                    </div>
                    <div class="case-scan-toolbar__tool">
                        <button type="button" class="case-scan-toolbar__btn" data-scan-tool="background" title="Background">
                            <i class="zmdi zmdi-invert-colors"></i>
                        </button>
                        <span class="case-scan-toolbar__label">Background</span>
                    </div>
                </div>
                <span class="case-scan-toolbar__sep" aria-hidden="true"></span>
                <div class="case-scan-toolbar__group" role="group" aria-label="Export">
                    <div class="case-scan-toolbar__tool">
                        <button type="button" class="case-scan-toolbar__btn case-scan-toolbar__btn--camera" data-scan-tool="screenshot" title="Screenshot">
                            <i class="zmdi zmdi-camera"></i>
                        </button>
                        <span class="case-scan-toolbar__label">Capture</span>
                    </div>
                    <div class="case-scan-toolbar__tool">
                        <button type="button" class="case-scan-toolbar__btn" data-scan-tool="fullscreen" title="Fullscreen" aria-pressed="false">
                            <i class="zmdi zmdi-fullscreen"></i>
                        </button>
                        <span class="case-scan-toolbar__label">Fullscreen</span>
                    </div>
                </div>
                </div>
            </nav>
            </div>
            <div class="case-scan-canvas-wrap" id="case-scan-canvas-wrap">
                <canvas id="case-scan-canvas" aria-label="3D models preview"></canvas>
                <div class="case-scan-legend" aria-hidden="true">
                    @foreach($scanFiles as $file)
                    <span class="case-scan-legend__item case-scan-legend__item--{{ $file['id'] }}"
                          data-scan-id="{{ $file['id'] }}">
                        <span class="case-scan-legend__dot"></span>
                        {{ $file['label'] }}
                    </span>
                    @endforeach
                </div>
                <div class="case-scan-canvas-overlay is-hidden" id="case-scan-loading">
                    <span class="case-scan-spinner"></span>
                    <span id="case-scan-loading-text">Loading models…</span>
                </div>
                <div class="case-scan-canvas-overlay is-hidden" id="case-scan-error">
                    <i class="zmdi zmdi-alert-circle"></i>
                    <span id="case-scan-error-text">Could not load models.</span>
                </div>
            </div>
        </div>
        </div>
    </div>
    @else
    <div class="case-scan-section__head">
        <div class="case-scan-section__head-inline">
            <h3>3D Models</h3>
        </div>
    </div>
    <div class="case-scan-empty-state">
        <i class="zmdi zmdi-rotate-3d"></i>
        <p>No 3D scan files uploaded for this case yet.</p>
        <span class="case-scan-empty-state__hint">Upload STL, OBJ, or PLY files when editing the case.</span>
    </div>
    @endif
</section>
