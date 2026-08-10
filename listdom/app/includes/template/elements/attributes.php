<?php

class LSD_Template_Elements_Attributes extends LSD_Template
{
    public function __construct($settings = [])
    {
        $this->key            = 'attributes';
        $this->label          = $this->label();
        $this->category       = 'advanced';
        $this->template_types = ['single_listing', 'listing_card', 'info_window'];
        $this->icon           = self::icons($this->key);

        parent::__construct($settings);
    }

    protected function label()
    {
        return esc_html__('Listing Custom Fields', 'listdom');
    }

    public function controls(): void
    {
        $this->start_controls_section('content_' . $this->key,
        [
            'label' => esc_html__('Options', 'listdom'),
            'tab'   => self::TAB_CONTENT,
        ]);

        $this->add_control('layout',
        [
            'type'    => 'select',
            'class'   => 'lsd-admin-input',
            'label'   => esc_html__('Layout', 'listdom'),
            'default' => 'column',
            'options' => [
                'column' => esc_html__('Default', 'listdom'),
                'row'    => esc_html__('Inline', 'listdom'),
            ],
            'selectors' => [
                '{{WRAPPER}} .lsdtb-card-attributes' => 'flex-direction:{{VALUE}};',
            ],
        ]);

        $this->add_control('show_icons',
        [
            'type'    => 'switcher',
            'label'   => esc_html__('Show Icons', 'listdom'),
            'default' => 0,
        ]);

        $this->add_control('show_attribute_title',
        [
            'type'    => 'switcher',
            'label'   => esc_html__('Show Custom Field Title', 'listdom'),
            'default' => 1,
        ]);

        $this->end_controls_section();

        $this->start_controls_section('fields_'. $this->key,
        [
           'label' => esc_html__('Custom Fields', 'listdom'),
           'tab'   => self::TAB_CONTENT,
        ]);

        $terms = LSD_Main::get_attributes();
        $fields = [];
        foreach ($terms as $term)
        {
            $fields[$term->slug] = $term->name;
        }

        $this->add_control('fields',
            [
                'type'        => 'repeater',
                'label'       => esc_html__('Fields', 'listdom'),
                'description' => esc_html__('Select your desired fields to display', 'listdom'),
                'fields'      => [
                    [
                        'id'      => 'field_id',
                        'type'    => 'select',
                        'class'   => 'lsd-admin-input',
                        'label'   => '',
                        'options' => $fields,
                    ],
                    [
                        'id'      => 'field_global_icon',
                        'type'    => 'switcher',
                        'label'   => esc_html__('Global Icon', 'listdom'),
                        'default' => 1,
                        'condition' => [
                            'show_icons' => true,
                        ]
                    ],
                    [
                        'id'    => 'field_icon',
                        'class'   => 'lsd-admin-input',
                        'type'  => 'iconpicker',
                        'label' => esc_html__('Icon', 'listdom'),
                        'condition' => [
                            'field_global_icon' => false,
                            'show_icons' => true,
                        ]
                    ],
                    [
                        'id'      => 'field_display_name',
                        'type'    => 'switcher',
                        'label'   => esc_html__('Name', 'listdom'),
                        'default' => 1,
                    ],
                    [
                        'id'      => 'field_display_value',
                        'type'    => 'switcher',
                        'label'   => esc_html__('Value', 'listdom'),
                        'default' => 1,
                    ],
                    [
                        'id'      => 'field_link_icon',
                        'type'    => 'switcher',
                        'label'   => esc_html__('Link Icon', 'listdom'),
                        'default' => 0,
                        'condition' => [
                            'show_icons' => true,
                        ]
                    ],
                    [
                        'id'      => 'field_link_name',
                        'type'    => 'switcher',
                        'label'   => esc_html__('Link Name', 'listdom'),
                        'default' => 0,
                    ],
                ],
            ]);

        $this->end_controls_section();

        $this->start_controls_section('style_' . $this->key,
        [
            'label'      => esc_html__('Style', 'listdom'),
            'responsive' => true,
            'tab'        => self::TAB_STYLE,
        ]);

        $this->add_control('icon_scale',
        [
            'type'       => 'number',
            'class'      => 'lsd-admin-input',
            'label'      => esc_html__('Icon Scale', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsdtb-card-attributes .lsdtb-attr-key img' => 'transform:scale({{VALUE}});',
                '{{WRAPPER}} .lsdtb-card-attributes .lsdtb-attr-key svg' => 'transform:scale({{VALUE}});',
                '{{WRAPPER}} .lsdtb-card-attributes .lsdtb-attr-key i' => 'transform:scale({{VALUE}});',
            ],
        ]);

        $this->add_control('attributes_typography',
        [
            'type'       => 'typography',
            'label'      => esc_html__('Typography', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsdtb-card-attributes, {{WRAPPER}} .lsdtb-card-attributes a' => '{{VALUE}}',
            ],
        ]);

        $this->end_controls_section();

        $this->start_controls_section('checkbox_values_' . $this->key,
        [
            'label'      => esc_html__('Checkbox Values', 'listdom'),
            'responsive' => true,
            'tab'        => self::TAB_STYLE,
        ]);

        $this->add_control('checkbox_value_items_typography',
        [
            'type'       => 'typography',
            'label'      => esc_html__('Value Item Typography', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsdtb-attr.lsdtb-attr-checkbox .lsdtb-attr-value-item' => '{{VALUE}}',
            ],
        ]);

        $this->add_control('checkbox_value_items_color',
        [
            'type'       => 'colorpicker',
            'label'      => esc_html__('Value Item Color', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsdtb-attr.lsdtb-attr-checkbox .lsdtb-attr-value-item' => 'color:{{VALUE}};',
            ],
        ]);

        $this->add_control('checkbox_value_items_bg',
        [
            'type'       => 'colorpicker',
            'label'      => esc_html__('Value Item Background', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsdtb-attr.lsdtb-attr-checkbox .lsdtb-attr-value-item' => 'background-color:{{VALUE}};',
            ],
        ]);

        $this->add_control('checkbox_value_items_padding',
        [
            'type'       => 'padding',
            'label'      => esc_html__('Padding', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsdtb-attr.lsdtb-attr-checkbox .lsdtb-attr-value-item' => '{{VALUE}}',
            ],
        ]);

        $this->add_control('checkbox_value_items_border',
        [
            'type'       => 'border',
            'label'      => esc_html__('Border', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsdtb-attr.lsdtb-attr-checkbox .lsdtb-attr-value-item' => '{{VALUE}}',
            ],
        ]);

        $this->end_controls_section();
    }

    public function render(array $args = []): string
    {
        $listing_id = isset($args['listing_id']) ? (int) $args['listing_id'] : 0;
        if (!$listing_id) return '';

        $settings = isset($args['settings']) && is_array($args['settings']) ? $args['settings'] : [];
        $content_settings = isset($settings['content']) && is_array($settings['content']) ? $settings['content'] : [];
        $fields = isset($content_settings['fields']) && is_array($content_settings['fields']) ? $content_settings['fields'] : [];

        $listing_attributes = get_post_meta($listing_id, 'lsd_attributes', true);
        if (!is_array($listing_attributes)) $listing_attributes = [];
        if (!count($listing_attributes) || !count($fields)) return '';

        $show_icons = !empty($content_settings['show_icons']);
        $show_attribute_title = !isset($content_settings['show_attribute_title']) || !empty($content_settings['show_attribute_title']);

        $output = '';
        foreach ($fields as $field)
        {
            $field_id = $field['field_id'] ?? '';
            if ($field_id === '') continue;

            if (!is_numeric($field_id)) $term = get_term_by('slug', $field_id, LSD_Base::TAX_ATTRIBUTE);
            else $term = get_term($field_id);

            if (!isset($term->term_id) || $term->taxonomy !== LSD_Base::TAX_ATTRIBUTE) continue;

            $att = new LSD_Entity_Attribute($term->term_id);

            $raw_value = $listing_attributes[$term->slug] ?? '';
            $value = $att->render($raw_value);

            $has_value = trim((string) $value) !== '';
            if ($att->type === 'url') $has_value = trim((string) $raw_value) !== '';
            if (!$has_value) continue;

            $icon = '';
            if ($show_icons)
            {
                $global_icon = !empty($field['field_global_icon']);
                if ($global_icon) $icon = LSD_Kses::element($att->icon());
                else
                {
                    $icon_class = $field['field_icon'] ?? '';
                    if (is_array($icon_class) && isset($icon_class['value'])) $icon_class = $icon_class['value'];
                    if (trim((string) $icon_class) !== '') $icon = '<i class="lsd-icon ' . esc_attr($icon_class) . '"></i>';
                }
            }

            $display_name = !empty($field['field_display_name']) && $show_attribute_title;
            $name = $display_name ? esc_html($term->name) . ': ' : '';

            $display_value = $value;
            if ($att->type === 'url')
            {
                $link_url = trim((string) $raw_value);
                $label = get_term_meta($term->term_id, 'lsd_link_label', true);
                $link_text = trim($label) === '' ? $link_url : $label;

                $display_value_enabled = !isset($field['field_display_value']) || !empty($field['field_display_value']);
                $display_value = $display_value_enabled ? '<a href="' . esc_attr($link_url) . '">' . esc_html($link_text) . '</a>' : '';

                $link_icon = !empty($field['field_link_icon']);
                if ($link_icon && trim($icon) !== '' && $link_url !== '')
                {
                    $icon = '<a href="' . esc_attr($link_url) . '">' . $icon . '</a>';
                }

                $link_name = !empty($field['field_link_name']);
                if ($link_name && $name !== '' && $link_url !== '')
                {
                    $name_text = esc_html($term->name);
                    $name = '<a href="' . esc_attr($link_url) . '">' . $name_text . '</a>: ';
                }
            }
            else if (in_array($att->type, ['checkbox', 'dropdown'], true))
            {
                $items = $this->get_attribute_items($att->type, $raw_value);
                if (!count($items)) continue;
                $display_value = $this->render_attribute_items($items);
            }

            $has_key = trim($icon . $name) !== '';
            $has_value_output = trim((string) $display_value) !== '';

            if (!$has_key && !$has_value_output) continue;

            $value_output = $has_value_output ? '<span class="lsdtb-attr-value">' . LSD_Kses::element($display_value) . '</span>' : '';

            $output .= '<div class="lsdtb-attr lsdtb-attr-' . sanitize_html_class($att->type) . '" ' . LSD_Entity_Attribute::schema($term->term_id) . '>
                <span class="lsdtb-attr-key">' . $icon . $name . '</span>
                ' . $value_output . '
            </div>';
        }

        if (trim($output) === '') return '';

        return '<div class="lsd-template-element-attributes lsdtb-card-attributes">' . $output . '</div>';
    }

    private function get_attribute_items(string $type, $raw_value): array
    {
        if ($type === 'checkbox')
        {
            if (is_array($raw_value)) $items = $raw_value;
            else $items = array_map('trim', explode(',', (string) $raw_value));
        }
        else
        {
            $items = is_array($raw_value) ? $raw_value : [(string) $raw_value];
        }

        $items = array_filter($items, function ($item)
        {
            return trim((string) $item) !== '';
        });

        return array_values($items);
    }

    private function render_attribute_items(array $items): string
    {
        $output = [];
        foreach ($items as $item)
        {
            $output[] = '<span class="lsdtb-attr-value-item">' . esc_html((string) $item) . '</span>';
        }

        return implode(' ', $output);
    }
}
