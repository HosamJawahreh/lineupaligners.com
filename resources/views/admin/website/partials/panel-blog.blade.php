<section class="wm-panel d-none" id="wm-panel-blog">

    <header class="wm-panel__head">

        <div>

            <h3 class="wm-panel__title">Blog</h3>

            <p class="wm-panel__desc">Manage every article on your public Blog page. Featured posts also appear on the homepage.</p>

        </div>

        @include('admin.website.partials.section-visibility-toggle', ['sectionKey' => 'blog', 'sectionLabel' => 'Blog'])

    </header>

    <div class="wm-panel__body">

        <div class="wm-block">

            <h4 class="wm-block__title">Homepage section title</h4>

            <div class="row m-b-10">

                <div class="col-md-4">

                    <input type="text" name="blog_subtitle" class="form-control wm-input" value="{{ old('blog_subtitle', $content['blog']['subtitle']) }}" placeholder="Eyebrow label">

                </div>

                <div class="col-md-8">

                    <input type="text" name="blog_title" class="form-control wm-input" value="{{ old('blog_title', $content['blog']['title']) }}" placeholder="Section title">

                </div>

            </div>

        </div>



        <div class="wm-block">

            <h4 class="wm-block__title">All articles</h4>

            @if($editLocale !== 'en')

            <p class="wm-hint wm-hint--info m-b-12"><i class="zmdi zmdi-info-outline"></i> Photos, slugs, dates, and custom URLs are shared across languages.</p>

            @endif

            <div id="website-blog-list">

                @foreach(old('blog_posts', $content['blog']['items']) as $i => $post)

                <div class="wm-blog-card website-repeatable__row">

                    <input type="hidden" name="blog_posts[{{ $i }}][image]" value="{{ $post['image'] ?? '' }}">

                    @include('admin.website.partials.section-image-field', [

                        'inputName' => "blog_posts[{$i}][image_file]",

                        'currentUrl' => $websiteContent->blogPostImageUrl($post),

                        'removeName' => !empty($post['image']) ? "blog_posts[{$i}][remove_image]" : null,

                        'previewId' => 'blog-preview-'.$i,

                        'compact' => true,

                    ])

                    <div class="wm-blog-card__fields">

                        <div class="row m-b-8">

                            <div class="col-md-6">

                                <input type="text" name="blog_posts[{{ $i }}][category]" class="form-control wm-input" value="{{ $post['category'] ?? '' }}" placeholder="Category (e.g. Clinical)">

                            </div>

                            <div class="col-md-6">

                                <input type="text" name="blog_posts[{{ $i }}][date]" class="form-control wm-input" value="{{ $post['date'] ?? '' }}" placeholder="Date (e.g. 9 July 2025)">

                            </div>

                        </div>

                        <input type="text" name="blog_posts[{{ $i }}][title]" class="form-control wm-input m-b-8" value="{{ $post['title'] ?? '' }}" placeholder="Post title">

                        <textarea name="blog_posts[{{ $i }}][excerpt]" class="form-control wm-input m-b-8" rows="2" placeholder="Short excerpt">{{ $post['excerpt'] ?? '' }}</textarea>

                        <input type="text" name="blog_posts[{{ $i }}][url]" class="form-control wm-input m-b-8" value="{{ $post['url'] ?? '' }}" placeholder="Custom link (optional — defaults to article page)">

                        @include('admin.website.partials.item-blog-detail', [

                            'prefix' => "blog_posts[{$i}]",

                            'item' => $post,

                            'index' => $i,

                        ])

                    </div>

                    <button type="button" class="btn btn-sm btn-simple btn-danger website-remove-row" title="Remove"><i class="zmdi zmdi-close"></i></button>

                </div>

                @endforeach

            </div>

            <button type="button" class="btn btn-sm btn-default btn-round m-t-10" id="website-add-blog"><i class="zmdi zmdi-plus"></i> Add post</button>

        </div>

    </div>

</section>

