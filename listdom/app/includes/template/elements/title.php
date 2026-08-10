<?php

class LSD_Template_Elements_Title extends LSD_Template
{
    public function __construct($settings = [])
    {
        $this->key            = 'title';
        $this->label          = $this->label();
        $this->category       = 'basic';
        $this->template_types = ['single_listing', 'listing_card', 'info_window',];
        $this->icon           = self::icons($this->key);

        parent::__construct($settings);
    }
    protected function label()
    {
        return esc_html__('Listing Title', 'listdom');
    }
    public function controls(): void
    {
        $this->start_controls_section('title_' . $this->key,
        [
            'label' => esc_html__('Options', 'listdom'),
            'tab'   => 'content',
        ]);

        $this->add_control('title_tag',
        [
            'type'    => 'select',
            'class'   => 'lsd-admin-input',
            'label'   => esc_html__('Title Tag', 'listdom'),
            'default' => 'h2',
            'options' => [
                'h1'     => esc_html__('Heading 1', 'listdom'),
                'h2'     => esc_html__('Heading 2', 'listdom'),
                'h3'     => esc_html__('Heading 3', 'listdom'),
                'h4'     => esc_html__('Heading 4', 'listdom'),
                'p'      => esc_html__('Paragraph', 'listdom'),
                'strong' => esc_html__('Strong', 'listdom'),
            ],
        ]);

        $this->add_control('link_method',
        [
            'type'    => 'select',
            'class'   => 'lsd-admin-input',
            'label'   => esc_html__('Link Method', 'listdom'),
            'default' => 'normal',
            'options' => LSD_Base::get_listing_link_methods(),
        ]);

        $this->end_controls_section();

        $this->start_controls_section('style_' . $this->key,
        [
            'label'      => esc_html__('Style', 'listdom'),
            'responsive' => true,
            'tab'        => self::TAB_STYLE
        ]);

        $this->start_controls_tabs('style_tabs');

        $this->start_controls_tab('style_normal_tab',
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
                '{{WRAPPER}} .lsd-template-element-title a, {{WRAPPER}} .lsd-template-element-title > *' => 'color:{{VALUE}};',
                '{{WRAPPER}} .lsdtb-card-title a, {{WRAPPER}} .lsdtb-card-title > *' => 'color:{{VALUE}};',
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
                '{{WRAPPER}} .lsd-template-element-title' => 'background-color:{{VALUE}};',
            ],
        ]);

        $this->add_control('border',
        [
            'type'        => 'border',
            'label'       => esc_html__('Border', 'listdom'),
            'description' => '',
            'responsive'  => true,
            'selectors'   => [
                '{{WRAPPER}} .lsd-template-element-title' => '{{VALUE}}',
                '{{WRAPPER}} .lsdtb-card-title' => '{{VALUE}}',
            ],
        ]);

        $this->add_control('padding',
        [
            'type'        => 'padding',
            'label'       => esc_html__('Padding', 'listdom'),
            'description' => '',
            'responsive'  => true,
            'selectors'   => [
                '{{WRAPPER}} .lsd-template-element-title' => '{{VALUE}}',
                '{{WRAPPER}} .lsdtb-card-title' => '{{VALUE}}',
            ],
        ]);

        $this->add_control('typography',
        [
            'type'        => 'typography',
            'label' => esc_html__('Typography', 'listdom'),
            'description' => '',
            'responsive'  => true,
            'selectors'   => [
                '{{WRAPPER}} .lsd-template-element-title a, {{WRAPPER}} .lsd-template-element-title > *' => '{{VALUE}}',
                '{{WRAPPER}} .lsdtb-card-title a, {{WRAPPER}} .lsdtb-card-title > *' => '{{VALUE}}',
            ],
        ]);

        $this->end_controls_tab();

        $this->start_controls_tab('style_hover_tab',
        [
            'label' => esc_html__('Hover', 'listdom'),
        ]);

        $this->add_control('text_hover_color',
        [
            'type'        => 'colorpicker',
            'label'       => esc_html__('Hover Color', 'listdom'),
            'default'     => '',
            'placeholder' => '#f5f5f5',
            'description' => '',
            'responsive'  => true,
            'selectors'   => [
                '{{WRAPPER}} .lsd-template-element-title:hover a, {{WRAPPER}} .lsd-template-element-title:hover > *' => 'color:{{VALUE}};',
                '{{WRAPPER}} .lsdtb-card-title:hover a, {{WRAPPER}} .lsdtb-card-title:hover > *' => 'color:{{VALUE}};',
            ],
        ]);

        $this->add_control('bg_hover_color',
        [
            'type'        => 'colorpicker',
            'label'       => esc_html__('Hover Background', 'listdom'),
            'placeholder' => '#f5f5f5',
            'description' => '',
            'responsive'  => true,
            'selectors'   => [
                '{{WRAPPER}} .lsd-template-element-title:hover' => 'background-color:{{VALUE}};',
                '{{WRAPPER}} .lsdtb-card-title:hover' => 'background-color:{{VALUE}};',
            ],
        ]);

        $this->add_control('border_hover',
        [
            'type'        => 'border',
            'label'       => esc_html__('Hover Border', 'listdom'),
            'description' => '',
            'responsive'  => true,
            'selectors'   => [
                '{{WRAPPER}} .lsd-template-element-title:hover' => '{{VALUE}}',
                '{{WRAPPER}} .lsdtb-card-title:hover' => '{{VALUE}}',
            ],
        ]);

        $this->add_control('padding_hover',
        [
            'type'        => 'padding',
            'label'       => esc_html__('Hover Padding', 'listdom'),
            'description' => '',
            'responsive'  => true,
            'selectors'   => [
                '{{WRAPPER}} .lsd-template-element-title:hover' => '{{VALUE}}',
                '{{WRAPPER}} .lsdtb-card-title:hover' => '{{VALUE}}',
            ],
        ]);

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->end_controls_section();
    }

    /**
     * Render title exactly like old LSD_Element_Title::get()
     *
     * @param array $args
     * @return string
     */
    public function render(array $args = []): string
    {
        // Prefer listing_id from builder
        $listing_id = isset($args['listing_id']) ? (int) $args['listing_id'] : 0;

        if (!$listing_id)
        {
            global $post;
            if ($post instanceof WP_Post) {
                $listing_id = (int) $post->ID;
            }
        }

        if (!$listing_id) return '';

        $settings = isset($args['settings']) && is_array($args['settings']) ? $args['settings'] : [];
        $content_settings = isset($settings['content']) && is_array($settings['content']) ? $settings['content'] : [];

        $tag = isset($content_settings['title_tag']) ? (string) $content_settings['title_tag'] : 'h2';
        if (!in_array($tag, ['h1', 'h2', 'h3', 'h4', 'p', 'strong'], true)) $tag = 'h2';

        $method = isset($content_settings['link_method']) ? (string) $content_settings['link_method'] : 'normal';
        if (!isset(LSD_Base::get_listing_link_methods()[$method])) $method = 'normal';

        $listing = new LSD_Entity_Listing($listing_id);
        $title_html = $listing->get_title_tag($method);
        if (trim($title_html) === '') return '';

        $html  = '<div class="lsd-template-element-title lsdtb-card-title">';
        $html .= '<' . $tag . '>' . $title_html . '</' . $tag . '>';
        $html .= '</div>';

        return $this->content(
            $html,
            $this,
            [
                'post_id'       => $listing_id,
                'context'       => $args['context'] ?? '',
                'template_type' => $args['template_type'] ?? '',
            ]
        );

    }
}
