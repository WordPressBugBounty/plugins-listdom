<?php

class LSD_Template_Elements_Image extends LSD_Template
{
    public function __construct($settings = [])
    {
        $this->key            = 'image';
        $this->label          = $this->label();
        $this->category       = 'media';
        $this->template_types = ['single_listing', 'listing_card', 'info_window'];
        $this->icon           = self::icons($this->key);

        parent::__construct($settings);
    }

    protected function label()
    {
        return esc_html__('Listing Image', 'listdom');
    }

    public function controls(): void
    {
        $this->start_controls_section('image_content_' . $this->key,
            [
                'label' => esc_html__('Image', 'listdom'),
                'tab'   => self::TAB_CONTENT,
            ]);

        $this->add_control('link_method',
            [
                'type'    => 'select',
                'label'   => esc_html__('Link Method', 'listdom'),
                'class'   => 'lsd-admin-input',
                'default' => 'normal',
                'options' => LSD_Base::get_listing_link_methods(),
            ]);

        $this->end_controls_section();

        $this->start_controls_section('content_' . $this->key,
            [
                'label' => esc_html__('Overlay Content', 'listdom'),
                'tab'   => self::TAB_CONTENT,
            ]);

        $modules = $this->modules();

        $this->add_control('top_left',
            [
                'type'     => 'select',
                'label'    => esc_html__('Top Left', 'listdom'),
                'class'    => 'lsd-admin-input',
                'multiple' => true,
                'options'  => $modules,
            ]);

        $this->add_control('top_right',
            [
                'type'     => 'select',
                'label'    => esc_html__('Top Right', 'listdom'),
                'class'    => 'lsd-admin-input',
                'multiple' => true,
                'options'  => $modules,
            ]);

        $this->add_control('bottom_left',
            [
                'type'     => 'select',
                'label'    => esc_html__('Bottom Left', 'listdom'),
                'class'    => 'lsd-admin-input',
                'multiple' => true,
                'options'  => $modules,
            ]);

        $this->add_control('bottom_right',
            [
                'type'     => 'select',
                'label'    => esc_html__('Bottom Right', 'listdom'),
                'class'    => 'lsd-admin-input',
                'multiple' => true,
                'options'  => $modules,
            ]);

        $this->end_controls_section();

        $this->start_controls_section('categories_content_' . $this->key,
            [
                'label'     => esc_html__('Categories', 'listdom'),
                'tab'       => self::TAB_CONTENT,
                'condition' => $this->module_condition('categories'),
            ]);

        $this->add_control('categories_default_colors',
            [
                'type'    => 'switcher',
                'label'   => esc_html__('Default Colors', 'listdom'),
                'default' => 1,
            ]);

        $this->end_controls_section();

        $this->start_controls_section('labels_content_' . $this->key,
            [
                'label'     => esc_html__('Labels', 'listdom'),
                'tab'       => self::TAB_CONTENT,
                'condition' => $this->module_condition('labels'),
            ]);

        $this->add_control('labels_default_colors',
            [
                'type'    => 'switcher',
                'label'   => esc_html__('Default Colors', 'listdom'),
                'default' => 1,
            ]);

        $this->end_controls_section();

        $this->start_controls_section('image_' . $this->key,
            [
                'label'      => esc_html__('Image', 'listdom'),
                'responsive' => true,
                'tab'        => self::TAB_STYLE,
            ]);

        $this->add_control('image_resolution',
            [
                'type'    => 'select',
                'label'   => esc_html__('Image Resolution', 'listdom'),
                'class'   => 'lsd-admin-input',
                'default' => 'full',
                'options' => $this->get_image_sizes(),
            ]);

        $this->add_control('image_width',
            [
                'type'       => 'number',
                'class'      => 'lsd-admin-input',
                'label'      => esc_html__('Width', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsdtb-card-image > a img' => 'width:{{VALUE}}px;',
                ],
            ]);

        $this->add_control('image_height',
            [
                'type'       => 'number',
                'class'      => 'lsd-admin-input',
                'label'      => esc_html__('Height', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsdtb-card-image > a img' => 'height:{{VALUE}}px;',
                ],
            ]);

        $this->add_control('image_object_fit',
            [
                'type'    => 'select',
                'label'   => esc_html__('Object Fit', 'listdom'),
                'class'   => 'lsd-admin-input',
                'default' => '',
                'options' => [
                    ''        => esc_html__('Default', 'listdom'),
                    'cover'   => esc_html__('Cover', 'listdom'),
                    'contain' => esc_html__('Contain', 'listdom'),
                    'fill'    => esc_html__('Fill', 'listdom'),
                ],
                'selectors' => [
                    '{{WRAPPER}} .lsdtb-card-image > a img' => 'object-fit:{{VALUE}};',
                ],
            ]);

        $this->add_control('image_border',
            [
                'type'            => 'border',
                'label'           => esc_html__('Border', 'listdom'),
                'responsive'      => true,
                'radius_fallback' => 'border_radius',
                'selectors'       => [
                    '{{WRAPPER}} .lsdtb-card-image > a img' => '{{VALUE}}',
                ],
            ]);

        $this->add_control('image_alignment',
        [
            'type'    => 'select',
            'label'   => esc_html__('Alignment', 'listdom'),
            'class'   => 'lsd-admin-input',
            'default' => 'flex-start',
            'options' => [
                'flex-start' => esc_html__('Left', 'listdom'),
                'center'     => esc_html__('Center', 'listdom'),
                'flex-end'   => esc_html__('Right', 'listdom'),
            ],
            'selectors' => [
                '{{WRAPPER}} .lsdtb-card-image > a' => 'display:flex;justify-content:{{VALUE}};',
            ],
        ]);

        $this->end_controls_section();

        $this->start_controls_section('overlay_' . $this->key,
        [
            'label'      => esc_html__('Overlay', 'listdom'),
            'responsive' => true,
            'tab'        => self::TAB_STYLE,
            'condition'  => $this->any_module_condition(),
        ]);

        $this->add_control('overlay_padding',
        [
            'type'       => 'padding',
            'label'      => esc_html__('Padding', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsdtb-card-image-overlay' => '{{VALUE}}',
            ],
        ]);

        $this->add_control('overlay_module_gap',
        [
            'type'       => 'number',
            'class'      => 'lsd-admin-input',
            'label'      => esc_html__('Module Gap', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsdtb-card-image-overlay-top-left, {{WRAPPER}} .lsdtb-card-image-overlay-top-right, {{WRAPPER}} .lsdtb-card-image-overlay-bottom-left, {{WRAPPER}} .lsdtb-card-image-overlay-bottom-right' => 'gap:{{VALUE}}px;',
            ],
        ]);

        $this->end_controls_section();

        $this->start_controls_section('owner_image_' . $this->key,
            [
                'label'      => esc_html__('Owner', 'listdom'),
                'responsive' => true,
                'tab'        => self::TAB_STYLE,
                'condition'  => $this->module_condition('owner'),
            ]);

        $this->start_controls_tabs('owner_style_tabs');

        $this->start_controls_tab('owner_image_tab',
        [
            'label' => esc_html__('Image', 'listdom'),
        ]);

        $this->add_control('owner_image_width',
            [
                'type'       => 'number',
                'class'      => 'lsd-admin-input',
                'label'      => esc_html__('Width', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsd-owner-image img' => 'width:{{VALUE}}px;',
                ],
            ]);

        $this->add_control('owner_image_height',
        [
            'type'       => 'number',
            'class'      => 'lsd-admin-input',
            'label'      => esc_html__('Height', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsd-owner-image img' => 'height:{{VALUE}}px;',
            ],
        ]);

        $this->add_control('owner_border',
        [
            'type'            => 'border',
            'label'           => esc_html__('Border', 'listdom'),
            'responsive'      => true,
            'radius_fallback' => 'owner_border_radius',
            'selectors'       => [
                '{{WRAPPER}} .lsd-owner-image img' => '{{VALUE}}',
            ],
        ]);

        $this->end_controls_tab();

        $this->start_controls_tab('owner_text_tab',
        [
            'label' => esc_html__('Text', 'listdom'),
        ]);

        $this->add_control('owner_typography',
        [
            'type'       => 'typography',
            'label'      => esc_html__('Typography', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsdtb-card-image-module-owner, {{WRAPPER}} .lsdtb-card-image-module-owner a, {{WRAPPER}} .lsdtb-card-image-module-owner span' => '{{VALUE}}',
            ],
        ]);

        $this->add_control('owner_color',
        [
            'type'       => 'colorpicker',
            'label'      => esc_html__('Color', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsdtb-card-image-module-owner, {{WRAPPER}} .lsdtb-card-image-module-owner a, {{WRAPPER}} .lsdtb-card-image-module-owner span' => 'color:{{VALUE}};',
            ],
        ]);

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->end_controls_section();

        $this->start_controls_section('labels_' . $this->key,
            [
                'label'      => esc_html__('Labels', 'listdom'),
                'responsive' => true,
                'tab'        => self::TAB_STYLE,
                'condition'  => $this->module_condition('labels'),
            ]);

        $this->start_controls_tabs('labels_style_tabs');

        $this->start_controls_tab('labels_normal_tab',
            [
                'label' => esc_html__('Normal', 'listdom'),
            ]);

        $this->add_control('labels_typography',
            [
                'type'       => 'typography',
                'label'      => esc_html__('Typography', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsdtb-labels-module a, {{WRAPPER}} .lsdtb-labels-module span' => '{{VALUE}}',
                ],
            ]);

        $this->add_control('labels_color',
            [
                'type'       => 'colorpicker',
                'label'      => esc_html__('Color', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsdtb-labels-module a, {{WRAPPER}} .lsdtb-labels-module span' => 'color:{{VALUE}};',
                ],
            ]);

        $this->add_control('labels_bg',
            [
                'type'       => 'colorpicker',
                'label'      => esc_html__('Background', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsdtb-labels-module a, {{WRAPPER}} .lsdtb-labels-module span' => 'background-color:{{VALUE}};',
                ],
            ]);

        $this->add_control('labels_padding',
            [
                'type'       => 'padding',
                'label'      => esc_html__('Padding', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsdtb-labels-module a, {{WRAPPER}} .lsdtb-labels-module span' => '{{VALUE}}',
                ],
            ]);

        $this->add_control('labels_border',
            [
                'type'            => 'border',
                'label'           => esc_html__('Border', 'listdom'),
                'responsive'      => true,
                'radius_fallback' => 'labels_border_radius',
                'selectors'       => [
                    '{{WRAPPER}} .lsdtb-labels-module a, {{WRAPPER}} .lsdtb-labels-module span' => '{{VALUE}}',
                ],
            ]);

        $this->end_controls_tab();

        $this->start_controls_tab('labels_hover_tab',
            [
                'label' => esc_html__('Hover', 'listdom'),
            ]);

        $this->add_control('labels_hover_color',
            [
                'type'       => 'colorpicker',
                'label'      => esc_html__('Color', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsdtb-labels-module li:hover a, {{WRAPPER}} .lsdtb-labels-module li:hover span' => 'color:{{VALUE}};',
                ],
            ]);

        $this->add_control('labels_hover_bg',
            [
                'type'       => 'colorpicker',
                'label'      => esc_html__('Background', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsdtb-labels-module li:hover a, {{WRAPPER}} .lsdtb-labels-module li:hover span' => 'background-color:{{VALUE}};',
                ],
            ]);

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->end_controls_section();

        $this->start_controls_section('categories_' . $this->key,
            [
                'label'      => esc_html__('Categories', 'listdom'),
                'responsive' => true,
                'tab'        => self::TAB_STYLE,
                'condition'  => $this->module_condition('categories'),
            ]);

        $this->start_controls_tabs('categories_style_tabs');

        $this->start_controls_tab('categories_normal_tab',
            [
                'label' => esc_html__('Normal', 'listdom'),
            ]);

        $this->add_control('categories_display_name',
            [
                'type'    => 'switcher',
                'label'   => esc_html__('Category Name', 'listdom'),
                'default' => 1,
            ]);

        $this->add_control('categories_display_icon',
            [
                'type'    => 'switcher',
                'label'   => esc_html__('Category Icon', 'listdom'),
                'default' => 0,
            ]);

        $this->add_control('categories_typography',
            [
                'type'       => 'typography',
                'label'      => esc_html__('Typography', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsdtb-categories-module a, {{WRAPPER}} .lsdtb-categories-module a i' => '{{VALUE}}',
                ],
            ]);

        $this->add_control('category_color',
            [
                'type'       => 'colorpicker',
                'label'      => esc_html__('Color', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsdtb-categories-module a, {{WRAPPER}} .lsdtb-categories-module a i' => 'color:{{VALUE}};',
                ],
            ]);

        $this->add_control('category_bg',
            [
                'type'       => 'colorpicker',
                'label'      => esc_html__('Background', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsdtb-categories-module a' => 'background-color:{{VALUE}};',
                ],
            ]);

        $this->add_control('categories_padding',
            [
                'type'       => 'padding',
                'label'      => esc_html__('Padding', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsdtb-categories-module a' => '{{VALUE}}',
                ],
            ]);

        $this->add_control('categories_border',
            [
                'type'            => 'border',
                'label'           => esc_html__('Border', 'listdom'),
                'responsive'      => true,
                'radius_fallback' => 'categories_border_radius',
                'selectors'       => [
                    '{{WRAPPER}} .lsdtb-categories-module a' => '{{VALUE}}',
                ],
            ]);

        $this->add_control('categories_gap',
            [
                'type'       => 'number',
                'class'      => 'lsd-admin-input',
                'label'      => esc_html__('Gap', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsdtb-categories-module' => 'gap:{{VALUE}}px;',
                ],
            ]);

        $this->end_controls_tab();

        $this->start_controls_tab('categories_hover_tab',
            [
                'label' => esc_html__('Hover', 'listdom'),
            ]);

        $this->add_control('category_hover_color',
        [
            'type'       => 'colorpicker',
            'label'      => esc_html__('Color', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsdtb-categories-module a:hover, {{WRAPPER}} .lsdtb-categories-module a:hover i' => 'color:{{VALUE}};',
            ],
        ]);

        $this->add_control('category_hover_bg',
        [
            'type'       => 'colorpicker',
            'label'      => esc_html__('Background', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsdtb-categories-module a:hover' => 'background-color:{{VALUE}};',
            ],
        ]);

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->end_controls_section();

        $this->start_controls_section('price_' . $this->key,
        [
            'label'      => esc_html__('Price', 'listdom'),
            'responsive' => true,
            'tab'        => self::TAB_STYLE,
            'condition'  => $this->module_condition('price'),
        ]);

        $this->start_controls_tabs('price_style_tabs');

        $this->start_controls_tab('price_text_tab',
        [
            'label' => esc_html__('Text', 'listdom'),
        ]);

        $this->add_control('price_typography',
        [
            'type'       => 'typography',
            'label'      => esc_html__('Typography', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsdtb-price-module' => '{{VALUE}}',
            ],
        ]);

        $this->add_control('price_color',
        [
            'type'       => 'colorpicker',
            'label'      => esc_html__('Text Color', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsdtb-price-module' => 'color:{{VALUE}};',
            ],
        ]);

        $this->add_control('price_bg',
        [
            'type'       => 'colorpicker',
            'label'      => esc_html__('Background Color', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsdtb-price-module' => 'background-color:{{VALUE}};',
            ],
        ]);

        $this->add_control('price_padding',
        [
            'type'       => 'padding',
            'label'      => esc_html__('Padding', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsdtb-price-module' => '{{VALUE}}',
            ],
        ]);

        $this->add_control('price_border',
        [
            'type'            => 'border',
            'label'           => esc_html__('Border', 'listdom'),
            'responsive'      => true,
            'radius_fallback' => 'price_border_radius',
            'selectors'       => [
                '{{WRAPPER}} .lsdtb-price-module' => '{{VALUE}}',
            ],
        ]);

        $this->end_controls_tab();

        $this->start_controls_tab('price_sign_tab',
        [
            'label' => esc_html__('Sign', 'listdom'),
        ]);

        $this->add_control('currency_typography',
        [
            'type'       => 'typography',
            'label'      => esc_html__('Sign Typography', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsd-min-price span, {{WRAPPER}} .lsd-max-price span' => '{{VALUE}}',
            ],
        ]);

        $this->add_control('currency_color',
        [
            'type'       => 'colorpicker',
            'label'      => esc_html__('Sign Color', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsd-min-price span, {{WRAPPER}} .lsd-max-price span' => 'color:{{VALUE}};',
            ],
        ]);

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->end_controls_section();

        $this->start_controls_section('locations_' . $this->key,
        [
            'label'      => esc_html__('Locations', 'listdom'),
            'responsive' => true,
            'tab'        => self::TAB_STYLE,
            'condition'  => $this->module_condition('locations'),
        ]);

        $this->start_controls_tabs('locations_style_tabs');

        $this->start_controls_tab('locations_normal_tab',
        [
            'label' => esc_html__('Normal', 'listdom'),
        ]);

        $this->add_control('locations_typography',
        [
            'type'       => 'typography',
            'label'      => esc_html__('Typography', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsd-locations-list-item a, {{WRAPPER}} .lsd-locations-list-item span' => '{{VALUE}}',
            ],
        ]);

        $this->add_control('locations_color',
        [
            'type'       => 'colorpicker',
            'label'      => esc_html__('Color', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsd-locations-list-item a, {{WRAPPER}} .lsd-locations-list-item span' => 'color:{{VALUE}};',
            ],
        ]);

        $this->add_control('locations_bg',
        [
            'type'       => 'colorpicker',
            'label'      => esc_html__('Background', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsd-locations-list-item a, {{WRAPPER}} .lsd-locations-list-item span' => 'background-color:{{VALUE}};',
            ],
        ]);

        $this->add_control('locations_padding',
        [
            'type'       => 'padding',
            'label'      => esc_html__('Padding', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsd-locations-list-item a, {{WRAPPER}} .lsd-locations-list-item span' => '{{VALUE}}',
            ],
        ]);

        $this->add_control('locations_border',
        [
            'type'            => 'border',
            'label'           => esc_html__('Border', 'listdom'),
            'responsive'      => true,
            'radius_fallback' => 'locations_border_radius',
            'selectors'       => [
                '{{WRAPPER}} .lsd-locations-list-item a, {{WRAPPER}} .lsd-locations-list-item span' => '{{VALUE}}',
            ],
        ]);

        $this->end_controls_tab();

        $this->start_controls_tab('locations_hover_tab',
        [
            'label' => esc_html__('Hover', 'listdom'),
        ]);

        $this->add_control('locations_hover_color',
        [
            'type'       => 'colorpicker',
            'label'      => esc_html__('Color', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsd-locations-list-item:hover a, {{WRAPPER}} .lsd-locations-list-item:hover span' => 'color:{{VALUE}};',
            ],
        ]);

        $this->add_control('locations_hover_bg',
        [
            'type'       => 'colorpicker',
            'label'      => esc_html__('Background', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsd-locations-list-item:hover a, {{WRAPPER}} .lsd-locations-list-item:hover span' => 'background-color:{{VALUE}};',
            ],
        ]);

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->end_controls_section();

        $this->start_controls_section('tags_' . $this->key,
        [
            'label'      => esc_html__('Tags', 'listdom'),
            'responsive' => true,
            'tab'        => self::TAB_STYLE,
            'condition'  => $this->module_condition('tags'),
        ]);

        $this->start_controls_tabs('tags_style_tabs');

        $this->start_controls_tab('tags_normal_tab',
        [
            'label' => esc_html__('Normal', 'listdom'),
        ]);

        $this->add_control('tags_typography',
        [
            'type'       => 'typography',
            'label'      => esc_html__('Typography', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsdtb-tags-module a, {{WRAPPER}} .lsdtb-tags-module span.lsd-single-term' => '{{VALUE}}',
            ],
        ]);

        $this->add_control('tags_color',
        [
            'type'       => 'colorpicker',
            'label'      => esc_html__('Color', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsdtb-tags-module a, {{WRAPPER}} .lsdtb-tags-module span.lsd-single-term' => 'color:{{VALUE}};',
            ],
        ]);

        $this->add_control('tags_bg',
        [
            'type'       => 'colorpicker',
            'label'      => esc_html__('Background', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsdtb-tags-module a, {{WRAPPER}} .lsdtb-tags-module span.lsd-single-term' => 'background-color:{{VALUE}};',
            ],
        ]);

        $this->add_control('tags_padding',
        [
            'type'       => 'padding',
            'label'      => esc_html__('Padding', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsdtb-tags-module a, {{WRAPPER}} .lsdtb-tags-module span.lsd-single-term' => '{{VALUE}}',
            ],
        ]);

        $this->add_control('tags_border',
        [
            'type'            => 'border',
            'label'           => esc_html__('Border', 'listdom'),
            'responsive'      => true,
            'radius_fallback' => 'tags_border_radius',
            'selectors'       => [
                '{{WRAPPER}} .lsdtb-tags-module a, {{WRAPPER}} .lsdtb-tags-module span.lsd-single-term' => '{{VALUE}}',
            ],
        ]);

        $this->end_controls_tab();

        $this->start_controls_tab('tags_hover_tab',
        [
            'label' => esc_html__('Hover', 'listdom'),
        ]);

        $this->add_control('tags_hover_color',
        [
            'type'       => 'colorpicker',
            'label'      => esc_html__('Color', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsdtb-tags-module li:hover a, {{WRAPPER}} .lsdtb-tags-module li:hover span.lsd-single-term' => 'color:{{VALUE}};',
            ],
        ]);

        $this->add_control('tags_hover_bg',
        [
            'type'       => 'colorpicker',
            'label'      => esc_html__('Background', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsdtb-tags-module li:hover a, {{WRAPPER}} .lsdtb-tags-module li:hover span.lsd-single-term' => 'background-color:{{VALUE}};',
            ],
        ]);

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->end_controls_section();

        $this->start_controls_section('availability_' . $this->key,
        [
            'label'      => esc_html__('Working Hours', 'listdom'),
            'responsive' => true,
            'tab'        => self::TAB_STYLE,
            'condition'  => $this->module_condition('availability'),
        ]);

        $this->add_control('availability_typography',
        [
            'type'       => 'typography',
            'label'      => esc_html__('Typography', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsd-ava-one-day span' => '{{VALUE}}',
            ],
        ]);

        $this->add_control('availability_color',
        [
            'type'       => 'colorpicker',
            'label'      => esc_html__('Color', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsd-ava-one-day span' => 'color:{{VALUE}};',
            ],
        ]);

        $this->add_control('availability_bg',
        [
            'type'       => 'colorpicker',
            'label'      => esc_html__('Background', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsd-ava-one-day span' => 'background-color:{{VALUE}};',
            ],
        ]);

        $this->add_control('availability_padding',
        [
            'type'       => 'padding',
            'label'      => esc_html__('Padding', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsd-ava-one-day span' => '{{VALUE}}',
            ],
        ]);

        $this->add_control('availability_border',
        [
            'type'            => 'border',
            'label'           => esc_html__('Border', 'listdom'),
            'responsive'      => true,
            'radius_fallback' => 'availability_border_radius',
            'selectors'       => [
                '{{WRAPPER}} .lsd-ava-one-day span' => '{{VALUE}}',
            ],
        ]);

        $this->end_controls_section();

        $this->start_controls_section('share_' . $this->key,
        [
            'label'      => esc_html__('Share', 'listdom'),
            'responsive' => true,
            'tab'        => self::TAB_STYLE,
            'condition'  => $this->module_condition('share'),
        ]);

        $this->start_controls_tabs('share_style_tabs');

        $this->start_controls_tab('share_normal_tab',
        [
            'label' => esc_html__('Normal', 'listdom'),
        ]);

        $this->add_control('share_color',
        [
            'type'       => 'colorpicker',
            'label'      => esc_html__('Icon Color', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsd-share-buttons a, {{WRAPPER}} .lsd-share-buttons i, {{WRAPPER}} .lsd-share-buttons svg' => 'color:{{VALUE}};fill:{{VALUE}};',
            ],
        ]);

        $this->add_control('share_bg',
        [
            'type'       => 'colorpicker',
            'label'      => esc_html__('Background', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsd-share-buttons a' => 'background-color:{{VALUE}};',
            ],
        ]);

        $this->add_control('share_size',
        [
            'type'       => 'number',
            'class'      => 'lsd-admin-input',
            'label'      => esc_html__('Icon Size', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsd-share-buttons a, {{WRAPPER}} .lsd-share-buttons i' => 'font-size:{{VALUE}}px;',
                '{{WRAPPER}} .lsd-share-buttons svg' => 'width:{{VALUE}}px;height:{{VALUE}}px;',
            ],
        ]);

        $this->add_control('share_padding',
        [
            'type'       => 'padding',
            'label'      => esc_html__('Padding', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsd-share-buttons a' => '{{VALUE}}',
            ],
        ]);

        $this->add_control('share_border',
        [
            'type'            => 'border',
            'label'           => esc_html__('Border', 'listdom'),
            'responsive'      => true,
            'radius_fallback' => 'share_border_radius',
            'selectors'       => [
                '{{WRAPPER}} .lsd-share-buttons a' => '{{VALUE}}',
            ],
        ]);

        $this->end_controls_tab();

        $this->start_controls_tab('share_hover_tab',
        [
            'label' => esc_html__('Hover', 'listdom'),
        ]);

        $this->add_control('share_hover_color',
        [
            'type'       => 'colorpicker',
            'label'      => esc_html__('Icon Color', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsd-share-buttons a:hover, {{WRAPPER}} .lsd-share-buttons a:hover i, {{WRAPPER}} .lsd-share-buttons a:hover svg' => 'color:{{VALUE}};fill:{{VALUE}};',
            ],
        ]);

        $this->add_control('share_hover_bg',
        [
            'type'       => 'colorpicker',
            'label'      => esc_html__('Background', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsd-share-buttons a:hover' => 'background-color:{{VALUE}};',
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
        $style_settings = isset($settings['style']) && is_array($settings['style']) ? $settings['style'] : [];

        $link_method = $content_settings['link_method'] ?? 'normal';
        $resolution_size = $style_settings['image_resolution'] ?? 'medium';

        $top_left = $this->selected_position_modules($content_settings, 'top_left');
        $top_right = $this->selected_position_modules($content_settings, 'top_right');
        $bottom_left = $this->selected_position_modules($content_settings, 'bottom_left');
        $bottom_right = $this->selected_position_modules($content_settings, 'bottom_right');

        $listing = new LSD_Entity_Listing($listing_id);

        $top_left_html = $this->get_modules_output($listing, 'top-left', $top_left);
        $top_right_html = $this->get_modules_output($listing, 'top-right', $top_right);
        $bottom_left_html = $this->get_modules_output($listing, 'bottom-left', $bottom_left);
        $bottom_right_html = $this->get_modules_output($listing, 'bottom-right', $bottom_right);

        $output = '<div class="lsd-template-element-image lsdtb-card-image">'
            . $listing->get_cover_image($resolution_size, $link_method)
            . '<div class="lsdtb-card-image-overlay">
                <div class="lsdtb-card-image-overlay-top-left">' . LSD_Kses::full($top_left_html) . '</div>
                <div class="lsdtb-card-image-overlay-top-right">' . LSD_Kses::full($top_right_html) . '</div>
                <div class="lsdtb-card-image-overlay-bottom-left">' . LSD_Kses::full($bottom_left_html) . '</div>
                <div class="lsdtb-card-image-overlay-bottom-right">' . LSD_Kses::full($bottom_right_html) . '</div>
            </div>
        </div>';

        return apply_filters('lsd_template_element_image_output', $output, $this, $args);
    }

    private function modules(): array
    {
        return apply_filters('lsdtb_image_modules', [
            'availability' => esc_html__('Working Hours', 'listdom'),
            'labels'       => esc_html__('Labels', 'listdom'),
            'owner'        => esc_html__('Owner', 'listdom'),
            'categories'   => esc_html__('Categories', 'listdom'),
            'price'        => esc_html__('Price', 'listdom'),
            'locations'    => esc_html__('Locations', 'listdom'),
            'share'        => esc_html__('Share', 'listdom'),
            'tags'         => esc_html__('Tags', 'listdom'),
        ]);
    }

    private function module_condition(string $module): array
    {
        return [
            'relation'     => 'OR',
            'top_left'     => [$module],
            'top_right'    => [$module],
            'bottom_left'  => [$module],
            'bottom_right' => [$module],
        ];
    }

    private function any_module_condition(): array
    {
        return [
            'relation'     => 'OR',
            'top_left'     => array_keys($this->modules()),
            'top_right'    => array_keys($this->modules()),
            'bottom_left'  => array_keys($this->modules()),
            'bottom_right' => array_keys($this->modules()),
        ];
    }

    private function selected_position_modules(array $content_settings, string $position): array
    {
        return isset($content_settings[$position]) && is_array($content_settings[$position])
            ? array_values(array_filter(array_map('sanitize_key', $content_settings[$position])))
            : [];
    }

    private function selected_modules(array $content_settings): array
    {
        $positions = ['top_left', 'top_right', 'bottom_left', 'bottom_right'];
        $modules = [];

        foreach ($positions as $position)
        {
            foreach ($this->selected_position_modules($content_settings, $position) as $module)
            {
                $modules[$module] = $module;
            }
        }

        return array_values($modules);
    }

    private function has_module(array $content_settings, string $module): bool
    {
        return in_array($module, $this->selected_modules($content_settings), true);
    }

    private function get_modules_output(LSD_Entity_Listing $listing, string $position, array $modules = []): string
    {
        $settings = $this->settings;

        $content_settings = isset($settings['content']) && is_array($settings['content']) ? $settings['content'] : [];
        $style_settings = isset($settings['style']) && is_array($settings['style']) ? $settings['style'] : [];
        $output = '';

        foreach ($modules as $module)
        {
            $module = sanitize_key((string) $module);
            if ($module === '') continue;

            $module_output = '';

            if ($module === 'availability')
            {
                $module_output .= $listing->get_availability(true);
            }
            else if ($module === 'labels')
            {
                $default_colors = $content_settings['labels_default_colors'] ?? $content_settings['default_colors'] ?? 1;
                $labels = $listing->get_labels();

                if (trim($labels))
                {
                    if ((int) $default_colors !== 1)
                    {
                        $labels = preg_replace('/\sstyle=(["\']).*?\1/', '', $labels);
                    }

                    $module_output .= '<div class="lsdtb-labels-module">' . $labels . '</div>';
                }
            }
            else if ($module === 'tags')
            {
                $tags = $listing->get_tags();
                $module_output .= trim($tags) ? '<div class="lsdtb-tags-module">' . $tags . '</div>' : '';
            }
            else if ($module === 'owner')
            {
                $module_output .= $listing->get_owner('image-name');
            }
            else if ($module === 'share')
            {
                $module_output .= $listing->get_share_buttons();
            }
            else if ($module === 'categories')
            {
                $module_output .= $this->get_categories_module_output($listing, $content_settings, $style_settings);
            }
            else if ($module === 'price')
            {
                $price = $listing->get_price(true);
                $module_output .= trim($price) ? '<div class="lsdtb-price-module lsd-color-m-bg ' . LSD_Color::text_class() . '">' . $price . '</div>' : '';
            }
            else if ($module === 'locations')
            {
                $module_output .= $listing->get_locations();
            }
            else
            {
                $module_output .= apply_filters('lsdtb_image_module_output', '', $module, $listing, $position, $modules);
            }

            if (trim($module_output) !== '')
            {
                $output .= '<div class="lsdtb-card-image-module lsdtb-card-image-module-' . sanitize_html_class($module) . '">' . $module_output . '</div>';
            }
        }

        return (string) apply_filters('lsdtb_image_modules_output', $output, $listing, $position, $modules);
    }

    private function get_categories_module_output(LSD_Entity_Listing $listing, array $content_settings, array $style_settings): string
    {
        $multiple = apply_filters('lsd_listing_display_multiple_categories', false);

        $display_name = !isset($style_settings['categories_display_name'])
            || $style_settings['categories_display_name'] === '1'
            || $style_settings['categories_display_name'] === 1;

        $display_icon = !empty($style_settings['categories_display_icon']);

        $default_colors = $content_settings['categories_default_colors'] ?? $content_settings['default_colors'] ?? 1;
        $show_color = (int) $default_colors === 1;

        $categories = $listing->get_categories([
            'show_color'          => $show_color,
            'multiple_categories' => $multiple,
            'display_name'        => $display_name,
            'display_icon'        => $display_icon,
        ]);

        return trim($categories) ? '<div class="lsdtb-categories-module">' . $categories . '</div>' : '';
    }

    private function get_image_sizes(): array
    {
        $sizes = get_intermediate_image_sizes();
        $options = [];

        foreach ($sizes as $size)
        {
            $label = ucwords(str_replace(['-', '_'], ' ', $size));
            $options[$size] = $label;
        }

        $options['full'] = esc_html__('Full', 'listdom');

        return $options;
    }
}
