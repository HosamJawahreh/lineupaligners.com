/**
 * LineUp — native squared selects (no bootstrap-select duplicate fields)
 */
(function ($) {
    'use strict';

    if (!$ || !$.fn) {
        return;
    }

    $(window).off('load.bs.select.data-api');

    function destroyAllSelectpickers() {
        if (!$.fn.selectpicker) {
            return;
        }
        $('select').each(function () {
            var $el = $(this);
            if ($el.data('selectpicker')) {
                try {
                    $el.selectpicker('destroy');
                } catch (e) { /* ignore */ }
            }
        });
        $('.bootstrap-select').each(function () {
            var $wrap = $(this);
            var $sel = $wrap.find('select');
            if ($sel.length) {
                $sel.insertBefore($wrap);
                $sel.removeClass('bs-select-hidden');
            }
            $wrap.remove();
        });
    }

    function cleanupSwalSelectpicker() {
        destroyAllSelectpickers();
    }

    function activateForms() {
        destroyAllSelectpickers();

        if ($.AdminOreo && $.AdminOreo.select) {
            $.AdminOreo.select.activate = function () {
                destroyAllSelectpickers();
            };
        }
    }

    window.LineUpActivateSelectpickers = activateForms;

    $(function () {
        activateForms();
    });

    if (window.MutationObserver) {
        var observer = new MutationObserver(function () {
            if (document.querySelector('.swal2-container') || document.querySelector('.bootstrap-select')) {
                cleanupSwalSelectpicker();
            }
        });
        observer.observe(document.body, { childList: true, subtree: true });
    }

    $(window).on('load', function () {
        activateForms();
    });
})(jQuery);
