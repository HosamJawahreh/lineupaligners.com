(function ($) {
    'use strict';

    $(function () {
        var $toggle = $('#menu-toggle');
        var $menu = $('#pbmit-top-menu');

        if (!$toggle.length) {
            return;
        }

        $toggle.attr({
            'aria-controls': 'pbmit-top-menu',
            'aria-expanded': 'false',
        });

        $toggle.on('click', function () {
            var expanded = $toggle.attr('aria-expanded') === 'true';
            $toggle.attr('aria-expanded', expanded ? 'false' : 'true');
            $('body').toggleClass('lineup-mobile-nav-open', !expanded);
        });

        $(document).on('click', '#pbmit-top-menu a', function () {
            if (window.innerWidth < 992) {
                $toggle.attr('aria-expanded', 'false');
                $('body').removeClass('lineup-mobile-nav-open');
            }
        });
    });
})(jQuery);
