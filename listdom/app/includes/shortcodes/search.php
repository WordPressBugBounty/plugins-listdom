<?php

class LSD_Shortcodes_Search extends LSD_Shortcodes
{
    public $id;
    public $atts;
    public $desktop;
    public $tablet;
    public $mobile;
    public $devices;
    public $device_key = '';
    public $form;
    public $sf;
    public $col_filter;
    public $col_button;
    public $is_more_options;
    public $is_boxed;
    public $settings;
    public $ajax;
    public $connected_shortcodes = [];

    /**
     * @var LSD_Search_Helper
     */
    public $helper;

    public function __construct()
    {
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

        $this->is_more_options = false;
        $this->is_boxed = true;

        // Shortcode ID
        $this->id = $atts['id'] ?? 0;

        // Listdom Bar
        (LSD_Bar::instance())->add($this->id);

        // Attributes
        $this->atts = apply_filters('lsd_search_atts', $this->parse($this->id, $atts));

        // Desktop
        $this->desktop = isset($this->atts['lsd_fields']) && is_array($this->atts['lsd_fields']) ? $this->atts['lsd_fields'] : [];

        // Tablet
        $this->tablet = isset($this->atts['lsd_tablet']) && is_array($this->atts['lsd_tablet']) ? $this->atts['lsd_tablet'] : [];

        // Mobile
        $this->mobile = isset($this->atts['lsd_mobile']) && is_array($this->atts['lsd_mobile']) ? $this->atts['lsd_mobile'] : [];

        // Devices
        $this->devices = $this->atts['lsd_devices'] ?? [];

        // Inherit from Desktop?
        if (!isset($this->devices['tablet']['inherit']) || $this->devices['tablet']['inherit']) $this->tablet = $this->desktop;
        if (!isset($this->devices['mobile']['inherit']) || $this->devices['mobile']['inherit']) $this->mobile = $this->desktop;

        // Form
        $this->form = $this->atts['lsd_form'] ?? [];

        // AJAX Search
        $this->ajax = $atts['ajax'] ?? ($this->form['ajax'] ?? 0);

        // Current Values
        $this->sf = $this->get_sf();

        // Connected Shortcodes
        $this->connected_shortcodes = isset($this->form['connected_shortcodes']) && is_array($this->form['connected_shortcodes'])
            ? $this->form['connected_shortcodes']
            : [];

        // Overwrite Form Options
        if (isset($this->atts['page']) && trim($this->atts['page'])) $this->form['page'] = (int) $this->atts['page'];
        if (isset($this->atts['shortcode']) && trim($this->atts['shortcode']))
        {
            $this->form['shortcode'] = (int) $this->atts['shortcode'];
            $this->connected_shortcodes = [];
        }

        // APS Addon is Required
        if (!class_exists(LSDADDAPS::class) && !class_exists(\LSDPACAPS\Base::class))
        {
            $this->ajax = 0;
            $this->connected_shortcodes = [];
        }

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

    public function row(array $args = []): string
    {
        $type = isset($args['type']) && trim($args['type']) ? $args['type'] : 'row';
        $filters = isset($args['filters']) && is_array($args['filters']) ? $args['filters'] : [];

        $buttons = isset($args['buttons']) && (
                (is_string($args['buttons']) && $args['buttons']) || (is_array($args['buttons']) && $args['buttons']['status'])
            );

        $clear = isset($args['clear']) && (
                (is_string($args['clear']) && $args['clear']) || (is_array($args['clear']) && $args['clear']['status'])
            );

        $row = '';
        if ($type === 'row')
        {
            // Visible Filters
            $visible_filters = array_filter($filters, function ($filter)
            {
                return !isset($filter['visibility']) || $filter['visibility'];
            });

            // Auto Width
            [$this->col_filter, $this->col_button] = $this->helper->column(count($visible_filters), $buttons);

            // Row container
            $row .= '<div class="lsd-search-row '
                . ($this->is_more_options ? 'lsd-search-included-in-more ' : '')
                . ($this->is_boxed ? '' : 'lsd-box-less-search ')
                . '"><div class="lsd-row lsd-grid-container">';

            // Filters
            foreach ($filters as $filter)
            {
                $visibility = !isset($filter['visibility']) || $filter['visibility'];

                // Manual Width
                $col_class = isset($filter['width']) && $filter['width'] ? 'lsd-col-span-' . ((int) $filter['width']) : $this->col_filter;

                $field = $this->filter($filter);
                if (!$field) continue;

                $row .= '<div class="lsd-search-filter ' . sanitize_html_class($col_class) . ' ' . sanitize_html_class(!$visibility ? 'lsd-search-field-hidden' : '') . '">';
                $row .= $field;
                $row .= '</div>';
            }

            // Buttons
            if ($buttons) $row .= $this->buttons('buttons', $args);
            if ($clear) $row .= $this->buttons('clear', $args);

            $row .= '</div></div>';
        }
        else
        {
            $this->is_more_options = true;

            $more_options = isset($args['more_options']) && is_array($args['more_options']) ? $args['more_options'] : ($this->atts['lsd_more_options'] ?? []);

            $mo_button = $more_options['button'] ?? esc_html__('More Options', 'listdom');
            $mo_type = $more_options['type'] ?? 'normal';
            $mo_width = isset($more_options['type']) && $more_options['type'] === 'popup' ? (int) $more_options['width'] : 60;

            $row .= '<div class="lsd-search-row-more-options '. ($this->is_boxed ? '': 'lsd-m-0 lsd-rounded-10') .'" data-for=".lsd-search-devices-' . esc_attr($this->device_key) . '" data-width="' . esc_attr($mo_width) . '" data-type="' . esc_attr($mo_type) . '">';
            $row .= '<span class="lsd-search-more-options"> ' . esc_html($mo_button) . '<i class="lsd-icon fa fa-plus"></i></span>';
            $row .= '</div>';
        }

        return $row;
    }

    private function buttons(string $type = 'buttons', array $args = []): string
    {
        // Alignment & Width
        $width = $args[$type]['width'] ?? (int) preg_replace('/\D/', '', $this->col_button);
        $alignment = $args[$type]['alignment'] ?? 'left';

        // Label
        $label = isset($args[$type]['label']) && trim($args[$type]['label'])
            ? $args[$type]['label']
            : __('Search', 'listdom');

        $total = 12;
        $remaining = $total - $width;
        $side = $alignment === 'center' ? (int) floor($remaining / 2) : $remaining;

        $buttons = '';

        // Add left spacer if needed
        if (in_array($alignment, ['center', 'right']) && $side > 0) $buttons .= '<div class="lsd-col-span-' . $side . '"></div>';

        $buttons .= '<div class="lsd-search-' . $type . ' lsd-col-span-' . esc_attr($width) . '">';

        // Shortcode Input
        if (!empty($this->form['page']) && !empty($this->form['shortcode'])) $buttons .= '<input type="hidden" name="sf-shortcode" value="' . esc_attr($this->form['shortcode']) . '">';

        // Language Input
        if ($lang = $this->current('lang')) $buttons .= '<input type="hidden" name="lang" value="' . esc_attr($lang) . '">';

        // Submit Button
        $buttons .= '<div class="lsd-search-' . $type . '-submit">';
        $buttons .= '<button type="submit" class="lsd-search-button ' .
            ($type === 'clear' ? 'lsd-search-clear-all' : '') . ' lsd-color-m-bg ' .
            sanitize_html_class(LSD_Main::get_text_class()) .
            '">';

        $buttons .= esc_html($label) . '</button></div>';
        $buttons .= '</div>';

        // Add right spacer if center-aligned
        if ($alignment === 'center' && $side > 0) $buttons .= '<div class="lsd-col-span-' . $side . '"></div>';

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

        // Field Type
        $type = $this->helper->get_type_by_key($key);

        // Invalid Type
        if (!$type) return $output;

        switch ($type)
        {
            case 'textsearch':

                $output = $this->field_textsearch($filter);
                break;

            case 'taxonomy':

                $output = $this->field_taxonomy($filter);
                break;

            case 'text':
            case 'tel':
            case 'textarea':

                $output = $this->field_text($filter);
                break;

            case 'numeric':
            case 'number':

                $output = $this->field_number($filter);
                break;

            case 'dropdown':
            case 'checkbox':
            case 'radio':

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

            case 'acf_dropdown':

                $output = $this->field_acf_dropdown($filter);
                break;

            case 'acf_range':

                $output = $this->field_acf_range($filter);
                break;

            case 'acf_true_false':

                $output = $this->field_acf_true_false($filter);
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

        $id = 'lsd_search_' . $this->device_key . '_' . $this->id . '_' . $key;
        $name = 'sf-' . $this->helper->standardize_key($key);

        $default = $filter['default_value'] ?? '';
        $current = $this->current($name, $default);

        $output = '<label for="' . esc_attr($id) . '">' . esc_html__($title, 'listdom') . '</label>';
        $output .= '<input type="text" name="' . esc_attr($name) . '" id="' . esc_attr($id) . '" placeholder="' . esc_attr__($placeholder, 'listdom') . '" value="' . esc_attr($current) . '">';

        return $output;
    }

    public function field_taxonomy_hierarchy($name, $current, $all_terms, $predefined_terms, $terms, $render_type, $items_per_row = 12): string
    {
        if (!is_array($current) && $current) $current = [$current];
        else if (!is_array($current)) $current = [];

        $render = '<ul class="lsd-hierarchy-list lsd-grid-container">';
        foreach ($terms as $key => $term)
        {
            // Term is not in the predefined terms
            if (!$all_terms && count($predefined_terms) && !isset($predefined_terms[$key])) continue;

            $render .= '<li class="lsd-col-span-' . esc_attr($items_per_row) . '"' . (isset($term['children']) && $term['children'] ? ' class="children"' : '') . '>';

            if ($render_type === 'checkboxes') $render .= '<label class="lsd-search-checkbox-label"><input type="checkbox" class="' . esc_attr($key) . '" name="' . esc_attr($name) . '[]" value="' . esc_attr($key) . '" ' . (in_array($key, $current) ? 'checked="checked"' : '') . '>' . esc_html($term["name"]) . '</label>';
            if (isset($term['children']) && $term['children'])
            {
                if ($items_per_row == 12)
                {
                    $render .= $this->field_taxonomy_hierarchy($name, $current, $all_terms, $predefined_terms, $term['children'], $render_type, $items_per_row);
                }
                else
                {
                    foreach ($term['children'] as $child_key => $child_term)
                    {
                        $render .= '<li class="lsd-col-span-' . esc_attr($items_per_row) . '">';

                        if ($render_type === 'checkboxes') $render .= '<label class="lsd-search-checkbox-label"><input type="checkbox" class="' . esc_attr($child_key) . '" name="' . esc_attr($name) . '[]" value="' . esc_attr($child_key) . '" ' . (in_array($child_key, $current) ? 'checked="checked"' : '') . '>' . esc_html($child_term["name"]) . '</label>';

                        $render .= '</li>';
                    }
                }
            }

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
        $dropdown_style = $filter['dropdown_style'] ?? 'enhanced';
        $placeholder = isset($filter['placeholder']) && trim($filter['placeholder']) ? $filter['placeholder'] : $title;

        $all_terms = $filter['all_terms'] ?? 1;
        $items_per_row = $filter['items_per_row'] ?? 12;
        $predefined_terms = isset($filter['terms']) && is_array($filter['terms']) ? $filter['terms'] : [];

        $id = 'lsd_search_' . $this->device_key . '_' . $this->id . '_' . $key;
        $name = 'sf-' . $this->helper->standardize_key($key);

        $default = $filter['default_value'] ?? '';
        if (trim($default) && !is_numeric($default) && $filter['method'] !== 'text-input') $default = $this->helper->get_term_id($key, $default);

        $current = $this->current($name, $default);

        $label = '<label for="' . esc_attr($id) . '">' . esc_html__($title, 'listdom') . '</label>';
        $output = '';

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
            if (!is_array($current)) $current = [];

            $output .= '<input type="hidden" name="' . esc_attr($name) . '[]" value="">';
            $output .= '<select class="' . esc_attr($key) . '" name="' . esc_attr($name) . '[]" id="' . esc_attr($id) . '" placeholder="' . esc_attr__($placeholder, 'listdom') . '" multiple data-enhanced="' . ($dropdown_style === 'enhanced' ? 1 : 0) . '">';

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

            $output .= $this->field_taxonomy_hierarchy($name, $current, $all_terms, $predefined_terms, $terms, 'checkboxes', $items_per_row);
        }
        else if ($method === 'radio')
        {
            $terms = $this->helper->get_terms($filter);
            $output .= '<div class="lsd-grid-container">';
            foreach ($terms as $key => $term)
            {
                // Term is not in the predefined terms
                if (!$all_terms && count($predefined_terms) && !isset($predefined_terms[$key])) continue;

                $output .= '<label class="lsd-search-radio-label lsd-col-span-' . esc_attr($items_per_row) . '"><input type="radio" class="' . esc_attr($key) . '" name="' . esc_attr($name) . '" value="' . esc_attr($key) . '" ' . ($current == $key ? 'checked="checked"' : '') . '>' . esc_html($term) . '</label>';
            }
            $output .= '</div>';
        }
        else if ($method === 'hierarchical' && $this->isPro())
        {
            $output .= $this->helper->hierarchical($filter, [
                'id' => $id,
                'name' => $name,
                'current' => $current,
            ]);
        }

        return trim($output) ? $label . $output : '';
    }

    public function field_text($filter): string
    {
        $key = $filter['key'] ?? '';
        $default = $filter['default_value'] ?? '';

        $name = 'sf-' . $this->helper->standardize_key($key) . '-lk';

        if (strpos($key, 'acf_email_') === 0 || strpos($key, 'acf_text_') === 0)
        {
            $key = str_replace('acf_email_', '', $key);
            $key = str_replace('acf_text_', '', $key);

            $name = 'sf-acf-' . $this->helper->standardize_key($key) . '-atx';
        }

        $id = 'lsd_search_' . $this->device_key . '_' . $this->id . '_' . $key;
        $title = $filter['title'] ?? '';
        $current = $this->current($name, $default);

        return $this->helper->text($filter, [
            'id' => $id,
            'name' => $name,
            'title' => $title,
            'current' => $current,
        ]);
    }

    public function field_number($filter): string
    {
        $key = $filter['key'];
        $default = $filter['default_value'] ?? '';

        if (strpos($key, 'acf_number_') === 0)
        {
            $key = str_replace('acf_number_', '', $key);

            $name = 'sf-acf-' . $this->helper->standardize_key($key) . '-nma';
            $dropdown_name = 'sf-acf-' . $this->helper->standardize_key($key) . '-nmd';
            $min_name = 'sf-acf-' . $this->helper->standardize_key($key) . '-ara-min';
            $max_name = 'sf-acf-' . $this->helper->standardize_key($key) . '-ara-max';
        }
        else
        {
            $name = 'sf-' . $this->helper->standardize_key($key) . '-eq';
            $dropdown_name = 'sf-' . $this->helper->standardize_key($key) . '-grq';
            $min_name = 'sf-' . $this->helper->standardize_key($key) . '-grb-min';
            $max_name = 'sf-' . $this->helper->standardize_key($key) . '-grb-max';
        }

        $min_current = $this->current($min_name, $default);
        $dropdown_current = $this->current($dropdown_name, $default);
        $current = $this->current($name, $default);

        $max_default = $filter['max_default_value'] ?? '';
        $max_current = $this->current($max_name, $max_default);

        $id = 'lsd_search_' . $this->device_key . '_' . $this->id . '_' . $key;
        $title = $filter['title'] ?? '';

        return $this->helper->number($filter, [
            'id' => $id,
            'name' => $name,
            'min_name' => $min_name,
            'min_current' => $min_current,
            'max_name' => $max_name,
            'max_current' => $max_current,
            'title' => $title,
            'current' => $current,
            'dropdown_name' => $dropdown_name,
            'dropdown_current' => $dropdown_current,
        ]);
    }

    public function field_dropdown($filter): string
    {
        $key = $filter['key'] ?? '';
        $method = $filter['method'] ?? 'dropdown';
        $title = $filter['title'] ?? '';
        $dropdown_style = $filter['dropdown_style'] ?? 'enhanced';
        $placeholder = isset($filter['placeholder']) && trim($filter['placeholder']) ? $filter['placeholder'] : $title;

        $all_terms = $filter['all_terms'] ?? 1;
        $predefined_terms = isset($filter['terms']) && is_array($filter['terms']) ? $filter['terms'] : [];

        $id = 'lsd_search_' . $this->device_key . '_' . $this->id . '_' . $key;
        $name = 'sf-' . $this->helper->standardize_key($key) . '-eq';

        $default = $filter['default_value'] ?? '';
        $current = $this->current($name, $default);

        $label = '<label for="' . esc_attr($id) . '">' . esc_html__($title, 'listdom') . '</label>';
        $output = '';

        if ($method === 'dropdown')
        {
            $output .= '<select name="' . esc_attr($name) . '" id="' . esc_attr($id) . '" placeholder="' . esc_attr__($placeholder, 'listdom') . '" data-enhanced="' . ($dropdown_style === 'enhanced' ? 1 : 0) . '">';
            $output .= '<option value="">' . esc_html__($placeholder, 'listdom') . '</option>';

            $terms = $this->helper->get_terms($filter, true);
            foreach ($terms as $key => $term)
            {
                if (!$all_terms && count($predefined_terms) && !isset($predefined_terms[$key])) continue;
                $output .= '<option value="' . esc_attr($term) . '" ' . ($current == $term ? 'selected="selected"' : '') . '>' . esc_html($term) . '</option>';
            }

            $output .= '</select>';
        }
        else if ($method === 'dropdown-multiple')
        {
            $name = 'sf-' . $this->helper->standardize_key($key) . '-in';
            $current = $this->current($name, explode(',', $default));

            $output .= '<select name="' . esc_attr($name) . '[]" id="' . esc_attr($id) . '" placeholder="' . esc_attr__($placeholder, 'listdom') . '" multiple data-enhanced="' . ($dropdown_style === 'enhanced' ? 1 : 0) . '">';

            $terms = $this->helper->get_terms($filter, true);
            foreach ($terms as $key => $term)
            {
                // Term is not in the predefined terms
                if (!$all_terms && count($predefined_terms) && !isset($predefined_terms[$key])) continue;

                $output .= '<option value="' . esc_attr($term) . '" ' . (in_array($term, $current) ? 'selected="selected"' : '') . '>' . esc_html($term) . '</option>';
            }

            $output .= '</select>';
        }
        else if ($method === 'text-input')
        {
            $output .= '<input type="text" name="' . esc_attr($name) . '" id="' . esc_attr($id) . '" placeholder="' . esc_attr__($placeholder, 'listdom') . '" value="' . esc_attr($current) . '">';
        }
        else if ($method === 'checkboxes')
        {
            $name = 'sf-' . $this->helper->standardize_key($key) . '-in';
            $current = $this->current($name, explode(',', $default));

            $terms = $this->helper->get_terms($filter, true);
            foreach ($terms as $key => $term)
            {
                // Term is not in the predefined terms
                if (!$all_terms && count($predefined_terms) && !isset($predefined_terms[$key])) continue;

                $output .= '<label class="lsd-search-checkbox-label"><input type="checkbox" name="' . esc_attr($name) . '[]" value="' . esc_attr($term) . '" ' . (in_array($term, $current) ? 'checked="checked"' : '') . '>' . esc_html($term) . '</label>';
            }
        }
        else if ($method === 'radio')
        {
            $current = $this->current($name, explode(',', $default));

            $terms = $this->helper->get_terms($filter, true);
            foreach ($terms as $key => $term)
            {
                // Term is not in the predefined terms
                if (!$all_terms && count($predefined_terms) && !isset($predefined_terms[$key])) continue;
                $output .= '<label class="lsd-search-checkbox-label"><input type="radio" name="' . esc_attr($name) . '" value="' . esc_attr($term) . '" ' . ($term == $current ? 'checked="checked"' : '') . '>' . esc_html($term) . '</label>';
            }
        }

        return trim($output) ? $label . $output : '';
    }

    public function field_price($filter): string
    {
        $key = $filter['key'] ?? '';
        $method = $filter['method'] ?? 'dropdown-plus';
        $title = $filter['title'] ?? '';
        $dropdown_style = $filter['dropdown_style'] ?? 'enhanced';

        $min_placeholder = isset($filter['placeholder']) && trim($filter['placeholder']) ? $filter['placeholder'] : $title;
        $max_placeholder = isset($filter['max_placeholder']) && trim($filter['max_placeholder']) ? $filter['max_placeholder'] : $title;

        $id = 'lsd_search_' . $this->device_key . '_' . $this->id . '_' . $key;

        $min_default = $filter['default_value'] ?? '';
        $max_default = $filter['max_default_value'] ?? '';
        $class = $method === 'range' ? 'lsd-search-range ' : '';

        $output = '<div class="' . esc_attr($class) . '">';
        $output .= '<label for="' . esc_attr($id) . '">' . esc_html__($title, 'listdom') . '</label>';
        $output .= $method === 'range' ? '<div class="lsd-search-range-inner">' : '';

        if ($method === 'dropdown-plus')
        {
            $min = $filter['min'] ?? 0;
            $max = $filter['max'] ?? 100;
            $increment = $filter['increment'] ?? 10;
            $th_separator = isset($filter['th_separator']) && $filter['th_separator'];

            $name = 'sf-att-' . $this->helper->standardize_key($key) . '-grq';
            $current = $this->current($name, $min_default);

            $output .= '<select name="' . esc_attr($name) . '" id="' . esc_attr($id) . '" placeholder="' . esc_attr__($min_placeholder, 'listdom') . '" data-enhanced="' . ($dropdown_style === 'enhanced' ? 1 : 0) . '">';
            $output .= '<option value="">' . $min_placeholder . '</option>';

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
            $min_name = 'sf-att-' . $this->helper->standardize_key($key) . '-bt-min';
            $min_current = $this->current($min_name, $min_default);

            $max_name = 'sf-att-' . $this->helper->standardize_key($key) . '-bt-max';
            $max_current = $this->current($max_name, $max_default);

            $output .= '<div class="lsd-search-mm-input">';
            $output .= '<input type="text" name="' . esc_attr($min_name) . '" id="' . esc_attr($id) . '" placeholder="' . esc_attr__($min_placeholder, 'listdom') . '" value="' . esc_attr($min_current) . '">';
            $output .= '<input type="text" name="' . esc_attr($max_name) . '" id="' . esc_attr($id) . '" placeholder="' . esc_attr__($max_placeholder, 'listdom') . '" value="' . esc_attr($max_current) . '">';
            $output .= '</div>';
        }
        else if ($method === 'range')
        {
            $min_name = 'sf-att-' . $this->helper->standardize_key($key) . '-bt-min';
            $min_current = $this->current($min_name, $min_default);
            $max_name = 'sf-att-' . $this->helper->standardize_key($key) . '-bt-max';
            $max_current = $this->current($max_name, $max_default);
            $prepend = $filter['prepend'] ?? '';
            $min = $filter['min'] ?? 0;
            $max = $filter['max'] ?? 100;
            $increment = $filter['increment'] ?? 10;

            $output .= $this->helper->range($filter, [
                'id' => $id,
                'min_name' => $min_name,
                'min_current' => $min_current,
                'max_name' => $max_name,
                'max_current' => $max_current,
                'default' => $min_default,
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

    public function field_class($filter): string
    {
        $key = $filter['key'] ?? '';
        $method = $filter['method'] ?? 'dropdown';
        $title = $filter['title'] ?? '';
        $dropdown_style = $filter['dropdown_style'] ?? 'enhanced';
        $placeholder = isset($filter['placeholder']) && trim($filter['placeholder']) ? $filter['placeholder'] : $title;

        $id = 'lsd_search_' . $this->device_key . '_' . $this->id . '_' . $key;

        $default = $filter['default_value'] ?? '';
        if (!is_numeric($default)) $default = substr_count($default, '$');

        $label = '<label for="' . esc_attr($id) . '">' . esc_html__($title, 'listdom') . '</label>';
        $output = '';

        if ($method === 'dropdown')
        {
            $name = 'sf-att-' . $this->helper->standardize_key($key) . '-eq';
            $current = $this->current($name, $default);

            $output .= '<select name="' . esc_attr($name) . '" id="' . esc_attr($id) . '" placeholder="' . esc_attr__($placeholder, 'listdom') . '" data-enhanced="' . ($dropdown_style === 'enhanced' ? 1 : 0) . '">';

            $output .= '<option value="">' . esc_html__('Any', 'listdom') . '</option>';
            $output .= '<option value="1" ' . (($current == 1) ? 'selected="selected"' : '') . '>' . esc_html__('$', 'listdom') . '</option>';
            $output .= '<option value="2" ' . (($current == 2) ? 'selected="selected"' : '') . '>' . esc_html__('$$', 'listdom') . '</option>';
            $output .= '<option value="3" ' . (($current == 3) ? 'selected="selected"' : '') . '>' . esc_html__('$$$', 'listdom') . '</option>';
            $output .= '<option value="4" ' . (($current == 4) ? 'selected="selected"' : '') . '>' . esc_html__('$$$$', 'listdom') . '</option>';

            $output .= '</select>';
        }

        return trim($output) ? $label . $output : '';
    }

    public function field_address($filter): string
    {
        $key = $filter['key'] ?? '';
        $method = $filter['method'] ?? 'text-input';
        $title = $filter['title'] ?? '';
        $placeholder = isset($filter['placeholder']) && trim($filter['placeholder']) ? $filter['placeholder'] : $title;

        $id = 'lsd_search_' . $this->device_key . '_' . $this->id . '_' . $key;
        $name = 'sf-att-' . $this->helper->standardize_key($key) . '-lk';

        $default = $filter['default_value'] ?? '';
        $label = '<label for="' . esc_attr($id) . '">' . esc_html__($title, 'listdom') . '</label>';
        $output = '';

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

        return trim($output) ? $label . $output : '';
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

        $id = 'lsd_search_' . $this->device_key . '_' . $this->id . '_' . $key;
        $name = 'sf-' . $this->helper->standardize_key($key);

        $default = $filter['default_value'] ?? '';
        $current = $this->current($name, $default);

        $months = [];
        for ($i = 0; $i <= 13; $i++) $months[] = LSD_Base::date(strtotime('+' . $i . ' Months'), 'M Y');

        $output = '<label for="' . esc_attr($id) . '">' . esc_html__($title, 'listdom') . '</label>';
        $output .= '<input type="text" class="lsd-date-range-picker" data-format="' . strtoupper(esc_attr($format)) . '" data-periods="' . htmlspecialchars(json_encode($months), ENT_QUOTES, 'UTF-8') . '" name="' . esc_attr($name) . '" id="' . esc_attr($id) . '" placeholder="' . esc_attr__($placeholder, 'listdom') . '" value="' . esc_attr($current) . '" autocomplete="off">';

        return $output;
    }

    public function field_acf_dropdown($filter): string
    {
        $key = $filter['key'] ?? '';
        $method = $filter['method'] ?? 'dropdown';
        $dropdown_style = $filter['dropdown_style'] ?? 'enhanced';

        $key = str_replace('acf_true_false_', '', $key);
        $key = str_replace('acf_select_', '', $key);
        $key = str_replace('acf_radio_', '', $key);
        $key = str_replace('acf_checkbox_', '', $key);

        $id = 'lsd_search_' . $this->device_key . '_' . $this->id . '_' . $key;
        $name = 'sf-acf-' . $this->helper->standardize_key($key) . '-dra';
        $default = $filter['default_value'] ?? '';
        $current = $this->current($name, $default);

        $title = $filter['title'] ?? '';
        $placeholder = isset($filter['placeholder']) && trim($filter['placeholder']) ? $filter['placeholder'] : $title;
        $acf_options = $this->helper->acf_field_data($key, 'choices')[0] ?? [];

        $label = '<label for="' . esc_attr($id) . '">' . esc_html__($title, 'listdom') . '</label>';
        $output = '';

        if ($method === 'dropdown')
        {
            $output .= $this->helper->acf_dropdown($filter, [
                'id' => $id,
                'name' => $name,
                'current' => $current,
                'title' => $title,
                'placeholder' => $placeholder,
                'options' => $acf_options,
            ]);
        }
        else if ($method === 'dropdown-multiple')
        {
            $name = 'sf-acf-' . $this->helper->standardize_key($key) . '-drm';
            $current = $this->current($name, explode(',', $default));
            $output .= '<select class="' . esc_attr($key) . '" name="' . esc_attr($name) . '[]" id="' . esc_attr($id) . '" placeholder="' . esc_attr__($placeholder, 'listdom') . '" multiple data-enhanced="' . ($dropdown_style === 'enhanced' ? 1 : 0) . '">';

            foreach ($acf_options as $value => $label)
            {
                $output .= '<option class="acf-option" value="' . esc_attr($value) . '" ' . (in_array($value, $current) ? 'selected="selected"' : '') . '>' . esc_html($label) . '</option>';
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
            $name = 'sf-acf-' . $this->helper->standardize_key($key) . '-drm';

            $output .= $this->field_acf_hierarchy($name, $current, $acf_options);
        }
        else if ($method === 'radio')
        {
            foreach ($acf_options as $key => $label)
            {
                $output .= '<label class="lsd-search-radio-label"><input type="radio" class="' . esc_attr($key) . '" name="' . esc_attr($name) . '" value="' . esc_attr($key) . '" ' . ($current == $key ? 'checked="checked"' : '') . '>' . esc_html($label) . '</label>';
            }
        }

        return trim($output) ? $label . $output : '';
    }

    public function field_acf_hierarchy($name, $current, $choices): string
    {
        if (!is_array($current) && $current) $current = [$current];
        else if (!is_array($current)) $current = [];

        $render = '<ul class="lsd-hierarchy-list">';
        foreach ($choices as $key => $label)
        {
            $render .= '<li>';
            $render .= '<label class="lsd-search-checkbox-label"><input type="checkbox" class="' . esc_attr($key) . '" name="' . esc_attr($name) . '[]" value="' . esc_attr($key) . '" ' . (in_array($key, $current) ? 'checked="checked"' : '') . '>' . esc_html($label) . '</label>';
            $render .= '</li>';
        }

        $render .= '</ul>';
        return $render;
    }

    public function field_acf_range($filter): string
    {
        $key = $filter['key'] ?? '';
        $key = str_replace('acf_range_', '', $key);

        $default = $filter['default_value'] ?? '';
        $max_default = $filter['max_default_value'] ?? '';

        $output = '';
        $id = 'lsd_search_' . $this->device_key . '_' . $this->id . '_' . $key;
        $min_name = 'sf-acf-' . $this->helper->standardize_key($key) . '-ara-min';
        $min_current = $this->current($min_name, $default);
        $max_name = 'sf-acf-' . $this->helper->standardize_key($key) . '-ara-max';
        $max_current = $this->current($max_name, $max_default);

        $title = $filter['title'] ?? '';
        $min = $filter['min'] ?? ($this->helper->acf_field_data($key, 'min')[0] ?? 0);
        $max = $filter['max'] ?? ($this->helper->acf_field_data($key, 'max')[0] ?? 100);
        $step = $filter['increment'] ?? ($this->helper->acf_field_data($key, 'step')[0] ?? 1);
        $prepend = $filter['prepend'] ?? ($this->helper->acf_field_data($key, 'prepend')[0] ?? '');

        $output .= '<div class="lsd-search-range">';
        $output .= '<label for="' . esc_attr($id) . '">' . esc_html__($title, 'listdom') . '</label>';
        $output .= $this->helper->range($filter, [
            'id' => $id,
            'min_name' => $min_name,
            'min_current' => $min_current,
            'max_name' => $max_name,
            'max_current' => $max_current,
            'default' => $default,
            'max_default' => $max_default,
            'min' => $min,
            'max' => $max,
            'step' => $step,
            'prepend' => $prepend,
        ]);

        $output .= '</div>';
        return $output;
    }

    public function field_acf_true_false($filter): string
    {
        $key = $filter['key'] ?? '';
        $key = str_replace('acf_true_false_', '', $key);

        $output = '';
        $id = 'lsd_search_' . $this->device_key . '_' . $this->id . '_' . $key;
        $name = 'sf-acf-' . $this->helper->standardize_key($key) . '-trf';
        $current = $this->current($name, '');
        $title = $filter['title'] ?? '';
        $default_value = $this->helper->acf_field_data($key, 'default_value')[0] ?? 0;

        $output .= '<div class="lsd-true-false-search">';
        $output .= '<label for="' . esc_attr($id) . '">' . esc_html__($title, 'listdom') . '</label>';
        $output .= '<label class="lsd-search-checkbox-label">';
        $output .= '<input type="hidden" class="lsd-search-checkbox-hidden" id="hidden-' . esc_attr($id) . '" name="' . esc_attr($name) . '" value="0">';
        $output .= '<input type="checkbox" class="lsd-search-checkbox-input" id="' . esc_attr($id) . '" name="' . esc_attr($name) . '" ' . (($default_value === 1 && $current === '') || (isset($current) && $current == 1) ? 'value="1" checked="checked"' : 'value="0"') . '>';
        $output .= esc_html($title);
        $output .= '</label>';
        $output .= '</div>';

        return $output;
    }

    public function device(string $action, bool $criteria = false, string $device = 'desktop')
    {
        $this->is_more_options = false;
        $this->device_key = $device;

        if ($device === 'tablet') $filters = $this->tablet;
        else if ($device === 'mobile') $filters = $this->mobile;
        else $filters = $this->desktop;

        $inherit = false;
        if ($device === 'desktop' || !isset($this->devices[$device]['inherit']) || $this->devices[$device]['inherit']) $inherit = true;

        $box = $this->devices[$device]['box'] ?? 1;
        if ($device !== 'desktop' && $inherit) $box = $this->devices['desktop']['box'] ?? 1;
        $this->is_boxed = (bool) $box;
        ?>
        <div class="lsd-search-devices-<?php echo esc_attr($device); ?>">
            <form action="<?php echo esc_url($action); ?>"
                  class="lsd-search-form <?php echo !$inherit ? 'lsd-search-not-inherit' : ''; ?>">
                <?php
                $HTML = '';
                foreach ($filters as $row) $HTML .= $this->row($row);

                // Display Criteria
                if ($criteria) $HTML .= $this->criteria();

                // Print the Search Form
                echo apply_filters('lsd_search_form_html', $HTML, $filters, $device);
                ?>
            </form>
        </div>
        <?php
    }
}
