/**
 * Legacy hook — SweetAlert / selectpicker cleanup is handled in forms-fix.js
 */
(function ($) {
    'use strict';
    if (window.LineUpActivateSelectpickers) {
        $(document).ready(window.LineUpActivateSelectpickers);
    }
})(jQuery);
