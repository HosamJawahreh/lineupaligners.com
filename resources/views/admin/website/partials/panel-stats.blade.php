<section class="wm-panel d-none" id="wm-panel-stats">
    <header class="wm-panel__head">
        <div>
            <h3 class="wm-panel__title">Stats</h3>
            <p class="wm-panel__desc">Trust numbers and optional call-to-action below the stats row.</p>
        </div>
        @include('admin.website.partials.section-visibility-toggle', ['sectionKey' => 'stats', 'sectionLabel' => 'Stats section'])
    </header>
    <div class="wm-panel__body">
        <div class="wm-block">
            <h4 class="wm-block__title">Section header</h4>
            <div class="row m-b-10">
                <div class="col-md-6">
                    <input type="text" name="stats_subtitle" class="form-control wm-input" value="{{ old('stats_subtitle', $content['stats_section']['subtitle']) }}" placeholder="Stats label">
                </div>
                <div class="col-md-6">
                    <input type="text" name="stats_title" class="form-control wm-input" value="{{ old('stats_title', $content['stats_section']['title']) }}" placeholder="Stats title">
                </div>
            </div>
        </div>

        <div class="wm-block">
            <h4 class="wm-block__title">Trust stats</h4>
            <div class="website-repeatable website-repeatable--stats" id="website-stats-list">
                @foreach(old('stats', $content['stats']) as $i => $stat)
                <div class="website-repeatable__row wm-repeat-row">
                    <input type="text" name="stats[{{ $i }}][value]" class="form-control wm-input" value="{{ $stat['value'] ?? '' }}" placeholder="500+">
                    <input type="text" name="stats[{{ $i }}][label]" class="form-control wm-input website-repeatable__grow" value="{{ $stat['label'] ?? '' }}" placeholder="Label">
                    <button type="button" class="btn btn-sm btn-simple btn-danger website-remove-row"><i class="zmdi zmdi-close"></i></button>
                </div>
                @endforeach
            </div>
            <button type="button" class="btn btn-sm btn-default btn-round m-t-10" id="website-add-stat"><i class="zmdi zmdi-plus"></i> Add stat</button>
        </div>

        <div class="wm-block">
            <h4 class="wm-block__title">Stats CTA</h4>
            <div class="row">
                <div class="col-md-6">
                    <div class="wm-field">
                        <label class="wm-label">CTA title</label>
                        <input type="text" name="stats_cta_title" class="form-control wm-input" value="{{ old('stats_cta_title', $content['stats_section']['cta_title']) }}">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="wm-field">
                        <label class="wm-label">CTA button</label>
                        <input type="text" name="stats_cta_label" class="form-control wm-input" value="{{ old('stats_cta_label', $content['stats_section']['cta_label']) }}">
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
