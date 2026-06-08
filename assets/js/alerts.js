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

    function escapeHtml(text) {
        return String(text || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function confirmIconClass(icon) {
        if (icon === 'warning' || icon === 'error') {
            return 'lineup-confirm-icon--warning';
        }
        if (icon === 'success') {
            return 'lineup-confirm-icon--success';
        }
        return 'lineup-confirm-icon--question';
    }

    window.AppConfirm = {
        ask: function (options) {
            options = options || {};

            if (typeof Swal === 'undefined') {
                var fallback = options.text || options.title || 'Are you sure?';
                return Promise.resolve(window.confirm(fallback));
            }

            cleanupSelectpickerFromSwal();

            var icon = options.icon || 'question';
            var title = options.title || 'Confirm action';
            var text = options.text || 'Are you sure you want to continue?';
            var confirmLabel = options.confirmButtonText || 'Yes, continue';
            var cancelLabel = options.cancelButtonText || 'Cancel';
            var confirmClass = options.confirmButtonClass || 'lineup-confirm-btn--primary';

            if (icon === 'warning') {
                confirmClass = options.confirmButtonClass || 'lineup-confirm-btn--warning';
            } else if (icon === 'success') {
                confirmClass = options.confirmButtonClass || 'lineup-confirm-btn--success';
            }

            return Swal.fire({
                title: '',
                html:
                    '<div class="lineup-confirm">' +
                    '<div class="lineup-confirm__brand" aria-hidden="true"></div>' +
                    '<div class="lineup-confirm__body">' +
                    '<div class="lineup-confirm__icon-wrap ' + confirmIconClass(icon) + '">' +
                    '<i class="zmdi zmdi-' + (icon === 'success' ? 'check' : icon === 'warning' ? 'alert-triangle' : 'help-outline') + '" aria-hidden="true"></i>' +
                    '</div>' +
                    '<h2 class="lineup-confirm__title">' + escapeHtml(title) + '</h2>' +
                    '<p class="lineup-confirm__text">' + escapeHtml(text) + '</p>' +
                    '</div>' +
                    '<div class="lineup-confirm__actions">' +
                    '<button type="button" class="lineup-confirm-btn lineup-confirm-btn--muted" data-lineup-confirm-cancel>' + escapeHtml(cancelLabel) + '</button>' +
                    '<button type="button" class="lineup-confirm-btn ' + confirmClass + '" data-lineup-confirm-ok>' + escapeHtml(confirmLabel) + '</button>' +
                    '</div>' +
                    '</div>',
                showConfirmButton: true,
                showCancelButton: true,
                confirmButtonText: confirmLabel,
                cancelButtonText: cancelLabel,
                showCloseButton: true,
                buttonsStyling: false,
                focusConfirm: false,
                reverseButtons: true,
                backdrop: 'rgba(9, 36, 60, 0.42)',
                customClass: {
                    container: 'lineup-confirm-container',
                    popup: 'lineup-confirm-popup',
                    closeButton: 'lineup-confirm-close',
                    actions: 'lineup-confirm-swal-actions',
                    confirmButton: 'lineup-confirm-swal-confirm',
                    cancelButton: 'lineup-confirm-swal-cancel',
                },
                didOpen: function (popup) {
                    cleanupSelectpickerFromSwal();

                    var okBtn = popup.querySelector('[data-lineup-confirm-ok]');
                    var cancelBtn = popup.querySelector('[data-lineup-confirm-cancel]');
                    var swalConfirm = Swal.getConfirmButton();
                    var swalCancel = Swal.getCancelButton();

                    if (cancelBtn) {
                        cancelBtn.addEventListener('click', function () {
                            if (swalCancel) {
                                swalCancel.click();
                            }
                        });
                        cancelBtn.focus();
                    }

                    if (okBtn && swalConfirm) {
                        okBtn.addEventListener('click', function () {
                            swalConfirm.click();
                        });
                    }
                },
            }).then(function (result) {
                return !!result.isConfirmed;
            });
        },
    };
})();
