/**
 * Address Autocomplete Dropdown
 */
(function($)
{
    'use strict';

    if (typeof window.lsdCreateAddressDropdown === 'function') return;

    const createStub = function($element)
    {
        const noop = function() {};

        return {
            $element: $element || $(),
            reset: noop,
            populate: noop,
            markSelected: noop,
            selectItem: noop,
            hide: noop,
            showLoading: noop,
            show: noop,
            isUpdating: function()
            {
                return false;
            }
        };
    };

    const uniqueNamespace = function()
    {
        return '.lsdDropdown-' + Math.random().toString(36).slice(2);
    };

    window.lsdCreateAddressDropdown = function(element, options)
    {
        const $dropdown = element instanceof $ ? element : $(element);
        if (!$dropdown || !$dropdown.length) return createStub($dropdown);

        const existing = $dropdown.data('lsdDropdownInstance');
        if (existing) return existing;

        const settings = $.extend({
            itemIconClass: 'fa-solid fa-location-dot',
            loadingIconClass: 'fa-solid fa-spinner fa-spin',
            loadingText: null
        }, options || {});

        let updating = false;
        let $list = null;
        const namespace = uniqueNamespace();

        const $originParent = $dropdown.parent();

        const getLoadingText = function()
        {
            return settings.loadingText || $dropdown.data('loading-text') || 'Loading...';
        };

        const setUpdating = function(state)
        {
            updating = !!state;
            $dropdown.data('lsd-dropdown-updating', updating);
            if (!updating) $dropdown.removeClass('lsd-address-autocomplete-popup--loading');
        };

        const ensureStructure = function()
        {
            if (!$dropdown.length) return null;

            if (!$dropdown.hasClass('lsd-address-autocomplete-popup'))
            {
                $dropdown.addClass('lsd-address-autocomplete-popup');
            }

            const $parent = $dropdown.parent();
            if ($parent.length && !$dropdown.hasClass('lsd-address-autocomplete-popup--floating') && $parent.css('position') === 'static')
            {
                $parent.css('position', 'relative');
            }

            if (!$list || !$list.length)
            {
                $dropdown.empty();
                $list = $('<div class="lsd-address-autocomplete-list" role="presentation"></div>');
                $dropdown.append($list);
            }

            return $list;
        };

        const getTrigger = function()
        {
            const $trigger = $dropdown.data('lsdDropdownTrigger');
            if ($trigger && $trigger.length) return $trigger;

            const $fallback = $dropdown.closest('.lsd-radius-search-field').find('.lsd-radius-search-address').first();
            if ($fallback.length)
            {
                $dropdown.data('lsdDropdownTrigger', $fallback);
                return $fallback;
            }

            return null;
        };

        const positionDropdown = function()
        {
            const $trigger = getTrigger();
            if (!$trigger || !$trigger.length) return;

            const offset = $trigger.offset();
            if (!offset) return;

            const $modalContext = $trigger.closest('.lsd-modal');
            const $scrollContext = $trigger.closest('.lsd-search-included-in-more');
            const shouldFloat = $modalContext.length || $scrollContext.length;

            if (shouldFloat)
            {
                const $appendTarget = $modalContext.length ? $modalContext : $('body');

                if ($appendTarget.length && !$dropdown.parent().is($appendTarget))
                {
                    $dropdown.appendTo($appendTarget);
                }

                const width = $trigger.outerWidth();
                const top = offset.top + $trigger.outerHeight();

                $dropdown
                .addClass('lsd-address-autocomplete-popup--floating')
                .css({
                    left: offset.left,
                    top: top,
                    width: width,
                    right: 'auto',
                    zIndex: Math.max(parseInt($appendTarget.css('z-index'), 10) || 0, 10000)
                });
            }
            else
            {
                if ($originParent && $originParent.length && !$dropdown.parent().is($originParent))
                {
                    $originParent.append($dropdown);
                }

                $dropdown
                .removeClass('lsd-address-autocomplete-popup--floating')
                .css({ left: '', top: '', width: '', right: '', zIndex: '' });
            }
        };

        const bindRepositionEvents = function()
        {
            const $trigger = getTrigger();
            if (!$trigger || !$trigger.length) return;

            $(window)
            .off('resize' + namespace + ' scroll' + namespace, positionDropdown)
            .on('resize' + namespace + ' scroll' + namespace, positionDropdown);

            const $scrollable = $trigger.closest('.lsd-search-included-in-more');
            if ($scrollable.length)
            {
                $scrollable
                .off('scroll' + namespace, positionDropdown)
                .on('scroll' + namespace, positionDropdown);
            }
        };

        const hide = function()
        {
            if (!$dropdown.length) return;

            $dropdown.removeClass('lsd-address-autocomplete-popup--open');
            $dropdown.attr('aria-hidden', 'true');
            $dropdown.hide();
        };

        const show = function(force)
        {
            if (!$dropdown.length) return;

            const $structure = ensureStructure();
            if ($structure && ($structure.children().length || force))
            {
                positionDropdown();
                $dropdown.addClass('lsd-address-autocomplete-popup--open');
                $dropdown.attr('aria-hidden', 'false');
                $dropdown.show();
            }
        };

        const reset = function()
        {
            if (!$dropdown.length) return;

            const $structure = ensureStructure();
            if ($structure) $structure.empty();

            hide();
            setUpdating(false);
        };

        const createEntry = function(label, opts)
        {
            const options = opts || {};
            const isMessage = !!options.message;
            const role = options.role || (isMessage ? 'status' : 'option');
            const $entry = $('<div class="lsd-address-autocomplete-item"></div>');

            $entry.attr('role', role);
            if (isMessage)
            {
                $entry.addClass('lsd-address-autocomplete-item--message');
            }
            else
            {
                $entry.attr('tabindex', '0');
            }

            const iconClass = typeof options.iconClass !== 'undefined' ? options.iconClass : settings.itemIconClass;

            if (iconClass)
            {
                const $icon = $('<span class="lsd-address-autocomplete-item-icon" aria-hidden="true"></span>');
                const $iconInner = $('<i class="lsd-fe-icon"></i>');
                $iconInner.addClass(iconClass);
                $icon.append($iconInner);
                $entry.append($icon);
            }

            const $label = $('<span class="lsd-address-autocomplete-item-label"></span>');
            $label.text(label);
            $entry.append($label);

            return $entry;
        };

        const showLoading = function(text)
        {
            if (!$dropdown.length) return;

            const $structure = ensureStructure();
            if (!$structure) return;

            setUpdating(true);
            $dropdown.addClass('lsd-address-autocomplete-popup--loading');
            $structure.empty();

            const message = text || getLoadingText();
            const $entry = createEntry(message, {
                message: true,
                role: 'status',
                iconClass: settings.loadingIconClass
            });

            $structure.append($entry);
            show(true);
        };

        const populate = function(items)
        {
            if (!$dropdown.length) return;

            const $structure = ensureStructure();
            if (!$structure) return;

            $dropdown.removeClass('lsd-address-autocomplete-popup--loading');
            $structure.empty();

            if (!items || !items.length)
            {
                hide();
                setUpdating(false);
                return;
            }

            const normalized = $.map(items, function(item)
            {
                if (!item) return null;
                const label = item.label || item.value || '';
                if (!label) return null;

                const value = item.value || label;
                const data = $.extend({}, item, {label: label, value: value});
                const $entry = createEntry(label, {iconClass: item.iconClass});

                $entry.data('lsdDropdownItem', data);
                return $entry;
            });

            if (!normalized.length)
            {
                hide();
                setUpdating(false);
                return;
            }

            normalized.forEach(function($entry)
            {
                $structure.append($entry);
            });

            show();
            setUpdating(false);
        };

        const markSelected = function(label, meta)
        {
            if (!$dropdown.length) return;

            const $structure = ensureStructure();
            if (!$structure) return;

            const selection = (label || '').toString().trim();
            $structure.children('.lsd-address-autocomplete-item').each(function()
            {
                const $item = $(this);
                const data = $item.data('lsdDropdownItem') || {};
                const itemLabel = (data.label || data.value || '').toString().trim();

                if (selection && itemLabel === selection)
                {
                    $item.addClass('lsd-address-autocomplete-item--selected');
                    $item.data('lsdDropdownItem', $.extend({}, data, meta || {}));
                }
                else
                {
                    $item.removeClass('lsd-address-autocomplete-item--selected');
                }
            });
        };

        const selectItem = function(item)
        {
            if (!$dropdown.length || !item) return;

            const data = item.data('lsdDropdownItem');
            if (!data) return;

            const $trigger = $dropdown.data('lsdDropdownTrigger');
            if ($trigger && $trigger.length)
            {
                const event = $.Event('lsd-autocomplete-select');
                $dropdown.trigger(event, [data]);

                if (!event.isDefaultPrevented())
                {
                    $trigger.trigger('lsd-autocomplete-select', [data]);
                }
            }
            else
            {
                $dropdown.trigger('lsd-autocomplete-select', [data]);
            }

            hide();
        };

        const isUpdating = function()
        {
            return updating;
        };

        const handleKeyboard = function(event)
        {
            if (!$dropdown.length) return;

            const $structure = ensureStructure();
            if (!$structure || !$structure.children().length) return;

            const $items = $structure.children('.lsd-address-autocomplete-item').filter(function()
            {
                return !$(this).hasClass('lsd-address-autocomplete-item--message');
            });

            if (!$items.length) return;

            const KEY_UP = 38;
            const KEY_DOWN = 40;
            const KEY_ENTER = 13;

            const activeIndex = $items.index($items.filter('.lsd-address-autocomplete-item--active'));

            if (event.which === KEY_DOWN)
            {
                const nextIndex = activeIndex < 0 ? 0 : Math.min(activeIndex + 1, $items.length - 1);
                $items.removeClass('lsd-address-autocomplete-item--active');
                $items.eq(nextIndex).addClass('lsd-address-autocomplete-item--active').focus();
                event.preventDefault();
            }
            else if (event.which === KEY_UP)
            {
                const prevIndex = activeIndex <= 0 ? 0 : activeIndex - 1;
                $items.removeClass('lsd-address-autocomplete-item--active');
                $items.eq(prevIndex).addClass('lsd-address-autocomplete-item--active').focus();
                event.preventDefault();
            }
            else if (event.which === KEY_ENTER && activeIndex >= 0)
            {
                selectItem($items.eq(activeIndex));
                event.preventDefault();
            }
        };

        const bindEvents = function()
        {
            if (!$dropdown.length) return;

            $dropdown
                .off(namespace)
                .on('mousedown' + namespace, '.lsd-address-autocomplete-item', function(event)
                {
                    event.preventDefault();
                })
                .on('click' + namespace, '.lsd-address-autocomplete-item', function(event)
                {
                    event.preventDefault();
                    selectItem($(this));
                })
                .on('mouseenter' + namespace, '.lsd-address-autocomplete-item', function()
                {
                    $(this)
                        .addClass('lsd-address-autocomplete-item--active')
                        .siblings()
                        .removeClass('lsd-address-autocomplete-item--active');
                })
                .on('mouseleave' + namespace, '.lsd-address-autocomplete-item', function()
                {
                    $(this).removeClass('lsd-address-autocomplete-item--active');
                });

            $(document)
                .off('keydown' + namespace)
                .on('keydown' + namespace, function(event)
                {
                    const $trigger = $dropdown.data('lsdDropdownTrigger');
                    if ($trigger && $trigger.length && $(event.target).closest($trigger).length)
                    {
                        handleKeyboard(event);
                    }
                })
                .off('click' + namespace)
                .on('click' + namespace, function(event)
                {
                    const $target = $(event.target);
                    if (!$target.closest($dropdown).length)
                    {
                        hide();
                    }
                });
        };

        bindEvents();
        bindRepositionEvents();

        const api = {
            $element: $dropdown,
            reset: reset,
            populate: populate,
            markSelected: markSelected,
            selectItem: function(item)
            {
                if (!item) return;
                if (item.jquery) selectItem(item);
                else
                {
                    const $structure = ensureStructure();
                    if (!$structure) return;

                    const match = $structure
                        .children('.lsd-address-autocomplete-item')
                        .filter(function()
                        {
                            const data = $(this).data('lsdDropdownItem') || {};
                            const value = data.value || data.label || '';
                            return value === item.value || value === item.label;
                        })
                        .first();

                    if (match.length) selectItem(match);
                }
            },
            hide: hide,
            showLoading: showLoading,
            show: show,
            isUpdating: isUpdating
        };

        $dropdown.data('lsdDropdownInstance', api);
        return api;
    };

    window.lsdGetAddressDropdownController = function(target, options)
    {
        const config = options || {};
        const $target = target instanceof $ ? target : $(target);

        const $storeElement = config.storeElement
            ? (config.storeElement instanceof $ ? config.storeElement : $(config.storeElement))
            : $target;

        const storeKey = config.storeKey || 'lsdAddressDropdownController';
        const existing = $storeElement && $storeElement.length ? $storeElement.data(storeKey) : null;
        if (existing) return existing;

        let $dropdownElement = null;

        if (config.dropdown)
        {
            $dropdownElement = config.dropdown instanceof $ ? config.dropdown : $(config.dropdown);
        }

        if ((!$dropdownElement || !$dropdownElement.length) && config.dropdownSelector)
        {
            $dropdownElement = $target.find(config.dropdownSelector).first();
        }

        if (!$dropdownElement || !$dropdownElement.length)
        {
            if ($target.hasClass('lsd-address-autocomplete-popup'))
            {
                $dropdownElement = $target;
            }
            else if ($target && $target.length)
            {
                $dropdownElement = $target.find('.lsd-address-autocomplete-popup').first();
            }
        }

        if ((!$dropdownElement || !$dropdownElement.length) && config.create)
        {
            $dropdownElement = $('<div class="lsd-address-autocomplete-popup" aria-hidden="true"></div>');

            const $appendTarget = config.createAppendTo
                ? (config.createAppendTo instanceof $ ? config.createAppendTo : $(config.createAppendTo))
                : $target;

            if ($appendTarget && $appendTarget.length)
            {
                $appendTarget.append($dropdownElement);
            }
        }

        let dropdown = null;

        if ($dropdownElement && $dropdownElement.length && typeof window.lsdCreateAddressDropdown === 'function')
        {
            dropdown = window.lsdCreateAddressDropdown($dropdownElement, config.dropdownOptions || {});
        }

        if (!dropdown)
        {
            dropdown = createStub($dropdownElement || $());
        }

        const $trigger = config.trigger
            ? (config.trigger instanceof $ ? config.trigger : $(config.trigger))
            : null;

        if ($trigger && $trigger.length && dropdown.$element && dropdown.$element.length)
        {
            dropdown.$element.data('lsdDropdownTrigger', $trigger);
        }

        if ($storeElement && $storeElement.length)
        {
            $storeElement.data(storeKey, dropdown);
        }

        return dropdown;
    };
})(jQuery);

/**
 * Address Autocomplete Sources
 */
(function($)
{
    'use strict';

    if (typeof window.lsdAddressAutocompleteSources === 'object') return;

    const MIN_LENGTH = 3;
    const DEFAULT_LIMIT = 5;

    const ensureGoogleServices = function(options)
    {
        const config = options || {};
        const services = config.services || {};

        if (typeof google === 'undefined' || typeof google.maps === 'undefined' || typeof google.maps.places === 'undefined')
        {
            return null;
        }

        if (!services.autocomplete && typeof google.maps.places.AutocompleteService === 'function')
        {
            services.autocomplete = new google.maps.places.AutocompleteService();
        }

        if (!services.places && typeof google.maps.places.PlacesService === 'function')
        {
            const element = config.map || config.placesElement || document.createElement('div');
            services.places = new google.maps.places.PlacesService(element);
        }

        services.statusEnum = typeof google.maps.places.PlacesServiceStatus !== 'undefined'
            ? google.maps.places.PlacesServiceStatus
            : {};

        return services;
    };

    const fetchGooglePredictions = function(term, options)
    {
        const deferred = $.Deferred();
        const config = options || {};
        const minLength = typeof config.minLength === 'number' ? config.minLength : MIN_LENGTH;

        const services = ensureGoogleServices(config);
        if (!services || !services.autocomplete || !term || term.length < minLength)
        {
            deferred.resolve({items: [], services: services || null});
            return {promise: deferred.promise(), cancel: function() {}, services: services || null};
        }

        if (typeof google.maps.places.AutocompleteSessionToken === 'function' && !services.sessionToken)
        {
            services.sessionToken = new google.maps.places.AutocompleteSessionToken();
        }

        services.autocomplete.getPlacePredictions({input: term, sessionToken: services.sessionToken}, function(predictions, status)
        {
            const statusEnum = services.statusEnum || {};

            if (status !== statusEnum.OK || !predictions || !predictions.length)
            {
                deferred.resolve({items: [], services: services});
                return;
            }

            const items = $.map(predictions, function(prediction)
            {
                return {
                    label: prediction.description,
                    value: prediction.description,
                    placeId: prediction.place_id
                };
            });

            deferred.resolve({items: items, services: services});
        });

        return {
            promise: deferred.promise(),
            cancel: function() {},
            services: services
        };
    };

    const fetchOpenStreetMap = function(term, options)
    {
        const deferred = $.Deferred();
        const config = options || {};
        const minLength = typeof config.minLength === 'number' ? config.minLength : MIN_LENGTH;

        if (!term || term.length < minLength)
        {
            deferred.resolve({items: []});
            return {promise: deferred.promise(), cancel: function() {}, request: null};
        }

        const endpoint = config.endpoint || 'https://nominatim.openstreetmap.org/search';
        const params = $.extend({format: 'json', limit: config.limit || DEFAULT_LIMIT, q: term}, config.query || {});

        const request = $.getJSON(endpoint, params)
            .done(function(data)
            {
                if (!Array.isArray(data) || !data.length)
                {
                    deferred.resolve({items: []});
                    return;
                }

                const items = $.map(data, function(item)
                {
                    return {
                        label: item.display_name,
                        value: item.display_name,
                        lat: item.lat,
                        lon: item.lon
                    };
                });

                deferred.resolve({items: items});
            })
            .fail(function()
            {
                deferred.resolve({items: []});
            });

        return {
            promise: deferred.promise(),
            cancel: function()
            {
                if (request && typeof request.abort === 'function') request.abort();
            },
            request: request
        };
    };

    const fetchProviderSuggestions = function(provider, term, options)
    {
        const normalized = (provider || '').toLowerCase();
        if (normalized === 'googlemap' || normalized === 'google' || normalized === 'googlemaps')
        {
            return fetchGooglePredictions(term, options || {});
        }

        return fetchOpenStreetMap(term, options || {});
    };

    window.lsdAddressAutocompleteSources = {
        fetch: fetchProviderSuggestions,
        ensureGoogleServices: ensureGoogleServices
    };
})(jQuery);

/**
 * Listing Submission
 */
(function($)
{
    class ListdomModal
    {
        constructor(target, options = {})
        {
            this.$modal = ListdomModal.resolve(target);
            this.options = $.extend({
                appendToBody: false,
                hideClass: undefined,
            }, options);

            if (this.$modal.length)
            {
                this.$modal.data('listdomModalInstance', this);
            }
        }

        static resolve(target)
        {
            if (!target) return $();
            if (target instanceof ListdomModal) return target.$modal;
            if (target.jquery) return target;
            return $(target);
        }

        updateOptions(options = {})
        {
            if ($.isPlainObject(options) && !$.isEmptyObject(options))
            {
                this.options = $.extend({}, this.options, options);
            }

            return this;
        }

        open(options = {})
        {
            this.updateOptions(options);

            const $modal = this.$modal;
            if (!$modal.length) return $();

            const settings = this.options;

            if (settings.appendToBody && !$modal.parent().is('body'))
            {
                $modal.appendTo('body');
            }

            if (typeof settings.hideClass !== 'undefined')
            {
                $modal.data('lsdModalHideClass', settings.hideClass);
            }
            else if (typeof $modal.data('lsdModalHideClass') === 'undefined')
            {
                const attrHideClass = $modal.attr('data-lsd-modal-hide-class');
                if (typeof attrHideClass !== 'undefined') $modal.data('lsdModalHideClass', attrHideClass);
            }

            const hideClass = $modal.data('lsdModalHideClass');
            if (hideClass) $modal.removeClass(hideClass);

            if (!$modal.data('lsdModalActive'))
            {
                $modal.data('lsdModalActive', true);
                ListdomModal.activeCount = (ListdomModal.activeCount || 0) + 1;

                if (typeof ListdomPageScroll !== 'undefined' && ListdomModal.activeCount === 1)
                {
                    ListdomPageScroll.stop();
                }
            }

            $modal.css('display', 'flex').hide().fadeIn(100, function ()
            {
                $(this).trigger('listdom:modal:opened');
            });

            return $modal;
        }

        close(options = {})
        {
            this.updateOptions(options);

            const $modal = this.$modal;
            if (!$modal.length) return $();

            let hideClass = this.options.hideClass;
            if (typeof hideClass !== 'undefined')
            {
                $modal.data('lsdModalHideClass', hideClass);
            }
            else
            {
                hideClass = $modal.data('lsdModalHideClass');
            }

            $modal.fadeOut(100, function ()
            {
                const $element = $(this);

                if ($element.data('lsdModalActive'))
                {
                    $element.removeData('lsdModalActive');

                    if (ListdomModal.activeCount > 0) ListdomModal.activeCount--;

                    if (typeof ListdomPageScroll !== 'undefined' && ListdomModal.activeCount === 0)
                    {
                        ListdomPageScroll.start();
                    }
                }

                if (hideClass) $element.addClass(hideClass);
                $element.trigger('listdom:modal:closed');
            });

            return $modal;
        }

        static get(target, options = {})
        {
            if (target instanceof ListdomModal)
            {
                return target.updateOptions(options);
            }

            const $modal = ListdomModal.resolve(target);
            if (!$modal.length)
            {
                return new ListdomModal(target, options);
            }

            let instance = $modal.data('listdomModalInstance');

            if (!(instance instanceof ListdomModal))
            {
                instance = new ListdomModal($modal, options);
            }
            else
            {
                instance.updateOptions(options);
            }

            return instance;
        }

        static open(target, options = {})
        {
            return ListdomModal.get(target, options).open();
        }

        static close(target, options = {})
        {
            return ListdomModal.get(target, options).close(options);
        }
    }

    window.ListdomModal = ListdomModal;
    ListdomModal.activeCount = 0;

    $(document)
    .off('click.listdomModalClose', '.lsd-modal-close')
    .on('click.listdomModalClose', '.lsd-modal-close', function (e)
    {
        e.preventDefault();
        ListdomModal.close($(this).closest('.lsd-modal'));
    });

    $(document)
    .off('click.listdomModalOverlay', '.lsd-modal')
    .on('click.listdomModalOverlay', '.lsd-modal', function (e)
    {
        if ($(e.target).closest('.lsd-modal-content').length) return;

        const $modal = $(this);
        const overlayPreference = $modal.data('lsdModalOverlayClose');
        if (overlayPreference === false || overlayPreference === 'false') return;

        ListdomModal.close($modal);
    });

    // Document is Ready!
    $(document).ready(function()
    {
        function listdomSwitchForm(buttonsSelector, fadeDuration)
        {
            // Delegate so it works for multiple instances (dropdown tabs, etc.)
            jQuery(document).on('click', buttonsSelector, function (e)
            {
                e.preventDefault();

                var $btn = jQuery(this);

                // Limit switching to the current auth wrapper instance
                var $wrapper = $btn.closest('.lsd-auth-wrapper');
                if (!$wrapper.length) return;

                var targetSelector = $btn.data('target'); // e.g. "#lsd-login-form"
                if (!targetSelector) return;

                // Find target ONLY inside this wrapper (important when IDs are duplicated)
                var $target = $wrapper.find(targetSelector).first();
                if (!$target.length) return;

                var $forms = $wrapper.find('.lsd-auth-form-content');
                var $visible = $forms.filter(':visible');

                // Switch active class only inside this wrapper (buttons + links)
                $wrapper.find('.lsd-auth-switch-button').removeClass('active');
                $btn.addClass('active');

                // If you want "all buttons/links pointing to same target" to be active:
                $wrapper.find('.lsd-auth-switch-button').filter(function () {
                    return jQuery(this).data('target') === targetSelector;
                }).addClass('active');

                // Show/hide with fade
                if (fadeDuration && fadeDuration > 0)
                {
                    if ($visible.length)
                    {
                        $visible.stop(true, true).fadeOut(fadeDuration, function () {
                            $target.stop(true, true).fadeIn(fadeDuration);
                        });
                    }
                    else
                    {
                        $target.stop(true, true).fadeIn(fadeDuration);
                    }
                }
                else
                {
                    $forms.hide();
                    $target.show();
                }
            });

            // Optional: initialize each wrapper (show first form, set first button active)
            jQuery('.lsd-auth-wrapper').each(function () {
                var $w = jQuery(this);
                var $firstForm = $w.find('.lsd-auth-form-content').first();
                var $firstBtn  = $w.find('.lsd-auth-switch-button').first();

                if ($firstForm.length) $firstForm.show();
                if ($firstBtn.length)
                {
                    $w.find('.lsd-auth-switch-button').removeClass('active');
                    $firstBtn.addClass('active');
                }
            });
        }

        function lsdUpdateGalleryPlaceholder(context)
        {
            let $containers;

            if (typeof context !== 'undefined' && context !== null)
            {
                $containers = $(context);

                if ($containers.is('.lsd-listing-gallery-container'))
                {
                    // Already the container
                }
                else
                {
                    $containers = $containers.closest('.lsd-listing-gallery-container');
                }
            }
            else
            {
                $containers = $('.lsd-listing-gallery-container');
            }

            if (!$containers.length) return;

            $containers.each(function()
            {
                const $container = $(this);
                const $list = $container.find('.lsd-listing-gallery');
                const $placeholder = $container.find('.lsd-gallery-placeholder');
                const $removeButton = $container.find('.lsd-remove-gallery-button');
                const $addMoreButton = $container.find('.lsd-gallery-add-more-button');

                if (!$list.length || !$placeholder.length) return;

                const hasItems = $list.find('li').length > 0;

                if (hasItems)
                {
                    $list.removeClass('lsd-util-hide');
                    $placeholder.addClass('lsd-util-hide');
                    $removeButton.removeClass('lsd-util-hide');
                    $addMoreButton.removeClass('lsd-util-hide');
                }
                else
                {
                    $list.addClass('lsd-util-hide');
                    $placeholder.removeClass('lsd-util-hide');
                    $removeButton.addClass('lsd-util-hide');
                    $addMoreButton.addClass('lsd-util-hide');
                }
            });
        }

        window.lsdUpdateGalleryPlaceholder = lsdUpdateGalleryPlaceholder;

        listdomSwitchForm('.lsd-auth-switch-button', 200);
        lsdUpdateGalleryPlaceholder();

        // Check for the 'tab' query parameter
        const urlParams = new URLSearchParams(window.location.search);
        const tab = urlParams.get('tab');
        const action = urlParams.get('action');

        let desired = null;
        if (tab === 'login')
        {
            desired = 'login';
        }
        else if (tab === 'register')
        {
            desired = 'register';
        }
        else if (tab === 'lostpassword' || action === 'rp' || action === 'resetpass')
        {
            desired = 'forgot';
        }

        if (desired)
        {
            const prefixMap = {
                login: '#lsd-login-form-',
                register: '#lsd-register-form-',
                forgot: '#lsd-forgot-password-form-'
            };

            jQuery('.lsd-auth-wrapper').each(function () {
                const $wrapper = jQuery(this);
                const prefix = prefixMap[desired];
                let $btn = $wrapper.find('.lsd-auth-switch-button').filter(function () {
                    const target = jQuery(this).data('target');
                    return target && target.indexOf(prefix) === 0;
                }).first();

                if (!$btn.length)
                {
                    $btn = $wrapper.find('.lsd-auth-switch-button[data-target="#' + prefix.replace('#', '').replace(/-$/, '') + '"]');
                }

                if ($btn.length)
                {
                    $btn.trigger('click').addClass('active');
                }
            });
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
                const $target = $(target);
                const $container = $target.closest('.lsd-listing-gallery-container');
                const ratioLabel = ($container.data('aspectRatio') || '').toString().trim();
                const ratioValue = parseAspectRatio(ratioLabel);
                const aspectMessage =
                    ($container.data('aspectMessage') || '').toString().trim() ||
                    (ratioLabel ? 'Please upload images with an aspect ratio close to ' + ratioLabel + '.' : '');
                let hasRatioMismatch = false;

                attachments.map(function(attachment)
                {
                    attachment = attachment.toJSON();

                    if (ratioValue)
                    {
                        const width = parseInt(attachment.width, 10) || 0;
                        const height = parseInt(attachment.height, 10) || 0;

                        if (width && height && !isAspectRatioAllowed(width, height, ratioValue, aspectRatioTolerance))
                        {
                            hasRatioMismatch = true;
                            return;
                        }
                    }

                    $target.append('<li data-id="'+attachment.id+'"><input type="hidden" name="'+name+'" value="'+attachment.id+'"><img src="'+attachment.url+'" alt=""><div class="lsd-gallery-actions"><i class="lsd-icon fas fa-trash-alt lsd-remove-gallery-single-button"></i> <i class="lsd-icon fas fa-arrows-alt lsd-handler"></i></div></li>');
                });

                lsdUpdateGalleryPlaceholder($target);

                if (hasRatioMismatch)
                {
                    const $message = $('#lsd_listing_gallery_uploader_message');
                    if ($message.length)
                    {
                        if (typeof listdom_alertify === 'function') $message.html(listdom_alertify(aspectMessage, 'lsd-error'));
                        else $message.text(aspectMessage);

                        setTimeout(function()
                        {
                            $message.html('');
                        }, 60000);
                    }
                }
                frame.close();
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
            const $target = $(target);

            $target.html('');

            lsdUpdateGalleryPlaceholder($target);
        });

        /**
         * Listdom Gallery picker -- Single Remove Button
         */
        $(document).on('click', '.lsd-remove-gallery-single-button', function(event)
        {
            event.preventDefault();

            const $item = $(this).closest('li');
            const $container = $item.closest('.lsd-listing-gallery-container');

            $item.remove();

            lsdUpdateGalleryPlaceholder($container);
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
         * Listdom FAQ -- Add Button
         */
        $('.lsd-add-faq-button').on('click', function(event)
        {
            event.preventDefault();

            const template = $(this).data('template');
            const target = $(this).data('for');

            // New Index
            const $index = $('#lsd_listing_faqs_index');
            const index = $index.val();
            const new_index = parseInt(index)+1;

            // Update Index
            $index.val(new_index);

            // Content
            const content = $(template).html().replace(/:i:/g, index);

            $(target).append(content);

            // Trigger Remove Button
            $('.lsd-remove-faq-single-button').off('click').on('click', function(event)
            {
                event.preventDefault();
                $(this).parent().parent().parent().remove();
            });
        });

        /**
         * Listdom FAQ -- Remove All Button
         */
        $('.lsd-remove-faq-button').on('click', function(event)
        {
            event.preventDefault();
            const target = $(this).data('for');

            $(target).html('');

            $(this).toggleClass('lsd-util-hide');
        });

        /**
         * Listdom FAQ -- Single Remove Button
         */
        $('.lsd-remove-faq-single-button').off('click').on('click', function(event)
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
                const $target = $(target);
                const url = attachment.attributes.url;
                const label = attachment.attributes.filename || url;
                const $preview = $(target + '_file');
                const $scope = $target.closest('.lsd-filepicker-wrapper, .lsd-attribute-file');
                const $fieldWrapper = $target.closest('.lsd-attribute-file');
                const $rulesSource = $fieldWrapper.length ? $fieldWrapper : $scope;
                const $message = $fieldWrapper.length ? $fieldWrapper.find('.lsd-attribute-file-message') : $scope.find('.lsd-attribute-file-message');

                if ($message.length) $message.html('');

                const allowedExtensionsRaw = (($rulesSource.data('allowed-extensions') || '') + '').toLowerCase();
                const allowedExtensions = allowedExtensionsRaw
                    ? allowedExtensionsRaw.split(',').map(function (item) {
                        return $.trim((item || '').toLowerCase().replace(/^\./, ''));
                    }).filter(Boolean)
                    : [];

                const extensionFromLabel = ((label || '') + '').split('.').pop().toLowerCase().replace(/[^a-z0-9]/g, '');
                const extensionFromUrl = ((url || '').split('?')[0].split('.').pop() || '').toLowerCase().replace(/[^a-z0-9]/g, '');
                const extension = extensionFromLabel || extensionFromUrl;
                const extensionMessage = $rulesSource.data('extension-message') || 'The selected file has an invalid extension.';

                if (allowedExtensions.length && (!extension || allowedExtensions.indexOf(extension) === -1))
                {
                    if ($message.length)
                    {
                        if (typeof listdom_alertify === 'function') $message.html(listdom_alertify(extensionMessage, 'lsd-error'));
                        else $message.text(extensionMessage);
                    }
                    frame.close();
                    return;
                }

                const maxUploadSize = parseInt($rulesSource.data('max-upload-size'), 10);
                const sizeMessage = $rulesSource.data('size-message') || 'The selected file exceeds the maximum allowed size.';
                let fileSize = 0;

                if (attachment && attachment.attributes)
                {
                    const size = parseInt(attachment.attributes.filesizeInBytes, 10);
                    if (!Number.isNaN(size) && size > 0) fileSize = size;
                }

                if (!fileSize && typeof attachment.get === 'function')
                {
                    const size = parseInt(attachment.get('filesizeInBytes'), 10);
                    if (!Number.isNaN(size) && size > 0) fileSize = size;
                }

                if (!Number.isNaN(maxUploadSize) && maxUploadSize > 0 && fileSize > 0 && fileSize > maxUploadSize * 1024)
                {
                    if ($message.length)
                    {
                        if (typeof listdom_alertify === 'function') $message.html(listdom_alertify(sizeMessage, 'lsd-error'));
                        else $message.text(sizeMessage);
                    }
                    frame.close();
                    return;
                }

                $preview.html(
                    $('<a>', {
                        href: url,
                        target: '_blank',
                        rel: 'noopener'
                    }).text(label)
                );
                $target.val(attachment.id);

                if ($scope.length)
                {
                    $scope.find('.lsd-image-placeholder-preview').removeClass('lsd-util-hide');
                    $scope.find('.lsd-image-placeholder-empty').addClass('lsd-util-hide');
                    $scope.find('.lsd-imagepicker-image-placeholder').addClass('lsd-image-placeholder-has-image');
                    $scope.find('.lsd-filepicker-preview').removeClass('lsd-checkbox-error lsd-attribute-error');
                    $scope.find('.lsd-file-error-msg, .lsd-attribute-error-msg').remove();
                }

                if ($scope.length)
                {
                    $scope.find('.lsd-select-file-button[data-for="' + target + '"]').addClass('lsd-util-hide');
                    $scope.find('.lsd-remove-file-button[data-for="' + target + '"]').removeClass('lsd-util-hide');
                }
                else
                {
                    $('.lsd-select-file-button[data-for="' + target + '"]').addClass('lsd-util-hide');
                    $('.lsd-remove-file-button[data-for="' + target + '"]').removeClass('lsd-util-hide');
                }

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

            const $target = $(target);
            const $scope = $target.closest('.lsd-filepicker-wrapper, .lsd-attribute-file');
            const $fieldWrapper = $target.closest('.lsd-attribute-file');
            if ($scope.length)
            {
                $scope.find('.lsd-image-placeholder-preview').addClass('lsd-util-hide');
                $scope.find('.lsd-image-placeholder-empty').removeClass('lsd-util-hide');
                $scope.find('.lsd-imagepicker-image-placeholder').removeClass('lsd-image-placeholder-has-image');
            }
            if ($fieldWrapper.length) $fieldWrapper.find('.lsd-attribute-file-message').html('');

            if ($scope.length)
            {
                $scope.find('.lsd-select-file-button[data-for="' + target + '"]').removeClass('lsd-util-hide');
                $scope.find('.lsd-remove-file-button[data-for="' + target + '"]').addClass('lsd-util-hide');
            }
            else
            {
                $('.lsd-select-file-button[data-for="' + target + '"]').removeClass('lsd-util-hide');
                $('.lsd-remove-file-button[data-for="' + target + '"]').addClass('lsd-util-hide');
            }
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
                const $target = $(event.target);

                // Check if click is inside popup
                const insidePopup = $popup.is($target) || $popup.has($target).length > 0;

                // Check if Select2 inside popup is open
                const hasOpenSelect2 = $popup.find('.select2-hidden-accessible').toArray().some(el => {
                    const select2 = $(el).data('select2');
                    return select2 && select2.isOpen();
                });

                // If click is outside popup and no Select2 is open, close popup
                if (!insidePopup && !hasOpenSelect2) {
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
    $('.lsd-select-image-files-button, .lsd-add-images-button').on('click', function (event)
    {
        event.preventDefault();

        const target = $(this).data('for');
        const $input = $(target);

        if (!$input.length) return;

        if ($input.prop('multiple'))
        {
            const existingFiles = $input.prop('files') || [];
            if (existingFiles.length)
            {
                $input.data('lsdExistingFiles', Array.from(existingFiles));
            }
            else
            {
                $input.removeData('lsdExistingFiles');
            }
        }

        $input.trigger('click');
    });

    function updateMultiImagepicker($input)
    {
        const files = $input.prop('files') || [];
        const $wrapper = $input.closest('.lsd-imagepicker-wrapper');
        const $placeholder = $wrapper.find('.lsd-imagepicker-image-placeholder');
        const $preview = $placeholder.find('.lsd-imagepicker-multiple-preview');
        const $list = $preview.find('.lsd-imagepicker-multiple-list');
        const $emptyState = $placeholder.find('.lsd-image-placeholder-empty');
        const target = '#' + $input.attr('id');
        const $removeButton = $wrapper.find('.lsd-remove-images-button[data-for="' + target + '"]');
        const $addButton = $wrapper.find('.lsd-add-images-button[data-for="' + target + '"]');
        const isMultiple = $input.prop('multiple');

        $list.html('');

        if (!files.length)
        {
            $preview.addClass('lsd-util-hide');
            $emptyState.removeClass('lsd-util-hide');
            $placeholder.removeClass('lsd-image-placeholder-has-image');
            $removeButton.addClass('lsd-util-hide');
            $addButton.addClass('lsd-util-hide');
            return;
        }

        $.each(files, function (index, file)
        {
            const $item = $('<li class="lsd-imagepicker-multiple-item"></li>');

            if (file && file.type && file.type.indexOf('image') === 0 && typeof FileReader !== 'undefined')
            {
                const reader = new FileReader();
                const $img = $('<img alt="">');
                $img.attr('alt', file.name || '');
                reader.onload = function (event)
                {
                    $img.attr('src', event.target.result || '');
                };
                reader.readAsDataURL(file);
                $item.append($img);
            }
            else if (file && file.name)
            {
                $item.append($('<span class="lsd-imagepicker-multiple-filename"></span>').text(file.name));
            }

            $list.append($item);
        });

        $preview.removeClass('lsd-util-hide');
        $emptyState.addClass('lsd-util-hide');
        $placeholder.addClass('lsd-image-placeholder-has-image');
        $removeButton.removeClass('lsd-util-hide');
        if (isMultiple) $addButton.removeClass('lsd-util-hide');
        else $addButton.addClass('lsd-util-hide');
    }

    const aspectRatioTolerance = 0.05;

    function parseAspectRatio(value)
    {
        if (!value || typeof value !== 'string') return null;

        const trimmed = value.trim();
        if (!trimmed || trimmed === 'none') return null;

        const parts = trimmed.split(':');
        if (parts.length !== 2) return null;

        const width = parseFloat(parts[0]);
        const height = parseFloat(parts[1]);

        if (!width || !height || Number.isNaN(width) || Number.isNaN(height)) return null;

        return width / height;
    }

    function isAspectRatioAllowed(width, height, ratio, tolerance)
    {
        if (!ratio || !width || !height) return true;
        const actual = width / height;
        return Math.abs(actual - ratio) / ratio <= tolerance;
    }

    $('.lsd-imagepicker-file-input').on('change', function ()
    {
        const $input = $(this);
        const existingFiles = $input.data('lsdExistingFiles');
        const newFiles = $input.prop('files') || [];

        if (existingFiles && existingFiles.length && $input.prop('multiple') && typeof DataTransfer !== 'undefined')
        {
            const dataTransfer = new DataTransfer();
            existingFiles.forEach((file) => dataTransfer.items.add(file));
            Array.from(newFiles).forEach((file) => dataTransfer.items.add(file));
            $input[0].files = dataTransfer.files;
        }

        $input.removeData('lsdExistingFiles');
        updateMultiImagepicker($input);
    });

    $('.lsd-remove-images-button').on('click', function (event)
    {
        event.preventDefault();

        const target = $(this).data('for');
        const $input = $(target);
        if (!$input.length) return;

        $input.val('');
        $input.removeData('lsdExistingFiles');
        updateMultiImagepicker($input);
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
            if (!attachment || !attachment.attributes) {
                frame.close();
                return;
            }

            const target = $button.data('for');
            const $input = $(target);
            const $wrapper = $button.closest('.lsd-attribute-image');
            const $placeholder = $(target + '_img');
            let $message = $wrapper.find('.lsd-attribute-image-message');

            if (!$message.length) {
                $message = $('<div class="lsd-attribute-image-message"></div>');
                $wrapper.append($message);
            }

            $message.html('');
            $wrapper.removeClass('lsd-attribute-error');
            $placeholder.removeClass('lsd-attribute-error');

            const maxUploadSize = parseInt($wrapper.data('maxUploadSize'), 10);
            let fileSize = 0;

            if (typeof attachment.get === 'function') {
                const bytes = parseInt(attachment.get('filesizeInBytes'), 10);
                if (!Number.isNaN(bytes) && bytes) fileSize = bytes;
            }

            if (!fileSize && attachment.attributes) {
                const bytes = parseInt(attachment.attributes.filesizeInBytes, 10);
                if (!Number.isNaN(bytes) && bytes) fileSize = bytes;
            }

            if (!fileSize && typeof attachment.get === 'function') {
                const readable = attachment.get('filesizeHumanReadable') || '';
                const match = readable.match(/([\d.,]+)\s*(KB|MB|GB)/i);
                if (match) {
                    const value = parseFloat(match[1].replace(',', '.'));
                    const unit = match[2].toUpperCase();
                    const multipliers = { KB: 1024, MB: 1024 * 1024, GB: 1024 * 1024 * 1024 };
                    if (!Number.isNaN(value) && multipliers[unit]) fileSize = value * multipliers[unit];
                }
            }

            if (!Number.isNaN(maxUploadSize) && maxUploadSize > 0 && fileSize && fileSize > maxUploadSize * 1024)
            {
                const sizeMessage = $wrapper.data('sizeMessage') || '';
                const messageContent = sizeMessage || 'The selected image exceeds the maximum allowed size.';

                if (typeof listdom_alertify === 'function') $message.html(listdom_alertify(messageContent, 'lsd-error'));
                else $message.text(messageContent);

                $wrapper.addClass('lsd-attribute-error');
                $placeholder.addClass('lsd-attribute-error');

                setTimeout(() => {
                    $message.html('');
                }, 9000);

                frame.close();
                return;
            }

            const ratioLabel = ($wrapper.data('aspectRatio') || '').toString().trim();
            const ratioValue = parseAspectRatio(ratioLabel);
            const aspectMessage =
                ($wrapper.data('aspectMessage') || '').toString().trim() ||
                (ratioLabel ? 'Please upload an image with an aspect ratio close to ' + ratioLabel + '.' : '');

            if (ratioValue)
            {
                let width = 0;
                let height = 0;

                if (typeof attachment.get === 'function')
                {
                    width = parseInt(attachment.get('width'), 10) || 0;
                    height = parseInt(attachment.get('height'), 10) || 0;
                }

                if ((!width || !height) && attachment.attributes)
                {
                    width = parseInt(attachment.attributes.width, 10) || 0;
                    height = parseInt(attachment.attributes.height, 10) || 0;
                }

                if (width && height && !isAspectRatioAllowed(width, height, ratioValue, aspectRatioTolerance))
                {
                    const messageContent = aspectMessage || 'The selected image does not match the required aspect ratio.';

                    if (typeof listdom_alertify === 'function') $message.html(listdom_alertify(messageContent, 'lsd-error'));
                    else $message.text(messageContent);

                    $wrapper.addClass('lsd-attribute-error');
                    $placeholder.addClass('lsd-attribute-error');

                    setTimeout(() => {
                        $message.html('');
                    }, 9000);

                    frame.close();
                    return;
                }
            }

            const $preview = $placeholder.find('.lsd-image-placeholder-preview');
            const $emptyState = $placeholder.find('.lsd-image-placeholder-empty');
            const $removeButton = $('.lsd-remove-image-button[data-for="' + target + '"]');

            $preview.html('<img alt="" src="' + attachment.attributes.url + '">').removeClass('lsd-util-hide');
            $placeholder.addClass('lsd-image-placeholder-has-image');
            $emptyState.addClass('lsd-util-hide');

            $input.val(attachment.id);

            $button.addClass('lsd-util-hide');
            $removeButton.removeClass('lsd-util-hide');

            setTimeout(() => {
                $message.html('');
            }, 9000);

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

        const $placeholder = $(target + '_img');
        const $wrapper = $button.closest('.lsd-attribute-image');
        const $selectButton = $placeholder.find('.lsd-select-image-button');
        const $emptyState = $placeholder.find('.lsd-image-placeholder-empty');
        const $preview = $placeholder.find('.lsd-image-placeholder-preview');

        $preview.html('').addClass('lsd-util-hide');
        $placeholder.removeClass('lsd-image-placeholder-has-image');
        $emptyState.removeClass('lsd-util-hide');
        $(target).val('');

        if ($wrapper.length)
        {
            $wrapper.removeClass('lsd-attribute-error');
            $wrapper.find('.lsd-attribute-image-message').html('');
        }

        $selectButton.removeClass('lsd-util-hide');
        $button.addClass('lsd-util-hide');
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

window.ListdomPageScroll = ListdomPageScroll;

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

/**
 * Locate Control Binding
 */
(function($)
{
    'use strict';

    if (typeof window.lsdBindLocateControl === 'function') return;

    let locateNamespaceCounter = 0;

    const defaultShowError = function(message)
    {
        if (!message) return;

        if (typeof window.listdom_toastify === 'function')
        {
            window.listdom_toastify(message, 'lsd-error');
            return;
        }

        if (typeof window.ListdomToast === 'function')
        {
            new window.ListdomToast(message, {type: 'lsd-error'});
            return;
        }

        window.alert(message);
    };

    const mapErrorType = function(error)
    {
        if (!error || typeof error.code === 'undefined') return 'failed';

        const code = error.code;
        const deniedCodes = [1];
        const unavailableCodes = [2];
        const timeoutCodes = [3];

        if (typeof error.PERMISSION_DENIED !== 'undefined') deniedCodes.push(error.PERMISSION_DENIED);
        if (typeof error.POSITION_UNAVAILABLE !== 'undefined') unavailableCodes.push(error.POSITION_UNAVAILABLE);
        if (typeof error.TIMEOUT !== 'undefined') timeoutCodes.push(error.TIMEOUT);

        if (deniedCodes.indexOf(code) !== -1) return 'denied';
        if (unavailableCodes.indexOf(code) !== -1) return 'failed';
        if (timeoutCodes.indexOf(code) !== -1) return 'failed';

        return 'failed';
    };

    window.lsdBindLocateControl = function(element, options)
    {
        const $button = element instanceof $ ? element : $(element);
        if (!$button || !$button.length) return null;

        const previous = $button.data('lsdLocateControl');
        if (previous && typeof previous.destroy === 'function') previous.destroy();

        const defaults = {
            messages: {
                unsupported: $button.data('error-unsupported') || '',
                denied: $button.data('error-denied') || '',
                failed: $button.data('error-failed') || ''
            },
            keydown: true,
            expectsFinalize: false,
            geolocationOptions: {},
            loadingClass: 'lsd-is-loading'
        };

        const settings = $.extend(true, {}, defaults, options || {});

        const showError = function(type)
        {
            const message = settings.messages && settings.messages[type] ? settings.messages[type] : '';
            if (typeof settings.showError === 'function') settings.showError(message, type);
            else defaultShowError(message);
        };

        const startLoading = function()
        {
            if (typeof settings.onRequestStart === 'function') settings.onRequestStart();

            if (typeof settings.setLoading === 'function') settings.setLoading(true);
            else if (settings.loadingClass) $button.addClass(settings.loadingClass);
        };

        const stopLoading = function()
        {
            if (typeof settings.setLoading === 'function') settings.setLoading(false);
            else if (settings.loadingClass) $button.removeClass(settings.loadingClass);

            if (typeof settings.onRequestComplete === 'function') settings.onRequestComplete();
        };

        const finalize = function()
        {
            stopLoading();
            if (typeof settings.onComplete === 'function') settings.onComplete();
        };

        const namespace = '.lsdLocateControl-' + (++locateNamespaceCounter);

        const requestGeolocation = function()
        {
            if (!navigator.geolocation)
            {
                if (typeof settings.onUnsupported === 'function') settings.onUnsupported();
                showError('unsupported');
                return;
            }

            startLoading();

            navigator.geolocation.getCurrentPosition(
                function(position)
                {
                    let finalizeCalled = false;
                    const done = function()
                    {
                        if (finalizeCalled) return;
                        finalizeCalled = true;
                        finalize();
                    };

                    if (typeof settings.onSuccess !== 'function')
                    {
                        done();
                        return;
                    }

                    let result;

                    try
                    {
                        result = settings.onSuccess(position.coords.latitude, position.coords.longitude, done);
                    }
                    catch (err)
                    {
                        if (typeof settings.onError === 'function') settings.onError(err, 'failed');
                        showError('failed');
                        finalize();
                        return;
                    }

                    if (!settings.expectsFinalize)
                    {
                        if (result && typeof result.then === 'function')
                        {
                            result.then(done).catch(function()
                            {
                                if (typeof settings.onError === 'function') settings.onError(null, 'failed');
                                showError('failed');
                                finalize();
                            });
                        }
                        else done();
                    }
                    else if (result && typeof result.then === 'function')
                    {
                        result.then(done).catch(function()
                        {
                            if (typeof settings.onError === 'function') settings.onError(null, 'failed');
                            showError('failed');
                            finalize();
                        });
                    }
                },
                function(error)
                {
                    if (typeof settings.onError === 'function') settings.onError(error, mapErrorType(error));
                    showError(mapErrorType(error));
                    finalize();
                },
                settings.geolocationOptions || {}
            );
        };

        const handlePermission = function()
        {
            if (!navigator.permissions || typeof navigator.permissions.query !== 'function')
            {
                requestGeolocation();
                return;
            }

            navigator.permissions
                .query({name: 'geolocation'})
                .then(function(status)
                {
                    if (status.state === 'denied')
                    {
                        if (typeof settings.onDenied === 'function') settings.onDenied(status);
                        showError('denied');
                        return;
                    }

                    requestGeolocation();
                })
                .catch(function()
                {
                    requestGeolocation();
                });
        };

        const clickHandler = function(event)
        {
            if (event) event.preventDefault();
            handlePermission();
        };

        const keydownHandler = function(event)
        {
            if (!event) return;
            if (event.key !== 'Enter' && event.key !== ' ') return;

            event.preventDefault();
            handlePermission();
        };

        $button.off(namespace).on('click' + namespace, clickHandler);
        if (settings.keydown) $button.on('keydown' + namespace, keydownHandler);

        const instance = {
            destroy: function()
            {
                $button.off(namespace);
                $button.removeData('lsdLocateControl');
            },
            trigger: function()
            {
                handlePermission();
            },
            element: $button
        };

        $button.data('lsdLocateControl', instance);
        return instance;
    };

    if (typeof $.fn.select2 === 'function') $(".lsd-select-multiple").select2();
})(jQuery);

(function ($) {
    if (window.lsdAuthDropdownInitialized) return;
    window.lsdAuthDropdownInitialized = true;

    const closeAll = function (except) {
        $(".lsd-auth-dropdown").each(function () {
            const dropdown = $(this);
            if (except && dropdown.is(except)) return;

            dropdown.removeClass("lsd-open");
            dropdown.find(".lsd-auth-dropdown-panel").attr("aria-hidden", "true");
            dropdown.find(".lsd-auth-dropdown-toggle").attr("aria-expanded", "false");
        });
    };

    $(document).on("click", ".lsd-auth-dropdown-toggle", function (e) {
        e.preventDefault();

        const dropdown = $(this).closest(".lsd-auth-dropdown");

        if (dropdown.hasClass("lsd-open")) {
            closeAll();
            return;
        }

        closeAll(dropdown);
        dropdown.addClass("lsd-open");
        dropdown.find(".lsd-auth-dropdown-panel").attr("aria-hidden", "false");
        dropdown.find(".lsd-auth-dropdown-toggle").attr("aria-expanded", "true");
    });

    $(document).on("click", function (e) {
        if (!$(e.target).closest(".lsd-auth-dropdown").length) closeAll();
    });

    $(document).on("keydown", function (e) {
        if (e.key === "Escape") closeAll();
    });

    $(document).on("click", ".lsd-auth-dropdown-role-tab", function (e) {
        e.preventDefault();

        const tab = $(this);
        const dropdown = tab.closest(".lsd-auth-dropdown");
        const target = tab.data("target");
        if (!target) return;

        dropdown
        .find(".lsd-auth-dropdown-role-tab")
        .removeClass("lsd-active")
        .attr("aria-selected", "false");

        tab.addClass("lsd-active").attr("aria-selected", "true");

        dropdown
        .find(".lsd-auth-dropdown-role-panel")
        .removeClass("lsd-active")
        .attr("hidden", "hidden");

        dropdown.find(target).addClass("lsd-active").removeAttr("hidden");
    });

    $(document).ready(function () {
        $(".lsd-auth-dropdown").each(function () {
            const dropdown = $(this);
            const hoverEnabled = parseInt(dropdown.data("hover"), 10) === 1;
            if (!hoverEnabled) return;

            dropdown.on("mouseenter", function () {
                closeAll(dropdown);
                dropdown.addClass("lsd-open");
                dropdown.find(".lsd-auth-dropdown-panel").attr("aria-hidden", "false");
                dropdown.find(".lsd-auth-dropdown-toggle").attr("aria-expanded", "true");
            });

            dropdown.on("mouseleave", function () {
                closeAll();
            });
        });
    });
})(jQuery);
