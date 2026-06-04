(function () {
    'use strict';

    function formatSize(bytes) {
        if (!bytes || bytes < 1024) {
            return (bytes || 0) + ' B';
        }
        if (bytes < 1048576) {
            return (Math.round((bytes / 1024) * 10) / 10) + ' KB';
        }
        return (Math.round((bytes / 1048576) * 10) / 10) + ' MB';
    }

    function selectedFiles(form) {
        var files = [];
        form.querySelectorAll('input[type="file"]').forEach(function (input) {
            if (!input.files) {
                return;
            }
            for (var i = 0; i < input.files.length; i++) {
                files.push(input.files[i]);
            }
        });
        return files;
    }

    function spinnerHtml() {
        var bars = '';
        for (var i = 0; i < 12; i++) {
            bars += '<span></span>';
        }
        return '<div class="lineup-ios-spinner scan-upload-overlay__spinner" role="status" aria-hidden="true">' + bars + '</div>';
    }

    function showOverlay(files) {
        if (document.getElementById('scan-upload-overlay')) {
            return;
        }

        var total = 0;
        var listItems = files.map(function (file) {
            total += file.size;
            return '<li><strong>' + escapeHtml(file.name) + '</strong> · ' + formatSize(file.size) + '</li>';
        }).join('');

        var overlay = document.createElement('div');
        overlay.id = 'scan-upload-overlay';
        overlay.className = 'scan-upload-overlay';
        overlay.setAttribute('role', 'alertdialog');
        overlay.setAttribute('aria-modal', 'true');
        overlay.setAttribute('aria-labelledby', 'scan-upload-overlay-title');
        overlay.innerHTML =
            '<div class="scan-upload-overlay__panel">' +
            spinnerHtml() +
            '<h2 id="scan-upload-overlay-title" class="scan-upload-overlay__title">Uploading 3D files…</h2>' +
            '<p class="scan-upload-overlay__detail">' +
            (total > 0
                ? 'Sending ' + formatSize(total) + ' to the server. Large scans can take several minutes.'
                : 'Sending your files to the server. Please wait.') +
            '</p>' +
            (listItems ? '<ul class="scan-upload-overlay__files">' + listItems + '</ul>' : '') +
            '<span class="scan-upload-overlay__hint">Do not close or refresh this page until the upload finishes.</span>' +
            '</div>';

        document.body.appendChild(overlay);
        document.body.classList.add('scan-upload-overlay-open');
    }

    function escapeHtml(text) {
        var div = document.createElement('div');
        div.textContent = text || '';
        return div.innerHTML;
    }

    function clearOverlay() {
        var overlay = document.getElementById('scan-upload-overlay');
        if (overlay) {
            overlay.remove();
        }
        document.body.classList.remove('scan-upload-overlay-open');
        document.querySelectorAll('form[data-scan-upload]').forEach(function (form) {
            delete form.dataset.scanUploading;
            form.querySelectorAll('button[type="submit"], input[type="submit"]').forEach(function (btn) {
                btn.disabled = false;
                btn.removeAttribute('aria-busy');
            });
        });
    }

    function bindForm(form) {
        form.addEventListener('submit', function (e) {
            if (form.dataset.scanUploading === '1') {
                e.preventDefault();
                return;
            }

            if (typeof form.checkValidity === 'function' && !form.checkValidity()) {
                return;
            }

            if (form.dataset.uploadBlocked === '1') {
                return;
            }

            var files = selectedFiles(form);
            if (!files.length) {
                return;
            }

            form.dataset.scanUploading = '1';
            showOverlay(files);

            form.querySelectorAll('button[type="submit"], input[type="submit"]').forEach(function (btn) {
                btn.disabled = true;
                btn.setAttribute('aria-busy', 'true');
            });
        });
    }

    function init() {
        clearOverlay();
        document.querySelectorAll('form[data-scan-upload]').forEach(bindForm);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    window.addEventListener('pageshow', clearOverlay);
})();
