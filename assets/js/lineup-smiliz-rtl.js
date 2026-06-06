(function ($) {
    'use strict';

    if (document.documentElement.getAttribute('dir') !== 'rtl') {
        return;
    }

    function patchSwipers() {
        document.querySelectorAll('.swiper-initialized').forEach(function (el) {
            var instance = el.swiper;

            if (!instance || instance.destroyed) {
                return;
            }

            if (typeof instance.changeLanguageDirection === 'function') {
                instance.changeLanguageDirection('rtl');
            } else if (instance.params) {
                instance.params.rtl = true;
                instance.rtl = true;
                instance.update();
            }
        });
    }

    $(window).on('load', function () {
        patchSwipers();
        window.setTimeout(patchSwipers, 400);
    });
})(jQuery);
