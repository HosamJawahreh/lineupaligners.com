(function () {
    'use strict';

    function patchCasesSwipers() {
        document.querySelectorAll('.lineup-cases-swiper').forEach(function (el) {
            var swiper = el.swiper;

            if (!swiper || swiper.__lineupCasesPatched) {
                return;
            }

            swiper.params.allowTouchMove = false;
            swiper.params.simulateTouch = false;
            swiper.params.noSwiping = true;
            swiper.params.noSwipingClass = 'swiper-no-swiping';

            var breakpoints = swiper.params.breakpoints;

            if (breakpoints) {
                [991, 767, 575, 0].forEach(function (width) {
                    if (breakpoints[width]) {
                        breakpoints[width].slidesPerView = 1;
                    }
                });
            }

            swiper.__lineupCasesPatched = true;
            swiper.currentBreakpoint = false;
            swiper.update();
        });
    }

    window.addEventListener('load', patchCasesSwipers);
})();
