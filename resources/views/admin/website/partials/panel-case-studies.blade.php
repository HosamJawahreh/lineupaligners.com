<section class="wm-panel d-none" id="wm-panel-case-studies">
    <header class="wm-panel__head">
        <div>
            <h3 class="wm-panel__title">Case studies</h3>
            <p class="wm-panel__desc">Manage every case on your public Case studies page (<code>/case-studies</code>). Each case has its own detail page — same pattern as Services. Published cases also feed the homepage Case results carousel.</p>
        </div>
    </header>

    <div class="wm-panel__body">
        <div class="wm-block">
            <h4 class="wm-block__title">Case studies page title</h4>
            <div class="row m-b-10">
                <div class="col-md-4">
                    <input type="text" name="case_studies_subtitle" class="form-control wm-input" value="{{ old('case_studies_subtitle', $content['case_studies']['subtitle']) }}" placeholder="Eyebrow label">
                </div>
                <div class="col-md-8">
                    <input type="text" name="case_studies_title" class="form-control wm-input" value="{{ old('case_studies_title', $content['case_studies']['title']) }}" placeholder="Page title">
                </div>
            </div>
            <p class="wm-hint m-b-0">These headings appear on the public listing page. Add and edit individual cases below — each gets a card on <code>/case-studies</code> and a detail page like <code>/case-studies/spacing-closure</code>.</p>
        </div>
    </div>
</section>
