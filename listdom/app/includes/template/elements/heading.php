<?php

class LSD_Template_Elements_Heading extends LSD_Template
{
    public function __construct($settings = [])
    {
        $this->key            = 'heading';
        $this->label          = $this->label();
        $this->category       = 'general';
        $this->template_types = ['single_listing', 'listing_card', 'info_window'];
        $this->icon           = self::icons($this->key);

        parent::__construct($settings);
    }

    protected function label()
    {
        return esc_html__('Heading', 'listdom');
    }

    public function controls(): void
    {
        $this->start_controls_section('content_' . $this->key, [
            'label' => esc_html__('Options', 'listdom'),
            'tab'   => self::TAB_CONTENT,
        ]);

        $this->add_control('text', [
            'type'    => 'text',
            'class'   => 'lsd-admin-input',
            'label'   => esc_html__('Text', 'listdom'),
            'default' => esc_html__('Heading', 'listdom'),
        ]);

        $this->add_control('tag', [
            'type'    => 'select',
            'class'   => 'lsd-admin-input',
            'label'   => esc_html__('Tag', 'listdom'),
            'default' => 'h3',
            'options' => [
                'h1'  => esc_html__('Heading 1', 'listdom'),
                'h2'  => esc_html__('Heading 2', 'listdom'),
                'h3'  => esc_html__('Heading 3', 'listdom'),
                'h4'  => esc_html__('Heading 4', 'listdom'),
                'h5'  => esc_html__('Heading 5', 'listdom'),
                'h6'  => esc_html__('Heading 6', 'listdom'),
                'p'   => esc_html__('Paragraph', 'listdom'),
                'div' => esc_html__('Div', 'listdom'),
            ],
        ]);

        $this->end_controls_section();

        $this->start_controls_section('style_' . $this->key, [
            'label'      => esc_html__('Style', 'listdom'),
            'responsive' => true,
            'tab'        => self::TAB_STYLE,
        ]);

        $this->add_control('text_color', [
            'type'       => 'colorpicker',
            'label'      => esc_html__('Color', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsd-template-element-heading, {{WRAPPER}} .lsd-template-element-heading > *' => 'color:{{VALUE}};',
            ],
        ]);

        $this->add_control('background_color', [
            'type'       => 'colorpicker',
            'label'      => esc_html__('Background', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsd-template-element-heading' => 'background-color:{{VALUE}};',
            ],
        ]);

        $this->add_control('border', [
            'type'       => 'border',
            'label'      => esc_html__('Border', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsd-template-element-heading' => '{{VALUE}}',
            ],
        ]);

        $this->add_control('padding', [
            'type'       => 'padding',
            'label'      => esc_html__('Padding', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsd-template-element-heading' => '{{VALUE}}',
            ],
        ]);

        $this->add_control('typography', [
            'type'       => 'typography',
            'label'      => esc_html__('Typography', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsd-template-element-heading, {{WRAPPER}} .lsd-template-element-heading > *' => '{{VALUE}}',
            ],
        ]);

        $this->end_controls_section();
    }

    public function render(array $args = []): string
    {
        $settings = isset($args['settings']) && is_array($args['settings']) ? $args['settings'] : [];
        $content_settings = isset($settings['content']) && is_array($settings['content']) ? $settings['content'] : [];

        $text = isset($content_settings['text']) ? trim((string) $content_settings['text']) : '';
        if ($text === '') $text = esc_html__('Heading', 'listdom');

        $tag = isset($content_settings['tag']) ? sanitize_key($content_settings['tag']) : 'h3';
        if (!in_array($tag, ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'div'], true)) $tag = 'h3';

        return '<div class="lsd-template-element-heading lsdtb-card-heading"><' . $tag . '>' . esc_html($text) . '</' . $tag . '></div>';
    }
}
