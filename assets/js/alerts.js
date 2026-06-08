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

    function escapeHtml(text) {
        return String(text || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function confirmTone(icon) {
        if (icon === 'warning' || icon === 'error') {
            return 'lineup-confirm__icon--warning';
        }
        if (icon === 'success') {
            return 'lineup-confirm__icon--success';
        }
        return 'lineup-confirm__icon--neutral';
    }

    function confirmZmdi(icon) {
        if (icon === 'success') {
            return 'zmdi-check';
        }
        if (icon === 'warning' || icon === 'error') {
            return 'zmdi-alert-circle-o';
        }
        return 'zmdi-info-outline';
    }

    window.AppConfirm = {
        ask: function (options) {
            options = options || {};

            if (typeof Swal === 'undefined') {
                var fallback = options.text || options.title || 'Are you sure?';
                return Promise.resolve(window.confirm(fallback));
            }

            var icon = options.icon || 'question';
            var title = options.title || 'Confirm action';
            var text = options.text || 'Are you sure you want to continue?';
            var confirmLabel = options.confirmButtonText || 'Confirm';
            var cancelLabel = options.cancelButtonText || 'Cancel';
            var confirmClass = options.confirmButtonClass || 'lineup-confirm-btn--success';

            return Swal.fire({
                title: '',
                html:
                    '<div class="lineup-confirm" role="document">' +
                    '<div class="lineup-confirm__head">' +
                    '<span class="lineup-confirm__kicker">Confirmation</span>' +
                    '</div>' +
                    '<div class="lineup-confirm__body">' +
                    '<span class="lineup-confirm__icon ' + confirmTone(icon) + '" aria-hidden="true">' +
                    '<i class="zmdi ' + confirmZmdi(icon) + '"></i>' +
                    '</span>' +
                    '<h2 class="lineup-confirm__title" id="lineup-confirm-title">' + escapeHtml(title) + '</h2>' +
                    '<p class="lineup-confirm__text">' + escapeHtml(text) + '</p>' +
                    '</div>' +
                    '<div class="lineup-confirm__foot">' +
                    '<button type="button" class="lineup-confirm-btn lineup-confirm-btn--cancel" data-lineup-confirm-cancel>' +
                    '<i class="zmdi zmdi-close lineup-confirm-btn__icon" aria-hidden="true"></i>' +
                    '<span>' + escapeHtml(cancelLabel) + '</span>' +
                    '</button>' +
                    '<button type="button" class="lineup-confirm-btn ' + confirmClass + '" data-lineup-confirm-ok>' +
                    '<i class="zmdi zmdi-check lineup-confirm-btn__icon" aria-hidden="true"></i>' +
                    '<span>' + escapeHtml(confirmLabel) + '</span>' +
                    '</button>' +
                    '</div>' +
                    '</div>',
                showConfirmButton: true,
                showCancelButton: true,
                confirmButtonText: confirmLabel,
                cancelButtonText: cancelLabel,
                showCloseButton: true,
                buttonsStyling: false,
                reverseButtons: true,
                focusCancel: true,
                heightAuto: true,
                backdrop: 'rgba(15, 23, 42, 0.38)',
                customClass: {
                    container: 'lineup-confirm-container',
                    popup: 'lineup-confirm-popup',
                    closeButton: 'lineup-confirm-close',
                    actions: 'lineup-confirm-swal-actions',
                    confirmButton: 'lineup-confirm-swal-confirm',
                    cancelButton: 'lineup-confirm-swal-cancel',
                },
                didOpen: function (popup) {
                    var okBtn = popup.querySelector('[data-lineup-confirm-ok]');
                    var cancelBtn = popup.querySelector('[data-lineup-confirm-cancel]');
                    var swalConfirm = Swal.getConfirmButton();
                    var swalCancel = Swal.getCancelButton();

                    if (cancelBtn && swalCancel) {
                        cancelBtn.addEventListener('click', function () {
                            swalCancel.click();
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
