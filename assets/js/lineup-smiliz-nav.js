(function ($) {
    'use strict';

    window.lineupSmilizNavManaged = true;

    function isMobileNav() {
        return window.innerWidth < 1201;
    }

    function mobileMenuPanel() {
        return $('#masthead .pbmit-main-header-area .pbmit-navbar > div').first();
    }

    function setMenuOpen(isOpen) {
        var $body = $('body');

        $body.toggleClass('active', isOpen);
        $body.toggleClass('lineup-mobile-nav-open', isOpen);
        mobileMenuPanel().toggleClass('active', isOpen);
        $('.pbmit-sticky-header .pbmit-navbar > div').removeClass('active');
        $('#menu-toggle, #menu-toggle2').attr('aria-expanded', isOpen ? 'true' : 'false');
    }

    function toggleMenu() {
        setMenuOpen(!$('body').hasClass('active'));
    }

    function appendClosePanel($panel) {
        if (!$panel.length || $panel.children('.closepanel').length) {
            return;
        }

        $panel.append(
            '<span class="closepanel" role="button" tabindex="0" aria-label="Close menu">' +
                '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 26 26" aria-hidden="true">' +
                    '<rect width="36" height="1" transform="translate(0.707) rotate(45)"></rect>' +
                    '<rect width="36" height="1" transform="translate(0 25.456) rotate(-45)"></rect>' +
                '</svg>' +
            '</span>'
        );
    }

    function ensureClosePanels() {
        mobileMenuPanel().each(function () {
            appendClosePanel($(this));
        });
    }

    $(function () {
        var $toggle = $('#menu-toggle');

        if (!$toggle.length) {
            return;
        }

        ensureClosePanels();

        $(window).on('load.lineupNav', ensureClosePanels);

        $toggle.attr({
            'aria-controls': 'pbmit-top-menu',
            'aria-expanded': 'false',
        });

        $toggle.on('click.lineupNav', function (event) {
            event.preventDefault();
            toggleMenu();
        });

        $(document).on('click.lineupNav', '#menu-toggle2', function (event) {
            event.preventDefault();
            event.stopImmediatePropagation();
            toggleMenu();
        });

        $(document).on('click.lineupNav', '.lineup-mobile-menu-backdrop, .pbmit-mobile-menu-bg, .pbmit-navbar > div > .closepanel', function () {
            setMenuOpen(false);
        });

        $(document).on('click.lineupNav', '.pbmit-navbar .navigation a', function () {
            if (isMobileNav()) {
                setMenuOpen(false);
            }
        });

        $(document).on('keydown.lineupNav', '.pbmit-navbar > div > .closepanel', function (event) {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                setMenuOpen(false);
            }
        });

        $(document).on('keydown.lineupNav', function (event) {
            if (event.key === 'Escape' && $('body').hasClass('active')) {
                setMenuOpen(false);
            }
        });

        $(window).on('resize.lineupNav', function () {
            if (!isMobileNav()) {
                setMenuOpen(false);
            }
        });
    });
})(jQuery);
