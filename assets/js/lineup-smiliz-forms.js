(function ($) {
    'use strict';

    var cfg = window.lineupSmilizConfig || {};

    function token() {
        var meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    function ensureStatus($form) {
        var $status = $form.find('.message-status').first();
        if (!$status.length) {
            $status = $('<div class="message-status mt-3" role="status" aria-live="polite"></div>');
            $form.append($status);
        }

        return $status;
    }

    function showStatus($form, message, isError) {
        ensureStatus($form)
            .toggleClass('text-danger', !!isError)
            .toggleClass('text-success', !isError)
            .html(message);
    }

    function setLoading($form, loading) {
        var $btn = $form.find('button.pbmit-btn, button.pbmit-form-btn, button[type="submit"]').first();
        $form.find('.form-btn-loader').toggleClass('d-none', !loading);
        $btn.prop('disabled', loading).attr('aria-busy', loading ? 'true' : 'false');
    }

    function validationMessage(xhr) {
        var payload = xhr.responseJSON || {};
        if (payload.errors) {
            return Object.values(payload.errors).flat().join(' ');
        }

        return payload.message || cfg.errorMessage || 'Something went wrong. Please try again.';
    }

    function postInquiry($form, payload) {
        setLoading($form, true);
        showStatus($form, '', false);

        $.ajax({
            url: cfg.inquiryUrl || '/website/inquiry',
            method: 'POST',
            data: payload,
            dataType: 'json',
        })
            .done(function (res) {
                showStatus($form, res.message || cfg.successMessage || 'Thank you.', false);
                $form[0].reset();
            })
            .fail(function (xhr) {
                showStatus($form, validationMessage(xhr), true);
            })
            .always(function () {
                setLoading($form, false);
            });
    }

    function submitContact($form, formType) {
        var payload = {
            _token: token(),
            name: $.trim($form.find('[name="name"]').val() || ''),
            email: $.trim($form.find('[name="email"]').val() || ''),
            phone: $.trim($form.find('[name="phone"]').val() || ''),
            subject: $.trim($form.find('[name="subject"]').val() || ''),
            message: $.trim($form.find('[name="message"]').val() || ''),
            form_type: formType || 'contact',
            website_hp: '',
        };

        if (!payload.name || !payload.email || !payload.message) {
            showStatus($form, cfg.requiredMessage || 'Please fill in all required fields.', true);
            return;
        }

        postInquiry($form, payload);
    }

    function submitNewsletter($form) {
        var email = $.trim($form.find('[name="email"], [name="EMAIL"]').val() || '');

        if (!email) {
            showStatus($form, cfg.requiredMessage || 'Please fill in all required fields.', true);
            return;
        }

        postInquiry($form, {
            _token: token(),
            email: email,
            form_type: 'newsletter',
            website_hp: '',
        });
    }

    $(function () {
        $('#contact-form, form.contact-form[data-lineup-inquiry-form="1"]').each(function () {
            var $form = $(this);
            $form.attr('action', cfg.inquiryUrl || $form.attr('action') || '/website/inquiry');
            $form.off('submit.lineup').on('submit.lineup', function (e) {
                e.preventDefault();
                e.stopImmediatePropagation();
                submitContact($form, $form.data('lineup-form-type') || 'contact');
            });
        });

        $('form[data-lineup-newsletter="1"]').each(function () {
            var $form = $(this);
            $form.off('submit.lineup').on('submit.lineup', function (e) {
                e.preventDefault();
                e.stopImmediatePropagation();
                submitNewsletter($form);
            });
        });
    });
})(jQuery);
