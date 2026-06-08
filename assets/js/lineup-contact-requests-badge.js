(function ($) {
    'use strict';

    var POLL_MS = 30000;

    function updateBadge(count) {
        var $badge = $('#lineup-sidebar-contact-badge');
        if (!$badge.length) {
            return;
        }

        if (count > 0) {
            var label = count > 99 ? '99+' : String(count);
            $badge.text(label).prop('hidden', false);
        } else {
            $badge.prop('hidden', true);
        }
    }

    function pollUnreadCount() {
        $.getJSON('/admin/contact-requests/unread-count')
            .done(function (payload) {
                updateBadge(parseInt(payload.count, 10) || 0);
            });
    }

    $(function () {
        if (!$('#lineup-sidebar-contact-badge').length) {
            return;
        }

        pollUnreadCount();
        setInterval(pollUnreadCount, POLL_MS);
    });
})(jQuery);
