<template id="website-feature-row-template">
    <div class="wm-feature-card website-repeatable__row">
        <input type="hidden" name="features[__INDEX__][image]" value="">
        <div class="wm-section-media wm-section-media--compact">
            <div class="wm-section-media__preview" id="feature-preview-__INDEX__">
                <span class="wm-section-media__empty"><i class="zmdi zmdi-image"></i></span>
            </div>
            <div class="wm-section-media__controls">
                <input type="file" name="features[__INDEX__][image_file]" class="form-control wm-input wm-image-input" accept="image/jpeg,image/png,image/webp" data-preview="feature-preview-__INDEX__">
            </div>
        </div>
        <div class="wm-feature-card__fields">
            <select name="features[__INDEX__][icon]" class="form-control wm-input">
                @foreach($iconOptions as $icon)
                <option value="{{ $icon }}">{{ $icon }}</option>
                @endforeach
            </select>
            <input type="text" name="features[__INDEX__][title]" class="form-control wm-input" placeholder="Service title">
            <input type="text" name="features[__INDEX__][description]" class="form-control wm-input" placeholder="Short description">
            <div class="row">
                <div class="col-md-5">
                    <input type="text" name="features[__INDEX__][button_label]" class="form-control wm-input" placeholder="Button text (optional)">
                </div>
                <div class="col-md-7">
                    <input type="text" name="features[__INDEX__][link_url]" class="form-control wm-input" placeholder="Link URL (optional)">
                </div>
            </div>
        </div>
        <button type="button" class="btn btn-sm btn-simple btn-danger website-remove-row"><i class="zmdi zmdi-close"></i></button>
    </div>
</template>

<template id="website-slide-row-template">
    <div class="website-slide-row wm-slide-card wm-slide-card--full">
        <div class="wm-slide-card__head"><strong>New slide</strong></div>
        <input type="hidden" name="slides[__INDEX__][image]" value="">
        <div class="row">
            <div class="col-md-4">
                <div class="wm-section-media wm-section-media--compact">
                    <div class="wm-section-media__preview" id="slide-preview-__INDEX__">
                        <span class="wm-section-media__empty"><i class="zmdi zmdi-image"></i></span>
                    </div>
                    <div class="wm-section-media__controls">
                        <label class="wm-label">Background photo</label>
                        <input type="file" name="slides[__INDEX__][image_file]" class="form-control wm-input wm-image-input" accept="image/jpeg,image/png,image/webp" data-preview="slide-preview-__INDEX__">
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <input type="text" name="slides[__INDEX__][eyebrow]" class="form-control wm-input m-b-10" placeholder="Small label">
                <input type="text" name="slides[__INDEX__][title]" class="form-control wm-input m-b-10" placeholder="Headline">
                <input type="text" name="slides[__INDEX__][cta_label]" class="form-control wm-input" value="Doctor Portal" placeholder="Button">
            </div>
        </div>
        <button type="button" class="btn btn-sm btn-simple btn-danger website-remove-row"><i class="zmdi zmdi-delete"></i> Remove</button>
    </div>
</template>

<template id="website-process-row-template">
    <div class="wm-process-card website-repeatable__row">
        <input type="hidden" name="process_steps[__INDEX__][image]" value="">
        <div class="wm-section-media wm-section-media--compact">
            <div class="wm-section-media__preview" id="process-preview-__INDEX__">
                <span class="wm-section-media__empty"><i class="zmdi zmdi-image"></i></span>
            </div>
            <div class="wm-section-media__controls">
                <label class="wm-label">Dashboard screenshot</label>
                <input type="file" name="process_steps[__INDEX__][image_file]" class="form-control wm-input wm-image-input" accept="image/jpeg,image/png,image/webp" data-preview="process-preview-__INDEX__">
            </div>
        </div>
        <div class="wm-process-card__fields">
            <input type="text" name="process_steps[__INDEX__][title]" class="form-control wm-input" placeholder="Step title">
            <textarea name="process_steps[__INDEX__][description]" class="form-control wm-input" rows="2" placeholder="Description"></textarea>
        </div>
        <button type="button" class="btn btn-sm btn-simple btn-danger website-remove-row" title="Remove"><i class="zmdi zmdi-close"></i></button>
    </div>
</template>

<template id="website-faq-row-template">
    <div class="wm-faq-item">
        <input type="text" name="faq_items[__INDEX__][question]" class="form-control wm-input m-b-8" placeholder="Question">
        <textarea name="faq_items[__INDEX__][answer]" class="form-control wm-input" rows="2" placeholder="Answer"></textarea>
        <button type="button" class="btn btn-sm btn-simple btn-danger website-remove-row m-t-8"><i class="zmdi zmdi-delete"></i> Remove</button>
    </div>
</template>

<template id="website-blog-row-template">
    <div class="wm-blog-card website-repeatable__row">
        <input type="hidden" name="blog_posts[__INDEX__][image]" value="">
        <div class="wm-section-media wm-section-media--compact">
            <div class="wm-section-media__preview" id="blog-preview-__INDEX__">
                <span class="wm-section-media__empty"><i class="zmdi zmdi-image"></i></span>
            </div>
            <div class="wm-section-media__controls">
                <input type="file" name="blog_posts[__INDEX__][image_file]" class="form-control wm-input wm-image-input" accept="image/jpeg,image/png,image/webp" data-preview="blog-preview-__INDEX__">
            </div>
        </div>
        <div class="wm-blog-card__fields">
            <div class="row m-b-8">
                <div class="col-md-6">
                    <input type="text" name="blog_posts[__INDEX__][category]" class="form-control wm-input" placeholder="Category">
                </div>
                <div class="col-md-6">
                    <input type="text" name="blog_posts[__INDEX__][date]" class="form-control wm-input" placeholder="Date">
                </div>
            </div>
            <input type="text" name="blog_posts[__INDEX__][title]" class="form-control wm-input m-b-8" placeholder="Post title">
            <textarea name="blog_posts[__INDEX__][excerpt]" class="form-control wm-input m-b-8" rows="2" placeholder="Short excerpt"></textarea>
            <input type="text" name="blog_posts[__INDEX__][url]" class="form-control wm-input" placeholder="Link URL (optional)">
        </div>
        <button type="button" class="btn btn-sm btn-simple btn-danger website-remove-row" title="Remove"><i class="zmdi zmdi-close"></i></button>
    </div>
</template>

<template id="website-stat-row-template">
    <div class="website-repeatable__row wm-repeat-row">
        <input type="text" name="stats[__INDEX__][value]" class="form-control wm-input" placeholder="500+">
        <input type="text" name="stats[__INDEX__][label]" class="form-control wm-input website-repeatable__grow" placeholder="Label">
        <button type="button" class="btn btn-sm btn-simple btn-danger website-remove-row"><i class="zmdi zmdi-close"></i></button>
    </div>
</template>

<template id="wm-nav-link-row-template">
    <div class="wm-nav-link-row wm-nav-link-row--compact" data-nav-link-row>
        <input type="text" name="__PREFIX__[label]" class="form-control wm-input wm-nav-link-row__label" placeholder="Link label">
        <select name="__PREFIX__[type]" class="form-control wm-input wm-nav-link-type wm-nav-link-row__type">
            <option value="page">Page</option>
            <option value="anchor">Section</option>
            <option value="home">Home</option>
            <option value="url">URL</option>
        </select>
        <select name="__PREFIX__[page_key]" class="form-control wm-input wm-nav-link-row__page" data-show-when="page">
            <option value="">Select page…</option>
            @foreach($pageLinkOptions ?? [] as $key => $label)
            <option value="{{ $key }}">{{ $label }}</option>
            @endforeach
        </select>
        <input type="text" name="__PREFIX__[url]" class="form-control wm-input wm-nav-link-row__url" data-show-when="anchor,home,url" style="display:none" placeholder="#cases or https://…">
        <button type="button" class="btn btn-sm btn-simple btn-danger website-remove-row" aria-label="Remove"><i class="zmdi zmdi-close"></i></button>
    </div>
</template>
