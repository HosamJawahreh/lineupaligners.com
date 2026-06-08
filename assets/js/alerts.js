/**
 * LineUp Aligners — SweetAlert2 toast alerts (v11.26.25)
 * Toast only: no modal, no confirm button, no dropdown.
 */
(function () {
    'use strict';

    if (typeof Swal === 'undefined') {
        return;
    }

    function cleanupSelectpickerFromSwal() {
        if (typeof jQuery === 'undefined') {
            return;
        }

        jQuery('.swal2-container select').each(function () {
            var $el = jQuery(this);
            if ($el.data('selectpicker')) {
                $el.selectpicker('destroy');
            }
        });

        jQuery('.swal2-container .bootstrap-select').remove();
        jQuery('.swal2-container #swal2-html-container').empty().hide();
    }

    var Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        showCancelButton: false,
        showDenyButton: false,
        showCloseButton: true,
        timer: 4000,
        timerProgressBar: true,
        customClass: {
            popup: 'lineup-toast',
            title: 'lineup-toast-title',
        },
        didOpen: function (toast) {
            cleanupSelectpickerFromSwal();

            toast.addEventListener('mouseenter', Swal.stopTimer);
            toast.addEventListener('mouseleave', Swal.resumeTimer);
        },
        didClose: cleanupSelectpickerFromSwal,
    });

    window.AppAlert = {
        success: function (message) {
            cleanupSelectpickerFromSwal();
            return Toast.fire({ icon: 'success', title: message });
        },
        error: function (message) {
            cleanupSelectpickerFromSwal();
            return Toast.fire({ icon: 'error', title: message, timer: 6000 });
        },
        warning: function (message) {
            cleanupSelectpickerFromSwal();
            return Toast.fire({ icon: 'warning', title: message });
        },
        info: function (message) {
            cleanupSelectpickerFromSwal();
            return Toast.fire({ icon: 'info', title: message });
        },
    };

    window.AppConfirm = {
        ask: function (options) {
            options = options || {};

            if (typeof Swal === 'undefined') {
                var fallback = options.text || options.title || 'Are you sure?';
                return Promise.resolve(window.confirm(fallback));
            }

            cleanupSelectpickerFromSwal();

            return Swal.fire({
                title: options.title || 'Confirm action',
                text: options.text || 'Are you sure you want to continue?',
                icon: options.icon || 'question',
                showCancelButton: true,
                confirmButtonText: options.confirmButtonText || 'Yes, continue',
                cancelButtonText: options.cancelButtonText || 'Cancel',
                reverseButtons: true,
                focusCancel: true,
                buttonsStyling: false,
                customClass: {
                    popup: 'lineup-confirm-popup',
                    title: 'lineup-confirm-title',
                    htmlContainer: 'lineup-confirm-text',
                    actions: 'lineup-confirm-actions',
                    confirmButton: 'lineup-confirm-btn lineup-confirm-btn--primary',
                    cancelButton: 'lineup-confirm-btn lineup-confirm-btn--muted',
                    icon: 'lineup-confirm-icon',
                },
            }).then(function (result) {
                return !!result.isConfirmed;
            });
        },
    };
})();
