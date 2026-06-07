(function () {
    'use strict';

    var DESKTOP_MIN = 992;
    var scrollTriggerInstance = null;
    var scrollTimeline = null;

    function initProcessShowcase() {
        var section = document.getElementById('process');
        var root = document.querySelector('.lineup-process-showcase');

        if (!section || !root) {
            return;
        }

        var spacer = root.querySelector('.lineup-process-showcase__pin-spacer');
        var sticky = root.querySelector('.lineup-process-showcase__pin-sticky');
        var tour = root.querySelector('.lineup-process-showcase__tour');
        var tabs = root.querySelectorAll('.lineup-process-showcase__tab');
        var panels = root.querySelectorAll('.lineup-process-showcase__panel');
        var fill = root.querySelector('.lineup-process-showcase__rail-fill');
        var stepCount = tabs.length;
        var scrollEnabled = false;

        if (!spacer || !sticky || !tabs.length || !panels.length) {
            return;
        }

        function clamp(value, min, max) {
            return Math.max(min, Math.min(max, value));
        }

        function prefersReducedMotion() {
            return window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        }

        function isDesktop() {
            return window.innerWidth >= DESKTOP_MIN;
        }

        function hasScrollTrigger() {
            if (typeof gsap === 'undefined' || typeof ScrollTrigger === 'undefined') {
                return false;
            }

            gsap.registerPlugin(ScrollTrigger);

            return true;
        }

        function pinScrollDistance() {
            return window.innerHeight * Math.max(stepCount - 1, 1) * 0.68;
        }

        function resetDrivenStyles() {
            root.removeAttribute('data-active-step');

            panels.forEach(function (panel) {
                panel.style.opacity = '';
                panel.style.transform = '';
                panel.style.visibility = '';
                panel.style.display = '';
                panel.style.pointerEvents = '';
                panel.style.zIndex = '';
            });

            tabs.forEach(function (tab) {
                var desc = tab.querySelector('.lineup-process-showcase__tab-desc');

                if (desc) {
                    desc.style.opacity = '';
                    desc.style.maxHeight = '';
                    desc.style.visibility = '';
                    desc.style.display = '';
                }
            });
        }

        function showStep(index) {
            index = clamp(index, 0, stepCount - 1);

            root.setAttribute('data-active-step', String(index));

            tabs.forEach(function (tab, i) {
                var isActive = i === index;
                var desc = tab.querySelector('.lineup-process-showcase__tab-desc');

                tab.classList.toggle('is-active', isActive);
                tab.setAttribute('aria-selected', isActive ? 'true' : 'false');

                if (desc) {
                    desc.style.display = isActive ? 'block' : 'none';
                    desc.style.visibility = isActive ? 'visible' : 'hidden';
                    desc.style.opacity = isActive ? '1' : '0';
                    desc.style.maxHeight = isActive ? '120px' : '0';
                }
            });

            panels.forEach(function (panel, i) {
                var isActive = i === index;

                panel.classList.toggle('is-active', isActive);
                panel.setAttribute('aria-hidden', isActive ? 'false' : 'true');
                panel.style.display = isActive ? 'block' : 'none';
                panel.style.visibility = isActive ? 'visible' : 'hidden';
                panel.style.opacity = isActive ? '1' : '0';
                panel.style.transform = 'none';
                panel.style.pointerEvents = isActive ? 'auto' : 'none';
                panel.style.zIndex = isActive ? '2' : '0';
            });
        }

        function updateFromProgress(progress) {
            progress = clamp(progress, 0, 1);

            var continuous = progress * Math.max(stepCount - 1, 0);
            var currentStep = clamp(Math.round(continuous), 0, stepCount - 1);

            if (fill && stepCount > 1) {
                fill.style.height = (progress * 100) + '%';
            }

            showStep(currentStep);
        }

        function goToStepDiscrete(index) {
            index = clamp(index, 0, stepCount - 1);
            var progress = stepCount <= 1 ? 0 : index / (stepCount - 1);
            updateFromProgress(progress);
        }

        function scrollToStep(index) {
            if (!scrollEnabled || !scrollTriggerInstance) {
                goToStepDiscrete(index);
                return;
            }

            var progress = stepCount <= 1 ? 0 : index / (stepCount - 1);
            var target = scrollTriggerInstance.start + progress * (scrollTriggerInstance.end - scrollTriggerInstance.start);

            window.scrollTo({
                top: target,
                behavior: prefersReducedMotion() ? 'auto' : 'smooth',
            });
        }

        function destroyScrollTrigger() {
            if (scrollTimeline) {
                scrollTimeline.kill();
                scrollTimeline = null;
            }

            if (scrollTriggerInstance) {
                scrollTriggerInstance.kill();
                scrollTriggerInstance = null;
            }
        }

        function disableTourAos() {
            if (!tour) {
                return;
            }

            tour.removeAttribute('data-aos');
            tour.removeAttribute('data-aos-duration');
            tour.classList.remove('aos-animate');

            if (typeof AOS !== 'undefined' && typeof AOS.refreshHard === 'function') {
                AOS.refreshHard();
            }
        }

        function enableScrollDrive() {
            destroyScrollTrigger();
            resetDrivenStyles();

            var shouldEnable = isDesktop() && stepCount > 1 && !prefersReducedMotion();

            scrollEnabled = shouldEnable;

            root.classList.toggle('is-scroll-driven', shouldEnable);
            root.classList.toggle('is-scroll-trigger', shouldEnable && hasScrollTrigger());
            section.classList.toggle('is-scroll-driven', shouldEnable);

            if (shouldEnable) {
                disableTourAos();
            }

            if (!shouldEnable) {
                goToStepDiscrete(0);
                return;
            }

            if (!hasScrollTrigger()) {
                goToStepDiscrete(0);
                return;
            }

            var proxy = { progress: 0 };

            scrollTimeline = gsap.timeline({
                scrollTrigger: {
                    trigger: spacer,
                    start: 'top top',
                    end: function () {
                        return '+=' + pinScrollDistance();
                    },
                    pin: sticky,
                    pinSpacing: true,
                    anticipatePin: 1,
                    invalidateOnRefresh: true,
                    scrub: true,
                },
            });

            scrollTimeline.to(proxy, {
                progress: 1,
                duration: 1,
                ease: 'none',
                onUpdate: function () {
                    updateFromProgress(proxy.progress);
                },
            });

            scrollTriggerInstance = scrollTimeline.scrollTrigger;
            updateFromProgress(0);
            ScrollTrigger.refresh();
        }

        tabs.forEach(function (tab) {
            tab.addEventListener('click', function () {
                var index = parseInt(tab.getAttribute('data-step-index'), 10) || 0;
                scrollToStep(index);
            });
        });

        showStep(0);
        enableScrollDrive();

        window.addEventListener('resize', enableScrollDrive);
        window.addEventListener('load', function () {
            enableScrollDrive();
            if (typeof ScrollTrigger !== 'undefined') {
                ScrollTrigger.refresh();
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initProcessShowcase);
    } else {
        initProcessShowcase();
    }
})();
