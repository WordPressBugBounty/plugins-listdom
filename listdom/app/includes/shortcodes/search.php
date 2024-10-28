<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom Search Shortcode Class.
 *
 * @class LSD_Shortcodes_Search
 * @version    1.0.0
 */
class LSD_Shortcodes_Search extends LSD_Shortcodes
{
    public $id;
    public $atts;
    public $filters;
    public $form;
    public $sf;
    public $col_filter;
    public $col_button;
    public $more_options;
    public $settings;
    public $ajax;

    /**
     * @var LSD_Search_Helper
     */
    public $helper;

    /**
     * Constructor method
     */
    public function __construct()
    {
        parent::__construct();

        $this->more_options = false;
        $this->helper = new LSD_Search_Helper();

        $this->settings = LSD_Options::settings();
    }

    public function init()
    {
        add_shortcode('listdom-search', [$this, 'output']);
    }

    public function output($atts = [])
    {
        // Listdom Pre Shortcode
        $pre = apply_filters('lsd_pre_shortcode', '', $atts, 'listdom-search');
        if (trim($pre)) return $pre;

        // Shortcode ID
        $this->id = $atts['id'] ?? 0;

        // AJAX Search
        $this->ajax = $atts['ajax'] ?? 0;

        // APS Addon is Required
        if (!apply_filters('lsd_is_addaps_installed', false)) $this->ajax = 0;

        // Attributes
        $this->atts = apply_filters('lsd_search_atts', $this->parse($this->id, $atts));

        // Filters
        $this->filters = $this->atts['lsd_fields'] ?? [];

        // Form
        $this->form = $this->atts['lsd_form'] ?? [];

        // Overwrite Form Options
        if (isset($this->atts['page']) && trim($this->atts['page'])) $this->form['page'] = $this->atts['page'];
        if (isset($this->atts['shortcode']) && trim($this->atts['shortcode'])) $this->form['shortcode'] = $this->atts['shortcode'];

        // Current Values
        $this->sf = $this->get_sf();

        // Search Form
        ob_start();
        include lsd_template('search/tpl.php');
        return ob_get_clean();
    }

    /**
     * @param $key
     * @param null $default
     * @return array|null|string
     */
    public function current($key, $default = null)
    {
        $value = $_REQUEST[$key] ?? $default;

        if (is_array($value) || is_object($value)) array_walk_recursive($value, 'sanitize_text_field');
        else if ($value) $value = sanitize_text_field(urldecode($value));

        return $value;
    }

    public function row($row): string
    {
        $type = isset($row['type']) && trim($row['type']) ? $row['type'] : 'row';
        $filters = isset($row['filters']) && is_array($row['filters']) && count($row['filters']) ? $row['filters'] : [];
        $buttons = isset($row['buttons']) && $row['buttons'];

        $row = '';
        if ($type == 'row')
        {
            // Columns
            list($this->col_filter, $this->col_button) = $this->helper->column(count($filters), $buttons);

            $row .= '<div class="lsd-search-row ' . ($this->more_options ? 'lsd-search-included-in-more' : '') . '"><div class="lsd-row">';

            foreach ($filters as $filter) $row .= $this->filter($filter);
            if ($buttons) $row .= $this->buttons();

            $row .= '</div></div>';
        }
        else
        {
            $this->more_options = true;

            $row .= '<div class="lsd-search-row-more-options">';
            $row .= '<span class="lsd-search-more-options"> ' . esc_html__('More Options', 'listdom') . '<i class="lsd-icon fa fa-plus"></i></span>';
            $row .= '</div>';
        }

        return $row;
    }

    public function buttons(): string
    {
        $main = new LSD_Main();

        $buttons = '<div class="lsd-search-buttons ' . esc_attr($this->col_button) . '">';

        // Shortcode Input
        if (isset($this->form['shortcode']) && trim($this->form['shortcode'])) $buttons .= '<input type="hidden" name="sf-shortcode" value="' . esc_attr($this->form['shortcode']) . '">';

        // Language Input
        $lang = $this->current('lang');
        if ($lang) $buttons .= '<input type="hidden" name="lang" value="' . esc_attr($lang) . '">';

        // Submit Button
        $buttons .= '<div class="lsd-search-buttons-submit"><button type="submit" class="lsd-search-button lsd-color-m-bg ' . sanitize_html_class($main->get_text_class()) . '">' . esc_html__('Search', 'listdom') . '</button></div>';

        $buttons .= '</div>';
        return $buttons;
    }

    public function criteria(): string
    {
        // Search Criteria
        $sf = $this->get_sf();

        // Human Readable Criteria
        $categories = '';
        $labels = '';
        $locations = '';

        // Category
        if (isset($sf[LSD_Base::TAX_CATEGORY]) && $sf[LSD_Base::TAX_CATEGORY])
        {
            $names = LSD_Taxonomies::name($sf[LSD_Base::TAX_CATEGORY], LSD_Base::TAX_CATEGORY);
            $categories = (is_array($names) ? '<strong>' . implode('</strong>, <strong>', $names) . '</strong>' : '<strong>' . $names . '</strong>');
        }

        // Label
        if (isset($sf[LSD_Base::TAX_LABEL]) && $sf[LSD_Base::TAX_LABEL])
        {
            $names = LSD_Taxonomies::name($sf[LSD_Base::TAX_LABEL], LSD_Base::TAX_LABEL);
            $labels = (is_array($names) ? '<strong>' . implode('</strong>, <strong>', $names) . '</strong>' : '<strong>' . $names . '</strong>');
        }

        // Location
        if (isset($sf[LSD_Base::TAX_LOCATION]) && $sf[LSD_Base::TAX_LOCATION])
        {
            $names = LSD_Taxonomies::name($sf[LSD_Base::TAX_LOCATION], LSD_Base::TAX_LOCATION);
            $locations = (is_array($names) ? '<strong>' . implode('</strong>, <strong>', $names) . '</strong>' : '<strong>' . $names . '</strong>');
        }

        $criteria = '';
        if (trim($categories)) $criteria .= $categories . ', ';
        if (trim($labels)) $criteria .= $labels . ', ';
        if (trim($locations)) $criteria .= $locations . ', ';

        $HR = (trim($criteria) ? sprintf(esc_html__("Results %s %s", 'listdom'), '<i class="lsd-icon fas fa-caret-right"></i>', trim($criteria, ', ')) : '');

        return '<div class="lsd-search-criteria">
            <span>' . $HR . '</span>
        </div>';
    }

    public function filter($filter)
    {
        $output = '';

        $key = $filter['key'] ?? null;
        if (!$key) return $output;

        $type = $this->helper->get_type_by_key($key);
        switch ($type)
        {
            case 'textsearch':

                $output = $this->field_textsearch($filter);
                break;

            case 'taxonomy':

                $output = $this->field_taxonomy($filter);
                break;

            case 'text':
            case 'textarea':

                $output = $this->field_text($filter);
                break;

            case 'numeric':
            case 'number':

                $output = $this->field_number($filter);
                break;

            case 'dropdown':

                $output = $this->field_dropdown($filter);
                break;

            case 'price':

                $output = $this->field_price($filter);
                break;

            case 'class':

                $output = $this->field_class($filter);
                break;

            case 'address':

                $output = $this->field_address($filter);
                break;

            case 'period':

                $output = $this->field_period($filter);
                break;
        }

        // Apply Filters
        return apply_filters('lsd_search_filter_field', $output, $filter, $this);
    }

    public function field_textsearch($filter): string
    {
        $key = $filter['key'] ?? '';
        $title = $filter['title'] ?? '';
        $placeholder = isset($filter['placeholder']) && trim($filter['placeholder']) ? $filter['placeholder'] : $title;

        $id = 'lsd_search_' . $this->id . '_' . $key;
        $name = 'sf-' . $key;

        $default = $filter['default_value'] ?? '';
        $current = $this->current($name, $default);

        $output = '<div class="lsd-search-filter ' . esc_attr($this->col_filter) . '">';
        $output .= '<label for="' . esc_attr($id) . '">' . esc_html__($title, 'listdom') . '</label>';
        $output .= '<input type="text" name="' . esc_attr($name) . '" id="' . esc_attr($id) . '" placeholder="' . esc_attr__($placeholder, 'listdom') . '" value="' . esc_attr($current) . '">';
        $output .= '</div>';

        return $output;
    }

    public function field_taxonomy_hierarchy($name, $current, $all_terms, $predefined_terms, $terms, $render_type): string
    {
        $render = '<ul class="lsd-hierarchy-list">';
        foreach ($terms as $key => $term)
        {
            // Term is not in the predefined terms
            if (!$all_terms && count($predefined_terms) && !isset($predefined_terms[$key])) continue;

            $render .= '<li ' . (isset($term['children']) && $term['children'] ? ' class="children"' : '') . '>';

            if ($render_type === 'checkboxes') $render .= '<label class="lsd-search-checkbox-label"><input type="checkbox" class="' . esc_attr($key) . '" name="' . esc_attr($name) . '[]" value="' . esc_attr($key) . '" ' . (in_array($key, $current) ? 'checked="checked"' : '') . '>' . esc_html($term["name"]) . '</label>';
            if (isset($term['children']) && $term['children']) $render .= $this->field_taxonomy_hierarchy($name, $current, $all_terms, $predefined_terms, $term['children'], $render_type);

            $render .= '</li>';
        }

        $render .= '</ul>';
        return $render;
    }

    public function field_taxonomy($filter): string
    {
        $key = $filter['key'] ?? '';
        $method = $filter['method'] ?? 'dropdown';
        $title = $filter['title'] ?? '';
        $placeholder = isset($filter['placeholder']) && trim($filter['placeholder']) ? $filter['placeholder'] : $title;

        $all_terms = $filter['all_terms'] ?? 1;
        $predefined_terms = isset($filter['terms']) && is_array($filter['terms']) ? $filter['terms'] : [];

        $id = 'lsd_search_' . $this->id . '_' . $key;
        $name = 'sf-' . $key;

        $default = $filter['default_value'] ?? '';
        if (trim($default) && !is_numeric($default) && $filter['method'] !== 'text-input') $default = $this->helper->get_term_id($key, $default);

        $current = $this->current($name, $default);

        $output = '<div class="lsd-search-filter ' . esc_attr($this->col_filter) . '">';
        $output .= '<label for="' . esc_attr($id) . '">' . esc_html__($title, 'listdom') . '</label>';

        if ($method === 'dropdown')
        {
            $output .= $this->helper->dropdown($filter, [
                'id' => $id,
                'name' => $name,
                'current' => $current,
            ]);
        }
        else if ($method === 'dropdown-multiple')
        {
            $current = $this->current($name, explode(',', $default));

            $output .= '<select class="' . esc_attr($key) . '" name="' . esc_attr($name) . '[]" id="' . esc_attr($id) . '" placeholder="' . esc_attr__($placeholder, 'listdom') . '" multiple>';

            $terms = $this->helper->get_terms($filter);
            foreach ($terms as $key => $term)
            {
                // Term is not in the predefined terms
                if (!$all_terms && count($predefined_terms) && !isset($predefined_terms[$key])) continue;

                $output .= '<option value="' . esc_attr($key) . '" ' . (in_array($key, $current) ? 'selected="selected"' : '') . '>' . esc_html($term) . '</option>';
            }

            $output .= '</select>';
        }
        else if ($method === 'text-input')
        {
            $output .= '<input class="' . esc_attr($key) . '" type="text" name="' . esc_attr($name) . '" id="' . esc_attr($id) . '" placeholder="' . esc_attr__($placeholder, 'listdom') . '" value="' . esc_attr($current) . '">';
        }
        else if ($method === 'checkboxes')
        {
            $current = $this->current($name, explode(',', $default));
            $terms = $this->helper->get_terms($filter, false, true);

            // Required for the AJAX search to work
            $output .= '<input type="hidden" name="' . esc_attr($name) . '[]">';

            $output .= $this->field_taxonomy_hierarchy($name, $current, $all_terms, $predefined_terms, $terms, 'checkboxes');
        }
        else if ($method === 'radio')
        {
            $terms = $this->helper->get_terms($filter);
            foreach ($terms as $key => $term)
            {
                // Term is not in the predefined terms
                if (!$all_terms && count($predefined_terms) && !isset($predefined_terms[$key])) continue;

                $output .= '<label class="lsd-search-radio-label"><input type="radio" class="' . esc_attr($key) . '" name="' . esc_attr($name) . '" value="' . esc_attr($key) . '" ' . ($current == $key ? 'checked="checked"' : '') . '>' . esc_html($term) . '</label>';
            }
        }
        else if ($method === 'hierarchical' && $this->isPro())
        {
            $output .= $this->helper->hierarchical($filter, [
                'id' => $id,
                'name' => $name,
                'current' => $current,
            ]);
        }

        $output .= '</div>';
        return $output;
    }

    public function field_text($filter): string
    {
        $key = $filter['key'] ?? '';
        $title = $filter['title'] ?? '';
        $placeholder = isset($filter['placeholder']) && trim($filter['placeholder']) ? $filter['placeholder'] : $title;

        $id = 'lsd_search_' . $this->id . '_' . $key;
        $name = 'sf-' . $key . '-lk';

        $default = $filter['default_value'] ?? '';
        $current = $this->current($name, $default);

        $output = '<div class="lsd-search-filter ' . esc_attr($this->col_filter) . '">';
        $output .= '<label for="' . esc_attr($id) . '">' . esc_html__($title, 'listdom') . '</label>';
        $output .= '<input type="text" name="' . esc_attr($name) . '" id="' . esc_attr($id) . '" placeholder="' . esc_attr__($placeholder, 'listdom') . '" value="' . esc_attr($current) . '">';
        $output .= '</div>';

        return $output;
    }

    public function field_number($filter): string
    {
        $key = $filter['key'] ?? '';
        $method = $filter['method'] ?? 'number-input';
        $title = $filter['title'] ?? '';
        $placeholder = isset($filter['placeholder']) && trim($filter['placeholder']) ? $filter['placeholder'] : $title;

        $id = 'lsd_search_' . $this->id . '_' . $key;
        $name = 'sf-' . $key . '-eq';

        $default = $filter['default_value'] ?? '';
        $current = $this->current($name, $default);

        $output = '<div class="lsd-search-filter ' . esc_attr($this->col_filter) . '">';
        $output .= '<label for="' . esc_attr($id) . '">' . esc_html__($title, 'listdom') . '</label>';

        if ($method === 'number-input')
        {
            $output .= '<input type="number" name="' . esc_attr($name) . '" id="' . esc_attr($id) . '" placeholder="' . esc_attr__($placeholder, 'listdom') . '" value="' . esc_attr($current) . '">';
        }
        else if ($method === 'dropdown')
        {
            $output .= '<select name="' . esc_attr($name) . '" id="' . esc_attr($id) . '" placeholder="' . esc_attr__($placeholder, 'listdom') . '">';

            $terms = $this->helper->get_terms($filter, true);
            foreach ($terms as $term) $output .= '<option value="' . esc_attr($term) . '" ' . ($current == $term ? 'selected="selected"' : '') . '>' . esc_html($term) . '</option>';

            $output .= '</select>';
        }
        else if ($method === 'dropdown-plus')
        {
            $min = $filter['min'] ?? 0;
            $max = $filter['max'] ?? 100;
            $increment = $filter['increment'] ?? 10;
            $th_separator = isset($filter['th_separator']) && $filter['th_separator'];

            $name = 'sf-' . $key . '-grq';
            $current = $this->current($name, $default);

            $output .= '<select name="' . esc_attr($name) . '" id="' . esc_attr($id) . '" placeholder="' . esc_attr__($placeholder, 'listdom') . '">';

            $output .= '<option value="0">' . $placeholder . '</option>';
            $i = $min;
            while ($i <= $max)
            {
                $decimals = (floor($i) == $i) ? 0 : 2;

                $output .= '<option value="' . esc_attr($i) . '" ' . (($current == (string) $i) ? 'selected="selected"' : '') . '>' . ($th_separator ? number_format_i18n($i, $decimals) : $i) . '+</option>';
                $i += $increment;
            }

            $output .= '</select>';
        }

        $output .= '</div>';
        return $output;
    }

    public function field_dropdown($filter): string
    {
        $key = $filter['key'] ?? '';
        $method = $filter['method'] ?? 'dropdown';
        $title = $filter['title'] ?? '';
        $placeholder = isset($filter['placeholder']) && trim($filter['placeholder']) ? $filter['placeholder'] : $title;

        $id = 'lsd_search_' . $this->id . '_' . $key;
        $name = 'sf-' . $key . '-eq';

        $default = $filter['default_value'] ?? '';
        $current = $this->current($name, $default);

        $output = '<div class="lsd-search-filter ' . esc_attr($this->col_filter) . '">';
        $output .= '<label for="' . esc_attr($id) . '">' . esc_html__($title, 'listdom') . '</label>';

        if ($method === 'dropdown')
        {
            $output .= '<select name="' . esc_attr($name) . '" id="' . esc_attr($id) . '" placeholder="' . esc_attr__($placeholder, 'listdom') . '">';
            $output .= '<option value="">' . esc_html__($placeholder, 'listdom') . '</option>';

            $terms = $this->helper->get_terms($filter, true);
            foreach ($terms as $term) $output .= '<option value="' . esc_attr($term) . '" ' . ($current == $term ? 'selected="selected"' : '') . '>' . esc_html($term) . '</option>';

            $output .= '</select>';
        }
        else if ($method === 'dropdown-multiple')
        {
            $name = 'sf-' . $key . '-in';
            $current = $this->current($name, explode(',', $default));

            $output .= '<select name="' . esc_attr($name) . '[]" id="' . esc_attr($id) . '" placeholder="' . esc_attr__($placeholder, 'listdom') . '" multiple>';

            $terms = $this->helper->get_terms($filter, true);
            foreach ($terms as $term) $output .= '<option value="' . esc_attr($term) . '" ' . (in_array($term, $current) ? 'selected="selected"' : '') . '>' . esc_html($term) . '</option>';

            $output .= '</select>';
        }
        else if ($method === 'text-input')
        {
            $output .= '<input type="text" name="' . esc_attr($name) . '" id="' . esc_attr($id) . '" placeholder="' . esc_attr__($placeholder, 'listdom') . '" value="' . esc_attr($current) . '">';
        }
        else if ($method === 'checkboxes')
        {
            $name = 'sf-' . $key . '-in';
            $current = $this->current($name, explode(',', $default));

            $terms = $this->helper->get_terms($filter, true);
            foreach ($terms as $term) $output .= '<label><input type="checkbox" name="' . esc_attr($name) . '[]" value="' . esc_attr($term) . '" ' . (in_array($term, $current) ? 'checked="checked"' : '') . '>' . esc_html($term) . '</label>';
        }
        else if ($method === 'radio')
        {
            $current = $this->current($name, explode(',', $default));

            $terms = $this->helper->get_terms($filter, true);
            foreach ($terms as $term) $output .= '<label><input type="radio" name="' . esc_attr($name) . '" value="' . esc_attr($term) . '" ' . ($term == $current ? 'checked="checked"' : '') . '>' . esc_html($term) . '</label>';
        }

        $output .= '</div>';
        return $output;
    }

    public function field_price($filter): string
    {
        $key = $filter['key'] ?? '';
        $method = $filter['method'] ?? 'dropdown-plus';
        $title = $filter['title'] ?? '';

        $min_placeholder = isset($filter['placeholder']) && trim($filter['placeholder']) ? $filter['placeholder'] : $title;
        $max_placeholder = isset($filter['max_placeholder']) && trim($filter['max_placeholder']) ? $filter['max_placeholder'] : $title;

        $id = 'lsd_search_' . $this->id . '_' . $key;

        $min_default = $filter['default_value'] ?? '';
        $max_default = $filter['max_default_value'] ?? '';

        $output = '<div class="lsd-search-filter ' . esc_attr($this->col_filter) . '">';
        $output .= '<label for="' . esc_attr($id) . '">' . esc_html__($title, 'listdom') . '</label>';

        if ($method === 'dropdown-plus')
        {
            $min = $filter['min'] ?? 0;
            $max = $filter['max'] ?? 100;
            $increment = $filter['increment'] ?? 10;
            $th_separator = isset($filter['th_separator']) && $filter['th_separator'];

            $name = 'sf-att-' . $key . '-grq';
            $current = $this->current($name, $min_default);

            $output .= '<select name="' . esc_attr($name) . '" id="' . esc_attr($id) . '" placeholder="' . esc_attr__($min_placeholder, 'listdom') . '">';

            $output .= '<option value="0">' . $min_placeholder . '</option>';
            $i = $min;
            while ($i <= $max)
            {
                $decimals = (floor($i) == $i) ? 0 : 2;

                $output .= '<option value="' . esc_attr($i) . '" ' . (($current == (string) $i) ? 'selected="selected"' : '') . '>' . ($th_separator ? number_format_i18n($i, $decimals) : $i) . '+</option>';
                $i += $increment;
            }

            $output .= '</select>';
        }
        else if ($method === 'mm-input')
        {
            $min_name = 'sf-att-' . $key . '-bt-min';
            $min_current = $this->current($min_name, $min_default);

            $max_name = 'sf-att-' . $key . '-bt-max';
            $max_current = $this->current($max_name, $max_default);

            $output .= '<div class="lsd-search-mm-input">';
            $output .= '<input type="text" name="' . esc_attr($min_name) . '" id="' . esc_attr($id) . '" placeholder="' . esc_attr__($min_placeholder, 'listdom') . '" value="' . esc_attr($min_current) . '">';
            $output .= '<input type="text" name="' . esc_attr($max_name) . '" id="' . esc_attr($id) . '" placeholder="' . esc_attr__($max_placeholder, 'listdom') . '" value="' . esc_attr($max_current) . '">';
            $output .= '</div>';
        }

        $output .= '</div>';
        return $output;
    }

    public function field_class($filter): string
    {
        $key = $filter['key'] ?? '';
        $method = $filter['method'] ?? 'dropdown';
        $title = $filter['title'] ?? '';
        $placeholder = isset($filter['placeholder']) && trim($filter['placeholder']) ? $filter['placeholder'] : $title;

        $id = 'lsd_search_' . $this->id . '_' . $key;

        $default = $filter['default_value'] ?? '';
        if (!is_numeric($default)) $default = substr_count($default, '$');

        $output = '<div class="lsd-search-filter ' . esc_attr($this->col_filter) . '">';
        $output .= '<label for="' . esc_attr($id) . '">' . esc_html__($title, 'listdom') . '</label>';

        if ($method === 'dropdown')
        {
            $name = 'sf-att-' . $key . '-eq';
            $current = $this->current($name, $default);

            $output .= '<select name="' . esc_attr($name) . '" id="' . esc_attr($id) . '" placeholder="' . esc_attr__($placeholder, 'listdom') . '">';

            $output .= '<option value="">' . esc_html__('Any', 'listdom') . '</option>';
            $output .= '<option value="1" ' . (($current == 1) ? 'selected="selected"' : '') . '>' . esc_html__('$', 'listdom') . '</option>';
            $output .= '<option value="2" ' . (($current == 2) ? 'selected="selected"' : '') . '>' . esc_html__('$$', 'listdom') . '</option>';
            $output .= '<option value="3" ' . (($current == 3) ? 'selected="selected"' : '') . '>' . esc_html__('$$$', 'listdom') . '</option>';
            $output .= '<option value="4" ' . (($current == 4) ? 'selected="selected"' : '') . '>' . esc_html__('$$$$', 'listdom') . '</option>';

            $output .= '</select>';
        }

        $output .= '</div>';
        return $output;
    }

    public function field_address($filter): string
    {
        $key = $filter['key'] ?? '';
        $method = $filter['method'] ?? 'text-input';
        $title = $filter['title'] ?? '';
        $placeholder = isset($filter['placeholder']) && trim($filter['placeholder']) ? $filter['placeholder'] : $title;

        $id = 'lsd_search_' . $this->id . '_' . $key;
        $name = 'sf-att-' . $key . '-lk';

        $default = $filter['default_value'] ?? '';

        $output = '<div class="lsd-search-filter ' . esc_attr($this->col_filter) . '">';
        $output .= '<label for="' . esc_attr($id) . '">' . esc_html__($title, 'listdom') . '</label>';

        if ($method === 'text-input')
        {
            $current = $this->current($name, $default);
            $output .= '<input type="text" name="' . esc_attr($name) . '" id="' . esc_attr($id) . '" placeholder="' . esc_attr__($placeholder, 'listdom') . '" value="' . esc_attr($current) . '">';
        }
        else if ($method === 'radius')
        {
            $name = 'sf-circle-center';
            $current = $this->current($name, $default);

            // Radius
            $radius_name = 'sf-circle-radius';
            $radius = $filter['radius'] ?? '';
            $radius_current = $this->current($radius_name, $radius);

            // Display Radius Field
            $radius_display = $filter['radius_display'] ?? 0;
            if ($radius_display) $output .= '<div class="lsd-radius-search-double-fields">';

            $output .= '<input type="text" name="' . esc_attr($name) . '" id="' . esc_attr($id) . '" placeholder="' . esc_attr__($placeholder, 'listdom') . '" value="' . esc_attr($current) . '">';
            $output .= '<input type="' . ($radius_display ? 'number' : 'hidden') . '" name="' . esc_attr($radius_name) . '" value="' . esc_attr($radius_current) . '" min="0" step="100" placeholder="' . esc_attr__('Meters', 'listdom') . '">';

            // Display Radius Field
            if ($radius_display) $output .= '</div>';
        }
        else if ($method === 'radius-dropdown')
        {
            $name = 'sf-circle-center';
            $current = $this->current($name, $default);

            // Radius
            $radius_values_str = $filter['radius_values'] ?? '';
            if (trim($radius_values_str, ', ') === '') $radius_values_str = '100,200,500,1000,2000,5000,10000';

            // Display Unit
            $radius_display_unit = $filter['radius_display_unit'] ?? 'm';

            $radius_values = explode(',', trim($radius_values_str, ', '));

            $radius_name = 'sf-circle-radius';
            $radius = $radius_values[0] ?? null;
            $radius_current = $this->current($radius_name, $radius);

            $output .= '<div class="lsd-radius-search-double-fields">';
            $output .= '<input type="text" name="' . esc_attr($name) . '" id="' . esc_attr($id) . '" placeholder="' . esc_attr__($placeholder, 'listdom') . '" value="' . esc_attr($current) . '">';

            $output .= '<select name="' . esc_attr($radius_name) . '" placeholder="' . esc_attr__('Meters', 'listdom') . '">';
            foreach ($radius_values as $radius_value)
            {
                $decimals = (floor($radius_value) == $radius_value) ? 0 : 2;

                $display_value = $radius_value;
                $display_suffix = '';

                if ($radius_display_unit === 'km')
                {
                    $display_value = $display_value / 1000;
                    $display_suffix = esc_html__('KM', 'listdom');
                }
                else if ($radius_display_unit === 'mile')
                {
                    $display_value = $display_value / 1609;
                    $display_suffix = esc_html__('Miles', 'listdom');
                }

                $output .= '<option value="' . esc_attr($radius_value) . '" ' . selected($radius_value, $radius_current, false) . '>' . esc_html(number_format_i18n($display_value, $decimals)) . (trim($display_suffix) ? ' ' . $display_suffix : '') . '</option>';
            }

            $output .= '</select>';
            $output .= '</div>';
        }

        $output .= '</div>';
        return $output;
    }

    public function field_period($filter): string
    {
        // Listdom Assets
        $assets = new LSD_Assets();

        // Date Range Picker
        $assets->moment();
        $assets->daterangepicker();

        $key = $filter['key'] ?? '';
        $title = $filter['title'] ?? '';
        $placeholder = isset($filter['placeholder']) && trim($filter['placeholder']) ? $filter['placeholder'] : $title;
        $format = $this->settings['datepicker_format'] ?? 'yyyy-mm-dd';

        $id = 'lsd_search_' . $this->id . '_' . $key;
        $name = 'sf-' . $key;

        $default = $filter['default_value'] ?? '';
        $current = $this->current($name, $default);

        $months = [];
        for ($i = 0; $i <= 13; $i++) $months[] = LSD_Base::date(strtotime('+' . $i . ' Months'), 'M Y');

        $output = '<div class="lsd-search-filter ' . esc_attr($this->col_filter) . '">';
        $output .= '<label for="' . esc_attr($id) . '">' . esc_html__($title, 'listdom') . '</label>';
        $output .= '<input type="text" class="lsd-date-range-picker" data-format="' . strtoupper(esc_attr($format)) . '" data-periods="' . htmlspecialchars(json_encode($months), ENT_QUOTES, 'UTF-8') . '" name="' . esc_attr($name) . '" id="' . esc_attr($id) . '" placeholder="' . esc_attr__($placeholder, 'listdom') . '" value="' . esc_attr($current) . '" autocomplete="off">';
        $output .= '</div>';

        return $output;
    }
}
