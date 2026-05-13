<?php

class LSD_Payments_Taxonomy extends LSD_Base
{
    const META_COUNTRY = 'lsd_tax_country';
    const META_STATE = 'lsd_tax_state';
    const META_RATE = 'lsd_tax_rate';

    public function init()
    {
        add_action('init', [$this, 'register']);

        add_action(LSD_Base::TAX_TAX . '_add_form_fields', [$this, 'add_form']);
        add_action(LSD_Base::TAX_TAX . '_edit_form_fields', [$this, 'edit_form']);
        add_action('created_' . LSD_Base::TAX_TAX, [$this, 'save_metadata']);
        add_action('edited_' . LSD_Base::TAX_TAX, [$this, 'save_metadata']);

        add_filter('manage_edit-' . LSD_Base::TAX_TAX . '_columns', [$this, 'filter_columns']);
        add_filter('manage_' . LSD_Base::TAX_TAX . '_custom_column', [$this, 'filter_columns_content'], 10, 3);

        add_action('admin_post_lsd_save_tax_settings', [$this, 'save_settings']);
    }

    public function register()
    {
        $payments = LSD_Options::payments();
        $tax_settings = isset($payments['taxes']) && is_array($payments['taxes']) ? $payments['taxes'] : [];

        $args = [
            'label' => esc_html__('Tax', 'listdom'),
            'labels' => [
                'name' => esc_html__('Tax', 'listdom'),
                'singular_name' => esc_html__('Tax Rate', 'listdom'),
                'all_items' => esc_html__('Tax Rates', 'listdom'),
                'edit_item' => esc_html__('Edit Tax Rate', 'listdom'),
                'view_item' => esc_html__('View Tax Rate', 'listdom'),
                'update_item' => esc_html__('Update Tax Rate', 'listdom'),
                'add_new_item' => esc_html__('Add New Tax Rate', 'listdom'),
                'new_item_name' => esc_html__('New Tax Rate', 'listdom'),
                'search_items' => esc_html__('Search Tax Rates', 'listdom'),
                'not_found' => esc_html__('No tax rates found.', 'listdom'),
                'back_to_items' => esc_html__('← Back to Tax', 'listdom'),
            ],
            'public' => false,
            'hierarchical' => false,
            'show_ui' => !empty($tax_settings['enable']),
            'show_admin_column' => false,
            'show_in_rest' => false,
            'meta_box_cb' => false,
        ];

        register_taxonomy(LSD_Base::TAX_TAX, [LSD_Base::PTYPE_PLAN, LSD_Base::PTYPE_COUPON, LSD_Base::PTYPE_ORDER, LSD_Base::PTYPE_RECURRING], apply_filters('lsd_taxonomy_tax_args', $args));
    }

    public function add_form()
    {
        $tax = new LSD_Payments_Tax();
        $states = $tax->get_states_list();
        ?>
        <div class="form-field">
            <label for="lsd_tax_country"><?php esc_html_e('Country', 'listdom'); ?></label>
            <select class="widefat" name="<?php echo esc_attr(self::META_COUNTRY); ?>" id="lsd_tax_country">
                <option value=""><?php esc_html_e('Select country', 'listdom'); ?></option>
                <?php foreach ($tax->get_countries() as $code => $country): ?>
                    <option value="<?php echo esc_attr($code); ?>"><?php echo esc_html($country); ?></option>
                <?php endforeach; ?>
            </select>
            <p class="description"><?php esc_html_e('Choose the country this rate applies to.', 'listdom'); ?></p>
        </div>
        <div class="form-field">
            <label for="lsd_tax_state"><?php esc_html_e('State / Province', 'listdom'); ?></label>
            <select class="widefat" name="<?php echo esc_attr(self::META_STATE); ?>" id="lsd_tax_state">
                <option value=""><?php esc_html_e('All states / provinces', 'listdom'); ?></option>
            </select>
            <p class="description"><?php esc_html_e('Optional: narrow the rate to a specific state or province.', 'listdom'); ?></p>
        </div>
        <div class="form-field">
            <label for="lsd_tax_rate"><?php esc_html_e('Rate (%)', 'listdom'); ?></label>
            <input type="number" name="<?php echo esc_attr(self::META_RATE); ?>" id="lsd_tax_rate" step="0.01" min="0" placeholder="0.00">
            <p class="description"><?php esc_html_e('Enter the percentage for this location.', 'listdom'); ?></p>
        </div>
        <script>
        (function($)
        {
            const states = <?php echo wp_json_encode($states); ?>;
            function refreshStates(country, selected)
            {
                const $state = $('#lsd_tax_state');
                const options = states[country] || {};
                let html = '<option value=""><?php echo esc_js(esc_html__('All states / provinces', 'listdom')); ?></option>';
                $.each(options, function (code, name)
                {
                    const sel = selected && code === selected ? ' selected="selected"' : '';
                    html += '<option value="' + code + '"' + sel + '>' + name + '</option>';
                });
                $state.html(html);
            }

            $('#lsd_tax_country').on('change', function()
            {
                refreshStates($(this).val(), '');
            });
        })(jQuery);
        </script>
        <?php
    }

    public function edit_form($term)
    {
        $tax = new LSD_Payments_Tax();

        $states = $tax->get_states_list();

        $country = get_term_meta($term->term_id, self::META_COUNTRY, true);
        $state = get_term_meta($term->term_id, self::META_STATE, true);
        $rate = get_term_meta($term->term_id, self::META_RATE, true);

        ?>
        <tr class="form-field">
            <th scope="row">
                <label for="lsd_tax_country"><?php esc_html_e('Country', 'listdom'); ?></label>
            </th>
            <td>
                <select class="widefat" name="<?php echo esc_attr(self::META_COUNTRY); ?>" id="lsd_tax_country">
                    <option value=""><?php esc_html_e('Select country', 'listdom'); ?></option>
                    <?php foreach ($tax->get_countries() as $code => $country_name): ?>
                        <option value="<?php echo esc_attr($code); ?>" <?php selected($country, $code); ?>><?php echo esc_html($country_name); ?></option>
                    <?php endforeach; ?>
                </select>
                <p class="description"><?php esc_html_e('Choose the country this rate applies to.', 'listdom'); ?></p>
            </td>
        </tr>
        <tr class="form-field">
            <th scope="row">
                <label for="lsd_tax_state"><?php esc_html_e('State / Province', 'listdom'); ?></label>
            </th>
            <td>
                <select class="widefat" name="<?php echo esc_attr(self::META_STATE); ?>" id="lsd_tax_state">
                    <option value=""><?php esc_html_e('All states / provinces', 'listdom'); ?></option>
                    <?php
                    $country_states = $states[$country] ?? [];
                    foreach ($country_states as $code => $state_name): ?>
                        <option value="<?php echo esc_attr($code); ?>" <?php selected($state, $code); ?>><?php echo esc_html($state_name); ?></option>
                    <?php endforeach; ?>
                </select>
                <p class="description"><?php esc_html_e('Optional: narrow the rate to a specific state or province.', 'listdom'); ?></p>
            </td>
        </tr>
        <tr class="form-field">
            <th scope="row">
                <label for="lsd_tax_rate"><?php esc_html_e('Rate (%)', 'listdom'); ?></label>
            </th>
            <td>
                <input type="number" name="<?php echo esc_attr(self::META_RATE); ?>" id="lsd_tax_rate" value="<?php echo esc_attr($rate); ?>" step="0.01" min="0" placeholder="0.00">
                <p class="description"><?php esc_html_e('Enter the percentage for this location.', 'listdom'); ?></p>
            </td>
        </tr>
        <script>
        (function($)
        {
            const states = <?php echo wp_json_encode($states); ?>;
            function refreshStates(country, selected)
            {
                const $state = $('#lsd_tax_state');
                const options = states[country] || {};
                let html = '<option value=""><?php echo esc_js(esc_html__('All states / provinces', 'listdom')); ?></option>';
                $.each(options, function (code, name)
                {
                    const sel = selected && code === selected ? ' selected="selected"' : '';
                    html += '<option value="' + code + '"' + sel + '>' + name + '</option>';
                });
                $state.html(html);
            }

            $('#lsd_tax_country').on('change', function()
            {
                refreshStates($(this).val(), '');
            });
        })(jQuery);
        </script>
        <?php
    }

    public function save_metadata($term_id)
    {
        if (!current_user_can('manage_options')) return;

        $country = isset($_POST[self::META_COUNTRY]) ? sanitize_text_field(wp_unslash($_POST[self::META_COUNTRY])) : '';
        $state = isset($_POST[self::META_STATE]) ? sanitize_text_field(wp_unslash($_POST[self::META_STATE])) : '';
        $rate = isset($_POST[self::META_RATE]) ? (float) $_POST[self::META_RATE] : 0;

        update_term_meta($term_id, self::META_COUNTRY, strtoupper($country));
        update_term_meta($term_id, self::META_STATE, strtoupper($state));
        update_term_meta($term_id, self::META_RATE, max(0.0, $rate));
    }

    public function filter_columns($columns)
    {
        $name = $columns['name'] ?? '';
        $posts = $columns['posts'] ?? '';

        unset($columns['description']);
        unset($columns['slug']);
        unset($columns['posts']);

        $columns['location'] = esc_html__('Location', 'listdom');
        $columns['rate'] = esc_html__('Rate', 'listdom');
        $columns['name'] = $name;
        $columns['posts'] = $posts;

        return $columns;
    }

    public function filter_columns_content($content, $column_name, $term_id)
    {
        switch ($column_name)
        {
            case 'location':
                $country = get_term_meta($term_id, self::META_COUNTRY, true);
                $state = get_term_meta($term_id, self::META_STATE, true);

                $parts = [];
                if ($country) $parts[] = esc_html($country);
                if ($state) $parts[] = esc_html($state);

                $content = count($parts) ? implode(' / ', $parts) : esc_html__('All locations', 'listdom');
                break;

            case 'rate':
                $rate = (float) get_term_meta($term_id, self::META_RATE, true);
                $content = esc_html(number_format_i18n($rate, 2)) . '%';
                break;

            default:
                break;
        }

        return $content;
    }

    public function save_settings()
    {
        if (!current_user_can('manage_options')) wp_die(esc_html__('You do not have permission to manage tax.', 'listdom'));
        check_admin_referer('lsd_save_tax_settings');

        $settings = isset($_POST['lsd_tax_settings']) ? wp_unslash($_POST['lsd_tax_settings']) : [];
        $settings = is_array($settings) ? $settings : [];

        $payments = LSD_Options::payments();

        $payments['taxes'] = [
            'enable' => isset($settings['enable']) && (int) $settings['enable'] ? 1 : 0,
            'prices_include_tax' => isset($settings['prices_include_tax']) && (int) $settings['prices_include_tax'] ? 1 : 0,
            'rate' => isset($settings['rate']) ? (float) $settings['rate'] : 0,
            'label' => isset($settings['label']) ? sanitize_text_field($settings['label']) : '',
        ];

        LSD_Options::merge('lsd_payments', $payments);

        $redirect = isset($_POST['redirect_to']) ? esc_url_raw($_POST['redirect_to']) : admin_url('edit-tags.php?taxonomy=' . LSD_Base::TAX_TAX);
        wp_safe_redirect($redirect);
        exit;
    }
}
