<?php

class LSD_Template_Elements_Features extends LSD_Template
{
    public function __construct($settings = [])
    {
        $this->key            = 'features';
        $this->label          = $this->label();
        $this->category       = 'taxonomies';
        $this->template_types = ['single_listing', 'listing_card', 'info_window'];
        $this->icon           = self::icons($this->key);

        parent::__construct($settings);
    }

    protected function label()
    {
        return esc_html__('Listing Features', 'listdom');
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
            'class' => 'lsd-admin-input',
            'label'   => esc_html__('Layout', 'listdom'),
            'default' => 'column',
            'options' => [
                'row'    => esc_html__('Inline', 'listdom'),
                'column' => esc_html__('Block', 'listdom'),
            ],
            'selectors' => [
                '{{WRAPPER}} .lsdtb-card-features ul' => 'flex-direction:{{VALUE}};',
            ],
        ]);

        $this->add_control('list_style',
        [
            'type'    => 'select',
            'class' => 'lsd-admin-input',
            'label'   => esc_html__('List Style', 'listdom'),
            'default' => 'per-row',
            'options' => [
                'per-row' => esc_html__('One Per Row', 'listdom'),
                'inline'  => esc_html__('Inline', 'listdom'),
            ],
        ]);

        $this->add_control('enable_features_link',
        [
            'type'    => 'switcher',
            'label'   => esc_html__('Link to Features Archive', 'listdom'),
            'default' => 1,
        ]);

        $this->add_control('show_icon',
        [
            'type'    => 'switcher',
            'label'   => esc_html__('Show Icon', 'listdom'),
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
                '{{WRAPPER}} .lsdtb-card-features' => 'background-color:{{VALUE}};',
            ],
        ]);

        $this->add_control('box_border',
        [
            'type'       => 'border',
            'label'      => esc_html__('Border', 'listdom'),
            'responsive' => true,
            'radius_fallback' => 'box_border_radius',
            'selectors'  => [
                '{{WRAPPER}} .lsdtb-card-features' => '{{VALUE}}',
            ],
        ]);

        $this->add_control('box_padding',
        [
            'type'       => 'padding',
            'label'      => esc_html__('Padding', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsdtb-card-features' => '{{VALUE}}',
            ],
        ]);

        $this->add_control('gap',
        [
            'type'       => 'number',
            'class'      => 'lsd-admin-input',
            'label'      => esc_html__('Gap Between Items', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsdtb-card-features ul.lsd-features-style-per-row' => 'gap:{{VALUE}}px;',
            ],
        ]);

        $this->end_controls_section();

        $this->start_controls_section('items_' . $this->key,
        [
            'label'      => esc_html__('Items', 'listdom'),
            'responsive' => true,
            'tab'        => self::TAB_STYLE,
        ]);

        $this->add_control('features_typography',
        [
            'type'       => 'typography',
            'label'      => esc_html__('Typography', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsdtb-card-features li, {{WRAPPER}} .lsdtb-card-features a' => '{{VALUE}}',
            ],
        ]);

        $this->add_control('item_color',
        [
            'type'       => 'colorpicker',
            'label'      => esc_html__('Color', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsdtb-card-features li' => 'color:{{VALUE}};',
                '{{WRAPPER}} .lsdtb-card-features a' => 'color:{{VALUE}};',
            ],
        ]);

        $this->add_control('item_background_color',
        [
            'type'       => 'colorpicker',
            'label'      => esc_html__('Background Color', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsdtb-card-features li' => 'background-color:{{VALUE}};',
            ],
        ]);

        $this->add_control('item_hover_background_color',
        [
            'type'       => 'colorpicker',
            'label'      => esc_html__('Hover Background Color', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsdtb-card-features li:hover' => 'background-color:{{VALUE}};',
            ],
        ]);

        $this->add_control('item_hover_color',
        [
            'type'       => 'colorpicker',
            'label'      => esc_html__('Hover Color', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsdtb-card-features li:hover' => 'color:{{VALUE}};',
                '{{WRAPPER}} .lsdtb-card-features li:hover a' => 'color:{{VALUE}};',
            ],
        ]);

        $this->add_control('item_border',
        [
            'type'       => 'border',
            'label'      => esc_html__('Border', 'listdom'),
            'responsive' => true,
            'radius_fallback' => 'item_border_radius',
            'selectors'  => [
                '{{WRAPPER}} .lsdtb-card-features ul li' => '{{VALUE}}',
            ],
        ]);

        $this->add_control('items_padding',
        [
            'type'       => 'padding',
            'label'      => esc_html__('Padding', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsdtb-card-features li' => '{{VALUE}}',
            ],
        ]);

        $this->add_control('item_icon_color',
        [
            'type'       => 'colorpicker',
            'label'      => esc_html__('Icon Color', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsdtb-card-features li i' => 'color:{{VALUE}};',
            ],
        ]);

        $this->add_control('item_icon_hover_color',
        [
            'type'       => 'colorpicker',
            'label'      => esc_html__('Icon Hover Color', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsdtb-card-features li:hover i' => 'color:{{VALUE}};',
            ],
        ]);

        $this->add_control('items_icon_gap',
        [
            'type'       => 'number',
            'class'      => 'lsd-admin-input',
            'label'      => esc_html__('Icon Item Gap', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsdtb-card-features li i' => 'margin-right:{{VALUE}}px;',
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

        $enable_link = !empty($content_settings['enable_features_link']);
        $show_icon = !empty($content_settings['show_icon']);
        $list_style = isset($content_settings['list_style']) ? (string) $content_settings['list_style'] : 'per-row';
        if (!in_array($list_style, ['per-row', 'inline'], true)) $list_style = 'per-row';

        $listing = new LSD_Entity_Listing($listing_id);
        $output = $listing->get_features('list', $show_icon, $enable_link, $list_style);
        if (trim($output) === '') return '';

        return '<div class="lsd-template-element-features lsdtb-card-features">' . $output . '</div>';
    }
}
