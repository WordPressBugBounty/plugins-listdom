<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom Search Helper Class.
 *
 * @class LSD_Search_Helper
 * @version    1.0.0
 */
class LSD_Search_Helper extends LSD_Base
{
    /**
     * Constructor method
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function get_type_by_key($key)
    {
        if (in_array($key, $this->taxonomies())) return 'taxonomy';
        else if (strpos($key, 'att') !== false)
        {
            $ex = explode('-', $key);
            return get_term_meta($ex[1], 'lsd_field_type', true);
        }
        else if ($key == 'price') return 'price';
        else if ($key == 'class') return 'class';
        else if ($key == 'address') return 'address';
        else if ($key == 'period') return 'period';
        else if (in_array($key, ['adults', 'children'])) return 'numeric';

        return 'textsearch';
    }

    public function get_terms_hierarchy(array &$elements, $parent_id = 0): array
    {
        $branch = [];
        foreach ($elements as $key => $element)
        {
            if ($element['parent'] == $parent_id)
            {
                $children = $this->get_terms_hierarchy($elements, $key);
                if ($children) $element['children'] = $children;

                $branch[$key] = $element;
                unset($elements[$key]);
            }
        }

        return $branch;
    }

    public function get_terms($filter, $numeric = false, $hierarchy = false): array
    {
        $key = $filter['key'] ?? null;
        if (in_array($key, $this->taxonomies()))
        {
            $results = get_terms([
                'taxonomy' => $key,
                'hide_empty' => $filter['hide_empty'] ?? 0,
                'orderby' => 'name',
                'order' => 'ASC',
            ]);

            $terms = [];
            foreach ($results as $result) $terms[$result->term_id] = $hierarchy ? ['name' => $result->name, 'parent' => $result->parent] : $result->name;

            return $hierarchy ? $this->get_terms_hierarchy($terms) : $terms;
        }
        else if (strpos($key, 'att') !== false)
        {
            $ex = explode('-', $key);
            $hide_empty = $filter['hide_empty'] ?? 1;

            if ($hide_empty)
            {
                $order = "`meta_value`";
                if ($numeric) $order = "CAST(`meta_value` as unsigned)";

                $db = new LSD_db();
                $results = $db->select("SELECT `meta_value` FROM `#__postmeta` WHERE `meta_key`='lsd_attribute_" . esc_sql($ex[1]) . "' AND `meta_value`!='' GROUP BY `meta_value` ORDER BY " . $order . " ASC", 'loadColumn');
            }
            else
            {
                $values_str = get_term_meta($ex[1], 'lsd_values', true);
                $results = explode(',', trim($values_str, ', '));
            }

            $terms = [];
            foreach ($results as $result) $terms[$result] = $result;

            return $terms;
        }

        return [];
    }

    public function get_term_id($taxonomy, $name)
    {
        $names = explode(',', $name);
        $ids = '';

        foreach ($names as $name)
        {
            $term = get_term_by('name', trim($name), $taxonomy);
            $ids .= (isset($term->term_id) ? $term->term_id . ',' : '');
        }

        $ids = trim($ids, ', ');
        return ((strpos($ids, ',') === false) ? (int) $ids : $ids);
    }

    public function column($count, $buttons = false): array
    {
        if ($count <= 1)
        {
            $field_column = $buttons ? 'lsd-col-10' : 'lsd-col-12';
            $button_column = 'lsd-col-2';
        }
        else if ($count == 2)
        {
            $field_column = $buttons ? 'lsd-col-5' : 'lsd-col-6';
            $button_column = 'lsd-col-2';
        }
        else if ($count == 3)
        {
            $field_column = $buttons ? 'lsd-col-3' : 'lsd-col-4';
            $button_column = 'lsd-col-3';
        }
        else if ($count == 4)
        {
            $field_column = $buttons ? 'lsd-col-2' : 'lsd-col-3';
            $button_column = 'lsd-col-4';
        }
        else
        {
            $field_column = 'lsd-col-2';
            $button_column = 'lsd-col-2';
        }

        return [$field_column, $button_column];
    }

    public function dropdown($filter, $args = []): string
    {
        $key = $filter['key'] ?? '';
        $title = $filter['title'] ?? '';
        $placeholder = isset($filter['placeholder']) && trim($filter['placeholder']) ? $filter['placeholder'] : $title;

        $id = $args['id'] ?? null;
        $name = $args['name'] ?? 'sf-' . $key;
        $current = $args['current'] ?? null;

        $output = '<select class="' . esc_attr($key) . '" name="' . esc_attr($name) . '" id="' . esc_attr($id) . '" placeholder="' . esc_attr__($placeholder, 'listdom') . '">';
        $output .= '<option value="">' . esc_html__($placeholder, 'listdom') . '</option>';
        $output .= $this->dropdown_options($filter, 0, $current);
        $output .= '</select>';

        return $output;
    }

    public function dropdown_options($filter, $parent = 0, $current = null, $level = 0): string
    {
        $key = $filter['key'] ?? null;

        $all_terms = $filter['all_terms'] ?? 1;
        $predefined_terms = isset($filter['terms']) && is_array($filter['terms']) ? $filter['terms'] : [];

        $terms = get_terms([
            'taxonomy' => $key,
            'hide_empty' => $filter['hide_empty'] ?? null,
            'parent' => $parent,
            'orderby' => 'name',
            'order' => 'ASC',
        ]);

        $prefix = str_repeat('-', $level);

        $output = '';
        foreach ($terms as $term)
        {
            // Term is not in the predefined terms
            if (!$all_terms and count($predefined_terms) and !isset($predefined_terms[$term->term_id])) continue;

            $output .= '<option class="level-' . esc_attr($level) . '" value="' . esc_attr($term->term_id) . '" ' . ($current == $term->term_id ? 'selected="selected"' : '') . '>' . esc_html(($prefix . (trim($prefix) ? ' ' : '') . $term->name)) . '</option>';

            $children = get_term_children($term->term_id, $key);
            if (is_array($children) and count($children))
            {
                $output .= $this->dropdown_options($filter, $term->term_id, $current, $level + 1);
            }
        }

        return $output;
    }

    public function hierarchical($filter, $args = []): string
    {
        $key = $filter['key'] ?? '';
        $title = $filter['title'] ?? '';
        $ph = isset($filter['placeholder']) && trim($filter['placeholder']) ? $filter['placeholder'] : $title;
        $placeholders = explode(',', $ph);

        $all_terms = $filter['all_terms'] ?? 1;
        $predefined_terms = isset($filter['terms']) && is_array($filter['terms']) ? $filter['terms'] : [];
        $hide_empty = $filter['hide_empty'] ?? 0;

        $id = $args['id'] ?? null;
        $name = $args['name'] ?? 'sf-' . $key;
        $current = $args['current'] ?? null;

        $max_levels = 1;
        $current_parents = $current ? LSD_Taxonomies::parents(get_term($current)) : [];
        $available_parents_for_childs = $current ? array_merge($current_parents, [$current]) : [];

        $terms = get_terms([
            'taxonomy' => $key,
            'hide_empty' => $hide_empty,
            'orderby' => 'name',
            'order' => 'ASC',
        ]);

        $hierarchy = [];
        foreach ($terms as $term)
        {
            // Term is not in the predefined terms
            if (!$all_terms and count($predefined_terms) and !isset($predefined_terms[$term->term_id])) continue;

            $level = count(LSD_Taxonomies::parents($term)) + 1;
            $max_levels = max($max_levels, $level);

            // Term is not child of current parents
            if ($current and count($available_parents_for_childs) and $term->parent != 0 and $term->term_id != $current and !in_array($term->parent, $available_parents_for_childs))
            {
                continue;
            }

            if (!isset($hierarchy[$level])) $hierarchy[$level] = [];
            $hierarchy[$level][] = $term;
        }

        $output = '<div class="lsd-hierarchical-dropdowns" id="' . esc_attr($id) . '_wrapper" data-for="' . esc_attr($key) . '" data-id="' . esc_attr($id) . '" data-max-levels="' . esc_attr($max_levels) . '" data-name="' . esc_attr($name) . '" data-hide-empty="' . esc_attr($hide_empty) . '">';
        for ($l = 1; $l <= $max_levels; $l++)
        {
            $level_terms = (isset($hierarchy[$l]) and is_array($hierarchy[$l])) ? $hierarchy[$l] : [];
            $placeholder = $placeholders[($l - 1)] ?? $placeholders[0];

            $output .= '<select class="' . esc_attr($key) . '" name="' . esc_attr($name) . '" id="' . esc_attr($id . '_' . $l) . '" placeholder="' . esc_attr__($placeholder, 'listdom') . '" data-level="' . esc_attr($l) . '">';
            $output .= '<option value="">' . esc_html__($placeholder, 'listdom') . '</option>';

            foreach ($level_terms as $level_term) $output .= '<option class="lsd-option lsd-parent-' . esc_attr($level_term->parent) . '" value="' . esc_attr($level_term->term_id) . '" ' . (($current == $level_term->term_id or in_array($level_term->term_id, $current_parents)) ? 'selected="selected"' : '') . '>' . esc_html($level_term->name) . '</option>';
            $output .= '</select>';
        }

        $output .= '</div>';
        return $output;
    }
}
