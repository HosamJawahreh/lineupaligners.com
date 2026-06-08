(function ($) {
    'use strict';

    var MOBILE_MAX = 767;

    function isMobileFilters() {
        return window.matchMedia('(max-width: ' + MOBILE_MAX + 'px)').matches;
    }

    function setFiltersOpen($form, $toggle, open) {
        $form.toggleClass('is-open', open);
        $toggle.attr('aria-expanded', open ? 'true' : 'false');
    }

    window.LineUpInitCasesFilters = function () {
        var $form = $('#cases-filter-form');
        var $toggle = $('#cases-filters-toggle');
        var $panel = $('#cases-filters-panel');

        if (!$form.length || !$toggle.length || !$panel.length) {
            return;
        }

        function syncForViewport() {
            if (!isMobileFilters()) {
                setFiltersOpen($form, $toggle, true);
                $panel.removeAttr('hidden');
                return;
            }

            var open = $form.hasClass('is-open');
            setFiltersOpen($form, $toggle, open);
            $panel.prop('hidden', !open);
        }

        $toggle.on('click', function () {
            if (!isMobileFilters()) {
                return;
            }

            var open = !$form.hasClass('is-open');
            setFiltersOpen($form, $toggle, open);
            $panel.prop('hidden', !open);
        });

        $(window).on('resize.lineupCasesFilters', syncForViewport);
        syncForViewport();
    };

    $(function () {
        if (typeof window.LineUpInitCasesFilters === 'function') {
            window.LineUpInitCasesFilters();
        }
    });
})(jQuery);
