(function ($) {
    'use strict';

    function initDropzone($zone) {
        var $input = $zone.find('[data-photos-input]');
        var $preview = $zone.closest('.case-photos-upload-block').find('[data-photos-preview]');

        if (!$input.length || !$preview.length) {
            return;
        }

        $zone.on('click', function (e) {
            if (e.target === $input[0]) {
                return;
            }
            $input.trigger('click');
        });

        $zone.on('dragover', function (e) {
            e.preventDefault();
            $zone.addClass('dragover');
        });

        $zone.on('dragleave drop', function (e) {
            e.preventDefault();
            $zone.removeClass('dragover');
        });

        $zone.on('drop', function (e) {
            var files = e.originalEvent.dataTransfer && e.originalEvent.dataTransfer.files;
            if (files && files.length) {
                $input[0].files = files;
                $input.trigger('change');
            }
        });

        $input.on('change', function () {
            $preview.empty();
            Array.prototype.forEach.call(this.files || [], function (file) {
                if (!file.type || file.type.indexOf('image/') !== 0) {
                    return;
                }
                var reader = new FileReader();
                reader.onload = function (ev) {
                    $preview.append(
                        '<div class="case-photo-preview-item"><img src="' + ev.target.result + '" alt=""><small>' + file.name + '</small></div>'
                    );
                };
                reader.readAsDataURL(file);
            });
        });
    }

    $(function () {
        $('[data-photos-dropzone]').each(function () {
            initDropzone($(this));
        });
    });
})(jQuery);
