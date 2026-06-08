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
            $('.lineup-topbar-overflow-menu').removeClass('is-open');
        }
    }

    function closeMenu() {
        if ($('body').hasClass('overlay-open')) {
            setMenuOpen(false);
        }
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
            $(this).siblings('.lineup-topbar-overflow-menu').toggleClass('is-open');
        });

        $(document).on('click.lineupMobileNav', function (event) {
            if (!$(event.target).closest('.lineup-topbar-tools-overflow').length) {
                $('.lineup-topbar-overflow-menu').removeClass('is-open');
            }
        });

        $(window).on('resize.lineupMobileNav', function () {
            if (!isMobileNav()) {
                closeMenu();
            }
        });
    }

    $(initLineupMobileNav);
    $(window).on('load.lineupMobileNav', bindDrawerToggle);
})(jQuery);
