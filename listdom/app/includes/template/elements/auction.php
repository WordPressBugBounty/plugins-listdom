<?php

class LSD_Template_Elements_Auction extends LSD_Template
{
    public function __construct($settings = [])
    {
        $this->key            = 'auction';
        $this->label          = $this->label();
        $this->category       = 'commerce';
        $this->template_types = ['single_listing', 'listing_card', 'info_window'];
        $this->icon           = self::icons($this->key);

        parent::__construct($settings);
    }

    protected function label()
    {
        return esc_html__('Auction Addon', 'listdom');
    }

    public function controls(): void
    {
        $this->start_controls_section('style_' . $this->key,
            [
                'label'      => esc_html__('Style', 'listdom'),
                'responsive' => true,
                'tab'        => self::TAB_STYLE,
            ]);

        $this->start_controls_tabs('auction_style_tabs');

        $this->start_controls_tab('auction_normal_tab',
            [
                'label' => esc_html__('Normal', 'listdom'),
            ]);

        $this->add_control('typography',
            [
                'type'       => 'typography',
                'label'      => esc_html__('Typography', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsd-template-element-auction' => '{{VALUE}}',
                    '{{WRAPPER}} .lsdtb-card-auction' => '{{VALUE}}',
                    '{{WRAPPER}} .lsd-template-element-auction a' => '{{VALUE}}',
                    '{{WRAPPER}} .lsdtb-card-auction a' => '{{VALUE}}',
                ],
            ]);

        $this->add_control('text_color',
            [
                'type'       => 'colorpicker',
                'label'      => esc_html__('Text Color', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsd-template-element-auction' => 'color:{{VALUE}};',
                    '{{WRAPPER}} .lsdtb-card-auction' => 'color:{{VALUE}};',
                    '{{WRAPPER}} .lsd-template-element-auction a' => 'color:{{VALUE}};',
                    '{{WRAPPER}} .lsdtb-card-auction a' => 'color:{{VALUE}};',
                ],
            ]);

        $this->add_control('background',
            [
                'type'       => 'colorpicker',
                'label'      => esc_html__('Background', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsd-template-element-auction' => 'background-color:{{VALUE}};',
                    '{{WRAPPER}} .lsdtb-card-auction' => 'background-color:{{VALUE}};',
                ],
            ]);

        $this->add_control('border',
            [
                'type'            => 'border',
                'label'           => esc_html__('Border', 'listdom'),
                'responsive'      => true,
                'radius_fallback' => 'border_radius',
                'selectors'       => [
                    '{{WRAPPER}} .lsd-template-element-auction' => '{{VALUE}}',
                    '{{WRAPPER}} .lsdtb-card-auction' => '{{VALUE}}',
                ],
            ]);

        $this->add_control('padding',
        [
            'type'       => 'padding',
            'label'      => esc_html__('Padding', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsd-template-element-auction' => '{{VALUE}}',
                '{{WRAPPER}} .lsdtb-card-auction' => '{{VALUE}}',
            ],
        ]);

        $this->end_controls_tab();

        $this->start_controls_tab('auction_hover_tab',
            [
                'label' => esc_html__('Hover', 'listdom'),
            ]);

        $this->add_control('hover_text_color',
            [
                'type'       => 'colorpicker',
                'label'      => esc_html__('Text Color', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsd-template-element-auction:hover' => 'color:{{VALUE}};',
                    '{{WRAPPER}} .lsdtb-card-auction:hover' => 'color:{{VALUE}};',
                    '{{WRAPPER}} .lsd-template-element-auction:hover a' => 'color:{{VALUE}};',
                    '{{WRAPPER}} .lsdtb-card-auction:hover a' => 'color:{{VALUE}};',
                ],
            ]);

        $this->add_control('hover_background',
            [
                'type'       => 'colorpicker',
                'label'      => esc_html__('Background', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsd-template-element-auction:hover' => 'background-color:{{VALUE}};',
                    '{{WRAPPER}} .lsdtb-card-auction:hover' => 'background-color:{{VALUE}};',
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

        $output = $this->get_auction_output($listing_id);

        if (trim((string) $output) === '') return '';

        $html = '<div class="lsd-template-element-auction lsdtb-card-auction">' . $output . '</div>';

        return apply_filters('lsd_template_element_auction_output', $html, $this, $args);
    }

    private function get_auction_output(int $listing_id): string
    {
        if (!class_exists(\LSDPACAUC\Base::class))
        {
            return LSD_Main::alert(esc_html__('Auction addon should be installed and activated!', 'listdom'), 'warning');
        }

        $element = new \LSDPACAUC\Element();

        return (string) $element->get($listing_id);
    }
}
