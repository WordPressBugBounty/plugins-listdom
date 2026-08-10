<?php

class LSD_Template_Elements_Remark extends LSD_Template
{
    public function __construct($settings = [])
    {
        $this->key            = 'remark';
        $this->label          = $this->label();
        $this->category       = 'advanced';
        $this->template_types = ['single_listing', 'listing_card', 'info_window'];
        $this->icon           = self::icons($this->key);

        parent::__construct($settings);
    }

    protected function label()
    {
        return esc_html__('Listing Remark', 'listdom');
    }

    public function controls(): void
    {
        $this->start_controls_section('content_' . $this->key,
        [
            'label' => esc_html__('Options', 'listdom'),
            'tab'   => self::TAB_CONTENT,
        ]);

        $this->add_control('auto_p',
        [
            'type'    => 'switcher',
            'label'   => esc_html__('Auto Paragraph', 'listdom'),
            'default' => 0,
        ]);

        $this->end_controls_section();

        $this->start_controls_section('style_' . $this->key,
        [
            'label'      => esc_html__('Style', 'listdom'),
            'responsive' => true,
            'tab'        => self::TAB_STYLE,
        ]);

        $this->add_control('typography',
        [
            'type'       => 'typography',
            'label'      => esc_html__('Typography', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsd-template-element-remark' => '{{VALUE}}',
                '{{WRAPPER}} .lsd-template-element-remark p' => '{{VALUE}}',
                '{{WRAPPER}} .lsdtb-card-remark' => '{{VALUE}}',
                '{{WRAPPER}} .lsdtb-card-remark p' => '{{VALUE}}',
            ],
        ]);

        $this->add_control('text_color',
        [
            'type'       => 'colorpicker',
            'label'      => esc_html__('Text Color', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsd-template-element-remark' => 'color:{{VALUE}};',
                '{{WRAPPER}} .lsd-template-element-remark p' => 'color:{{VALUE}};',
                '{{WRAPPER}} .lsdtb-card-remark' => 'color:{{VALUE}};',
                '{{WRAPPER}} .lsdtb-card-remark p' => 'color:{{VALUE}};',
            ],
        ]);

        $this->add_control('background_color',
        [
            'type'       => 'colorpicker',
            'label'      => esc_html__('Background Color', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsd-template-element-remark' => 'background-color:{{VALUE}};',
                '{{WRAPPER}} .lsdtb-card-remark' => 'background-color:{{VALUE}};',
            ],
        ]);

        $this->add_control('border',
        [
            'type'       => 'border',
            'label'      => esc_html__('Border', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsd-template-element-remark' => '{{VALUE}}',
                '{{WRAPPER}} .lsdtb-card-remark' => '{{VALUE}}',
            ],
        ]);

        $this->add_control('padding',
        [
            'type'       => 'padding',
            'label'      => esc_html__('Padding', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsd-template-element-remark' => '{{VALUE}}',
                '{{WRAPPER}} .lsdtb-card-remark' => '{{VALUE}}',
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
        $output = $listing->get_remark();
        if (trim($output) === '') return '';

        $auto_p = !empty($content_settings['auto_p']);
        if ($auto_p && strpos($output, '<p') === false) $output = wpautop($output);

        return '<div class="lsd-template-element-remark lsdtb-card-remark">' . $output . '</div>';
    }
}
