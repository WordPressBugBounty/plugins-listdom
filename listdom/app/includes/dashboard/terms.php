<?php

class LSD_Dashboard_Terms extends LSD_Base
{
    public static function category($args)
    {
        $output = LSD_Dashboard_Terms::dropdown($args);

        // Apply Filters
        return apply_filters('lsd_dashboard_category_options', $output, $args);
    }

    public static function taxonomy(array $args): string
    {
        // Settings
        $settings = LSD_Options::settings();

        // Field Method
        $method = isset($settings['submission_tax_' . $args['taxonomy'] . '_method']) && trim($settings['submission_tax_' . $args['taxonomy'] . '_method']) ? $settings['submission_tax_' . $args['taxonomy'] . '_method'] : 'checkboxes';

        // Dropdown
        if ($method === 'dropdown')
        {
            $args['multiple'] = true;
            $args['selected'] = isset($args['post_id']) ? wp_get_post_terms($args['post_id'], $args['taxonomy'], ['fields' => 'ids']) : [];
            $args['class'] = 'lsd-select-multiple';

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
        if (is_null($selected)) $selected = isset($args['post_id']) ? wp_get_post_terms($args['post_id'], $args['taxonomy'], ['fields' => 'ids']) : [];

        // Available Terms
        $terms = self::terms($a);

        $prefix = str_repeat($pre, $args['level']);
        $output = '<ul ' . ($args['level'] > 0 ? 'class="lsd-children"' : '') . '>';
        foreach ($terms as $term)
        {
            $output .= '<li>';
            $output .= '<label class="selectit"><input value="' . esc_attr($term->term_id) . '" type="checkbox" name="' . esc_attr($name) . '[]" id="in-listdom-location-' . esc_attr($term->term_id) . '" ' . (in_array($term->term_id, $selected) ? 'checked="checked"' : '') . '> ' . esc_html(($prefix . (trim($prefix) ? ' ' : '') . $term->name)) . '</label>';

            $children = get_term_children($term->term_id, $args['taxonomy']);
            if (is_array($children) and count($children))
            {
                $a = $args;
                $a['parent'] = $term->term_id;
                $a['level'] = $a['level'] + 1;

                $output .= self::checkboxes($a, $selected);
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

        $output = '<select name="' . esc_attr($name) . ($multiple ? '[]' : '') . '" id="' . esc_attr($id) . '" class="' . esc_attr($class) . '" ' . ($multiple ? 'multiple' : '') . ' ' . ($required ? 'required' : '') . '>';
        if ($none) $output .= '<option class="level-0" value="">' . $none_label . '</option>';

        $output .= self::options($a, 0, $selected);
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
