<?php

class LSD_Template_Elements_Acf extends LSD_Template
{
    public function __construct($settings = [])
    {
        $this->key            = 'acf';
        $this->label          = $this->label();
        $this->category       = 'advanced';
        $this->template_types = ['single_listing', 'listing_card', 'info_window'];
        $this->icon           = self::icons($this->key);

        parent::__construct($settings);
    }

    protected function label()
    {
        return esc_html__('ACF Addon', 'listdom');
    }

    public function controls(): void
    {
        $this->start_controls_section('acf_fields_' . $this->key,
        [
            'label' => esc_html__('ACF Fields', 'listdom'),
            'tab'   => self::TAB_CONTENT,
        ]);

        $acf_fields = $this->get_acf_fields();
        $default_fields = [];
        foreach ($acf_fields as $key => $label)
        {
            $default_fields[] = [
                'acf_field_key' => $key,
                'field_label' => '',
            ];
        }

        $this->add_control('acf_fields',
        [
            'type'    => 'repeater',
            'label'   => '',
            'fields'  => [
                [
                    'id'      => 'acf_field_key',
                    'type'    => 'select',
                    'class'   => 'lsd-admin-input',
                    'label'   => esc_html__('Field', 'listdom'),
                    'options' => $acf_fields,
                    'default' => '',
                ],
                [
                    'id'      => 'field_label',
                    'type'    => 'text',
                    'class'   => 'lsd-admin-input',
                    'label'   => esc_html__('Label', 'listdom'),
                    'default' => '',
                ],
            ],
            'default' => $default_fields,
        ]);

        $this->end_controls_section();

        $this->start_controls_section('style_' . $this->key,
        [
            'label'      => esc_html__('Style', 'listdom'),
            'responsive' => true,
            'tab'        => self::TAB_STYLE
        ]);

        $this->start_controls_tabs('style_tabs');

        $this->start_controls_tab('style_label_tab',
        [
            'label' => esc_html__('Label', 'listdom'),
        ]);

        $this->add_control('label_typography',
        [
            'type'        => 'typography',
            'label'       => esc_html__('Typography', 'listdom'),
            'responsive'  => true,
            'selectors'   => [
                '{{WRAPPER}} .lsd-template-element-acf strong' => '{{VALUE}}',
                '{{WRAPPER}} .lsdtb-card-acf strong' => '{{VALUE}}',
            ],
        ]);

        $this->add_control('label_text_color',
        [
            'type'        => 'colorpicker',
            'label'       => esc_html__('Text Color', 'listdom'),
            'responsive'  => true,
            'selectors'   => [
                '{{WRAPPER}} .lsd-template-element-acf strong' => 'color:{{VALUE}};',
                '{{WRAPPER}} .lsdtb-card-acf strong' => 'color:{{VALUE}};',
            ],
        ]);

        $this->end_controls_tab();

        $this->start_controls_tab('style_value_tab',
        [
            'label' => esc_html__('Value', 'listdom'),
        ]);

        $this->add_control('value_typography',
        [
            'type'        => 'typography',
            'label'       => esc_html__('Typography', 'listdom'),
            'responsive'  => true,
            'selectors'   => [
                '{{WRAPPER}} .lsd-template-element-acf span' => '{{VALUE}}',
                '{{WRAPPER}} .lsdtb-card-acf span' => '{{VALUE}}',
            ],
        ]);

        $this->add_control('value_text_color',
        [
            'type'        => 'colorpicker',
            'label'       => esc_html__('Text Color', 'listdom'),
            'responsive'  => true,
            'selectors'   => [
                '{{WRAPPER}} .lsd-template-element-acf' => 'color:{{VALUE}};',
                '{{WRAPPER}} .lsdtb-card-acf' => 'color:{{VALUE}};',
            ],
        ]);

        $this->end_controls_tab();
        $this->end_controls_tabs();

        $this->end_controls_section();
    }

    private function get_acf_fields(): array
    {
        if (!function_exists('acf_get_field_groups')) return [];

        $field_groups = acf_get_field_groups([
            'post_type' => LSD_Base::PTYPE_LISTING,
            'post_status' => 'publish',
        ]);

        $fields = [];

        foreach ($field_groups as $group)
        {
            $group_fields = acf_get_fields($group['key']);
            if (!is_array($group_fields)) continue;

            foreach ($group_fields as $field)
            {
                if (in_array($field['type'], ['tab', 'accordion', 'message'], true)) continue;
                $fields[$field['name']] = $field['label'] . ' (' . $field['name'] . ')';
            }
        }

        return $fields;
    }

    public function render(array $args = []): string
    {
        $listing_id = isset($args['listing_id']) ? (int) $args['listing_id'] : 0;
        if (!$listing_id) return '';

        if (!class_exists(\LSDPACACF\Base::class))
        {
            return LSD_Main::alert(esc_html__('ACF addon should be installed and activated!', 'listdom'), 'warning');
        }

        if (!function_exists('acf_get_field') || !function_exists('get_field'))
        {
            return LSD_Main::alert(esc_html__('ACF plugin should be installed and activated!', 'listdom'), 'warning');
        }

        $settings = isset($args['settings']) && is_array($args['settings']) ? $args['settings'] : [];
        $content_settings = isset($settings['content']) && is_array($settings['content']) ? $settings['content'] : [];

        $fields = isset($content_settings['acf_fields']) && is_array($content_settings['acf_fields'])
            ? $content_settings['acf_fields']
            : [];

        if (!$fields)
        {
            $fallback_fields = $this->get_acf_fields();
            foreach ($fallback_fields as $key => $label)
            {
                $fields[] = [
                    'acf_field_key' => $key,
                    'field_label' => '',
                ];
            }
        }

        if (!$fields) return '';

        $output = '<div class="lsd-template-element-acf lsdtb-card-acf">';

        foreach ($fields as $field)
        {
            $field_key = $field['acf_field_key'] ?? '';
            if ($field_key === '') continue;

            $acf_field = acf_get_field($field_key);
            if (!$acf_field) continue;
            if (in_array($acf_field['type'], ['tab', 'accordion', 'message'], true)) continue;

            $acf_field['value'] = get_field($acf_field['key'], $listing_id);
            $field_html = LSD_Fields::acf($acf_field, $listing_id);

            $label = $field['field_label'] ?? '';
            if ($label === '') $label = $acf_field['label'];

            $output .= sprintf(
                '<div class="acf-field"><strong>%s:</strong> <span>%s</span></div>',
                esc_html($label),
                $field_html
            );
        }

        $output .= '</div>';

        if (trim(wp_strip_all_tags($output)) === '') return '';

        return apply_filters('lsd_template_element_acf_output', $output, $this, $args);
    }
}
