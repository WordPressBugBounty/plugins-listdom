<?php

class LSD_Search_Builder extends LSD_Base
{
    /**
     * @var LSD_Search_Helper
     */
    public $helper;

    public function __construct()
    {
        $this->helper = new LSD_Search_Helper();
    }

    public function getAvailableFields($existingFields = [])
    {
        $existings = [];
        foreach ($existingFields as $row)
        {
            if (!isset($row['filters'])) continue;

            foreach ($row['filters'] as $key => $data)
            {
                $existings[] = $this->helper->standardize_key($key, false);
            }
        }

        $fields = [];

        // Text Search
        if (!in_array('s', $existings)) $fields[] = [
            'type' => 'textsearch',
            'key' => 's',
            'title' => esc_html__('Text Search', 'listdom'),
            'methods' => $this->getFieldMethods('textsearch'),
        ];

        // Category Taxonomy
        if (!in_array(LSD_Base::TAX_CATEGORY, $existings)) $fields[] = [
            'type' => 'taxonomy',
            'key' => LSD_Base::TAX_CATEGORY,
            'title' => esc_html__('Categories', 'listdom'),
            'methods' => $this->getFieldMethods('taxonomy', LSD_Base::TAX_CATEGORY),
        ];

        // Location Taxonomy
        if (!in_array(LSD_Base::TAX_LOCATION, $existings)) $fields[] = [
            'type' => 'taxonomy',
            'key' => LSD_Base::TAX_LOCATION,
            'title' => esc_html__('Locations', 'listdom'),
            'methods' => $this->getFieldMethods('taxonomy', LSD_Base::TAX_LOCATION),
        ];

        // Tag Taxonomy
        if (!in_array(LSD_Base::TAX_TAG, $existings)) $fields[] = [
            'type' => 'taxonomy',
            'key' => LSD_Base::TAX_TAG,
            'title' => esc_html__('Tags', 'listdom'),
            'methods' => $this->getFieldMethods('taxonomy', LSD_Base::TAX_TAG),
        ];

        // Feature Taxonomy
        if (!in_array(LSD_Base::TAX_FEATURE, $existings)) $fields[] = [
            'type' => 'taxonomy',
            'key' => LSD_Base::TAX_FEATURE,
            'title' => esc_html__('Features', 'listdom'),
            'methods' => $this->getFieldMethods('taxonomy', LSD_Base::TAX_FEATURE),
        ];

        // Label Taxonomy
        if (!in_array(LSD_Base::TAX_LABEL, $existings)) $fields[] = [
            'type' => 'taxonomy',
            'key' => LSD_Base::TAX_LABEL,
            'title' => esc_html__('Labels', 'listdom'),
            'methods' => $this->getFieldMethods('taxonomy', LSD_Base::TAX_LABEL),
        ];

        // Price
        if (!in_array('price', $existings)) $fields[] = [
            'type' => 'price',
            'key' => 'price',
            'title' => esc_html__('Price', 'listdom'),
            'methods' => $this->getFieldMethods('price'),
        ];

        // Price Class
        if (!in_array('class', $existings)) $fields[] = [
            'type' => 'class',
            'key' => 'class',
            'title' => esc_html__('Price Class', 'listdom'),
            'methods' => $this->getFieldMethods('class'),
        ];

        // Address
        if (!in_array('address', $existings)) $fields[] = [
            'type' => 'address',
            'key' => 'address',
            'title' => esc_html__('Address', 'listdom'),
            'methods' => $this->getFieldMethods('address'),
        ];

        // Attributes
        $attributes = LSD_Main::get_attributes();

        foreach ($attributes as $attribute)
        {
            $type = get_term_meta($attribute->term_id, 'lsd_field_type', true);
            $key = 'att-' . $attribute->slug;

            // Skip URL, Email and Separator Fields
            if (in_array($type, ['url', 'email', 'separator'])) continue;
            if (in_array($key, $existings)) continue;

            $fields[] = [
                'type' => 'attribute',
                'key' => $key,
                'title' => $attribute->name,
                'description' => esc_html__('Custom Fields', 'listdom'),
                'methods' => $this->getFieldMethods($type),
            ];
        }

        // Apply Filters
        return apply_filters('lsd_search_fields', $fields, $existings, $this);
    }

    public function getAllFields(?string $key = null): array
    {
        // All Fields
        $all = $this->getAvailableFields();

        // Key / Value Pair
        $pairs = [];
        foreach ($all as $field)
        {
            if (!isset($field['key']) || !$field['key']) continue;

            $pairs[$field['key']] = $field;
        }

        // Single Field
        if ($key)
        {
            $key = $this->helper->standardize_key($key, false);
            return isset($pairs[$key]) && is_array($pairs[$key]) ? $pairs[$key] : [];
        }

        // All Fields
        return $pairs;
    }

    public function getFieldMethods($type, $key = '')
    {
        // Methods
        $methods = [
            'taxonomy' => [
                'dropdown' => esc_html__('Dropdown', 'listdom'),
                'dropdown-multiple' => esc_html__('Dropdown (Multiple Selection)', 'listdom'),
                'checkboxes' => esc_html__('Checkboxes', 'listdom'),
                'radio' => esc_html__('Radio Buttons', 'listdom'),
                'text-input' => esc_html__('Text Input', 'listdom'),
            ],
            'textsearch' => [
                'text-input' => esc_html__('Text Input', 'listdom'),
            ],
            'text' => [
                'text-input' => esc_html__('Text Input', 'listdom'),
            ],
            'textarea' => [
                'text-input' => esc_html__('Text Input', 'listdom'),
            ],
            'number' => [
                'number-input' => esc_html__('Number Input', 'listdom'),
                'dropdown' => esc_html__('Dropdown', 'listdom'),
                'dropdown-plus' => esc_html__('Dropdown+', 'listdom'),
            ],
            'numeric' => [
                'number-input' => esc_html__('Number Input', 'listdom'),
            ],
            'dropdown' => [
                'dropdown' => esc_html__('Dropdown', 'listdom'),
                'dropdown-multiple' => esc_html__('Dropdown (Multiple Selection)', 'listdom'),
                'text-input' => esc_html__('Text Input', 'listdom'),
                'checkboxes' => esc_html__('Checkboxes', 'listdom'),
                'radio' => esc_html__('Radio Buttons', 'listdom'),
            ],
            'checkbox' => [
                'dropdown' => esc_html__('Dropdown', 'listdom'),
                'dropdown-multiple' => esc_html__('Dropdown (Multiple Selection)', 'listdom'),
                'text-input' => esc_html__('Text Input', 'listdom'),
                'checkboxes' => esc_html__('Checkboxes', 'listdom'),
                'radio' => esc_html__('Radio Buttons', 'listdom'),
            ],
            'radio' => [
                'dropdown' => esc_html__('Dropdown', 'listdom'),
                'dropdown-multiple' => esc_html__('Dropdown (Multiple Selection)', 'listdom'),
                'text-input' => esc_html__('Text Input', 'listdom'),
                'checkboxes' => esc_html__('Checkboxes', 'listdom'),
                'radio' => esc_html__('Radio Buttons', 'listdom'),
            ],
            'address' => [
                'text-input' => esc_html__('Text Input', 'listdom'),
            ],
            'price' => [
                'dropdown-plus' => esc_html__('Dropdown+', 'listdom'),
                'mm-input' => esc_html__('Min/Max Input', 'listdom'),
            ],
            'class' => [
                'dropdown' => esc_html__('Dropdown', 'listdom'),
            ],
            'period' => [
                'date-range-picker' => esc_html__('Date Range Picker', 'listdom'),
            ],
            'acf_dropdown' => [
                'dropdown' => esc_html__('Dropdown', 'listdom'),
                'dropdown-multiple' => esc_html__('Dropdown (Multiple Selection)', 'listdom'),
                'text-input' => esc_html__('Text Input', 'listdom'),
                'checkboxes' => esc_html__('Checkboxes', 'listdom'),
                'radio' => esc_html__('Radio Buttons', 'listdom'),
            ],
            'acf_range' => [
                'acf_range' => esc_html__('Range Slider', 'listdom'),
            ],
            'acf_true_false' => [
                'checkbox' => esc_html__('Checkbox', 'listdom'),
            ],
        ];

        // Pro Methods
        if ($this->isPro())
        {
            if (in_array($key, [LSD_Base::TAX_CATEGORY, LSD_Base::TAX_LOCATION]))
                $methods['taxonomy']['hierarchical'] = esc_html__('Hierarchical Dropdowns', 'listdom');

            $methods['address']['radius'] = esc_html__('Radius Search', 'listdom');
            $methods['address']['radius-dropdown'] = esc_html__('Radius Search (Dropdown)', 'listdom');
            $methods['price']['range'] = esc_html__('Range Slider', 'listdom');
            $methods['number']['range'] = esc_html__('Range Slider', 'listdom');
        }

        // Apply Filters
        $methods = apply_filters('lsd_search_field_methods', $methods);

        return $methods[$type] ?? [];
    }

    public function device(string $device, WP_Post $post)
    {
        // Generate output
        return $this->include_html_file('metaboxes/search/device.php', [
            'return_output' => true,
            'parameters' => [
                'device' => $device,
                'post' => $post,
            ],
        ]);
    }

    public function params(string $device_key, $key, $data, $index)
    {
        $type = $this->helper->get_type_by_key($key);
        $methods = $this->getFieldMethods($type, $key);

        // Original Field Data
        $field = $this->getAllFields($key);

        // Invalid Field
        if (!count($field)) return '';

        // Generate output
        return $this->include_html_file('metaboxes/search/params.php', [
            'return_output' => true,
            'parameters' => [
                'device_key' => $device_key,
                'key' => $this->helper->standardize_key($key, false),
                'data' => $data,
                'field' => $field,
                'type' => $type,
                'i' => $index,
                'methods' => $methods,
                'helper' => $this->helper,
            ],
        ]);
    }

    public function row(int $post_id, string $device_key, $row, $index)
    {
        // Generate output
        return $this->include_html_file('metaboxes/search/row.php', [
            'return_output' => true,
            'parameters' => [
                'device_key' => $device_key,
                'post_id' => $post_id,
                'row' => $row,
                'i' => $index,
            ],
        ]);
    }
}
