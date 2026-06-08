(function ($) {
    'use strict';

    var cfg = window.websiteAdminConfig || {};
    var soloSections = ['main-menu'];
    var linkedSections = {
        'portfolio': ['portfolio', 'portfolio-gallery'],
        'case-studies': ['case-studies', 'case-studies-gallery']
    };
    var sectionAliases = {
        'process': 'how-it-works',
        'cases': 'portfolio',
        'portfolio-gallery': 'case-studies'
    };
    var dirtyForms = new Set();

    function hasDirtyForms() {
        return dirtyForms.size > 0;
    }

    function markFormDirty(selector) {
        dirtyForms.add(selector);
        $(selector).addClass('is-dirty').find('.wm-savebar').addClass('is-dirty');
    }

    function clearFormDirty(selector) {
        dirtyForms.delete(selector);
        $(selector).removeClass('is-dirty').find('.wm-savebar').removeClass('is-dirty');
    }

    function syncSidebarGroups(section) {
        $('.wm-sidebar__group').each(function () {
            var $group = $(this);
            var children = String($group.data('wm-group-sections') || '').split(',');
            var isChildActive = children.indexOf(section) !== -1;
            $group.toggleClass('is-child-active', isChildActive);
            if (isChildActive) {
                $group.addClass('is-open');
                $group.find('.wm-sidebar__group-toggle').attr('aria-expanded', 'true');
            }
        });
    }

    function initSidebar() {
        var $formMain = $('#wm-main-form');
        var $soloMain = $('#wm-main-solo');
        var $panels = $('.wm-panel');
        var $links = $('.wm-sidebar__link');

        function showSection(section) {
            section = section || 'general';
            if (sectionAliases[section]) {
                section = sectionAliases[section];
            }
            $links.removeClass('is-active');
            $links.filter('[data-wm-section="' + section + '"]').addClass('is-active');
            syncSidebarGroups(section);

            if (soloSections.indexOf(section) !== -1) {
                $formMain.addClass('d-none');
                $soloMain.removeClass('d-none');
                $panels.addClass('d-none');
                $('#wm-panel-' + section).removeClass('d-none');
            } else {
                $soloMain.addClass('d-none');
                $formMain.removeClass('d-none');
                $panels.addClass('d-none');
                var panelIds = linkedSections[section] || [section];
                panelIds.forEach(function (panelId) {
                    $('#wm-panel-' + panelId).removeClass('d-none');
                });
            }

            $('#website-return-tab').val(section);

            if (history.replaceState) {
                var url = new URL(window.location.href);
                url.searchParams.set('section', section);
                history.replaceState({}, '', url.toString());
            }
        }

        $links.on('click', function (e) {
            e.preventDefault();
            showSection($(this).data('wm-section'));
        });

        $('.wm-sidebar__group-toggle').on('click', function () {
            var $group = $(this).closest('.wm-sidebar__group');
            var willOpen = !$group.hasClass('is-open');
            $group.toggleClass('is-open', willOpen);
            $(this).attr('aria-expanded', willOpen ? 'true' : 'false');
        });

        var section = new URLSearchParams(window.location.search).get('section') || 'general';
        if (sectionAliases[section]) {
            section = sectionAliases[section];
        }
        if (!$('#wm-panel-' + section).length) {
            section = 'general';
        }
        showSection(section);

        $(document).on('click', '.wm-goto-section', function (e) {
            e.preventDefault();
            showSection($(this).data('wm-section'));
        });
    }

    function initRepeatable() {
        var featureIndex = cfg.featureIndex || 0;
        var statIndex = cfg.statIndex || 0;
        var slideIndex = cfg.slideIndex || 0;
        var processIndex = cfg.processIndex || 0;
        var faqIndex = cfg.faqIndex || 0;
        var blogIndex = cfg.blogIndex || 0;

        $('#website-add-feature').on('click', function () {
            $('#website-features-list').append($('#website-feature-row-template').html().replace(/__INDEX__/g, featureIndex++));
        });
        $('#website-add-stat').on('click', function () {
            $('#website-stats-list').append($('#website-stat-row-template').html().replace(/__INDEX__/g, statIndex++));
        });
        $('#website-add-slide').on('click', function () {
            $('#website-slides-list').append($('#website-slide-row-template').html().replace(/__INDEX__/g, slideIndex++));
        });
        $('#website-add-process').on('click', function () {
            $('#website-process-list').append($('#website-process-row-template').html().replace(/__INDEX__/g, processIndex++));
        });
        $('#website-add-faq').on('click', function () {
            $('#website-faq-list').append($('#website-faq-row-template').html().replace(/__INDEX__/g, faqIndex++));
        });
        $('#website-add-blog').on('click', function () {
            $('#website-blog-list').append($('#website-blog-row-template').html().replace(/__INDEX__/g, blogIndex++));
        });

        $(document).on('click', '.website-remove-row', function () {
            $(this).closest('.website-repeatable__row, .website-slide-row, .wm-faq-item, .wm-slide-card, .wm-feature-card, .wm-treatable-card, .wm-blog-card, .wm-process-card').remove();
        });
    }

    function initHeroTypeToggle() {
        var $type = $('#website-hero-type');
        var $videoPanel = $('#website-hero-video-panel');
        var $sliderPanel = $('#website-hero-slider-panel');

        function sync() {
            var isVideo = $type.val() === 'video';
            $videoPanel.toggleClass('d-none', !isVideo);
            $sliderPanel.toggleClass('d-none', isVideo);
        }

        $type.on('change', sync);
        sync();
    }

    function initHeroPreview() {
        $(document).on('change', '.wm-image-input', function () {
            var file = this.files[0];
            var previewId = $(this).data('preview');
            if (!file || !previewId) return;
            var reader = new FileReader();
            reader.onload = function (e) {
                $('#' + previewId).html('<img src="' + e.target.result + '" alt="">');
            };
            reader.readAsDataURL(file);
        });
    }

    function resetShowcaseForm($form) {
        if (!$form || !$form.length) {
            return;
        }

        var formId = $form.attr('id');
        var addLabel = $form.data('add-label') || 'Add case';

        $form.attr('action', cfg.showcaseStoreUrl || $form.attr('action'));
        $form.find('.showcase-form-method').prop('disabled', true).val('POST');
        $form[0].reset();
        $form.find('.showcase-published').prop('checked', true);
        $form.find('.showcase-edit-extras').addClass('d-none');
        $form.find('.showcase-cancel-edit').addClass('d-none');
        $('#' + formId + '-title').text(addLabel);
        $form.find('.showcase-submit-btn').html('<i class="zmdi zmdi-plus"></i> ' + addLabel);
        $form.find('details.wm-item-detail').prop('open', false);
    }

    function initShowcaseEdit() {
        $('.showcase-form').each(function () {
            var $form = $(this);
            var $crud = $form.closest('.website-showcase-crud');
            $form.data('add-label', $crud.data('add-label') || 'Add case');
        });

        $(document).on('click', '.website-edit-showcase', function () {
            var $card = $(this).closest('.website-showcase-card');
            var formId = $card.data('form-id');
            var $form = $('#' + formId);
            if (!$form.length) {
                return;
            }

            var addLabel = $card.data('add-label') || 'Add case';
            var editLabel = $card.data('edit-label') || 'Edit case';

            $form.data('add-label', addLabel);
            $form.attr('action', $card.data('update-url'));
            $form.find('.showcase-form-method').prop('disabled', false).val('PUT');
            $form.find('[name="title"]').val($card.data('title'));
            $form.find('[name="patient_label"]').val($card.data('patient-label') || '');
            $form.find('[name="case_type"]').val($card.data('case-type'));
            $form.find('[name="treatment_months"]').val($card.data('treatment-months') || '');
            $form.find('[name="summary"]').val($card.data('summary') || '');
            $form.find('[name="slug"]').val($card.data('slug') || '');
            var detail = $card.data('detail') || {};
            $form.find('[name="detail_title"]').val(detail.title || '');
            $form.find('[name="detail_summary_title"]').val(detail.summary_title || '');
            $form.find('[name="detail_sidebar_intro"]').val(detail.sidebar_intro || '');
            $form.find('[name="detail_intro"]').val(detail.intro || '');
            $form.find('[name="detail_body"]').val(detail.body || '');
            $form.find('[name="detail_what_we_did_title"]').val(detail.what_we_did_title || '');
            $form.find('[name="detail_what_we_did_body"]').val(detail.what_we_did_body || '');
            $form.find('[name="detail_client"]').val(detail.client || '');
            $form.find('[name="detail_category"]').val(detail.category || '');
            $form.find('[name="detail_date"]').val(detail.date || '');
            $form.find('[name="detail_location"]').val(detail.location || '');
            $form.find('[name="detail_image1"]').val(detail.detail_image1 || '');
            $form.find('[name="detail_image2"]').val(detail.detail_image2 || '');
            if ($form.find('details.wm-item-detail').length) {
                $form.find('details.wm-item-detail').prop('open', true);
            }
            $form.find('[name="outcome"]').val($card.data('outcome') || '');
            $form.find('.showcase-published').prop('checked', String($card.data('published')) === '1');
            $form.find('.showcase-edit-extras').removeClass('d-none');
            $form.find('.showcase-cancel-edit').removeClass('d-none');
            $('#' + formId + '-title').text(editLabel);
            $form.find('.showcase-submit-btn').html('<i class="zmdi zmdi-check"></i> Save changes');
            $('html, body').animate({ scrollTop: $form.offset().top - 80 }, 300);
        });

        $(document).on('click', '.showcase-cancel-edit', function () {
            resetShowcaseForm($(this).closest('.showcase-form'));
        });
    }

    function initSaveUx() {
        if ($('.alert-success, .alert.alert-success').length) {
            var savebar = document.getElementById('website-save-actions');
            if (savebar) {
                savebar.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        }
    }

    $(function () {
        initSidebar();
        initRepeatable();
        initHeroTypeToggle();
        initHeroPreview();
        initShowcaseEdit();
        initMainMenuManager();
        initNavigationLinks();
        initDirtyFormGuard();
        initSaveUx();
    });

    function initDirtyFormGuard() {
        var selectors = ['#website-content-form', '#website-main-menu-form', '.showcase-form'];

        selectors.forEach(function (selector) {
            var $form = $(selector);
            if (!$form.length) {
                return;
            }

            $form.on('change input', ':input:not([type="hidden"])', function () {
                markFormDirty(selector);
            });

            $form.on('submit', function () {
                clearFormDirty(selector);
            });
        });
    }

    function initMainMenuManager() {
        var $list = $('#wm-main-menu-list');
        if (!$list.length) {
            return;
        }

        function initSortable($container, itemSelector, handleSelector) {
            var dragEl = null;

            $container.on('dragstart', handleSelector, function (e) {
                dragEl = $(this).closest(itemSelector)[0];
                if (!dragEl) {
                    return;
                }
                $(dragEl).addClass('is-dragging');
                e.originalEvent.dataTransfer.effectAllowed = 'move';
                e.originalEvent.dataTransfer.setData('text/plain', 'drag');
            });

            $container.on('dragend', handleSelector, function () {
                if (dragEl) {
                    $(dragEl).removeClass('is-dragging');
                }
                dragEl = null;
                $container.find(itemSelector).removeClass('is-drop-target');
            });

            $container.on('dragover', itemSelector, function (e) {
                e.preventDefault();
                if (!dragEl || dragEl === this) {
                    return;
                }
                $container.find(itemSelector).removeClass('is-drop-target');
                $(this).addClass('is-drop-target');
                var rect = this.getBoundingClientRect();
                var after = e.originalEvent.clientY > rect.top + rect.height / 2;
                if (after) {
                    this.parentNode.insertBefore(dragEl, this.nextSibling);
                } else {
                    this.parentNode.insertBefore(dragEl, this);
                }
            });

            $container.on('drop', itemSelector, function (e) {
                e.preventDefault();
                $container.find(itemSelector).removeClass('is-drop-target');
            });
        }

        initSortable($list, '.wm-main-menu-entry', '.wm-main-menu-entry__handle');
        $list.find('[data-wm-menu-children]').each(function () {
            initSortable($(this), '.wm-main-menu-page', '.wm-main-menu-page__handle');
        });

        $(document).on('change', '.wm-main-menu-enabled', function () {
            var $row = $(this).closest('.wm-main-menu-page');
            var enabled = $(this).is(':checked');
            var $nav = $row.find('.wm-main-menu-nav');
            $nav.prop('disabled', !enabled);
            if (!enabled) {
                $nav.prop('checked', false);
            }
            refreshEntryBadges($row.closest('.wm-main-menu-entry'));
        });

        $(document).on('change', '.wm-main-menu-nav', function () {
            refreshEntryBadges($(this).closest('.wm-main-menu-entry'));
        });

        function refreshEntryBadges($entry) {
            if (!$entry.length) {
                return;
            }
            var visible = $entry.find('.wm-main-menu-nav:checked:not(:disabled)').length;
            var $badge = $entry.find('.wm-main-menu-entry__badge');
            $badge.removeClass('is-dropdown is-link is-hidden');
            if (visible > 1) {
                $badge.addClass('is-dropdown').text('Dropdown · ' + visible + ' links');
            } else if (visible === 1) {
                $badge.addClass('is-link').text('Direct link');
            } else {
                $badge.addClass('is-hidden').text('Hidden');
            }
        }
    }

    function initNavigationLinks() {
        var footerIndex = cfg.navLinkIndex || 0;
        var bottomIndex = cfg.bottomLinkIndex || 0;

        function syncLinkRow($row) {
            var type = $row.find('.wm-nav-link-type').val();
            $row.find('[data-show-when]').each(function () {
                var types = String($(this).data('show-when')).split(',');
                $(this).toggle(types.indexOf(type) !== -1);
            });
        }

        $(document).on('change', '.wm-nav-link-type', function () {
            syncLinkRow($(this).closest('[data-nav-link-row]'));
        });

        $('[data-nav-link-row]').each(function () {
            syncLinkRow($(this));
        });

        function appendLink(listId, prefixBuilder) {
            var index = listId === '#wm-footer-links-list' ? footerIndex++ : bottomIndex++;
            var html = $('#wm-nav-link-row-template').html().replace(/__PREFIX__/g, prefixBuilder(index));
            var $row = $(html);
            $(listId).append($row);
            syncLinkRow($row);
        }

        $('#wm-add-footer-link').on('click', function () {
            appendLink('#wm-footer-links-list', function (i) {
                return 'navigation[footer_columns][0][links][' + i + ']';
            });
        });

        $('#wm-add-bottom-link').on('click', function () {
            appendLink('#wm-bottom-links-list', function (i) {
                return 'navigation[bottom_links][' + i + ']';
            });
        });
    }
})(jQuery);
