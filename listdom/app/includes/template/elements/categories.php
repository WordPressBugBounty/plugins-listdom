<?php

class LSD_Template_Elements_Categories extends LSD_Template
{
    public function __construct($settings = [])
    {
        $this->key            = 'categories';
        $this->label          = $this->label();
        $this->category       = 'taxonomies';
        $this->template_types = ['single_listing', 'listing_card', 'info_window',];
        $this->icon           = self::icons($this->key);

        parent::__construct($settings);
    }

    protected function label()
    {
        return esc_html__('Listing Categories', 'listdom');
    }

    public function controls(): void
    {
        $this->start_controls_section('settings_' . $this->key,
        [
            'label' => esc_html__('Options', 'listdom'),
            'tab'   => self::TAB_CONTENT,
        ]);

        $this->add_control('default_colors',
        [
            'type'    => 'switcher',
            'label'   => esc_html__('Default Colors', 'listdom'),
            'default' => 1,
        ]);

        $this->add_control('color_method',
            [
                'type'    => 'select',
                'class'   => 'lsd-admin-input',
                'label'   => esc_html__('Color Method', 'listdom'),
                'show_empty' => true,
                'empty_label' => esc_html__('Select Method', 'listdom'),
                'options' => [
                    'bg'   => esc_html__('Background Color', 'listdom'),
                    'text' => esc_html__('Text Color', 'listdom'),
                ],
                'condition' => [
                    'default_colors' => true,
                ]
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
                '{{WRAPPER}} .lsdtb-card-categories' => 'display: flex;flex-direction:{{VALUE}};',
            ],
        ]);

        $this->add_control('display_name',
        [
            'type'    => 'switcher',
            'label'   => esc_html__('Category Name', 'listdom'),
            'default' => 1,
        ]);

        $this->add_control('display_icon',
        [
            'type'    => 'switcher',
            'label'   => esc_html__('Category Icon', 'listdom'),
            'default' => 0,
        ]);

        $this->add_control('enable_link',
        [
            'type'    => 'switcher',
            'label'   => esc_html__('Enable Archive Links', 'listdom'),
            'default' => 0,
        ]);

        $this->add_control('multiple',
        [
            'type'    => 'select',
            'class'   => 'lsd-admin-input',
            'label'   => esc_html__('Show', 'listdom'),
            'default' => 1,
            'options' => [
                1 => esc_html__('All Categories', 'listdom'),
                0 => esc_html__('Primary Category Only', 'listdom'),
            ],
        ]);

        $this->end_controls_section();

        $this->start_controls_section('wrapper_' . $this->key,
        [
            'label'      => esc_html__('Wrapper', 'listdom'),
            'responsive' => true,
            'tab'        => self::TAB_STYLE
        ]);

        $this->add_control('wrapper_bg_color',
        [
            'type'        => 'colorpicker',
            'label'       => esc_html__('Background', 'listdom'),
            'placeholder' => '#fff',
            'description' => '',
            'responsive'  => true,
            'selectors'   => [
                '{{WRAPPER}} .lsd-template-element-categories' => 'background-color:{{VALUE}};',
                '{{WRAPPER}} .lsdtb-card-categories' => 'background-color:{{VALUE}};',
            ],
        ]);

        $this->add_control('wrapper_border',
        [
            'type'        => 'border',
            'label'       => esc_html__('Border', 'listdom'),
            'default'     => '4px 8px',
            'description' => '',
            'responsive'  => true,
            'radius_fallback' => 'wrapper_border_radius',
            'selectors'   => [
                '{{WRAPPER}} .lsd-template-element-categories' => '{{VALUE}}',
                '{{WRAPPER}} .lsdtb-card-categories' => '{{VALUE}}',
            ],
        ]);

        $this->add_control('wrapper_padding',
        [
            'type'        => 'padding',
            'label'       => esc_html__('Padding', 'listdom'),
            'default'     => '4px 8px',
            'description' => '',
            'responsive'  => true,
            'selectors'   => [
                '{{WRAPPER}} .lsd-template-element-categories' => '{{VALUE}}',
                '{{WRAPPER}} .lsdtb-card-categories' => '{{VALUE}}',
            ],
        ]);

        $this->add_control('wrapper_gap',
        [
            'type'       => 'number',
            'class'      => 'lsd-admin-input',
            'label'      => esc_html__('Gap Between Items', 'listdom'),
            'responsive' => true,
            'default' => 12,
            'selectors'  => [
                '{{WRAPPER}} .lsd-template-element-categories' => 'gap:{{VALUE}}px;',
                '{{WRAPPER}} .lsdtb-card-categories' => 'gap:{{VALUE}}px;',
            ],
        ]);

        $this->end_controls_section();

        $this->start_controls_section('items_' . $this->key,
        [
            'label'      => esc_html__('Items', 'listdom'),
            'responsive' => true,
            'tab'        => self::TAB_STYLE
        ]);

        $this->start_controls_tabs('items_tabs_' . $this->key);

        $this->start_controls_tab('items_normal_tab_' . $this->key,
        [
            'label' => esc_html__('Normal', 'listdom'),
        ]);

        $this->add_control('text_color',
        [
            'type'        => 'colorpicker',
            'label'       => esc_html__('Color', 'listdom'),
            'default'     => '#000',
            'placeholder' => '#f5f5f5',
            'description' => '',
            'responsive'  => true,
            'selectors'   => [
                '{{WRAPPER}} .lsd-template-element-categories .lsd-template-element-category-item a' => 'color:{{VALUE}};',
                '{{WRAPPER}} .lsd-template-element-categories .lsd-template-element-category-item .lsd-single-term' => 'color:{{VALUE}};',
                '{{WRAPPER}} .lsdtb-card-categories a' => 'color:{{VALUE}};',
                '{{WRAPPER}} .lsdtb-card-categories span.lsd-single-term' => 'color:{{VALUE}};',
            ],
        ]);

        $this->add_control('icon_color',
            [
                'type'        => 'colorpicker',
                'label'       => esc_html__('Icon Color', 'listdom'),
                'default'     => '#000',
                'placeholder' => '#f5f5f5',
                'description' => '',
                'responsive'  => true,
                'selectors'   => [
                    '{{WRAPPER}} .lsd-template-element-categories .lsd-template-element-category-item a i' => 'color:{{VALUE}};',
                    '{{WRAPPER}} .lsd-template-element-categories .lsd-template-element-category-item .lsd-single-term i' => 'color:{{VALUE}};',
                    '{{WRAPPER}} .lsdtb-card-categories a i' => 'color:{{VALUE}};',
                    '{{WRAPPER}} .lsdtb-card-categories span.lsd-single-term i' => 'color:{{VALUE}};',
                ],
            ]);

        $this->add_control('bg_color',
        [
            'type'        => 'colorpicker',
            'label'       => esc_html__('Background', 'listdom'),
            'placeholder' => '#f5f5f5',
            'description' => '',
            'responsive'  => true,
            'selectors'   => [
                '{{WRAPPER}} .lsd-template-element-categories .lsd-template-element-category-item' => 'background-color:{{VALUE}};',
            ],
        ]);

        $this->add_control('border',
        [
            'type'        => 'border',
            'label'       => esc_html__('Border', 'listdom'),
            'default'     => [
                'top'    => '0',
                'right'  => '0',
                'bottom' => '0',
                'left'   => '0',
                'style'  => 'none',
                'color'  => '#fff',
                'radius' => '0'
            ],
            'description' => '',
            'responsive'  => true,
            'selectors'   => [
                '{{WRAPPER}} .lsd-template-element-categories .lsd-template-element-category-item' => '{{VALUE}}',
            ],
        ]);

        $this->add_control('padding',
        [
            'type'        => 'padding',
            'label'       => esc_html__('Padding', 'listdom'),
            'default'     => [
                'top'    => '0',
                'right'  => '0',
                'bottom' => '0',
                'left'   => '0',
            ],
            'description' => '',
            'responsive'  => true,
            'selectors'   => [
                '{{WRAPPER}} .lsd-template-element-categories .lsd-template-element-category-item' => '{{VALUE}}',
            ],
        ]);

        $this->add_control('typography',
        [
            'type'        => 'typography',
            'label'       => esc_html__('Typography', 'listdom'),
            'default'     => [],
            'description' => '',
            'responsive'  => true,
            'selectors'   => [
                '{{WRAPPER}} .lsd-template-element-categories .lsd-template-element-category-item' => '{{VALUE}}',
                '{{WRAPPER}} .lsdtb-card-categories a' => '{{VALUE}}',
                '{{WRAPPER}} .lsdtb-card-categories span.lsd-single-term' => '{{VALUE}}',
            ],
        ]);

        $this->add_control('icon_gap',
        [
            'type'       => 'number',
            'class'      => 'lsd-admin-input',
            'label'      => esc_html__('Icon Gap', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsd-template-element-categories .lsd-template-element-category-item a i' => 'margin-right:{{VALUE}}px;',
                '{{WRAPPER}} .lsd-template-element-categories .lsd-template-element-category-item .lsd-single-term i' => 'margin-right:{{VALUE}}px;',
                '{{WRAPPER}} .lsdtb-card-categories a i' => 'margin-right:{{VALUE}}px;',
                '{{WRAPPER}} .lsdtb-card-categories span.lsd-single-term i' => 'margin-right:{{VALUE}}px;',
            ],
        ]);

        $this->end_controls_tab();

        $this->start_controls_tab('items_hover_tab_' . $this->key,
        [
            'label' => esc_html__('Hover', 'listdom'),
        ]);

        $this->add_control('bg_hover_color',
        [
            'type'        => 'colorpicker',
            'label'       => esc_html__('Hover Background', 'listdom'),
            'description' => '',
            'responsive'  => true,
            'selectors'   => [
                '{{WRAPPER}} .lsd-template-element-categories .lsd-template-element-category-item:hover' => 'background-color:{{VALUE}};',
            ],
        ]);

        $this->add_control('text_hover_color',
        [
            'type'        => 'colorpicker',
            'label'       => esc_html__('Hover Text Color', 'listdom'),
            'description' => '',
            'responsive'  => true,
            'selectors'   => [
                '{{WRAPPER}} .lsd-template-element-categories .lsd-template-element-category-item:hover a i' => 'color:{{VALUE}};',
                '{{WRAPPER}} .lsd-template-element-categories .lsd-template-element-category-item:hover a' => 'color:{{VALUE}};',
                '{{WRAPPER}} .lsd-template-element-categories .lsd-template-element-category-item a:hover' => 'color:{{VALUE}};',
                '{{WRAPPER}} .lsd-template-element-categories .lsd-template-element-category-item .lsd-single-term:hover' => 'color:{{VALUE}};',
                '{{WRAPPER}} .lsdtb-card-categories a:hover' => 'color:{{VALUE}};',
                '{{WRAPPER}} .lsdtb-card-categories span.lsd-single-term:hover' => 'color:{{VALUE}};',
            ],
        ]);

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->end_controls_section();
    }

    /**
     * Render the element on frontend.
     *
     * @param array $args
     * @return string
     */
    public function render(array $args = []): string
    {
        $listing_id = isset($args['listing_id']) ? (int) $args['listing_id'] : 0;

        $settings         = isset($args['settings']) && is_array($args['settings']) ? $args['settings'] : [];

        if (!$listing_id) return '';

        $content_settings = isset($settings['content']) && is_array($settings['content']) ? $settings['content'] : [];
        $enable_link  = isset($content_settings['enable_link']) ? (int) $content_settings['enable_link'] : 1;
        $multiple     = isset($content_settings['multiple']) ? (int) $content_settings['multiple'] : 1;
        $has_color_method = array_key_exists('color_method', $content_settings);
        $color_method = $has_color_method ? sanitize_key((string) $content_settings['color_method']) : 'bg';
        $display_icon = isset($content_settings['display_icon']) ? (int) $content_settings['display_icon'] : 0;
        $display_name = isset($content_settings['display_name']) ? (int) $content_settings['display_name'] : 1;
        $default_colors = $content_settings['default_colors'] ?? null;
        $show_color = $default_colors !== null ? (int) $default_colors : (int) ($content_settings['show_color'] ?? 1);

        // Get categories
        $terms = [];

        if ($multiple)
        {
            // All categories
            $terms = wp_get_post_terms($listing_id, LSD_Base::TAX_CATEGORY);
        } else
        {
            // Primary category via entity (if available)
            $entity  = new LSD_Entity_Listing($listing_id);
            $primary = $entity->get_data_category();

            if ($primary instanceof WP_Term)
            {
                $terms = [$primary];
            }
            elseif (!empty($primary))
            {
                $term_obj = get_term($primary, LSD_Base::TAX_CATEGORY);
                if ($term_obj && !is_wp_error($term_obj)) $terms = [$term_obj];
            }
        }

        if (empty($terms)) return '';

        $items = [];

        foreach ($terms as $term)
        {
            if (!$term instanceof WP_Term) continue;

            $icon_html = '';
            if ($display_icon) $icon_html = LSD_Taxonomies::icon($term->term_id);

            $name_html = $display_name ? esc_html($term->name) : '';

            $style_string = $show_color && in_array($color_method, ['bg', 'text'], true)
                ? LSD_Element_Categories::styles($term->term_id, $color_method)
                : '';

            if ($enable_link)
            {
                $url   = get_term_link($term->term_id);
                $inner = '<a href="' . esc_url($url) . '"' . ($style_string ?? '') . ' ' . lsd_schema()->category() . '>' . $icon_html . $name_html . '</a>';
            }
            else
            {
                $inner = '<span class="lsd-single-term"' . ($style_string ?? '') . ' ' . lsd_schema()->category() . '>' . $icon_html . $name_html . '</span>';
            }

            $items[] = '<span class="lsd-template-element-category-item"' . ($style_string ?? '') . '>' . $inner . '</span>';
        }

        if (empty($items)) return '';

        $output  = '<div class="lsd-template-element-categories lsdtb-card-categories">';
        $output .= implode(' ', $items);
        $output .= '</div>';

        return apply_filters('lsd_template_element_categories_output', $output, $this, $args);
    }

}
