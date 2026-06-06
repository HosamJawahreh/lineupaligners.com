(function () {
    'use strict';

    var photosBySet = window.casePhotosGalleryBySet || {};
    var setLabels = window.casePhotosGallerySetLabels || {};
    var downloadAllBase = window.casePhotosDownloadAllBaseUrl || '';
    var currentSetKey = window.casePhotosGalleryDefaultSet || 'original';
    var photos = photosBySet[currentSetKey] || [];

    var modal = document.getElementById('case-photos-gallery-modal');
    var openBtn = document.getElementById('case-photos-gallery-open');
    var scanSetSelect = document.getElementById('case-scan-set-select');
    var modNotesEl = document.getElementById('case-scan-mod-notes');
    var modNotesLabel = document.getElementById('case-scan-mod-notes-label');
    var modNotesText = document.getElementById('case-scan-mod-notes-text');

    if (!openBtn && !scanSetSelect) {
        return;
    }

    var imageEl = document.getElementById('case-photos-gallery-image');
    var emptyEl = document.getElementById('case-photos-gallery-empty');
    var counterEl = document.getElementById('case-photos-gallery-counter');
    var scopeEl = document.getElementById('case-photos-gallery-scope');
    var filenameEl = document.getElementById('case-photos-gallery-filename');
    var progressEl = document.getElementById('case-photos-gallery-progress');
    var downloadCurrent = document.getElementById('case-photos-download-current');
    var downloadAll = document.getElementById('case-photos-download-all');
    var prevBtn = document.getElementById('case-photos-prev');
    var nextBtn = document.getElementById('case-photos-next');
    var thumbsEl = document.getElementById('case-photos-gallery-thumbs');
    var triggerThumbs = document.getElementById('case-photos-gallery-trigger-thumbs');
    var triggerCount = document.getElementById('case-photos-gallery-trigger-count');

    var currentIndex = 0;
    var lastFocus = null;

    function findScanSetMeta(key) {
        var meta = window.caseScanSetsMeta || [];
        for (var i = 0; i < meta.length; i++) {
            if (meta[i].key === key) {
                return meta[i];
            }
        }
        return null;
    }

    function updateScanSetNotes(key) {
        if (!modNotesEl || !modNotesText) {
            return;
        }

        var set = findScanSetMeta(key);
        var notes = set && set.notes ? String(set.notes).trim() : '';

        if (!notes) {
            modNotesEl.classList.add('is-hidden');
            modNotesText.textContent = '';
            return;
        }

        if (modNotesLabel) {
            if (key.indexOf('ref-') === 0) {
                modNotesLabel.textContent = 'Refinement notes';
            } else if (key.indexOf('mod-') === 0) {
                modNotesLabel.textContent = 'Modification notes';
            } else {
                modNotesLabel.textContent = 'Case notes';
            }
        }

        modNotesText.textContent = notes;
        modNotesEl.classList.remove('is-hidden');
    }

    function updateTrigger() {
        if (!openBtn) {
            return;
        }

        if (!photos.length) {
            openBtn.hidden = true;
            return;
        }

        openBtn.hidden = false;

        if (triggerCount) {
            triggerCount.textContent = photos.length + (photos.length === 1 ? ' photo' : ' photos');
        }

        if (triggerThumbs) {
            var html = '';
            photos.slice(0, 3).forEach(function (photo) {
                html += '<img src="' + photo.url + '" alt="">';
            });
            if (photos.length > 3) {
                html += '<span class="case-photos-gallery-trigger__more">+' + (photos.length - 3) + '</span>';
            }
            triggerThumbs.innerHTML = html;
        }
    }

    function updateProgress() {
        if (!progressEl || !photos.length) {
            if (progressEl) {
                progressEl.style.width = '0%';
            }
            return;
        }

        progressEl.style.width = (((currentIndex + 1) / photos.length) * 100) + '%';
    }

    function rebuildThumbs() {
        if (!thumbsEl) {
            return;
        }

        thumbsEl.innerHTML = photos.map(function (photo, index) {
            return '<button type="button" class="case-photos-gallery-thumb' + (index === 0 ? ' is-active' : '') + '" role="tab" aria-selected="' + (index === 0 ? 'true' : 'false') + '" aria-label="Photo ' + (index + 1) + ': ' + photo.name + '" data-index="' + index + '"><img src="' + photo.url + '" alt=""></button>';
        }).join('');

        thumbsEl.querySelectorAll('.case-photos-gallery-thumb').forEach(function (btn) {
            btn.addEventListener('click', function () {
                setIndex(parseInt(btn.getAttribute('data-index'), 10) || 0);
            });
        });
    }

    function applyImage(photo) {
        if (!imageEl || !photo) {
            return;
        }

        imageEl.classList.add('is-changing');

        window.setTimeout(function () {
            imageEl.src = photo.url;
            imageEl.alt = photo.name;
            imageEl.classList.remove('is-hidden', 'is-changing');
            if (emptyEl) {
                emptyEl.classList.add('is-hidden');
            }
        }, 120);
    }

    function setIndex(index, instant) {
        if (!photos.length || !imageEl) {
            return;
        }

        currentIndex = (index + photos.length) % photos.length;
        var photo = photos[currentIndex];

        if (instant) {
            imageEl.src = photo.url;
            imageEl.alt = photo.name;
            imageEl.classList.remove('is-hidden', 'is-changing');
            if (emptyEl) {
                emptyEl.classList.add('is-hidden');
            }
        } else {
            applyImage(photo);
        }

        if (counterEl) {
            counterEl.textContent = (currentIndex + 1) + ' / ' + photos.length;
        }
        if (filenameEl) {
            filenameEl.textContent = photo.name;
        }
        if (downloadCurrent) {
            downloadCurrent.href = photo.download_url;
        }

        if (prevBtn) {
            prevBtn.disabled = photos.length < 2;
        }
        if (nextBtn) {
            nextBtn.disabled = photos.length < 2;
        }

        updateProgress();

        if (thumbsEl) {
            thumbsEl.querySelectorAll('.case-photos-gallery-thumb').forEach(function (btn, i) {
                var active = i === currentIndex;
                btn.classList.toggle('is-active', active);
                btn.setAttribute('aria-selected', active ? 'true' : 'false');
            });
        }
    }

    function applyPhotoSet(setKey) {
        currentSetKey = setKey;
        photos = photosBySet[setKey] || [];

        if (scopeEl) {
            scopeEl.textContent = setLabels[setKey] || setKey;
        }

        if (downloadAll && downloadAllBase) {
            downloadAll.href = downloadAllBase + (downloadAllBase.indexOf('?') >= 0 ? '&' : '?') + 'set=' + encodeURIComponent(setKey);
        }

        updateTrigger();
        updateScanSetNotes(setKey);

        if (modal && !modal.hidden) {
            if (!photos.length) {
                if (imageEl) {
                    imageEl.src = '';
                    imageEl.classList.add('is-hidden');
                }
                if (emptyEl) {
                    emptyEl.classList.remove('is-hidden');
                }
                if (counterEl) {
                    counterEl.textContent = '0 / 0';
                }
                if (filenameEl) {
                    filenameEl.textContent = '';
                }
                if (thumbsEl) {
                    thumbsEl.innerHTML = '';
                }
                updateProgress();
            } else {
                rebuildThumbs();
                setIndex(0);
            }
        }
    }

    function openModal(startIndex) {
        if (!modal || !photos.length) {
            return;
        }

        lastFocus = document.activeElement;
        modal.hidden = false;
        modal.classList.add('is-open');
        document.body.classList.add('case-photos-gallery-open');
        rebuildThumbs();
        setIndex(typeof startIndex === 'number' ? startIndex : 0, true);
    }

    function closeModal() {
        if (!modal) {
            return;
        }

        modal.classList.remove('is-open');
        modal.hidden = true;
        document.body.classList.remove('case-photos-gallery-open');
        if (lastFocus && typeof lastFocus.focus === 'function') {
            lastFocus.focus();
        }
    }

    if (openBtn) {
        openBtn.addEventListener('click', function () {
            openModal(0);
        });
    }

    if (prevBtn) {
        prevBtn.addEventListener('click', function () {
            setIndex(currentIndex - 1);
        });
    }

    if (nextBtn) {
        nextBtn.addEventListener('click', function () {
            setIndex(currentIndex + 1);
        });
    }

    if (modal) {
        modal.querySelectorAll('[data-case-photos-close]').forEach(function (el) {
            el.addEventListener('click', closeModal);
        });
    }

    document.addEventListener('keydown', function (e) {
        if (!modal || modal.hidden) {
            return;
        }

        if (e.key === 'Escape') {
            e.preventDefault();
            closeModal();
            return;
        }

        if (e.key === 'ArrowLeft') {
            e.preventDefault();
            setIndex(currentIndex - 1);
        }

        if (e.key === 'ArrowRight') {
            e.preventDefault();
            setIndex(currentIndex + 1);
        }
    });

    if (scanSetSelect) {
        scanSetSelect.addEventListener('change', function () {
            applyPhotoSet(scanSetSelect.value);
            document.dispatchEvent(new CustomEvent('case-scan-set-changed', {
                detail: { key: scanSetSelect.value },
            }));
        });
    }

    document.addEventListener('case-scan-set-changed', function (e) {
        if (!e.detail || !e.detail.key || e.detail.fromViewer) {
            return;
        }
        if (scanSetSelect && scanSetSelect.value !== e.detail.key) {
            scanSetSelect.value = e.detail.key;
        }
        applyPhotoSet(e.detail.key);
    });

    applyPhotoSet(currentSetKey);
})();
