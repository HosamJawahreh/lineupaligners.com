(function ($) {
    'use strict';

    var MOBILE_BREAKPOINT = 1170;
    var bound = false;

    function isMobileNav() {
        return window.innerWidth < MOBILE_BREAKPOINT;
    }

    function setMenuOpen(open) {
        var $body = $('body');
        var $overlay = $('.overlay');
        var $toggle = $('.lineup-topbar-btn-menu--drawer');

        if (open) {
            $body.addClass('overlay-open lineup-mobile-nav-open');
            $overlay.fadeIn(200);
            $toggle.attr('aria-expanded', 'true');
        } else {
            $body.removeClass('overlay-open lineup-mobile-nav-open');
            $overlay.fadeOut(200);
            $toggle.attr('aria-expanded', 'false');
            closeOverflowMenu();
        }
    }

    function closeMenu() {
        if ($('body').hasClass('overlay-open')) {
            setMenuOpen(false);
        }
    }

    function closeOverflowMenu() {
        $('.lineup-topbar-overflow-menu').removeClass('is-open');
        $('.lineup-topbar-btn-more').attr('aria-expanded', 'false');
    }

    function syncOverflowThemeLabel() {
        var isDark = $('body').hasClass('lineup-color-dark');
        var $btn = $('[data-lineup-overflow-theme]');

        if (!$btn.length) {
            return;
        }

        $btn.find('[data-lineup-overflow-theme-label]').text(isDark ? 'Light mode' : 'Dark mode');
        $btn.find('i.zmdi').attr('class', isDark ? 'zmdi zmdi-sun' : 'zmdi zmdi-brightness-2');
        $btn.attr('aria-label', isDark ? 'Switch to light mode' : 'Switch to dark mode');
    }

    function bindDrawerToggle() {
        $('.bars').off('click').on('click.lineupMobileNav', function (event) {
            event.preventDefault();
            event.stopPropagation();

            if ($('body').hasClass('overlay-open')) {
                closeMenu();
            } else {
                setMenuOpen(true);
            }
        });
    }

    function initLineupMobileNav() {
        if (!$('body').hasClass('lineup-app')) {
            return;
        }

        bindDrawerToggle();

        if (bound) {
            return;
        }

        bound = true;

        $('.overlay').on('click.lineupMobileNav', function () {
            closeMenu();
        });

        $(document).on('click.lineupMobileNav', '.lineup-sidebar-close', function () {
            closeMenu();
        });

        $(document).on('click.lineupMobileNav', '#leftsidebar.lineup-sidebar .lineup-nav a', function () {
            if (isMobileNav()) {
                closeMenu();
            }
        });

        $(document).on('click.lineupMobileNav', '.lineup-topbar-btn-more', function (event) {
            event.preventDefault();
            event.stopPropagation();

            var $menu = $(this).siblings('.lineup-topbar-overflow-menu');
            var willOpen = !$menu.hasClass('is-open');

            $menu.toggleClass('is-open');
            $(this).attr('aria-expanded', willOpen ? 'true' : 'false');

            if (willOpen) {
                syncOverflowThemeLabel();
            }
        });

        $(document).on('click.lineupMobileNav', '[data-lineup-overflow-theme]', function (event) {
            event.preventDefault();

            var $themeToggle = $('#lineup-theme-toggle');
            if ($themeToggle.length) {
                $themeToggle.trigger('click');
            }

            window.setTimeout(syncOverflowThemeLabel, 0);
            closeOverflowMenu();
        });

        $(document).on('click.lineupMobileNav', '.lineup-topbar-overflow-menu a', function () {
            closeOverflowMenu();
        });

        $(document).on('click.lineupMobileNav', function (event) {
            if (!$(event.target).closest('.lineup-topbar-tools-overflow').length) {
                closeOverflowMenu();
            }
        });

        syncOverflowThemeLabel();

        $(window).on('resize.lineupMobileNav', function () {
            if (!isMobileNav()) {
                closeMenu();
            }
        });
    }

    $(initLineupMobileNav);
    $(window).on('load.lineupMobileNav', bindDrawerToggle);
})(jQuery);
