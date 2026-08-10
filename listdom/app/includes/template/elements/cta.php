<?php

class LSD_Template_Elements_Cta extends LSD_Template
{
    public function __construct($settings = [])
    {
        $this->key            = 'cta';
        $this->label          = $this->label();
        $this->category       = 'commerce';
        $this->template_types = ['single_listing', 'listing_card', 'info_window'];
        $this->icon           = self::icons($this->key);

        parent::__construct($settings);
    }

    protected function label()
    {
        return esc_html__('Call to Action', 'listdom');
    }

    public function controls(): void
    {
        $this->start_controls_section('content_' . $this->key,
            [
                'label' => esc_html__('Options', 'listdom'),
                'tab'   => self::TAB_CONTENT,
            ]);

        $this->add_control('text',
            [
                'type'    => 'text',
                'class'       => 'lsd-admin-input',
                'label'   => esc_html__('Button Text', 'listdom'),
                'default' => esc_html__('Click Here', 'listdom'),
            ]);

        $this->add_control('target',
            [
                'type'    => 'select',
                'class'   => 'lsd-admin-input',
                'label'   => esc_html__('Button Action', 'listdom'),
                'default' => 'details',
                'options' => $this->target_options(),
            ]);

        $this->add_control('url',
            [
                'type'        => 'text',
                'class'       => 'lsd-admin-input',
                'label'       => esc_html__('Custom Link', 'listdom'),
                'placeholder' => esc_attr__('https://example.com', 'listdom'),
                'condition'   => [
                    'target' => 'custom',
                ],
            ]);

        $this->add_control('content',
            [
                'type'        => 'textarea',
                'class'       => 'lsd-admin-input',
                'label'       => esc_html__('Popup Content', 'listdom'),
                'description' => esc_html__('HTML and shortcodes are supported.', 'listdom'),
                'default'     => '',
                'condition'   => [
                    'target' => 'popup',
                ],
            ]);

        $this->add_control('popup_width',
            [
                'type'        => 'text',
                'class'       => 'lsd-admin-input',
                'label'       => esc_html__('Popup Width', 'listdom'),
                'description' => esc_html__('Any valid CSS width, for example 520px or 60%.', 'listdom'),
                'default'     => '',
                'condition'   => [
                    'target' => 'popup',
                ],
            ]);

        $this->add_control('alignment',
            [
                'type'    => 'select',
                'class'   => 'lsd-admin-input',
                'label'   => esc_html__('Alignment', 'listdom'),
                'default' => 'left',
                'options' => [
                    'left'    => esc_html__('Left', 'listdom'),
                    'center'  => esc_html__('Center', 'listdom'),
                    'right'   => esc_html__('Right', 'listdom'),
                    'stretch' => esc_html__('Stretch', 'listdom'),
                ],
            ]);

        $this->end_controls_section();

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
                    '{{WRAPPER}} .lsd-template-element-cta' => 'background-color:{{VALUE}};',
                    '{{WRAPPER}} .lsdtb-card-cta' => 'background-color:{{VALUE}};',
                ],
            ]);

        $this->add_control('box_padding',
            [
                'type'       => 'padding',
                'label'      => esc_html__('Padding', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsd-template-element-cta' => '{{VALUE}}',
                    '{{WRAPPER}} .lsdtb-card-cta' => '{{VALUE}}',
                ],
            ]);

        $this->add_control('box_margin',
            [
                'type'       => 'margin',
                'label'      => esc_html__('Margin', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsd-template-element-cta' => '{{VALUE}}',
                    '{{WRAPPER}} .lsdtb-card-cta' => '{{VALUE}}',
                ],
            ]);

        $this->add_control('box_border',
            [
                'type'            => 'border',
                'label'           => esc_html__('Border', 'listdom'),
                'responsive'      => true,
                'radius_fallback' => 'box_border_radius',
                'selectors'       => [
                    '{{WRAPPER}} .lsd-template-element-cta' => '{{VALUE}}',
                    '{{WRAPPER}} .lsdtb-card-cta' => '{{VALUE}}',
                ],
            ]);

        $this->end_controls_section();

        $this->start_controls_section('button_' . $this->key,
            [
                'label'      => esc_html__('Button', 'listdom'),
                'responsive' => true,
                'tab'        => self::TAB_STYLE,
            ]);

        $this->start_controls_tabs('button_style_tabs');

        $this->start_controls_tab('button_normal_tab',
            [
                'label' => esc_html__('Normal', 'listdom'),
            ]);

        $this->add_control('button_typography',
            [
                'type'       => 'typography',
                'label'      => esc_html__('Typography', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsd-template-element-cta a' => '{{VALUE}}',
                    '{{WRAPPER}} .lsdtb-card-cta a' => '{{VALUE}}',
                    '{{WRAPPER}} .lsd-template-element-cta button' => '{{VALUE}}',
                    '{{WRAPPER}} .lsdtb-card-cta button' => '{{VALUE}}',
                ],
            ]);

        $this->add_control('button_color',
            [
                'type'       => 'colorpicker',
                'label'      => esc_html__('Text Color', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsd-template-element-cta a' => 'color:{{VALUE}};',
                    '{{WRAPPER}} .lsdtb-card-cta a' => 'color:{{VALUE}};',
                    '{{WRAPPER}} .lsd-template-element-cta button' => 'color:{{VALUE}};',
                    '{{WRAPPER}} .lsdtb-card-cta button' => 'color:{{VALUE}};',
                ],
            ]);

        $this->add_control('button_background',
            [
                'type'       => 'colorpicker',
                'label'      => esc_html__('Background', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsd-template-element-cta a' => 'background-color:{{VALUE}};',
                    '{{WRAPPER}} .lsdtb-card-cta a' => 'background-color:{{VALUE}};',
                    '{{WRAPPER}} .lsd-template-element-cta button' => 'background-color:{{VALUE}};',
                    '{{WRAPPER}} .lsdtb-card-cta button' => 'background-color:{{VALUE}};',
                ],
            ]);

        $this->add_control('button_padding',
            [
                'type'       => 'padding',
                'label'      => esc_html__('Padding', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsd-template-element-cta a' => '{{VALUE}}',
                    '{{WRAPPER}} .lsdtb-card-cta a' => '{{VALUE}}',
                    '{{WRAPPER}} .lsd-template-element-cta button' => '{{VALUE}}',
                    '{{WRAPPER}} .lsdtb-card-cta button' => '{{VALUE}}',
                ],
            ]);

        $this->add_control('button_border',
            [
                'type'            => 'border',
                'label'           => esc_html__('Border', 'listdom'),
                'responsive'      => true,
                'radius_fallback' => 'button_border_radius',
                'selectors'       => [
                    '{{WRAPPER}} .lsd-template-element-cta a' => '{{VALUE}}',
                    '{{WRAPPER}} .lsdtb-card-cta a' => '{{VALUE}}',
                    '{{WRAPPER}} .lsd-template-element-cta button' => '{{VALUE}}',
                    '{{WRAPPER}} .lsdtb-card-cta button' => '{{VALUE}}',
                ],
            ]);

        $this->end_controls_tab();

        $this->start_controls_tab('button_hover_tab',
            [
                'label' => esc_html__('Hover', 'listdom'),
            ]);

        $this->add_control('button_hover_color',
            [
                'type'       => 'colorpicker',
                'label'      => esc_html__('Text Color', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsd-template-element-cta a:hover' => 'color:{{VALUE}};',
                    '{{WRAPPER}} .lsdtb-card-cta a:hover' => 'color:{{VALUE}};',
                    '{{WRAPPER}} .lsd-template-element-cta button:hover' => 'color:{{VALUE}};',
                    '{{WRAPPER}} .lsdtb-card-cta button:hover' => 'color:{{VALUE}};',
                ],
            ]);

        $this->add_control('button_hover_background',
            [
                'type'       => 'colorpicker',
                'label'      => esc_html__('Background', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsd-template-element-cta a:hover' => 'background-color:{{VALUE}};',
                    '{{WRAPPER}} .lsdtb-card-cta a:hover' => 'background-color:{{VALUE}};',
                    '{{WRAPPER}} .lsd-template-element-cta button:hover' => 'background-color:{{VALUE}};',
                    '{{WRAPPER}} .lsdtb-card-cta button:hover' => 'background-color:{{VALUE}};',
                ],
            ]);

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->end_controls_section();
    }

    public function render(array $args = []): string
    {
        $listing_id = isset($args['listing_id']) ? (int) $args['listing_id'] : 0;
        if (!$listing_id) return '';

        $settings = isset($args['settings']) && is_array($args['settings']) ? $args['settings'] : [];
        $content_settings = isset($settings['content']) && is_array($settings['content']) ? $settings['content'] : [];

        $alignment = $this->get_alignment($content_settings);
        $override = $this->get_cta_override($content_settings);
        $popup_width = $this->get_popup_width($content_settings);

        $listing = new LSD_Entity_Listing($listing_id);
        $output = $listing->get_cta('single', [
            'cta_override' => $override,
            'alignment'    => $alignment,
            'popup_width'  => $popup_width,
        ]);

        if (trim((string) $output) === '') return '';

        if ($alignment === 'stretch')
        {
            $output = $this->stretch_cta_output($output);
        }

        $html = '<div class="lsd-template-element-cta lsdtb-card-cta lsd-template-element-cta-' . esc_attr($alignment) . '" style="' . esc_attr($this->get_alignment_style($alignment)) . '">' . $output . '</div>';

        return apply_filters('lsd_template_element_cta_output', $html, $this, $args);
    }

    private function target_options(): array
    {
        $targets = [
            'details'  => esc_html__('Open listing details page', 'listdom'),
            'lightbox' => esc_html__('Open listing details in lightbox', 'listdom'),
            'custom'   => esc_html__('Open a custom link', 'listdom'),
        ];

        if (LSD_Base::isPro())
        {
            $targets['popup'] = esc_html__('Popup with custom content', 'listdom');
        }

        return $targets;
    }

    private function get_alignment(array $content_settings): string
    {
        $alignment = isset($content_settings['alignment']) ? (string) $content_settings['alignment'] : 'left';

        return in_array($alignment, ['left', 'center', 'right', 'stretch'], true) ? $alignment : 'left';
    }

    private function get_alignment_style(string $alignment): string
    {
        if ($alignment === 'center') return 'text-align:center;';
        if ($alignment === 'right') return 'text-align:right;';

        return 'text-align:left;';
    }

    private function get_cta_override(array $content_settings): array
    {
        return [
            'text'    => isset($content_settings['text']) ? (string) $content_settings['text'] : '',
            'target'  => isset($content_settings['target']) ? (string) $content_settings['target'] : 'details',
            'url'     => isset($content_settings['url']) ? (string) $content_settings['url'] : '',
            'content' => isset($content_settings['content']) ? (string) $content_settings['content'] : '',
        ];
    }

    private function get_popup_width(array $content_settings)
    {
        $popup_width = isset($content_settings['popup_width']) ? trim((string) $content_settings['popup_width']) : '';

        return $popup_width !== '' ? $popup_width : null;
    }

    private function stretch_cta_output(string $output): string
    {
        return preg_replace('/<a\b(?![^>]*\bstyle=)/i', '<a style="display:inline-block;width:100%;"', $output, 1) ?: $output;
    }
}
