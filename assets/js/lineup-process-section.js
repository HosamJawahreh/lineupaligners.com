(function () {
    'use strict';

    var DESKTOP_MIN = 992;
    var ENTER_RATIO = 0.65;
    var EXIT_RATIO = 0.35;

    function initProcessShowcase() {
        var root = document.querySelector('.lineup-process-showcase');

        if (!root) {
            return;
        }

        var tour = root.querySelector('.lineup-process-showcase__tour');
        var tabs = root.querySelectorAll('.lineup-process-showcase__tab');
        var panels = root.querySelectorAll('.lineup-process-showcase__panel');
        var fill = root.querySelector('.lineup-process-showcase__rail-fill');
        var stepCount = tabs.length;
        var activeIndex = 0;
        var scrollTicking = false;
        var scrollEnabled = false;

        if (!tabs.length || !panels.length) {
            return;
        }

        function setActive(index, fromScroll) {
            index = Math.max(0, Math.min(index, stepCount - 1));

            if (index === activeIndex && fromScroll) {
                return;
            }

            activeIndex = index;

            tabs.forEach(function (tab, i) {
                var active = i === index;
                tab.classList.toggle('is-active', active);
                tab.setAttribute('aria-selected', active ? 'true' : 'false');
            });

            panels.forEach(function (panel, i) {
                var active = i === index;
                panel.classList.toggle('is-active', active);
                panel.setAttribute('aria-hidden', active ? 'false' : 'true');
            });

            if (fill && stepCount > 1) {
                fill.style.height = (index / (stepCount - 1)) * 100 + '%';
            }
        }

        function isDesktop() {
            return window.innerWidth >= DESKTOP_MIN;
        }

        function getScrollMetrics() {
            var target = tour || root;
            var rect = target.getBoundingClientRect();
            var vh = window.innerHeight;
            var sectionTop = window.scrollY + rect.top;
            var sectionHeight = target.offsetHeight;
            var start = sectionTop - vh * ENTER_RATIO;
            var end = sectionTop + sectionHeight - vh * EXIT_RATIO;
            var scrollRange = end - start;

            if (scrollRange < 1) {
                scrollRange = 1;
            }

            return {
                start: start,
                scrollRange: scrollRange,
            };
        }

        function getStepFromScroll() {
            var metrics = getScrollMetrics();
            var progress = (window.scrollY - metrics.start) / metrics.scrollRange;
            progress = Math.max(0, Math.min(1, progress));

            if (stepCount === 1) {
                return 0;
            }

            return Math.min(stepCount - 1, Math.round(progress * (stepCount - 1)));
        }

        function scrollToStep(index) {
            var metrics = getScrollMetrics();

            if (stepCount <= 1) {
                setActive(index, false);
                return;
            }

            var progress = index / (stepCount - 1);
            var target = metrics.start + progress * metrics.scrollRange;
            var reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

            window.scrollTo({
                top: target,
                behavior: reducedMotion ? 'auto' : 'smooth',
            });

            setActive(index, false);
        }

        function updateFromScroll() {
            scrollTicking = false;

            if (!scrollEnabled || !isDesktop()) {
                return;
            }

            setActive(getStepFromScroll(), true);
        }

        function onScroll() {
            if (!scrollTicking) {
                scrollTicking = true;
                window.requestAnimationFrame(updateFromScroll);
            }
        }

        function enableScrollDrive() {
            var shouldEnable = isDesktop() && stepCount > 1;

            scrollEnabled = shouldEnable;
            root.classList.toggle('is-scroll-driven', shouldEnable);

            if (shouldEnable) {
                updateFromScroll();
            } else {
                setActive(activeIndex, false);
            }
        }

        tabs.forEach(function (tab) {
            tab.addEventListener('click', function () {
                var index = parseInt(tab.getAttribute('data-step-index'), 10) || 0;

                if (scrollEnabled) {
                    scrollToStep(index);
                } else {
                    setActive(index, false);
                }
            });
        });

        setActive(0, false);
        enableScrollDrive();

        window.addEventListener('scroll', onScroll, { passive: true });
        window.addEventListener('resize', function () {
            enableScrollDrive();
            onScroll();
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initProcessShowcase);
    } else {
        initProcessShowcase();
    }
})();
