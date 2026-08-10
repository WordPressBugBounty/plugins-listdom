<?php

class LSD_Template_Elements_Labels extends LSD_Template
{
    public function __construct($settings = [])
    {
        $this->key            = 'labels';
        $this->label          = $this->label();
        $this->category       = 'taxonomies';
        $this->template_types = ['single_listing', 'listing_card', 'info_window'];
        $this->icon           = self::icons($this->key);

        parent::__construct($settings);
    }

    protected function label()
    {
        return esc_html__('Listing Labels', 'listdom');
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
            'default' => 'row',
            'options' => [
                'row'    => esc_html__('Inline', 'listdom'),
                'column' => esc_html__('Block', 'listdom'),
            ],
            'selectors' => [
                '{{WRAPPER}} .lsdtb-card-labels .lsd-labels-list' => 'flex-direction:{{VALUE}};',
                '{{WRAPPER}} .lsdtb-card-labels .lsd-labels-simple' => 'flex-direction:{{VALUE}};',
            ],
        ]);

        $this->add_control('enable_link',
        [
            'type'    => 'switcher',
            'label'   => esc_html__('Link to Label Archive', 'listdom'),
            'default' => 1,
        ]);

        $this->end_controls_section();

        $this->start_controls_section('box_' . $this->key,
        [
            'label'      => esc_html__('Box', 'listdom'),
            'responsive' => true,
            'tab'        => self::TAB_STYLE,
        ]);

        $this->add_control('box_background_color',
        [
            'type'       => 'colorpicker',
            'label'      => esc_html__('Background Color', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsdtb-card-labels' => 'background-color:{{VALUE}};',
            ],
        ]);

        $this->add_control('box_border',
        [
            'type'       => 'border',
            'label'      => esc_html__('Border', 'listdom'),
            'responsive' => true,
            'radius_fallback' => 'box_border_radius',
            'selectors'  => [
                '{{WRAPPER}} .lsdtb-card-labels' => '{{VALUE}}',
            ],
        ]);

        $this->add_control('box_padding',
        [
            'type'       => 'padding',
            'label'      => esc_html__('Padding', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsdtb-card-labels' => '{{VALUE}}',
            ],
        ]);

        $this->add_control('gap',
        [
            'type'       => 'number',
            'class'      => 'lsd-admin-input',
            'label'      => esc_html__('Gap Between Items', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsdtb-card-labels .lsd-labels-list' => 'gap:{{VALUE}}px;',
            ],
        ]);

        $this->end_controls_section();

        $this->start_controls_section('items_' . $this->key,
            [
                'label'      => esc_html__('Items', 'listdom'),
                'responsive' => true,
                'tab'        => self::TAB_STYLE,
            ]);

        $this->start_controls_tabs('items_style_tabs');

        $this->start_controls_tab('items_normal_tab',
            [
                'label' => esc_html__('Normal', 'listdom'),
            ]);

        $this->add_control('labels_typography',
            [
                'type'       => 'typography',
                'label'      => esc_html__('Typography', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsdtb-card-labels li, {{WRAPPER}} .lsdtb-card-labels a, {{WRAPPER}} .lsdtb-card-labels span' => '{{VALUE}}',
                ],
            ]);

        $this->add_control('item_color',
            [
                'type'       => 'colorpicker',
                'label'      => esc_html__('Color', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsdtb-card-labels a, {{WRAPPER}} .lsdtb-card-labels span' => 'color:{{VALUE}};',
                ],
            ]);

        $this->add_control('item_background_color',
            [
                'type'       => 'colorpicker',
                'label'      => esc_html__('Background Color', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsdtb-card-labels a, {{WRAPPER}} .lsdtb-card-labels span' => 'background-color:{{VALUE}};',
                ],
            ]);

        $this->add_control('item_border',
            [
                'type'            => 'border',
                'label'           => esc_html__('Border', 'listdom'),
                'responsive'      => true,
                'radius_fallback' => 'item_border_radius',
                'selectors'       => [
                    '{{WRAPPER}} .lsdtb-card-labels .lsd-labels-list-item a, {{WRAPPER}} .lsdtb-card-labels .lsd-labels-list-item span' => '{{VALUE}}',
                ],
            ]);

        $this->add_control('items_padding',
            [
                'type'       => 'padding',
                'label'      => esc_html__('Padding', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsdtb-card-labels a, {{WRAPPER}} .lsdtb-card-labels span' => '{{VALUE}}',
                ],
            ]);

        $this->end_controls_tab();

        $this->start_controls_tab('items_hover_tab',
            [
                'label' => esc_html__('Hover', 'listdom'),
            ]);

        $this->add_control('item_hover_color',
            [
                'type'       => 'colorpicker',
                'label'      => esc_html__('Color', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsdtb-card-labels li:hover a, {{WRAPPER}} .lsdtb-card-labels li:hover span' => 'color:{{VALUE}};',
                ],
            ]);

        $this->add_control('item_hover_background_color',
            [
                'type'       => 'colorpicker',
                'label'      => esc_html__('Background Color', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsdtb-card-labels li:hover a, {{WRAPPER}} .lsdtb-card-labels li:hover span' => 'background-color:{{VALUE}};',
                ],
            ]);

        $this->add_control('item_hover_border',
            [
                'type'            => 'border',
                'label'           => esc_html__('Border', 'listdom'),
                'responsive'      => true,
                'radius_fallback' => 'item_hover_border_radius',
                'selectors'       => [
                    '{{WRAPPER}} .lsdtb-card-labels li:hover a, {{WRAPPER}} .lsdtb-card-labels li:hover span' => '{{VALUE}}',
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
        $enable_link = !empty($content_settings['enable_link']);

        $listing = new LSD_Entity_Listing($listing_id);
        $output = $listing->get_labels('tags', $enable_link);
        if (trim($output) === '') return '';

        return '<div class="lsd-template-element-labels lsdtb-card-labels">' . $output . '</div>';
    }
}
