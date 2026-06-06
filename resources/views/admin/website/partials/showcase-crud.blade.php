@php
    $context = $context ?? 'case-studies';
    $formId = $formId ?? 'showcase-form-'.$context;
    $returnSection = $returnSection ?? $context;
    $showDetailFields = $showDetailFields ?? false;
    $blockTitle = $blockTitle ?? 'All cases';
    $blockHint = $blockHint ?? '';
    $addLabel = $addLabel ?? 'Add case';
    $editLabel = $editLabel ?? 'Edit case';
    $deleteConfirm = $deleteConfirm ?? 'Remove this case?';
    $emptyMessage = $emptyMessage ?? 'No cases yet — add your first using the form.';
@endphp

<div class="website-showcase-crud" data-showcase-context="{{ $context }}" data-add-label="{{ $addLabel }}" data-edit-label="{{ $editLabel }}">
    @if($portfolioUsesDemo ?? false)
    <div class="wm-alert wm-alert--info">
        <strong>Placeholder cases are live on your site.</strong>
        Add and publish your own below to replace the {{ count($portfolioPreviewItems ?? []) }} demo items.
    </div>
    @elseif(($showcases ?? collect())->where('is_published', false)->count() > 0 && ($showcases ?? collect())->where('is_published', true)->count() === 0)
    <div class="wm-alert wm-alert--warning">
        <strong>Drafts only.</strong> Publish at least one case to replace the demo content on the site.
    </div>
    @endif

    <div class="wm-block">
        <h4 class="wm-block__title">{{ $blockTitle }}</h4>
        @if($blockHint !== '')
        <p class="wm-hint m-b-12">{{ $blockHint }}</p>
        @endif

        <div class="row">
            <div class="col-lg-5">
                <div class="website-showcase-form-panel">
                    <h4 class="m-t-0 showcase-form-title" id="{{ $formId }}-title">{{ $addLabel }}</h4>
                    <form method="POST" action="{{ route('admin.website.showcases.store') }}" enctype="multipart/form-data" id="{{ $formId }}" class="showcase-form">
                        @csrf
                        <input type="hidden" name="return_section" value="{{ $returnSection }}">
                        <input type="hidden" name="_method" class="showcase-form-method" value="POST" disabled>
                        <div class="wm-field">
                            <label class="wm-label">Case title</label>
                            <input type="text" name="title" class="form-control wm-input" required placeholder="e.g. Spacing closure">
                        </div>
                        <div class="wm-field">
                            <label class="wm-label">Patient label <span class="text-muted">(optional)</span></label>
                            <input type="text" name="patient_label" class="form-control wm-input" placeholder="e.g. Patient A">
                        </div>
                        <div class="row">
                            <div class="col-6">
                                <div class="wm-field">
                                    <label class="wm-label">Type</label>
                                    <select name="case_type" class="form-control wm-input">
                                        @foreach($caseTypes as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="wm-field">
                                    <label class="wm-label">Months</label>
                                    <input type="number" name="treatment_months" class="form-control wm-input" min="1" max="120" placeholder="12">
                                </div>
                            </div>
                        </div>
                        <div class="wm-field">
                            <label class="wm-label">Summary</label>
                            <textarea name="summary" class="form-control wm-input" rows="2" placeholder="Short description shown on the carousel card"></textarea>
                        </div>
                        <div class="wm-field">
                            <label class="wm-label">Before photo</label>
                            <input type="file" name="before_image" class="form-control wm-input" accept="image/*">
                        </div>
                        <div class="wm-field">
                            <label class="wm-label">After photo</label>
                            <input type="file" name="after_image" class="form-control wm-input" accept="image/*">
                        </div>
                        @if($showDetailFields)
                        @include('admin.website.partials.item-case-detail', ['detail' => [], 'slug' => '', 'open' => false])
                        @else
                        <input type="hidden" name="slug" value="">
                        <p class="wm-hint m-b-10">This case also gets a detail page on <code>/case-studies</code>. Edit full page content under <strong>Case studies</strong> in the sidebar.</p>
                        @endif
                        <label class="wm-check"><input type="checkbox" name="is_published" class="showcase-published" value="1" checked> Show on website</label>
                        <div class="showcase-edit-extras d-none m-t-10">
                            <label class="wm-check"><input type="checkbox" name="remove_before_image" value="1"> Remove before image</label>
                            <label class="wm-check"><input type="checkbox" name="remove_after_image" value="1"> Remove after image</label>
                        </div>
                        <button type="submit" class="btn btn-primary btn-round btn-block m-t-15 showcase-submit-btn">
                            <i class="zmdi zmdi-plus"></i> {{ $addLabel }}
                        </button>
                        <button type="button" class="btn btn-default btn-round btn-block d-none m-t-8 showcase-cancel-edit">Cancel</button>
                    </form>
                </div>
            </div>
            <div class="col-lg-7">
                @if($portfolioUsesDemo ?? false)
                <h4 class="website-showcase-list__heading">On site now (placeholders)</h4>
                <div class="website-showcase-list website-showcase-list--demo m-b-20">
                    @foreach($portfolioPreviewItems ?? [] as $item)
                    <article class="website-showcase-card website-showcase-card--demo">
                        <div class="website-showcase-card__images">
                            <div class="website-showcase-card__img">
                                <span class="website-showcase-card__badge">Before</span>
                                @if(!empty($item['before_url']))
                                <img src="{{ $item['before_url'] }}" alt="Before — {{ $item['title'] }}">
                                @else
                                <span class="website-showcase-card__empty">No image</span>
                                @endif
                            </div>
                            <div class="website-showcase-card__img">
                                <span class="website-showcase-card__badge website-showcase-card__badge--after">After</span>
                                @if(!empty($item['after_url']))
                                <img src="{{ $item['after_url'] }}" alt="After — {{ $item['title'] }}">
                                @else
                                <span class="website-showcase-card__empty">No image</span>
                                @endif
                            </div>
                        </div>
                        <div class="website-showcase-card__body">
                            <div class="website-showcase-card__head">
                                <h4>{{ $item['title'] }}</h4>
                                <span class="label label-default">Demo</span>
                            </div>
                            @if(!empty($item['patient_label']))<p class="text-muted small">{{ $item['patient_label'] }}</p>@endif
                            <p class="website-showcase-card__summary">{{ $item['summary'] ?? '' }}</p>
                        </div>
                    </article>
                    @endforeach
                </div>
                @endif

                <h4 class="website-showcase-list__heading">{{ ($portfolioUsesDemo ?? false) ? 'Your cases' : 'Published & draft cases' }}</h4>
                @forelse($showcases as $showcase)
                <article class="website-showcase-card"
                         data-form-id="{{ $formId }}"
                         data-title="{{ $showcase->title }}"
                         data-slug="{{ $showcase->slug }}"
                         data-patient-label="{{ $showcase->patient_label }}"
                         data-case-type="{{ $showcase->case_type }}"
                         data-treatment-months="{{ $showcase->treatment_months }}"
                         data-summary="{{ $showcase->summary }}"
                         data-outcome="{{ $showcase->outcome }}"
                         data-published="{{ $showcase->is_published ? '1' : '0' }}"
                         data-detail='@json($showcase->detail ?? [])'
                         data-update-url="{{ route('admin.website.showcases.update', $showcase) }}"
                         data-add-label="{{ $addLabel }}"
                         data-edit-label="{{ $editLabel }}">
                    <div class="website-showcase-card__images">
                        <div class="website-showcase-card__img">
                            <span class="website-showcase-card__badge">Before</span>
                            @if($showcase->beforeImageUrl())
                            <img src="{{ $showcase->beforeImageUrl() }}" alt="Before">
                            @else
                            <span class="website-showcase-card__empty">No image</span>
                            @endif
                        </div>
                        <div class="website-showcase-card__img">
                            <span class="website-showcase-card__badge website-showcase-card__badge--after">After</span>
                            @if($showcase->afterImageUrl())
                            <img src="{{ $showcase->afterImageUrl() }}" alt="After">
                            @else
                            <span class="website-showcase-card__empty">No image</span>
                            @endif
                        </div>
                    </div>
                    <div class="website-showcase-card__body">
                        <div class="website-showcase-card__head">
                            <h4>{{ $showcase->title }}</h4>
                            @if(!$showcase->is_published)<span class="label label-warning">Draft</span>@endif
                        </div>
                        @if($showcase->patient_label)<p class="text-muted small">{{ $showcase->patient_label }}</p>@endif
                        <p class="website-showcase-card__meta">{{ $showcase->caseTypeLabel() }}@if($showcase->treatment_months) · {{ $showcase->treatment_months }} mo @endif</p>
                        @if($showcase->summary)<p class="website-showcase-card__summary">{{ $showcase->summary }}</p>@endif
                        <div class="website-showcase-card__actions">
                            @if($showcase->slug)
                            <a href="{{ $websiteContent->caseStudyDetailUrl(['slug' => $showcase->slug]) }}" class="btn btn-sm btn-default btn-round" target="_blank" rel="noopener">Preview</a>
                            @endif
                            <button type="button" class="btn btn-sm btn-primary btn-round website-edit-showcase">Edit</button>
                            <form method="POST" action="{{ route('admin.website.showcases.destroy', $showcase) }}" class="d-inline" onsubmit="return confirm(@json($deleteConfirm));">
                                @csrf
                                @method('DELETE')
                                <input type="hidden" name="return_section" value="{{ $returnSection }}">
                                <button type="submit" class="btn btn-sm btn-danger btn-simple">Delete</button>
                            </form>
                        </div>
                    </div>
                </article>
                @empty
                <div class="website-showcase-empty">
                    <i class="zmdi zmdi-camera"></i>
                    <p>{{ $emptyMessage }}</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
