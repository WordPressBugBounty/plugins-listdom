<?php

class LSD_Template_Elements_Address extends LSD_Template
{
    public function __construct($settings = [])
    {
        $this->key            = 'address';
        $this->label          = $this->label();
        $this->category       = 'location';
        $this->template_types = ['single_listing', 'listing_card', 'info_window'];
        $this->icon           = self::icons($this->key);

        parent::__construct($settings);
    }

    protected function label()
    {
        return esc_html__('Listing Address', 'listdom');
    }

    public function controls(): void
    {
        $this->register_content_controls();
        $this->register_style_controls();
    }

    private function register_content_controls(): void
    {
        $this->start_controls_section('content_' . $this->key,
            [
                'label' => esc_html__('Options', 'listdom'),
                'tab'   => self::TAB_CONTENT,
            ]);

        $this->add_control('show_icon',
            [
                'type'    => 'switcher',
                'label'   => esc_html__('Show Icon', 'listdom'),
                'default' => 1,
            ]);

        $this->add_control('alignment',
            [
                'type'       => 'select',
                'class'      => 'lsd-admin-input',
                'label'      => esc_html__('Alignment', 'listdom'),
                'default'    => 'flex-start',
                'responsive' => true,
                'options'    => [
                    'flex-start' => esc_html__('Left', 'listdom'),
                    'center'     => esc_html__('Center', 'listdom'),
                    'flex-end'   => esc_html__('Right', 'listdom'),
                ],
                'selectors'  => [
                    '{{WRAPPER}} .lsdtb-card-address' => 'display:flex; flex-direction:column; align-items:{{VALUE}};',
                ],
            ]);

        $this->end_controls_section();
    }

    private function register_style_controls(): void
    {
        $this->start_controls_section('style_' . $this->key,
            [
                'label'      => esc_html__('Style', 'listdom'),
                'responsive' => true,
                'tab'        => self::TAB_STYLE,
            ]);

        $this->start_controls_tabs('address_style_tabs');

        $this->start_controls_tab('address_normal_tab',
            [
                'label' => esc_html__('Normal', 'listdom'),
            ]);

        $this->add_control('typography',
            [
                'type'       => 'typography',
                'label'      => esc_html__('Typography', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsdtb-card-address' => '{{VALUE}}',
                    '{{WRAPPER}} .lsdtb-card-address a' => '{{VALUE}}',
                ],
            ]);

        $this->add_control('text_color',
            [
                'type'       => 'colorpicker',
                'label'      => esc_html__('Text Color', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsdtb-card-address' => 'color:{{VALUE}};',
                    '{{WRAPPER}} .lsdtb-card-address a' => 'color:{{VALUE}};',
                ],
            ]);

        $this->add_control('icon_color',
            [
                'type'       => 'colorpicker',
                'label'      => esc_html__('Icon Color', 'listdom'),
                'responsive' => true,
                'condition'  => [
                    'show_icon' => true,
                ],
                'selectors'  => [
                    '{{WRAPPER}} .lsdtb-card-address i' => 'color:{{VALUE}};',
                ],
            ]);

        $this->add_control('background',
            [
                'type'       => 'colorpicker',
                'label'      => esc_html__('Background', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsdtb-card-address' => 'background-color:{{VALUE}};',
                ],
            ]);

        $this->add_control('border',
            [
                'type'            => 'border',
                'label'           => esc_html__('Border', 'listdom'),
                'responsive'      => true,
                'radius_fallback' => 'border_radius',
                'selectors'       => [
                    '{{WRAPPER}} .lsdtb-card-address' => '{{VALUE}}',
                ],
            ]);

        $this->add_control('padding',
            [
                'type'       => 'padding',
                'label'      => esc_html__('Padding', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsdtb-card-address' => '{{VALUE}}',
                ],
            ]);

        $this->end_controls_tab();

        $this->start_controls_tab('address_hover_tab',
            [
                'label' => esc_html__('Hover', 'listdom'),
            ]);

        $this->add_control('link_hover_color',
            [
                'type'       => 'colorpicker',
                'label'      => esc_html__('Link Hover Color', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsdtb-card-address a:hover' => 'color:{{VALUE}};',
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

        $listing = new LSD_Entity_Listing($listing_id);
        $output = $listing->get_address($this->show_icon($content_settings));

        if (trim((string) $output) === '') return '';

        $html = '<div class="lsd-template-element-address lsdtb-card-address">' . $output . '</div>';

        return apply_filters('lsd_template_element_address_output', $html, $this, $args);
    }

    private function show_icon(array $content_settings): bool
    {
        if (!array_key_exists('show_icon', $content_settings)) return true;

        return !empty($content_settings['show_icon']);
    }
}
