<?php

class LSD_Template_Elements_Stats extends LSD_Template
{
    public function __construct($settings = [])
    {
        $this->key            = 'stats';
        $this->label          = $this->label();
        $this->category       = 'advanced';
        $this->template_types = ['single_listing', 'listing_card', 'info_window'];
        $this->icon           = self::icons($this->key);

        parent::__construct($settings);
    }

    protected function label()
    {
        return esc_html__('Stats Addon', 'listdom');
    }

    public function controls(): void
    {
        $this->start_controls_section('content_' . $this->key,
        [
            'label' => esc_html__('Content', 'listdom'),
            'tab'   => self::TAB_CONTENT,
        ]);

        $this->add_control('display_zero',
        [
            'type'    => 'switcher',
            'label'   => esc_html__('Display Zero Values', 'listdom'),
            'default' => 1,
        ]);

        $this->add_control('display_visits',
        [
            'type'    => 'switcher',
            'label'   => esc_html__('Display Visits', 'listdom'),
            'default' => 1,
        ]);

        $this->add_control('display_contacts',
        [
            'type'    => 'switcher',
            'label'   => esc_html__('Display Contacts', 'listdom'),
            'default' => 1,
        ]);

        $this->add_control('display_offers',
        [
            'type'    => 'switcher',
            'label'   => esc_html__('Display Offers', 'listdom'),
            'default' => 1,
        ]);

        $this->add_control('display_bookings',
        [
            'type'    => 'switcher',
            'label'   => esc_html__('Display Bookings', 'listdom'),
            'default' => 1,
        ]);

        $this->add_control('display_reviews',
        [
            'type'    => 'switcher',
            'label'   => esc_html__('Display Reviews', 'listdom'),
            'default' => 1,
        ]);

        $this->add_control('display_comments',
        [
            'type'    => 'switcher',
            'label'   => esc_html__('Display Comments', 'listdom'),
            'default' => 1,
        ]);

        $this->end_controls_section();

        $this->start_controls_section('layout_' . $this->key,
        [
            'label'      => esc_html__('Layout', 'listdom'),
            'responsive' => true,
            'tab'        => self::TAB_STYLE,
        ]);

        $this->add_control('layout',
        [
            'type'    => 'select',
            'class'   => 'lsd-admin-input',
            'label'   => esc_html__('Layout', 'listdom'),
            'default' => 'column',
            'options' => [
                'row' => esc_html__('Inline', 'listdom'),
                'column' => esc_html__('Block', 'listdom'),
            ],
            'selectors' => [
                '{{WRAPPER}} .lsd-template-element-stats ul' => 'flex-direction:{{VALUE}};',
                '{{WRAPPER}} .lsdtb-card-stats ul' => 'flex-direction:{{VALUE}};',
            ],
        ]);

        $this->add_control('stats_gap',
        [
            'type'       => 'number',
            'class'      => 'lsd-admin-input',
            'label'      => esc_html__('List Gap', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsd-template-element-stats ul' => 'gap:{{VALUE}}px;',
                '{{WRAPPER}} .lsdtb-card-stats ul' => 'gap:{{VALUE}}px;',
            ],
        ]);

        $this->add_control('stats_items_gap',
        [
            'type'       => 'number',
            'class'      => 'lsd-admin-input',
            'label'      => esc_html__('Item Gap', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsd-template-element-stats li' => 'gap:{{VALUE}}px;',
                '{{WRAPPER}} .lsdtb-card-stats li' => 'gap:{{VALUE}}px;',
            ],
        ]);

        $this->end_controls_section();

        $this->start_controls_section('typography_' . $this->key,
        [
            'label'      => esc_html__('Typography', 'listdom'),
            'responsive' => true,
            'tab'        => self::TAB_STYLE,
        ]);

        $this->start_controls_tabs('style_tabs');

        $this->start_controls_tab('style_label_tab',
        [
            'label' => esc_html__('Label', 'listdom'),
        ]);

        $this->add_control('stats_typography',
            [
                'type'        => 'typography',
                'label'       => esc_html__('Typography', 'listdom'),
                'responsive'  => true,
                'selectors'   => [
                    '{{WRAPPER}} .lsd-template-element-stats span.lsdtbsts-label' => '{{VALUE}}',
                    '{{WRAPPER}} .lsdtb-card-stats span.lsdtbsts-label' => '{{VALUE}}',
                ],
            ]);

        $this->add_control('stats_text_color',
            [
                'type'       => 'colorpicker',
                'label'      => esc_html__('Text Color', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsd-template-element-stats .lsdtbsts-label' => 'color:{{VALUE}};',
                    '{{WRAPPER}} .lsdtb-card-stats .lsdtbsts-label' => 'color:{{VALUE}};',
                ],
            ]);

        $this->end_controls_tab();

        $this->start_controls_tab('style_value_tab',
        [
            'label' => esc_html__('Value', 'listdom'),
        ]);

        $this->add_control('stats_value_typography',
            [
                'type'        => 'typography',
                'label'       => esc_html__('Typography', 'listdom'),
                'responsive'  => true,
                'selectors'   => [
                    '{{WRAPPER}} .lsd-template-element-stats .lsdtbsts-value' => '{{VALUE}}',
                    '{{WRAPPER}} .lsdtb-card-stats .lsdtbsts-value' => '{{VALUE}}',
                ],
            ]);

        $this->add_control('stats_value_text_color',
            [
                'type'       => 'colorpicker',
                'label'      => esc_html__('Text Color', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsd-template-element-stats .lsdtbsts-value' => 'color:{{VALUE}};',
                    '{{WRAPPER}} .lsdtb-card-stats .lsdtbsts-value' => 'color:{{VALUE}};',
                ],
            ]);

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->end_controls_section();

        $this->start_controls_section('icons_' . $this->key,
        [
            'label'      => esc_html__('Icons', 'listdom'),
            'responsive' => true,
            'tab'        => self::TAB_STYLE,
        ]);

        $this->add_control('stats_icons_bg_color',
        [
            'type'       => 'colorpicker',
            'label'      => esc_html__('Background Color', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsd-template-element-stats i' => 'background-color:{{VALUE}};',
                '{{WRAPPER}} .lsdtb-card-stats i' => 'background-color:{{VALUE}};',
            ],
        ]);

        $this->add_control('stats_icon_color',
        [
            'type'       => 'colorpicker',
            'label'      => esc_html__('Color', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsd-template-element-stats i' => 'color:{{VALUE}};',
                '{{WRAPPER}} .lsdtb-card-stats i' => 'color:{{VALUE}};',
            ],
        ]);

        $this->add_control('stats_icon_size',
        [
            'type'       => 'number',
            'class'      => 'lsd-admin-input',
            'label'      => esc_html__('Size', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsd-template-element-stats i' => 'font-size:{{VALUE}}px;',
                '{{WRAPPER}} .lsdtb-card-stats i' => 'font-size:{{VALUE}}px;',
            ],
        ]);

        $this->add_control('stats_icon_border_radius',
        [
            'type'       => 'border',
            'label'      => esc_html__('Border', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsd-template-element-stats i' => '{{VALUE}}',
                '{{WRAPPER}} .lsdtb-card-stats i' => '{{VALUE}}',
            ],
        ]);

        $this->add_control('stats_icon_padding',
        [
            'type'       => 'padding',
            'label'      => esc_html__('Padding', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsd-template-element-stats i' => '{{VALUE}}',
                '{{WRAPPER}} .lsdtb-card-stats i' => '{{VALUE}}',
            ],
        ]);

        $this->end_controls_section();
    }

    public function render(array $args = []): string
    {
        $listing_id = isset($args['listing_id']) ? (int) $args['listing_id'] : 0;
        if (!$listing_id) return '';

        if (!class_exists(\LSDPACSTS\Base::class))
        {
            return LSD_Main::alert(esc_html__('Stats addon should be installed and activated!', 'listdom'), 'warning');
        }

        $settings = isset($args['settings']) && is_array($args['settings']) ? $args['settings'] : [];
        $content_settings = isset($settings['content']) && is_array($settings['content']) ? $settings['content'] : [];

        $element_args = [
            'display_zero' => $content_settings['display_zero'] ?? 1,
            'display_visits' => $content_settings['display_visits'] ?? 1,
            'display_contacts' => $content_settings['display_contacts'] ?? 1,
            'display_offers' => $content_settings['display_offers'] ?? 1,
            'display_bookings' => $content_settings['display_bookings'] ?? 1,
            'display_reviews' => $content_settings['display_reviews'] ?? 1,
            'display_comments' => $content_settings['display_comments'] ?? 1,
        ];

        $element = new \LSDPACSTS\Element();
        $output = $element->get($listing_id, $element_args);
        if (trim((string) $output) === '') return '';

        $html = '<div class="lsd-template-element-stats lsdtb-card-stats">' . $output . '</div>';

        return apply_filters('lsd_template_element_stats_output', $html, $this, $args);
    }
}
