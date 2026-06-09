(function ($) {
    'use strict';

    var FORM_DEFAULTS = {
        '.case-summary-card__action-form': {
            title: 'Email patient?',
            text: 'Send the latest case action to the patient by email.',
            icon: 'question',
            confirmButtonText: 'Send email',
        },
        '.mfg-plan__mark-form': {
            title: 'Mark as manufactured?',
            text: 'This completes the current case cycle. The doctor will not be able to request modifications until they order a refinement.',
            icon: 'warning',
            confirmButtonText: 'Mark manufactured',
        },
        '.mfg-plan__mark-form--stage': {
            title: 'Mark stage manufactured?',
            text: 'Confirm the manufacturing step range for this stage. The case cycle completes once every stage is marked manufactured.',
            icon: 'warning',
            confirmButtonText: 'Mark stage manufactured',
        },
        '.mfg-plan__form': {
            title: 'Submit treatment plan?',
            text: 'Send this plan to the doctor for review. They will approve or reject before manufacturing proceeds.',
            icon: 'question',
            confirmButtonText: 'Submit for review',
        },
        '.mfg-plan__form--revise': {
            title: 'Submit plan revision?',
            text: 'Upload this revised plan for the doctor to review again.',
            icon: 'question',
            confirmButtonText: 'Submit revision',
        },
        '.mfg-plan__add-stage-form': {
            title: 'Save stage and send for review?',
            text: 'This stage plan will be sent to the doctor for approval before you can add the next stage.',
            icon: 'question',
            confirmButtonText: 'Save and send',
        },
        '[data-mfg-review-form]': {
            title: 'Confirm your decision',
            text: 'Review your choice before submitting.',
            icon: 'question',
            confirmButtonText: 'Confirm',
        },
        '.case-modification-card__form': {
            title: 'Submit modification request?',
            text: 'Notes are required. Scans and photos are optional. LineUp will prepare an updated treatment plan for your review.',
            icon: 'question',
            confirmButtonText: 'Submit request',
        },
        '#case-refinement-form': {
            title: 'Start refinement case?',
            text: 'Notes are required. Scans and photos are optional. LineUp will prepare a new treatment plan for this refinement cycle.',
            icon: 'question',
            confirmButtonText: 'Start refinement',
        },
    };

    function defaultsForForm($form) {
        var merged = null;

        $.each(FORM_DEFAULTS, function (selector, options) {
            if ($form.is(selector)) {
                merged = $.extend({}, merged || {}, options);
            }
        });

        return merged || {
            title: 'Confirm action',
            text: 'Are you sure you want to continue?',
            icon: 'question',
            confirmButtonText: 'Yes, continue',
        };
    }

    function readOptions($trigger, $form) {
        var options = defaultsForForm($form);

        ['title', 'text', 'icon', 'confirmButtonText', 'cancelButtonText'].forEach(function (key) {
            var dataKey = 'confirm' + key.charAt(0).toUpperCase() + key.slice(1);
            if (key === 'confirmButtonText') {
                dataKey = 'confirmBtn';
            }
            if (key === 'cancelButtonText') {
                dataKey = 'confirmCancel';
            }

            var fromTrigger = $trigger.data(dataKey);
            var fromForm = $form.data(dataKey);

            if (fromTrigger) {
                options[key] = fromTrigger;
            } else if (fromForm) {
                options[key] = fromForm;
            }
        });

        if ($trigger.attr('name') === 'decision') {
            if ($trigger.val() === 'approved') {
                options.title = $trigger.data('confirmTitle') || 'Approve treatment plan?';
                options.text = $trigger.data('confirmText')
                    || 'Approve this plan for manufacture? You may still request a modification before manufacturing begins.';
                options.icon = 'success';
                options.confirmButtonText = $trigger.data('confirmBtn') || 'Approve for manufacture';
                options.confirmButtonClass = 'lineup-confirm-btn--success';
            } else if ($trigger.val() === 'rejected') {
                options.title = $trigger.data('confirmTitle') || 'Order modification?';
                options.text = $trigger.data('confirmText')
                    || 'Send this plan back to LineUp with your notes for a revised plan? You will be taken to the Modification tab.';
                options.icon = 'warning';
                options.confirmButtonText = $trigger.data('confirmBtn') || 'Order modification';
                options.confirmButtonClass = 'lineup-confirm-btn--warning';
            }
        }

        return options;
    }

    function askConfirm(options) {
        if (window.AppConfirm && typeof window.AppConfirm.ask === 'function') {
            return window.AppConfirm.ask(options);
        }

        return Promise.resolve(window.confirm(options.text || options.title || 'Are you sure?'));
    }

    function warnMissingComment($comment) {
        if (window.AppAlert && typeof window.AppAlert.warning === 'function') {
            window.AppAlert.warning('Please add modification notes explaining what LineUp should change.');
        } else {
            window.alert('Please add modification notes explaining what LineUp should change.');
        }

        $comment.focus();
    }

    function warnMissingNotes($notes, message) {
        if (window.AppAlert && typeof window.AppAlert.warning === 'function') {
            window.AppAlert.warning(message);
        } else {
            window.alert(message);
        }

        $notes.focus();
    }

    function formRequiresNotes($form) {
        return $form.is('.case-modification-card__form') || $form.is('#case-refinement-form');
    }

    function submitForm($form, $trigger) {
        $form.data('caseConfirmPassed', true);

        var formEl = $form.get(0);
        var triggerEl = $trigger && $trigger.length ? $trigger.get(0) : null;

        if (triggerEl && typeof formEl.requestSubmit === 'function') {
            formEl.requestSubmit(triggerEl);
            return;
        }

        if (triggerEl && triggerEl.name) {
            $form.find('input[data-case-confirm-proxy]').remove();
            $('<input type="hidden" data-case-confirm-proxy>')
                .attr('name', triggerEl.name)
                .val($(triggerEl).val() || '')
                .appendTo($form);
        }

        formEl.submit();
    }

    function bindForm($form) {
        if ($form.data('caseConfirmBound')) {
            return;
        }

        $form.data('caseConfirmBound', true);

        var $lastSubmit = $();

        $form.find('[type="submit"]').on('click', function () {
            $lastSubmit = $(this);
        });

        $form.on('submit', function (event) {
            if ($form.data('caseConfirmPassed')) {
                $form.removeData('caseConfirmPassed');
                return;
            }

            var $trigger = $lastSubmit.length ? $lastSubmit : $form.find('[type="submit"]').first();

            if ($trigger.is('[data-requires-comment]')) {
                var $comment = $form.find('textarea[name="comment"]');
                if ($comment.length && !$.trim($comment.val())) {
                    event.preventDefault();
                    warnMissingComment($comment);
                    return;
                }
            }

            if (formRequiresNotes($form)) {
                var $notes = $form.find('textarea[name="notes"]');
                var notesMessage = $form.is('#case-refinement-form')
                    ? 'Please add refinement notes explaining why the patient is returning and what LineUp should plan.'
                    : 'Please add modification notes explaining what LineUp should change.';

                if ($notes.length && !$.trim($notes.val())) {
                    event.preventDefault();
                    warnMissingNotes($notes, notesMessage);
                    return;
                }
            }

            event.preventDefault();

            askConfirm(readOptions($trigger, $form)).then(function (confirmed) {
                if (confirmed) {
                    submitForm($form, $trigger);
                }
            });
        });
    }

    function initCaseActionConfirm() {
        var selector = [
            '.case-summary-card__action-form',
            '.mfg-plan__mark-form',
            '.mfg-plan__mark-form--stage',
            '.mfg-plan__form',
            '.mfg-plan__add-stage-form',
            '[data-mfg-review-form]',
            '.case-modification-card__form',
            '#case-refinement-form',
            'form[data-case-confirm]',
        ].join(', ');

        $(selector).each(function () {
            bindForm($(this));
        });
    }

    $(initCaseActionConfirm);
})(jQuery);
