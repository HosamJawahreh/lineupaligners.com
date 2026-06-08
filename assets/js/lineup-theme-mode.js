(function () {
    'use strict';

    var STORAGE_KEY = 'lineup-color-mode';
    var body = document.body;

    if (!body || !body.classList.contains('lineup-app')) {
        return;
    }

    function normalizeMode(mode) {
        return mode === 'dark' ? 'dark' : 'light';
    }

    function getDefaultMode() {
        return normalizeMode(body.getAttribute('data-default-color-mode') || 'light');
    }

    function getStoredMode() {
        try {
            var stored = localStorage.getItem(STORAGE_KEY);
            return stored ? normalizeMode(stored) : null;
        } catch (err) {
            return null;
        }
    }

    function applyMode(mode) {
        var next = normalizeMode(mode);
        body.classList.remove('lineup-color-light', 'lineup-color-dark');
        body.classList.add('lineup-color-' + next);
        document.documentElement.style.colorScheme = next;

        var toggle = document.getElementById('lineup-theme-toggle');
        if (toggle) {
            toggle.setAttribute('aria-pressed', next === 'dark' ? 'true' : 'false');
            toggle.setAttribute('title', next === 'dark' ? 'Switch to light mode' : 'Switch to dark mode');
            toggle.setAttribute('aria-label', toggle.getAttribute('title'));
        }

        body.dispatchEvent(new CustomEvent('lineup-color-mode-change', {
            detail: { mode: next },
        }));
    }

    function persistMode(mode) {
        try {
            localStorage.setItem(STORAGE_KEY, normalizeMode(mode));
        } catch (err) {
            /* ignore */
        }
    }

    function initMode() {
        applyMode(getStoredMode() || getDefaultMode());
    }

    function bindToggle() {
        var toggle = document.getElementById('lineup-theme-toggle');
        if (!toggle) {
            return;
        }

        toggle.addEventListener('click', function (event) {
            event.preventDefault();
            var next = body.classList.contains('lineup-color-dark') ? 'light' : 'dark';
            applyMode(next);
            persistMode(next);
        });
    }

    initMode();
    bindToggle();
})();
