/**
 * Listing Submission
 */
(function($)
{
    // Document is Ready!
    $(document).ready(function()
    {
        function listdomSwitchForm(buttonsSelector, formSelector, fadeDuration)
        {
            jQuery(formSelector).first().show();
            jQuery(buttonsSelector).first().addClass('active');

            jQuery(buttonsSelector).on('click', function()
            {
                let targetForm = jQuery(this).data('target');

                jQuery('.lsd-auth-form-content:visible').fadeOut(fadeDuration, function() {
                    jQuery(targetForm).fadeIn(fadeDuration);
                });

                jQuery(buttonsSelector).removeClass('active');
                jQuery(this).addClass('active');

                jQuery(buttonsSelector).filter(function() {
                    return jQuery(this).data('target') === targetForm;
                }).addClass('active');
            });
        }

        listdomSwitchForm('.lsd-auth-switch-button', '.lsd-auth-form-content', 200);

         // Check for the 'tab' query parameter
        const urlParams = new URLSearchParams(window.location.search);
        const tab = urlParams.get('tab');

        if (tab)
        {
            const $loginForm = jQuery('.lsd-auth-switch-button[data-target="#lsd-login-form"]');
            const $registerForm = jQuery('.lsd-auth-switch-button[data-target="#lsd-register-form"]');
            const $forgotPasswordForm = jQuery('.lsd-auth-switch-button[data-target="#lsd-forgot-password-form"]');

            // Add the 'active' class to the correct tab based on the tab
            if (tab === 'login')
            {
                $loginForm.trigger('click')
                    .addClass('active');
            }
            else if (tab === 'register')
            {
                $registerForm.trigger('click')
                    .addClass('active');
            }
            else if (tab === 'lostpassword')
            {
                $forgotPasswordForm.trigger('click')
                    .addClass('active');
            }
        }

        /**
         * Listdom tab system
         */
        $('.lsd-tabs .nav-tab').on('click', function()
        {
            const key = $(this).data('key');

            $('.nav-tab').removeClass('nav-tab-active');
            $(this).addClass('nav-tab-active');

            $('.lsd-tab-content').removeClass('lsd-tab-content-active');
            $('#lsd_tab_content_' + key).addClass('lsd-tab-content-active');
        });

        /**
         * Listdom Profile picker -- Upload/Select Button
         */
        $('.lsd-select-profile-button').on('click', function(event)
        {
            event.preventDefault();

            const button = $(this);

            let frame;
            if(frame)
            {
                frame.open();
                return;
            }

            frame = wp.media({
                multiple: true
            });

            frame.on('select', function()
            {
                // Grab the selected attachments.
                const attachments = frame.state().get('selection');

                const target = $(button).data('for');
                const name = $(button).data('name');

                attachments.map(function(attachment)
                {
                    attachment = attachment.toJSON();
                    $(target).append('<li data-id="'+attachment.id+'"><input type="hidden" name="'+name+'" value="'+attachment.id+'"><img src="'+attachment.url+'" alt=""><div class="lsd-profile-actions"><i class="lsd-icon fas fa-trash-alt lsd-remove-profile-single-button"></i> <i class="lsd-icon fas fa-arrows-alt lsd-handler"></i></div></li>');
                });

                $('.lsd-remove-profile-button').toggleClass('lsd-util-hide');
                frame.close();

                // Trigger Remove Button
                $('.lsd-remove-profile-single-button').off('click').on('click', function(event)
                {
                    event.preventDefault();
                    $(this).parent().parent().remove();
                });
            });

            frame.open();
        });

        /**
         * Listdom Profile picker -- Remove All Button
         */
        $('.lsd-remove-profile-button').on('click', function(event)
        {
            event.preventDefault();
            const target = $(this).data('for');

            $(target).html('');

            $(this).toggleClass('lsd-util-hide');
        });

        /**
         * Listdom Gallery picker -- Upload/Select Button
         */
        $('.lsd-select-gallery-button').on('click', function(event)
        {
            event.preventDefault();

            const button = $(this);

            let frame;
            if(frame)
            {
                frame.open();
                return;
            }

            frame = wp.media({
                multiple: true
            });

            frame.on('select', function()
            {
                // Grab the selected attachments.
                const attachments = frame.state().get('selection');

                const target = $(button).data('for');
                const name = $(button).data('name');

                attachments.map(function(attachment)
                {
                    attachment = attachment.toJSON();
                    $(target).append('<li data-id="'+attachment.id+'"><input type="hidden" name="'+name+'" value="'+attachment.id+'"><img src="'+attachment.url+'" alt=""><div class="lsd-gallery-actions"><i class="lsd-icon fas fa-trash-alt lsd-remove-gallery-single-button"></i> <i class="lsd-icon fas fa-arrows-alt lsd-handler"></i></div></li>');
                });

                $('.lsd-remove-gallery-button').toggleClass('lsd-util-hide');
                frame.close();

                // Trigger Remove Button
                $('.lsd-remove-gallery-single-button').off('click').on('click', function(event)
                {
                    event.preventDefault();
                    $(this).parent().parent().remove();
                });
            });

            frame.open();
        });

        /**
         * Listdom Gallery Uploader
         */
        $('.lsd-upload-gallery-button').on('click', function(event)
        {
            event.preventDefault();

            $('#lsd_listing_gallery_uploader').click();
        });

        /**
         * Listdom Gallery picker -- Remove All Button
         */
        $('.lsd-remove-gallery-button').on('click', function(event)
        {
            event.preventDefault();
            const target = $(this).data('for');

            $(target).html('');

            $(this).toggleClass('lsd-util-hide');
        });

        /**
         * Listdom Gallery picker -- Single Remove Button
         */
        $('.lsd-remove-gallery-single-button').off('click').on('click', function(event)
        {
            event.preventDefault();
            $(this).parent().parent().remove();
        });

        /**
         * Listdom Embed -- Toggle Featured
         */
        $(document).on('click', '.lsd-embed-featured-icon', function()
        {
            let $icon = $(this);
            let isCurrentlyFeatured = $icon.attr('data-featured') === '1';

            if(!isCurrentlyFeatured)
            {
                $('.lsd-embed-featured-icon[data-featured="1"]').removeClass('fas fa-star')
                    .addClass('far fa-star')
                    .attr('title', 'Add as Featured Video')
                    .attr('data-featured', '0')
                    .closest('li').find('.lsd-embed-featured-status').val('0');

                $icon.removeClass('far fa-star')
                    .addClass('fas fa-star')
                    .attr('title', 'Remove From Featured Video')
                    .attr('data-featured', '1')
                    .closest('li').find('.lsd-embed-featured-status').val('1');
            }
            else
            {
                $icon.removeClass('fas fa-star')
                    .addClass('far fa-star')
                    .attr('title', 'Add as Featured Video')
                    .attr('data-featured', '0')
                    .closest('li').find('.lsd-embed-featured-status').val('0');
            }
        });

        /**
         * Listdom Embed -- Add Button
         */
        $('.lsd-add-embed-button').on('click', function(event)
        {
            event.preventDefault();

            const template = $(this).data('template');
            const target = $(this).data('for');

            // New Index
            const $index = $('#lsd_listing_embeds_index');
            const index = $index.val();
            const new_index = parseInt(index)+1;

            // Update Index
            $index.val(new_index);

            // Content
            const content = $(template).html().replace(/:i:/g, index);

            $(target).append(content);

            // Trigger Remove Button
            $('.lsd-remove-embed-single-button').off('click').on('click', function(event)
            {
                event.preventDefault();
                $(this).parent().parent().parent().remove();
            });
        });

        /**
         * Listdom Embed -- Remove All Button
         */
        $('.lsd-remove-embed-button').on('click', function(event)
        {
            event.preventDefault();
            const target = $(this).data('for');

            $(target).html('');

            $(this).toggleClass('lsd-util-hide');
        });

        /**
         * Listdom Embed -- Single Remove Button
         */
        $('.lsd-remove-embed-single-button').off('click').on('click', function(event)
        {
            event.preventDefault();
            $(this).parent().parent().parent().remove();
        });

        /**
         * Listdom file picker -- Upload/Select Button
         */
        $('.lsd-select-file-button').on('click', function(event)
        {
            event.preventDefault();

            const button = $(this);

            let frame;
            if(frame)
            {
                frame.open();
                return;
            }

            frame = wp.media();
            frame.on('select', function()
            {
                // Grab the selected attachment.
                const attachment = frame.state().get('selection').first();

                const target = $(button).data('for');
                $(target+'_file').html('<a href="'+attachment.attributes.url+'" target="_blank">'+attachment.attributes.url+'</a>');
                $(target).val(attachment.id);

                $('.lsd-select-file-button').toggleClass('lsd-util-hide');
                $('.lsd-remove-file-button').toggleClass('lsd-util-hide');

                frame.close();
            });

            frame.open();
        });

        /**
         * Listdom File picker -- Remove Button
         */
        $('.lsd-remove-file-button').on('click', function(event)
        {
            event.preventDefault();
            const target = $(this).data('for');

            $(target+'_file').html('');
            $(target).val('');

            $('.lsd-select-file-button').toggleClass('lsd-util-hide');
            $('.lsd-remove-file-button').toggleClass('lsd-util-hide');
        });

        /**
         * Bookables -- Add Button
         */
        $('.lsd-add-bookable-button').on('click', function(event)
        {
            event.preventDefault();

            const template = $(this).data('template');
            const target = $(this).data('for');

            // New Index
            const $index = $('#lsd_listing_bookables_index');
            const index = $index.val();
            const new_index = parseInt(index)+1;

            // Update Index
            $index.val(new_index);

            // Content
            const content = $(template).html().replace(/:i:/g, index);

            $(target).append(content);

            listdom_trigger_bookable_remove();
            listdom_trigger_bookable_advanced();
            listdom_trigger_bookable_prices();
            listdom_trigger_toggle();
        });

        /**
         * Booking -- Bookables Type
         */
        $('#lsd_bo_type').on('change', function()
        {
            const type = $(this).val();

            $('.lsd-listing-bookable-container')
                .removeClass('lsd-listing-bookables-general')
                .removeClass('lsd-listing-bookables-property')
                .removeClass('lsd-listing-bookables-event')
                .addClass('lsd-listing-bookables-'+type);
        });

        listdom_trigger_bookable_remove();
        listdom_trigger_bookable_advanced();
        listdom_trigger_bookable_prices();

        /**
         * Add/Edit Listing
         */
        $('#lsd_listing_category').on('change', function() {
            let category = $(this).val();

            const $fields = $('.lsd-category-specific');
            const $all = $('.lsd-category-specific-all');
            const $category = $('.lsd-category-specific-' + category);
            const $required_fields = $('input[data-required="1"], select[data-required="1"], textarea[data-required="1"]');

            $fields.addClass('lsd-util-hide');
            $all.removeClass('lsd-util-hide');
            $category.removeClass('lsd-util-hide');

            $fields.find($('input[required], select[required], textarea[required]')).removeAttr('required');
            $all.find($required_fields).attr('required', true);
            $category.find($required_fields).attr('required', true);

            lsdaddjob_new_category(category);
        }).trigger('change');

        // Opening Hours Off
        $('.lsd-ava-off').on('change', function()
        {
            const daycode = $(this).data('daycode');

            if($(this).is(':checked')) $('#lsd-ava-'+daycode+' .lsd-ava-hours input').attr('disabled', 'disabled').addClass('disabled');
            else $('#lsd-ava-'+daycode+' .lsd-ava-hours input').removeAttr('disabled').removeClass('disabled');
        }).trigger('change');

        // Disable Form Submit on Enter of Address Field
        $('#lsd_object_type_address').on('keyup keypress', function(e)
        {
            const keyCode = e.keyCode || e.which;
            if(keyCode === 13)
            {
                e.preventDefault();
                return false;
            }
        });

        /**
         * Listdom Auto Suggest Field
         */
        let $lsd_autosuggest_ajax;
        $('.lsd-autosuggest').on('keyup', function()
        {
            const $input = $(this);

            const term = $input.val();
            const min_characters = $input.data('min-characters');
            const max_items = $input.data('max-items');
            const source = $input.data('source');
            const name = $input.data('name');
            const nonce = $input.data('nonce');

            const append = $input.data('append');
            const $append = $(append);

            const $wrapper = $input.parent();
            const $suggestions = $($input.data('suggestions'));

            // Term is too short
            if(term.length < min_characters)
            {
                $wrapper.removeClass('lsd-has-suggestions');
                $suggestions.html('');

                return false;
            }

            // Abort Previous AJAX Request
            if($lsd_autosuggest_ajax) $lsd_autosuggest_ajax.abort();

            $lsd_autosuggest_ajax = $.ajax(
            {
                url: lsd.ajaxurl,
                data: 'action=lsd_autosuggest&_wpnonce='+nonce+'&term='+term+'&source='+source,
                dataType: 'json',
                type: 'post',
                success: function(data)
                {
                    if(data.success)
                    {
                        if(data.total) $wrapper.addClass('lsd-has-suggestions');
                        else $wrapper.removeClass('lsd-has-suggestions');

                        $suggestions.html(data.items);
                        $suggestions.find($('li')).off('click').on('click', function()
                        {
                            const $item = $(this);
                            const value = $item.data('value');
                            const label = $item.text();

                            // Certain Amount of Items are Allowed
                            if(max_items && $append.find($('span')).length >= max_items)
                            {
                                return;
                            }

                            // Append if not exists
                            if(!$append.find($('.lsd-autosuggest-items-'+value)).length)
                            {
                                $append.append('<span class="lsd-tooltip lsd-autosuggest-items-'+value+'" data-lsd-tooltip="Click twice to delete">'+label+' <i class="lsd-icon far fa-trash-alt" data-value="'+value+'" data-confirm="0"></i><input type="hidden" name="'+name+'[]" value="'+value+'"></span>');
                                listdom_trigger_autosuggest_remove();

                                // Toggle
                                const toggle = $wrapper.data('toggle');
                                if(toggle) $(toggle).removeClass('lsd-util-hide');
                            }

                            // Empty Value and Suggestions
                            $input.val('');
                            $suggestions.html('');

                            $wrapper.removeClass('lsd-has-suggestions');
                        });
                    }
                }
            });
        });

        listdom_trigger_autosuggest_remove();

        // Date Range Picker
        if(typeof $.fn.daterangepicker !== 'undefined')
        {
            $('input.lsd-date-range-picker').each(function()
            {
                // Input
                const $input = $(this);

                // Periods
                const periods = $input.data('periods');

                // Format
                const df = $input.data('format');

                let ranges = {};
                for(const p in periods)
                {
                    const label = periods[p];
                    ranges[label] = [moment().add(p, 'month').startOf('month'), moment().add(p, 'month').endOf('month')];
                }

                // Init Date Range Picker
                $input.daterangepicker(
                {
                    minDate: moment(),
                    ranges: ranges,
                    alwaysShowCalendars: true,
                    showCustomRangeLabel: false,
                    autoUpdateInput: false,
                    locale:
                    {
                        format: df
                    }
                });

                $input.on('apply.daterangepicker', function(ev, picker) {
                    $(this).val(picker.startDate.format(df) + ' - ' + picker.endDate.format(df));
                });

                $input.on('cancel.daterangepicker', function() {
                    $(this).val('');
                });
            });
        }

        // Uploader Field
        $('.lsd-uploader input[type=file]').on('change', function(e)
        {
            e.preventDefault();

            const $input = $(this);
            const key = $input.data('key');
            const nonce = $input.data('nonce');

            // Elements
            const $wrapper = $('#lsd_uploader_'+key+'_wrapper');
            const $preview = $wrapper.find($('.lsd-uploader-preview'));
            const $value = $wrapper.find($('input.lsd-uploader-value'))
            const $alert = $('#lsd_uploader_'+key+'_message');

            let fd = new FormData();
            fd.append('action', 'lsd_uploader');
            fd.append('key', key);
            fd.append('_wpnonce', nonce);

            // Append Images
            const ins = $input.prop('files').length;
            for(let x = 0; x < ins; x++) fd.append('files[]', $input.prop('files')[x]);

            // Empty Alert
            $alert.html('');

            $.ajax(
            {
                url: lsd.ajaxurl,
                type: 'POST',
                data: fd,
                dataType: 'json',
                processData: false,
                contentType: false
            })
            .done(function(response)
            {
                if(response.success)
                {
                    let value = '';
                    if($preview.length)
                    {
                        // Empty Preview
                        $preview.html('');

                        response.data.map(function(attachment)
                        {
                            $preview.append('<a href="'+attachment.url+'" target="_blank">'+attachment.url+'</a><br>');
                        });
                    }

                    response.data.map(function(attachment)
                    {
                        value += attachment.id+',';
                    });

                    // Show Alert
                    $alert.html(listdom_alertify(response.message, 'lsd-success'));

                    // Set Value
                    $value.val(value);
                }
                else
                {
                    // Show Alert
                    $alert.html(listdom_alertify(response.message, 'lsd-error'));
                }

                // Empty Alert
                setTimeout(function()
                {
                    $alert.html('');
                }, 5000);
            });
        });

        // Horizontal Scroll Shadow
        $('.lsd-h-scroll-shadow-wrapper').each(function()
        {
            const $wrapper = $(this);
            const $content = $wrapper.find($('.lsd-h-scroll-shadow-content'));
            const $shadow_left = $wrapper.find($('.lsd-h-scroll-shadow-left'));
            const $shadow_right = $wrapper.find($('.lsd-h-scroll-shadow-right'));
            const scrollable = $content[0].scrollWidth - $wrapper[0].offsetWidth;

            if (scrollable === 0)
            {
                $shadow_left.css('opacity', 0);
                $shadow_right.css('opacity', 0);
            }
            else
            {
                $shadow_right.css('opacity', 1);

                $content.on('scroll', function()
                {
                    let current = this.scrollLeft / scrollable;

                    $shadow_left.css('opacity', current);
                    $shadow_right.css('opacity', 1 - current);
                });
            }
        });

        // Collapsible
        $('.lsd-collapsible .lsd-collapsible-trigger').on('click', function()
        {
            const $collapsible = $(this).parent();
            $collapsible.removeClass('lsd-collapsible-close');
        });

        // Inline Popup
        $('.lsd-inline-popup-trigger').on('click', function(e)
        {
            e.preventDefault();
            e.stopPropagation();

            const $popup = $($(this).data('for'));
            const $body = $('body');

            const hidePopup = (event) => {
                if (!$popup.is(event.target) && $popup.has(event.target).length === 0) {
                    $body.find('.lsd-inline-popup-active').removeClass('lsd-inline-popup-active');
                    $body.removeClass('lsd-inline-popup-is-open');
                    $body.off('click.lsd-inline-popup', hidePopup);
                }
            };

            // Close current popup if the same button is clicked
            if ($popup.hasClass('lsd-inline-popup-active')) {
                return hidePopup(e);
            }

            // Close any other opened popup and remove previous handlers
            $body.off('click.lsd-inline-popup');
            $body.find('.lsd-inline-popup-active').removeClass('lsd-inline-popup-active');
            $body.removeClass('lsd-inline-popup-is-open');

            $popup.addClass('lsd-inline-popup-active');

            const focus = $(this).data('focus');
            if (focus) $(focus).focus();

            setTimeout(() => {
                $body.addClass('lsd-inline-popup-is-open');
                $body.on('click.lsd-inline-popup', hidePopup);
            }, 200);
        });
    });

    /**
     * Listdom Image picker -- Upload/Select Button
     */
    $('.lsd-select-image-button').on('click', function (event)
    {
        event.preventDefault();

        const $button = $(this);

        let frame;
        if (frame)
        {
            frame.open();
            return;
        }

        frame = wp.media({});
        frame.on('select', function ()
        {
            // Grab the selected attachment.
            const attachment = frame.state().get('selection').first();

            const target = $button.data('for');
            $(target + '_img').html('<img alt="" src="' + attachment.attributes.url + '">');
            $(target).val(attachment.id);

            $button.toggleClass('lsd-util-hide');
            $button.parent().find($('.lsd-remove-image-button')).toggleClass('lsd-util-hide');

            frame.close();
        });

        frame.open();
    });

    /**
     * Listdom Image picker -- Remove Button
     */
    $('.lsd-remove-image-button').on('click', function (event)
    {
        event.preventDefault();

        const $button = $(this);
        const target = $button.data('for');

        $(target + '_img').html('');
        $(target).val('');

        $button.parent().find($('.lsd-select-image-button')).toggleClass('lsd-util-hide');
        $button.toggleClass('lsd-util-hide');
    });
}(jQuery));

/**
 * Listdom Toggle System
 */
function listdom_trigger_toggle()
{
    jQuery('.lsd-toggle').off('click').on('click', function()
    {
        const $toggle = jQuery(this);
        const target = $toggle.data('for');
        const target2 = $toggle.data('for2');
        const all = $toggle.data('all');

        const status = jQuery(target).is(':visible') ? 'show' : 'hide';

        // Hide All Elements if defined
        if(all) jQuery(all).hide();

        // Show/Hide target element
        if(status === 'show')
        {
            jQuery(target).addClass('lsd-util-hide').hide();
            if(target2) jQuery(target2).removeClass('lsd-util-hide').show();
        }
        else if(status === 'hide')
        {
            jQuery(target).removeClass('lsd-util-hide').show();
            if(target2) jQuery(target2).addClass('lsd-util-hide').hide();
        }

        $toggle.data('triggered', 1);
        setTimeout(function()
        {
            $toggle.data('triggered', 0);
        }, 200);
    });
}

function listdom_trigger_select()
{
    jQuery('.lsd-trigger-select-options').off('change').on('change', function()
    {
        const $selectedOption = jQuery(this).find('option:selected');
        const show = $selectedOption.data('lsd-show');
        const hide = $selectedOption.data('lsd-hide');

        if (show) jQuery(show).removeClass('lsd-util-hide').show();
        if (hide) jQuery(hide).addClass('lsd-util-hide').hide();
    });
}

function listdom_trigger_bookable_remove()
{
    jQuery('.lsd-remove-bookable-button').off('click').on('click', function(e)
    {
        e.preventDefault();

        const $icon = jQuery(this);

        // Delete Confirm
        const confirm = $icon.data('confirm');
        if(confirm === 0)
        {
            $icon.data('confirm', 1);
            $icon.addClass('lsd-need-confirm');

            setTimeout(function()
            {
                $icon.data('confirm', 0);
                $icon.removeClass('lsd-need-confirm');
            }, 5000);

            return;
        }

        $icon.parent().parent().parent().remove();
    });
}

function listdom_trigger_bookable_advanced()
{
    // Trigger Advanced Button
    jQuery('.lsd-bookable-advanced').off('click').on('click', function()
    {
        const i = jQuery(this).data('i');
        const $advanced = jQuery('#lsd_listing_bookables_'+i+'_advanced');

        if($advanced.hasClass('lsd-util-hide')) $advanced.removeClass('lsd-util-hide');
        else $advanced.addClass('lsd-util-hide');
    });
}

function listdom_trigger_bookable_prices()
{
    // Trigger Price Button
    jQuery('.lsd-bookable-add-price-button').off('click').on('click', function()
    {
        const i = jQuery(this).data('i');
        const $advanced = jQuery('#lsd_listing_bookables_'+i+'_advanced');
        const $start = $advanced.find(jQuery('.lsd-bookable-adv-start-date'));
        const $end = $advanced.find(jQuery('.lsd-bookable-adv-end-date'));
        const $price = $advanced.find(jQuery('.lsd-bookable-adv-price'));
        const $list = $advanced.find(jQuery('.lsd-listing-bookables-prices'));

        const start = $start.val();
        const end = $end.val();
        const price = $price.val();

        if(start === '')
        {
            $start.trigger('focus');
            return;
        }

        if(end === '')
        {
            $end.trigger('focus');
            return;
        }

        if(price === '')
        {
            $price.trigger('focus');
            return;
        }

        $list.prepend('<li><i class="lsd-icon fas fa-trash-alt lsd-remove-bookable-price-button" data-confirm="0"></i> <span>'+start+' - '+end+': '+price+'</span><input type="hidden" name="lsd[bookables]['+i+'][prices][]" value="'+start+','+end+','+price+'"></li>');
        listdom_trigger_bookable_price_remove();
    });

    listdom_trigger_bookable_price_remove();

    // Trigger Unavailable Button
    jQuery('.lsd-bookable-add-unavailable-button').off('click').on('click', function()
    {
        const i = jQuery(this).data('i');
        const $advanced = jQuery('#lsd_listing_bookables_'+i+'_advanced');
        const $start = $advanced.find(jQuery('.lsd-bookable-unavailable-start-date'));
        const $end = $advanced.find(jQuery('.lsd-bookable-unavailable-end-date'));
        const $list = $advanced.find(jQuery('.lsd-listing-bookables-unavailable-periods'));

        const start = $start.val();
        const end = $end.val();

        if(start === '')
        {
            $start.trigger('focus');
            return;
        }

        if(end === '')
        {
            $end.trigger('focus');
            return;
        }

        $list.prepend('<li><i class="lsd-icon fas fa-trash-alt lsd-remove-bookable-unavailable-button" data-confirm="0"></i> <span>'+start+' - '+end+'</span><input type="hidden" name="lsd[bookables]['+i+'][unavailable][]" value="'+start+','+end+'"></li>');
        listdom_trigger_bookable_unavailable_remove();
    });

    listdom_trigger_bookable_unavailable_remove();
}

function listdom_trigger_bookable_price_remove()
{
    jQuery('.lsd-remove-bookable-price-button').off('click').on('click', function(e)
    {
        e.preventDefault();

        const $icon = jQuery(this);

        // Delete Confirm
        const confirm = $icon.data('confirm');
        if(confirm === 0)
        {
            $icon.data('confirm', 1);
            $icon.addClass('lsd-need-confirm');

            setTimeout(function()
            {
                $icon.data('confirm', 0);
                $icon.removeClass('lsd-need-confirm');
            }, 5000);

            return;
        }

        $icon.parent().remove();
    });
}

function listdom_trigger_bookable_unavailable_remove()
{
    jQuery('.lsd-remove-bookable-unavailable-button').off('click').on('click', function(e)
    {
        e.preventDefault();

        const $icon = jQuery(this);

        // Delete Confirm
        const confirm = $icon.data('confirm');
        if(confirm === 0)
        {
            $icon.data('confirm', 1);
            $icon.addClass('lsd-need-confirm');

            setTimeout(function()
            {
                $icon.data('confirm', 0);
                $icon.removeClass('lsd-need-confirm');
            }, 5000);

            return;
        }

        $icon.parent().remove();
    });
}

function listdom_trigger_autosuggest_remove()
{
    // Remove Button
    jQuery('.lsd-autosuggest-current .lsd-icon').off('click').on('click', function(e)
    {
        e.preventDefault();

        const $button = jQuery(this);
        const $wrapper = $button.closest('.lsd-autosuggest-wrapper');
        const $current = $button.closest('.lsd-autosuggest-current');

        // Delete Confirm
        const confirm = $button.data('confirm');
        if(confirm === 0)
        {
            $button.data('confirm', 1);
            $button.parent().addClass('lsd-need-confirm');

            setTimeout(function()
            {
                $button.data('confirm', 0);
                $button.parent().removeClass('lsd-need-confirm');
            }, 5000);

            return;
        }

        // Remove Item
        $button.parent().remove();

        // Toggle
        setTimeout(() => {
            const toggle = $wrapper.data('toggle');
            if(toggle && !$current.find(jQuery('span')).length) jQuery(toggle).addClass('lsd-util-hide');
        }, 10);
    });
}

function lsdaddjob_new_category(category)
{
    const $dashboard = jQuery('#lsd_dashboard');
    if(!$dashboard.data('job-addon-installed')) return;

    jQuery.ajax(
    {
        url: lsd.ajaxurl,
        type: 'POST',
        data: {
            'action': 'lsdaddjob_is_job',
            'category': category
        },
        dataType: 'json',
    })
    .done(function(response)
    {
        if(response.success)
        {
            // Hide Modules
            if(response.is_job && typeof response.modules)
            {
                response.modules.forEach(function(item)
                {
                    const $module = jQuery('.lsd-listing-module-'+item);

                    $module.addClass('lsd-util-hide');
                    $module.find('[required]').removeAttr('required');

                    if(item === 'attributes') jQuery('.lsd-dashboard-attributes > h4').addClass('lsd-util-hide');
                    else if(item === 'address') jQuery('.lsd-dashboard-address > h4').addClass('lsd-util-hide');
                });
            }
            // Show Modules
            else
            {
                response.modules.forEach(function(item)
                {
                    jQuery('.lsd-listing-module-'+item).removeClass('lsd-util-hide');

                    if(item === 'attributes') jQuery('.lsd-dashboard-attributes > h4').removeClass('lsd-util-hide');
                    else if(item === 'address') jQuery('.lsd-dashboard-address > h4').removeClass('lsd-util-hide');
                });
            }
        }
    });
}

/**
 * Google Maps
 */
let listdom_googlemaps_callbacks = [];
function listdom_add_googlemaps_callbacks(func)
{
    if(typeof func !== 'undefined' && jQuery.isFunction(func))
    {
        // Push the function to callbacks if the callbacks didn't call already
        if(!listdom_did_googlemaps_callbacks) listdom_googlemaps_callbacks.push(func);
        // Run the function if callbacks called already
        else func();

        return true;
    }

    return false;
}

function listdom_get_googlemaps_callbacks()
{
    return listdom_googlemaps_callbacks;
}

let listdom_did_googlemaps_callbacks = false;
function listdom_googlemaps_callback()
{
    listdom_did_googlemaps_callbacks = true;
    for(let i in listdom_get_googlemaps_callbacks())
    {
        listdom_googlemaps_callbacks[i]();
    }
}

/**
 * Utilities
 */
function listdom_alertify(alert, type)
{
    return '<div class="lsd-alert '+type+'">'+alert+'</div>';
}

class ListdomToast {
    constructor(alert, options = {}) {
        this.alert = alert;
        this.type = options.type || 'lsd-natural';
        this.position = options.position || 'lsd-bottom-right';
        this.icon = options.icon || null;
        this.hideTime = typeof options.hideTime === 'number' ? options.hideTime : 8000;
        this.progress = options.progress !== false;
        this.showClose = options.showClose !== false;
        this.confirm = options.confirm || null;

        this.container = this.getContainer();
        this.toast = this.createToast();
        this.messageEl = this.toast.find('.lsd-toast-message');
        this.autoHideTimer = null;

        this.appendToast();

        // don’t auto-hide if it's confirmation
        if (!this.confirm) this.initAutoHide();

        this.bindEvents();
    }

    getContainer()
    {
        let container = jQuery('.lsd-toast-container.' + this.position);
        if (!container.length) container = jQuery('<div class="lsd-toast-container ' + this.position + '"></div>').appendTo('body');

        return container;
    }

    createToast()
    {
        const toast = jQuery('<div class="lsd-toast ' + this.type + '"></div>');

        const defaultIcons = {
            'lsd-error': '<i class="fa fa-times-circle"></i>',
            'lsd-warning': '<i class="fa fa-exclamation-triangle"></i>',
            'lsd-info': '<i class="fa fa-info-circle"></i>',
            'lsd-success': '<i class="fa fa-check-circle"></i>',
            'lsd-natural': '<i class="fa fa-bell"></i>',
            'lsd-in-progress': '<i class="lsd-loader"></i>',
            'lsd-confirm': '<i class="listdom-icon lsdi-question"></i>',
        };

        const finalIcon = this.icon || defaultIcons[this.type] || '';
        if (finalIcon)
        {
            if (finalIcon.indexOf('<') === -1) toast.append('<span class="lsd-toast-icon"><i class="' + finalIcon + '"></i></span>');
            else toast.append('<span class="lsd-toast-icon">' + finalIcon + '</span>');
        }

        toast.append('<span class="lsd-toast-message">' + this.alert + '</span>');

        // Confirmation buttons
        if (this.confirm)
        {
            this.overlay = jQuery('<div class="lsd-toast-overlay"></div>');
            jQuery('body').append(this.overlay);

            this.overlay.on('click', (e) => {
                if (e.target === this.overlay[0]) {
                    if (typeof this.confirm.onCloseOverlay === 'function') {
                        this.confirm.onCloseOverlay(this);
                    }
                    this.remove();
                }
            });

            const btnWrap = jQuery('<div class="lsd-toast-actions"></div>');

            // Default to Confirm / Cancel, but allow override
            const confirmLabel = this.confirm.confirmText;
            const cancelLabel  = this.confirm.cancelText;

            const confirmBtn = jQuery('<button class="lsd-secondary-button">' + confirmLabel + '</button>');
            const cancelBtn  = jQuery('<button class="lsd-secondary-button">' + cancelLabel  + '</button>');

            confirmBtn.on('click', () => {
                if (typeof this.confirm.onConfirm === 'function') this.confirm.onConfirm(this);
                this.remove();
            });

            cancelBtn.on('click', () => {
                if (typeof this.confirm.onCancel === 'function') this.confirm.onCancel(this);
                this.remove();
            });

            btnWrap.append(confirmBtn, cancelBtn);
            toast.append(btnWrap);

            // disable progress + auto-hide
            this.progress = false;
            this.hideTime = 0;
        }

        if (this.showClose && !this.confirm)
        {
            const closeBtn = jQuery('<span class="lsd-toast-close">&times;</span>');
            closeBtn.on('click', () => this.remove());
            toast.append(closeBtn);
        }

        if (this.progress && this.hideTime > 0)
        {
            toast.addClass('lsd-has-progress');
            toast.css('--lsd-progress-time', this.hideTime + 'ms');
            setTimeout(() => toast.addClass('lsd-progress-run'), 50);
        }

        return toast;
    }

    appendToast()
    {
        this.container.append(this.toast);
    }

    initAutoHide()
    {
        if (this.hideTime > 0) this.autoHideTimer = setTimeout(() => this.remove(), this.hideTime);
    }

    bindEvents()
    {
        if (!this.confirm)
        {
            this.toast.on('mouseenter', () => this.pause());
            this.toast.on('mouseleave', () => this.resume());
        }
    }

    pause()
    {
        this.toast.addClass('lsd-paused');
        if (this.autoHideTimer) clearTimeout(this.autoHideTimer);
        const computed = getComputedStyle(this.toast[0], '::after');
        const width = computed.getPropertyValue('width');
        this.toast[0].style.setProperty('--lsd-paused-width', width);
    }

    resume()
    {
        this.toast.removeClass('lsd-paused');
        if (this.hideTime > 0)
        {
            this.toast.css('--lsd-progress-time', this.hideTime + 'ms');
            this.toast.addClass('lsd-progress-run');
            this.autoHideTimer = setTimeout(() => this.remove(), this.hideTime);
        }
    }

    update(newMessage, newType)
    {
        this.messageEl.html(newMessage);
        if (newType)
        {
            this.toast.removeClass('lsd-error lsd-warning lsd-info lsd-success lsd-natural lsd-in-progress lsd-confirm');
            this.toast.addClass(newType);
        }
    }

    remove()
    {
        this.toast.addClass('lsd-toast-remove');
        setTimeout(() =>
        {
            this.toast.remove();
            if (this.overlay) this.overlay.remove();
            if (!this.container.children().length) this.container.remove();
        }, 300);
    }
}

const ListdomPageScroll = {
    scrollPosition: 0,

    stop() {
        this.scrollPosition = window.scrollY;
        document.body.style.position = 'fixed';
        document.body.style.top = `-${this.scrollPosition}px`;
        document.body.style.left = '0';
        document.body.style.right = '0';
        document.body.style.overflowY = 'scroll';
    },

    start() {
        document.body.style.position = '';
        document.body.style.top = '';
        document.body.style.left = '';
        document.body.style.right = '';
        document.body.style.overflowY = '';
        window.scrollTo(0, this.scrollPosition);
    }
};


function listdom_toastify(alert, type, options = {})
{
    if (options.update instanceof ListdomToast)
    {
        options.update.update(alert, type);
        return options.update;
    }

    return new ListdomToast(alert, {
        type: type,
        position: options.position,
        icon: options.icon,
        hideTime: options.hideTime,
        progress: options.progress,
        showClose: options.showClose,
        confirm: options.confirm
    });
}
