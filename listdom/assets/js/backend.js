// Listdom LOADING WRAPPER
function ListdomLoadingWrapper()
{
    this.wrap = jQuery('#wpwrap');
    this.body = jQuery('body');
    this.scrollTop = 0;

    // Start Loading Style
    this.start = () =>
    {
        this.scrollTop = window.scrollY || document.documentElement.scrollTop || 0;
        this.wrap.addClass('lsd-loading-wrapper');
      
        const docHeight = Math.max(
            document.body.scrollHeight,
            document.documentElement.scrollHeight,
            document.body.offsetHeight,
            document.documentElement.offsetHeight,
            document.body.clientHeight,
            document.documentElement.clientHeight
        );

        this.body.addClass('lsd-not-scrollable')
        .css({
            top: `-${this.scrollTop}px`,
            height: docHeight + 'px',
            overflowY: 'scroll'
        });
    };

    // Stop Loading Style
    this.stop = ($message, time) =>
    {
        // Default Time Value
        if (typeof time === 'undefined') time = 0;

        // Add Message
        $message && this.wrap.prepend($message) && $message.removeClass('lsd-util-hide');

        setTimeout(() =>
        {
            // Hide Message
            $message && $message.addClass('lsd-util-hide');

            // Remove Loading Style
            this.wrap.removeClass('lsd-loading-wrapper');
            this.body.removeClass('lsd-not-scrollable').css({
                top: '',
                height: '',
                overflowY: ''
            });

            window.scrollTo(0, this.scrollTop);
        }, time);
    };
}

function ListdomButtonLoader($button) {
    this.$button = $button;
    this.originalHtml = $button.html();

    // Start Loading Style
    this.start = (loadingText) => {
        this.$button
        .html(loadingText + ' <i class="lsd-loader"></i>')
        .attr('disabled', 'disabled');
    };

    // Stop Loading Style
    this.stop = () => {
        this.$button
        .html(this.originalHtml)
        .removeAttr('disabled');
    };
}

// Listdom Search Builder PLUGIN
(function ($)
{
    $.fn.listdomSearchBuilder = function (options)
    {
        // Default Options
        const settings = $.extend(
            {
                // These are the defaults.
                has_more_features: false,
                post_id: 0
            }, options);

        const $builder = $('#lsd-search-fields');

        // Device Key
        let device_key = $builder.data('active-device');
        if (device_key === 'desktop') device_key = 'fields';

        let $device = $('.lsd-search-fields-device-' + $builder.data('active-device'));

        let $sandbox = $device.find($(".lsd-search-sandbox"));
        let $available_fields = $device.find($(".lsd-search-available-fields-container"));
        let $rows = $device.find($(".lsd-search-row"));

        // HTML Elements
        const $wrapper = $(".lsd-search-fields-metabox");
        const $search_style = $('#lsd_search_form_style');
        const $btn_add_row = $('#lsd_search_add_row');
        const $btn_more_options = $("#lsd_search_more_options");
        const $btn_delete_row = $('.lsd-search-row-actions-delete');
        const $btn_delete_field = $('.lsd-search-field-actions-delete');
        const $field_method = $('.lsd-search-method');
        const $device_tabs = $('.lsd-search-device-tabs');
        const $all_terms_dropdowns = $(".lsd-search-field-all-terms select");
        const $page_selection = $("#lsd_search_form_results_page");

        // Set the listener
        setListeners();

        // Disable More Options
        if ($device.find($(".lsd-search-more-options")).length) $btn_more_options.prop('disabled', 'disabled');

        function sortable_listeners()
        {
            $sandbox.sortable(
                {
                    handle: '.lsd-row-handler'
                });

            $device.find($(".lsd-search-row")).find('.lsd-search-filters').sortable(
                {
                    handle: '.lsd-field-handler',
                    start: function (event, ui)
                    {
                        $(ui.item).addClass('lsd-field-dragging');
                    },
                    stop: function (event, ui)
                    {
                        $(ui.item).removeClass('lsd-field-dragging');
                    }
                });
        }

        function setListeners()
        {
            $search_style.off('change').on('change', function ()
            {
                const style = $search_style.val();

                if (style === 'sidebar') $builder.addClass('lsd-search-style-sidebar');
                else $builder.removeClass('lsd-search-style-sidebar');
            }).trigger('change');

            $btn_add_row.off('click').on('click', function ()
            {
                add_row();
            });

            $btn_more_options.off('click').on('click', function ()
            {
                add_more_options();
                add_row();
            });

            $device_tabs.find($('li')).off('click').on('click', function ()
            {
                show_device($(this).data('device'));
            });

            $btn_delete_row.off('click').on('click', function ()
            {
                delete_row($(this));
            });

            $btn_delete_field.off('click').on('click', function ()
            {
                delete_field($(this));
            });

            $(document).on('click', '.lsd-search-field-actions-visibility', function ()
            {
                field_visibility($(this));
            });

            $(document).on('click', '.lsd-search-field-param-title-visibility', function ()
            {
                field_title_visibility($(this));
            });

            $(document).on('change', '.lsd-search-row-params .lsd-switch input[type=checkbox]', function ()
            {
                const checked = $(this).is(':checked');

                if (checked) $(this).closest('.lsd-search-row-button-wrapper-params').addClass('lsd-search-row-button-enabled');
                else $(this).closest('.lsd-search-row-button-wrapper-params').removeClass('lsd-search-row-button-enabled');
            });

            $available_fields.off('mouseover').on('mouseover', 'div:not(.ui-draggable)', function ()
            {
                $(this).draggable(
                    {
                        revert: "invalid"
                    });
            });

            $rows.droppable(
                {
                    accept: '.lsd-search-field',
                    drop: function (event, ui)
                    {
                        add_field($(this), ui.draggable);
                    }
                });

            // Sortable
            sortable_listeners();

            $field_method.off('change').on('change', function ()
            {
                method_changed($(this));
            }).trigger('change');

            $all_terms_dropdowns.off('change').on('change', function ()
            {
                const $dropdown = $(this);
                const value = $dropdown.val();

                if (value === '1') $dropdown.parent().parent().find($('.lsd-search-field-terms')).addClass('lsd-util-hide');
                else $dropdown.parent().parent().find($('.lsd-search-field-terms')).removeClass('lsd-util-hide');
            })

            $rows.find('select[multiple=""]').each(function ()
            {
                $(this).select2(
                    {
                        allowClear: $(this).attr('multiple'),
                        placeholder: $(this).attr('placeholder'),
                        minimumResultsForSearch: 0,
                        shouldFocusInput: () => false,
                    });
            })

            $page_selection.off('change').on('change', function ()
            {
                const $target_shortcode = $('.lsd-search-target-shortcode');
                const $connected_shortcodes = $('.lsd-search-connected-shortcodes');
                const value = $(this).val();

                if (value)
                {
                    $target_shortcode.removeClass('lsd-util-hide');
                    $connected_shortcodes.addClass('lsd-util-hide');
                }
                else
                {
                    $target_shortcode.addClass('lsd-util-hide');
                    $connected_shortcodes.removeClass('lsd-util-hide');
                }
            });

            $('.lsd-searchable-select').each(function ()
            {
                const $select = $(this);

                if (!$select.hasClass('select2-hidden-accessible'))
                {
                    $select.select2({
                        allowClear: $(this).attr('multiple'),
                        placeholder: $(this).attr('placeholder'),
                        width: '100%',
                        minimumResultsForSearch: 0,
                        shouldFocusInput: () => false,
                    });
                }
            });

            $(document).on('change', '.lsd-more-options-type-toggle', function ()
            {
                const $type = $(this);
                const $target = $($type.data('for'));
                const type = $type.val();

                if (type === 'popup') $target.removeClass('lsd-util-hide');
                else $target.addClass('lsd-util-hide');
            });
        }

        function add_row()
        {
            const i = $(".lsd-search-sandbox > div").length + 1;
            const $row = $('<div class="lsd-search-row" id="lsd_' + device_key + '_search_row_' + i + '" data-i="' + i + '"><ul class="lsd-search-row-actions"><li class="lsd-search-row-actions-sort lsd-row-handler"><i class="lsd-icon fas fa-arrows-alt"></i></li><li class="lsd-search-row-actions-delete lsd-tooltip" data-lsd-tooltip="' + lsd.i18n_field_delete + '" data-confirm="0" data-i="' + i + '"><i class="lsd-icon fas fa-trash-alt"></i></li></ul><input type="hidden" name="lsd[' + device_key + '][' + i + '][type]" value="row"><div class="lsd-search-filters"></div></div>');

            // Append New Row
            $sandbox.append($row);

            $row.droppable(
                {
                    accept: '.lsd-search-field',
                    drop: function (event, ui)
                    {
                        add_field($(this), ui.draggable);
                    }
                });

            $row.find('.lsd-search-filters').sortable(
                {
                    handle: '.lsd-handler',
                    start: function (event, ui)
                    {
                        $(ui.item).addClass('lsd-field-dragging');
                    },
                    stop: function (event, ui)
                    {
                        $(ui.item).removeClass('lsd-field-dragging');
                    }
                });

            // Sortable
            sortable_listeners();

            // Register Delete Button
            $row.find('.lsd-search-row-actions-delete').off('click').on('click', function ()
            {
                delete_row($(this));
            });

            $.ajax(
                {
                    url: settings.ajax_url,
                    data: "action=lsd_search_builder_row_params&type=row&i=" + i + "&device_key=" + device_key + "&post_id=" + settings.post_id,
                    dataType: "json",
                    type: "post",
                    success: function (response)
                    {
                        if (response.success === 1)
                        {
                            $row.find('.lsd-search-filters').after(response.html);
                        }
                    },
                    error: function ()
                    {
                    }
                });
        }

        function add_more_options()
        {
            // One More Options Added Already
            if ($device.find($(".lsd-search-more-options")).length)
            {
                $btn_more_options.prop('disabled', 'disabled');
                return false;
            }

            const i = $(".lsd-search-row").length + 1;
            const $row = $('<div class="lsd-search-more-options" id="lsd_' + device_key + '_search_row_' + i + '" data-i="' + i + '"><span class="lsd-search-more-options-label">' + lsd.i18n_field_search + '</span><input type="hidden" name="lsd[' + device_key + '][' + i + '][type]" value="more_options"><ul class="lsd-search-row-actions"><li class="lsd-search-row-actions-sort lsd-row-handler"><i class="lsd-icon fas fa-arrows-alt"></i></li><li class="lsd-search-row-actions-delete lsd-tooltip" data-lsd-tooltip="' + lsd.i18n_field_delete + '" data-confirm="0" data-i="' + i + '"><i class="lsd-icon fas fa-trash-alt"></i></li></ul></div>');

            // Append New Row
            $sandbox.append($row);

            // Disable Button
            $btn_more_options.prop('disabled', 'disabled');

            // Update Rows Sort
            $sandbox.sortable('refresh');

            // Register Delete Button
            $row.find('.lsd-search-row-actions-delete').off('click').on('click', function ()
            {
                delete_row($(this));
            });

            $.ajax(
                {
                    url: settings.ajax_url,
                    data: "action=lsd_search_builder_row_params&type=more_options&i=" + i + "&device_key=" + device_key + "&post_id=" + settings.post_id,
                    dataType: "json",
                    type: "post",
                    success: function (response)
                    {
                        if (response.success === 1)
                        {
                            $row.find('.lsd-search-row-actions').after(response.html);
                        }
                    },
                    error: function ()
                    {
                    }
                });
        }

        function show_device(device)
        {
            // Device
            $device = $('.lsd-search-fields-device-' + device);

            // Tab
            $device_tabs.find($('li')).removeClass('lsd-tab-active');
            $device_tabs.find($('li[data-device=' + device + ']')).addClass('lsd-tab-active');

            // Content
            const $device_contents = $('.lsd-search-fields-device');

            $device_contents.addClass('lsd-util-hide');
            $device.removeClass('lsd-util-hide');

            // Active Device
            $builder.data('active-device', device);
            device_key = device === 'desktop' ? 'fields' : device;

            $sandbox = $device.find($(".lsd-search-sandbox"));
            $available_fields = $device.find($(".lsd-search-available-fields-container"));
            $rows = $device.find($(".lsd-search-row"));

            // More Options Button Toggle
            if ($device.find($(".lsd-search-more-options")).length) $btn_more_options.attr('disabled', 'disabled');
            else $btn_more_options.removeAttr('disabled');

            setListeners();
        }

        function add_field($row, $field)
        {
            const i = $row.data('i');
            const key = $field.data('key');
            const id = $field.prop('id');

            // It's a change in sort so don't add the field again
            if ($row.find('#' + id).length) return false;

            // Loading Style
            $wrapper.fadeTo(200, 0.7);

            let title = $field.find($('strong')).text();
            const $search_filters = $row.find('.lsd-search-filters');

            // It's a row change for field so don't add it again
            if ($field.find('.lsd-search-field-actions').length) title = $field.find('input[name*="title\]"]').val();

            // Remove Field from Available Options
            $field.remove();

            $.ajax(
                {
                    url: settings.ajax_url,
                    data: "action=lsd_search_builder_params&i=" + i + "&key=" + key + "&title=" + encodeURIComponent(title) + "&device_key=" + device_key,
                    dataType: "json",
                    type: "post",
                    success: function (response)
                    {
                        if (response.success === 1)
                        {
                            $search_filters.append(response.html);

                            // Sortable
                            sortable_listeners();

                            const $field = $('#lsd_' + device_key + '_search_field_' + i + '_' + key);

                            // Register Delete Button
                            $field.find('.lsd-search-field-actions-delete').off('click').on('click', function ()
                            {
                                delete_field($(this));
                            });

                            $field.find('.lsd-search-method').on('change', function ()
                            {
                                method_changed($(this));
                            }).trigger('change');

                            $field.find($('.lsd-search-field-all-terms select')).on('change', function ()
                            {
                                const $dropdown = $(this);
                                const value = $dropdown.val();

                                if (value === '1') $dropdown.parent().parent().find($('.lsd-search-field-terms')).addClass('lsd-util-hide');
                                else $dropdown.parent().parent().find($('.lsd-search-field-terms')).removeClass('lsd-util-hide');
                            })

                            $field.find('select[multiple=""]').each(function ()
                            {
                                $(this).select2(
                                    {
                                        allowClear: $(this).attr('multiple'),
                                        placeholder: $(this).attr('placeholder'),
                                        minimumResultsForSearch: 0,
                                        shouldFocusInput: () => false,
                                    });
                            })
                        }

                        // Loading Style
                        $wrapper.fadeTo(200, 1);
                    },
                    error: function ()
                    {
                        // Loading Style
                        $wrapper.fadeTo(200, 1);
                    }
                });
        }

        function delete_row($btn)
        {
            const confirm = $btn.data('confirm');
            if (confirm === 0) return need_confirm($btn);

            const i = $btn.data('i');
            const $row = $('#lsd_' + device_key + '_search_row_' + i);

            const type = $row.find('input[name*="type"]').val();
            if (type === 'more_options')
            {
                // Enable Button
                $btn_more_options.removeAttr('disabled');
            }

            // Remove Fields
            $row.find('.lsd-search-field-actions-delete').data('confirm', 1).trigger('click');

            // Remove Row
            $row.remove();
        }

        function delete_field($btn)
        {
            const confirm = $btn.data('confirm');
            if (confirm === 0) return need_confirm($btn);

            const i = $btn.data('i');
            const key = $btn.data('key');

            const $field = $('#lsd_' + device_key + '_search_field_' + i + '_' + key);
            const title = $field.data('label');

            $field.remove();

            // Add it to Available Fields
            $available_fields.append('<div class="lsd-search-field" id="lsd_search_available_' + device_key + '_' + key + '" data-key="' + key + '"><strong>' + title + '</strong></div>');
        }

        function field_visibility($btn)
        {
            const i = $btn.data('i');
            const key = $btn.data('key');

            const $field = $('#lsd_' + device_key + '_search_field_' + i + '_' + key);
            const $visibility = $('#lsd_' + device_key + '_' + i + '_filters_' + key + '_visibility');

            if ($field.hasClass('lsd-search-field-hidden'))
            {
                $field.removeClass('lsd-search-field-hidden');
                $visibility.val(1);

                $btn.find($('i')).removeClass('fa-eye-slash').addClass('fa-eye');
            }
            else
            {
                $field.addClass('lsd-search-field-hidden');
                $visibility.val(0);

                $btn.find($('i')).removeClass('fa-eye').addClass('fa-eye-slash');
            }
        }

        function field_title_visibility($btn)
        {
            const i = $btn.data('i');
            const key = $btn.data('key');

            const $field = $('#lsd_' + device_key + '_search_field_' + i + '_' + key);
            const $title_visibility = $('#lsd_' + device_key + '_' + i + '_filters_' + key + '_title_visibility');
            const $title_input = $field.find($('.lsd-search-field-param-title'));
            const $placeholder_input = $field.find($('.lsd-search-field-param-placeholder'));
            const title = $title_input.val();
            const placeholder = $placeholder_input.val();

            if ($field.hasClass('lsd-search-field-title-hidden'))
            {
                $field.removeClass('lsd-search-field-title-hidden');
                $title_visibility.val(1);
                $title_input.removeAttr('disabled');

                $btn.find($('i')).removeClass('fa-eye-slash').addClass('fa-eye');
            }
            else
            {
                $field.addClass('lsd-search-field-title-hidden');
                $title_visibility.val(0);

                $title_input.val('').attr('disabled', 'disabled');
                if (placeholder === '') $placeholder_input.val(title);

                $btn.find($('i')).removeClass('fa-eye').addClass('fa-eye-slash');
            }
        }

        function method_changed($method)
        {
            const $field = $method.closest('.lsd-search-field');
            const method = $method.val();

            // Hide All Dependant Fields
            $field.find('.lsd-search-method-dependant').hide();

            // Show Related Fields
            $field.find('.lsd-search-method-' + method).show();
        }

        function need_confirm($element)
        {
            $element.data('confirm', 1);
            $element.addClass('lsd-need-confirm');

            setTimeout(function ()
            {
                $element.data('confirm', 0);
                $element.removeClass('lsd-need-confirm');
            }, 10000);
        }
    };
}(jQuery));

// JS File
jQuery(document).ready(function ($)
{
    /**
     * Listdom Toggle
     */
    listdom_trigger_toggle();
    listdom_trigger_select();

    /**
     * Listdom accordion system
     */
    $('.lsd-accordion-title').on('click', function (event)
    {
        let opened = false;
        if ($(this).hasClass('lsd-accordion-active')) opened = true;

        // Close All other accordions except parents
        $('.lsd-accordion-title').not($(this).parents('.lsd-accordion-panel').prev('.lsd-accordion-title')).removeClass('lsd-accordion-active');
        $('.lsd-accordion-panel').not($(this).parents('.lsd-accordion-panel')).removeClass('lsd-accordion-open');

        // Don't open it again
        if (opened) return;

        // Active Class
        $(this).toggleClass('lsd-accordion-active');

        // Panel
        const $panel = $(this).next();
        $panel.toggleClass('lsd-accordion-open');

        // Stop bubbling to parent accordions
        event.stopPropagation();
    });

    /**
     * Listdom Icon Picker
     */
    if (typeof $.fn.fontIconPicker !== 'undefined') $('.lsd-iconpicker').fontIconPicker(
        {
            emptyIcon: false,
            emptyIconValue: '',
        });

    /**
     * Listdom Color Palette
     */
    $('.lsd-color-box').on('click', function ()
    {
        const $palette = $(this).parent();

        $palette.find('.lsd-color-box').removeClass('lsd-color-box-active');
        $(this).addClass('lsd-color-box-active');

        const color = $(this).data('color');
        $($palette.data('for')).wpColorPicker('color', color);
    });

    /**
     * Listdom Color Picker
     */
    if (typeof $.fn.wpColorPicker !== 'undefined') $('.lsd-colorpicker').wpColorPicker();

    /**
     * Sortable System
     */
    $('.lsd-sortable').sortable();

    // Listdom Switcher
    $('.lsd-switch input[type=checkbox]').on('change', function ()
    {
        const $toggle = $(this).parent().find($('.lsd-toggle'));
        if ($toggle.data('triggered')) return;

        $toggle.trigger('click');
    });

    /**
     * Attributes Menu
     */
    // Categories field
    $('#lsd_all_categories').on('change', function ()
    {
        if ($(this).is(':checked')) $('#lsd_categories_wp').addClass('lsd-util-hide');
        else $('#lsd_categories_wp').removeClass('lsd-util-hide');
    });

    // Type Dependent Fields
    $('#lsd_field_type').on('change', function ()
    {
        const type = $(this).val();

        // Hide All Fields
        $('.lsd-field-type-dependent').hide();

        // Show Dependent Fields
        $('.lsd-field-type-dependent.lsd-field-type-' + type).show();
    }).trigger('change');

    // Rich Editor
    $('#lsd_editor, #lsd_required').on('change', function ()
    {
        const value = $(this).is(':checked');
        const name = $(this).attr('name');

        if (value)
        {
            if (name === 'lsd_editor') $('#lsd_required').removeAttr('checked');
            else if (name === 'lsd_required') $('#lsd_editor').removeAttr('checked');
        }
    });

    /**
     * Single Listing Settings
     */
    // Enabling / Disabling Element
    $('.lsd-details-page-element-toggle-status strong').on('click', function ()
    {
        const key = $(this).parent().data('key');

        // Disabling the element
        if ($(this).hasClass('lsd-enabled'))
        {
            $(this).addClass('lsd-util-hide');
            $('#lsd_actions_' + key + ' .lsd-disabled').removeClass('lsd-util-hide');

            $('input[name="lsd[elements][' + key + '][enabled]"]').val('0');
            $('#lsd_element_' + key).addClass('lsd-element-disabled');
        }
        // Enabling the element
        else
        {
            $(this).addClass('lsd-util-hide');
            $('#lsd_actions_' + key + ' .lsd-enabled').removeClass('lsd-util-hide');

            $('input[name="lsd[elements][' + key + '][enabled]"]').val('1');
            $('#lsd_element_' + key).removeClass('lsd-element-disabled');
        }
    });

    // Details Page Style Switcher
    $('#lsd_details_page_style').on('change', function ()
    {
        const style = $(this).val();
        const $elements = $('ul.lsd-elements');
        const $builder_switcher = $('#lsd_dynamic_switcher');

        $('.lsd-style-dependency').addClass('lsd-util-hide');
        $('.lsd-style-dependency-' + style).removeClass('lsd-util-hide');

        // Disable Drag & Drop
        if (style !== 'style1')
        {
            $elements.removeClass('lsd-sortable');
            $elements.sortable('disable');
        }
        else
        {
            $elements.addClass('lsd-sortable');
            $elements.sortable('enable');
        }

        // Design Builder
        if (style === 'dynamic') $builder_switcher.removeClass('lsd-util-hide');
        else
        {
            $builder_switcher.find($('li[data-tab=config] a')).trigger('click');
            $builder_switcher.addClass('lsd-util-hide');
        }
    }).trigger('change');

    // Style 3 Elements
    $('.lsd-builder-column input[type=checkbox]').on('change', function ()
    {
        const $input = $(this);
        const $li = $input.parent();
        const key = $li.data('key');

        if ($input.is(':checked')) $li.removeClass('lsd-builder-element-disabled').addClass('lsd-builder-element-enabled');
        else $li.removeClass('lsd-builder-element-enabled').addClass('lsd-builder-element-disabled');

        // Map Element
        if (key === 'map' && $input.is(':checked'))
        {
            $(`.lsd-builder-column li[data-key=${key}] input[type=checkbox]`).each(function (i, e)
            {
                if ($input.prop('id') !== $(e).prop('id'))
                {
                    $(e).prop('checked', false).trigger('change');
                }
            });
        }
    });

    // Map Provider Switcher
    $('.lsd-map-provider-toggle').on('change', function ()
    {
        const parent = $(this).data('parent');
        const provider = $(this).val();

        $(parent + ' .lsd-map-provider-dependency').hide();
        $(parent + ' .lsd-map-provider-dependency-' + provider).show();
    }).trigger('change');

    $('.lsd-default-view-toggle').on('change', function ()
    {
        const parent = $(this).data('parent');
        const provider = $(this).val();

        $(parent + ' .lsd-default-view-dependency').hide();
        $(parent + ' .lsd-default-view-dependency-' + provider).show();
    }).trigger('change');

    function lsdUpdateStyleAwareSelect($select, style)
    {
        if (!$select || !$select.length) return;

        let optionsMap = $select.data('styleOptionsMap');

        if (!optionsMap)
        {
            const rawOptions = $select.attr('data-style-options');
            if (!rawOptions) return;

            try
            {
                optionsMap = JSON.parse(rawOptions);
            }
            catch (err)
            {
                optionsMap = null;
            }

            if (!optionsMap || typeof optionsMap !== 'object') return;

            $select.data('styleOptionsMap', optionsMap);
        }

        const mapKeys = Object.keys(optionsMap);
        if (!mapKeys.length) return;

        let activeStyle = style;
        if (!activeStyle || typeof optionsMap[activeStyle] === 'undefined')
        {
            const declaredActive = $select.attr('data-style-options-active');
            if (declaredActive && typeof optionsMap[declaredActive] !== 'undefined') activeStyle = declaredActive;
            else if (typeof optionsMap.default !== 'undefined') activeStyle = 'default';
            else activeStyle = mapKeys[0];
        }

        const optionSet = optionsMap[activeStyle];
        if (!optionSet || typeof optionSet !== 'object') return;

        const previousValue = $select.val();
        const nativeSelect = $select.get(0);
        if (!nativeSelect) return;

        const optionKeys = Object.keys(optionSet);
        if (!optionKeys.length) return;

        nativeSelect.options.length = 0;

        optionKeys.forEach((value) =>
        {
            const option = document.createElement('option');
            option.value = value;
            option.textContent = optionSet[value];
            nativeSelect.appendChild(option);
        });

        let nextValue = previousValue;
        if (!Object.prototype.hasOwnProperty.call(optionSet, nextValue))
        {
            nextValue = optionKeys[0];
        }

        const valueChanged = nextValue !== previousValue;

        if (typeof nextValue !== 'undefined' && nextValue !== null) $select.val(nextValue);

        $select.attr('data-style-options-active', activeStyle);

        if (valueChanged) $select.trigger('change');
    }

    function lsdUpdateStyleAwareSelects(selector, style)
    {
        if (!selector) return;

        $(selector + ' select[data-style-options]').each(function ()
        {
            lsdUpdateStyleAwareSelect($(this), style);
        });
    }

    $('.lsd-display-options-style-toggle').on('change', function ()
    {
        const style = $(this).val();
        const parentData = $(this).data('parent');
        let parents = [];

        if (Array.isArray(parentData)) parents = parentData;
        else if (typeof parentData === 'string') parents = parentData.split(',');
        else if (parentData) parents = [parentData];

        if (!parents.length)
        {
            const $closest = $(this).closest('.lsd-skin-display-options');
            if ($closest.length) parents = ['#' + $closest.attr('id')];
        }

        parents
        .map((selector) => (selector || '').toString().trim())
        .filter(Boolean)
        .forEach((selector) =>
        {
            $(selector + ' .lsd-display-options-style-dependency').hide();
            $(selector + ' .lsd-display-options-style-dependency-' + style).show();

            $(selector + ' .lsd-display-options-style-not-for').show();
            $(selector + ' .lsd-display-options-style-not-for-' + style).hide();

            // Custom Style
            if (!isNaN(style))
            {
                $(selector + ' .lsd-display-options-style-dependency-custom').show();
                $(selector + ' .lsd-display-options-style-not-for-custom').hide();
            }

            lsdUpdateStyleAwareSelects(selector, style);
        });
    }).trigger('change');

    $('.lsd-gallery-element-style-toggle').on('change', function ()
    {
        const parent = $(this).data('parent');
        const style = $(this).val();

        $(parent + ' .lsd-gallery-element-style-dependency').hide();
        $(parent + ' .lsd-gallery-element-style-dependency-' + style).show();

        $(parent + ' .lsd-gallery-element-style-not-for').show();
        $(parent + ' .lsd-gallery-element-style-not-for-' + style).hide();

        // Custom Style
        if (!isNaN(style))
        {
            $(parent + ' .lsd-gallery-element-style-dependency-custom').show();
            $(parent + ' .lsd-gallery-element-style-not-for-custom').hide();
        }
    }).trigger('change');

    $('.lsd-do-listing-link').on('change', function ()
    {
        const $parent = $(this).parent().parent().parent();
        const method = $(this).val();

        $parent.find($('.lsd-do-listing-link-dependent')).addClass('lsd-util-hide');
        $parent.find($('.lsd-do-listing-link-dependent-' + method)).removeClass('lsd-util-hide');
    }).trigger('change');

    /**
     * Add/Edit Shortcode
     */
    // Skin Changer
    $('#lsd_display_options_skin').on('change', function ()
    {
        const skin = $(this).val();

        $('.lsd-skin-display-options').hide();
        $('#lsd_skin_display_options_' + skin).show();
        $('#lsd_skin_display_options_map_' + skin).show();
        $('#lsd_skin_display_options_layout_' + skin).show();

        // Toggle Map Options
        const $map_options_tab = $('.lsd-display-options-map-tab');
        if (skin === 'side' || skin === 'table' || skin === 'masonry' || skin === 'carousel' || skin === 'cover' || skin === 'slider') $map_options_tab.hide();
        else $map_options_tab.show();

        // Toggle Search Options
        const $search_options = $('#lsd_metabox_search');
        const $search_options_tab = $('.lsd-display-options-search-tab');

        if (skin === 'carousel' || skin === 'slider' || skin === 'cover')
        {
            $search_options.hide();
            $search_options_tab.hide();
        }
        else
        {
            $search_options.show();
            $search_options_tab.show();
        }

        // Toggle Filter & Default Sort Options
        const $filter_options = $('#lsd_metabox_filter_options');
        const $filter_options_tab = $('.lsd-display-options-filter-options-tab');
        const $default_sort_options = $('#lsd_metabox_default_sort');
        const $default_sort_options_tab = $('.lsd-display-options-sort-tab');

        if (skin === 'cover')
        {
            $filter_options.hide();
            $filter_options_tab.hide();
            $default_sort_options.hide();
            $default_sort_options_tab.hide();
        }
        else
        {
            $filter_options.show();
            $filter_options_tab.show();
            $default_sort_options.show();
            $default_sort_options_tab.show();
        }

        const $elements_options_tab = $('.lsd-display-options-elements-tab');

        // Builder Message for singlemap skin
        if (skin === 'singlemap')
        {
            $('.lsd-display-options-builder-skin').addClass('lsd-util-hide');
            $elements_options_tab.hide();
        }
        else $elements_options_tab.show();

        // Toggle Sort Options
        const $sort_options = $('#lsd_metabox_sort_options');
        const $sort_options_tab = $('#lsd_metabox_sort_options_tab');

        if (skin === 'singlemap' || skin === 'masonry' || skin === 'carousel' || skin === 'slider' || skin === 'cover')
        {
            $sort_options.hide();
            $sort_options_tab.hide();
        }
        else
        {
            $sort_options.show();
            $sort_options_tab.show();
        }

        // Toggle Map Control Options
        $('#lsd_display_options_skin_list_map_provider,#lsd_display_options_skin_grid_map_provider,#lsd_display_options_skin_accordion_map_provider,#lsd_display_options_skin_mosaic_map_provider,#lsd_display_options_skin_timeline_map_provider,#lsd_display_options_skin_listgrid_map_provider,#lsd_display_options_skin_halfmap_map_provider,#lsd_display_options_skin_singlemap_map_provider,#lsd_display_options_skin_gallery_map_provider').trigger('change');

        // Toggle Style Change
        $('.lsd-display-options-style-selector').trigger('change');
    }).trigger('change');

    $(document).on('click', '.lsd-copy', function ()
    {
        const $button = $(this);
        const target = $button.data('target');
        const $targetEl = $('#' + target).length
            ? $('#' + target)
            : $('.' + target);
        const buttonHTML = $button.html();
        const copiedText = $button.data('copied');
        const copyData = $targetEl.data('lsd-copy');
        const textToCopy = (typeof copyData !== 'undefined' ? copyData : $targetEl.text()).trim();

        const showCopiedText = () =>
        {
            $button.text(copiedText);
            setTimeout(() => $button.html(buttonHTML), 6000);
        };

        const fallbackCopy = (text) =>
        {
            const scrollX = window.scrollX || window.pageXOffset;
            const scrollY = window.scrollY || window.pageYOffset;

            const $textarea = $('<textarea>')
            .val(text)
            .css({
                position: 'fixed',
                top: '0',
                left: '0',
                width: '1px',
                height: '1px',
                opacity: '0',
                zIndex: '-1'
            })
            .appendTo('body');

            $textarea[0].focus();
            $textarea[0].select();

            try
            {
                const successful = document.execCommand('copy');
                if (successful) showCopiedText();
            } catch (err) {}

            $textarea.remove();

            // Restore scroll position
            window.scrollTo(scrollX, scrollY);
        };

        if (navigator.clipboard && navigator.clipboard.writeText)
        {
            navigator.clipboard.writeText(textToCopy)
            .then(showCopiedText)
            .catch(() => fallbackCopy(textToCopy));
        } else fallbackCopy(textToCopy);
    });

    // Google Maps Status Changer
    $('#lsd_display_options_skin_list_map_provider,#lsd_display_options_skin_accordion_map_provider,#lsd_display_options_skin_mosaic_map_provider,#lsd_display_options_skin_timeline_map_provider,#lsd_display_options_skin_grid_map_provider,#lsd_display_options_skin_listgrid_map_provider,#lsd_display_options_skin_halfmap_map_provider,#lsd_display_options_skin_singlemap_map_provider,#lsd_display_options_skin_gallery_map_provider').on('change', function ()
    {
        const skin = $('#lsd_display_options_skin').val();
        const map_provider = $('#lsd_display_options_skin_' + skin + '_map_provider').val();

        // Map Options
        const $map_control_options = $('#lsd_metabox_map_controls');
        const $map_control_tab = $('.lsd-metabox-map-controls-tab');
        const $map_options = $('.lsd_display_options_skin_' + skin + '_map_options');

        if (typeof map_provider === 'undefined' || map_provider === '0')
        {
            $map_options.addClass('lsd-util-hide');
            $map_control_options.hide();
            $map_control_tab.hide();
        }
        else if (map_provider === 'leaflet')
        {
            $map_options.removeClass('lsd-util-hide');
            $map_control_options.hide();
            $map_control_tab.hide();
        }
        else
        {
            $map_options.removeClass('lsd-util-hide');
            $map_control_options.show();
            $map_control_tab.show();
        }
    }).trigger('change');

    // Style Changer
    $('.lsd-display-options-style-selector').on('change', function ()
    {
        if (!$(this).is(':visible')) return;

        const style = $(this).val();

        const $message = $('.lsd-display-options-builder-skin');
        const $options = $('.lsd-display-options-builder-option');

        // Builder Style
        if (!isNaN(parseFloat(style)) && isFinite(style))
        {
            $message.removeClass('lsd-util-hide');
            $options.addClass('lsd-util-hide');
        }
        else
        {
            $message.addClass('lsd-util-hide');
            $options.removeClass('lsd-util-hide');
        }
    }).trigger('change');

    // Default Sort Option
    $('#lsd_sort_options_orderby').on('change', function ()
    {
        const value = $(this).val();
        const $input = $('#lsd-sort-options-' + value).find('input');
        const disabled = $input.filter(':disabled').length > 0;

        if (disabled) $('.lsd-sort-option-toggle[data-key="' + value + '"]').trigger('click');
    });

    // Enabling / Disabling Sort Option
    $('.lsd-sort-option-toggle').on('click', function ()
    {
        const key = $(this).data('key');
        const $input = $('#lsd-sort-options-' + key + '-status');
        const $row = $('#lsd-sort-options-' + key);

        const current_status = $input.val();
        if (parseInt(current_status) === 1) // Make it Disable
        {
            $input.val(0);
            $row.find('input[type=text], select').attr('disabled', 'disabled');

            // Toggle Icon
            $(this).find('i').removeClass('fa-check-circle').addClass('fa-minus-circle');

            $row.addClass('lsd-metabox-sort-option-disable');
        }
        else // Make it Enable
        {
            $input.val(1);
            $row.find('input[type=text], select').removeAttr('disabled');

            // Toggle Icon
            $(this).find('i').removeClass('fa-minus-circle').addClass('fa-check-circle');

            $row.removeClass('lsd-metabox-sort-option-disable');
        }
    });

    /**
     * Add / Edit Notifications
     */
    // Hook Listener
    $('#lsd_notification_content_hook').on('change', function ()
    {
        const hook = $(this).val();

        // Hide All Placeholders
        $('.lsd-placeholder-item').hide();

        // Show Hook Placeholders
        $('.lsd-placeholder-' + hook).show();

        // Show General Placeholders
        $('.lsd-placeholder-general').show();
    }).trigger('change');

    // Franchise Options
    $('#lsd_addons_franchise_category').on('change', function ()
    {
        const category = $(this).val();
        const $wrapper = $('.lsd-franchise-certain-category');

        if (category) $wrapper.removeClass('lsd-util-hide');
        else $wrapper.addClass('lsd-util-hide');
    });

    // Tab Switcher
    $('.lsd-tab-switcher li a').on('click', function (e)
    {
        e.preventDefault();

        const $tab = $(this).parent();
        const $tabs = $tab.parent();
        const content = $tabs.data('for');
        const $contents = $(content);

        $tabs.find($('li')).removeClass('lsd-sub-tabs-active');
        $tab.addClass('lsd-sub-tabs-active');

        const target = $tab.data('tab');

        $contents.removeClass('lsd-tab-switcher-content-active');
        $(`#lsd-tab-switcher-${target}-content`).addClass('lsd-tab-switcher-content-active');
    });

    // Skin Switcher
    $('.lsd-skin-style-options').on('click', '.lsd-skin-style-option', function ()
    {
        const $skin = $(this);
        const skin = $skin.data('skin');

        $('#lsd_display_options_skin').val(skin).trigger('change');

        $('.lsd-skin-style-option').removeClass('selected');
        $skin.addClass('selected');

        $('.lsd-skin-content').addClass('lsd-util-hide');

        // Show the content for selected skin
        $(`.lsd-skin-content[data-skin="${skin}"]`).removeClass('lsd-util-hide');
    });

    const selectedSkin = $('#lsd_display_options_skin').val();

    if (!selectedSkin)
    {
        const $listgridSkin = $('.lsd-skin-style-option[data-skin="listgrid"]');
        if ($listgridSkin.length) $listgridSkin.trigger('click');
    }

    /**
     * Listdom Additional Dashboard Menus
     */
    $('.lsd-settings-dashboard-menus').sortable({
        cancel: ".lsd-custom-menu-content"
    });

    $('body').on('click', '.lsd-custom-menu-btn', function ()
    {
        const $newMenuItem = $('<li class="lsd-custom-menu-list"><p><span class="lsd-custom-menu-label">' + lsd.i18n_field_label + '</span><span class="lsd-custom-menu-actions"><i class="fas fa-trash"></i> <i class="fas fa-chevron-down"></i></span></p><div class="lsd-custom-menu-content"></div></li>');
        $('.lsd-settings-dashboard-menus').append($newMenuItem);

        const $labelGroup = $newMenuItem.find('.lsd-custom-menu-content');
        const $inputHiddenMenu = $('<input>', {
            type: 'hidden',
            name: 'lsd[dashboard_menus][]',
            class: 'custom-menu-id'
        });

        const fields = ['Label', 'Slug', 'Icon', 'Content'];
        const placeholders = {
            Label: lsd.i18n_placeholder_label,
            Slug: lsd.i18n_placeholder_slug,
            Icon: lsd.i18n_placeholder_icon,
            Content: ''
        };
        const descriptions = {
            Label: lsd.i18n_description_label,
            Slug: lsd.i18n_description_slug,
            Icon: lsd.i18n_description_icon,
            Content: lsd.i18n_description_content
        };

        fields.forEach((field) =>
        {
            const uniqueName = field.toLowerCase().replace(/\s+/g, '_');
            const $fieldGroup = $('<div class="lsd-field-group"></div>');

            const $label = $('<label class="lsd-fields-label-tiny"></label>').text(field);
            $fieldGroup.append($label);

            let $inputEl;

            if (field === 'Icon')
            {
                $inputEl = $('<select>', {
                    name: '',
                    required: true,
                    'data-field': 'icon',
                    class: 'lsd-iconpicker',
                    id: 'lsd_icon'
                });

                lsd.icon_options.forEach(iconOption =>
                {
                    $inputEl.append($('<option>', {
                        value: iconOption.value,
                        text: iconOption.label
                    }));
                });

                setTimeout(() => {
                    if (typeof $.fn.fontIconPicker !== 'undefined') {
                        $inputEl.fontIconPicker({
                            emptyIcon: false,
                            emptyIconValue: ''
                        });
                    }
                }, 0);
            }
            else if (field === 'Content')
            {
                const uniqueId = 'textarea_' + Date.now();
                $inputEl = $('<textarea>', {
                    name: '',
                    id: uniqueId,
                    'data-field': 'content',
                });

                setTimeout(() =>
                {
                    wp.editor.initialize(uniqueId, {
                        tinymce: {
                            toolbar1: 'formatselect bold italic underline bullist numlist blockquote alignleft aligncenter alignright link unlink wp_more fullscreen',
                            plugins: 'lists paste link',
                        },
                        quicktags: true,
                        mediaButtons: true,
                    });
                }, 10);
            }
            else
            {
                $inputEl = $('<input>', {
                    type: 'text',
                    name: '',
                    required: true,
                    'data-field': uniqueName,
                    placeholder: placeholders[field],
                    class: 'lsd-admin-input'
                });
            }

            $fieldGroup.append($inputEl);
            $fieldGroup.append($('<p></p>').text(descriptions[field]).addClass('lsd-admin-description-tiny lsd-mb-0 lsd-mt-2'));

            $labelGroup.append($fieldGroup);
        });

        $newMenuItem.append($inputHiddenMenu);
    });

    $(document).on('input', '.lsd-custom-menu-list input[data-field="slug"]', function ()
    {
        const $slugInput = $(this);
        const $thisList = $slugInput.closest('.lsd-custom-menu-list');
        const $inputHiddenMenu = $thisList.find('.custom-menu-slug');
        const $thisListContent = $slugInput.closest('.lsd-custom-menu-content');
        const $inputField = $thisListContent.find('input[data-field]');
        const $iconField = $thisListContent.find('select.lsd-iconpicker');
        const $textField = $thisListContent.find('textarea');
        const slugValue = $slugInput.val();

        const slugValueSanitize = slugValue
        .toLowerCase()
        .replace(/[^a-z0-9\s-]/g, '')
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-')
        .replace(/^-+|-+$/g, '');

        $inputHiddenMenu.val(slugValueSanitize);

        $inputField.each(function ()
        {
            const $field = $(this);
            const fieldName = $field.data('field');
            $field.attr('name', `lsd[dashboard_menu_custom][${slugValueSanitize}][${fieldName}]`);
        });

        $iconField.each(function ()
        {
            const $field = $(this);
            $field.attr('name', `lsd[dashboard_menu_custom][${slugValueSanitize}][icon]`);
        });

        $textField.each(function ()
        {
            const $field = $(this);
            $field.attr('name', `lsd[dashboard_menu_custom][${slugValueSanitize}][content]`);
        });

        $slugInput.val(slugValueSanitize);
        $inputHiddenMenu.val(slugValueSanitize);
    });

    $(document).on('input', '.lsd-custom-menu-list input[data-field="label"]', function ()
    {
        const $thisList = $(this).closest('.lsd-custom-menu-list');
        $thisList.find('.lsd-custom-menu-label').html($(this).val());
    });

    $(document).on('blur', '.lsd-custom-menu-list input[data-field="label"]', function ()
    {
        const $labelInput = $(this);
        const $thisList = $labelInput.closest('.lsd-custom-menu-list');
        const $slugInput = $thisList.find('input[data-field="slug"]');

        if ($slugInput.val().trim() === '')
        {
            const labelValue = $labelInput.val().trim();
            const slugValue = labelValue.toLowerCase().replace(/\s+/g, '-');
            $slugInput.val(slugValue).trigger('input');
        }
    });

    $(document).on('click', '.lsd-custom-menu-list .fa-trash', function ()
    {
        $(this).closest('.lsd-custom-menu-list').remove();
    });

    $(document).on('click', '.lsd-custom-menu-list .fa-chevron-down', function ()
    {
        const $thisListContent = $(this).closest('.lsd-custom-menu-list').find('.lsd-custom-menu-content');
        $thisListContent.toggle();
    });

    /**
     * Listdom Style Category Change
     */
    const $detail_type_select = $('#lsd_details_page_detail_type');
    const $style_select = $('#lsd_details_page_style');

    const original_options = $style_select.find('option').map(function ()
    {
        return {
            value: $(this).val().trim(),
            text: $(this).text()
        };
    }).get();

    function filterOptions(type)
    {
        return original_options.filter(option =>
        {
            if (type === 'premade') return option.value.includes('style');
            if (type === 'dynamic') return option.value === 'dynamic';
            if (type === 'elementor') return !isNaN(option.value);
            return true;
        });
    }

    function updateStyleOptions(filtered_options)
    {
        const current = $style_select.val();

        $style_select.empty();
        if (filtered_options.length === 0)
        {
            $style_select.append($('<option></option>').attr('value', '').text('No Style exists'));
        }
        else
        {
            filtered_options.forEach(option =>
            {
                $style_select.append($('<option></option>').attr('value', option.value).text(option.text));
            });

            if ($style_select.find(`option[value="${current}"]`).length)
            {
                $style_select.val(current);
            }
        }

        $style_select.trigger('change');
    }

    $detail_type_select.change(function ()
    {
        const type = $(this).val();
        const filtered_options = filterOptions(type);
        updateStyleOptions(filtered_options);
    });

    $detail_type_select.trigger('change');

    /**
     * Listing Quick Edit
     */
    if (typeof inlineEditPost !== 'undefined')
    {
        const wp_inline_edit_function = inlineEditPost.edit;
        inlineEditPost.edit = function (post_id)
        {
            wp_inline_edit_function.apply(this, arguments);

            if (typeof (post_id) === 'object') post_id = parseInt(this.getId(post_id));

            const edit_row = $('#edit-' + post_id)
            const post_row = $('#post-' + post_id)
            const value = $('.column-style', post_row).text()

            $('#lsd_displ_style', edit_row).val(value);
        }
    }

    if (typeof $.fn.select2 !== 'undefined')
    {
        jQuery('#lsd_metabox_filter_options select').select2(
        {
            allowClear: true
        });
    }

    jQuery('#lsd_display_options_skin_mosaic_limit').on('blur', function ()
    {
        let $input = jQuery(this);
        let val = parseInt($input.val(), 10);
        let original = parseInt($input.prop('defaultValue'), 10) || 12;

        if (isNaN(val) || val % 2 !== 0)
        {
            $input.val(original);
        }
    });

    jQuery('.post-type-listdom-listing #post').on('submit', function (e)
    {
        let isValid = true;

        // Validate required checkbox groups
        $('.lsd-attribute-checkbox[data-required="1"]:visible').each(function ()
        {
            const $group = $(this);
            const $checkboxes = $group.find('input[type="checkbox"]');
            const isChecked = $checkboxes.is(':checked');
            const requiredMessage = $group.data('required-message');

            if (!isChecked)
            {
                isValid = false;

                $group.addClass('lsd-checkbox-error');

                if ($group.find('.lsd-checkbox-error-msg').length === 0)
                {
                    $group.append('<div class="lsd-checkbox-error-msg lsd-alert lsd-error">' + requiredMessage + '</div>');
                }
            }
            else
            {
                $group.removeClass('lsd-checkbox-error');
                $group.find('.lsd-checkbox-error-msg').remove();
            }
        });

        // Validate required radio groups if browser validation is disabled
        $('.lsd-attribute-radio[data-required="1"]:visible').each(function ()
        {
            const $group = $(this);
            const $radios = $group.find('input[type="radio"]');
            const isSelected = $radios.is(':checked');
            const requiredMessage = $group.data('required-message') || 'Please select at least one option.';

            if (!isSelected)
            {
                isValid = false;

                $group.addClass('lsd-checkbox-error');

                if ($group.find('.lsd-checkbox-error-msg').length === 0)
                {
                    $group.append('<div class="lsd-checkbox-error-msg lsd-alert lsd-error">' + requiredMessage + '</div>');
                }
            }
            else
            {
                $group.removeClass('lsd-checkbox-error');
                $group.find('.lsd-checkbox-error-msg').remove();
            }
        });

        // Validate required image fields
        $('.lsd-attribute-image[data-required="1"]:visible').each(function ()
        {
            const $this = $(this);
            const $input = $(this).find('input[type=hidden]');
            const value = $input.val();
            const requiredMessage = $this.data('required-message');
            const $placeholder = $('#' + $input.attr('id') + '_img');

            if (!value)
            {
                isValid = false;
                $placeholder.addClass('lsd-checkbox-error');

                if ($placeholder.next('.lsd-image-error-msg').length === 0)
                {
                    $placeholder.after('<div class="lsd-image-error-msg lsd-alert lsd-error">' + requiredMessage + '</div>');
                }
            }
            else
            {
                $placeholder.removeClass('lsd-checkbox-error');
                $placeholder.next('.lsd-image-error-msg').remove();
            }
        });

        if (!isValid)
        {
            e.preventDefault();
            $('html, body').animate({
                scrollTop: $('.lsd-checkbox-error:first').offset().top - 100
            }, 300);
        }
    });

    jQuery(function ($)
    {
        const params = new URLSearchParams(window.location.search);
        const tab = params.get('tab');
        const accordion = params.get('accordion');

        if (tab && accordion)
        {
            const $target = $(`.lsd-accordion-title-${tab}-${accordion}`);

            if ($target.length)
            {
                $target.trigger('click');

                $('html, body').animate({
                    scrollTop: $target.offset().top - 100
                }, 500, function ()
                {

                    // Remove 'accordion' param from URL
                    params.delete('accordion');
                    const newUrl = `${window.location.pathname}?${params.toString()}`;
                    window.history.replaceState({}, '', newUrl);
                });
            }
        }
    });
});

jQuery(function ($)
{
    const ACTIVE_CLASS = 'lsd-nav-tab-active';

    const makeContentPanelId = (parent, child) => `lsd_panel_${parent}_${child}`;

    function updateURL(parent, child) {
        const url = new URL(window.location);
        url.searchParams.set('tab', parent);
        url.searchParams.set('subtab', child);
        history.replaceState(null, '', url);
    }

    function updateCustomizerTitle(parentKey, childKey) {
        if (parentKey !== 'customizer') return;

        const $panel = $(`#${makeContentPanelId(parentKey, childKey)}`);

        if ($panel.length && $panel.data('title'))
        {
            const $titleEl = $('#lsd_customizer_tab_title');
            const $resetButton = $('.lsd-customizer-reset-category');
            $titleEl.text($panel.data('title'));

            // Also update category if needed
            const category = $panel.data('category');
            if (category) $resetButton.attr('data-category', category);
        }
    }

    function activateChildTab($tab) {
        const parentKey = $tab.closest('.lsd-nav-sub-tabs').data('parent');
        const childKey = $tab.data('key');

        $tab.addClass(ACTIVE_CLASS)
        .siblings('.lsd-nav-tab').removeClass(ACTIVE_CLASS);

        const $newPanel = $(`#${makeContentPanelId(parentKey, childKey)}`);
        const $currentPanel = $('.lsd-tab-content-active');

        if ($newPanel.length && !$newPanel.is($currentPanel)) {
            if ($currentPanel.length) {
                $currentPanel.removeClass('lsd-tab-content-active').fadeOut(150, function () {
                    $('.lsd-tab-content').attr('hidden', true);
                    $newPanel.removeAttr('hidden').hide().fadeIn(150).addClass('lsd-tab-content-active');
                });
            } else {
                // No currently active panel, so just show new panel directly
                $('.lsd-tab-content').attr('hidden', true);
                $newPanel.removeAttr('hidden').hide().fadeIn(150).addClass('lsd-tab-content-active');
            }
        }

        updateCustomizerTitle(parentKey, childKey);
        updateURL(parentKey, childKey);
    }

    // On subtab click
    $('.lsd-nav-sub-tabs').on('click', '.lsd-nav-tab', function () {activateChildTab($(this));});
    // Trigger active subtab only within the active parent tab
    $('.lsd-nav-tab-wrapper > li > a.lsd-nav-tab-active')
        .closest('li')
        .find('.lsd-nav-sub-tabs .lsd-nav-tab.lsd-nav-tab-active')
        .trigger('click');
});

jQuery(function ($)
{
    $('.lsd-switch-confirm').each(function()
    {
        const $wrapper = jQuery(this);
        const $checkbox = $wrapper.find('input[type="checkbox"]');
        const $box = $wrapper.find('.lsd-switch-confirm-box');

        $checkbox.on('change', function()
        {
            if (!$checkbox.is(':checked')) $box.removeClass('lsd-util-hide');
            else $box.addClass('lsd-util-hide');
        });

        $box.find('.lsd-switch-confirm-cancel').on('click', function()
        {
            $checkbox.prop('checked', true).trigger('change');
            $box.addClass('lsd-util-hide');
        });

        $box.find('.lsd-switch-confirm-accept').on('click', function()
        {
            $box.addClass('lsd-util-hide');
        });
    });
});
