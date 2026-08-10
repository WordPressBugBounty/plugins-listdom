<?php

class LSD_Template_Elements_Franchise extends LSD_Template
{
    public function __construct($settings = [])
    {
        $this->key            = 'franchise';
        $this->label          = $this->label();
        $this->category       = 'advanced';
        $this->template_types = ['single_listing', 'listing_card', 'info_window'];
        $this->icon           = self::icons($this->key);

        parent::__construct($settings);
    }

    protected function label()
    {
        return esc_html__('Franchise Addon', 'listdom');
    }

    public function controls(): void
    {
        $this->start_controls_section('heading_' . $this->key,
        [
            'label' => esc_html__('Heading', 'listdom'),
            'tab'   => self::TAB_CONTENT,
        ]);

        $this->add_control('show_section_headings',
        [
            'type'    => 'switcher',
            'label'   => esc_html__('Show Headings', 'listdom'),
            'default' => true,
        ]);

        $this->add_control('parent_heading',
        [
            'type'    => 'text',
            'class'   => 'lsd-admin-input',
            'label'   => esc_html__('Parent', 'listdom'),
            'default' => esc_html__('Parent', 'listdom-franchise'),
            'condition' => [
              'show_section_headings' => true,
            ],
        ]);

        $this->add_control('sub_heading',
        [
            'type'    => 'text',
            'class'   => 'lsd-admin-input',
            'label'   => esc_html__('Sub Listings Heading', 'listdom'),
            'default' => esc_html__('Sub Listings', 'listdom-franchise'),
            'condition' => [
                'show_section_headings' => true,
            ],
        ]);

        $this->end_controls_section();

        $this->start_controls_section('shortcode_' . $this->key,
        [
            'label' => esc_html__('Shortcode Selection', 'listdom'),
            'tab'   => self::TAB_CONTENT,
        ]);

        $posts = get_posts([
            'post_type' => LSD_Base::PTYPE_SHORTCODE,
            'posts_per_page' => -1,
        ]);

        $shortcodes = ['' => esc_html__('Default', 'listdom')];
        foreach ($posts as $post) $shortcodes[$post->ID] = $post->post_title;

        $this->add_control('shortcode',
        [
            'type'        => 'select',
            'class'   => 'lsd-admin-input',
            'label'       => esc_html__('Listdom Shortcode', 'listdom'),
            'options'     => $shortcodes,
            'description' => esc_html__('Use a specific shortcode instead of the current listing context.', 'listdom'),
        ]);

        $this->end_controls_section();

        $this->start_controls_section('heading_style_' . $this->key,
        [
            'label'      => esc_html__('Headings', 'listdom'),
            'responsive' => true,
            'tab'        => self::TAB_STYLE,
            'condition' => [
                'show_section_headings' => true,
            ],
        ]);

        $this->add_control('heading_typography',
        [
            'type'        => 'typography',
            'label'       => esc_html__('Typography', 'listdom'),
            'responsive'  => true,
            'selectors'   => [
                '{{WRAPPER}} .lsd-template-element-franchise h5' => '{{VALUE}}',
                '{{WRAPPER}} .lsdtb-card-franchise h5' => '{{VALUE}}',
            ],
        ]);

        $this->add_control('heading_color',
        [
            'type'       => 'colorpicker',
            'label'      => esc_html__('Heading Color', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsd-template-element-franchise h5' => 'color:{{VALUE}};',
                '{{WRAPPER}} .lsdtb-card-franchise h5' => 'color:{{VALUE}};',
            ],
        ]);

        $this->end_controls_section();
    }

    public function render(array $args = []): string
    {
        $listing_id = isset($args['listing_id']) ? (int) $args['listing_id'] : 0;

        if (!$listing_id)
        {
            global $post;

            if ($post instanceof WP_Post)
            {
                $listing_id = (int) $post->ID;
            }
        }

        if (!$listing_id) return '';

        if (!class_exists(\LSDPACFS\Base::class))
        {
            return LSD_Main::alert(esc_html__('Franchise addon should be installed and activated!', 'listdom'), 'warning');
        }

        $settings = isset($args['settings']) && is_array($args['settings']) ? $args['settings'] : [];
        $content_settings = isset($settings['content']) && is_array($settings['content']) ? $settings['content'] : [];

        $show_section_headings = $content_settings['show_section_headings'] ?? 1;

        $show_section_headings = in_array($show_section_headings, [1, '1', true, 'true', 'yes', 'on',], true);

        $parent_heading = isset($content_settings['parent_heading']) && trim((string) $content_settings['parent_heading']) !== ''
            ? (string) $content_settings['parent_heading']
            : esc_html__('Parent', 'listdom-franchise');

        $sub_heading = isset($content_settings['sub_heading']) && trim((string) $content_settings['sub_heading']) !== ''
            ? (string) $content_settings['sub_heading']
            : esc_html__('Sub Listings', 'listdom-franchise');

        $shortcode = isset($content_settings['shortcode']) ? (string) $content_settings['shortcode'] : '';

        $element = new \LSDPACFS\Element();
        $output = $element->get($listing_id, [
            'show_headings'         => $show_section_headings,
            'show_section_headings' => $show_section_headings,
            'parent_heading'        => $parent_heading,
            'sub_heading'           => $sub_heading,
            'shortcode'             => $shortcode,
        ]);

        if (trim((string) $output) === '') return '';

        $html = '<div class="lsd-template-element-franchise lsdtb-card-franchise">' . $output . '</div>';

        return apply_filters('lsd_template_element_franchise_output', $html, $this, $args);
    }
}
