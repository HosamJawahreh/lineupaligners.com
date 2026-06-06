(function ($) {
    'use strict';

    function hideLoader() {
        $('.page-loader-wrapper.lineup-page-loader').fadeOut();
    }

    $(function () {
        setTimeout(hideLoader, 50);
    });

    $(window).on('load', hideLoader);
})(jQuery);
