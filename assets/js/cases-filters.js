(function ($) {
    'use strict';

    var MOBILE_MAX = 767;

    function isMobileFilters() {
        return window.matchMedia('(max-width: ' + MOBILE_MAX + 'px)').matches;
    }

    function setFiltersOpen($form, $toggle, $panel, open) {
        $form.toggleClass('is-open', open);
        $toggle.attr('aria-expanded', open ? 'true' : 'false');

        if (isMobileFilters()) {
            if (open) {
                $panel.removeAttr('hidden');
            } else {
                $panel.attr('hidden', 'hidden');
            }
        } else {
            $panel.removeAttr('hidden');
        }
    }

    window.LineUpInitCasesFilters = function () {
        var $form = $('#cases-filter-form');
        var $toggle = $('#cases-filters-toggle');
        var $panel = $('#cases-filters-panel');

        if (!$form.length || !$toggle.length || !$panel.length || $form.data('lineup-filters-init')) {
            return;
        }

        $form.data('lineup-filters-init', true);

        function syncForViewport() {
            if (!isMobileFilters()) {
                setFiltersOpen($form, $toggle, $panel, true);
                return;
            }

            setFiltersOpen($form, $toggle, $panel, $form.data('mobile-filters-open') === true);
        }

        $toggle.on('click', function () {
            if (!isMobileFilters()) {
                return;
            }

            var open = !$form.hasClass('is-open');
            $form.data('mobile-filters-open', open);
            setFiltersOpen($form, $toggle, $panel, open);
        });

        $(document).on('keydown.lineupCasesFilters', function (event) {
            if (event.key === 'Escape' && isMobileFilters() && $form.hasClass('is-open')) {
                $form.data('mobile-filters-open', false);
                setFiltersOpen($form, $toggle, $panel, false);
                $toggle.trigger('focus');
            }
        });

        $(window).on('resize.lineupCasesFilters', syncForViewport);

        $form.data('mobile-filters-open', false);
        syncForViewport();
    };

    $(function () {
        if (typeof window.LineUpInitCasesFilters === 'function') {
            window.LineUpInitCasesFilters();
        }
    });
})(jQuery);
