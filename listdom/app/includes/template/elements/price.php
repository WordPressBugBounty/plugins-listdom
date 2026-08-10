<?php

class LSD_Template_Elements_Price extends LSD_Template
{
    public function __construct($settings = [])
    {
        $this->key            = 'price';
        $this->label          = $this->label();
        $this->category       = 'commerce';
        $this->template_types = ['single_listing', 'listing_card', 'info_window'];
        $this->icon           = self::icons($this->key);

        parent::__construct($settings);
    }

    protected function label()
    {
        return esc_html__('Listing Price', 'listdom');
    }

    public function controls(): void
    {
        $this->start_controls_section('content_' . $this->key,
        [
            'label' => esc_html__('Options', 'listdom'),
            'tab'   => self::TAB_CONTENT,
        ]);

        $this->add_control('minimized',
        [
            'type'    => 'switcher',
            'label'   => esc_html__('Minimized Price', 'listdom'),
            'default' => 0,
        ]);

        $this->add_control('enable_price_link',
        [
            'type'    => 'switcher',
            'label'   => esc_html__('Link Price', 'listdom'),
            'default' => 0,
        ]);

        $this->add_control('price_link_url',
        [
            'type'        => 'url',
            'class'       => 'lsd-admin-input',
            'label'       => esc_html__('Price Link URL', 'listdom'),
            'placeholder' => esc_html__('https://your-link.com', 'listdom'),
            'condition'   => [
                'enable_price_link' => true,
            ],
        ]);

        $this->add_control('price_link_target',
        [
            'type'      => 'switcher',
            'label'     => esc_html__('Open in New Tab', 'listdom'),
            'default'   => 0,
            'condition' => [
                'enable_price_link' => true,
            ],
        ]);

        $this->add_control('price_link_nofollow',
        [
            'type'      => 'switcher',
            'label'     => esc_html__('Nofollow', 'listdom'),
            'default'   => 0,
            'condition' => [
                'enable_price_link' => true,
            ],
        ]);

        $this->end_controls_section();

        $this->start_controls_section('box_' . $this->key,
        [
            'label'      => esc_html__('Box', 'listdom'),
            'responsive' => true,
            'tab'        => self::TAB_STYLE,
        ]);

        $this->start_controls_tabs('box_style_tabs');

        $this->start_controls_tab('box_normal_tab',
        [
            'label' => esc_html__('Normal', 'listdom'),
        ]);

        $this->add_control('bg_color',
        [
            'type'       => 'colorpicker',
            'label'      => esc_html__('Background Color', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsdtb-card-price' => 'background-color:{{VALUE}};',
            ],
        ]);

        $this->add_control('text_color',
        [
            'type'       => 'colorpicker',
            'label'      => esc_html__('Text Color', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsdtb-card-price' => 'color:{{VALUE}};',
                '{{WRAPPER}} .lsdtb-card-price a' => 'color:{{VALUE}};',
            ],
        ]);

        $this->add_control('padding',
        [
            'type'       => 'padding',
            'label'      => esc_html__('Padding', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsdtb-card-price' => '{{VALUE}}',
            ],
        ]);

        $this->add_control('border',
        [
            'type'            => 'border',
            'label'           => esc_html__('Border', 'listdom'),
            'responsive'      => true,
            'radius_fallback' => 'border_radius',
            'selectors'       => [
                '{{WRAPPER}} .lsdtb-card-price' => '{{VALUE}}',
            ],
        ]);

        $this->end_controls_tab();

        $this->start_controls_tab('box_hover_tab',
        [
            'label' => esc_html__('Hover', 'listdom'),
        ]);

        $this->add_control('hover_bg_color',
        [
            'type'       => 'colorpicker',
            'label'      => esc_html__('Background Color', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsdtb-card-price:hover' => 'background-color:{{VALUE}};',
            ],
        ]);

        $this->add_control('hover_text_color',
        [
            'type'       => 'colorpicker',
            'label'      => esc_html__('Text Color', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsdtb-card-price:hover' => 'color:{{VALUE}};',
                '{{WRAPPER}} .lsdtb-card-price:hover a' => 'color:{{VALUE}};',
            ],
        ]);

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->end_controls_section();

        $this->start_controls_section('typography_' . $this->key,
        [
            'label'      => esc_html__('Typography', 'listdom'),
            'responsive' => true,
            'tab'        => self::TAB_STYLE,
        ]);

        $this->start_controls_tabs('typography_style_tabs');

        $this->start_controls_tab('style_price_tab',
            [
                'label' => esc_html__('Price', 'listdom'),
            ]);

        $this->add_control('price_typography',
            [
                'type'       => 'typography',
                'label'      => esc_html__('Price Typography', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsdtb-card-price' => '{{VALUE}}',
                    '{{WRAPPER}} .lsdtb-card-price a' => '{{VALUE}}',
                ],
            ]);

        $this->add_control('description_typography',
            [
                'type'       => 'typography',
                'label'      => esc_html__('Description Typography', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsd-text-price' => '{{VALUE}}',
                ],
                'condition'  => [
                    'minimized' => false,
                ],
            ]);

        $this->add_control('description_color',
            [
                'type'       => 'colorpicker',
                'label'      => esc_html__('Description Color', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsd-text-price' => 'color:{{VALUE}};',
                ],
                'condition'  => [
                    'minimized' => false,
                ],
            ]);

        $this->end_controls_tab();

        $this->start_controls_tab('style_sign_tab',
            [
                'label' => esc_html__('Sign', 'listdom'),
            ]);

        $this->add_control('currency_typography',
            [
                'type'       => 'typography',
                'label'      => esc_html__('Typography', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsd-min-price span, {{WRAPPER}} .lsd-max-price span' => '{{VALUE}}',
                ],
            ]);

        $this->add_control('currency_color',
            [
                'type'       => 'colorpicker',
                'label'      => esc_html__('Color', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsd-min-price span, {{WRAPPER}} .lsd-max-price span' => 'color:{{VALUE}};',
                ],
            ]);

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->end_controls_section();

        $this->end_controls_section();
    }

    public function render(array $args = []): string
    {
        $listing_id = isset($args['listing_id']) ? (int) $args['listing_id'] : 0;
        if (!$listing_id) return '';

        $settings = isset($args['settings']) && is_array($args['settings']) ? $args['settings'] : [];
        $content_settings = isset($settings['content']) && is_array($settings['content']) ? $settings['content'] : [];

        $minimized = !empty($content_settings['minimized']);

        $listing = new LSD_Entity_Listing($listing_id);
        $price = $listing->get_price($minimized);

        if (trim((string) $price) === '') return '';

        if (!empty($content_settings['enable_price_link']))
        {
            $price = $this->link_price($price, $content_settings);
        }

        $html = '<span class="lsd-template-element-price lsdtb-card-price">' . LSD_Kses::element($price) . '</span>';

        return apply_filters('lsd_template_element_price_output', $html, $this, $args);
    }

    private function link_price(string $price, array $content_settings): string
    {
        $url = isset($content_settings['price_link_url']) ? trim((string) $content_settings['price_link_url']) : '';

        if ($url === '') return $price;

        $attrs = [
            'href' => esc_url($url),
        ];

        if (!empty($content_settings['price_link_target']))
        {
            $attrs['target'] = '_blank';
        }

        $rel = [];

        if (!empty($content_settings['price_link_nofollow']))
        {
            $rel[] = 'nofollow';
        }

        if (!empty($content_settings['price_link_target']))
        {
            $rel[] = 'noopener';
            $rel[] = 'noreferrer';
        }

        if (!empty($rel))
        {
            $attrs['rel'] = implode(' ', array_unique($rel));
        }

        return '<a' . $this->html_attrs($attrs) . '>' . $price . '</a>';
    }

    private function html_attrs(array $attrs): string
    {
        $output = '';

        foreach ($attrs as $key => $value)
        {
            if ($value === null || $value === '') continue;

            $output .= ' ' . esc_attr($key) . '="' . esc_attr($value) . '"';
        }

        return $output;
    }
}
