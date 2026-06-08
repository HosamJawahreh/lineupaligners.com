<aside class="wm-sidebar">
    <p class="wm-sidebar__heading">Site</p>
    <ul class="wm-sidebar__list">
        <li>
            <a href="#wm-panel-general" class="wm-sidebar__link wm-goto-section" data-wm-section="general">
                <i class="zmdi zmdi-view-dashboard"></i> Overview
            </a>
        </li>
        <li>
            <a href="#wm-panel-main-menu" class="wm-sidebar__link wm-goto-section" data-wm-section="main-menu">
                <i class="zmdi zmdi-menu"></i> Main menu
            </a>
        </li>
    </ul>

    <p class="wm-sidebar__heading wm-sidebar__heading--spaced">Website content</p>
    <ul class="wm-sidebar__list">
        <li class="wm-sidebar__group" data-wm-group-sections="hero,how-it-works,portfolio">
            <button type="button" class="wm-sidebar__group-toggle" aria-expanded="false">
                <i class="zmdi zmdi-view-module"></i>
                <span>Homepage sections</span>
                <i class="zmdi zmdi-chevron-down wm-sidebar__group-caret" aria-hidden="true"></i>
            </button>
            <ul class="wm-sidebar__sublist">
                <li>
                    <a href="#wm-panel-hero" class="wm-sidebar__link wm-sidebar__sublink wm-goto-section" data-wm-section="hero">
                        Hero banner
                    </a>
                </li>
                <li>
                    <a href="#wm-panel-how-it-works" class="wm-sidebar__link wm-sidebar__sublink wm-goto-section" data-wm-section="how-it-works">
                        How it works
                    </a>
                </li>
                <li>
                    <a href="#wm-panel-portfolio" class="wm-sidebar__link wm-sidebar__sublink wm-goto-section" data-wm-section="portfolio">
                        Case results
                    </a>
                </li>
            </ul>
        </li>
        <li>
            <a href="#wm-panel-about" class="wm-sidebar__link wm-goto-section" data-wm-section="about">
                <i class="zmdi zmdi-account"></i> About us
            </a>
        </li>
        <li>
            <a href="#wm-panel-why-lineup" class="wm-sidebar__link wm-goto-section" data-wm-section="why-lineup">
                <i class="zmdi zmdi-star-circle"></i> Why LINEUP
            </a>
        </li>
        <li>
            <a href="#wm-panel-case-studies" class="wm-sidebar__link wm-goto-section" data-wm-section="case-studies">
                <i class="zmdi zmdi-collection-image"></i> Case studies
            </a>
        </li>
        <li>
            <a href="#wm-panel-blog" class="wm-sidebar__link wm-goto-section" data-wm-section="blog">
                <i class="zmdi zmdi-collection-text"></i> Blog
            </a>
        </li>
        <li>
            <a href="#wm-panel-faq" class="wm-sidebar__link wm-goto-section" data-wm-section="faq">
                <i class="zmdi zmdi-help"></i> FAQ
            </a>
        </li>
        <li>
            <a href="#wm-panel-contact" class="wm-sidebar__link wm-goto-section" data-wm-section="contact">
                <i class="zmdi zmdi-email"></i> Contact
            </a>
        </li>
        <li>
            <a href="#wm-panel-navigation" class="wm-sidebar__link wm-goto-section" data-wm-section="navigation">
                <i class="zmdi zmdi-link"></i> Footer
            </a>
        </li>
    </ul>

    <p class="wm-sidebar__note">
        @php
            $sidebarPreviewUrl = ($editLocale ?? 'en') === 'ar'
                ? url('/ar?preview=1')
                : route('website.home', ['preview' => 1]);
        @endphp
        <a href="{{ $sidebarPreviewUrl }}" target="_blank" rel="noopener">
            <i class="zmdi zmdi-eye"></i> Preview site
        </a>
    </p>
</aside>
