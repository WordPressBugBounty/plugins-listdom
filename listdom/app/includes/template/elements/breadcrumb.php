<?php

class LSD_Template_Elements_Breadcrumb extends LSD_Template
{
    public function __construct($settings = [])
    {
        $this->key            = 'breadcrumb';
        $this->label          = $this->label();
        $this->category       = 'basic';
        $this->template_types = ['single_listing', 'listing_card', 'info_window'];
        $this->icon           = self::icons($this->key);

        parent::__construct($settings);
    }

    protected function label()
    {
        return esc_html__('Listing Breadcrumb', 'listdom');
    }

    public function controls(): void
    {
        $this->start_controls_section('content_' . $this->key,
        [
            'label' => esc_html__('Options', 'listdom'),
            'tab'   => self::TAB_CONTENT,
        ]);

        $this->add_control('icon',
        [
            'type'    => 'switcher',
            'label'   => esc_html__('Show Home Icon', 'listdom'),
            'default' => 1,
        ]);

        $this->add_control('taxonomy',
        [
            'type'    => 'select',
            'class'   => 'lsd-admin-input',
            'label'   => esc_html__('Second Layer', 'listdom'),
            'default' => LSD_Base::TAX_CATEGORY,
            'options' => [
                LSD_Base::TAX_CATEGORY => esc_html__('Category', 'listdom'),
                LSD_Base::TAX_TAG      => esc_html__('Tag', 'listdom'),
                LSD_Base::TAX_LOCATION => esc_html__('Location', 'listdom'),
                LSD_Base::TAX_LABEL    => esc_html__('Label', 'listdom'),
                LSD_Base::TAX_FEATURE  => esc_html__('Feature', 'listdom'),
            ],
        ]);

        $this->end_controls_section();

        $this->start_controls_section('general_' . $this->key,
        [
            'label'      => esc_html__('General', 'listdom'),
            'responsive' => true,
            'tab'        => self::TAB_STYLE,
        ]);

        $this->add_control('breadcrumb_item_gap',
        [
            'type'       => 'number',
            'class'      => 'lsd-admin-input',
            'label'      => esc_html__('Item Gap', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsdtb-breadcrumb .lsd-breadcrumb-item' => 'gap:{{VALUE}}px;',
                '{{WRAPPER}} .lsdtb-breadcrumb .lsd-breadcrumb-list' => 'gap:{{VALUE}}px;',
            ],
        ]);

        $this->end_controls_section();

        $this->start_controls_section('home_' . $this->key,
        [
            'label'      => esc_html__('Home', 'listdom'),
            'responsive' => true,
            'tab'        => self::TAB_STYLE,
        ]);

        $this->start_controls_tabs('style_tabs');

        $this->start_controls_tab('style_Text_tab',
        [
            'label' => esc_html__('Text', 'listdom'),
        ]);

        $this->add_control('home_text_color',
            [
                'type'       => 'colorpicker',
                'label'      => esc_html__('Text Color', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsdtb-breadcrumb .lsd-home-page a' => 'color:{{VALUE}};',
                ],
            ]);
        $this->add_control('home_icon_text_gap',
            [
                'type'       => 'number',
                'class'      => 'lsd-admin-input',
                'label'      => esc_html__('Icon & Text Gap', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsdtb-breadcrumb .lsd-home-page a' => 'gap:{{VALUE}}px;',
                ],
            ]);

        $this->add_control('home_typography',
            [
                'type'       => 'typography',
                'label'      => esc_html__('Typography', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsdtb-breadcrumb .lsd-home-page a' => '{{VALUE}}',
                ],
            ]);

        $this->end_controls_tab();

        $this->start_controls_tab('style_icon_tab',
        [
            'label' => esc_html__('Icon', 'listdom'),
            'condition' => [
                'icon' => true,
            ]
        ]);

        $this->add_control('home_icon_color',
            [
                'type'       => 'colorpicker',
                'label'      => esc_html__('Icon Color', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsdtb-breadcrumb .lsd-home-page i' => 'color:{{VALUE}};',
                ],
            ]);

        $this->add_control('home_icon_size',
            [
                'type'       => 'number',
                'class'      => 'lsd-admin-input',
                'label'      => esc_html__('Icon Size', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsdtb-breadcrumb .lsd-home-page i' => 'font-size:{{VALUE}}px;',
                ],
            ]);

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->end_controls_section();

        $this->start_controls_section('taxonomy_' . $this->key,
        [
            'label'      => esc_html__('Taxonomy', 'listdom'),
            'responsive' => true,
            'tab'        => self::TAB_STYLE,
        ]);

        $this->add_control('taxonomy_text_color',
        [
            'type'       => 'colorpicker',
            'label'      => esc_html__('Text Color', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsdtb-breadcrumb .lsd-taxonomy-page a' => 'color:{{VALUE}};',
            ],
        ]);

        $this->add_control('taxonomy_typography',
        [
            'type'       => 'typography',
            'label'      => esc_html__('Typography', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsdtb-breadcrumb .lsd-taxonomy-page a' => '{{VALUE}}',
            ],
        ]);

        $this->end_controls_section();

        $this->start_controls_section('current_' . $this->key,
        [
            'label'      => esc_html__('Current', 'listdom'),
            'responsive' => true,
            'tab'        => self::TAB_STYLE,
        ]);

        $this->add_control('current_text_color',
        [
            'type'       => 'colorpicker',
            'label'      => esc_html__('Text Color', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsdtb-breadcrumb .lsd-current-page span' => 'color:{{VALUE}};',
            ],
        ]);

        $this->add_control('current_typography',
        [
            'type'       => 'typography',
            'label'      => esc_html__('Typography', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsdtb-breadcrumb .lsd-current-page span' => '{{VALUE}}',
            ],
        ]);

        $this->end_controls_section();

        $this->start_controls_section('separator_' . $this->key,
        [
            'label'      => esc_html__('Separator', 'listdom'),
            'responsive' => true,
            'tab'        => self::TAB_STYLE,
        ]);

        $this->add_control('separator_color',
        [
            'type'       => 'colorpicker',
            'label'      => esc_html__('Color', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsdtb-breadcrumb .lsd-breadcrumb-item:after' => 'color:{{VALUE}};',
            ],
        ]);

        $this->add_control('separator_icon_size',
        [
            'type'       => 'number',
            'class'      => 'lsd-admin-input',
            'label'      => esc_html__('Icon Size', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsdtb-breadcrumb .lsd-breadcrumb-item:after' => 'font-size:{{VALUE}}px;',
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

        $icon = $content_settings['icon'] ?? 1;
        $icon = in_array($icon, [1, '1', true, 'true', 'yes', 'on'], true);
        $taxonomy = isset($content_settings['taxonomy']) ? (string) $content_settings['taxonomy'] : LSD_Base::TAX_CATEGORY;
        if ($taxonomy === 'listdom-tags') $taxonomy = LSD_Base::TAX_TAG;

        $listing = new LSD_Entity_Listing($listing_id);
        $output = $listing->get_breadcrumb($icon, $taxonomy);
        if (trim($output) === '') return '';

        return '<div class="lsd-template-element-breadcrumb lsdtb-breadcrumb">' . $output . '</div>';
    }
}
