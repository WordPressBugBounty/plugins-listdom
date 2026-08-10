<?php

class LSD_Template_Elements_Abuse extends LSD_Template
{
    public function __construct($settings = [])
    {
        $this->key            = 'abuse';
        $this->label          = $this->label();
        $this->category       = 'contact_owner';
        $this->template_types = ['single_listing', 'listing_card', 'info_window'];
        $this->icon           = self::icons($this->key);

        parent::__construct($settings);
    }

    protected function label()
    {
        return esc_html__('Report Abuse', 'listdom');
    }

    public function controls(): void
    {
        $this->register_content_controls();
        $this->register_box_controls();
        $this->register_form_controls();
        $this->register_field_controls();
        $this->register_icon_controls();
        $this->register_consent_controls();
    }

    private function register_content_controls(): void
    {
        $this->start_controls_section('content_' . $this->key,
            [
                'label' => esc_html__('Options', 'listdom'),
                'tab'   => self::TAB_CONTENT,
            ]);

        $this->add_control('name_field',
            [
                'type'    => 'switcher',
                'label'   => esc_html__('Name Field', 'listdom'),
                'default' => 1,
            ]);

        $this->add_control('phone_field',
            [
                'type'    => 'switcher',
                'label'   => esc_html__('Phone Field', 'listdom'),
                'default' => 1,
            ]);

        $this->add_control('pc_enabled',
            [
                'type'    => 'switcher',
                'label'   => esc_html__('Privacy Consent', 'listdom'),
                'default' => 1,
            ]);

        $this->add_control('pc_label',
            [
                'type'        => 'text',
                'class'       => 'lsd-admin-input',
                'label'       => esc_html__('Consent Label', 'listdom'),
                'description' => esc_html__('Leave empty to use the default label. Use {{privacy_policy}} to include the default privacy policy link.', 'listdom'),
                'default'     => '',
                'condition'   => [
                    'pc_enabled' => true,
                ],
            ]);

        $this->end_controls_section();
    }

    private function register_box_controls(): void
    {
        $this->start_controls_section('box_' . $this->key,
            [
                'label'      => esc_html__('Box', 'listdom'),
                'responsive' => true,
                'tab'        => self::TAB_STYLE,
            ]);

        $this->add_control('box_background',
            [
                'type'       => 'colorpicker',
                'label'      => esc_html__('Background', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsdtb-card-abuse' => 'background-color:{{VALUE}};',
                ],
            ]);

        $this->add_control('box_border',
            [
                'type'            => 'border',
                'label'           => esc_html__('Border', 'listdom'),
                'responsive'      => true,
                'radius_fallback' => 'border_radius',
                'selectors'       => [
                    '{{WRAPPER}} .lsdtb-card-abuse' => '{{VALUE}}',
                ],
            ]);

        $this->add_control('box_padding',
            [
                'type'       => 'padding',
                'label'      => esc_html__('Padding', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsdtb-card-abuse' => '{{VALUE}}',
                ],
            ]);

        $this->end_controls_section();
    }

    private function register_form_controls(): void
    {
        $this->start_controls_section('form_' . $this->key,
            [
                'label'      => esc_html__('Form', 'listdom'),
                'responsive' => true,
                'tab'        => self::TAB_STYLE,
            ]);

        $this->add_control('row_gap',
        [
            'type'       => 'number',
            'class'      => 'lsd-admin-input',
            'label'      => esc_html__('Row Gap', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsdtb-card-abuse .lsd-report-abuse-form-row' => 'margin-bottom:{{VALUE}}px;',
            ],
        ]);

        $this->end_controls_section();
    }

    private function register_field_controls(): void
    {
        $this->start_controls_section('fields_' . $this->key,
            [
                'label'      => esc_html__('Fields', 'listdom'),
                'responsive' => true,
                'tab'        => self::TAB_STYLE,
            ]);

        $this->start_controls_tabs('abuse_fields_tabs');

        $this->start_controls_tab('abuse_fields_normal_tab',
            [
                'label' => esc_html__('Normal', 'listdom'),
            ]);

        $this->add_control('input_typography',
            [
                'type'       => 'typography',
                'label'      => esc_html__('Typography', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsdtb-card-abuse .lsd-form-control-input' => '{{VALUE}}',
                    '{{WRAPPER}} .lsdtb-card-abuse .lsd-form-control-textarea' => '{{VALUE}}',
                ],
            ]);

        $this->add_control('input_text_color',
            [
                'type'       => 'colorpicker',
                'label'      => esc_html__('Text Color', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsdtb-card-abuse .lsd-form-control-input' => 'color:{{VALUE}};',
                    '{{WRAPPER}} .lsdtb-card-abuse .lsd-form-control-textarea' => 'color:{{VALUE}};',
                ],
            ]);

        $this->add_control('input_bg_color',
            [
                'type'       => 'colorpicker',
                'label'      => esc_html__('Background', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsdtb-card-abuse .lsd-form-control-input' => 'background-color:{{VALUE}};',
                    '{{WRAPPER}} .lsdtb-card-abuse .lsd-form-control-textarea' => 'background-color:{{VALUE}};',
                ],
            ]);

        $this->add_control('input_border',
            [
                'type'            => 'border',
                'label'           => esc_html__('Border', 'listdom'),
                'responsive'      => true,
                'radius_fallback' => 'input_border_radius',
                'selectors'       => [
                    '{{WRAPPER}} .lsdtb-card-abuse .lsd-form-control-input' => '{{VALUE}}',
                    '{{WRAPPER}} .lsdtb-card-abuse .lsd-form-control-textarea' => '{{VALUE}}',
                ],
            ]);

        $this->add_control('input_padding',
            [
                'type'       => 'padding',
                'label'      => esc_html__('Padding', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsdtb-card-abuse .lsd-form-control-input' => '{{VALUE}}',
                    '{{WRAPPER}} .lsdtb-card-abuse .lsd-form-control-textarea' => '{{VALUE}}',
                ],
            ]);

        $this->add_control('placeholder_color',
            [
                'type'       => 'colorpicker',
                'label'      => esc_html__('Placeholder Color', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsdtb-card-abuse .lsd-form-control-input::placeholder' => 'color:{{VALUE}};',
                    '{{WRAPPER}} .lsdtb-card-abuse .lsd-form-control-textarea::placeholder' => 'color:{{VALUE}};',
                ],
            ]);

        $this->add_control('placeholder_typography',
            [
                'type'       => 'typography',
                'label'      => esc_html__('Placeholder Typography', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsdtb-card-abuse .lsd-form-control-input::placeholder' => '{{VALUE}}',
                    '{{WRAPPER}} .lsdtb-card-abuse .lsd-form-control-textarea::placeholder' => '{{VALUE}}',
                ],
            ]);

        $this->end_controls_tab();

        $this->start_controls_tab('abuse_fields_focus_tab',
            [
                'label' => esc_html__('Focus', 'listdom'),
            ]);

        $this->add_control('input_hover_text_color',
            [
                'type'       => 'colorpicker',
                'label'      => esc_html__('Text Color', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsdtb-card-abuse .lsd-form-control-input:hover' => 'color:{{VALUE}};',
                    '{{WRAPPER}} .lsdtb-card-abuse .lsd-form-control-input:focus' => 'color:{{VALUE}};',
                    '{{WRAPPER}} .lsdtb-card-abuse .lsd-form-control-textarea:hover' => 'color:{{VALUE}};',
                    '{{WRAPPER}} .lsdtb-card-abuse .lsd-form-control-textarea:focus' => 'color:{{VALUE}};',
                ],
            ]);

        $this->add_control('input_hover_bg_color',
            [
                'type'       => 'colorpicker',
                'label'      => esc_html__('Background', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsdtb-card-abuse .lsd-form-control-input:hover' => 'background-color:{{VALUE}};',
                    '{{WRAPPER}} .lsdtb-card-abuse .lsd-form-control-input:focus' => 'background-color:{{VALUE}};',
                    '{{WRAPPER}} .lsdtb-card-abuse .lsd-form-control-textarea:hover' => 'background-color:{{VALUE}};',
                    '{{WRAPPER}} .lsdtb-card-abuse .lsd-form-control-textarea:focus' => 'background-color:{{VALUE}};',
                ],
            ]);

        $this->add_control('input_hover_border',
            [
                'type'            => 'border',
                'label'           => esc_html__('Border', 'listdom'),
                'responsive'      => true,
                'radius_fallback' => 'input_hover_border_radius',
                'selectors'       => [
                    '{{WRAPPER}} .lsdtb-card-abuse .lsd-form-control-input:hover' => '{{VALUE}}',
                    '{{WRAPPER}} .lsdtb-card-abuse .lsd-form-control-input:focus' => '{{VALUE}}',
                    '{{WRAPPER}} .lsdtb-card-abuse .lsd-form-control-textarea:hover' => '{{VALUE}}',
                    '{{WRAPPER}} .lsdtb-card-abuse .lsd-form-control-textarea:focus' => '{{VALUE}}',
                ],
            ]);

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->end_controls_section();
    }

    private function register_icon_controls(): void
    {
        $this->start_controls_section('icon_' . $this->key,
            [
                'label'      => esc_html__('Icon', 'listdom'),
                'responsive' => true,
                'tab'        => self::TAB_STYLE,
            ]);

        $this->add_control('icon_color',
            [
                'type'       => 'colorpicker',
                'label'      => esc_html__('Color', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsdtb-card-abuse .lsd-report-abuse-form-row .lsd-fe-icon' => 'color:{{VALUE}};',
                ],
            ]);

        $this->add_control('icon_size',
            [
                'type'       => 'number',
                'class'      => 'lsd-admin-input',
                'label'      => esc_html__('Size', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsdtb-card-abuse .lsd-report-abuse-form-row .lsd-fe-icon' => 'font-size:{{VALUE}}px;',
                ],
            ]);

        $this->end_controls_section();
    }

    private function register_consent_controls(): void
    {
        $this->start_controls_section('consent_' . $this->key,
            [
                'label'      => esc_html__('Consent', 'listdom'),
                'responsive' => true,
                'tab'        => self::TAB_STYLE,
                'condition'  => [
                    'pc_enabled' => true,
                ],
            ]);

        $this->add_control('consent_text_color',
            [
                'type'       => 'colorpicker',
                'label'      => esc_html__('Text Color', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsdtb-card-abuse .lsd-privacy-consent-label span' => 'color:{{VALUE}};',
                    '{{WRAPPER}} .lsdtb-card-abuse .lsd-privacy-consent-description' => 'color:{{VALUE}};',
                    '{{WRAPPER}} .lsdtb-card-abuse .lsd-privacy-consent-description a' => 'color:{{VALUE}};',
                ],
            ]);

        $this->add_control('consent_typography',
            [
                'type'       => 'typography',
                'label'      => esc_html__('Typography', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsdtb-card-abuse .lsd-privacy-consent-label span' => '{{VALUE}}',
                    '{{WRAPPER}} .lsdtb-card-abuse .lsd-privacy-consent-description' => '{{VALUE}}',
                    '{{WRAPPER}} .lsdtb-card-abuse .lsd-privacy-consent-description a' => '{{VALUE}}',
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

        $element = new LSD_Element_Abuse($this->get_abuse_options($content_settings));
        $output = $element->get($listing_id);

        if (trim((string) $output) === '') return '';

        $html = '<div class="lsd-template-element-abuse lsdtb-card-abuse">' . $output . '</div>';

        return apply_filters('lsd_template_element_abuse_output', $html, $this, $args);
    }

    private function get_abuse_options(array $content_settings): array
    {
        return [
            'name_field'  => $this->enabled($content_settings, 'name_field', true),
            'phone_field' => $this->enabled($content_settings, 'phone_field', true),
            'pc_enabled'  => $this->enabled($content_settings, 'pc_enabled', true),
            'pc_label'    => isset($content_settings['pc_label']) ? (string) $content_settings['pc_label'] : '',
        ];
    }
}
