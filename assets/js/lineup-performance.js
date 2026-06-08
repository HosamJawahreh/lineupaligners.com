(function () {
    'use strict';

    function prefersReducedMotion() {
        return window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    }

    function onReady(fn) {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', fn, { once: true });
        } else {
            fn();
        }
    }

    function rafThrottle(fn) {
        var frame = null;

        return function () {
            var self = this;
            var args = arguments;

            if (frame !== null) {
                return;
            }

            frame = window.requestAnimationFrame(function () {
                frame = null;
                fn.apply(self, args);
            });
        };
    }

    function initSmoothScroll() {
        if (prefersReducedMotion()) {
            return;
        }

        document.documentElement.classList.add('lineup-smooth-scroll');

        document.addEventListener('click', function (event) {
            var link = event.target.closest('a[href*="#"]');

            if (!link) {
                return;
            }

            var href = link.getAttribute('href');

            if (!href || href === '#') {
                return;
            }

            var hash = '';

            try {
                var url = new URL(href, window.location.href);

                if (url.pathname !== window.location.pathname || url.origin !== window.location.origin) {
                    return;
                }

                hash = url.hash;
            } catch (err) {
                if (href.charAt(0) !== '#') {
                    return;
                }

                hash = href;
            }

            if (!hash || hash === '#') {
                return;
            }

            var target = document.querySelector(hash);

            if (!target) {
                return;
            }

            event.preventDefault();
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });

            if (window.history && window.history.replaceState) {
                window.history.replaceState(null, '', hash);
            }
        });
    }

    function isViewerPanelVisible(root) {
        var panel = root.closest('.case-study-panel');

        if (!panel) {
            return true;
        }

        return !panel.hasAttribute('hidden') && panel.classList.contains('is-active');
    }

    function initCaseScanViewerLifecycle() {
        var root = document.querySelector('.case-scan-viewer-root');

        if (!root) {
            return;
        }

        var intersecting = true;

        function sync() {
            if (!window.caseScanViewer) {
                return;
            }

            var shouldRun = !document.hidden
                && intersecting
                && isViewerPanelVisible(root);

            if (shouldRun) {
                window.caseScanViewer.resume();
                window.caseScanViewer.resize();
            } else {
                window.caseScanViewer.pause();
            }
        }

        document.addEventListener('visibilitychange', sync, { passive: true });

        var panel = root.closest('.case-study-panel');

        if (panel && typeof MutationObserver !== 'undefined') {
            var observer = new MutationObserver(sync);
            observer.observe(panel, { attributes: true, attributeFilter: ['hidden', 'class'] });
        }

        if (typeof IntersectionObserver !== 'undefined') {
            var io = new IntersectionObserver(function (entries) {
                intersecting = entries.some(function (entry) {
                    return entry.isIntersecting;
                });
                sync();
            }, { root: null, rootMargin: '80px 0px', threshold: 0.04 });

            io.observe(root);
        }

        window.lineupSyncCaseScanViewer = sync;
        sync();
    }

    function initScrollSurfaces() {
        var selector = [
            '.cases-table-scroll',
            '.ig-chat-thread',
            '.case-study-tabs-scroll',
            '.case-scan-files__panel',
            '.case-scan-toolbar-wrap',
        ].join(',');

        document.querySelectorAll(selector).forEach(function (el) {
            el.classList.add('lineup-scroll-surface');
        });
    }

    function initAosPerformance() {
        if (typeof AOS === 'undefined') {
            return;
        }

        var refresh = rafThrottle(function () {
            if (typeof AOS.refreshHard === 'function') {
                AOS.refreshHard();
            } else if (typeof AOS.refresh === 'function') {
                AOS.refresh();
            }
        });

        window.addEventListener('resize', refresh, { passive: true });
        window.addEventListener('orientationchange', refresh, { passive: true });
    }

    function initScrollTriggerPerf() {
        if (typeof ScrollTrigger === 'undefined' || typeof ScrollTrigger.config !== 'function') {
            return;
        }

        try {
            ScrollTrigger.config({
                limitCallbacks: true,
                ignoreMobileResize: true,
            });
        } catch (err) {
            /* ignore */
        }
    }

    window.lineupRafThrottle = rafThrottle;

    onReady(function () {
        initSmoothScroll();
        initCaseScanViewerLifecycle();
        initScrollSurfaces();
        initAosPerformance();
        initScrollTriggerPerf();
    });
}());
