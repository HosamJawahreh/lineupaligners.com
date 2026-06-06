@php
    $entries = $mainMenuEntries ?? [];
    $previewItems = collect($menuPreview ?? [])->map(function (array $group) {
        if (count($group['children']) === 1) {
            return $group['children'][0]['label'];
        }

        return $group['label'].' ▾';
    });
@endphp
<section class="wm-panel wm-panel--solo d-none" id="wm-panel-main-menu">
    <header class="wm-panel__head">
        <div>
            <h3 class="wm-panel__title">Main menu</h3>
            <p class="wm-panel__desc">
                Drag to reorder header links. Home always appears first on the public site.
                Use <strong>Live</strong> to publish a page and <strong>Menu</strong> to show it in the header.
                <strong>{{ $navInMenuCount ?? 0 }}</strong> link{{ ($navInMenuCount ?? 0) === 1 ? '' : 's' }} visible.
            </p>
        </div>
    </header>
    <div class="wm-panel__body">
        <form method="POST" action="{{ route('admin.website.main-menu.update') }}" id="website-main-menu-form">
            @csrf
            @method('PUT')

            <div class="wm-main-menu-preview">
                <span class="wm-main-menu-preview__home"><i class="zmdi zmdi-home"></i> Home</span>
                @forelse($previewItems as $label)
                <span class="wm-main-menu-preview__item">{{ $label }}</span>
                @empty
                <span class="wm-main-menu-preview__empty">No menu links yet — turn on Live and Menu for pages below.</span>
                @endforelse
            </div>

            <p class="wm-hint wm-hint--info m-b-20">
                <i class="zmdi zmdi-info-outline"></i>
                One visible page in a group shows as a direct link. Two or more show as a dropdown.
            </p>

            <ul class="wm-main-menu-list" id="wm-main-menu-list">
                @foreach($entries as $entry)
                <li class="wm-main-menu-entry" data-wm-menu-group="{{ $entry['group'] }}">
                    <input type="hidden" name="main_menu[order][]" value="{{ $entry['group'] }}">

                    <div class="wm-main-menu-entry__head">
                        <button type="button" class="wm-main-menu-entry__handle" draggable="true" aria-label="Drag to reorder section" title="Drag to reorder">
                            <i class="zmdi zmdi-unfold-more"></i>
                        </button>
                        <div class="wm-main-menu-entry__meta">
                            <strong>{{ $entry['label'] }}</strong>
                            <span class="wm-main-menu-entry__badge @if($entry['is_dropdown']) is-dropdown @elseif($entry['visible_count'] === 1) is-link @else is-hidden @endif">
                                @if($entry['is_dropdown'])
                                    Dropdown · {{ $entry['visible_count'] }} links
                                @elseif($entry['visible_count'] === 1)
                                    Direct link
                                @else
                                    Hidden
                                @endif
                            </span>
                        </div>
                        @if($entry['is_dropdown'] || count($entry['pages']) > 1)
                        <div class="wm-main-menu-entry__group-label">
                            <label class="wm-main-menu-entry__group-label-text">Dropdown title (EN)</label>
                            <input type="text"
                                   name="main_menu[labels][{{ $entry['group'] }}]"
                                   class="form-control input-sm wm-input"
                                   value="{{ $entry['custom_label'] }}"
                                   placeholder="{{ $entry['label'] }}">
                        </div>
                        <div class="wm-main-menu-entry__group-label">
                            <label class="wm-main-menu-entry__group-label-text">Dropdown title (AR)</label>
                            <input type="text"
                                   name="main_menu[labels_ar][{{ $entry['group'] }}]"
                                   class="form-control input-sm wm-input"
                                   value="{{ $entry['custom_label_ar'] }}"
                                   placeholder="{{ $entry['label_ar'] }}"
                                   dir="rtl">
                        </div>
                        @endif
                    </div>

                    <ul class="wm-main-menu-children" data-wm-menu-children="{{ $entry['group'] }}">
                        @foreach($entry['pages'] as $page)
                        <li class="wm-main-menu-page" data-wm-menu-page="{{ $page['key'] }}">
                            <input type="hidden" name="main_menu[children][{{ $entry['group'] }}][]" value="{{ $page['key'] }}">

                            <button type="button" class="wm-main-menu-page__handle" draggable="true" aria-label="Drag to reorder page" title="Drag to reorder">
                                <i class="zmdi zmdi-drag"></i>
                            </button>

                            <div class="wm-main-menu-page__info">
                                <strong>{{ $page['label'] }}</strong>
                                <code>/{{ $page['path'] }}</code>
                            </div>

                            <label class="wm-main-menu-page__toggle">
                                <input type="hidden" name="main_menu[pages][{{ $page['key'] }}][enabled]" value="0">
                                <input type="checkbox"
                                       name="main_menu[pages][{{ $page['key'] }}][enabled]"
                                       value="1"
                                       class="wm-main-menu-enabled"
                                       @checked($page['enabled'])>
                                <span>Live</span>
                            </label>

                            <label class="wm-main-menu-page__toggle">
                                <input type="checkbox"
                                       name="main_menu[pages][{{ $page['key'] }}][in_nav]"
                                       value="1"
                                       class="wm-main-menu-nav"
                                       @checked($page['in_nav'])
                                       @disabled(! $page['enabled'])>
                                <span>Menu</span>
                            </label>

                            <input type="text"
                                   name="main_menu[pages][{{ $page['key'] }}][nav_label]"
                                   class="form-control input-sm wm-input wm-main-menu-page__label"
                                   value="{{ $page['nav_label'] }}"
                                   placeholder="Menu label (EN)">
                            <input type="text"
                                   name="main_menu[pages][{{ $page['key'] }}][nav_label_ar]"
                                   class="form-control input-sm wm-input wm-main-menu-page__label wm-main-menu-page__label--ar"
                                   value="{{ $page['nav_label_ar'] ?? '' }}"
                                   placeholder="Menu label (AR)"
                                   dir="rtl">
                        </li>
                        @endforeach
                    </ul>
                </li>
                @endforeach
            </ul>

            @if($entries === [])
            <p class="wm-hint">No page groups available yet.</p>
            @endif

            <div class="wm-savebar" id="website-main-menu-savebar">
                <p class="wm-savebar__hint">Drag to reorder. Dropdown titles and Arabic labels appear on the Arabic site.</p>
                <button type="submit" class="btn btn-primary btn-round">
                    <i class="zmdi zmdi-check"></i> Save main menu
                </button>
            </div>
        </form>
    </div>
</section>
