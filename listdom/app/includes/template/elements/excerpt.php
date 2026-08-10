<?php

class LSD_Template_Elements_Excerpt extends LSD_Template
{
    public function __construct($settings = [])
    {
        $this->key            = 'excerpt';
        $this->label          = $this->label();
        $this->category       = 'basic';
        $this->template_types = ['single_listing', 'listing_card', 'info_window'];
        $this->icon           = self::icons($this->key);

        parent::__construct($settings);
    }

    protected function label()
    {
        return esc_html__('Listing Excerpt', 'listdom');
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
            'label'       => esc_html__('Excerpt Length', 'listdom'),
            'description' => esc_html__('Length in words', 'listdom'),
            'default'     => '',
        ]);

        $this->add_control('read_more',
        [
            'type'    => 'switcher',
            'label'   => esc_html__('Read More Link', 'listdom'),
            'default' => 0,
        ]);

        $this->add_control('full',
        [
            'type'    => 'switcher',
            'label'   => esc_html__('Use Full Content', 'listdom'),
            'default' => 0,
        ]);

        $this->add_control('auto_p',
        [
            'type'    => 'switcher',
            'label'   => esc_html__('Auto Paragraph', 'listdom'),
            'default' => 1,
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
                '{{WRAPPER}} .lsd-template-element-excerpt' => '{{VALUE}}',
                '{{WRAPPER}} .lsd-template-element-excerpt p' => '{{VALUE}}',
                '{{WRAPPER}} .lsdtb-card-excerpt' => '{{VALUE}}',
                '{{WRAPPER}} .lsdtb-card-excerpt p' => '{{VALUE}}',
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
                '{{WRAPPER}} .lsd-template-element-excerpt' => 'color:{{VALUE}};',
                '{{WRAPPER}} .lsd-template-element-excerpt p' => 'color:{{VALUE}};',
                '{{WRAPPER}} .lsdtb-card-excerpt' => 'color:{{VALUE}};',
                '{{WRAPPER}} .lsdtb-card-excerpt p' => 'color:{{VALUE}};',
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

        $limit = isset($content_settings['content_length']) ? (int) $content_settings['content_length'] : 0;
        $read_more = !empty($content_settings['read_more']);
        $full = !empty($content_settings['full']);
        $auto_p = !empty($content_settings['auto_p']);

        $excerpt = get_the_excerpt($listing_id);

        if ((trim($excerpt) === '' && $limit > 0) || $full)
        {
            $excerpt = strip_shortcodes(get_post_field('post_content', $listing_id));
        }

        $has_more = false;
        if ($limit > 0)
        {
            $words = explode(' ', wp_strip_all_tags($excerpt));
            $excerpt = implode(' ', array_slice($words, 0, $limit));
            $has_more = count($words) > $limit;
        }

        $html = $excerpt . ($has_more ? ' ...' : '');

        if ($has_more && $read_more)
        {
            $html .= ' <a href="' . esc_url(get_the_permalink($listing_id)) . '" class="lsd-excerpt-read-more lsd-color-m-txt">[' . esc_html__('More', 'listdom') . ']</a>';
        }

        if ($auto_p)
        {
            $html = wpautop($html);
            if (strpos($html, '<p>') !== false) $html = str_replace('<p>', '<p class="lsd-fe-description">', $html);
        }

        if (trim(wp_strip_all_tags($html)) === '') return '';

        $output = '<div class="lsd-template-element-excerpt lsdtb-card-excerpt">' . $html . '</div>';

        return apply_filters('lsd_template_element_excerpt_output', $output, $this, $args);
    }
}
