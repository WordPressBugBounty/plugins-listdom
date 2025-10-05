(function ($) {
    // Elementor's init Hook
    $(window).on('elementor:init', function () {
        elementor.hooks.addAction('panel/open_editor/widget', function (panel, model) {
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
            }, 300);

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
            }, 300);
        });

        function update_link(id, $button) {
            if (!id || isNaN(id)) {
                $button.hide();
                return;
            }

            const admin_url = (elementor.config && elementor.config.admin_url) ? elementor.config.admin_url : '/wp-admin/';
            const link = `${admin_url}post.php?post=${id}&action=edit`;

            $button.attr('href', link).show();
        }
    });
})(jQuery);
