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
        const $body = $('body');
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

        function apply_search_style_classes()
        {
            const style = ($search_style.val() || 'default').toString();

            if (style === 'mobile_app')
            {
                const active_device = ($builder.data('active-device') || 'desktop').toString();
                if (active_device !== 'desktop') show_device('desktop', false);
            }

            const clear_style_classes = function ($element)
            {
                const classes = ($element.attr('class') || '')
                    .split(/\s+/)
                    .filter((class_name) => class_name && class_name.indexOf('lsd-search-style-') !== 0);

                $element.attr('class', classes.join(' '));
            };

            clear_style_classes($builder);
            clear_style_classes($body);

            $builder.addClass('lsd-search-style-' + style);
            $body.addClass('lsd-search-style-' + style.replace(/_/g, '-'));

            sync_mobile_app_method_options();
        }

        function sync_mobile_app_method_options($context)
        {
            const style = ($search_style.val() || 'default').toString();
            const is_mobile_app = style === 'mobile_app';
            const $root = ($context && $context.length) ? $context : $builder;

            $root.find('.lsd-search-method').each(function ()
            {
                const $method = $(this);
                const previous_method = ($method.val() || '').toString();
                const method_options = $method.data('lsd-method-options') || $method.html();

                if (!$method.data('lsd-method-options')) $method.data('lsd-method-options', method_options);

                $method.html(method_options);

                if (is_mobile_app)
                {
                    ['hierarchical', 'dropdown-multiple', 'radio'].forEach(function (method)
                    {
                        $method.find('option[value="' + method + '"]').remove();
                    });
                }

                if ($method.find('option[value="' + previous_method + '"]').length)
                {
                    $method.val(previous_method);
                    return;
                }

                if ($method.find('option').length) $method.prop('selectedIndex', 0).trigger('change');
            });
        }

        function init_iconpicker($context)
        {
            if (typeof $.fn.fontIconPicker === 'undefined') return;

            const $root = ($context && $context.length) ? $context : $builder;
            $root.find('select.lsd-iconpicker').each(function ()
            {
                const $iconpicker = $(this);
                if ($iconpicker.next('.icons-selector').length) return;

                $iconpicker.fontIconPicker(
                {
                    emptyIcon: false,
                    emptyIconValue: '',
                });
            });
        }

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
                apply_search_style_classes();
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

            $(document)
                .off('click', '.lsd-search-field-actions-visibility')
                .on('click', '.lsd-search-field-actions-visibility', function ()
            {
                field_visibility($(this));
            });

            $(document)
                .off('click', '.lsd-search-field-param-title-visibility')
                .on('click', '.lsd-search-field-param-title-visibility', function ()
            {
                field_title_visibility($(this));
            });

            $(document)
                .off('change', '.lsd-search-row-params .lsd-switch input[type=checkbox]')
                .on('change', '.lsd-search-row-params .lsd-switch input[type=checkbox]', function ()
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

            $field_method.off('change').on('change', function (event, options)
            {
                method_changed($(this), options || {});
            });

            $(document)
                .off('change', '.lsd-search-field select[name$="[buttons_multiple]"]')
                .on('change', '.lsd-search-field select[name$="[buttons_multiple]"]', function ()
            {
                sync_button_default_inputs($(this).closest('.lsd-search-field'));
            });

            sync_mobile_app_method_options();
            $field_method.trigger('change', {initial: true});

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

            $(document)
                .off('change', '.lsd-more-options-type-toggle')
                .on('change', '.lsd-more-options-type-toggle', function ()
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
                            init_iconpicker($row);
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

        function show_device(device, reset_listeners = true)
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

            if (reset_listeners) setListeners();
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
                            });

                            sync_mobile_app_method_options($field);
                            init_iconpicker($field);
                            $field.find('.lsd-search-method').trigger('change');
                            sync_button_default_inputs($field);

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

        function method_changed($method, options = {})
        {
            const $field = $method.closest('.lsd-search-field');
            const aiMethod = ($method.data('ai-method') || 'ai-search').toString();
            const fallbackMethod = ($method.data('ai-fallback') || 'text-input').toString();
            const hasAiProfiles = parseInt($method.data('has-ai-profiles'), 10) === 1;

            if (($method.val() || '').toString() === aiMethod && !hasAiProfiles)
            {
                const message = ($method.data('ai-warning') || '').toString();

                if (message && !options.initial)
                {
                    if (typeof window.listdom_toastify === 'function') window.listdom_toastify(message, 'lsd-warning');
                    else window.alert(message);
                }

                $method.val(fallbackMethod);
            }

            const method = ($method.val() || '').toString();

            // Hide All Dependant Fields
            $field.find('.lsd-search-method-dependant').hide();

            // Show Related Fields
            $field.find('.lsd-search-method-' + method).show();

            sync_button_default_inputs($field);
        }

        function sync_button_default_inputs($field)
        {
            const $single = $field.find('.lsd-search-buttons-single-default');
            const $multiple = $field.find('.lsd-search-buttons-multiple-default');
            if (!$single.length && !$multiple.length) return;

            const method = ($field.find('.lsd-search-method').val() || '').toString();
            if (method !== 'buttons')
            {
                $single.removeClass('lsd-util-hide');
                $multiple.removeClass('lsd-util-hide');
                return;
            }

            const isMultiple = parseInt($field.find('select[name$="[buttons_multiple]"]').val(), 10) === 1;

            if (isMultiple)
            {
                $single.addClass('lsd-util-hide').hide();
                $multiple.removeClass('lsd-util-hide').show();
            }
            else
            {
                $multiple.addClass('lsd-util-hide').hide();
                $single.removeClass('lsd-util-hide').show();
            }
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
     * CTA Controls
     */
    function listdomUpdateCtaEnable($toggle, triggered)
    {
        if (!$toggle || !$toggle.length) return;

        const $root = $toggle.closest('[data-lsd-cta-root]');
        if (!$root.length) return;

        const enabled = $toggle.is(':checked');
        const $settings = $root.find('[data-lsd-cta-settings]');
        if ($settings.length) $settings.toggleClass('lsd-util-hide', !enabled);

        const $modeInput = $root.find('[data-lsd-cta-mode]');
        if (!enabled)
        {
            if ($modeInput.length) $modeInput.val('disabled');

            const $customFields = $root.find('[data-lsd-cta-custom-fields]');
            if ($customFields.length) $customFields.addClass('lsd-util-hide');

            return;
        }

        const $modeToggle = $root.find('[data-lsd-cta-mode-toggle] input[type="checkbox"]');
        if ($modeToggle.length)
        {
            const $preference = $root.find('[data-lsd-cta-mode-preference]');
            if ($preference.length)
            {
                const preferred = $preference.val();
                if (preferred === 'custom') $modeToggle.prop('checked', false);
                else $modeToggle.prop('checked', true);
            }

            listdomUpdateCtaMode($modeToggle, triggered);
        }
        else if ($modeInput.length)
        {
            $modeInput.val('custom');
        }
    }

    function listdomUpdateCtaMode($toggle, triggered)
    {
        if (!$toggle || !$toggle.length) return;

        const $root = $toggle.closest('[data-lsd-cta-root]');
        if (!$root.length) return;

        const $modeInput = $root.find('[data-lsd-cta-mode]');
        const $preference = $root.find('[data-lsd-cta-mode-preference]');
        const $enableToggle = $root.find('[data-lsd-cta-enable-toggle] input[type="checkbox"]');
        const enabled = !$enableToggle.length || $enableToggle.is(':checked');

        const $customFields = $root.find('[data-lsd-cta-custom-fields]');

        if (!enabled)
        {
            if ($modeInput.length) $modeInput.val('disabled');
            if ($customFields.length) $customFields.addClass('lsd-util-hide');
            return;
        }

        const inherit = $toggle.is(':checked');
        const mode = inherit ? 'inherit' : 'custom';

        if ($modeInput.length) $modeInput.val(mode);
        if ($preference.length && mode !== 'disabled') $preference.val(mode);
        if ($customFields.length) $customFields.toggleClass('lsd-util-hide', inherit);
        if (mode === 'custom') listdomUpdateCtaTarget($root, false);

        return mode;
    }

    function listdomUpdateCtaTarget($root, triggered)
    {
        if (!$root || !$root.length) return;

        if (typeof triggered === 'undefined') triggered = false;

        const $target = $root.find('[data-lsd-cta-target]');
        if (!$target.length) return;

        const value = $target.val();
        const $fields = $root.find('[data-lsd-cta-target-field]').addClass('lsd-util-hide');

        if (value === 'custom')
        {
            $fields.filter('[data-lsd-cta-target-field="custom"]').removeClass('lsd-util-hide');
        }
        else if (value === 'popup')
        {
            const $popupField = $fields.filter('[data-lsd-cta-target-field="popup"]').removeClass('lsd-util-hide');
            if (triggered)
            {
                const $button = $popupField.find('[data-lsd-cta-open-modal]');
                if ($button.length) $button.trigger('click');
            }
        }
    }

    function listdomGetCtaStorage(editorId)
    {
        if (!editorId) return $();

        return $('[data-lsd-cta-storage="' + editorId + '"]');
    }

    function listdomGetCtaStorageValue(editorId)
    {
        const $storage = listdomGetCtaStorage(editorId);
        if (!$storage.length) return null;

        return $storage.val();
    }

    function listdomGetCtaEditorValue(editorId)
    {
        if (!editorId) return '';

        let value = '';

        if (typeof tinymce !== 'undefined')
        {
            const editor = tinymce.get(editorId);
            if (editor)
            {
                value = editor.getContent();
                if (!value) value = editor.getContent({format: 'raw'});
            }
        }

        if (!value)
        {
            const $textarea = $('#' + editorId);
            if ($textarea.length) value = $textarea.val();
        }

        return value;
    }

    function listdomSetCtaEditorValue(editorId, value)
    {
        if (!editorId) return;

        if (typeof tinymce !== 'undefined')
        {
            const editor = tinymce.get(editorId);
            if (editor) editor.setContent(value || '');
        }

        const $textarea = $('#' + editorId);
        if ($textarea.length) $textarea.val(value || '');
    }

    function listdomSyncCtaStorage(editorId)
    {
        if (!editorId) return;

        const $storage = listdomGetCtaStorage(editorId);
        if (!$storage.length) return;

        $storage.val(listdomGetCtaEditorValue(editorId));
    }

    function listdomInitCtaStorage(editorId)
    {
        if (!editorId) return;

        const $storage = listdomGetCtaStorage(editorId);
        if (!$storage.length || $storage.data('lsdCtaStorageBound')) return;

        $storage.data('lsdCtaStorageBound', true);

        listdomSetCtaEditorValue(editorId, $storage.val());

        const bindEditorEvents = function (editor)
        {
            if (!editor) return;

            editor.on('change', function ()
            {
                listdomSyncCtaStorage(editorId);
            });

            editor.on('keyup', function ()
            {
                listdomSyncCtaStorage(editorId);
            });
        };

        if (typeof tinymce !== 'undefined')
        {
            const editor = tinymce.get(editorId);
            if (editor) bindEditorEvents(editor);

            $(document).on('tinymce-editor-init.lsdCtaStorage', function (event, editor)
            {
                if (editor.id === editorId) bindEditorEvents(editor);
            });
        }

        const $textarea = $('#' + editorId);
        if ($textarea.length)
        {
            $textarea.on('input.lsdCtaStorage change.lsdCtaStorage', function ()
            {
                listdomSyncCtaStorage(editorId);
            });
        }
    }

    function listdomSyncAllCtaStorages()
    {
        $('[data-lsd-cta-storage]').each(function ()
        {
            const editorId = $(this).data('lsd-cta-storage');
            if (!editorId) return;

            listdomSyncCtaStorage(editorId);
        });
    }

    function listdomEnsureModalEditors($modal)
    {
        if (!$modal || !$modal.length) return;
        if (typeof wp === 'undefined' || !wp.editor) return;

        const preInit = window.tinyMCEPreInit || {};

        $modal.find('.wp-editor-wrap').each(function () {
            const $wrap = $(this);
            const $textarea = $wrap.find('textarea.wp-editor-area');
            const editorId = $textarea.attr('id');
            if (!editorId) return;

            if (typeof tinymce !== 'undefined' && tinymce.get(editorId)) tinymce.get(editorId).remove();

            if (typeof QTags !== 'undefined' && QTags.instances && QTags.instances[editorId]) delete QTags.instances[editorId];

            const $wrapParent = $wrap.parent();
            const storageValue = listdomGetCtaStorageValue(editorId);
            const content = storageValue !== null ? storageValue : $textarea.val();
            $wrap.remove();

            const $newTextarea = $('<textarea>', {
                id: editorId,
                class: 'wp-editor-area',
                text: content,
            });
            $wrapParent.append($newTextarea);

            const settings = {};
            settings.tinymce = $.extend(true, {}, preInit.mceInit[editorId] || preInit.mceInit['content'] || {});
            settings.quicktags = $.extend(true, {}, preInit.qtInit[editorId] || preInit.qtInit['content'] || {});

            const initEditor = function () {
                wp.editor.initialize(editorId, settings);
                setTimeout(function () {
                    const editor = tinymce.get(editorId);
                    if (editor) {
                        editor.show();
                        editor.focus();
                        window.wpActiveEditor = editorId;
                    }

                    listdomInitCtaStorage(editorId);
                    listdomSyncCtaStorage(editorId);
                }, 200);
            };

            if ($modal.is(':visible')) initEditor();
            else $modal.one('shown.bs.modal', initEditor);
        });
    }

    function listdomInitCta($root)
    {
        if (!$root || !$root.length) return;

        const $enableToggle = $root.find('[data-lsd-cta-enable-toggle] input[type="checkbox"]');
        if ($enableToggle.length)
        {
            listdomUpdateCtaEnable($enableToggle, false);
        }
        else
        {
            const $modeToggle = $root.find('[data-lsd-cta-mode-toggle] input[type="checkbox"]');
            if ($modeToggle.length) listdomUpdateCtaMode($modeToggle, false);
        }

        listdomUpdateCtaTarget($root, false);
    }

    $(document).on('change', '[data-lsd-cta-enable-toggle] input[type="checkbox"]', function ()
    {
        listdomUpdateCtaEnable($(this), true);
    });

    $(document).on('change', '[data-lsd-cta-mode-toggle] input[type="checkbox"]', function ()
    {
        listdomUpdateCtaMode($(this), true);
    });

    $(document).on('change', '[data-lsd-cta-target]', function ()
    {
        const $root = $(this).closest('[data-lsd-cta-root]');
        listdomUpdateCtaTarget($root, true);
    });

    $('[data-lsd-cta-root]').each(function ()
    {
        listdomInitCta($(this));
    });

    $(document).on('click', '[data-lsd-cta-open-modal]', function (e)
    {
        e.preventDefault();
        const target = $(this).data('lsd-cta-open-modal');
        if (!target || typeof ListdomModal === 'undefined') return;

        const $modal = ListdomModal.open('#' + target, {
            appendToBody: true,
            hideClass: 'lsd-util-hide',
        });
        if (!$modal.length) return;

        $modal.one('listdom:modal:opened', function ()
        {
            listdomEnsureModalEditors($(this));
        });
    });

    $(document).on('listdom:modal:closed', '.lsd-cta-modal', function ()
    {
        listdomSyncAllCtaStorages();
    });

    $(document).on('submit', 'form', function ()
    {
        listdomSyncAllCtaStorages();
    });

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
     * Listdom Flatpickr
     */
    if (typeof window.lsdInitFlatpickr === 'function') window.lsdInitFlatpickr(document);

    $(document).off('click.lsdBookableFlatpickr', '.lsd-add-bookable-button').on('click.lsdBookableFlatpickr', '.lsd-add-bookable-button', function ()
    {
        const target = $(this).data('for');
        if (!target) return;

        setTimeout(function ()
        {
            const $target = $(target);
            if ($target.length && typeof window.lsdInitFlatpickr === 'function') window.lsdInitFlatpickr($target);
        }, 0);
    });

    /**
     * Sortable System
     */
    const SortableStyleCopier =
    {
        blacklist: new Set([
            'position','top','right','bottom','left','inset',
            'transform','transform-origin','translate','rotate','scale',
            'z-index',
            'transition','transition-property','transition-duration','transition-delay','transition-timing-function',
            'animation','animation-name','animation-duration','animation-delay','animation-timing-function',
            'width','height','min-width','min-height','max-width','max-height',
            'pointer-events','cursor',
            'visibility','will-change',
        ]),

        copy(fromEl, toEl) {
            const cs = getComputedStyle(fromEl);

            for (let i = 0; i < cs.length; i++) {
                const prop = cs[i];
                if (this.blacklist.has(prop)) continue;

                toEl.style.setProperty(
                    prop,
                    cs.getPropertyValue(prop),
                    cs.getPropertyPriority(prop)
                );
            }
        },

        copyDeep($from, $to) {
            const fromNodes = $from.find('*').addBack().toArray();
            const toNodes   = $to.find('*').addBack().toArray();

            const len = Math.min(fromNodes.length, toNodes.length);
            for (let i = 0; i < len; i++) {
                this.copy(fromNodes[i], toNodes[i]);
            }
        }
    };

    $('.lsd-sortable').sortable({
        tolerance: 'pointer',
        appendTo: 'body',

        helper: function (_, $item) {
            const $helper = $item.clone(false);

            SortableStyleCopier.copyDeep($item, $helper);

            $helper.css({
                width: $item.outerWidth(),
                height: $item.outerHeight(),
                boxSizing: 'border-box'
            });

            return $helper;
        }
    });

    $(document).on('click', '.lsd-display-options-table-device-tabs li', function (e)
    {
        e.preventDefault();

        const $tab = $(this);
        const device = $tab.data('device');
        if (!device) return;

        const $wrapper = $tab.closest('.lsd-display-options-table-devices');

        $wrapper.find('.lsd-display-options-table-device-tabs li').removeClass('lsd-sub-tabs-active');
        $tab.addClass('lsd-sub-tabs-active');

        $wrapper.find('.lsd-display-options-table-device').addClass('lsd-util-hide');
        $wrapper.find('.lsd-display-options-table-device[data-device="' + device + '"]').removeClass('lsd-util-hide');
    });

    // Listdom Switcher
    $('.lsd-switch input[type=checkbox]').on('change', function ()
    {
        const $toggle = $(this).parent().find($('.lsd-toggle'));
        if ($toggle.data('triggered')) return;

        $toggle.trigger('click');
    });

    function lsdSyncSearchStickyOffsetVisibility()
    {
        const $position = $('#lsd_search_position');
        const $sticky = $('#lsd_search_sticky');
        const $stickyRow = $('.lsd-search-sticky-row');
        const $offsetRow = $('.lsd-search-sticky-offset-row');

        if (!$position.length || !$sticky.length || !$stickyRow.length || !$offsetRow.length) return;

        const position = $position.val();
        const isSidePosition = position === 'left' || position === 'right';
        const isStickyEnabled = $sticky.is(':checked');

        $stickyRow.toggleClass('lsd-util-hide', !isSidePosition);

        if (isSidePosition && isStickyEnabled) $offsetRow.removeClass('lsd-util-hide').show();
        else $offsetRow.addClass('lsd-util-hide').hide();
    }

    $(document).on('change', '#lsd_search_position, #lsd_search_sticky', lsdSyncSearchStickyOffsetVisibility);
    lsdSyncSearchStickyOffsetVisibility();

    /**
     * Attributes Menu
     */
    // Categories field
    $('#lsd_all_categories').on('change', function ()
    {
        if ($(this).is(':checked')) $('#lsd_categories_wp').addClass('lsd-util-hide');
        else $('#lsd_categories_wp').removeClass('lsd-util-hide');
    });

    $('#lsd_all_packages').on('change', function ()
    {
        if ($(this).is(':checked')) $('#lsd_packages_wp').addClass('lsd-util-hide');
        else $('#lsd_packages_wp').removeClass('lsd-util-hide');
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
    const $shortcodeSkinSelector = $('#lsd_display_options_skin');
    const $shortcodeForm = $('#post');
    const $builderStyleMessage = $('#lsd_display_options_builder_message_elements');

    function lsdToggleSkinFormFields(activeSkin)
    {
        if (!$shortcodeSkinSelector.length || !activeSkin) return;

        $('.lsd-skin-content, .lsd-skin-display-options').each(function ()
        {
            const $container = $(this);
            const containerSkin = $container.data('skin') || ($container.attr('id') || '').replace(/^lsd_skin_display_options_(?:map_|layout_)?/, '');

            if (!containerSkin) return;

            const enabled = containerSkin === activeSkin;
            $container.find('input, select, textarea').prop('disabled', !enabled);
        });
    }

    function lsdIsCustomBuilderStyle(style)
    {
        return !isNaN(parseFloat(style)) && isFinite(style);
    }

    function lsdToggleBuilderStyleState(skin)
    {
        if (!$shortcodeSkinSelector.length || !skin)
        {
            $builderStyleMessage.addClass('lsd-util-hide');
            return;
        }

        const $styleSelector = $('#lsd_display_options_skin_' + skin + '_style');
        const $elementsContainer = $('#lsd_skin_display_options_' + skin);

        if (!$styleSelector.length || skin === 'singlemap')
        {
            $builderStyleMessage.addClass('lsd-util-hide');
            return;
        }

        const isCustomStyle = lsdIsCustomBuilderStyle($styleSelector.val());

        $builderStyleMessage.toggleClass('lsd-util-hide', !isCustomStyle);

        $elementsContainer.find('.lsd-form-row-style-needed').each(function ()
        {
            const $section = $(this);
            const $wrapper = $section.parent('.lsd-settings-fields-wrapper');

            $section.toggleClass('lsd-util-hide', isCustomStyle);
            if ($wrapper.length) $wrapper.toggleClass('lsd-util-hide', isCustomStyle);
        });
    }

    function lsdUpdateListingLinkOptions(skin)
    {
        if (!skin) return;

        const $listingLink = $('#lsd_display_options_skin_' + skin + '_listing_link');
        if (!$listingLink.length) return;

        const mapOptionLabel = $listingLink.data('lsdMapOptionLabel') || 'Show on the map';
        const $mapProvider = $('#lsd_display_options_skin_' + skin + '_map_provider');
        const unsupportedSkins = ['side', 'table', 'masonry', 'carousel', 'cover', 'slider'];
        const mapSupported = unsupportedSkins.indexOf(skin) === -1 && $mapProvider.length && $mapProvider.val() && $mapProvider.val() !== '0';
        const hasMapOption = $listingLink.find('option[value="map"]').length > 0;

        if (mapSupported && !hasMapOption)
        {
            const $insertBefore = $listingLink.find('option[value="lightbox"]').first();
            const $option = $('<option></option>').val('map').text(mapOptionLabel);

            if ($insertBefore.length) $option.insertBefore($insertBefore);
            else $listingLink.append($option);
        }
        else if (!mapSupported && hasMapOption)
        {
            if ($listingLink.val() === 'map') $listingLink.val('normal');
            $listingLink.find('option[value="map"]').remove();
        }

        $listingLink.trigger('change');
    }

    // Skin Changer
    $shortcodeSkinSelector.on('change', function ()
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

        lsdUpdateListingLinkOptions(skin);

        // Toggle Style Change
        $('.lsd-display-options-style-selector').trigger('change');

        lsdToggleBuilderStyleState(skin);

        lsdToggleSkinFormFields(skin);
    }).trigger('change');

    if ($shortcodeForm.length && $shortcodeSkinSelector.length)
    {
        $shortcodeForm.on('submit', function ()
        {
            lsdToggleSkinFormFields($shortcodeSkinSelector.val());
        });
    }

    $(document).on('click', '.lsd-copy', function ()
    {
        const $button = $(this);
        const target = $button.data('target');
        const $target = $('#' + target);
        const $targetEl = $target.length
            ? $target
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
            const scrollX = window.scrollX;
            const scrollY = window.scrollY;

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

        lsdUpdateListingLinkOptions(skin);
    }).trigger('change');

    // Style Changer
    $('.lsd-display-options-style-selector').on('change', function ()
    {
        lsdToggleBuilderStyleState($shortcodeSkinSelector.val());
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
        items: '> .lsd-dashboard-menu-item',
        cancel: '.lsd-dashboard-menu-content',
        tolerance: 'pointer',
        helper: 'clone',
        appendTo: 'body',
        start: function (e, ui) {
            ui.helper
            .addClass('lsd-sort-helper')
            .css({
                height: ui.item.outerHeight(),
                zIndex: 999999
            });
        }
    });

    jQuery(function ($) {

        $('body').on('click', '.lsd-custom-menu-btn', function ()
        {
            const defaultIcon = 'fas fa-tachometer-alt';
            const $newMenuItem = $('<li class="lsd-dashboard-menu-item lsd-custom-menu-list">' +
                '<p class="lsd-flex lsd-flex-content-between">' +
                '<span>' +
                '<i class="lsd-icon lsd-custom-menu-icon ' + defaultIcon + '"></i>' +
                '<span class="lsd-custom-menu-label">' + lsd.i18n_field_label + '</span>' +
                '</span>' +
                '<span class="lsd-menu-actions">' +
                '<i class="fas fa-trash"></i> ' +
                '<button type="button" class="lsd-dashboard-menu-toggle" aria-pressed="true"><i class="fas fa-check-circle"></i></button> ' +
                '<i class="fas fa-chevron-up lsd-dashboard-menu-chevron"></i>' +
                '</span>' +
                '</p>' +
                '<div class="lsd-custom-menu-content lsd-dashboard-menu-content"></div>' +
                '</li>');

            $('.lsd-settings-dashboard-menus').append($newMenuItem);

            const $labelGroup = $newMenuItem.find('.lsd-custom-menu-content');
            const $inputHiddenMenu = $('<input>', {
                type: 'hidden',
                name: 'lsd[dashboard_menus][]',
                class: 'custom-menu-slug'
            });
            const $inputMenuStatus = $('<input>', {
                type: 'hidden',
                name: '',
                class: 'lsd-dashboard-menu-status',
                'data-field': 'enabled',
                value: '1',
            });

            const fields = ['Label', 'Slug', 'Icon', 'Login Status', 'Content'];

            const placeholders = {
                Label: lsd.i18n_placeholder_label,
                Slug: lsd.i18n_placeholder_slug,
                Icon: lsd.i18n_placeholder_icon,
                'Login Status': '',
                Content: ''
            };

            const descriptions = {
                Label: lsd.i18n_description_label,
                Slug: lsd.i18n_description_slug,
                Icon: lsd.i18n_description_icon,
                'Login Status': 'Choose whether this custom menu requires the user to be logged in.',
                Content: lsd.i18n_description_content
            };

            $labelGroup.append($inputMenuStatus);

            fields.forEach((field) =>
            {
                let uniqueName = field.toLowerCase().replace(/\s+/g, '_');
                if (field === 'Login Status') {
                    uniqueName = 'login_status';
                }

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

                    $inputEl.val(defaultIcon);

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
                    const uniqueId = 'textarea_' + Date.now() + '_' + Math.floor(Math.random() * 100000);
                    $inputEl = $('<textarea>', {
                        name: '',
                        id: uniqueId,
                        'data-field': 'content'
                    });

                    setTimeout(() =>
                    {
                        if (typeof wp !== 'undefined' && wp.editor && wp.editor.initialize) {
                            wp.editor.initialize(uniqueId, {
                                tinymce: {
                                    toolbar1: 'formatselect bold italic underline bullist numlist blockquote alignleft aligncenter alignright link unlink wp_more fullscreen',
                                    plugins: 'lists paste link'
                                },
                                quicktags: true,
                                mediaButtons: true
                            });
                        }
                    }, 10);
                }
                else if (field === 'Login Status')
                {
                    // Select: required / optional, default = required
                    $inputEl = $('<select>', {
                        name: '',
                        'data-field': uniqueName,
                        class: 'lsd-admin-input'
                    });

                    $inputEl.append($('<option>', {
                        value: 'required',
                        text: 'Required (only logged-in users)'
                    }));

                    $inputEl.append($('<option>', {
                        value: 'optional',
                        text: 'Optional (visible to all users)'
                    }));
                }
                else
                {
                    // Label / Slug
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
                $fieldGroup.append(
                    $('<p></p>')
                    .text(descriptions[field] || '')
                    .addClass('lsd-admin-description-tiny lsd-mb-0 lsd-mt-2')
                );

                $labelGroup.append($fieldGroup);
            });

            $newMenuItem.append($inputHiddenMenu);
        });

    });

    $(document).on('input', '.lsd-custom-menu-list input[data-field="slug"]', function ()
    {
        const $slugInput = $(this);
        const $thisList = $slugInput.closest('.lsd-custom-menu-list');
        const $inputHiddenMenu = $thisList.find('.custom-menu-slug');
        const $thisListContent = $slugInput.closest('.lsd-custom-menu-content');

        const $inputField  = $thisListContent.find('input[data-field]');
        const $iconField   = $thisListContent.find('select.lsd-iconpicker');
        const $selectField = $thisListContent.find('select[data-field]').not('.lsd-iconpicker');
        const $textField   = $thisListContent.find('textarea');

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

        // For login_status select (and other selects with data-field)
        $selectField.each(function ()
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

    $(document).on('input', 'input[name^="lsd[dashboard_menu_builtin]"][name$="[label]"]', function ()
    {
        const $input = $(this);
        const $item = $input.closest('.lsd-dashboard-menu-item');
        const $label = $item.find('p > span > span').first();

        $label.text($input.val());
    });

    $(document).on('change', '.lsd-custom-menu-list select.lsd-iconpicker', function ()
    {
        const $select = $(this);
        const $thisList = $select.closest('.lsd-custom-menu-list');
        const $icon = $thisList.find('.lsd-custom-menu-icon');
        const iconClass = $select.val() || '';

        $icon.attr('class', 'lsd-icon lsd-custom-menu-icon ' + iconClass);
    });

    $(document).on('change', 'select.lsd-iconpicker[name^="lsd[dashboard_menu_builtin]"][name$="[icon]"]', function ()
    {
        const $select = $(this);
        const $item = $select.closest('.lsd-dashboard-menu-item');
        const $icon = $item.find('p > span > i.lsd-icon').first();
        const iconClass = $select.val() || '';

        $icon.attr('class', 'lsd-icon ' + iconClass);
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

    $(document).on('click', '.lsd-dashboard-menu-toggle', function (event)
    {
        event.preventDefault();

        const $toggle = $(this);
        const $item = $toggle.closest('.lsd-dashboard-menu-item');
        const $status = $item.find('.lsd-dashboard-menu-status').first();

        if (!$status.length) return;

        const enabled = parseInt($status.val(), 10) === 1;
        if (enabled)
        {
            $status.val(0);
            $toggle.find('i').removeClass('fa-check-circle').addClass('fa-minus-circle');
            $toggle.attr('aria-pressed', 'false');
            $item.addClass('lsd-dashboard-menu-item-disabled');
        }
        else
        {
            $status.val(1);
            $toggle.find('i').removeClass('fa-minus-circle').addClass('fa-check-circle');
            $toggle.attr('aria-pressed', 'true');
            $item.removeClass('lsd-dashboard-menu-item-disabled');
        }
    });

    $(document).on('click', '.lsd-custom-menu-list .fa-trash', function ()
    {
        $(this).closest('.lsd-custom-menu-list').remove();
    });

    $(document).on('click', '.lsd-dashboard-menu-item .lsd-dashboard-menu-chevron', function ()
    {
        const $chevron = $(this);
        const $thisListContent = $chevron.closest('.lsd-dashboard-menu-item').find('.lsd-dashboard-menu-content');
        if ($thisListContent.hasClass('lsd-util-hide'))
        {
            $thisListContent.removeClass('lsd-util-hide').show();
            $chevron.removeClass('fa-chevron-down').addClass('fa-chevron-up');
        }
        else
        {
            $thisListContent.addClass('lsd-util-hide').hide();
            $chevron.removeClass('fa-chevron-up').addClass('fa-chevron-down');
        }
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

        // Validate required file fields
        $('.lsd-attribute-file[data-required="1"]:visible').each(function ()
        {
            const $this = $(this);
            const $input = $(this).find('input[type=hidden]');
            const value = $input.val();
            const requiredMessage = $this.data('required-message') || 'Please select a file.';
            const $placeholder = $('#' + $input.attr('id') + '_img');

            if (!value)
            {
                isValid = false;
                $placeholder.addClass('lsd-checkbox-error');

                if ($placeholder.next('.lsd-file-error-msg').length === 0)
                {
                    $placeholder.after('<div class="lsd-file-error-msg lsd-alert lsd-error">' + requiredMessage + '</div>');
                }
            }
            else
            {
                $placeholder.removeClass('lsd-checkbox-error');
                $placeholder.next('.lsd-file-error-msg').remove();
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

    const parseSavedState = (value) =>
    {
        if (typeof value === 'boolean') return value;
        if (typeof value === 'number') return value === 1;

        if (typeof value === 'string')
        {
            const normalized = value.trim().toLowerCase();
            return normalized === '1' || normalized === 'true';
        }

        return false;
    };

    function initToggleAwareNav($nav)
    {
        const controlSelector = $nav.data('lsdToggleControl');
        if (!controlSelector) return;

        const $control = $(controlSelector);
        if (!$control.length) return;

        const disableSelector = $nav.data('lsdToggleDisableTargets') || null;
        const tooltipMessageRaw = $nav.data('disabledTooltip') || $nav.data('lsdToggleTooltip');
        const tooltipMessage = typeof tooltipMessageRaw === 'string' ? tooltipMessageRaw : '';

        const $targets = disableSelector ? $nav.find(disableSelector) : $();

        $targets.each(function ()
        {
            const $target = $(this);

            $target.data('lsdToggleOriginalTooltip', $target.attr('data-lsd-tooltip'));
            $target.data('lsdToggleHadTooltipAttr', typeof $target.attr('data-lsd-tooltip') !== 'undefined');
            $target.data('lsdToggleOriginalAriaDisabled', $target.attr('aria-disabled'));
            $target.data('lsdToggleHadAriaAttr', typeof $target.attr('aria-disabled') !== 'undefined');
            $target.data('lsdToggleOriginalTabindex', $target.attr('tabindex'));
            $target.data('lsdToggleHadTabindexAttr', typeof $target.attr('tabindex') !== 'undefined');
            $target.data('lsdToggleHadTooltipClass', $target.hasClass('lsd-tooltip'));
        });

        const toggleTargetsDisabledState = (disabled) =>
        {
            if (!$targets.length) return;

            $targets.each(function ()
            {
                const $target = $(this);

                if (disabled)
                {
                    $target
                        .addClass('lsd-nav-tab-disabled')
                        .attr('aria-disabled', 'true')
                        .attr('tabindex', '-1');

                    if (tooltipMessage)
                    {
                        $target.attr('data-lsd-tooltip', tooltipMessage);

                        if (!$target.data('lsdToggleHadTooltipClass'))
                        {
                            $target.addClass('lsd-tooltip').data('lsdToggleAddedTooltipClass', true);
                        }
                    }
                }
                else
                {
                    $target.removeClass('lsd-nav-tab-disabled');

                    if ($target.data('lsdToggleHadAriaAttr')) $target.attr('aria-disabled', $target.data('lsdToggleOriginalAriaDisabled'));
                    else $target.removeAttr('aria-disabled');

                    if ($target.data('lsdToggleHadTabindexAttr')) $target.attr('tabindex', $target.data('lsdToggleOriginalTabindex'));
                    else $target.removeAttr('tabindex');

                    if (tooltipMessage)
                    {
                        if ($target.data('lsdToggleHadTooltipAttr')) $target.attr('data-lsd-tooltip', $target.data('lsdToggleOriginalTooltip'));
                        else $target.removeAttr('data-lsd-tooltip');
                    }

                    if ($target.data('lsdToggleAddedTooltipClass'))
                    {
                        $target.removeClass('lsd-tooltip');
                        $target.removeData('lsdToggleAddedTooltipClass');
                    }
                }
            });
        };

        const readSavedState = () => parseSavedState($nav.data('lsdToggleSaved'));

        const updateNavState = () =>
        {
            const savedActive = readSavedState();
            const toggleChecked = $control.is(':checkbox, :radio') ? $control.is(':checked') : Boolean($control.val());
            const shouldShowNav = toggleChecked || savedActive;
            const shouldDisableNav = !savedActive && toggleChecked;

            $nav.toggleClass('lsd-util-hide', !shouldShowNav);
            toggleTargetsDisabledState(shouldDisableNav);
        };

        updateNavState();

        $control.on('change', updateNavState);
    }

    $('.lsd-nav-sub-tabs[data-lsd-toggle-control]').each(function ()
    {
        initToggleAwareNav($(this));
    });

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
    $('.lsd-nav-sub-tabs').on('click', '.lsd-nav-tab', function (event)
    {
        const $tab = $(this);

        if ($tab.hasClass('lsd-nav-tab-disabled'))
        {
            event.preventDefault();
            return;
        }

        activateChildTab($tab);
    });

    // Trigger active subtab only within the active parent tab
    $('.lsd-nav-tab-wrapper > li > a.lsd-nav-tab-active')
        .closest('li')
        .find('.lsd-nav-sub-tabs .lsd-nav-tab.lsd-nav-tab-active')
        .trigger('click');
});

jQuery(function ($)
{
    const $wrapper = $('#lsd_plan_tiers');
    if (!$wrapper.length) return;

    const $btnAdd = $('#lsd_add_tier');

    function ensureDefault()
    {
        let $default = $wrapper.find('.lsd-plan-tier-default-input[value="1"]').closest('.lsd-plan-tier');

        if (!$default.length) $default = $wrapper.children('.lsd-plan-tier').first();

        $wrapper.children('.lsd-plan-tier').each(function ()
        {
            const isDefault = $(this).is($default);
            $(this).find('.lsd-plan-tier-star').toggleClass('fas', isDefault).toggleClass('far', !isDefault);
            $(this).find('.lsd-plan-tier-default-input').val(isDefault ? '1' : '0');
        });
    }

    function renumber()
    {
        $wrapper.children('.lsd-plan-tier').each(function (i)
        {
            $(this).attr('data-index', i);
            $(this).find('[name^="lsd_tiers"]').each(function ()
            {
                $(this).attr('name', $(this).attr('name').replace(/lsd_tiers\[[0-9]+]/, 'lsd_tiers[' + i + ']'));
            });
        });

        ensureDefault();
    }

    function refreshSortable()
    {
        $wrapper.sortable({
            handle: '.lsd-plan-tier-sort',
            stop: renumber
        });
    }

    $wrapper.on('click', '.lsd-plan-tier-remove', function ()
    {
        $(this).closest('.lsd-plan-tier').remove();
        renumber();
    });

    $wrapper.on('click', '.lsd-plan-tier-star', function ()
    {
        const $tier = $(this).closest('.lsd-plan-tier');
        $wrapper.find('.lsd-plan-tier-default-input').val('0');
        $tier.find('.lsd-plan-tier-default-input').val('1');
        ensureDefault();
    });

    $wrapper.on('change', '.lsd-plan-tier-type', function ()
    {
        const $tier = $(this).closest('.lsd-plan-tier');
        const $expiry = $tier.find('.lsd-plan-tier-expiry');
        const $expiryWrapper = $tier.find('.lsd-plan-tier-expiry-wrapper');
        const isRecurring = $(this).val() === 'recurring';

        $expiry.prop('required', isRecurring);

        if ($expiryWrapper.length)
        {
            if (isRecurring) $expiryWrapper.removeClass('lsd-util-hide');
            else $expiryWrapper.addClass('lsd-util-hide');
        }
    }).trigger('change');

    $wrapper.on('input', 'input[name$="[name]"]', function ()
    {
        const $tier = $(this).closest('.lsd-plan-tier');
        $tier.find('.lsd-plan-tier-title').text($(this).val());
    });

    $btnAdd.on('click', function (e)
    {
        e.preventDefault();
        const index = $wrapper.children('.lsd-plan-tier').length;

        $.post(lsd.ajaxurl, {
            action: 'lsd_plan_new_tier',
            index: index,
            _lsdnonce: $('input[name="_lsdnonce"]').val()
        }, function (res)
        {
            if (res.success)
            {
                const $tier = $(res.html);
                $wrapper.append($tier);

                refreshSortable();
                renumber();

                const $name = $tier.find('input[name$="[name]"]');
                $('html, body').animate({scrollTop: $tier.offset().top}, 300, () => $name.trigger('focus'));
            }
        }, 'json');
    });

    refreshSortable();
    renumber();

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

jQuery(function ($)
{
    const config = window.lsdGooglePlacesAdmin || {};
    if (!Object.keys(config).length) return;

    const escapeHtml = function (value)
    {
        return String(value === undefined || value === null ? '' : value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    };

    const notify = function (message, type, options)
    {
        if (typeof window.listdom_toastify === 'function')
        {
            window.listdom_toastify(message, type || 'lsd-info', options || {});
        }
    };

    const notifyCopy = function (message, type)
    {
        notify(message, type, {
            hideTime: 4800
        });
    };

    const setFeedback = function ($element, message, type)
    {
        if (!$element.length) return;

        $element.removeClass('lsd-alert lsd-info lsd-success lsd-warning lsd-error');

        if (!message)
        {
            $element.html('').hide();
            return;
        }

        $element
            .addClass('lsd-alert')
            .addClass(type || 'lsd-info')
            .html(message)
            .show();
    };

    const debounce = function (callback, wait)
    {
        let timer = null;

        return function ()
        {
            const args = arguments;
            const context = this;

            window.clearTimeout(timer);
            timer = window.setTimeout(function ()
            {
                callback.apply(context, args);
            }, wait);
        };
    };

    const initSeedingPage = function ()
    {
        const $page = $('#lsdaddgpl-page');
        if (!$page.length) return;

        const $map = $('#lsdaddgpl-map');
        const $query = $('#lsdaddgpl-query');
        const $type = $('#lsdaddgpl-type');
        const $refreshOnMove = $('#lsdaddgpl-refresh-on-move');
        const $queue = $('#lsdaddgpl-queue');
        const $queueEmpty = $('#lsdaddgpl-queue-empty');
        const $results = $('#lsdaddgpl-results');
        const $draftResults = $('#lsdaddgpl-draft-results');
        const $makeDrafts = $('#lsdaddgpl-make-drafts');
        const state = {
            map: null,
            markers: [],
            places: [],
            queue: [],
            request: null,
            highlightTimer: null,
            missingPlacesNoticeShown: false,
            hasSearched: false
        };

        const placeRowId = function (placeId)
        {
            return 'lsdaddgpl-place-' + String(placeId || '').replace(/[^a-zA-Z0-9_-]/g, '');
        };

        const getPlace = function (placeId)
        {
            return state.places.find(function (item)
            {
                return item.place_id === placeId;
            }) || null;
        };

        const renderQueue = function ()
        {
            if (!state.queue.length)
            {
                $queue.html('');
                $queueEmpty.show();
                return;
            }

            $queueEmpty.hide();

            let html = '';
            state.queue.forEach(function (item)
            {
                html += '<li class="lsdaddgpl-queue-item" data-place-id="' + escapeHtml(item.place_id) + '">';
                html += '<div class="lsdaddgpl-queue-main">';
                html += '<strong>' + escapeHtml(item.title) + '</strong>';
                if (item.address) html += '<span>' + escapeHtml(item.address) + '</span>';
                html += '<code>' + escapeHtml(item.place_id) + '</code>';
                html += '</div>';
                html += '<button type="button" class="button lsdaddgpl-queue-remove" data-place-id="' + escapeHtml(item.place_id) + '">' + escapeHtml(config.strings.remove || 'Remove') + '</button>';
                html += '</li>';
            });

            $queue.html(html);
        };

        const renderPlaces = function ()
        {
            if (!state.places.length)
            {
                $results.html('<div class="lsdaddgpl-empty-state">' + escapeHtml(config.strings.noResults || 'No places found.') + '</div>');
                return;
            }

            let html = '<div class="lsdaddgpl-business-list">';

            state.places.forEach(function (place)
            {
                const title = place.seed_title || place.name || config.strings.untitledPlace || 'Untitled Place';
                const inQueue = state.queue.some(function (item)
                {
                    return item.place_id === place.place_id;
                });
                const primaryType = place.primary_type_label || place.primary_type || '';

                html += '<div class="lsdaddgpl-business-row lsdaddgpl-business-row-no-media" id="' + escapeHtml(placeRowId(place.place_id)) + '" data-place-id="' + escapeHtml(place.place_id) + '">';

                html += '<div class="lsdaddgpl-business-content">';
                html += '<div class="lsdaddgpl-business-top-content">';
                html += '<div class="lsdaddgpl-business-top">';
                html += '<input type="text" class="lsdaddgpl-business-title" value="' + escapeHtml(title) + '" data-place-id="' + escapeHtml(place.place_id) + '">';
                html += '<button type="button" class="lsd-primary-button lsdaddgpl-business-add" data-place-id="' + escapeHtml(place.place_id) + '"' + (inQueue ? ' disabled' : '') + '>' + escapeHtml(config.strings.addToQueue || 'Add') + '</button>';
                html += '</div>';

                if (place.address)
                {
                    html += '<div class="lsdaddgpl-business-address">' + escapeHtml(place.address) + '</div>';
                }

                if (primaryType)
                {
                    html += '<div class="lsdaddgpl-business-address">' + escapeHtml(primaryType) + '</div>';
                }

                html += '</div>';
                html += '</div>';
                html += '</div>';
            });

            html += '</div>';
            $results.html(html);
        };

        const clearMarkers = function ()
        {
            state.markers.forEach(function (marker)
            {
                marker.setMap(null);
            });

            state.markers = [];
        };

        const highlightPlaceRow = function (placeId)
        {
            const $row = $('#' + placeRowId(placeId));
            if (!$row.length) return;

            $row.get(0).scrollIntoView({
                behavior: 'smooth',
                block: 'center'
            });

            $('.lsdaddgpl-business-row').removeClass('lsdaddgpl-business-row-highlight');
            $row.addClass('lsdaddgpl-business-row-highlight');

            if (state.highlightTimer) window.clearTimeout(state.highlightTimer);
            state.highlightTimer = window.setTimeout(function ()
            {
                $row.removeClass('lsdaddgpl-business-row-highlight');
            }, 2000);
        };

        const addPlaceToQueue = function (placeId)
        {
            const place = getPlace(placeId);
            if (!place) return;

            const exists = state.queue.some(function (item)
            {
                return item.place_id === place.place_id;
            });

            if (exists)
            {
                notify(config.strings.duplicateQueue || 'Duplicate place.', 'lsd-warning');
                return;
            }

            if (state.queue.length >= Number(config.maxQueueSize || 20))
            {
                notify(config.strings.queueFull || 'Queue is full.', 'lsd-warning');
                return;
            }

            state.queue.push({
                place_id: place.place_id,
                title: $.trim(place.seed_title || place.name || '') || config.strings.untitledPlace || 'Untitled Place',
                address: place.address || ''
            });

            renderQueue();
            renderPlaces();
            notify(config.strings.addedToQueue || 'Added to queue.', 'lsd-success');
        };

        const renderMarkers = function ()
        {
            clearMarkers();

            state.places.forEach(function (place)
            {
                if (typeof place.lat !== 'number' || typeof place.lng !== 'number') return;

                const marker = new google.maps.Marker({
                    position: {lat: place.lat, lng: place.lng},
                    map: state.map,
                    title: place.name || ''
                });

                marker.addListener('click', function ()
                {
                    highlightPlaceRow(place.place_id);
                });

                state.markers.push(marker);
            });
        };

        const performSearch = function ()
        {
            if (!state.map) return;
            state.hasSearched = true;

            if (!config.hasPlacesApiKey)
            {
                if (!state.missingPlacesNoticeShown)
                {
                    notify(config.strings.missingPlacesApiKey || 'Missing Google Places API key.', 'lsd-warning');
                    state.missingPlacesNoticeShown = true;
                }

                state.places = [];
                renderPlaces();
                clearMarkers();
                return;
            }

            const bounds = state.map.getBounds();
            if (!bounds) return;

            if (state.request && typeof state.request.abort === 'function') state.request.abort();

            const northEast = bounds.getNorthEast();
            const southWest = bounds.getSouthWest();
            const payload = {
                query: $.trim($query.val()),
                type: $type.val() || '',
                bounds: {
                    north: northEast.lat(),
                    east: northEast.lng(),
                    south: southWest.lat(),
                    west: southWest.lng()
                }
            };

            state.request = $.ajax({
                type: 'POST',
                url: config.ajaxUrl,
                dataType: 'json',
                data: {
                    action: 'lsdaddgpl_search',
                    _wpnonce: config.seedingNonce,
                    payload: JSON.stringify(payload)
                }
            }).done(function (response)
            {
                if (!response || Number(response.success) !== 1)
                {
                    notify(response && response.message ? response.message : config.strings.searchFailed || 'Unable to load places.', 'lsd-error');
                    state.places = [];
                    renderPlaces();
                    clearMarkers();
                    return;
                }

                state.places = $.isArray(response.results) ? response.results.map(function (place)
                {
                    place.seed_title = place.name || '';
                    return place;
                }) : [];

                renderPlaces();
                renderMarkers();
            }).fail(function (xhr, textStatus)
            {
                if (textStatus === 'abort') return;

                notify(config.strings.searchFailed || 'Unable to load places.', 'lsd-error');
                state.places = [];
                renderPlaces();
                clearMarkers();
            });
        };

        const makeDrafts = function ()
        {
            if (!state.queue.length)
            {
                notify(config.strings.emptyQueue || 'Queue is empty.', 'lsd-warning');
                return;
            }

            $makeDrafts.prop('disabled', true);

            $.ajax({
                type: 'POST',
                url: config.ajaxUrl,
                dataType: 'json',
                data: {
                    action: 'lsdaddgpl_make_drafts',
                    _wpnonce: config.seedingNonce,
                    queue: JSON.stringify(state.queue)
                }
            }).done(function (response)
            {
                if (!response || Number(response.success) !== 1)
                {
                    notify(response && response.message ? response.message : config.strings.draftCreationFailed || 'Unable to create drafts.', 'lsd-error');
                    return;
                }

                let html = '';

                if ($.isArray(response.created) && response.created.length)
                {
                    html += '<div class="lsdaddgpl-result-group"><h4>' + escapeHtml(response.message || '') + '</h4><ul>';
                    response.created.forEach(function (item)
                    {
                        html += '<li><a href="' + escapeHtml(item.edit_url || '#') + '">' + escapeHtml(item.title || item.place_id) + '</a></li>';
                    });
                    html += '</ul></div>';
                }

                if ($.isArray(response.skipped) && response.skipped.length)
                {
                    html += '<div class="lsdaddgpl-result-group"><h4>' + escapeHtml(config.strings.skipped || 'Skipped') + '</h4><ul>';
                    response.skipped.forEach(function (item)
                    {
                        const title = item.edit_url
                            ? '<a href="' + escapeHtml(item.edit_url) + '">' + escapeHtml(item.title || item.place_id || '') + '</a>'
                            : escapeHtml(item.title || item.place_id || '');

                        html += '<li>' + title + (item.reason ? ' <span>' + escapeHtml(item.reason) + '</span>' : '') + '</li>';
                    });
                    html += '</ul></div>';
                }

                $draftResults.html(html);
                notify(response.message || '', 'lsd-success');
                state.queue = [];
                renderQueue();
                renderPlaces();
            }).fail(function ()
            {
                notify(config.strings.draftCreationFailed || 'Unable to create drafts.', 'lsd-error');
            }).always(function ()
            {
                $makeDrafts.prop('disabled', false);
            });
        };

        const initMap = function ()
        {
            const center = {
                lat: parseFloat($map.data('lat')) || parseFloat(config.defaultCenter && config.defaultCenter.lat) || 0,
                lng: parseFloat($map.data('lng')) || parseFloat(config.defaultCenter && config.defaultCenter.lng) || 0
            };
            const zoom = parseInt($map.data('zoom'), 10) || parseInt(config.defaultCenter && config.defaultCenter.zoom, 10) || 12;

            state.map = new google.maps.Map($map.get(0), {
                center: center,
                zoom: zoom,
                mapTypeId: google.maps.MapTypeId.ROADMAP,
                streetViewControl: false,
                fullscreenControl: true
            });

            state.map.addListener('idle', debounce(function ()
            {
                if (!state.hasSearched || !$refreshOnMove.is(':checked')) return;
                performSearch();
            }, 500));
        };

        $query.on('keydown', function (event)
        {
            if (event.key !== 'Enter') return;

            event.preventDefault();
            performSearch();
        });

        $('#lsdaddgpl-search').on('click', function ()
        {
            performSearch();
        });

        $results.on('input', '.lsdaddgpl-business-title', function ()
        {
            const placeId = String($(this).data('placeId') || '');
            const place = getPlace(placeId);
            if (!place) return;

            place.seed_title = $(this).val();
        });

        $results.on('click', '.lsdaddgpl-business-add', function ()
        {
            addPlaceToQueue(String($(this).data('placeId') || ''));
        });

        $queue.on('click', '.lsdaddgpl-queue-remove', function ()
        {
            const placeId = String($(this).data('placeId') || '');
            state.queue = state.queue.filter(function (item)
            {
                return item.place_id !== placeId;
            });

            renderQueue();
            renderPlaces();
        });

        $makeDrafts.on('click', function ()
        {
            makeDrafts();
        });

        renderQueue();
        renderPlaces();

        if (!config.hasMapApiKey)
        {
            notify(config.strings.missingMapApiKey || 'Missing Google Maps API key.', 'lsd-warning');
            return;
        }

        if (typeof window.listdom_add_googlemaps_callbacks === 'function')
        {
            window.listdom_add_googlemaps_callbacks(function ()
            {
                initMap();
            });
            return;
        }

        if (window.google && window.google.maps)
        {
            initMap();
            return;
        }

        let attempts = 0;
        const timer = window.setInterval(function ()
        {
            attempts += 1;

            if (typeof window.listdom_add_googlemaps_callbacks === 'function')
            {
                window.clearInterval(timer);
                window.listdom_add_googlemaps_callbacks(function ()
                {
                    initMap();
                });
                return;
            }

            if (window.google && window.google.maps)
            {
                window.clearInterval(timer);
                initMap();
                return;
            }

            if (attempts >= 40)
            {
                window.clearInterval(timer);
                notify(config.strings.mapLoadError || 'Unable to load Google Maps.', 'lsd-error');
            }
        }, 250);
    };

    const initSuggestionsMetabox = function ()
    {
        const $metabox = $('#lsdaddgpl-metabox');
        if (!$metabox.length) return;

        const $feedback = $('#lsdaddgpl-metabox-feedback');
        const $entry = $('#lsdaddgpl-place-id-entry');
        const $searchInput = $('#lsdaddgpl-place-search-input');
        const $searchClear = $('#lsdaddgpl-place-search-clear');
        const $searchResults = $('#lsdaddgpl-place-search-results');
        const $display = $('#lsdaddgpl-place-id-display');
        const $displayCurrent = $('#lsdaddgpl-place-id-current');
        const $fetchButton = $('#lsdaddgpl-place-id-fetch');
        const $changeButton = $('#lsdaddgpl-place-id-change');
        const $suggestions = $('#lsdaddgpl-suggestions');
        const postId = parseInt($metabox.data('postId'), 10) || parseInt(config.postId, 10) || 0;
        const fieldMap = config.fieldMap || {};
        const titleField = fieldMap.title || {};
        const addressFieldMap = fieldMap.address || {};
        const phoneField = fieldMap.phone || {};
        const websiteField = fieldMap.website || {};
        const openingHoursField = fieldMap.opening_hours || {};
        const primaryTypeField = fieldMap.primary_type || {};
        const categoriesField = fieldMap.categories || {};
        const coordinatesField = fieldMap.coordinates || {};
        let placeId = String($metabox.data('placeId') || '');
        let searchRequest = null;

        if (!postId || !$suggestions.length || !$searchResults.length) return;

        const formatPriceLevel = function (value)
        {
            const map = {
                PRICE_LEVEL_INEXPENSIVE: '$',
                PRICE_LEVEL_MODERATE: '$$',
                PRICE_LEVEL_EXPENSIVE: '$$$',
                PRICE_LEVEL_VERY_EXPENSIVE: '$$$$'
            };

            return map[value] || value || '';
        };

        const readableTypes = function (types)
        {
            if (!$.isArray(types)) return '';

            return types.map(function (item)
            {
                return String(item || '').replace(/_/g, ' ');
            }).join(', ');
        };

        const applyAddressSelection = function (address, details)
        {
            const $addressField = $(addressFieldMap.input || '#lsd_object_type_address');
            const $dropdown = $(addressFieldMap.dropdown || '#lsd_object_type_address_dropdown');
            if (!$addressField.length) return false;

            const label = String(address || '');
            const item = {
                label: label,
                value: label
            };

            if (details && typeof details.latitude === 'number' && typeof details.longitude === 'number')
            {
                item.lat = details.latitude;
                item.lon = details.longitude;
            }

            if (details && details.place_id)
            {
                item.placeId = details.place_id;
            }

            $addressField.val(label).trigger('input');

            if ($dropdown.length && ((details && typeof details.latitude === 'number' && typeof details.longitude === 'number') || (details && details.place_id)))
            {
                $dropdown.trigger('lsd-autocomplete-select', [item]);
            }
            else
            {
                $addressField.trigger('change');
            }

            return true;
        };

        const rowHtml = function (label, value, actions)
        {
            if (!value && value !== 0) return '';

            let html = '<div class="lsdaddgpl-suggestion-row">';
            html += '<strong>' + escapeHtml(label) + '</strong>';
            html += '<div class="lsdaddgpl-suggestion-value">' + escapeHtml(value) + '</div>';

            if (actions) html += '<div class="lsdaddgpl-suggestion-actions">' + actions + '</div>';

            html += '</div>';
            return html;
        };

        const multilineRowHtml = function (label, values, actions)
        {
            if (!$.isArray(values) || !values.length) return '';

            const lines = values.map(function (value)
            {
                return escapeHtml(value);
            }).filter(function (value)
            {
                return value !== '';
            });

            if (!lines.length) return '';

            let html = '<div class="lsdaddgpl-suggestion-row">';
            html += '<strong>' + escapeHtml(label) + '</strong>';
            html += '<div class="lsdaddgpl-suggestion-value">' + lines.join('<br>') + '</div>';

            if (actions) html += '<div class="lsdaddgpl-suggestion-actions">' + actions + '</div>';

            html += '</div>';
            return html;
        };

        const renderSuggestions = function (details)
        {
            const openingHours = $.isArray(details.opening_hours) ? details.opening_hours : [];
            const matchedCategory = details.matched_category || {};
            const categoryActions = matchedCategory.id
                ? '<button type="button" class="button button-small" data-apply-category="' + escapeHtml(matchedCategory.id) + '">' + escapeHtml(config.strings.applyCategory || 'Apply Category') + '</button>'
                : '';
            const addressCopyAttributes = [
                'data-copy-address="1"',
                'data-copy-value="' + escapeHtml(details.formatted_address || '') + '"',
                'data-copy-place-id="' + escapeHtml(details.place_id || '') + '"'
            ];

            if (typeof details.latitude === 'number') addressCopyAttributes.push('data-copy-lat="' + escapeHtml(details.latitude) + '"');
            if (typeof details.longitude === 'number') addressCopyAttributes.push('data-copy-lng="' + escapeHtml(details.longitude) + '"');

            let html = '<p class="lsd-admin-description-tiny lsd-my-0">' + escapeHtml(config.strings.reviewDisclaimer || 'Review before copying. Saved values become native Listdom content.') + '</p>';
            html += rowHtml(titleField.label || 'Place Name', details.place_name, '<button type="button" class="button button-small" data-copy-target="' + escapeHtml(titleField.target || '#title') + '" data-copy-value="' + escapeHtml(details.place_name || '') + '">' + escapeHtml(config.strings.copyToTitle || 'Copy to Title') + '</button>');
            html += rowHtml(addressFieldMap.label || 'Formatted Address', details.formatted_address, '<button type="button" class="button button-small" ' + addressCopyAttributes.join(' ') + '>' + escapeHtml(config.strings.copyToAddress || 'Copy to Address') + '</button>');
            html += rowHtml(phoneField.label || 'Phone Number', details.phone_number, '<button type="button" class="button button-small" data-copy-target="' + escapeHtml(phoneField.target || '#lsd_phone') + '" data-copy-value="' + escapeHtml(details.phone_number || '') + '">' + escapeHtml(config.strings.copyToPhone || 'Copy to Phone') + '</button>');
            html += rowHtml(websiteField.label || 'Website', details.website, '<button type="button" class="button button-small" data-copy-target="' + escapeHtml(websiteField.target || '#lsd_website') + '" data-copy-value="' + escapeHtml(details.website || '') + '">' + escapeHtml(config.strings.copyToWebsite || 'Copy to Website') + '</button>');
            html += multilineRowHtml(openingHoursField.label || 'Opening Hours', openingHours, '');
            html += rowHtml(primaryTypeField.label || 'Primary Type', details.primary_type_label || details.primary_type, '');
            html += rowHtml(categoriesField.label || 'Categories', matchedCategory.name ? (matchedCategory.name + ' (' + (matchedCategory.source || 'exact match') + ')') : readableTypes(details.types), categoryActions);

            if (typeof details.latitude === 'number' && typeof details.longitude === 'number')
            {
                html += rowHtml(
                    coordinatesField.label || 'Latitude / Longitude',
                    details.latitude + ', ' + details.longitude,
                    '<button type="button" class="button button-small" data-copy-lat="' + escapeHtml(details.latitude) + '" data-copy-lng="' + escapeHtml(details.longitude) + '">' + escapeHtml(config.strings.copyToMapFields || 'Copy to Map Fields') + '</button>'
                );
            }

            html += rowHtml(config.strings.priceLevelLabel || 'Price Level', formatPriceLevel(details.price_level), '');

            $suggestions.html(html);
        };

        const searchEmptyHtml = function (message)
        {
            return '<div class="lsdaddgpl-place-search-empty">' + escapeHtml(message) + '</div>';
        };

        const renderSearchResults = function (results, query)
        {
            if (query.length < 2)
            {
                $searchResults.html(searchEmptyHtml(config.strings.searchEmpty || 'Start typing to search Google Places.'));
                return;
            }

            if (!$.isArray(results) || !results.length)
            {
                $searchResults.html(searchEmptyHtml(config.strings.searchNoMatches || 'No matching Google Places were found.'));
                return;
            }

            let html = '<div class="lsdaddgpl-place-search-list">';

            results.forEach(function (item)
            {
                html += '<button type="button" class="lsdaddgpl-place-search-item" data-place-id="' + escapeHtml(item.place_id || '') + '">';
                html += '<span class="lsdaddgpl-place-search-item-icon"><i class="fa-solid fa-location-dot" aria-hidden="true"></i></span>';
                html += '<span class="lsdaddgpl-place-search-item-content">';
                html += '<strong>' + escapeHtml(item.name || '') + '</strong>';

                if (item.address)
                {
                    html += '<span>' + escapeHtml(item.address) + '</span>';
                }

                html += '</span>';
                html += '</button>';
            });

            html += '</div>';
            $searchResults.html(html);
        };

        const showPlaceIdEntry = function ()
        {
            $display.hide();
            $suggestions.hide().html('');
            $entry.show();
            $searchInput.val('');
            $searchClear.hide();
            renderSearchResults([], '');
            $searchInput.trigger('focus');
        };

        const setFetchButtonVisibility = function (visible)
        {
            if (!$fetchButton.length) return;

            $fetchButton.toggle(!!visible);
        };

        const showSuggestionsPanel = function ()
        {
            $entry.hide();
            $display.show();
            $suggestions.show();
        };

        const updateCurrentPlaceId = function (nextPlaceId)
        {
            placeId = String(nextPlaceId || '');
            $metabox.attr('data-place-id', placeId).data('placeId', placeId);
            $displayCurrent.text(placeId);
            setFetchButtonVisibility(!!placeId);
        };

        const saveSelectedPlace = function (nextPlaceId)
        {
            $.ajax({
                type: 'POST',
                url: config.ajaxUrl,
                dataType: 'json',
                data: {
                    action: 'lsdaddgpl_save_place_id',
                    _wpnonce: config.placeIdNonce,
                    post_id: postId,
                    place_id: nextPlaceId
                }
            }).done(function (response)
            {
                if (!response || Number(response.success) !== 1)
                {
                    setFeedback($feedback, escapeHtml(response && response.message ? response.message : (config.strings.placeIdSaveFailed || 'Unable to save the Place ID for this listing.')), 'lsd-error');
                    return;
                }

                updateCurrentPlaceId(response.place_id || nextPlaceId);
                setFeedback($feedback, config.strings.placeSelected || 'Place selected. Review the Google suggestions below and save the listing when ready.', 'lsd-success');
                loadSuggestions();
            }).fail(function ()
            {
                setFeedback($feedback, config.strings.placeIdSaveFailed || 'Unable to save the Place ID for this listing.', 'lsd-error');
            });
        };

        const searchPlaces = debounce(function ()
        {
            const query = $.trim($searchInput.val());
            $searchClear.toggle(query.length > 0);

            if (query.length < 2)
            {
                if (searchRequest && typeof searchRequest.abort === 'function') searchRequest.abort();
                renderSearchResults([], query);
                return;
            }

            if (!config.hasPlacesApiKey)
            {
                setFeedback($feedback, config.strings.missingPlacesApiKey || 'Missing Google Places API key.', 'lsd-warning');
                return;
            }

            if (searchRequest && typeof searchRequest.abort === 'function') searchRequest.abort();

            searchRequest = $.ajax({
                type: 'POST',
                url: config.ajaxUrl,
                dataType: 'json',
                data: {
                    action: 'lsdaddgpl_lookup_place',
                    _wpnonce: config.placeIdNonce,
                    post_id: postId,
                    query: query
                }
            }).done(function (response)
            {
                if (!response || Number(response.success) !== 1)
                {
                    setFeedback($feedback, escapeHtml(response && response.message ? response.message : (config.strings.searchPlaceFailed || 'Unable to search Google Places right now.')), 'lsd-error');
                    $searchResults.html(searchEmptyHtml(config.strings.searchPlaceFailed || 'Unable to search Google Places right now.'));
                    return;
                }

                setFeedback($feedback, '', '');
                renderSearchResults(response.results || [], query);
            }).fail(function (xhr, textStatus)
            {
                if (textStatus === 'abort') return;

                setFeedback($feedback, config.strings.searchPlaceFailed || 'Unable to search Google Places right now.', 'lsd-error');
                $searchResults.html(searchEmptyHtml(config.strings.searchPlaceFailed || 'Unable to search Google Places right now.'));
            });
        }, 300);

        const loadSuggestions = function ()
        {
            if (!placeId) return;

            if (!config.hasPlacesApiKey)
            {
                setFeedback($feedback, config.strings.missingPlacesApiKey || 'Missing Google Places API key.', 'lsd-warning');
                return;
            }

            setFetchButtonVisibility(false);
            showSuggestionsPanel();
            setFeedback($feedback, '', '');
            $suggestions.html('<p class="lsd-admin-description">' + escapeHtml(config.strings.loadingPlaceDetails || 'Loading Google Places…') + '</p>');

            $.ajax({
                type: 'POST',
                url: config.ajaxUrl,
                dataType: 'json',
                data: {
                    action: 'lsdaddgpl_details',
                    _wpnonce: config.detailsNonce,
                    post_id: postId,
                    place_id: placeId
                }
            }).done(function (response)
            {
                if (!response || Number(response.success) !== 1)
                {
                    setFeedback($feedback, escapeHtml(response && response.message ? response.message : (config.strings.loadSuggestionsFailed || 'Unable to load suggestions.')), 'lsd-error');
                    $suggestions.html('');
                    return;
                }

                setFeedback($feedback, '', '');
                renderSuggestions(response.details || {});
            }).fail(function ()
            {
                setFeedback($feedback, config.strings.loadSuggestionsFailed || 'Unable to load suggestions.', 'lsd-error');
                $suggestions.html('');
            });
        };

        const setFieldValue = function (selector, value)
        {
            const $field = $(selector);
            if (!$field.length) return false;

            $field.val(value).trigger('input').trigger('change');

            if (selector === '#title' && typeof window.send_to_editor === 'undefined')
            {
                $('#title-prompt-text').hide();
            }

            return true;
        };

        const ensureMarkerObjectType = function ()
        {
            $(coordinatesField.object_type_target || '#lsd_object_type').val('marker');

            const $markerToggle = $(coordinatesField.marker_toggle || '#lsd_metabox_object_type_marker');
            if ($markerToggle.length)
            {
                $markerToggle.trigger('click');
            }
        };

        $suggestions.on('click', '[data-copy-address]', function ()
        {
            const $button = $(this);
            const value = String($button.data('copyValue') || '');
            const details = {
                place_id: String($button.data('copyPlaceId') || '')
            };
            const lat = $button.data('copyLat');
            const lng = $button.data('copyLng');

            if (lat !== undefined && lat !== '') details.latitude = Number(lat);
            if (lng !== undefined && lng !== '') details.longitude = Number(lng);

            if (applyAddressSelection(value, details))
            {
                notifyCopy(config.strings.copySuccess || 'Copied.', 'lsd-success');
            }
        });

        $suggestions.on('click', '[data-copy-target]', function ()
        {
            const $button = $(this);
            const target = String($button.data('copyTarget') || '');
            const value = String($button.data('copyValue') || '');

            if (setFieldValue(target, value))
            {
                notifyCopy(config.strings.copySuccess || 'Copied.', 'lsd-success');
            }
        });

        $suggestions.on('click', '[data-copy-lat]', function ()
        {
            const $button = $(this);
            const lat = String($button.data('copyLat') || '');
            const lng = String($button.data('copyLng') || '');

            ensureMarkerObjectType();

            const latDone = setFieldValue(coordinatesField.latitude_target || '#lsd_object_type_latitude', lat);
            const lngDone = setFieldValue(coordinatesField.longitude_target || '#lsd_object_type_longitude', lng);

            if (latDone && lngDone) notifyCopy(config.strings.copySuccess || 'Copied.', 'lsd-success');
        });

        $suggestions.on('click', '[data-apply-category]', function ()
        {
            const categoryId = String($(this).data('applyCategory') || '');
            if (!setFieldValue(categoriesField.target || '#lsd_listing_category', categoryId))
            {
                notifyCopy(config.strings.selectCategoryFirst || 'No category match.', 'lsd-warning');
                return;
            }

            notifyCopy(config.strings.copySuccess || 'Category applied.', 'lsd-success');
        });

        $searchInput.on('input', function ()
        {
            searchPlaces();
        });

        $searchInput.on('keydown', function (event)
        {
            if (event.key !== 'Enter') return;

            event.preventDefault();
        });

        $searchClear.on('click', function ()
        {
            $searchInput.val('').trigger('focus');
            $searchClear.hide();
            renderSearchResults([], '');
        });

        $searchResults.on('click', '.lsdaddgpl-place-search-item', function ()
        {
            const nextPlaceId = String($(this).data('placeId') || '');
            if (!nextPlaceId) return;

            saveSelectedPlace(nextPlaceId);
        });

        $changeButton.on('click', function ()
        {
            setFeedback($feedback, '', '');
            showPlaceIdEntry();
        });

        $fetchButton.on('click', function ()
        {
            setFeedback($feedback, '', '');
            loadSuggestions();
        });

        if (!placeId)
        {
            showPlaceIdEntry();
            return;
        }

        $entry.hide();
        $display.show();
        setFetchButtonVisibility(true);
        $suggestions.hide().html('');
    };

    initSeedingPage();
    initSuggestionsMetabox();
});
