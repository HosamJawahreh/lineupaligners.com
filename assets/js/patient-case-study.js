(function ($) {
    'use strict';

    function tabStorageKey() {
        return 'case_study_tab_' + window.location.pathname;
    }

    function persistActiveTab(tabId) {
        if (!tabId) {
            return;
        }

        try {
            sessionStorage.setItem(tabStorageKey(), tabId);
        } catch (err) {
            /* ignore quota / private mode */
        }

        var url = new URL(window.location.href);

        if (url.searchParams.get('tab') === tabId) {
            return;
        }

        url.searchParams.set('tab', tabId);
        window.history.replaceState({ caseStudyTab: tabId }, '', url);
    }

    function activateTab(tabId) {
        var $tab = $('.case-study-tab[data-tab="' + tabId + '"]');
        var $panel = $('#case-panel-' + tabId);

        if (!$tab.length || !$panel.length) {
            return false;
        }

        $('.case-study-tab').removeClass('is-active').attr('aria-selected', 'false');
        $tab.addClass('is-active').attr('aria-selected', 'true');

        $('.case-study-panel').removeClass('is-active').attr('hidden', true);
        $panel.addClass('is-active').removeAttr('hidden');

        if (tabId === 'view-data' && window.caseScanViewer) {
            window.caseScanViewer.resume();
            window.caseScanViewer.resize();
        } else if (window.caseScanViewer) {
            window.caseScanViewer.pause();
        }

        if (typeof window.lineupSyncCaseScanViewer === 'function') {
            window.lineupSyncCaseScanViewer();
        }

        if (tabId === 'messages' && window.caseChatOnTabShow) {
            window.caseChatOnTabShow();
        }

        return true;
    }

    function initTabs() {
        $('.case-study-tab').on('click', function () {
            var tabId = $(this).data('tab');

            if (activateTab(tabId)) {
                persistActiveTab(tabId);
            }
        });
    }

    function initChat() {
        var $chat = $('#case-chat');
        if (!$chat.length || ($chat.data('can-chat') !== 1 && $chat.data('can-chat') !== '1')) {
            return;
        }

        var messagesUrl = $chat.data('messages-url');
        var sendUrl = $chat.data('send-url');
        var csrf = $chat.data('csrf');
        var pollMs = parseInt($chat.data('poll-ms'), 10) || 3500;
        var $list = $('#case-chat-messages');
        var $form = $('#case-chat-form');
        var $input = $('#case-chat-input');
        var $file = $('#case-chat-file');
        var $attachBtn = $('#case-chat-attach-btn');
        var $preview = $('#case-chat-preview');
        var $previewThumb = $('#case-chat-preview-thumb');
        var $previewName = $('#case-chat-preview-name');
        var $previewMeta = $('#case-chat-preview-meta');
        var $previewRemove = $('#case-chat-preview-remove');
        var $empty = $('#case-chat-empty');
        var $live = $('#case-chat-live');
        var lastMessageId = 0;
        var seenMessageId = parseInt($chat.data('seen-msg-id'), 10) || 0;
        var pollTimer = null;
        var isPolling = false;
        var pendingFile = null;
        var previewObjectUrl = null;

        function escapeHtml(text) {
            return $('<div>').text(text || '').html();
        }

        function formatFileSize(bytes) {
            if (!bytes || bytes < 1024) {
                return (bytes || 0) + ' B';
            }
            if (bytes < 1048576) {
                return (Math.round(bytes / 1024 * 10) / 10) + ' KB';
            }
            return (Math.round(bytes / 1048576 * 10) / 10) + ' MB';
        }

        function fileExtension(name) {
            var parts = (name || '').split('.');
            return parts.length > 1 ? parts.pop().toLowerCase() : 'file';
        }

        function fileIconKind(ext) {
            if (ext === 'pdf') return 'pdf';
            if (['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg'].indexOf(ext) >= 0) return 'image';
            if (['doc', 'docx', 'rtf'].indexOf(ext) >= 0) return 'word';
            if (['xls', 'xlsx', 'csv'].indexOf(ext) >= 0) return 'sheet';
            if (['zip', 'rar', '7z', 'tar', 'gz'].indexOf(ext) >= 0) return 'archive';
            if (['stl', 'obj', 'ply'].indexOf(ext) >= 0) return 'scan';
            return 'file';
        }

        function fileIconClass(icon) {
            var map = {
                pdf: 'zmdi-collection-pdf',
                word: 'zmdi-file-text',
                sheet: 'zmdi-grid',
                archive: 'zmdi-folder',
                scan: 'zmdi-scanner',
                image: 'zmdi-image',
                file: 'zmdi-file',
            };
            return map[icon] || map.file;
        }

        function buildAttachmentIconHtml(att, icon) {
            if (att.is_image && att.url) {
                return (
                    '<img src="' + escapeHtml(att.url) + '" alt="" class="ig-attach-card__thumb" loading="lazy" ' +
                    'onerror="this.classList.add(\'is-broken\');this.removeAttribute(\'src\');">' +
                    '<i class="zmdi zmdi-image ig-attach-card__thumb-fallback" aria-hidden="true"></i>'
                );
            }

            return '<i class="zmdi ' + fileIconClass(icon) + '"></i>';
        }

        function buildAttachmentHtml(att) {
            if (!att || !att.download_url) {
                return '';
            }

            var icon = att.icon || fileIconKind((att.extension || att.name || '').toLowerCase());
            if (att.is_image) {
                icon = 'image';
            }
            var ext = escapeHtml(att.extension || fileExtension(att.name).toUpperCase());
            var sizePart = att.size ? escapeHtml(att.size) + ' · ' : '';
            var hasThumb = att.is_image && att.url;
            var iconClass = 'ig-attach-card__icon ig-attach-card__icon--' + icon + (hasThumb ? ' ig-attach-card__icon--has-thumb' : '');
            var extBadge = hasThumb ? '' : '<span class="ig-attach-card__ext">' + ext + '</span>';

            return (
                '<div class="ig-bubble ig-bubble--attach">' +
                '<a href="' + escapeHtml(att.download_url) + '" class="ig-attach-card" download title="Download ' + escapeHtml(att.name) + '">' +
                '<span class="' + iconClass + '" aria-hidden="true">' +
                buildAttachmentIconHtml(att, icon) +
                extBadge + '</span>' +
                '<span class="ig-attach-card__body">' +
                '<span class="ig-attach-card__name">' + escapeHtml(att.name) + '</span>' +
                '<span class="ig-attach-card__meta">' + sizePart + '<span class="ig-attach-card__action-label">Download</span></span>' +
                '</span>' +
                '<span class="ig-attach-card__dl" aria-hidden="true"><i class="zmdi zmdi-download"></i></span>' +
                '</a></div>'
            );
        }

        function getLastMessageId() {
            var max = 0;
            $list.find('.ig-msg[data-msg-id]').each(function () {
                var id = parseInt($(this).attr('data-msg-id'), 10);
                if (id > max) {
                    max = id;
                }
            });
            return max;
        }

        function scrollToBottom() {
            if ($list.length && $list[0]) {
                $list[0].scrollTop = $list[0].scrollHeight;
            }
        }

        function buildMessageHtml(data, animate) {
            if (!data || !data.id) {
                return null;
            }

            var mine = data.is_mine ? 'ig-msg--mine' : 'ig-msg--theirs';
            var animClass = animate ? ' ig-msg--new' : '';
            var avatarHtml = '';

            if (!data.is_mine) {
                avatarHtml =
                    '<div class="ig-msg-avatar"><img src="' + escapeHtml(data.avatar_url) + '" alt=""></div>';
            }

            var bubblesHtml = '';

            if (data.attachment) {
                bubblesHtml += buildAttachmentHtml(data.attachment);
            }

            if (data.body) {
                bubblesHtml +=
                    '<div class="ig-bubble ig-bubble--text"><p>' + escapeHtml(data.body) + '</p></div>';
            }

            var metaHtml = '<div class="ig-msg-meta">';
            if (data.show_seen) {
                metaHtml += '<span class="ig-msg-seen">Seen</span>';
            }
            metaHtml += '<span class="ig-msg-time">' + escapeHtml(data.time) + '</span></div>';

            return (
                '<div class="ig-msg ' + mine + animClass + '" data-msg-id="' + data.id + '">' +
                avatarHtml +
                '<div class="ig-msg-stack">' +
                '<div class="ig-msg-bubbles">' + bubblesHtml + '</div>' +
                metaHtml +
                '</div></div>'
            );
        }

        function applySeenIndicator(messageId) {
            if (!messageId) {
                return;
            }

            $list.find('.ig-msg--mine .ig-msg-seen').remove();
            var $target = $list.find('.ig-msg--mine[data-msg-id="' + messageId + '"]');
            if (!$target.length) {
                return;
            }

            var $meta = $target.find('.ig-msg-meta');
            if (!$meta.length) {
                $meta = $('<div class="ig-msg-meta"></div>');
                $target.find('.ig-msg-stack').append($meta);
            }

            if (!$meta.find('.ig-msg-seen').length) {
                $meta.prepend('<span class="ig-msg-seen">Seen</span>');
            }
        }

        function appendMessage(data, animate) {
            if ($list.find('[data-msg-id="' + data.id + '"]').length) {
                return;
            }

            if ($empty.length) {
                $empty.remove();
            }

            var html = buildMessageHtml(data, animate);
            if (html) {
                $list.append(html);
                if (data.id > lastMessageId) {
                    lastMessageId = data.id;
                }
                scrollToBottom();
            }
        }

        function pollMessages() {
            if (isPolling || document.hidden) {
                return;
            }

            isPolling = true;

            $.ajax({
                url: messagesUrl,
                method: 'GET',
                data: { after: lastMessageId },
                dataType: 'json',
            })
                .done(function (res) {
                    if (res.seen_message_id && res.seen_message_id !== seenMessageId) {
                        seenMessageId = res.seen_message_id;
                        applySeenIndicator(seenMessageId);
                    }

                    if (!res.messages || !res.messages.length) {
                        return;
                    }

                    var hasIncoming = false;
                    res.messages.forEach(function (msg) {
                        if (!msg.is_mine) {
                            hasIncoming = true;
                        }
                        appendMessage(msg, !msg.is_mine);
                        if (msg.show_seen) {
                            seenMessageId = msg.id;
                            applySeenIndicator(seenMessageId);
                        }
                    });

                    if (hasIncoming && $live.length) {
                        $live.addClass('ig-chat-live--pulse');
                        setTimeout(function () {
                            $live.removeClass('ig-chat-live--pulse');
                        }, 1200);
                    }
                })
                .always(function () {
                    isPolling = false;
                });
        }

        function revokePreviewUrl() {
            if (previewObjectUrl) {
                URL.revokeObjectURL(previewObjectUrl);
                previewObjectUrl = null;
            }
        }

        function clearPreview() {
            pendingFile = null;
            $file.val('');
            revokePreviewUrl();
            $previewThumb.empty().removeClass(function (i, c) {
                return (c.match(/ig-compose-preview__thumb--\S+/g) || []).join(' ');
            });
            $previewMeta.text('');
            $preview.addClass('d-none');
        }

        function showPreview(file) {
            pendingFile = file;
            var ext = fileExtension(file.name);
            var icon = fileIconKind(ext);
            var isImage = file.type && file.type.indexOf('image/') === 0;

            revokePreviewUrl();
            $previewThumb.empty().removeClass(function (i, c) {
                return (c.match(/ig-compose-preview__thumb--\S+/g) || []).join(' ');
            });
            $previewThumb.addClass('ig-compose-preview__thumb--' + icon);

            if (isImage) {
                previewObjectUrl = URL.createObjectURL(file);
                $previewThumb.append($('<img>').attr('src', previewObjectUrl).attr('alt', ''));
            } else {
                $previewThumb.text(ext.toUpperCase());
            }

            $previewName.text(file.name);
            $previewMeta.text(formatFileSize(file.size) + ' · Ready to send');
            $preview.removeClass('d-none');
        }

        $attachBtn.on('click', function () {
            $file.trigger('click');
        });

        $file.on('change', function () {
            var file = this.files && this.files[0];
            if (file) {
                showPreview(file);
            } else {
                clearPreview();
            }
        });

        $previewRemove.on('click', function () {
            clearPreview();
        });

        lastMessageId = getLastMessageId();
        scrollToBottom();

        window.caseChatOnTabShow = function () {
            scrollToBottom();
            pollMessages();
        };

        pollTimer = setInterval(pollMessages, pollMs);

        document.addEventListener('visibilitychange', function () {
            if (!document.hidden) {
                pollMessages();
            }
        });

        $form.on('submit', function (e) {
            e.preventDefault();

            var body = $.trim($input.val());
            var file = pendingFile || ($file[0].files && $file[0].files[0]);

            if (!body && !file) {
                return;
            }

            var formData = new FormData();
            formData.append('_token', csrf);
            if (body) {
                formData.append('body', body);
            }
            if (file) {
                formData.append('attachment', file);
            }

            var $send = $form.find('.ig-chat-send');
            $send.prop('disabled', true);

            $.ajax({
                url: sendUrl,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
            })
                .done(function (res) {
                    if (res.message) {
                        appendMessage(res.message, false);
                        if (res.message.show_seen) {
                            seenMessageId = res.message.id;
                            applySeenIndicator(seenMessageId);
                        }
                    }
                    $input.val('');
                    clearPreview();
                    $input.focus();
                })
                .fail(function (xhr) {
                    var msg = 'Could not send message. Try again.';
                    var json = xhr.responseJSON;
                    if (json) {
                        if (json.error) {
                            msg = json.error;
                        } else if (json.errors) {
                            var first = Object.keys(json.errors)[0];
                            if (first && json.errors[first][0]) {
                                msg = json.errors[first][0];
                            }
                        } else if (json.message && json.message !== 'Server Error') {
                            msg = json.message;
                        }
                    } else if (xhr.status === 419) {
                        msg = 'Session expired. Refresh the page and try again.';
                    } else if (xhr.status === 403) {
                        msg = 'You are not allowed to send messages on this case.';
                    }
                    if (window.Swal) {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'error',
                            title: msg,
                            showConfirmButton: false,
                            timer: 4500,
                        });
                    }
                })
                .always(function () {
                    $send.prop('disabled', false);
                });
        });
    }

    function openTabFromParams() {
        var params = new URLSearchParams(window.location.search);
        var tabId = params.get('tab') || window.CASE_STUDY_OPEN_TAB;
        var mfgStage = params.get('stage') || window.CASE_STUDY_MFG_STAGE;

        if (!tabId) {
            try {
                tabId = sessionStorage.getItem(tabStorageKey());
            } catch (err) {
                tabId = null;
            }
        }

        if (!tabId) {
            return;
        }

        if (activateTab(tabId)) {
            persistActiveTab(tabId);
        }

        if (tabId === 'manufacture-plan' && mfgStage) {
            var $stageBtn = $('[data-mfg-stage-btn][data-stage="' + mfgStage + '"]');
            if ($stageBtn.length) {
                $stageBtn.trigger('click');
            }
        }
    }

    function initSummaryNotes() {
        $('[data-case-summary-notes]').each(function () {
            var $wrap = $(this);
            var $content = $wrap.find('.case-summary-notes__content');
            var $toggle = $wrap.find('.case-summary-notes__toggle');

            if (!$content.length || !$toggle.length) {
                return;
            }

            function syncToggle() {
                var expanded = $wrap.hasClass('is-expanded');
                var overflows = $content[0].scrollHeight > $content[0].clientHeight + 2;

                if (expanded || overflows) {
                    $toggle.removeAttr('hidden');
                } else {
                    $toggle.attr('hidden', true);
                }

                $toggle.attr('aria-expanded', expanded ? 'true' : 'false');
                $toggle.text(expanded ? 'Show less' : 'Show full notes');
            }

            $toggle.on('click', function () {
                $wrap.toggleClass('is-expanded');
                syncToggle();
            });

            syncToggle();
            $(window).on('resize.caseSummaryNotes', syncToggle);
        });
    }

    function initCaseSummaryDossier() {
        var $card = $('[data-case-summary-dossier]');
        if (!$card.length) {
            return;
        }

        var $toggle = $card.find('.case-summary-card__mobile-toggle');
        var $panel = $card.find('.case-summary-card__expandable');
        var mq = window.matchMedia('(max-width: 767px)');

        function isMobile() {
            return mq.matches;
        }

        function setExpanded(expanded) {
            $card.toggleClass('is-open', expanded);
            $toggle.attr('aria-expanded', expanded ? 'true' : 'false');

            if (expanded) {
                $panel.removeAttr('hidden');
            } else {
                $panel.attr('hidden', true);
            }
        }

        function syncLayout() {
            if (!isMobile()) {
                $card.removeClass('is-open');
                $toggle.attr('aria-expanded', 'true');
                $panel.removeAttr('hidden');
                return;
            }

            if (!$card.data('dossier-mobile-init')) {
                setExpanded(false);
                $card.data('dossier-mobile-init', true);
            }
        }

        $toggle.on('click', function () {
            if (!isMobile()) {
                return;
            }

            setExpanded(!$card.hasClass('is-open'));
        });

        if (typeof mq.addEventListener === 'function') {
            mq.addEventListener('change', syncLayout);
        } else if (typeof mq.addListener === 'function') {
            mq.addListener(syncLayout);
        }

        $(window).on('resize.caseSummaryDossier', syncLayout);
        syncLayout();
    }

    $(function () {
        initTabs();
        initChat();
        initSummaryNotes();
        initCaseSummaryDossier();
        openTabFromParams();
    });
})(jQuery);
