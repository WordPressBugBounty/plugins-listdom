<?php

class LSD_Template_Elements_Content extends LSD_Template
{
    public function __construct($settings = [])
    {
        $this->key            = 'content';
        $this->label          = $this->label();
        $this->category       = 'basic';
        $this->template_types = ['single_listing', 'listing_card', 'info_window'];
        $this->icon           = self::icons($this->key);

        parent::__construct($settings);
    }

    protected function label()
    {
        return esc_html__('Listing Content', 'listdom');
    }

    public function controls(): void
    {
        $this->start_controls_section('content_' . $this->key,
        [
            'label' => esc_html__('Options', 'listdom'),
            'tab'   => self::TAB_CONTENT,
        ]);

        $this->add_control('content_length',
        [
            'type'        => 'number',
            'class'       => 'lsd-admin-input',
            'label'       => esc_html__('Content Length', 'listdom'),
            'description' => esc_html__('Length in words', 'listdom'),
            'default'     => '',
        ]);

        $this->end_controls_section();

        $this->start_controls_section('style_' . $this->key,
        [
            'label'      => esc_html__('Style', 'listdom'),
            'responsive' => true,
            'tab'        => self::TAB_STYLE
        ]);

        $this->add_control('typography',
        [
            'type'        => 'typography',
            'label'       => esc_html__('Typography', 'listdom'),
            'description' => '',
            'responsive'  => true,
            'selectors'   => [
                '{{WRAPPER}} .lsd-template-element-content' => '{{VALUE}}',
                '{{WRAPPER}} .lsdtb-card-content' => '{{VALUE}}',
            ],
        ]);

        $this->add_control('text_color',
        [
            'type'        => 'colorpicker',
            'label'       => esc_html__('Text Color', 'listdom'),
            'default'     => '#000',
            'placeholder' => '#000',
            'description' => '',
            'responsive'  => true,
            'selectors'   => [
                '{{WRAPPER}} .lsd-template-element-content' => 'color:{{VALUE}};',
                '{{WRAPPER}} .lsdtb-card-content' => 'color:{{VALUE}};',
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

        $listing = new LSD_Entity_Listing($listing_id);
        if (!$listing->post instanceof WP_Post) return '';

        $content = (string) $listing->post->post_content;

        // Content Length
        $length = isset($content_settings['content_length']) ? (int) $content_settings['content_length'] : 0;
        if ($length)
        {
            $content = wp_strip_all_tags($content);
            $words = explode(' ', $content);
            $content = implode(' ', array_slice($words, 0, $length));
        }

        if (trim($content) === '') return '';

        $output = '<div class="lsd-template-element-content lsdtb-card-content">' . $listing->get_content($content) . '</div>';

        return apply_filters('lsd_template_element_content_output', $output, $this, $args);
    }
}
