(function ($) {
    'use strict';

    function initBeforeAfter($scope) {
        if (typeof $.fn.twentytwenty !== 'function') {
            return;
        }

        $scope.find('.pbmit-ele-before-after-inner').each(function () {
            var $container = $(this);

            if ($container.hasClass('twentytwenty-container') || $container.closest('.twentytwenty-wrapper').length) {
                return;
            }

            if ($container.find('img').length < 2) {
                return;
            }

            $container.find('.pbmit-after-image').removeClass('pbmit-hide');

            $container.twentytwenty({
                default_offset_pct: 0.5,
                before_label: window.lineupSmilizConfig?.beforeLabel || '',
                after_label: window.lineupSmilizConfig?.afterLabel || '',
                no_overlay: true,
            });
        });

        $(window).trigger('resize.twentytwenty');
    }

    $(function () {
        initBeforeAfter($(document));
    });

    $(window).on('load', function () {
        initBeforeAfter($(document));
    });
})(jQuery);
