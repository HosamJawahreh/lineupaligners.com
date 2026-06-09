(function ($) {
    'use strict';

    function isImageFile(file) {
        return file && file.type && file.type.indexOf('image/') === 0;
    }

    function syncInputFiles(input, files) {
        var dt = new DataTransfer();

        files.forEach(function (file) {
            dt.items.add(file);
        });

        input.files = dt.files;
    }

    function escapeHtml(value) {
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function renderPreview($preview, files, compact) {
        $preview.empty();

        files.forEach(function (file, index) {
            var name = escapeHtml(file.name || 'Photo');
            var $item = $('<div class="case-photo-preview-item">')
                .attr('data-file-index', index)
                .attr('title', name);

            if (compact) {
                var $thumb = $('<div class="case-photo-preview-item__thumb">');
                $thumb.append($('<img>', { alt: '' }));
                $thumb.append(
                    $('<button type="button" class="case-photo-preview-item__remove">')
                        .attr('aria-label', 'Remove ' + name)
                        .append($('<i class="zmdi zmdi-close" aria-hidden="true">'))
                );
                $item.append($thumb);
            } else {
                $item.append($('<img>', { alt: '' }));
                $item.append($('<small>').text(file.name || 'Photo'));
            }

            $preview.append($item);

            var reader = new FileReader();
            reader.onload = function (ev) {
                $item.find('img').first().attr('src', ev.target.result);
            };
            reader.readAsDataURL(file);
        });
    }

    function initDropzone($zone) {
        var $block = $zone.closest('.case-photos-upload-block');
        var $input = $zone.find('[data-photos-input]');
        var $preview = $block.find('[data-photos-preview]');
        var compact = $block.is('[data-photos-compact]');
        var files = [];

        if (!$input.length || !$preview.length) {
            return;
        }

        function addFiles(fileList) {
            Array.prototype.forEach.call(fileList || [], function (file) {
                if (!isImageFile(file)) {
                    return;
                }

                files.push(file);
            });

            syncInputFiles($input[0], files);
            renderPreview($preview, files, compact);
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
            var dropped = e.originalEvent.dataTransfer && e.originalEvent.dataTransfer.files;
            if (dropped && dropped.length) {
                addFiles(dropped);
            }
        });

        $input.on('change', function () {
            addFiles(this.files);
            this.value = '';
        });

        $preview.on('click', '.case-photo-preview-item__remove', function (e) {
            e.preventDefault();
            e.stopPropagation();

            var index = Number($(this).closest('[data-file-index]').attr('data-file-index'));
            if (Number.isNaN(index) || index < 0 || index >= files.length) {
                return;
            }

            files.splice(index, 1);
            syncInputFiles($input[0], files);
            renderPreview($preview, files, compact);
        });
    }

    $(function () {
        $('[data-photos-dropzone]').each(function () {
            initDropzone($(this));
        });
    });
})(jQuery);
