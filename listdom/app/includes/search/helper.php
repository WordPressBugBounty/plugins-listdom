<?php

class LSD_Search_Helper extends LSD_Base
{
    public static function get_search_fields(int $search_form_id, string $device = 'desktop'): array
    {
        // Desktop Fields
        $search_fields = get_post_meta($search_form_id, 'lsd_fields', true);

        $devices = get_post_meta($search_form_id, 'lsd_devices', true);
        if (!is_array($devices)) $devices = [];

        // Tablet Fields
        if ($device === 'tablet' && isset($devices['tablet']['inherit']) && !$devices['tablet']['inherit'])
        {
            $search_fields = get_post_meta($search_form_id, 'lsd_tablet', true);
        }
        // Mobile Fields
        else if ($device === 'mobile' && isset($devices['mobile']['inherit']) && !$devices['mobile']['inherit'])
        {
            $search_fields = get_post_meta($search_form_id, 'lsd_mobile', true);
        }

        return !is_array($search_fields) ? [] : $search_fields;
    }

    public function get_type_by_key($key)
    {
        // Taxonomy
        if (in_array($key, $this->taxonomies())) return 'taxonomy';
        // Attributes
        else if (strpos($key, 'att-') !== false)
        {
            $id = LSD_Main::get_attr_id(substr($key, 4));

            return get_term_meta($id, 'lsd_field_type', true);
        }
        // Meta Fields
        else if ($key == 'price') return 'price';
        else if ($key == 'class') return 'class';
        else if ($key == 'address') return 'address';
        else if ($key == 'period') return 'period';
        else if (in_array($key, ['adults', 'children'])) return 'numeric';
        // ACF
        else if (strpos($key, 'acf_number_') === 0) return 'number';
        else if (strpos($key, 'acf_text_') === 0 || strpos($key, 'acf_email_') === 0) return 'text';
        else if (strpos($key, 'acf_select_') === 0 || strpos($key, 'acf_radio_') === 0 || strpos($key, 'acf_checkbox_') === 0) return 'acf_dropdown';
        else if (strpos($key, 'acf_range_') === 0) return 'acf_range';
        else if (strpos($key, 'acf_true_false_') === 0) return 'acf_true_false';

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

    public function acf_get_values($name): array
    {
        $acf_name = substr(substr($name, 0, -4), 7);
        $args = [
            'post_type' => LSD_Base::PTYPE_LISTING,
            'posts_per_page' => -1,
            'meta_query' => [
                [
                    'key' => $acf_name,
                    'compare' => 'EXISTS',
                ],
            ],
        ];

        $query = new WP_Query($args);
        if ($query->have_posts())
        {
            $values = [];
            while ($query->have_posts())
            {
                $query->the_post();
                $value = get_field($acf_name);

                if (!empty($value)) $values[] = $value;
            }

            wp_reset_postdata();
            return array_values(array_unique($values));
        }

        return [];
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
        else if (strpos($key, 'att-') !== false)
        {
            $slug = substr($key, 4);
            $hide_empty = $filter['hide_empty'] ?? 1;

            if ($hide_empty)
            {
                $order = "`meta_value`";
                if ($numeric) $order = "CAST(`meta_value` as unsigned)";

                $db = new LSD_db();
                $raw_results = $db->select("SELECT `meta_value` FROM `#__postmeta` WHERE `meta_key`='lsd_attribute_" . esc_sql($slug) . "' AND `meta_value`!='' GROUP BY `meta_value` ORDER BY " . $order . " ASC", 'loadColumn');

                $results = [];
                foreach ($raw_results as $value)
                {
                    if (strpos($value, ',') !== false)
                    {
                        $parts = array_map('trim', explode(',', $value));
                        foreach ($parts as $part) if ($part !== '') $results[] = $part;
                    }
                    else
                    {
                        $value = trim($value);
                        if ($value !== '') $results[] = $value;
                    }
                }
            }
            else
            {
                $id = LSD_Main::get_attr_id($slug);

                $values_str = get_term_meta($id, 'lsd_values', true);
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
            $field_column = $buttons ? 'lsd-col-span-10' : 'lsd-col-span-12';
            $button_column = 'lsd-col-span-2';
        }
        else if ($count == 2)
        {
            $field_column = $buttons ? 'lsd-col-span-5' : 'lsd-col-span-6';
            $button_column = 'lsd-col-span-2';
        }
        else if ($count == 3)
        {
            $field_column = $buttons ? 'lsd-col-span-3' : 'lsd-col-span-4';
            $button_column = 'lsd-col-span-3';
        }
        else if ($count == 4)
        {
            $field_column = $buttons ? 'lsd-col-span-2' : 'lsd-col-span-3';
            $button_column = 'lsd-col-span-4';
        }
        else
        {
            $field_column = 'lsd-col-span-2';
            $button_column = 'lsd-col-span-2';
        }

        return [$field_column, $button_column];
    }

    public function text($filter, $args = []): string
    {
        $key = $filter['key'] ?? '';
        $title = $args['title'] ?? '';
        $placeholder = isset($filter['placeholder']) && trim($filter['placeholder']) ? $filter['placeholder'] : $title;
        $id = $args['id'] ?? 'lsd_search_text_' . $key;
        $name = $args['name'] ?? 'sf-' . $key . '-lk';
        $current = $args['current'] ?? '';

        $output = '<label for="' . esc_attr($id) . '">' . esc_html($title) . '</label>';
        $output .= '<input type="text" name="' . esc_attr($name) . '" id="' . esc_attr($id) . '" placeholder="' . esc_attr($placeholder) . '" value="' . esc_attr($current) . '">';

        return $output;
    }

    public function number($filter, $args = []): string
    {
        $key = $filter['key'] ?? '';
        $method = $filter['method'] ?? 'number-input';
        $title = $args['title'] ?? '';
        $dropdown_style = $filter['dropdown_style'] ?? 'enhanced';
        $placeholder = isset($filter['placeholder']) && trim($filter['placeholder']) ? $filter['placeholder'] : $title;

        $id = $args['id'] ?? 'lsd_search_number_' . $key;
        $name = $args['name'] ?? 'sf-' . $key . '-eq';
        $class = $method === 'range' ? 'lsd-search-range ' : '';

        $current = $args['current'] ?? '';

        $output = '<div class="' . esc_attr($class) . '">';
        $output .= '<label for="' . esc_attr($id) . '">' . esc_html($title) . '</label>';
        $output .= $method === 'range' ? '<div class="lsd-search-range-inner">' : '';

        if ($method === 'number-input')
        {
            $output .= '<input type="number" name="' . esc_attr($name) . '" id="' . esc_attr($id) . '" placeholder="' . esc_attr($placeholder) . '" value="' . esc_attr($current) . '">';
        }
        else if ($method === 'dropdown')
        {
            $output .= '<select name="' . esc_attr($name) . '" id="' . esc_attr($id) . '" placeholder="' . esc_attr($placeholder) . '" data-enhanced="' . ($dropdown_style === 'enhanced' ? 1 : 0) . '">';
            $output .= '<option value="">' . esc_html($placeholder) . '</option>';

            if (strpos($name, 'sf-acf-') !== false)
            {
                $values = $this->acf_get_values($name);
                foreach ($values as $v) $output .= '<option value="' . esc_attr($v) . '" ' . ($current == $v ? 'selected="selected"' : '') . '>' . esc_html($v) . '</option>';
            }
            else
            {
                $terms = $this->get_terms($filter, true);
                foreach ($terms as $term) $output .= '<option value="' . esc_attr($term) . '" ' . ($current == $term ? 'selected="selected"' : '') . '>' . esc_html($term) . '</option>';
            }

            $output .= '</select>';
        }
        else if ($method === 'dropdown-plus')
        {
            $min = $filter['min'] ?? 0;
            $max = $filter['max'] ?? 100;
            $increment = $filter['increment'] ?? 10;
            $th_separator = isset($filter['th_separator']) && $filter['th_separator'];

            $name = $args['dropdown_name'] ?? '';
            $current = $args['dropdown_current'] ?? '';

            $output .= '<select name="' . esc_attr($name) . '" id="' . esc_attr($id) . '" placeholder="' . esc_attr($placeholder) . '" data-enhanced="' . ($dropdown_style === 'enhanced' ? 1 : 0) . '">';
            $output .= '<option value="">' . esc_html($placeholder) . '</option>';

            $i = $min;
            while ($i <= $max)
            {
                $decimals = (floor($i) == $i) ? 0 : 2;

                $output .= '<option value="' . esc_attr($i) . '" ' . (($current == (string) $i) ? 'selected="selected"' : '') . '>' . ($th_separator ? number_format_i18n($i, $decimals) : $i) . '+</option>';
                $i += $increment;
            }

            $output .= '</select>';
        }
        else if ($method === 'range')
        {
            if (strpos($name, 'sf-acf-') !== false)
            {
                $acf_name = substr(substr($name, 0, -4), 7);
                $min_name = $args['min_name'] ?? 'sf-acf-' . $acf_name . '-ara-min';
                $min_current = $args['min_current'] ?? '';
                $max_name = $args['max_name'] ?? 'sf-acf-' . $acf_name . '-ara-max';
                $max_current = $args['max_current'] ?? '';
                $min = !empty($filter['min']) ? $filter['min'] : ($this->acf_field_data($acf_name, 'min')[0] ?? 0);
                $max = !empty($filter['max']) ? $filter['max'] : ($this->acf_field_data($acf_name, 'max')[0] ?? 100);
                $increment = !empty($filter['increment']) ? $filter['increment'] : ($this->acf_field_data($acf_name, 'step')[0] ?? 1);
                $prepend = !empty($filter['prepend']) ? $filter['prepend'] : ($this->acf_field_data($acf_name, 'prepend')[0] ?? '');
            }
            else
            {
                $min_name = $args['min_name'] ?? 'sf-' . $key . '-grb-min';
                $min_current = $args['min_current'] ?? '';
                $max_name = $args['max_name'] ?? 'sf-' . $key . '-grb-max';
                $max_current = $args['max_current'] ?? '';
                $min = $filter['min'] ?? 0;
                $max = $filter['max'] ?? 100;
                $increment = $filter['increment'] ?? 10;
                $prepend = $filter['prepend'] ?? '';
            }

            $default = $filter['default_value'] ?? '';
            $max_default = $filter['max_default_value'] ?? '';

            $output .= $this->range($filter, [
                'id' => $id,
                'min_name' => $min_name,
                'min_current' => $min_current,
                'max_name' => $max_name,
                'max_current' => $max_current,
                'default' => $default,
                'max_default' => $max_default,
                'min' => $min,
                'max' => $max,
                'step' => $increment,
                'prepend' => $prepend,
            ]);
        }

        $output .= $method === 'range' ? '</div>' : '';
        $output .= '</div>';
        return $output;
    }

    public function dropdown($filter, $args = []): string
    {
        $key = $filter['key'] ?? '';
        $title = $filter['title'] ?? '';
        $dropdown_style = $filter['dropdown_style'] ?? 'enhanced';
        $placeholder = isset($filter['placeholder']) && trim($filter['placeholder']) ? $filter['placeholder'] : $title;

        $id = $args['id'] ?? null;
        $name = $args['name'] ?? 'sf-' . $key;
        $current = $args['current'] ?? null;

        $output = '<select class="' . esc_attr($key) . '" name="' . esc_attr($name) . '" id="' . esc_attr($id) . '" placeholder="' . esc_attr($placeholder) . '" data-enhanced="' . ($dropdown_style === 'enhanced' ? 1 : 0) . '">';
        $output .= '<option value="">' . esc_html($placeholder) . '</option>';
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
            if (!$all_terms && count($predefined_terms) && !isset($predefined_terms[$term->term_id])) continue;

            $output .= '<option class="level-' . esc_attr($level) . '" value="' . esc_attr($term->term_id) . '" ' . ($current == $term->term_id ? 'selected="selected"' : '') . '>' . esc_html(($prefix . (trim($prefix) ? ' ' : '') . $term->name)) . '</option>';

            $children = get_term_children($term->term_id, $key);
            if (is_array($children) && count($children))
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
        $dropdown_style = $filter['dropdown_style'] ?? 'enhanced';
        $ph = isset($filter['placeholder']) && trim($filter['placeholder']) ? $filter['placeholder'] : $title;
        $placeholders = explode(',', $ph);

        $all_terms = $filter['all_terms'] ?? 1;
        $predefined_terms = isset($filter['terms']) && is_array($filter['terms']) ? $filter['terms'] : [];
        $hide_empty = $filter['hide_empty'] ?? 0;
        $level_status = $filter['level_status'] ?? 'dependant';

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
            if (!$all_terms && count($predefined_terms) && !isset($predefined_terms[$term->term_id])) continue;

            $level = count(LSD_Taxonomies::parents($term)) + 1;
            $max_levels = max($max_levels, $level);

            // Term is not child of current parents
            if ($current && count($available_parents_for_childs) && $term->parent != 0 && $term->term_id != $current && !in_array($term->parent, $available_parents_for_childs))
            {
                continue;
            }

            if (!isset($hierarchy[$level])) $hierarchy[$level] = [];
            $hierarchy[$level][] = $term;
        }

        $output = '<div class="lsd-hierarchical-dropdowns" id="' . esc_attr($id) . '_wrapper" data-for="' . esc_attr($key) . '" data-id="' . esc_attr($id) . '" data-max-levels="' . esc_attr($max_levels) . '" data-name="' . esc_attr($name) . '" data-hide-empty="' . esc_attr($hide_empty) . '" data-level-status="' . esc_attr($level_status) . '">';
        for ($l = 1; $l <= $max_levels; $l++)
        {
            $level_terms = isset($hierarchy[$l]) && is_array($hierarchy[$l]) ? $hierarchy[$l] : [];
            $placeholder = $placeholders[($l - 1)] ?? $placeholders[0];

            $output .= '<select class="' . esc_attr($key) . '" name="' . esc_attr($name) . '" id="' . esc_attr($id . '_' . $l) . '" placeholder="' . esc_attr($placeholder) . '" data-level="' . esc_attr($l) . '" data-enhanced="' . ($dropdown_style === 'enhanced' ? 1 : 0) . '">';
            $output .= '<option value="">' . esc_html($placeholder) . '</option>';

            foreach ($level_terms as $level_term) $output .= '<option class="lsd-option lsd-parent-' . esc_attr($level_term->parent) . '" value="' . esc_attr($level_term->term_id) . '" ' . (($current == $level_term->term_id || in_array($level_term->term_id, $current_parents)) ? 'selected="selected"' : '') . '>' . esc_html($level_term->name) . '</option>';
            $output .= '</select>';
        }

        $output .= '</div>';
        return $output;
    }

    public function range($filter, $args = []): string
    {
        $key = $filter['key'] ?? '';
        $id = $args['id'] ?? null;
        $min_name = $args['min_name'] ?? 'sf-' . $key . '-min';
        $max_name = $args['max_name'] ?? 'sf-' . $key . '-max';
        $min = $args['min'] ?? '';
        $min_current = $args['min_current'] ?? '';
        $max = $args['max'] ?? '';
        $max_current = $args['max_current'] ?? '';
        $step = $args['step'] ?? '1';
        $min_default = !empty($min_current) ? $min_current : ($args['default'] !== '' && $args['default'] >= $min ? $args['default'] : $min);
        $max_default = !empty($max_current) ? $max_current : ($args['max_default'] !== '' && $args['max_default'] <= $max ? $args['max_default'] : $max);
        $prepend = $args['prepend'] ?? '';

        $output = '<div class="lsd-range-slider-label"><div><span>' . esc_html($prepend) . '<span class="lsd-range-min">' . esc_html($min_default) . '</span></span> <span>' . esc_html($prepend) . '<span class="lsd-range-max">' . esc_html($max_default) . '</span></span></div></div>';
        $output .= '<div class="lsd-range-slider-search ' . esc_attr($key) . '" data-default="' . esc_html($min_default) . '" data-max-default="' . esc_html($max_default) . '" data-min="' . esc_html($min) . '" data-max="' . esc_html($max) . '" data-step="' . esc_html($step) . '"></div>';
        $output .= '<input class="lsd-range-min-value" type="hidden" id="' . esc_attr($id) . '" name="' . esc_attr($min_name) . '" value="' . esc_attr($min_default) . '">';
        $output .= '<input class="lsd-range-max-value" type="hidden" id="' . esc_attr($id) . '" name="' . esc_attr($max_name) . '" value="' . esc_attr($max_default) . '">';

        return $output;
    }

    public function acf_dropdown($filter, $args = []): string
    {
        $key = $filter['key'] ?? '';
        $dropdown_style = $filter['dropdown_style'] ?? 'enhanced';

        $title = $args['title'] ?? '';
        $placeholder = isset($args['placeholder']) && trim($args['placeholder']) ? $args['placeholder'] : $title;
        $id = $args['id'] ?? null;
        $name = $args['name'] ?? 'sf-' . $key;
        $current = $args['current'] ?? null;
        $options = $args['options'] ?? [];

        $output = '<select class="' . esc_attr($key) . '" name="' . esc_attr($name) . '" id="' . esc_attr($id) . '" placeholder="' . esc_attr($placeholder) . '" data-enhanced="' . ($dropdown_style === 'enhanced' ? 1 : 0) . '">';
        $output .= '<option value="">' . esc_html($placeholder) . '</option>';
        $output .= $this->acf_dropdown_options($options, $current);
        $output .= '</select>';

        return $output;
    }

    public function acf_dropdown_options($options, $current = null): string
    {
        $acf_options = $options ?? [];
        $output = '';

        foreach ($acf_options as $value => $label)
        {
            $output .= '<option class="acf-option" value="' . esc_attr($value) . '" ' . ($current == $value ? 'selected="selected"' : '') . '>' . esc_html($label) . '</option>';
        }

        return $output;
    }

    public function acf_field_data($acf_name, $data): array
    {
        $acf_field_name = is_string($acf_name) ? [$acf_name] : ($acf_name ?? []);

        $options = [];
        foreach ($acf_field_name as $acf_field_data)
        {
            $acf_field = acf_get_field($acf_field_data);
            if (!empty($acf_field[$data])) $options[] = $acf_field[$data];
        }

        return $options;
    }

    public function standardize_key(string $key, bool $search = true): string
    {
        if (strpos($key, 'att-') !== false)
        {
            if ($search)
            {
                $id = LSD_Main::get_attr_id(substr($key, 4));
                return 'att-'.$id;
            }

            $slug = LSD_Main::get_attr_slug(substr($key, 4));
            return 'att-'.$slug;
        }

        return $key;
    }

    public function attribute_checkboxes(array $values, array $args = []): string
    {
        $current = $args['current'] ?? [];
        $name = $args['name'] ?? 'lsd_attribute';
        $id_prefix = $args['id_prefix'] ?? 'attr_';

        $output = '';
        foreach ($values as $val)
        {
            $val = trim($val);
            if ($val === '') continue;

            $id = $id_prefix . sanitize_title($val);
            $checked = isset($current[$val]) && $current[$val] ? 'checked="checked"' : '';

            $output .= '<li>';
            $output .= '<input type="checkbox" name="' . esc_attr($name) . '[' . esc_attr($val) . ']" id="' . esc_attr($id) . '" value="1" ' . $checked . '>';
            $output .= '<label for="' . esc_attr($id) . '">' . esc_html($val) . '</label>';
            $output .= '</li>';
        }

        return $output;
    }
}
