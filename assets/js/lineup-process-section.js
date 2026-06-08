(function () {
    'use strict';

    var DESKTOP_MIN = 992;
    var WHEEL_COOLDOWN_MS = 420;

    function initProcessShowcase() {
        var section = document.getElementById('process');
        var root = document.querySelector('.lineup-process-showcase');

        if (!section || !root) {
            return;
        }

        var tour = root.querySelector('.lineup-process-showcase__tour');
        var tabs = root.querySelectorAll('.lineup-process-showcase__tab');
        var panels = root.querySelectorAll('.lineup-process-showcase__panel');
        var fill = root.querySelector('.lineup-process-showcase__rail-fill');
        var stepCount = tabs.length;
        var activeStep = 0;
        var interactiveMode = false;
        var wheelLocked = false;

        if (!tabs.length || !panels.length) {
            return;
        }

        function clamp(value, min, max) {
            return Math.max(min, Math.min(max, value));
        }

        function isDesktop() {
            return window.innerWidth >= DESKTOP_MIN;
        }

        function railProgress(index) {
            if (stepCount <= 1) {
                return 0;
            }

            return index / (stepCount - 1);
        }

        function showStep(index) {
            index = clamp(index, 0, stepCount - 1);
            activeStep = index;
            root.setAttribute('data-active-step', String(index));

            tabs.forEach(function (tab, i) {
                var isActive = i === index;
                tab.classList.toggle('is-active', isActive);
                tab.setAttribute('aria-selected', isActive ? 'true' : 'false');
            });

            panels.forEach(function (panel, i) {
                var isActive = i === index;

                if (isActive && interactiveMode && !panel.classList.contains('is-active')) {
                    panel.classList.remove('is-entering');
                    void panel.offsetWidth;
                    panel.classList.add('is-entering');
                }

                panel.classList.toggle('is-active', isActive);
                panel.setAttribute('aria-hidden', isActive ? 'false' : 'true');
            });

            if (fill) {
                fill.style.height = (railProgress(index) * 100) + '%';
            }
        }

        function bindMode() {
            var shouldInteractive = isDesktop() && stepCount > 1;

            interactiveMode = shouldInteractive;
            root.classList.toggle('is-interactive', shouldInteractive);
            root.classList.remove('is-scroll-driven', 'is-scroll-trigger');
            section.classList.remove('is-scroll-driven');

            showStep(activeStep);
        }

        tabs.forEach(function (tab) {
            tab.addEventListener('click', function () {
                var index = parseInt(tab.getAttribute('data-step-index'), 10) || 0;
                showStep(index);
            });

            tab.addEventListener('keydown', function (event) {
                if (!interactiveMode) {
                    return;
                }

                var index = parseInt(tab.getAttribute('data-step-index'), 10) || 0;
                var targetIndex = null;

                if (event.key === 'ArrowDown' || event.key === 'ArrowRight') {
                    targetIndex = index + 1;
                } else if (event.key === 'ArrowUp' || event.key === 'ArrowLeft') {
                    targetIndex = index - 1;
                } else if (event.key === 'Home') {
                    targetIndex = 0;
                } else if (event.key === 'End') {
                    targetIndex = stepCount - 1;
                }

                if (targetIndex === null) {
                    return;
                }

                event.preventDefault();
                showStep(targetIndex);
                tabs[targetIndex].focus();
            });
        });

        if (tour) {
            tour.addEventListener('wheel', function (event) {
                if (!interactiveMode || wheelLocked || stepCount <= 1) {
                    return;
                }

                var rect = root.getBoundingClientRect();
                var inView = rect.top < window.innerHeight * 0.85 && rect.bottom > window.innerHeight * 0.15;

                if (!inView || Math.abs(event.deltaY) < 18) {
                    return;
                }

                var next = event.deltaY > 0 ? activeStep + 1 : activeStep - 1;

                if (next < 0 || next >= stepCount) {
                    return;
                }

                event.preventDefault();
                showStep(next);
                wheelLocked = true;
                window.setTimeout(function () {
                    wheelLocked = false;
                }, WHEEL_COOLDOWN_MS);
            }, { passive: false });
        }

        showStep(0);
        bindMode();

        window.addEventListener('resize', bindMode);
        window.addEventListener('load', bindMode);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initProcessShowcase);
    } else {
        initProcessShowcase();
    }
})();
