(function ($) {
    'use strict';

    function initVersionPicker($root) {
        $root.find('[data-mfg-version-nav]').each(function () {
            var $nav = $(this);
            var navKey = String($nav.data('mfg-version-nav'));

            function showVersion(version) {
                var versionKey = String(version);

                $nav.find('[data-mfg-version-btn]').each(function () {
                    var $btn = $(this);
                    var isActive = String($btn.data('version')) === versionKey;
                    $btn.toggleClass('is-active', isActive);
                    $btn.attr('aria-selected', isActive ? 'true' : 'false');
                });

                $root.find('[data-mfg-version-panel][data-version-nav="' + navKey + '"]').each(function () {
                    var $panel = $(this);
                    var isActive = String($panel.data('mfg-version-panel')) === versionKey;
                    $panel.toggleClass('is-active', isActive);
                    if (isActive) {
                        $panel.removeAttr('hidden');
                    } else {
                        $panel.attr('hidden', true);
                    }
                });
            }

            $nav.on('click', '[data-mfg-version-btn]', function () {
                showVersion($(this).data('version'));
            });

            var $active = $nav.find('[data-mfg-version-btn].is-active').first();
            if ($active.length) {
                showVersion($active.data('version'));
            } else {
                var $last = $nav.find('[data-mfg-version-btn]').last();
                if ($last.length) {
                    showVersion($last.data('version'));
                }
            }
        });
    }

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
        initVersionPicker($root);
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
