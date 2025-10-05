(function(wp, $)
{
    // Block Editor
    if(lsd && wp && wp.blocks)
    {
        lsd.shortcodes.forEach(function(e, i)
        {
            wp.blocks.registerBlockType('listdom/shortcodes-'+i,
            {
                title: e.title.toLowerCase().replace("/(^([a-zA-Z\p{M}]))|([ -][a-zA-Z\p{M}])/g", function(s)
                {
                    return s.toUpperCase().replace(/-/g,' ');
                }),
                icon: 'editor-code',
                category: 'lsd.be.category',
                edit: function()
                {
                    return '[listdom id="'+e.id+'"]';
                },
                save: function()
                {
                    return '[listdom id="'+e.id+'"]';
                }
            });
        });
    }
}(window.wp, jQuery));
