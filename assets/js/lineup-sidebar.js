/**
 * LineUp sidebar — expand/collapse submenu groups
 */
(function ($) {
    'use strict';

    if (!$) {
        return;
    }

    $(document).on('click', '.lineup-menu-toggle', function (e) {
        e.preventDefault();
        var $li = $(this).closest('.lineup-menu-parent');
        var $sub = $li.children('.lineup-submenu');
        $li.toggleClass('open');
        $sub.slideToggle(150);
    });
})(jQuery);
