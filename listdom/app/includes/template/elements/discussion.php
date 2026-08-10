<?php

class LSD_Template_Elements_Discussion extends LSD_Template
{
    public function __construct($settings = [])
    {
        $this->key            = 'discussion';
        $this->label          = $this->label();
        $this->category       = 'advanced';
        $this->template_types = ['single_listing', 'listing_card', 'info_window'];
        $this->icon           = self::icons($this->key);

        parent::__construct($settings);
    }

    protected function label()
    {
        return esc_html__('Listing Review Form', 'listdom');
    }

    public function controls(): void
    {
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
                '{{WRAPPER}} .lsd-template-element-discussion' => '{{VALUE}}',
                '{{WRAPPER}} .lsdtb-card-discussion' => '{{VALUE}}',
            ],
        ]);

        $this->add_control('text_color',
        [
            'type'       => 'colorpicker',
            'label'      => esc_html__('Text Color', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsd-template-element-discussion' => 'color:{{VALUE}};',
                '{{WRAPPER}} .lsdtb-card-discussion' => 'color:{{VALUE}};',
            ],
        ]);

        $this->add_control('background',
        [
            'type'       => 'colorpicker',
            'label'      => esc_html__('Background', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsd-template-element-discussion' => 'background-color:{{VALUE}};',
                '{{WRAPPER}} .lsdtb-card-discussion' => 'background-color:{{VALUE}};',
            ],
        ]);

        $this->add_control('border',
        [
            'type'       => 'border',
            'label'      => esc_html__('Border', 'listdom'),
            'responsive' => true,
            'radius_fallback' => 'border_radius',
            'selectors'  => [
                '{{WRAPPER}} .lsd-template-element-discussion' => '{{VALUE}}',
                '{{WRAPPER}} .lsdtb-card-discussion' => '{{VALUE}}',
            ],
        ]);

        $this->add_control('padding',
        [
            'type'       => 'padding',
            'label'      => esc_html__('Padding', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsd-template-element-discussion' => '{{VALUE}}',
                '{{WRAPPER}} .lsdtb-card-discussion' => '{{VALUE}}',
            ],
        ]);

        $this->end_controls_section();
    }

    public function render(array $args = []): string
    {
        $listing_id = isset($args['listing_id']) ? (int) $args['listing_id'] : 0;
        if (!$listing_id) return '';

        if (!class_exists(\LSDPACREV\Base::class))
        {
            return LSD_Main::alert(esc_html__('Review addon should be installed and activated!', 'listdom'), 'warning');
        }

        $element = new \LSDPACREV\Element();
        $output = $element->get($listing_id);
        $element->disable();

        if (trim((string) $output) === '') return '';

        $html = '<div class="lsd-template-element-discussion lsdtb-card-discussion">' . $output . '</div>';

        return apply_filters('lsd_template_element_discussion_output', $html, $this, $args);
    }
}
