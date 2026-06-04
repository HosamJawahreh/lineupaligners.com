(function ($) {
    'use strict';

    function initStagePicker($root) {
        var $nav = $root.find('[data-mfg-stage-nav]');
        if (!$nav.length) {
            return;
        }

        function showStage(stage) {
            var stageKey = String(stage);

            $nav.find('[data-mfg-stage-btn]').each(function () {
                var $btn = $(this);
                var isActive = String($btn.data('stage')) === stageKey;
                $btn.toggleClass('is-active', isActive);
                $btn.attr('aria-selected', isActive ? 'true' : 'false');
            });

            $root.find('[data-mfg-stage-panel]').each(function () {
                var $panel = $(this);
                var isActive = String($panel.data('mfg-stage-panel')) === stageKey;
                $panel.toggleClass('is-active', isActive);
                if (isActive) {
                    $panel.removeAttr('hidden');
                } else {
                    $panel.attr('hidden', true);
                }
            });
        }

        $nav.on('click', '[data-mfg-stage-btn]', function () {
            showStage($(this).data('stage'));
        });

        var $active = $nav.find('[data-mfg-stage-btn].is-active').first();
        if ($active.length) {
            showStage($active.data('stage'));
        } else {
            var $last = $nav.find('[data-mfg-stage-btn]').last();
            if ($last.length) {
                showStage($last.data('stage'));
            }
        }
    }

    function initStepRangeFields($root) {
        $root.find('#mfg-step-from, #mfg-step-to').on('change input', function () {
            var $from = $root.find('#mfg-step-from');
            var $to = $root.find('#mfg-step-to');
            if (!$from.length || !$to.length) {
                return;
            }
            var fromVal = parseInt($from.val(), 10) || 1;
            var toVal = parseInt($to.val(), 10) || fromVal;
            if (toVal < fromVal) {
                $to.val(fromVal);
            }
        });

        $root.find('#mfg-step-from').on('change', function () {
            var $from = $(this);
            var $to = $root.find('#mfg-step-to');
            if ($to.length && (!$to.val() || parseInt($to.val(), 10) < parseInt($from.val(), 10))) {
                $to.val($from.val());
            }
        });
    }

    function initManufacturePlan() {
        var $root = $('#case-manufacture-plan');
        if (!$root.length) {
            return;
        }

        initStagePicker($root);
        initStepRangeFields($root);

        $root.find('[data-mfg-review-form]').each(function () {
            var $form = $(this);
            var $comment = $form.find('textarea[name="comment"]');

            $form.on('click', '[data-requires-comment]', function (event) {
                if (!$comment.val().trim()) {
                    event.preventDefault();
                    $comment.focus();
                    window.alert('Please add a comment explaining what LineUp should fix before rejecting.');
                }
            });
        });
    }

    $(function () {
        initManufacturePlan();
    });
})(jQuery);
