(function(wp, settings)
{
    if (!settings || !wp || !wp.blocks || !wp.element) return;

    var editor = wp.blockEditor || wp.editor;
    if (!editor) return;

    // Native blocks are defined once in PHP and passed to the editor here.
    settings.blocks.forEach(function(block)
    {
        var fallback = function()
        {
            var label = block.editor.message || block.description || block.title;
            var props = editor.useBlockProps ? editor.useBlockProps() : {};

            return wp.element.createElement('div', props, label);
        };
        var edit = wp.hooks ? wp.hooks.applyFilters('lsd.blockEditor.edit', fallback, block) : fallback;

        wp.blocks.registerBlockType(block.name, {
            apiVersion: block.apiVersion,
            title: block.title,
            description: block.description,
            icon: block.icon,
            category: block.category,
            attributes: block.attributes,
            supports: block.supports,
            edit: edit,
            save: function() { return null; }
        });
    });

    // Register only names found in the edited document; their numbers are not layout IDs.
    settings.legacyBlocks.forEach(function(name)
    {
        wp.blocks.registerBlockType(name, {
            apiVersion: 3,
            title: settings.legacyTitle,
            icon: 'editor-code',
            category: 'listdom',
            supports: { inserter: false },
            attributes: { shortcode: { type: 'string', source: 'raw' } },
            edit: function(props)
            {
                return wp.element.createElement('div', editor.useBlockProps ? editor.useBlockProps() : {}, props.attributes.shortcode);
            },
            save: function(props)
            {
                return props.attributes.shortcode;
            }
        });
    });
}(window.wp, window.lsdBlockEditor));
