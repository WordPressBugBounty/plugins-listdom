(function(wp)
{
    if (!wp || !wp.hooks || !wp.element || !wp.components) return;

    var editor = wp.blockEditor || wp.editor;
    if (!editor) return;

    var createElement = wp.element.createElement;
    var components = wp.components;
    var packageRender = wp.serverSideRender;
    var ServerSideRender = packageRender && packageRender.default ? packageRender.default : packageRender || components.ServerSideRender;
    var searchesRequest;

    function loadSearches()
    {
        if (!searchesRequest)
        {
            searchesRequest = wp.apiFetch({ path: '/listdom/v1/block-searches' }).then(function(items)
            {
                return Array.isArray(items) ? { items: items, error: false } : { items: [], error: true };
            }, function()
            {
                return { items: [], error: true };
            });
        }

        return searchesRequest;
    }

    function applicationEdit(block)
    {
        return function(props)
        {
            var settings = block.editor;
            var attributes = props.attributes;
            var searchState = wp.element.useState({ items: [], loaded: false, error: false });
            var searchData = searchState[0];
            var setSearchData = searchState[1];
            var previewRef = wp.element.useRef(null);

            wp.element.useEffect(function()
            {
                if (settings.kind !== 'search') return;

                var active = true;
                loadSearches().then(function(result)
                {
                    if (active) setSearchData({ items: result.items, loaded: true, error: result.error });
                });

                return function() { active = false; };
            }, []);

            wp.element.useEffect(function()
            {
                if (settings.kind !== 'search' || !previewRef.current) return;

                var root = previewRef.current;
                var previewWindow = root.ownerDocument.defaultView || window;
                var $ = previewWindow.jQuery && previewWindow.jQuery.fn.select2 ? previewWindow.jQuery : window.jQuery;
                if (!$ || !$.fn.select2) return;
                var sliderLibrary = previewWindow.noUiSlider || window.noUiSlider;
                var frame = 0;
                var active = true;
                var enhance = function()
                {
                    frame = 0;
                    if (!active) return;

                    $(root).find('.lsd-search select[data-enhanced="1"]').each(function()
                    {
                        var select = $(this);
                        if (select.data('select2')) return;

                        select.select2({
                            allowClear: true,
                            placeholder: select.attr('placeholder'),
                            minimumResultsForSearch: 0,
                            dropdownParent: $(root)
                        });
                    });

                    if (sliderLibrary) $(root).find('.lsd-range-slider-search').each(function()
                    {
                        if (this.noUiSlider) return;

                        var slider = this;
                        var min = parseFloat(slider.getAttribute('data-min'));
                        var max = parseFloat(slider.getAttribute('data-max'));
                        var step = parseFloat(slider.getAttribute('data-step'));
                        var initialMin = parseFloat(slider.getAttribute('data-default'));
                        var initialMax = parseFloat(slider.getAttribute('data-max-default'));
                        if (!isFinite(min) || !isFinite(max) || max <= min) return;

                        sliderLibrary.create(slider, {
                            start: [isFinite(initialMin) ? initialMin : min, isFinite(initialMax) ? initialMax : max],
                            connect: true,
                            step: isFinite(step) && step > 0 ? step : 1,
                            range: { min: min, max: max },
                            format: {
                                to: function(value) { return parseFloat(value).toFixed(0); },
                                from: function(value) { return parseFloat(value); }
                            }
                        });
                        slider.noUiSlider.on('update', function(values)
                        {
                            $(slider).parent().find('.lsd-range-min').text(values[0]);
                            $(slider).parent().find('.lsd-range-max').text(values[1]);
                        });
                    });
                };
                var observer = new MutationObserver(function()
                {
                    if (!frame) frame = window.requestAnimationFrame(enhance);
                });
                observer.observe(root, { childList: true, subtree: true });
                enhance();

                return function()
                {
                    active = false;
                    observer.disconnect();
                    if (frame) window.cancelAnimationFrame(frame);
                    $(root).find('.lsd-search select[data-enhanced="1"]').each(function()
                    {
                        if ($(this).data('select2')) $(this).select2('destroy');
                    });
                    $(root).find('.lsd-range-slider-search').each(function()
                    {
                        if (this.noUiSlider) this.noUiSlider.destroy();
                    });
                };
            }, [settings.kind, attributes.searchId]);

            var controls = [];
            var missing = false;

            if (settings.kind === 'search')
            {
                var id = Number(attributes.searchId) || 0;
                var searches = searchData.items;
                var options = [{ label: settings.searchPrompt, value: '0' }];

                searches.forEach(function(search)
                {
                    options.push({ label: search.title || '#' + search.id, value: String(search.id) });
                });
                missing = searchData.loaded && !searchData.error && id && !searches.some(function(search) { return Number(search.id) === id; });
                if (missing) options.push({ label: settings.missing, value: String(id) });
                if (id && (!searchData.loaded || searchData.error)) options.push({ label: '#' + id, value: String(id) });

                controls.push(createElement(components.SelectControl, {
                    key: 'searchId', label: settings.searchLabel, value: String(id), options: options,
                    disabled: !searchData.loaded || searchData.error,
                    onChange: function(value) { props.setAttributes({ searchId: parseInt(value, 10) || 0 }); }
                }));
            }
            else
            {
                settings.controls.forEach(function(control)
                {
                    var update = function(value)
                    {
                        var changed = {};
                        changed[control.key] = value;
                        props.setAttributes(changed);
                    };

                    if (control.type === 'select')
                    {
                        controls.push(createElement(components.SelectControl, {
                            key: control.key, label: control.label, value: attributes[control.key],
                            options: control.options, onChange: function(value)
                            {
                                if (block.attributes[control.key] && block.attributes[control.key].type === 'integer') value = parseInt(value, 10) || 0;
                                update(value);
                            }
                        }));
                    }
                    else if (control.type === 'toggle')
                    {
                        controls.push(createElement(components.ToggleControl, {
                            key: control.key, label: control.label, checked: attributes[control.key] !== false,
                            onChange: update
                        }));
                    }
                    else
                    {
                        controls.push(createElement(components.TextControl, {
                            key: control.key, label: control.label,
                            type: control.type === 'text' ? 'text' : 'number',
                            min: control.min,
                            max: control.max,
                            step: control.type === 'text' ? undefined : (block.attributes[control.key] && block.attributes[control.key].type === 'integer' ? 1 : 'any'),
                            value: attributes[control.key] === undefined ? '' : attributes[control.key],
                            onChange: function(value)
                            {
                                if (control.type === 'integer' || control.type === 'number')
                                {
                                    value = value === '' || !Number.isFinite(Number(value)) ? undefined : Number(value);
                                    if (value !== undefined)
                                    {
                                        if (block.attributes[control.key] && block.attributes[control.key].type === 'integer') value = Math.trunc(value);
                                        if (control.min !== undefined) value = Math.max(control.min, value);
                                        if (control.max !== undefined) value = Math.min(control.max, value);
                                    }
                                }
                                update(value);
                            }
                        }));
                    }
                });
            }

            var inspector = controls.length ? createElement(editor.InspectorControls, null,
                createElement(components.PanelBody, { title: settings.panel }, controls)
            ) : null;

            var preview;
            if (settings.kind === 'search' && searchData.error && !Number(attributes.searchId))
            {
                preview = createElement(components.Placeholder, { icon: block.icon, label: block.title }, settings.searchUnavailable);
            }
            else if (settings.kind === 'search' && !searchData.loaded && !Number(attributes.searchId))
            {
                preview = createElement(components.Placeholder, { icon: block.icon, label: block.title }, settings.searchLoading);
            }
            else if (settings.kind === 'search' && (!Number(attributes.searchId) || missing))
            {
                preview = createElement(components.Placeholder, { icon: block.icon, label: block.title },
                    missing ? settings.missing : settings.message
                );
            }
            else if (settings.kind !== 'search')
            {
                preview = createElement(components.Placeholder, { icon: block.icon, label: block.title }, settings.message);
            }
            else if (!ServerSideRender)
            {
                preview = createElement(components.Placeholder, { icon: block.icon, label: block.title }, settings.message);
            }
            else
            {
                preview = createElement(ServerSideRender, {
                    block: block.name,
                    attributes: attributes,
                    urlQueryArgs: { lsd_editor_preview: 1 },
                    EmptyResponsePlaceholder: function()
                    {
                        return createElement(components.Placeholder, { icon: block.icon, label: block.title }, settings.message);
                    }
                });
            }

            var wrapper = editor.useBlockProps ? editor.useBlockProps({ ref: previewRef }) : { ref: previewRef };
            wrapper.onClickCapture = function(event)
            {
                if (!event.target.closest('a, button')) return;
                event.preventDefault();
                event.stopPropagation();
            };
            wrapper.onSubmitCapture = function(event)
            {
                event.preventDefault();
                event.stopPropagation();
            };

            return createElement(wp.element.Fragment, null, inspector, createElement('div', wrapper, preview));
        };
    }

    wp.hooks.addFilter('lsd.blockEditor.edit', 'listdom/application', function(edit, block)
    {
        return block.editor.type === 'application' ? applicationEdit(block) : edit;
    });
}(window.wp));
