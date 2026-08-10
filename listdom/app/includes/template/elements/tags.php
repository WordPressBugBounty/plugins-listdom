<?php

class LSD_Template_Elements_Tags extends LSD_Template
{
    public function __construct($settings = [])
    {
        $this->key            = 'tags';
        $this->label          = $this->label();
        $this->category       = 'taxonomies';
        $this->template_types = ['single_listing', 'listing_card', 'info_window'];
        $this->icon           = self::icons($this->key);

        parent::__construct($settings);
    }

    protected function label()
    {
        return esc_html__('Listing Tags', 'listdom');
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
                '{{WRAPPER}} .lsdtb-card-tags ul' => 'flex-direction:{{VALUE}};',
            ],
        ]);

        $this->add_control('enable_links',
        [
            'type'    => 'switcher',
            'label'   => esc_html__('Enable Archive Links', 'listdom'),
            'default' => 0,
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
                '{{WRAPPER}} .lsdtb-card-tags' => 'background-color:{{VALUE}};',
            ],
        ]);

        $this->add_control('box_border',
        [
            'type'       => 'border',
            'label'      => esc_html__('Border', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsdtb-card-tags' => '{{VALUE}}',
            ],
        ]);

        $this->add_control('box_padding',
        [
            'type'       => 'padding',
            'label'      => esc_html__('Padding', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsdtb-card-tags' => '{{VALUE}}',
            ],
        ]);

        $this->add_control('gap',
        [
            'type'       => 'number',
            'class'      => 'lsd-admin-input',
            'label'      => esc_html__('Gap Between Items', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsdtb-card-tags ul' => 'gap:{{VALUE}}px;',
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

        $this->add_control('tags_typography',
        [
            'type'       => 'typography',
            'label'      => esc_html__('Typography', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsdtb-card-tags li, {{WRAPPER}} .lsdtb-card-tags a, {{WRAPPER}} .lsdtb-card-tags span.lsd-single-term' => '{{VALUE}}',
            ],
        ]);

        $this->add_control('item_color',
        [
            'type'       => 'colorpicker',
            'label'      => esc_html__('Color', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsdtb-card-tags a, {{WRAPPER}} .lsdtb-card-tags span.lsd-single-term' => 'color:{{VALUE}};',
            ],
        ]);

        $this->add_control('item_background_color',
        [
            'type'       => 'colorpicker',
            'label'      => esc_html__('Background Color', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsdtb-card-tags a, {{WRAPPER}} .lsdtb-card-tags span.lsd-single-term' => 'background-color:{{VALUE}};',
            ],
        ]);

        $this->add_control('item_border',
        [
            'type'       => 'border',
            'label'      => esc_html__('Border', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsdtb-card-tags ul a, {{WRAPPER}} .lsdtb-card-tags ul span.lsd-single-term' => '{{VALUE}}',
            ],
        ]);

        $this->add_control('items_padding',
        [
            'type'       => 'padding',
            'label'      => esc_html__('Padding', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsdtb-card-tags a, {{WRAPPER}} .lsdtb-card-tags span.lsd-single-term' => '{{VALUE}}',
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
                '{{WRAPPER}} .lsdtb-card-tags li:hover a, {{WRAPPER}} .lsdtb-card-tags li:hover span.lsd-single-term' => 'color:{{VALUE}};',
            ],
        ]);

        $this->add_control('item_hover_background_color',
        [
            'type'       => 'colorpicker',
            'label'      => esc_html__('Background Color', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsdtb-card-tags li:hover a, {{WRAPPER}} .lsdtb-card-tags li:hover span.lsd-single-term' => 'background-color:{{VALUE}};',
            ],
        ]);

        $this->add_control('item_hover_border',
        [
            'type'       => 'border',
            'label'      => esc_html__('Border', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsdtb-card-tags li:hover a, {{WRAPPER}} .lsdtb-card-tags li:hover span.lsd-single-term' => '{{VALUE}}',
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
        $enable_links = !empty($content_settings['enable_links']);

        $listing = new LSD_Entity_Listing($listing_id);
        $output = $listing->get_tags($enable_links);
        if (trim($output) === '') return '';

        return '<div class="lsd-template-element-tags lsdtb-card-tags">' . $output . '</div>';
    }
}
