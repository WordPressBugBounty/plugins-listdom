/** Shared countdown messages, including content inserted over AJAX. */
(function ()
{
    const states = new WeakMap();
    const active = new Set();
    let timer = null;

    function format(seconds, pattern)
    {
        return pattern.replace(/\{hours\}/g, String(Math.floor(seconds / 3600)))
            .replace(/\{minutes\}/g, String(Math.floor(seconds / 60) % 60).padStart(2, '0'))
            .replace(/\{seconds\}/g, String(seconds % 60).padStart(2, '0'));
    }

    function text(options, seconds)
    {
        if (seconds === 0 && options.complete) return options.complete;
        return options.template.replace(/\{time\}/g, format(seconds, options.format || '{hours}h {minutes}m {seconds}s'));
    }

    function tick()
    {
        active.forEach(function (element)
        {
            if (!element.isConnected)
            {
                active.delete(element);
                return;
            }
            const state = states.get(element);
            const remaining = Math.max(0, Math.ceil((state.until - Date.now()) / 1000));
            const value = text(state.options, remaining);
            if (element.textContent !== value) element.textContent = value;
            if (remaining === 0) active.delete(element);
        });
        if (!active.size && timer !== null)
        {
            clearInterval(timer);
            timer = null;
        }
    }

    function init(element)
    {
        let state = states.get(element);
        if (!state)
        {
            let options;
            try { options = JSON.parse(element.getAttribute('data-lsd-countdown')); }
            catch (error) { return; }
            if (!options || !Number.isFinite(options.seconds) || options.seconds < 0 || typeof options.template !== 'string') return;
            state = {options: options, until: Date.now() + options.seconds * 1000};
            states.set(element, state);
        }
        active.add(element);
        tick();
        if (active.size && timer === null) timer = setInterval(tick, 1000);
    }

    function scan(root)
    {
        if (root.nodeType === 1 && root.matches('[data-lsd-countdown]')) init(root);
        root.querySelectorAll('[data-lsd-countdown]').forEach(init);
    }

    function mount(element, options)
    {
        if (!options) return;
        element.setAttribute('data-lsd-countdown', JSON.stringify(options));
        element.textContent = text(options, Math.max(0, Math.ceil(options.seconds)));
        states.delete(element);
        if (element.isConnected) init(element);
    }

    window.ListdomCountdown = {
        mount: mount,
        init: scan,
        message: function (response)
        {
            if (!response.countdown) return response.message;
            const element = document.createElement('span');
            mount(element, response.countdown);
            return element.outerHTML;
        },
    };

    function ready()
    {
        scan(document);
        new MutationObserver(function (changes)
        {
            changes.forEach(function (change)
            {
                change.addedNodes.forEach(function (node)
                {
                    if (node.nodeType === 1) scan(node);
                });
            });
        }).observe(document.body, {childList: true, subtree: true});
        document.addEventListener('visibilitychange', tick);
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', ready);
    else ready();
})();
