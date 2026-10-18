(function(wp)
{
    if (!wp || !wp.hooks || !wp.element || !wp.components) return;

    var editor = wp.blockEditor || wp.editor;
    if (!editor) return;

    var createElement = wp.element.createElement;
    var components = wp.components;
    var packageRender = wp.serverSideRender;
    var ServerSideRender = packageRender && packageRender.default ? packageRender.default : packageRender || components.ServerSideRender;

    function layoutEdit(block)
    {
        var emptyPreview = function()
        {
            return createElement(components.Placeholder, { icon: block.icon, label: block.title }, block.editor.missing);
        };

        return function(props)
        {
            var id = Number(props.attributes.layoutId) || 0;
            var shortcodes = window.lsdBlockEditor ? window.lsdBlockEditor.shortcodes : [];
            var exists = shortcodes.some(function(shortcode)
            {
                return Number(shortcode.id) === id;
            });
            var options = [{ label: block.editor.selectPrompt, value: '0' }];

            shortcodes.forEach(function(shortcode)
            {
                options.push({ label: shortcode.title || '#' + shortcode.id, value: String(shortcode.id) });
            });

            if (id && !exists) options.push({ label: block.editor.missing, value: String(id) });

            var select = function(value)
            {
                props.setAttributes({ layoutId: parseInt(value, 10) || 0 });
            };
            var selector = function()
            {
                return createElement(components.SelectControl, {
                    label: block.editor.selectLabel,
                    value: String(id),
                    options: options,
                    onChange: select
                });
            };
            var inspector = createElement(editor.InspectorControls, null,
                createElement(components.PanelBody, { title: block.editor.selectLabel }, selector())
            );
            var preview;

            if (!id)
            {
                preview = createElement(components.Placeholder, { icon: block.icon, label: block.title },
                    createElement('p', null, block.editor.empty), selector()
                );
            }
            else if (!exists)
            {
                preview = createElement(components.Placeholder, { icon: block.icon, label: block.title }, block.editor.missing);
            }
            else if (!ServerSideRender)
            {
                preview = createElement(components.Placeholder, { icon: block.icon, label: block.title }, block.editor.previewUnavailable);
            }
            else
            {
                preview = createElement(ServerSideRender, {
                    block: block.name,
                    attributes: props.attributes,
                    urlQueryArgs: { lsd_editor_preview: 1 },
                    EmptyResponsePlaceholder: emptyPreview
                });
            }

            var wrapper = editor.useBlockProps ? editor.useBlockProps() : {};
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

            return createElement(wp.element.Fragment, null,
                inspector,
                createElement('div', wrapper, preview)
            );
        };
    }

    wp.hooks.addFilter('lsd.blockEditor.edit', 'listdom/layout', function(edit, block)
    {
        return block.editor.type === 'layout' ? layoutEdit(block) : edit;
    });
}(window.wp));
