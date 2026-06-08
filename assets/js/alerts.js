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
        // Only toast popups — never touch confirm modal html containers.
        jQuery('.swal2-container .swal2-popup.swal2-toast #swal2-html-container').empty().hide();
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

            var icon = options.icon || 'question';
            var confirmClass = options.confirmButtonClass || 'lineup-confirm-btn--primary';

            if (icon === 'warning') {
                confirmClass = options.confirmButtonClass || 'lineup-confirm-btn--warning';
            } else if (icon === 'success') {
                confirmClass = options.confirmButtonClass || 'lineup-confirm-btn--success';
            }

            return Swal.fire({
                title: options.title || 'Confirm action',
                text: options.text || 'Are you sure you want to continue?',
                icon: icon,
                showCancelButton: true,
                confirmButtonText: options.confirmButtonText || 'Yes, continue',
                cancelButtonText: options.cancelButtonText || 'Cancel',
                showCloseButton: true,
                buttonsStyling: false,
                reverseButtons: true,
                focusCancel: true,
                heightAuto: true,
                backdrop: 'rgba(9, 36, 60, 0.42)',
                customClass: {
                    container: 'lineup-confirm-container',
                    popup: 'lineup-confirm-popup',
                    title: 'lineup-confirm__title',
                    htmlContainer: 'lineup-confirm__text',
                    actions: 'lineup-confirm__actions',
                    confirmButton: 'lineup-confirm-btn ' + confirmClass,
                    cancelButton: 'lineup-confirm-btn lineup-confirm-btn--muted',
                    closeButton: 'lineup-confirm-close',
                    icon: 'lineup-confirm-swal-icon',
                },
            }).then(function (result) {
                return !!result.isConfirmed;
            });
        },
    };
})();
