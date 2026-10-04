(function(wp, config)
{
    if (!wp || !wp.data || !wp.element || !wp.apiFetch || !config) return;

    const useEffect = wp.element.useEffect;
    const useState = wp.element.useState;
    const createElement = wp.element.createElement;
    // Match the Listdom menu mark with padding comparable to WordPress command icons.
    const iconPaths = [
        'M19.7,6.9c-0.1-1-0.5-1.5-1.6-1.5H17c-0.9,0-1.2,0.2-1.5,1.1v1.8c-0.1,0.7,0.4,1.2,1.1,1.3c0.1,0,0.1,0,0.2,0h1.4c0.4,0,0.9-0.1,1.2-0.4c0.3-0.3,0.4-0.7,0.4-1.1V6.9z',
        'M16.7,4.6h1.9c0.7,0,1.2-0.5,1.3-1.2V1.7c0-0.7-0.5-1.2-1.2-1.2h-1.9c-0.7,0-1.2,0.6-1.2,1.3l0,0v1.3c0,0.4,0,0.8,0.2,1.1C16,4.4,16.3,4.6,16.7,4.6z',
        'M12.6,16.5c-0.2-0.8-1-0.9-1.6-0.9H4.5c-0.2,0-0.2,0-0.2-0.2V1.8c0-0.7-0.6-1.3-1.2-1.3H1.5c-0.2,0-0.5,0.1-0.7,0.1C0.4,0.8,0.2,1.3,0.2,1.8v16.5c0,0.2,0,0.3,0.1,0.5c0.2,0.5,0.7,0.9,1.3,0.8h10.2c0.3-0.1,0.6-0.2,0.8-0.5c0.2-0.4,0.1-0.9,0.1-1.4v-0.5C12.6,16.8,12.7,16.7,12.6,16.5z',
        'M19.6,11.9c0.1-0.7-0.4-1.3-1.1-1.4c-0.1,0-0.2,0-0.3,0H17c-0.5-0.1-1,0.2-1.3,0.6c-0.2,0.3-0.2,0.7-0.2,1v6.2c0,0.4,0.1,0.8,0.3,1.1c0.2,0.3,0.5,0.5,0.9,0.5c0.7,0,1.5,0,2.2-0.1c0.5-0.3,0.8-0.8,0.8-1.4C19.6,18.3,19.6,11.9,19.6,11.9z'
    ];
    const listdomIcon = createElement('svg', {
        viewBox: '-3 -3 26 26',
        fill: 'currentColor',
        xmlns: 'http://www.w3.org/2000/svg',
        'aria-hidden': true,
        focusable: false
    }, iconPaths.map(function(d, index)
    {
        return createElement('path', {key: index, d: d});
    }));

    function useListdomSearchCommandLoader({search})
    {
        const query = search.trim();
        const [state, setState] = useState({query: '', items: [], isLoading: false});

        useEffect(function()
        {
            if (!query)
            {
                setState({query: '', items: [], isLoading: false});
                return;
            }

            let active = true;
            setState({query: query, items: [], isLoading: true});

            const timer = window.setTimeout(function()
            {
                wp.apiFetch({
                    path: '/listdom/v1/command-search?search=' + encodeURIComponent(query),
                    headers: {'X-WP-Nonce': config.nonce}
                }).then(function(response)
                {
                    if (active) setState({query: query, items: response.items || [], isLoading: false});
                }).catch(function()
                {
                    if (active) setState({query: query, items: [], isLoading: false});
                });
            }, 200);

            return function()
            {
                active = false;
                window.clearTimeout(timer);
            };
        }, [query]);

        if (!query || state.query !== query) return {commands: [], isLoading: !!query};

        return {
            commands: state.items.map(function(item)
            {
                return {
                    name: 'listdom/' + item.kind + '-' + item.id,
                    label: item.title + ' — ' + item.type_label,
                    icon: listdomIcon,
                    category: 'edit',
                    callback: function({close})
                    {
                        close();
                        window.location.assign(item.url);
                    }
                };
            }),
            isLoading: state.isLoading
        };
    }

    wp.data.dispatch('core/commands').registerCommandLoader({
        name: 'listdom/search-records',
        category: 'edit',
        hook: useListdomSearchCommandLoader
    });
})(window.wp, window.lsdCommandPalette);
