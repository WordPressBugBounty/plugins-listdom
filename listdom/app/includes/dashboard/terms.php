<?php

class LSD_Dashboard_Terms extends LSD_Base
{
    public static function category($args)
    {
        $output = LSD_Dashboard_Terms::taxonomy($args);

        // Apply Filters
        return apply_filters('lsd_dashboard_category_options', $output, $args);
    }

    public static function taxonomy(array $args): string
    {
        $tax_key = $args['key'] ?? $args['taxonomy'];

        // Field Method (frontend only)
        $method = 'checkboxes';
        $allow_frontend_setting = !is_admin();
        if (!$allow_frontend_setting && function_exists('wp_doing_ajax') && wp_doing_ajax())
        {
            $action = isset($_REQUEST['action']) ? sanitize_text_field(wp_unslash($_REQUEST['action'])) : '';
            $allow_frontend_setting = $action === 'lsd_refresh_additional_categories';
        }

        if ($allow_frontend_setting)
        {
            $settings = LSD_Options::settings();
            $setting_key = 'submission_tax_' . $tax_key . '_method';
            $method = isset($settings[$setting_key]) && trim($settings[$setting_key]) ? $settings[$setting_key] : 'checkboxes';
        }

        // Dropdown
        if ($method === 'dropdown')
        {
            if ($args['taxonomy'] !== LSD_Base::TAX_CATEGORY || $tax_key === 'additional_' . LSD_Base::TAX_CATEGORY)
            {
                $args['multiple'] = true;
                $args['selected'] = isset($args['post_id']) ? wp_get_post_terms($args['post_id'], $args['taxonomy'], ['fields' => 'ids']) : [];
                $args['class'] = 'lsd-select-multiple';
            }

            return LSD_Dashboard_Terms::dropdown($args);
        }
        // Textarea Input
        else if ($method === 'textarea')
        {
            return LSD_Dashboard_Terms::textarea($args);
        }

        // Checkbox
        return LSD_Dashboard_Terms::checkboxes($args);
    }

    public static function locations($args)
    {
        $output = self::taxonomy($args);

        // Apply Filters
        return apply_filters('lsd_dashboard_locations_options', $output, $args);
    }

    public static function tags($args)
    {
        $output = self::taxonomy($args);

        // Apply Filters
        return apply_filters('lsd_dashboard_tags_options', $output, $args);
    }

    public static function features($args)
    {
        $output = self::taxonomy($args);

        // Apply Filters
        return apply_filters('lsd_dashboard_features_options', $output, $args);
    }

    public static function labels($args)
    {
        $output = LSD_Dashboard_Terms::checkboxes($args);

        // Apply Filters
        return apply_filters('lsd_dashboard_labels_options', $output, $args);
    }

    public static function checkboxes($args, $selected = null): string
    {
        $a = $args;
        foreach (['id', 'name', 'post_id', 'class'] as $key) if (isset($a[$key])) unset($a[$key]);

        // Field Name
        $name = $args['name'] ?? '';
        $pre = $args['pre'] ?? '-';

        // Current Values
        if (is_null($selected))
        {
            if (array_key_exists('selected', $args)) $selected = $args['selected'];
            else $selected = isset($args['post_id']) ? wp_get_post_terms($args['post_id'], $args['taxonomy'], ['fields' => 'ids']) : [];

            if (is_wp_error($selected)) $selected = [];
        }

        if (!is_array($selected)) $selected = (string) $selected !== '' ? [(int) $selected] : [];

        // Make sure all selected values are int
        $selected = array_values(array_filter(array_map('intval', $selected)));

        // Available Terms
        $terms = self::terms($a);

        $prefix = str_repeat($pre, (int) ($args['level'] ?? 0));
        $output = '<ul class="' . ((int) ($args['level'] ?? 0) > 0 ? 'lsd-children ' : '') . LSD_Base::get_lsd_class('taxonomies-checkboxes') . '">';

        foreach ($terms as $term)
        {
            $term_id = (int) $term->term_id;

            $output .= '<li>';
            $output .= '<label class="selectit lsd-fields-label">';
            $output .= '<input value="' . esc_attr($term_id) . '" type="checkbox" name="' . esc_attr($name) . '[]" id="in-listdom-location-' . esc_attr($term_id) . '" ' . (in_array($term_id, $selected, true) ? 'checked="checked"' : '') . '> ';
            $output .= esc_html(($prefix . (trim($prefix) ? ' ' : '') . $term->name));
            $output .= '</label>';

            $children = get_term_children($term_id, $args['taxonomy']);
            if (is_array($children) && count($children))
            {
                $child_args = $args;
                $child_args['parent'] = $term_id;
                $child_args['level'] = ((int) ($child_args['level'] ?? 0)) + 1;

                $output .= self::checkboxes($child_args, $selected);
            }

            $output .= '</li>';
        }

        $output .= '</ul>';
        return $output;
    }

    public static function dropdown($args = []): string
    {
        $a = $args;
        foreach (['id', 'name', 'selected', 'class'] as $key) if (isset($a[$key])) unset($a[$key]);

        $name = $args['name'] ?? '';
        $id = $args['id'] ?? '';
        $class = $args['class'] ?? 'postform';
        $selected = $args['selected'] ?? '';
        $multiple = isset($args['multiple']) && $args['multiple'];
        $none = isset($args['none']) && $args['none'];
        $none_label = $args['none_label'] ?? '-----';
        $required = $args['required'] ?? false;

        $output = '<select name="' . esc_attr($name) . ($multiple ? '[]' : '') . '" id="' . esc_attr($id) . '" class="' . (is_admin() ? 'lsd-admin-input ' : '') . 'lsd-fd-taxonomies-dropdown ' . esc_attr($class) . '" ' . ($multiple ? 'multiple' : '') . ' ' . ($required ? 'required' : '') . '>';
        if ($none) $output .= '<option class="level-0" value="">' . $none_label . '</option>';

        $parent = isset($args['parent']) ? (int) $args['parent'] : 0;
        $output .= self::options($a, $parent, $selected);
        $output .= '</select>';

        return $output;
    }

    public static function textarea($args = []): string
    {
        $name = $args['name'] ?? '';
        $id = $args['id'] ?? '';
        $placeholder = $args['placeholder'] ?? '';
        $class = $args['class'] ?? 'postform';
        $rows = $args['rows'] ?? 3;
        $required = isset($args['required']) && $args['required'];

        $selected = isset($args['selected']) && is_string($args['selected'])
            ? stripslashes(trim($args['selected'], ', '))
            : '';

        return '<textarea
            name="' . esc_attr($name) . '"
            id="' . esc_attr($id) . '"
            class="' . esc_attr($class) . '"
            rows="' . esc_attr($rows) . '" ' . ($required ? 'required' : '') . '
            placeholder="' . esc_attr($placeholder) . '"
        >' . esc_textarea($selected) . '</textarea>';
    }

    public static function options($args, $parent = 0, $selected = null, $level = 0): string
    {
        $args['parent'] = $parent;
        $terms = self::terms($args);

        $prefix = str_repeat('-', $level);
        $output = '';
        foreach ($terms as $term)
        {
            $output .= '<option class="level-' . esc_attr($level) . '" value="' . esc_attr($term->term_id) . '" ' . (((is_array($selected) and in_array($term->term_id, $selected)) or (!is_array($selected) and $selected == $term->term_id)) ? 'selected="selected"' : '') . '>' . esc_html(($prefix . (trim($prefix) ? ' ' : '') . $term->name)) . '</option>';

            $children = get_term_children($term->term_id, $args['taxonomy']);
            if (is_array($children) and count($children))
            {
                $output .= self::options($args, $term->term_id, $selected, $level + 1);
            }
        }

        return $output;
    }

    public static function terms($args = [])
    {
        // Get Terms
        $terms = get_terms($args);

        // Do not filter (Used in package add / edit page in backend)
        if (isset($args['lsd_no_filter']) and $args['lsd_no_filter']) return $terms;

        // Apply Filters
        return apply_filters('lsd_dashboard_terms', $terms, $args);
    }
}
