<?php

class LSD_Template_Elements_Ads extends LSD_Template
{
    public function __construct($settings = [])
    {
        $this->key            = 'ads';
        $this->label          = $this->label();
        $this->category       = 'commerce';
        $this->template_types = ['single_listing', 'listing_card', 'info_window'];
        $this->icon           = self::icons($this->key);

        parent::__construct($settings);
    }

    protected function label()
    {
        return esc_html__('Ads', 'listdom');
    }

    public function controls(): void
    {
        $this->start_controls_section('style_' . $this->key,
            [
                'label'      => esc_html__('Style', 'listdom'),
                'responsive' => true,
                'tab'        => self::TAB_STYLE,
            ]);

        $this->start_controls_tabs('ads_style_tabs');

        $this->start_controls_tab('ads_normal_tab',
            [
                'label' => esc_html__('Normal', 'listdom'),
            ]);

        $this->add_control('typography',
            [
                'type'       => 'typography',
                'label'      => esc_html__('Typography', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsd-template-element-ads' => '{{VALUE}}',
                    '{{WRAPPER}} .lsdtb-card-ads' => '{{VALUE}}',
                ],
            ]);

        $this->add_control('text_color',
            [
                'type'       => 'colorpicker',
                'label'      => esc_html__('Text Color', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsd-template-element-ads' => 'color:{{VALUE}};',
                    '{{WRAPPER}} .lsdtb-card-ads' => 'color:{{VALUE}};',
                    '{{WRAPPER}} .lsd-template-element-ads a' => 'color:{{VALUE}};',
                    '{{WRAPPER}} .lsdtb-card-ads a' => 'color:{{VALUE}};',
                ],
            ]);

        $this->add_control('background',
            [
                'type'       => 'colorpicker',
                'label'      => esc_html__('Background', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsd-template-element-ads' => 'background-color:{{VALUE}};',
                    '{{WRAPPER}} .lsdtb-card-ads' => 'background-color:{{VALUE}};',
                ],
            ]);

        $this->add_control('border',
            [
                'type'            => 'border',
                'label'           => esc_html__('Border', 'listdom'),
                'responsive'      => true,
                'radius_fallback' => 'border_radius',
                'selectors'       => [
                    '{{WRAPPER}} .lsd-template-element-ads' => '{{VALUE}}',
                    '{{WRAPPER}} .lsdtb-card-ads' => '{{VALUE}}',
                ],
            ]);

        $this->add_control('padding',
        [
            'type'       => 'padding',
            'label'      => esc_html__('Padding', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsd-template-element-ads' => '{{VALUE}}',
                '{{WRAPPER}} .lsdtb-card-ads' => '{{VALUE}}',
            ],
        ]);

        $this->end_controls_tab();

        $this->start_controls_tab('ads_hover_tab',
            [
                'label' => esc_html__('Hover', 'listdom'),
            ]);

        $this->add_control('hover_text_color',
            [
                'type'       => 'colorpicker',
                'label'      => esc_html__('Text Color', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsd-template-element-ads:hover' => 'color:{{VALUE}};',
                    '{{WRAPPER}} .lsdtb-card-ads:hover' => 'color:{{VALUE}};',
                    '{{WRAPPER}} .lsd-template-element-ads:hover a' => 'color:{{VALUE}};',
                    '{{WRAPPER}} .lsdtb-card-ads:hover a' => 'color:{{VALUE}};',
                ],
            ]);

        $this->add_control('hover_background',
            [
                'type'       => 'colorpicker',
                'label'      => esc_html__('Background', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsd-template-element-ads:hover' => 'background-color:{{VALUE}};',
                    '{{WRAPPER}} .lsdtb-card-ads:hover' => 'background-color:{{VALUE}};',
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

        $output = $this->get_ads_output($listing_id);

        if (trim((string) $output) === '') return '';

        $html = '<div class="lsd-template-element-ads lsdtb-card-ads">' . $output . '</div>';

        return apply_filters('lsd_template_element_ads_output', $html, $this, $args);
    }

    private function get_ads_output(int $listing_id): string
    {
        if (!class_exists(\LSDPACADS\Base::class)) return LSD_Main::alert(esc_html__('Ads addon should be installed and activated!', 'listdom'), 'warning');

        $element = new \LSDPACADS\Element();

        return (string) $element->get($listing_id);
    }
}
