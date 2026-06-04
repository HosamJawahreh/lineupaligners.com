(function ($, window, document) {
    'use strict';

    if (window.LINEUP_NOTIFY_INITIALIZED) {
        return;
    }
    window.LINEUP_NOTIFY_INITIALIZED = true;

    var POLL_MS = 5000;
    var SOUND_FALLBACK = '/assets/sounds/notification.mp3';
    var knownIds = new Set();
    var bootstrapped = false;
    var pollTimer = null;
    var audioUnlocked = false;
    var pendingChimes = 0;
    var sharedAudioCtx = null;
    var unlockListenersBound = false;

    function normalizeUrl(url) {
        if (!url) {
            return '';
        }
        try {
            return new URL(url, window.location.origin).href;
        } catch (e) {
            return url;
        }
    }

    function soundUrl() {
        var root = document.getElementById('lineup-notify');
        if (root) {
            var fromAttr = root.getAttribute('data-sound-url');
            if (fromAttr) {
                return normalizeUrl(fromAttr);
            }
        }

        return normalizeUrl(SOUND_FALLBACK);
    }

    function getSharedAudioContext() {
        if (sharedAudioCtx) {
            return sharedAudioCtx;
        }

        var Ctx = window.AudioContext || window.webkitAudioContext;
        if (!Ctx) {
            return null;
        }

        sharedAudioCtx = new Ctx();
        return sharedAudioCtx;
    }

    function getPreloadAudio() {
        var el = document.getElementById('lineup-notify-audio');
        if (!el) {
            el = document.createElement('audio');
            el.id = 'lineup-notify-audio';
            el.preload = 'auto';
            el.setAttribute('playsinline', '');
            document.body.appendChild(el);
        }

        var url = soundUrl();
        var current = el.dataset.lineupSrc || '';

        if (current !== url) {
            el.dataset.lineupSrc = url;
            el.src = url;
            el.load();
        }

        el.volume = 0.85;
        return el;
    }

    function flushPendingChimes() {
        if (!audioUnlocked || pendingChimes < 1) {
            return;
        }

        var count = pendingChimes;
        pendingChimes = 0;

        for (var i = 0; i < count; i += 1) {
            playNotificationSoundNow();
        }
    }

    function unlockAudio() {
        if (audioUnlocked) {
            return Promise.resolve(true);
        }

        var audio = getPreloadAudio();
        var ctx = getSharedAudioContext();
        var wasMuted = audio.muted;

        audio.muted = true;
        audio.volume = 0;

        var tasks = [
            audio.play().then(function () {
                audio.pause();
                audio.currentTime = 0;
            }),
        ];

        if (ctx && ctx.state === 'suspended') {
            tasks.push(ctx.resume());
        }

        return Promise.allSettled(tasks).then(function (results) {
            audio.muted = wasMuted;
            audio.volume = 0.85;

            var htmlAudioOk = results[0].status === 'fulfilled';

            if (htmlAudioOk) {
                audioUnlocked = true;
                try {
                    sessionStorage.setItem('lineup_notify_audio_unlocked', '1');
                } catch (e) {
                    /* ignore */
                }
                flushPendingChimes();
                return true;
            }

            return false;
        });
    }

    function bindAudioUnlock() {
        if (unlockListenersBound) {
            return;
        }
        unlockListenersBound = true;

        var events = ['pointerdown', 'touchstart', 'keydown', 'click'];

        function onUserGesture() {
            unlockAudio().then(function (ok) {
                if (ok) {
                    events.forEach(function (ev) {
                        document.removeEventListener(ev, onUserGesture, true);
                    });
                }
            });
        }

        events.forEach(function (ev) {
            document.addEventListener(ev, onUserGesture, { capture: true, passive: true });
        });
    }

    function playWebAudioChime() {
        if (!audioUnlocked) {
            return;
        }

        var ctx = getSharedAudioContext();
        if (!ctx) {
            return;
        }

        var play = function () {
            var t0 = ctx.currentTime;
            var notes = [
                { freq: 784, start: 0, duration: 0.1 },
                { freq: 988, start: 0.1, duration: 0.1 },
                { freq: 1319, start: 0.2, duration: 0.14 },
            ];

            notes.forEach(function (note) {
                var osc = ctx.createOscillator();
                var gain = ctx.createGain();
                osc.type = 'triangle';
                osc.frequency.setValueAtTime(note.freq, t0 + note.start);
                gain.gain.setValueAtTime(0.0001, t0 + note.start);
                gain.gain.exponentialRampToValueAtTime(0.22, t0 + note.start + 0.015);
                gain.gain.exponentialRampToValueAtTime(0.0001, t0 + note.start + note.duration);
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.start(t0 + note.start);
                osc.stop(t0 + note.start + note.duration + 0.02);
            });
        };

        if (ctx.state === 'suspended') {
            ctx.resume().then(play).catch(function () {});
        } else {
            play();
        }
    }

    function playHtmlAudio(audio) {
        return new Promise(function (resolve, reject) {
            var url = soundUrl();

            if ((audio.dataset.lineupSrc || '') !== url) {
                audio.dataset.lineupSrc = url;
                audio.src = url;
                audio.load();
            }

            audio.muted = false;
            audio.volume = 0.85;

            function startPlayback() {
                audio.currentTime = 0;
                var playPromise = audio.play();
                if (playPromise && typeof playPromise.then === 'function') {
                    playPromise.then(resolve).catch(reject);
                } else {
                    resolve();
                }
            }

            if (audio.readyState >= 3) {
                startPlayback();
                return;
            }

            function onCanPlay() {
                audio.removeEventListener('canplaythrough', onCanPlay);
                audio.removeEventListener('error', onError);
                startPlayback();
            }

            function onError() {
                audio.removeEventListener('canplaythrough', onCanPlay);
                audio.removeEventListener('error', onError);
                reject(new Error('Notification sound failed to load: ' + url));
            }

            audio.addEventListener('canplaythrough', onCanPlay);
            audio.addEventListener('error', onError);

            if (audio.networkState === 3 || !audio.src) {
                audio.src = url;
                audio.load();
            }
        });
    }

    function playNotificationSoundNow() {
        var preload = getPreloadAudio();

        playHtmlAudio(preload)
            .catch(function () {
                var fresh = new window.Audio(soundUrl());
                fresh.preload = 'auto';
                fresh.volume = 0.85;
                return playHtmlAudio(fresh);
            })
            .catch(function () {
                playWebAudioChime();
            });
    }

    function playNotificationSound() {
        if (!audioUnlocked) {
            pendingChimes += 1;
            return;
        }

        playNotificationSoundNow();
    }

    function updateBadge(count) {
        var $badge = $('#lineup-notify-badge');
        var $sidebar = $('#lineup-sidebar-notify-badge');
        if (count > 0) {
            var label = count > 99 ? '99+' : String(count);
            $badge.text(label).prop('hidden', false);
            $sidebar.text(label).prop('hidden', false);
        } else {
            $badge.prop('hidden', true);
            $sidebar.prop('hidden', true);
        }
    }

    function escapeHtml(text) {
        return $('<div>').text(text || '').html();
    }

    function renderDropdownItem(n) {
        var unread = !n.read_at;
        return (
            '<button type="button" class="lineup-notify__item' + (unread ? ' is-unread' : '') + '" data-id="' + escapeHtml(n.id) + '" data-url="' + escapeHtml(n.url) + '">' +
            '<span class="lineup-notify__item-inner">' +
            '<span class="lineup-notify__item-icon"><i class="zmdi ' + escapeHtml(n.icon) + '"></i></span>' +
            '<span><span class="lineup-notify__item-title">' + escapeHtml(n.title) + '</span>' +
            '<span class="lineup-notify__item-body">' + escapeHtml(n.body) + '</span>' +
            '<span class="lineup-notify__item-time">' + escapeHtml(n.created_human) + '</span></span>' +
            '</span></button>'
        );
    }

    function showToast(n) {
        var $existing = $('.lineup-notify-toast');
        $existing.remove();

        var $toast = $(
            '<div class="lineup-notify-toast" role="alert">' +
            '<p class="lineup-notify-toast__title">' + escapeHtml(n.title) + '</p>' +
            '<p class="lineup-notify-toast__body">' + escapeHtml(n.body) + '</p>' +
            '</div>'
        );

        $toast.on('click', function () {
            navigateNotification(n.url, n.id);
            $toast.remove();
        });

        $('body').append($toast);
        setTimeout(function () {
            $toast.fadeOut(300, function () {
                $(this).remove();
            });
        }, 8000);
    }

    function navigateNotification(url, id) {
        if (id) {
            markRead(id, false);
        }
        window.location.href = url;
    }

    function markRead(id, refreshList) {
        var $root = $('#lineup-notify');
        if (!$root.length) {
            return;
        }

        $.ajax({
            method: 'POST',
            url: $root.attr('data-read-base') + '/' + encodeURIComponent(id) + '/read',
            headers: { 'X-CSRF-TOKEN': $root.attr('data-csrf'), Accept: 'application/json' },
        }).done(function (res) {
            if (typeof res.unread_count === 'number') {
                updateBadge(res.unread_count);
            }
            if (refreshList !== false) {
                fetchFeed(true);
            }
        });
    }

    window.lineupNotifyMarkRead = markRead;
    window.lineupNotifyPlaySound = function () {
        unlockAudio().finally(function () {
            playNotificationSound();
        });
    };
    window.lineupNotifyUnlockAudio = unlockAudio;

    function markAllRead() {
        var $root = $('#lineup-notify');
        $.ajax({
            method: 'POST',
            url: $root.attr('data-read-all-url'),
            headers: { 'X-CSRF-TOKEN': $root.attr('data-csrf'), Accept: 'application/json' },
        }).done(function (res) {
            updateBadge(res.unread_count || 0);
            fetchFeed(true);
        });
    }

    function renderList(items) {
        var $list = $('#lineup-notify-list');
        var $empty = $('#lineup-notify-empty');

        if (!items.length) {
            $list.find('.lineup-notify__item').remove();
            $empty.show();
            return;
        }

        $empty.hide();
        $list.find('.lineup-notify__item').remove();
        items.forEach(function (n) {
            $list.append(renderDropdownItem(n));
        });
    }

    function handleNewNotifications(items) {
        items.forEach(function (n) {
            if (!knownIds.has(n.id) && !n.read_at) {
                knownIds.add(n.id);
                playNotificationSound();
                showToast(n);
            } else {
                knownIds.add(n.id);
            }
        });
    }

    function fetchFeed(silent) {
        var $root = $('#lineup-notify');
        if (!$root.length) {
            return;
        }

        $.getJSON($root.attr('data-feed-url'), { limit: 15 })
            .done(function (res) {
                var items = res.notifications || [];
                updateBadge(res.unread_count || 0);
                renderList(items.slice(0, 15));

                if (!bootstrapped) {
                    items.forEach(function (n) {
                        knownIds.add(n.id);
                    });
                    bootstrapped = true;
                    return;
                }

                if (!silent) {
                    handleNewNotifications(items);
                }
            });
    }

    function toggleDropdown(open) {
        var $dd = $('#lineup-notify-dropdown');
        var $btn = $('#lineup-notify-trigger');
        var show = typeof open === 'boolean' ? open : $dd.prop('hidden');

        if (show) {
            unlockAudio();
            $dd.prop('hidden', false);
            $btn.attr('aria-expanded', 'true');
            fetchFeed(true);
        } else {
            $dd.prop('hidden', true);
            $btn.attr('aria-expanded', 'false');
        }
    }

    function init() {
        var $root = $('#lineup-notify');
        if (!$root.length) {
            return;
        }

        getPreloadAudio();
        bindAudioUnlock();

        $('#lineup-notify-trigger').on('click', function (e) {
            e.stopPropagation();
            unlockAudio();
            var $dd = $('#lineup-notify-dropdown');
            toggleDropdown($dd.prop('hidden'));
        });

        $('#lineup-notify-mark-all').on('click', function (e) {
            e.preventDefault();
            markAllRead();
        });

        $(document).on('click', function (e) {
            if (!$(e.target).closest('#lineup-notify').length) {
                toggleDropdown(false);
            }
        });

        $('#lineup-notify-list').on('click', '.lineup-notify__item', function () {
            var id = $(this).data('id');
            var url = $(this).data('url');
            navigateNotification(url, id);
        });

        fetchFeed(true);

        if (pollTimer) {
            window.clearInterval(pollTimer);
        }

        pollTimer = window.setInterval(function () {
            fetchFeed(false);
        }, POLL_MS);

        document.addEventListener('visibilitychange', function () {
            if (!document.hidden) {
                fetchFeed(false);
            }
        });
    }

    $(init);
})(jQuery, window, document);
