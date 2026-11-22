(function(wp, settings)
{
    if (!wp || !settings || !wp.plugins || !wp.editPost || !wp.data || !wp.apiFetch) return;

    var createElement = wp.element.createElement;
    var useEffect = wp.element.useEffect;
    var useState = wp.element.useState;
    var Panel = wp.editPost.PluginDocumentSettingPanel;
    if (!Panel) return;

    function TemplateSettings()
    {
        var meta = wp.data.useSelect(function(select)
        {
            return select('core/editor').getEditedPostAttribute('meta') || {};
        }, []);
        var selected = parseInt(meta.lsd_gutenberg_preview_listing, 10) || 0;
        var searchState = useState('');
        var search = searchState[0];
        var setSearch = searchState[1];
        var listingState = useState([]);
        var listings = listingState[0];
        var setListings = listingState[1];

        useEffect(function()
        {
            var active = true;
            var path = '/listdom/v1/template-preview-listings?template_id=' + settings.templateId
                + '&selected=' + selected + '&search=' + encodeURIComponent(search);
            wp.apiFetch({ path: path }).then(function(items)
            {
                if (active) setListings(items);
            }).catch(function()
            {
                if (active) setListings([]);
            });
            return function() { active = false; };
        }, [search, selected]);

        var options = [{ value: 0, label: settings.chooseListing }].concat(listings);
        return createElement(Panel, { name: 'lsd-template-listing', title: settings.panelTitle },
            createElement(wp.components.TextControl, {
                label: settings.searchListings,
                value: search,
                onChange: setSearch
            }),
            createElement(wp.components.SelectControl, {
                label: settings.sampleListing,
                help: settings.sampleHelp,
                value: selected,
                options: options,
                onChange: function(value)
                {
                    wp.data.dispatch('core/editor').editPost({
                        meta: Object.assign({}, meta, { lsd_gutenberg_preview_listing: parseInt(value, 10) || 0 })
                    });
                }
            })
        );
    }

    wp.plugins.registerPlugin('lsd-template-listing', { render: TemplateSettings });
}(window.wp, window.lsdTemplateGutenberg));
