/**
 * Cases table — action dropdowns & download controls
 */
(function ($) {
    'use strict';

    if (!$) {
        return;
    }

    function initCasesTableDropdowns(container) {
        var $root = container ? $(container) : $(document);

        $root.find('.cases-actions [data-toggle="dropdown"]').each(function () {
            var $toggle = $(this);

            if ($toggle.data('cases-dropdown-ready')) {
                return;
            }

            $toggle.addClass('dropdown-toggle');
            $toggle.attr('data-display', 'static');
            $toggle.attr('aria-haspopup', 'true');
            $toggle.attr('aria-expanded', 'false');

            if ($toggle.is('a')) {
                $toggle.attr('href', 'javascript:void(0);');
            }

            $toggle.data('cases-dropdown-ready', true);
        });
    }

    window.LineUpInitCasesActions = initCasesTableDropdowns;

    $(function () {
        initCasesTableDropdowns();

        $(document).on('click', '.cases-actions .dropdown-menu', function (e) {
            e.stopPropagation();
        });

        var $table = $('#cases-table');
        if ($table.length && $.fn.DataTable) {
            $table.on('draw.dt', function () {
                var wrapper = $(this).closest('.dataTables_wrapper')[0];
                initCasesTableDropdowns(wrapper || document);
            });
        }
    });
})(jQuery);
