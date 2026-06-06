(function ($) {
    'use strict';

    function prefersReducedMotion() {
        return window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    }

    function disableAos() {
        if (typeof AOS !== 'undefined') {
            AOS.init({ disable: true });
        }

        document.querySelectorAll('[data-aos]').forEach(function (el) {
            el.removeAttribute('data-aos');
            el.removeAttribute('data-aos-duration');
            el.style.opacity = '1';
            el.style.transform = 'none';
        });
    }

    function initBackToTop() {
        var $top = $('.pbmit-backtotop, .lineup-back-to-top');
        if (!$top.length) {
            return;
        }

        $top.attr('tabindex', '0');
        $top.on('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                $(this).trigger('click');
            }
        });
    }

    function initSkipLink() {
        $('.lineup-skip-link').on('click', function (e) {
            var target = document.querySelector(this.getAttribute('href'));
            if (!target) {
                return;
            }
            e.preventDefault();
            target.focus({ preventScroll: false });
        });
    }

    $(function () {
        if (prefersReducedMotion()) {
            disableAos();
        }

        initBackToTop();
        initSkipLink();
    });
})(jQuery);
