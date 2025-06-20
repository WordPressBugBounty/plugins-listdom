<?php

class LSD_Form extends LSD_Base
{
    public static function label($args = [])
    {
        if (!count($args)) return false;

        $required = isset($args['required']) && $args['required'];
        return '<label for="' . (isset($args['for']) ? esc_attr($args['for']) : '') . '" class="' . (isset($args['class']) ? esc_attr($args['class']) : '') . '">' . esc_html($args['title']) . ($required ? ' ' . LSD_Base::REQ_HTML : '') . '</label>';
    }

    public static function text($args = [])
    {
        if (!count($args)) return false;
        return self::input($args);
    }

    public static function number($args = [])
    {
        if (!count($args)) return false;
        return self::input($args, 'number');
    }

    public static function url($args = [])
    {
        if (!count($args)) return false;
        return self::input($args, 'url');
    }

    public static function tel($args = [])
    {
        if (!count($args)) return false;
        return self::input($args, 'tel');
    }

    public static function email($args = [])
    {
        if (!count($args)) return false;
        return self::input($args, 'email');
    }

    public static function datepicker($args = [])
    {
        if (!count($args)) return false;
        return self::input($args, 'date');
    }

    public static function checkbox($args = [])
    {
        if (!count($args)) return false;
        return self::input($args, 'checkbox');
    }

    public static function separator($args = [])
    {
        if (!count($args)) return false;
        return '<div class="lsd-separator">' . (isset($args['label']) ? esc_html($args['label']) : '') . '</div>';
    }

    public static function input($args = [], $type = 'text')
    {
        if (!count($args)) return false;

        $attributes = '';
        if (isset($args['attributes']) && is_array($args['attributes']) && count($args['attributes']))
        {
            foreach ($args['attributes'] as $key => $value) $attributes .= $key . '="' . esc_attr($value) . '" ';
        }

        $required = isset($args['required']) && $args['required'];
        return '<input type="' . esc_attr($type) . '" name="' . (isset($args['name']) ? esc_attr($args['name']) : '') . '" id="' . (isset($args['id']) ? esc_attr($args['id']) : '') . '" class="' . (isset($args['class']) ? esc_attr($args['class']) : '') . '" value="' . (isset($args['value']) ? esc_attr($args['value']) : '') . '" placeholder="' . (isset($args['placeholder']) ? esc_attr($args['placeholder']) : '') . '" ' . trim($attributes) . ' ' . ($required ? 'required' : '') . '>';
    }

    public static function select($args = [])
    {
        if (!count($args)) return false;

        $options = '';

        // Show Empty Option
        if (isset($args['show_empty']) and $args['show_empty'])
        {
            $options .= '<option value="" ' . ((isset($args['value']) and !is_array($args['value']) and $args['value'] == '') ? 'selected="selected"' : '') . '>' . ((isset($args['empty_label']) and trim($args['empty_label'])) ? esc_html($args['empty_label']) : '-----') . '</option>';
        }

        foreach ($args['options'] as $value => $label) $options .= '<option value="' . esc_attr($value) . '" ' . ((isset($args['value']) and ((!is_array($args['value']) and trim($args['value']) != '' and $args['value'] == $value) or (is_array($args['value']) and in_array($value, $args['value'])))) ? 'selected="selected"' : '') . '>' . esc_html($label) . '</option>';

        $attributes = '';
        if (isset($args['attributes']) and is_array($args['attributes']) and count($args['attributes']))
        {
            foreach ($args['attributes'] as $key => $value) $attributes .= $key . '="' . esc_attr($value) . '" ';
        }

        // Required
        $required = isset($args['required']) && $args['required'];

        return '<select name="' . (isset($args['name']) ? esc_attr($args['name']) : '') . '" id="' . (isset($args['id']) ? esc_attr($args['id']) : '') . '" class="' . (isset($args['class']) ? esc_attr($args['class']) : '') . '" ' . trim($attributes) . ' ' . ($required ? 'required' : '') . '>
            ' . $options . '                       
        </select>';
    }

    public static function checkboxes($args = [])
    {
        if (!count($args)) return false;

        $attributes = '';
        if (isset($args['attributes']) and is_array($args['attributes']) and count($args['attributes']))
        {
            foreach ($args['attributes'] as $key => $value) $attributes .= $key . '="' . esc_attr($value) . '" ';
        }

        // Required
        $required = isset($args['required']) && $args['required'];

        $checkboxes = '';
        foreach ($args['options'] as $value => $label) $checkboxes .= '<li><label><input name="' . (isset($args['name']) ? esc_attr($args['name']) : '') . '" type="checkbox" value="' . esc_attr($value) . '" ' . ((isset($args['value']) and is_array($args['value']) and in_array($value, $args['value'])) ? 'checked="checked"' : '') . ' ' . trim($attributes) . ' ' . ($required ? 'required' : '') . '> ' . esc_html($label) . '</label></li>';

        return '<ul class="' . (isset($args['class']) ? esc_attr($args['class']) : '') . '">' . $checkboxes . '</ul>';
    }

    public static function switcher($args = [])
    {
        if (!count($args)) return false;

        $toggle = isset($args['toggle']);
        $toggle2 = isset($args['toggle2']);

        return '<label class="lsd-switch">
            <input type="hidden" name="' . esc_attr($args['name']) . '" value="0">
            <input type="checkbox" id="' . esc_attr($args['id']) . '" name="' . esc_attr($args['name']) . '" value="1" ' . ((isset($args['value']) and trim($args['value']) != '' and $args['value'] == 1) ? 'checked="checked"' : '') . '>
            <span class="lsd-slider ' . ($toggle ? 'lsd-toggle' : '') . '" ' . ($toggle ? 'data-for="' . esc_attr($args['toggle']) . '"' : '') . ' ' . ($toggle2 ? 'data-for2="' . esc_attr($args['toggle2']) . '"' : '') . '></span>
        </label>';
    }

    public static function textarea($args = [])
    {
        if (!count($args)) return false;

        $attributes = '';
        if (isset($args['attributes']) and is_array($args['attributes']) and count($args['attributes']))
        {
            foreach ($args['attributes'] as $key => $value) $attributes .= $key . '="' . esc_attr($value) . '" ';
        }

        $required = isset($args['required']) && $args['required'];
        return '<textarea name="' . esc_attr($args['name']) . '" id="' . (isset($args['id']) ? esc_attr($args['id']) : '') . '" placeholder="' . (isset($args['placeholder']) ? esc_attr($args['placeholder']) : '') . '" rows="' . (isset($args['rows']) ? esc_attr($args['rows']) : '') . '" ' . trim($attributes) . ' ' . ($required ? 'required' : '') . '>' . (isset($args['value']) ? esc_textarea(stripslashes($args['value'])) : '') . '</textarea>';
    }

    public static function editor($args = [])
    {
        if (!count($args)) return false;

        $value = (isset($args['value']) ? stripslashes($args['value']) : '');
        $id = (isset($args['id']) ? esc_attr($args['id']) : '');

        $name = (isset($args['name']) ? esc_attr($args['name']) : '');
        $args['textarea_name'] = $name;

        $args['textarea_rows'] = (isset($args['rows']) ? (int) $args['rows'] : 10);

        ob_start();
        wp_editor($value, $id, $args);
        return ob_get_clean();
    }

    public static function iconpicker($args = [])
    {
        if (!count($args)) return false;

        // Include icon picker assets
        $assets = new LSD_Assets();
        $assets->iconpicker();

        $options = '';
        $fonts = LSD_Base::get_font_icons();

        foreach ($fonts as $font => $code) $options .= '<option value="' . esc_attr($font) . '" ' . ((isset($args['value']) and $args['value'] == $font) ? 'selected="selected"' : '') . '>' . esc_html($font) . '</option>';

        return '<select name="' . esc_attr($args['name']) . '" id="' . (isset($args['id']) ? esc_attr($args['id']) : '') . '" class="' . (isset($args['class']) ? esc_attr($args['class']) : 'lsd-iconpicker') . '">
            ' . $options . '                       
        </select>';
    }

    public static function colorpalette($args)
    {
        if (!count($args)) return false;
        $palette = LSD_Base::get_colors();

        $output = '<div class="lsd-color-palette" data-for="' . (isset($args['for']) ? esc_attr($args['for']) : '') . '">';
        foreach ($palette as $color)
        {
            $output .= '<div class="lsd-color-box ' . ((isset($args['value']) and $args['value'] == $color) ? 'lsd-color-box-active' : '') . '" data-color="' . esc_attr($color) . '" style="background-color: ' . esc_attr($color) . '"></div>';
        }

        $output .= '</div>';
        return $output;
    }

    public static function colorpicker($args)
    {
        if (!count($args)) return false;

        $transparency = isset($args['transparency']) && $args['transparency'];
        $value = $args['value'] ?? '';

        return '<div class="lsd-colorpicker-wrapper">
            <input type="text" name="' . esc_attr($args['name']) . '" id="' . (isset($args['id']) ? esc_attr($args['id']) : '') . '" class="' . (isset($args['class']) ? esc_attr($args['class']) : 'lsd-colorpicker') . '" value="' . $value . '" data-default-color="' . (isset($args['default']) ? esc_attr($args['default']) : '') . '">
            ' . ($transparency ? '<label><input type="checkbox" ' . (trim($value) === '' ? 'checked' : '') . ' class="lsd-colorpicker-transparent">' . esc_html__('Transparent', 'listdom') . '</label>' : '') . '
        </div>';
    }

    public static function imagepicker($args)
    {
        if (!count($args)) return false;

        $image_id = $args['value'] ?? '';
        $image = $image_id ? wp_get_attachment_image($image_id, ['400', '266']) : '';

        return '<div>
            <div id="' . esc_attr($args['id']) . '_img" class="lsd-imagepicker-image-placeholder lsd-mb-2">' . (trim($image) ? $image : '') . '</div>
            <input type="hidden" name="' . esc_attr($args['name']) . '" id="' . (isset($args['id']) ? esc_attr($args['id']) : '') . '" value="' . esc_attr($image_id) . '">
            <button type="button" class="lsd-select-image-button button ' . ($image_id ? 'lsd-util-hide' : '') . '" id="' . esc_attr($args['id']) . '_button" data-for="#' . esc_attr($args['id']) . '">' . esc_html__('Upload/Select image', 'listdom') . '</button>
            <button type="button" class="lsd-remove-image-button button ' . ($image_id ? '' : 'lsd-util-hide') . '" data-for="#' . esc_attr($args['id']) . '">' . esc_html__('Remove image', 'listdom') . '</button>
        </div>';
    }

    public static function filepicker($args)
    {
        if (!count($args)) return false;

        $file_id = $args['value'] ?? '';
        $url = $file_id ? wp_get_attachment_url($file_id) : '';

        return '<div id="' . esc_attr($args['id']) . '_file" class="lsd-filepicker-preview">' . (trim($url) ? '<a href="' . esc_url($url) . '" target="_blank">' . $url . '</a>' : '') . '</div>
        <input type="hidden" name="' . esc_attr($args['name']) . '" id="' . (isset($args['id']) ? esc_attr($args['id']) : '') . '" value="' . esc_attr($file_id) . '">
        <button type="button" class="lsd-select-file-button button ' . ($file_id ? 'lsd-util-hide' : '') . '" id="' . esc_attr($args['id']) . '_button" data-for="#' . esc_attr($args['id']) . '">' . esc_html__('Upload/Select file', 'listdom') . '</button>
        <button type="button" class="lsd-remove-file-button button ' . ($file_id ? '' : 'lsd-util-hide') . '" data-for="#' . esc_attr($args['id']) . '">' . esc_html__('Remove file', 'listdom') . '</button>';
    }

    public static function mapstyle($args)
    {
        if (!count($args)) return false;

        $options = '';
        $styles = LSD_Base::get_map_styles();

        foreach ($styles as $code => $label) $options .= '<option value="' . esc_attr($code) . '" ' . ((isset($args['value']) and $args['value'] == $code) ? 'selected="selected"' : '') . '>' . esc_html($label) . '</option>';

        return '<select name="' . esc_attr($args['name']) . '" id="' . (isset($args['id']) ? esc_attr($args['id']) : '') . '" class="' . (isset($args['class']) ? esc_attr($args['class']) : 'lsd-mapstyle') . '">
            ' . $options . '                       
        </select>';
    }

    public static function fontpicker($args)
    {
        if (!count($args)) return false;

        $options = '';
        $fonts = LSD_Base::get_fonts();

        // Inherit
        if (isset($args['show_inherit']) && $args['show_inherit'])
        {
            $options .= '<option value="inherit" ' . ((isset($args['value']) && $args['value'] === 'inherit') ? 'selected="selected"' : '') . '>' . esc_html__('Inherit', 'listdom') . '</option>';
        }

        foreach ($fonts as $code => $font) $options .= '<option value="' . esc_attr($code) . '" ' . ((isset($args['value']) and $args['value'] == $code) ? 'selected="selected"' : '') . '>' . esc_html($font['label']) . '</option>';

        return '<select name="' . esc_attr($args['name']) . '" id="' . (isset($args['id']) ? esc_attr($args['id']) : '') . '" class="' . (isset($args['class']) ? esc_attr($args['class']) : 'lsd-font-picker') . '">
            ' . $options . '                       
        </select>';
    }

    public static function shortcodes($args)
    {
        if (!count($args)) return false;

        $options = '';
        $query = ['post_type' => LSD_Base::PTYPE_SHORTCODE, 'posts_per_page' => '-1'];

        // Show Only Archive Skins
        if (isset($args['only_archive_skins']) and $args['only_archive_skins'])
        {
            $query['meta_query'] = [
                [
                    'key' => 'lsd_skin',
                    'value' => ['list', 'grid', 'halfmap', 'listgrid', 'masonry', 'singlemap', 'table', 'side'],
                    'compare' => 'IN',
                ],
            ];
        }

        $shortcodes = get_posts($query);

        // Show Empty Option
        if (isset($args['show_empty']) and $args['show_empty'])
        {
            $options .= '<option value="" ' . ((isset($args['value']) and esc_attr($args['value']) == '') ? 'selected="selected"' : '') . '>' . ((isset($args['empty_label']) and trim($args['empty_label'])) ? esc_html($args['empty_label']) : '-----') . '</option>';
        }

        foreach ($shortcodes as $shortcode) $options .= '<option value="' . esc_attr($shortcode->ID) . '" ' . ((isset($args['value']) and $args['value'] == $shortcode->ID) ? 'selected="selected"' : '') . '>' . esc_html($shortcode->post_title) . '</option>';

        return '<select name="' . esc_attr($args['name']) . '" id="' . (isset($args['id']) ? esc_attr($args['id']) : '') . '" class="' . (isset($args['class']) ? esc_attr($args['class']) : 'lsd-shortcode') . '">
            ' . $options . '                       
        </select>';
    }

    public static function listings($args)
    {
        if (!count($args)) return false;

        $options = '';
        $q = ['post_type' => LSD_Base::PTYPE_LISTING, 'posts_per_page' => '-1'];

        // Only Posts with Featured Image
        if (isset($args['has_post_thumbnail']) && $args['has_post_thumbnail'])
        {
            $q['meta_query'] = [[
                'key' => '_thumbnail_id',
                'compare' => 'EXISTS',
            ]];
        }

        // Get Listings
        $listings = get_posts($q);

        // Show Empty Option
        if (isset($args['show_empty']) and $args['show_empty'])
        {
            $options .= '<option value="" ' . ((isset($args['value']) and $args['value'] == '') ? 'selected="selected"' : '') . '>' . ((isset($args['empty_label']) and trim($args['empty_label'])) ? esc_html($args['empty_label']) : '-----') . '</option>';
        }

        foreach ($listings as $listing) $options .= '<option value="' . esc_attr($listing->ID) . '" ' . ((isset($args['value']) and $args['value'] == $listing->ID) ? 'selected="selected"' : '') . '>' . esc_html($listing->post_title) . '</option>';

        return '<select name="' . esc_attr($args['name']) . '" id="' . (isset($args['id']) ? esc_attr($args['id']) : '') . '" class="' . (isset($args['class']) ? esc_attr($args['class']) : 'lsd-listing') . '">
            ' . $options . '                       
        </select>';
    }

    public static function providers($args = [])
    {
        // Get Available Map Providers
        $args['options'] = LSD_Map_Provider::get_providers();

        // Add Disabled Option
        if (isset($args['disabled']) and $args['disabled'])
        {
            $args['options'] = ['0' => esc_html__('Disabled', 'listdom')] + $args['options'];
        }

        // Dropdown Field
        return self::select($args);
    }

    public static function taxonomy($taxonomy, $args = [])
    {
        // Get Terms
        $terms = get_terms([
            'taxonomy' => $taxonomy,
            'orderby' => $args['orderby'] ?? 'name',
            'order' => $args['order'] ?? 'ASC',
            'hide_empty' => $args['hide_empty'] ?? false,
        ]);

        $options = [];
        foreach ($terms as $term) $options[$term->term_id] = $term->name;

        // Options
        $args['options'] = $options;

        // Dropdown Field
        return self::select($args);
    }

    public static function hidden($args = [])
    {
        if (!count($args)) return false;
        return self::input($args, 'hidden');
    }

    public static function pages($args = [])
    {
        if (!count($args)) return false;

        // Get WordPress Pages
        $pages = get_pages();

        $options = [];
        foreach ($pages as $page) $options[$page->ID] = $page->post_title;

        $args['options'] = $options;

        // Dropdown Field
        return self::select($args);
    }

    public static function wc($args = [])
    {
        if (!count($args)) return false;

        // Get Products
        $products = wc_get_products([
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC',
        ]);

        $options = [];
        foreach ($products as $product) $options[$product->get_id()] = $product->get_title();

        $args['options'] = $options;

        $checkbox = isset($args['checkboxes']) && $args['checkboxes'];

        // Dropdown Field
        return $checkbox ? self::checkboxes($args) : self::select($args);
    }

    public static function searches($args)
    {
        if (!count($args)) return false;

        $options = '';

        $query = ['post_type' => LSD_Base::PTYPE_SEARCH, 'posts_per_page' => '-1'];
        $searches = get_posts($query);

        // Show Empty Option
        if (isset($args['show_empty']) and $args['show_empty'])
        {
            $options .= '<option value="" ' . ((isset($args['value']) and esc_attr($args['value']) == '') ? 'selected="selected"' : '') . '>' . ((isset($args['empty_label']) and trim($args['empty_label'])) ? esc_html($args['empty_label']) : '-----') . '</option>';
        }

        foreach ($searches as $search) $options .= '<option value="' . esc_attr($search->ID) . '" ' . ((isset($args['value']) and $args['value'] == $search->ID) ? 'selected="selected"' : '') . '>' . esc_html($search->post_title) . '</option>';

        return '<select name="' . esc_attr($args['name']) . '" id="' . (isset($args['id']) ? esc_attr($args['id']) : '') . '" class="' . (isset($args['class']) ? esc_attr($args['class']) : 'lsd-search') . '">
            ' . $options . '                       
        </select>';
    }

    public static function file($args = [])
    {
        if (!count($args)) return false;
        return LSD_Form::input($args, 'file');
    }

    public static function currency($args)
    {
        if (!count($args)) return false;

        $options = '';
        $currencies = LSD_Base::get_currencies();

        // Show Empty Option
        if (isset($args['show_empty']) && $args['show_empty'])
        {
            $options .= '<option value="" ' . ((isset($args['value']) and esc_attr($args['value']) == '') ? 'selected="selected"' : '') . '>' . ((isset($args['empty_label']) and trim($args['empty_label'])) ? esc_html($args['empty_label']) : '-----') . '</option>';
        }

        // Database
        $db = new LSD_db();

        $hide_empty = (isset($args['hide_empty']) and $args['hide_empty']);
        foreach ($currencies as $symbol => $currency)
        {
            if ($hide_empty and !$db->select("SELECT COUNT(`meta_id`) FROM `#__postmeta` WHERE `meta_key`='lsd_currency' AND `meta_value`='" . $currency . "'", 'loadResult')) continue;

            $options .= '<option value="' . esc_attr($currency) . '" ' . ((isset($args['value']) and $args['value'] == $currency) ? 'selected="selected"' : '') . '>' . esc_html($symbol) . '</option>';
        }

        return '<select name="' . esc_attr($args['name']) . '" id="' . (isset($args['id']) ? esc_attr($args['id']) : '') . '" class="' . (isset($args['class']) ? esc_attr($args['class']) : 'lsd-currency') . '">
            ' . $options . '                       
        </select>';
    }

    public static function packages($args)
    {
        if (!count($args)) return false;
        if (!class_exists(\LSDPACSUB\Base::class)) return false;

        $options = '';
        $query = ['post_type' => \LSDPACSUB\Base::PTYPE_PACKAGE, 'posts_per_page' => '-1'];
        $packages = get_posts($query);

        // Show Empty Option
        if (isset($args['show_empty']) && $args['show_empty'])
        {
            $options .= '<option value="" ' . ((isset($args['value']) && esc_attr($args['value']) == '') ? 'selected="selected"' : '') . '>' . ((isset($args['empty_label']) && trim($args['empty_label'])) ? $args['empty_label'] : '-----') . '</option>';
        }

        foreach ($packages as $package) $options .= '<option value="' . esc_attr($package->ID) . '" ' . ((isset($args['value']) && $args['value'] == $package->ID) ? 'selected="selected"' : '') . '>' . esc_html($package->post_title) . '</option>';

        return '<select name="' . esc_attr($args['name']) . '" id="' . (isset($args['id']) ? esc_attr($args['id']) : '') . '" class="' . (isset($args['class']) ? esc_attr($args['class']) : 'lsd-package') . '">
            ' . $options . '                       
        </select>';
    }

    public static function users($args)
    {
        if (!count($args)) return false;

        $args['echo'] = 0;

        return wp_dropdown_users($args);
    }

    public static function rate($args)
    {
        if (!count($args)) return false;

        $stars = ((isset($args['stars']) and is_numeric($args['stars'])) ? $args['stars'] : 5);
        $value = ((isset($args['value']) and is_numeric($args['value'])) ? $args['value'] : 1);

        $output = '<div class="lsd-rate">';

        $output .= '<div class="lsd-rate-stars">';
        for ($i = 1; $i <= $stars; $i++) $output .= '<a href="#" data-rating-value="' . esc_attr($i) . '" data-rating-text="' . esc_attr($i) . '" class="' . ($value >= $i ? 'lsd-rate-selected' : '') . '"><i class="lsd-icon ' . ($value >= $i ? 'fas fa-star' : 'far fa-star') . '"></i></a>';
        $output .= '</div>';

        $output .= '<input type="hidden" name="' . esc_attr($args['name']) . '" value="' . esc_attr($value) . '" id="' . (isset($args['id']) ? esc_attr($args['id']) : '') . '" class="lsd-rate-input ' . (isset($args['class']) ? esc_attr($args['class']) : '') . '">';
        $output .= '</div>';

        return $output;
    }

    public static function autosuggest($args)
    {
        if (!count($args)) return false;

        $placeholder = isset($args['placeholder']) && trim($args['placeholder']) ? $args['placeholder'] : '';
        $name = isset($args['name']) && trim($args['name']) ? $args['name'] : 'lsd[team]';
        $id = isset($args['id']) && trim($args['id']) ? $args['id'] : 'lsd_autosuggest_append';
        $input_id = isset($args['input_id']) && trim($args['input_id']) ? $args['input_id'] : '';
        $suggestions = isset($args['suggestions']) && trim($args['suggestions']) ? $args['suggestions'] : 'lsd_autosuggest_suggestions';
        $min = isset($args['min-characters']) && is_numeric($args['min-characters']) ? (int) $args['min-characters'] : 3;
        $max_items = isset($args['max_items']) && is_numeric($args['max_items']) && $args['max_items'] > 0 ? (int) $args['max_items'] : null;
        $nonce = wp_create_nonce('lsd_autosuggest');

        $source = isset($args['source']) && trim($args['source']) ? $args['source'] : '';
        $toggle = isset($args['toggle']) && trim($args['toggle']) ? $args['toggle'] : '';
        $values = isset($args['values']) && is_array($args['values']) ? $args['values'] : [];

        $description = isset($args['description']) && trim($args['description']) ? $args['description'] : '';
        $description_class = isset($args['description_class']) && trim($args['description_class']) ? $args['description_class'] : '';

        $current = '';
        foreach ($values as $value)
        {
            if ($source === 'users')
            {
                $user = get_user_by('id', $value);
                $current .= '<span class="lsd-tooltip lsd-autosuggest-items-' . $user->ID . '" data-lsd-tooltip="' . esc_attr__('Click twice to delete', 'listdom') . '">' . $user->user_email . ' <i class="lsd-icon far fa-trash-alt" data-value="' . esc_attr($user->ID) . '" data-confirm="0"></i><input type="hidden" name="' . $name . '[]" value="' . $user->ID . '"></span>';
            }
            else if (in_array($source, get_taxonomies()))
            {
                $term = get_term($value);
                $current .= '<span class="lsd-tooltip lsd-autosuggest-items-' . esc_attr($term->term_id) . '" data-lsd-tooltip="' . esc_attr__('Click twice to delete', 'listdom') . '">' . esc_html($term->name) . ' <i class="lsd-icon far fa-trash-alt" data-value="' . esc_attr($term->term_id) . '" data-confirm="0"></i><input type="hidden" name="' . $name . '[]" value="' . esc_attr($term->term_id) . '"></span>';
            }
            else
            {
                $post = get_post($value);
                $current .= '<span class="lsd-tooltip lsd-autosuggest-items-' . $post->ID . '" data-lsd-tooltip="' . esc_attr__('Click twice to delete', 'listdom') . '">' . $post->post_title . ' <i class="lsd-icon far fa-trash-alt" data-value="' . esc_attr($post->ID) . '" data-confirm="0"></i><input type="hidden" name="' . $name . '[]" value="' . $post->ID . '"></span>';
            }
        }

        // Start Wrapper
        $output = '<div class="lsd-autosuggest-wrapper" data-toggle="' . esc_attr($toggle) . '">';

        // Input
        $output .= '<input type="text" id="' . esc_attr($input_id) . '" class="lsd-autosuggest" data-source="' . esc_attr($source) . '" data-name="' . esc_attr($name) . '" data-append="#' . esc_attr($id) . '" data-suggestions="#' . esc_attr($suggestions) . '" data-min-characters="' . esc_attr($min) . '" data-max-items="' . esc_attr($max_items) . '" data-nonce="' . esc_attr($nonce) . '" placeholder="' . esc_attr($placeholder) . '" autocomplete="off">';

        // Suggestions Placeholder
        $output .= '<div class="lsd-autosuggest-suggestions" id="' . esc_attr($suggestions) . '"></div>';

        // Current Items
        $output .= '<div class="lsd-autosuggest-current" id="' . esc_attr($id) . '">' . $current . '</div>';

        // Show Help
        if (trim($description)) $output .= '<p class="description '.$description_class.'">' . esc_html($description) . '</p>';

        // Close Wrapper
        $output .= '</div>';

        return $output;
    }

    public static function timepicker($args, $method = null)
    {
        if (!count($args)) return false;

        // Time Picker Method
        if (is_null($method))
        {
            $settings = LSD_Options::settings();
            $method = isset($settings['timepicker_format']) ? (int) $settings['timepicker_format'] : 24;
        }

        // Current Value
        $value = $args['value'] ?? [];
        $hour = $value['hour'] ?? 8;
        $minute = $value['minute'] ?? 0;

        $output = '<div class="lsd-row lsd-timepicker">';

        // Hour
        $output .= '<div class="lsd-col-6"><select name="' . esc_attr($args['name']) . '[hour]" title="' . esc_attr__('Hour', 'listdom') . '">';

        for ($h = 0; $h <= 23; $h++)
        {
            $label = sprintf("%02d", $h);
            if ($method === 12)
            {
                if ($h === 0) $label = sprintf(__('%d AM', 'listdom'), 12);
                else if ($h >= 1 && $h <= 11) $label = sprintf(__('%d AM', 'listdom'), $h);
                else if ($h === 12) $label = sprintf(__('%d PM', 'listdom'), 12);
                else if ($h >= 13) $label = sprintf(__('%d PM', 'listdom'), ($h - 12));
            }

            $output .= '<option value="' . esc_attr($h) . '" ' . ($hour == $h ? 'selected="selected"' : '') . '>' . $label . '</option>';
        }

        $output .= '</select></div>';

        // Minute
        $output .= '<div class="lsd-col-6"><select name="' . esc_attr($args['name']) . '[minute]" title="' . esc_attr__('Minute', 'listdom') . '" class="lsd-col-6">';

        for ($m = 0; $m <= 11; $m++)
        {
            $label = sprintf("%02d", ($m * 5));
            $output .= '<option value="' . esc_attr(($m * 5)) . '" ' . ($minute == ($m * 5) ? 'selected="selected"' : '') . '>' . $label . '</option>';
        }

        $output .= '</select></div>';

        // Close Wrapper
        $output .= '</div>';

        return $output;
    }

    public static function uploader($args)
    {
        if (!count($args)) return false;
        if (!current_user_can('upload_files')) return false;

        $name = $args['name'] ?? '';
        if (!trim($name)) return false;

        $value = isset($args['value']) ? trim($args['value'], ', ') : '';
        $values = explode(', ', $value);

        $preview_html = '';
        foreach ($values as $attach_id)
        {
            if (!trim($attach_id)) continue;

            $attachment = wp_get_attachment_url($attach_id);
            $preview_html .= '<a href="' . esc_url($attachment) . '" target="_blank">' . $attachment . '</a><br>';
        }

        $unique = $args['unique'] ?? LSD_id::code();
        $multiple = isset($args['multiple']) && $args['multiple'];
        $preview = isset($args['preview']) && $args['preview'];

        return '<div class="lsd-uploader" id="lsd_uploader_' . esc_attr($unique) . '_wrapper">
            <div class="lsd-form-row">
                <div class="lsd-col-12">
                    <input type="file" id="lsd_uploader_' . esc_attr($unique) . '" ' . ($multiple ? 'multiple' : '') . ' data-key="' . esc_attr($unique) . '" data-nonce="' . esc_attr(wp_create_nonce('lsd_uploader_' . $unique)) . '">
                    <div id="lsd_uploader_' . esc_attr($unique) . '_message"></div>
                </div>
            </div>
            <div class="lsd-form-row">
                <div class="lsd-col-12">
                    <input type="hidden" class="lsd-uploader-value" name="' . esc_attr($name) . '" value="' . esc_attr($value) . '">
                    ' . ($preview ? '<div class="lsd-uploader-preview">' . (trim($preview_html) ? $preview_html : '') . '</div>' : '') . '
                </div>
            </div>
        </div>';
    }

    public static function apis($args = [])
    {
        if (!count($args)) return false;

        // Get API Token
        $APIs = LSD_Options::api();

        $options = [];
        foreach ($APIs['tokens'] as $token) $options[$token['key']] = $token['name'];

        $args['options'] = $options;

        // Dropdown Field
        return self::select($args);
    }

    public static function franchise($args)
    {
        if (!count($args)) return false;

        return '<select name="' . esc_attr($args['name']) . '" id="' . (isset($args['id']) ? esc_attr($args['id']) : '') . '" class="' . (isset($args['class']) ? esc_attr($args['class']) : 'lsd-franchise-dropdown') . '">
            <option value="0" ' . ((isset($args['value']) and $args['value'] == '') ? 'selected="selected"' : '') . '>' . ((isset($args['empty_label']) and trim($args['empty_label'])) ? esc_html($args['empty_label']) : '-----') . '</option>
            ' . self::franchiseOptions($args) . '                    
        </select>';
    }

    protected static function franchiseOptions($args, $parent = 0, $prefix = ''): string
    {
        $tax_query = [];
        if (isset($args['category']) and trim($args['category']))
        {
            $tax_query[] = [
                'taxonomy' => LSD_Base::TAX_CATEGORY,
                'terms' => (int) $args['category'],
            ];
        }

        // Get Listings
        $listings = get_posts([
            'post_type' => LSD_Base::PTYPE_LISTING,
            'posts_per_page' => -1,
            'exclude' => $args['exclude'] ?? [],
            'meta_query' => [[
                'key' => 'lsd_parent',
                'value' => $parent,
            ]],
            'tax_query' => $tax_query,
        ]);

        $options = '';
        foreach ($listings as $listing)
        {
            $options .= '<option value="' . esc_attr($listing->ID) . '" ' . ((isset($args['value']) and $args['value'] == $listing->ID) ? 'selected="selected"' : '') . '>' . esc_html((trim($prefix) ? $prefix . ' ' : '')) . esc_html($listing->post_title) . '</option>';

            // Children
            if ((new LSD_Entity_Listing($listing))->has_child()) $options .= self::franchiseOptions($args, $listing->ID, $prefix . '-');
        }

        return $options;
    }

    public static function skins($args = [])
    {
        // Get Available Skins
        $skin = new LSD_Skins();
        $args['options'] = $skin->get_skins();

        // Dropdown Field
        return self::select($args);
    }

    public static function ai_providers($args = [])
    {
        // Get Available AI Providers
        $args['options'] = LSD_AI_Models::get_models();

        // Dropdown Field
        return self::select($args);
    }

    public static function ai_profiles($args = [])
    {
        // AI Options
        $ai = LSD_Options::ai();

        // AI Profiles
        $profiles = [];

        if (isset($ai['profiles']) && is_array($ai['profiles']))
        {
            foreach ($ai['profiles'] as $profile)
            {
                $profiles[$profile['id']] = $profile['name'] ?? 'N/A';
            }
        }

        // AI Profiles
        $args['options'] = $profiles;

        // Dropdown Field
        return self::select($args);
    }

    public static function border(array $args): string
    {
        $id = $args['id'] ?? 'lsd_border';
        $name = $args['name'] ?? 'border';
        $value = isset($args['value']) && is_array($args['value']) ? $args['value'] : [];

        return '<div class="lsd-border-wrapper">
            <div class="lsd-border-width-wrapper lsd-flex-3">
                <div class="lsd-border-width-top">
                    '.LSD_Form::label([
                        'title' => esc_html__('Border Top', 'listdom'),
                        'for' => $id.'_top'
                    ]).'
                    '.LSD_Form::number([
                        'id' => $id.'_top',
                        'name' => $name.'[top]',
                        'placeholder' => esc_html__('Top', 'listdom'),
                        'attributes' => [
                            'min' => 0,
                            'max' => 10,
                            'increment' => 1
                        ],
                        'value' => $value['top'] ?? 0
                    ]).'
                </div>
                <div class="lsd-border-width-right">
                    '.LSD_Form::label([
                        'title' => esc_html__('Border Right', 'listdom'),
                        'for' => $id.'_right'
                    ]).'
                    '.LSD_Form::number([
                        'id' => $id.'_right',
                        'name' => $name.'[right]',
                        'placeholder' => esc_html__('Right', 'listdom'),
                        'attributes' => [
                            'min' => 0,
                            'max' => 10,
                            'increment' => 1
                        ],
                        'value' => $value['right'] ?? 0
                    ]).'
                </div>
                <div class="lsd-border-width-bottom">
                    '.LSD_Form::label([
                        'title' => esc_html__('Border Bottom', 'listdom'),
                        'for' => $id.'_bottom'
                    ]).'
                    '.LSD_Form::number([
                        'id' => $id.'_bottom',
                        'name' => $name.'[bottom]',
                        'placeholder' => esc_html__('Bottom', 'listdom'),
                        'attributes' => [
                            'min' => 0,
                            'max' => 10,
                            'increment' => 1
                        ],
                        'value' => $value['bottom'] ?? 0
                    ]).'
                </div>
                <div class="lsd-border-width-left">
                    '.LSD_Form::label([
                        'title' => esc_html__('Border Left', 'listdom'),
                        'for' => $id.'_left'
                    ]).'
                    '.LSD_Form::number([
                        'id' => $id.'_left',
                        'name' => $name.'[left]',
                        'placeholder' => esc_html__('Left', 'listdom'),
                        'attributes' => [
                            'min' => 0,
                            'max' => 10,
                            'increment' => 1
                        ],
                        'value' => $value['left'] ?? 0
                    ]).'
                </div>
            </div>
            <div class="lsd-border-color-wrapper lsd-flex-1">
                '.LSD_Form::label([
                    'title' => esc_html__('Border Color', 'listdom'),
                    'for' => $id.'_color'
                ]).'
                '.LSD_Form::colorpicker([
                    'id' => $id.'_color',
                    'name' => $name.'[color]',
                    'value' => $value['color'] ?? ''
                ]).'
            </div>
            <div class="lsd-border-radius-wrapper lsd-flex-1">
                '.LSD_Form::label([
                    'title' => esc_html__('Border Radius', 'listdom'),
                    'for' => $id.'_radius'
                ]).'
                '.LSD_Form::number([
                    'id' => $id.'_radius',
                    'name' => $name.'[radius]',
                    'placeholder' => esc_html__('Radius', 'listdom'),
                    'attributes' => [
                        'min' => 0,
                        'max' => 50,
                        'increment' => 1
                    ],
                    'value' => $value['radius'] ?? 0
                ]).'
            </div>
            <div class="lsd-border-style-wrapper lsd-flex-1">
                '.LSD_Form::label([
                    'title' => esc_html__('Border Style', 'listdom'),
                    'for' => $id.'_style'
                ]).'
                '.LSD_Form::select([
                    'id' => $id.'_style',
                    'name' => $name.'[style]',
                    'options' => [
                        'none' => esc_html__('None', 'listdom'),
                        'solid' => esc_html__('Solid', 'listdom'),
                        'dashed' => esc_html__('Dashed', 'listdom'),
                        'dotted' => esc_html__('Dotted', 'listdom'),
                        'double' => esc_html__('Double', 'listdom'),
                    ],
                    'value' => $value['style'] ?? 'none'
                ]).'
            </div>
        </div>';
    }

    public static function padding(array $args): string
    {
        $id = $args['id'] ?? 'lsd_padding';
        $name = $args['name'] ?? 'padding';
        $value = isset($args['value']) && is_array($args['value']) ? $args['value'] : [];

        return '<div class="lsd-padding-wrapper">
            <div class="lsd-padding-width-wrapper lsd-flex-1">
                <div class="lsd-padding-top">
                    '.LSD_Form::label([
                        'title' => esc_html__('Padding Top', 'listdom'),
                        'for' => $id.'_top'
                    ]).'
                    '.LSD_Form::number([
                        'id' => $id.'_top',
                        'name' => $name.'[top]',
                        'placeholder' => esc_html__('Top', 'listdom'),
                        'attributes' => [
                            'min' => 0,
                            'increment' => 1
                        ],
                        'value' => $value['top'] ?? 0
                    ]).'
                </div>
                <div class="lsd-padding-right">
                    '.LSD_Form::label([
                        'title' => esc_html__('Padding Right', 'listdom'),
                        'for' => $id.'_right'
                    ]).'
                    '.LSD_Form::number([
                        'id' => $id.'_right',
                        'name' => $name.'[right]',
                        'placeholder' => esc_html__('Right', 'listdom'),
                        'attributes' => [
                            'min' => 0,
                            'increment' => 1
                        ],
                        'value' => $value['right'] ?? 0
                    ]).'
                </div>
                <div class="lsd-padding-bottom">
                    '.LSD_Form::label([
                        'title' => esc_html__('Padding Bottom', 'listdom'),
                        'for' => $id.'_bottom'
                    ]).'
                    '.LSD_Form::number([
                        'id' => $id.'_bottom',
                        'name' => $name.'[bottom]',
                        'placeholder' => esc_html__('Bottom', 'listdom'),
                        'attributes' => [
                            'min' => 0,
                            'increment' => 1
                        ],
                        'value' => $value['bottom'] ?? 0
                    ]).'
                </div>
                <div class="lsd-padding-left">
                    '.LSD_Form::label([
                        'title' => esc_html__('Padding Left', 'listdom'),
                        'for' => $id.'_left'
                    ]).'
                    '.LSD_Form::number([
                        'id' => $id.'_left',
                        'name' => $name.'[left]',
                        'placeholder' => esc_html__('Left', 'listdom'),
                        'attributes' => [
                            'min' => 0,
                            'increment' => 1
                        ],
                        'value' => $value['left'] ?? 0
                    ]).'
                </div>
            </div>
        </div>';
    }

    public static function typography(array $args): string
    {
        $id = $args['id'] ?? 'lsd_typography';
        $name = $args['name'] ?? 'typography';
        $include = isset($args['include']) && is_array($args['include']) ? $args['include'] : [];
        $value = isset($args['value']) && is_array($args['value']) ? $args['value'] : [];

        return '<div class="lsd-typography-wrapper">
            '.(!$include || in_array('family', $include) ? '<div class="lsd-typography-family-wrapper lsd-flex-2">
                '.LSD_Form::label([
                    'title' => esc_html__('Font Family', 'listdom'),
                    'for' => $id.'_family'
                ]).'
                '.LSD_Form::fontpicker([
                    'id' => $id.'_family',
                    'name' => $name.'[family]',
                    'value' => $value['family'] ?? 'inherit',
                    'show_inherit' => true,
                ]).'
            </div>' : '' ).'
            '.(!$include || in_array('weight', $include) ? '<div class="lsd-typography-weight-wrapper lsd-flex-2">
                '.LSD_Form::label([
                    'title' => esc_html__('Font Weight', 'listdom'),
                    'for' => $id.'_weight'
                ]).'
                '.LSD_Form::select([
                    'id' => $id.'_weight',
                    'name' => $name.'[weight]',
                    'options' => [
                        'inherit' => esc_html__('Inherit', 'listdom'),
                        '100' => esc_html__('100 (Thin)', 'listdom'),
                        '200' => esc_html__('200 (Extra Light)', 'listdom'),
                        '300' => esc_html__('300 (Light)', 'listdom'),
                        '400' => esc_html__('400 (Regular)', 'listdom'),
                        '500' => esc_html__('500 (Medium)', 'listdom'),
                        '600' => esc_html__('600 (Semi-Bold)', 'listdom'),
                        '700' => esc_html__('700 (Bold)', 'listdom'),
                        '800' => esc_html__('800 (Extra Bold)', 'listdom'),
                        '900' => esc_html__('900 (Black)', 'listdom'),
                    ],
                    'value' => $value['weight'] ?? 'inherit'
                ]).'
            </div>' : '' ).'
            '.(!$include || in_array('align', $include) ? '<div class="lsd-typography-align-wrapper lsd-flex-1">
                '.LSD_Form::label([
                    'title' => esc_html__('Text Align', 'listdom'),
                    'for' => $id.'_align'
                ]).'
                '.LSD_Form::select([
                    'id' => $id.'_align',
                    'name' => $name.'[align]',
                    'options' => [
                        'inherit' => esc_html__('Inherit', 'listdom'),
                        'left' => esc_html__('Left', 'listdom'),
                        'right' => esc_html__('Right', 'listdom'),
                        'center' => esc_html__('Center', 'listdom'),
                        'justify' => esc_html__('Justify', 'listdom'),
                        'initial' => esc_html__('Initial', 'listdom'),
                    ],
                    'value' => $value['align'] ?? 'inherit'
                ]).'
            </div>' : '' ).'
            '.(!$include || in_array('size', $include) ? '<div class="lsd-typography-size-wrapper lsd-flex-1">
                '.LSD_Form::label([
                    'title' => esc_html__('Size', 'listdom'),
                    'for' => $id.'_size'
                ]).'
                '.LSD_Form::number([
                    'id' => $id.'_size',
                    'name' => $name.'[size]',
                    'placeholder' => esc_html__('Size', 'listdom'),
                    'attributes' => [
                        'min' => 0,
                        'max' => 80,
                        'increment' => 1
                    ],
                    'value' => $value['size'] ?? ''
                ]).'
            </div>' : '' ).'
            '.(!$include || in_array('line_height', $include) ? '<div class="lsd-typography-line-height-wrapper lsd-flex-1">
                '.LSD_Form::label([
                    'title' => esc_html__('Line Height', 'listdom'),
                    'for' => $id.'_line_height'
                ]).'
                '.LSD_Form::number([
                    'id' => $id.'_line_height',
                    'name' => $name.'[line_height]',
                    'placeholder' => esc_html__('Line Height', 'listdom'),
                    'attributes' => [
                        'min' => 0,
                        'max' => 120,
                        'increment' => 1
                    ],
                    'value' => $value['line_height'] ?? ''
                ]).'
            </div>' : '' ).'
        </div>';
    }

    public static function submit($args = []): string
    {
        return '<button type="submit" id="' . (isset($args['id']) ? esc_attr($args['id']) : '') . '" class="' . (isset($args['class']) ? esc_attr($args['class']) : 'button button-primary') . '">' . esc_html($args['label']) . '</button>';
    }

    public static function nonce($action, $name = '_wpnonce')
    {
        wp_nonce_field($action, $name);
    }
}
