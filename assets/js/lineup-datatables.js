/**
 * LineUp — DataTables init for all .lineup-datatable tables
 */
(function ($) {
    'use strict';

    window.LineUpInitDataTables = function (selector) {
        if (!$.fn.DataTable) {
            return;
        }

        var $tables = selector ? $(selector) : $('.lineup-datatable');

        $tables.each(function () {
            var $table = $(this);

            if ($.fn.dataTable.isDataTable(this)) {
                return;
            }

            if ($table.find('tbody tr').length === 0) {
                return;
            }

            var orderCol = $table.data('order-col');
            var orderDir = $table.data('order-dir') || 'desc';
            var pageLen = parseInt($table.data('page-length'), 10) || 20;
            var noSort = $table.data('no-sort-columns');

            var columnDefs = [];
            if (noSort !== undefined && noSort !== '') {
                var targets = String(noSort).split(',').map(function (n) {
                    return parseInt(n.trim(), 10);
                }).filter(function (n) {
                    return !isNaN(n);
                });
                if (targets.length) {
                    columnDefs.push({ orderable: false, targets: targets });
                }
            }

            var responsive = $table.data('responsive');
            if (responsive === false || responsive === 'false') {
                responsive = false;
            } else {
                responsive = true;
            }

            var options = {
                responsive: responsive,
                autoWidth: false,
                deferRender: true,
                pageLength: pageLen,
                lengthMenu: [[10, 20, 50, 100, -1], [10, 20, 50, 100, 'All']],
                order: orderCol !== undefined ? [[orderCol, orderDir]] : [],
                columnDefs: columnDefs,
                language: {
                    search: '',
                    searchPlaceholder: 'Search in table…',
                    lengthMenu: 'Show _MENU_ entries',
                    info: 'Showing _START_ to _END_ of _TOTAL_ entries',
                    infoEmpty: 'No entries',
                    zeroRecords: 'No matching records found',
                    paginate: {
                        first: 'First',
                        last: 'Last',
                        next: 'Next',
                        previous: 'Previous',
                    },
                },
                dom: "<'lineup-dt-top'lf>rt<'lineup-dt-bottom'ip>",
                drawCallback: function () {
                    var $wrap = $(this.api().table().container());
                    $wrap.find('.dataTables_paginate .paginate_button.previous').attr('aria-label', 'Previous page');
                    $wrap.find('.dataTables_paginate .paginate_button.next').attr('aria-label', 'Next page');
                    $wrap.find('.dataTables_paginate .paginate_button.first').attr('aria-label', 'First page');
                    $wrap.find('.dataTables_paginate .paginate_button.last').attr('aria-label', 'Last page');
                    $wrap.find('.dataTables_paginate .page-item.previous .page-link').attr('aria-label', 'Previous page');
                    $wrap.find('.dataTables_paginate .page-item.next .page-link').attr('aria-label', 'Next page');

                    if (typeof window.LineUpActivateSelectpickers === 'function') {
                        window.LineUpActivateSelectpickers();
                    }
                    if (typeof window.LineUpInitCasesActions === 'function' && $table.closest('.cases-panel').length) {
                        window.LineUpInitCasesActions(this.api().table().container());
                    }
                },
            };

            $table.DataTable(options);
        });
    };

    $(function () {
        window.LineUpInitDataTables();
    });
})(jQuery);
