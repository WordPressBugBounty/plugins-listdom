(function ($) {
    // Elementor's init Hook
    $(window).on('elementor:init', function () {
        function dashboardMenuSlug(value) {
            return (value || '')
                .toString()
                .trim()
                .toLowerCase()
                .replace(/[^a-z0-9_-]/g, '');
        }

        function syncDashboardDefaultSection(model) {
            if (!model || model.get('widgetType') !== 'lsd-frontend-dashboard') return;

            const settings = model.get('settings');
            if (!settings || typeof settings.get !== 'function') return;

            const updateOptions = function () {
                const $select = $('.elementor-control-default_active_section select').first();
                if (!$select.length) return false;

                const selected = settings.get('default_active_section') || $select.val();
                $select.find('option[data-lsd-custom-menu-option]').remove();

                const existingValues = Object.create(null);
                $select.find('option').each(function () {
                    existingValues[this.value] = true;
                });

                const repeater = settings.get('custom_menu_items');
                const items = Array.isArray(repeater)
                    ? repeater
                    : (repeater && typeof repeater.toJSON === 'function' ? repeater.toJSON() : []);
                if (items.length) {
                    items.forEach(function (item, itemIndex) {
                        if (!item) return;

                        const isEnabled = item.enabled === undefined || item.enabled === 'yes' || item.enabled === 1 || item.enabled === true;
                        if (!isEnabled) return;

                        const label = (item.label || '').toString().trim();
                        const content = (item.content || '').toString().trim();
                        let slug = dashboardMenuSlug(item.slug);
                        if (!slug) slug = 'custom-' + itemIndex;
                        if (!label || !content || !slug || existingValues[slug]) return;

                        $('<option>')
                            .attr('value', slug)
                            .attr('data-lsd-custom-menu-option', 'true')
                            .text(label)
                            .appendTo($select);
                        existingValues[slug] = true;
                    });
                }

                if (selected) $select.val(selected);
                if ($select.hasClass('select2-hidden-accessible')) $select.trigger('change.select2');

                return true;
            };

            if (!model.lsdDashboardDefaultSectionBound) {
                const bindRepeater = function () {
                    const repeater = settings.get('custom_menu_items');
                    if (!repeater || typeof repeater.on !== 'function') return;

                    if (model.lsdDashboardDefaultSectionRepeater) {
                        model.stopListening(model.lsdDashboardDefaultSectionRepeater, 'add remove reset change', updateOptions);
                    }
                    model.listenTo(repeater, 'add remove reset change', updateOptions);
                    model.lsdDashboardDefaultSectionRepeater = repeater;
                };

                settings.on('change:custom_menu_items', function () {
                    bindRepeater();
                    updateOptions();
                });
                bindRepeater();
                model.lsdDashboardDefaultSectionBound = true;
            }

            let attempts = 0;
            const waitForControl = function () {
                if (updateOptions() || attempts >= 20) return;

                attempts += 1;
                window.setTimeout(waitForControl, 100);
            };
            waitForControl();
        }

        elementor.hooks.addAction('panel/open_editor/widget', function (panel, model) {
            syncDashboardDefaultSection(model);

            let shortcodeAttempts = 0;
            const shortcode_update_interval = setInterval(() => {
                const $select = $('.elementor-control-shortcode select');
                const $button = $('#lsd-shortcode-edit-link');

                if ($select.length && $button.length) {
                    update_link($select.val(), $button);

                    $select.on('change', function () {
                        update_link(this.value, $button);
                    });

                    clearInterval(shortcode_update_interval);
                }
                else if (++shortcodeAttempts >= 20) clearInterval(shortcode_update_interval);
            }, 300);

            let searchAttempts = 0;
            const search_update_interval = setInterval(() => {
                const $select = $('.elementor-control-search select');
                const $button = $('#lsd-search-edit-link');

                if ($select.length && $button.length) {
                    update_link($select.val(), $button);

                    $select.on('change', function () {
                        update_link(this.value, $button);
                    });

                    clearInterval(search_update_interval);
                }
                else if (++searchAttempts >= 20) clearInterval(search_update_interval);
            }, 300);
        });

        function update_link(id, $button) {
            if (!id || isNaN(id)) {
                $button.hide();
                return;
            }

            const admin_url = (() => {
                const ajaxurl = (typeof window.ajaxurl === 'string') ? window.ajaxurl : '';
                if (ajaxurl) return ajaxurl.replace(/admin-ajax\.php(?:\?.*)?$/, '');

                if (elementor.config && elementor.config.admin_url) return elementor.config.admin_url;

                return '/wp-admin/';
            })();

            const link = `${admin_url}post.php?post=${id}&action=edit`;

            $button.attr('href', link).show();
        }
    });
})(jQuery);
