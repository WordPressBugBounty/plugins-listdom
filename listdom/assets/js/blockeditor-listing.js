(function(wp)
{
    if (!wp || !wp.hooks || !wp.element || !wp.components) return;

    var editor = wp.blockEditor || wp.editor;
    if (!editor) return;

    var createElement = wp.element.createElement;
    var components = wp.components;
    var packageRender = wp.serverSideRender;
    var ServerSideRender = packageRender && packageRender.default ? packageRender.default : packageRender || components.ServerSideRender;

    function listingEdit(block)
    {
        return function(props)
        {
            var controls = [];
            var settings = block.editor;
            var attributes = props.attributes;
            var wrapper = editor.useBlockProps ? editor.useBlockProps() : {};

            if (settings.kind === 'title')
            {
                controls.push(createElement(components.SelectControl, {
                    key: 'titleTag', label: settings.titleTag, value: attributes.titleTag,
                    options: settings.titleTags,
                    onChange: function(value) { props.setAttributes({ titleTag: value }); }
                }));
                controls.push(createElement(components.SelectControl, {
                    key: 'linkMethod', label: settings.linkMethod, value: attributes.linkMethod,
                    options: settings.linkMethods,
                    onChange: function(value) { props.setAttributes({ linkMethod: value }); }
                }));
            }
            else if (settings.kind === 'address')
            {
                controls.push(createElement(components.ToggleControl, {
                    key: 'showIcon', label: settings.showIcon, checked: attributes.showIcon !== false,
                    onChange: function(value) { props.setAttributes({ showIcon: value }); }
                }));
            }
            else if (settings.kind === 'excerpt')
            {
                controls.push(createElement(components.TextControl, {
                    key: 'words', label: settings.words, type: 'number', min: 1,
                    value: attributes.words,
                    onChange: function(value) { props.setAttributes({ words: Math.max(1, parseInt(value, 10) || 1) }); }
                }));
            }

            else if (settings.kind === 'image')
            {
                controls.push(createElement(components.SelectControl, {
                    key: 'imageSize', label: settings.imageSize, value: attributes.imageSize,
                    options: settings.imageSizes,
                    onChange: function(value) { props.setAttributes({ imageSize: value }); }
                }));
            }
            else if (settings.kind === 'gallery')
            {
                controls.push(createElement(components.SelectControl, {
                    key: 'galleryStyle', label: settings.galleryStyle, value: attributes.galleryStyle,
                    options: settings.galleryStyles,
                    onChange: function(value) { props.setAttributes({ galleryStyle: value }); }
                }));
                controls.push(createElement(components.ToggleControl, {
                    key: 'lightbox', label: settings.lightbox, checked: attributes.lightbox !== false,
                    onChange: function(value) { props.setAttributes({ lightbox: value }); }
                }));
                controls.push(createElement(components.ToggleControl, {
                    key: 'includeThumbnail', label: settings.includeThumbnail, checked: attributes.includeThumbnail === true,
                    onChange: function(value) { props.setAttributes({ includeThumbnail: value }); }
                }));
                if (attributes.galleryStyle === 'linear')
                {
                    controls.push(createElement(components.TextControl, {
                        key: 'imageLimit', label: settings.imageLimit, type: 'number', min: 1,
                        value: attributes.imageLimit,
                        onChange: function(value) { props.setAttributes({ imageLimit: Math.max(1, parseInt(value, 10) || 1) }); }
                    }));
                }
                if (attributes.galleryStyle === 'slider')
                {
                    controls.push(createElement(components.ToggleControl, {
                        key: 'autoplay', label: settings.autoplay, checked: attributes.autoplay !== false,
                        onChange: function(value) { props.setAttributes({ autoplay: value }); }
                    }));
                    controls.push(createElement(components.ToggleControl, {
                        key: 'autoHeight', label: settings.autoHeight, checked: attributes.autoHeight !== false,
                        onChange: function(value) { props.setAttributes({ autoHeight: value }); }
                    }));
                    controls.push(createElement(components.ToggleControl, {
                        key: 'loop', label: settings.loop, checked: attributes.loop === true,
                        onChange: function(value) { props.setAttributes({ loop: value }); }
                    }));
                    controls.push(createElement(components.SelectControl, {
                        key: 'thumbnailStatus', label: settings.thumbnailStatus, value: attributes.thumbnailStatus,
                        options: settings.thumbnailStatuses,
                        onChange: function(value) { props.setAttributes({ thumbnailStatus: value }); }
                    }));
                    controls.push(createElement(components.SelectControl, {
                        key: 'navigationMethod', label: settings.navigationMethod, value: attributes.navigationMethod,
                        options: settings.navigationMethods,
                        onChange: function(value) { props.setAttributes({ navigationMethod: value }); }
                    }));
                    if (attributes.lightbox === false)
                    {
                        controls.push(createElement(components.SelectControl, {
                            key: 'linkMethod', label: settings.linkMethod, value: attributes.linkMethod,
                            options: settings.linkMethods,
                            onChange: function(value) { props.setAttributes({ linkMethod: value }); }
                        }));
                    }
                }
            }
            else if (settings.kind === 'content')
            {
                controls.push(createElement(components.TextControl, {
                    key: 'contentLength', label: settings.contentLength, type: 'number', min: 0,
                    value: attributes.contentLength,
                    onChange: function(value) { props.setAttributes({ contentLength: Math.max(0, parseInt(value, 10) || 0) }); }
                }));
                controls.push(createElement(components.ToggleControl, {
                    key: 'autoParagraph', label: settings.autoParagraph, checked: attributes.autoParagraph === true,
                    onChange: function(value) { props.setAttributes({ autoParagraph: value }); }
                }));
            }
            else if (settings.kind === 'faqs')
            {
                controls.push(createElement(components.TextControl, {
                    key: 'faqLimit', label: settings.faqLimit, type: 'number', min: 0,
                    value: attributes.faqLimit,
                    onChange: function(value) { props.setAttributes({ faqLimit: Math.max(0, parseInt(value, 10) || 0) }); }
                }));
            }
            else if (settings.controls)
            {
                settings.controls.forEach(function(control)
                {
                    if (control.when && attributes[control.when.key] !== control.when.value) return;

                    var update = function(value)
                    {
                        var changed = {};
                        changed[control.key] = value;
                        props.setAttributes(changed);
                    };

                    if (control.type === 'toggle')
                    {
                        controls.push(createElement(components.ToggleControl, {
                            key: control.key, label: control.label, checked: attributes[control.key] === true,
                            onChange: update
                        }));
                    }
                    else if (control.type === 'select')
                    {
                        controls.push(createElement(components.SelectControl, {
                            key: control.key, label: control.label, value: attributes[control.key],
                            options: control.options, onChange: update
                        }));
                    }
                    else if (control.type === 'text' || control.type === 'number')
                    {
                        controls.push(createElement(components.TextControl, {
                            key: control.key, label: control.label, type: control.type,
                            min: control.min, max: control.max, value: attributes[control.key],
                            onChange: function(value)
                            {
                                if (control.type === 'number') value = Math.min(control.max, Math.max(control.min, parseInt(value, 10) || control.min));
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
            if (!ServerSideRender)
            {
                preview = createElement(components.Placeholder, { icon: block.icon, label: block.title }, settings.previewUnavailable);
            }
            else
            {
                var store = wp.data && wp.data.select('core/editor');
                var postId = store && store.getCurrentPostId ? store.getCurrentPostId() : 0;
                var args = { lsd_editor_preview: 1 };
                if (/^[1-9]\d*$/.test(String(postId))) args.post_id = parseInt(postId, 10);

                preview = createElement(ServerSideRender, {
                    block: block.name,
                    attributes: attributes,
                    urlQueryArgs: args,
                    EmptyResponsePlaceholder: function()
                    {
                        return createElement(components.Placeholder, { icon: block.icon, label: block.title }, settings.empty);
                    }
                });
            }

            wrapper.onClickCapture = function(event)
            {
                if (!event.target.closest('a, button, .lsd-share-modal-button')) return;
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

    wp.hooks.addFilter('lsd.blockEditor.edit', 'listdom/listing', function(edit, block)
    {
        return block.editor.type === 'listing' ? listingEdit(block) : edit;
    });
}(window.wp));
