(function(wp, $)
{
    // Block Editor
    if(lsd && wp && wp.blocks && wp.blockEditor && wp.element)
    {
        lsd.shortcodes.forEach(function(e, i)
        {
            var shortcode = '[listdom id="'+e.id+'"]';

            wp.blocks.registerBlockType('listdom/shortcodes-'+i,
            {
                apiVersion: 3,
                title: e.title.toLowerCase().replace("/(^([a-zA-Z\p{M}]))|([ -][a-zA-Z\p{M}])/g", function(s)
                {
                    return s.toUpperCase().replace(/-/g,' ');
                }),
                icon: 'editor-code',
                category: 'lsd.be.category',
                edit: function()
                {
                    return wp.element.createElement(
                        'div',
                        wp.blockEditor.useBlockProps(),
                        shortcode
                    );
                },
                save: function()
                {
                    return shortcode;
                }
            });
        });
    }
}(window.wp, jQuery));
