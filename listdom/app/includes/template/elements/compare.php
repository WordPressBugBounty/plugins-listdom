<?php

class LSD_Template_Elements_Compare extends LSD_Template
{
    public function __construct($settings = [])
    {
        $this->key            = 'compare';
        $this->label          = $this->label();
        $this->category       = 'advanced';
        $this->template_types = ['single_listing', 'listing_card', 'info_window'];
        $this->icon           = self::icons($this->key);

        parent::__construct($settings);
    }

    protected function label()
    {
        return esc_html__('Compare Button', 'listdom');
    }

    public function controls(): void
    {
        $this->start_controls_section('style_' . $this->key, [
            'label'      => esc_html__('Style', 'listdom'),
            'responsive' => true,
            'tab'        => self::TAB_STYLE,
        ]);

        $this->add_control('text_align', [
            'type'    => 'select',
            'class'   => 'lsd-admin-input',
            'label'   => esc_html__('Alignment', 'listdom'),
            'default' => 'left',
            'options' => [
                'left'   => esc_html__('Left', 'listdom'),
                'center' => esc_html__('Center', 'listdom'),
                'right'  => esc_html__('Right', 'listdom'),
            ],
            'selectors' => [
                '{{WRAPPER}} .lsd-template-element-compare' => 'text-align:{{VALUE}};',
                '{{WRAPPER}} .lsdtb-card-compare' => 'text-align:{{VALUE}};',
            ],
        ]);

        $this->add_control('padding', [
            'type'       => 'padding',
            'label'      => esc_html__('Padding', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsd-template-element-compare .lsd-compare > .lsd-compare-toggle, {{WRAPPER}} .lsd-template-element-compare .lsd-compare > a' => '{{VALUE}}',
            ],
        ]);

        $this->start_controls_tabs('compare_state_style_tabs_' . $this->key);

        $this->start_controls_tab('compare_inactive_state_tab_' . $this->key, [
            'label' => esc_html__('Inactive', 'listdom'),
        ]);

        $this->add_control('inactive_color', [
            'type'       => 'colorpicker',
            'label'      => esc_html__('Color', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsd-template-element-compare .lsd-compare > .lsd-compare-toggle.lsd-compare-off i, {{WRAPPER}} .lsd-template-element-compare .lsd-compare > .lsd-compare-toggle.lsd-compare-off i span, {{WRAPPER}} .lsd-template-element-compare .lsd-compare > a, {{WRAPPER}} .lsd-template-element-compare .lsd-compare > a i, {{WRAPPER}} .lsd-template-element-compare .lsd-compare > a span' => 'color:{{VALUE}};',
                '{{WRAPPER}} .lsd-template-element-compare .lsd-compare > .lsd-compare-toggle.lsd-compare-off i::before, {{WRAPPER}} .lsd-template-element-compare .lsd-compare > a i::before' => 'color:{{VALUE}} !important;',
            ],
        ]);

        $this->add_control('inactive_background_color', [
            'type'       => 'colorpicker',
            'label'      => esc_html__('Background Color', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsd-template-element-compare .lsd-compare > .lsd-compare-toggle.lsd-compare-off, {{WRAPPER}} .lsd-template-element-compare .lsd-compare > a' => 'background-color:{{VALUE}};',
            ],
        ]);

        $this->add_control('inactive_border', [
            'type'            => 'border',
            'label'           => esc_html__('Border', 'listdom'),
            'responsive'      => true,
            'radius_fallback' => 'inactive_border_radius',
            'selectors'       => [
                '{{WRAPPER}} .lsd-template-element-compare .lsd-compare > .lsd-compare-toggle.lsd-compare-off, {{WRAPPER}} .lsd-template-element-compare .lsd-compare > a' => '{{VALUE}}',
            ],
        ]);

        $this->end_controls_tab();

        $this->start_controls_tab('compare_active_state_tab_' . $this->key, [
            'label' => esc_html__('Active', 'listdom'),
        ]);

        $this->add_control('active_color', [
            'type'       => 'colorpicker',
            'label'      => esc_html__('Color', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsd-template-element-compare .lsd-compare > .lsd-compare-toggle.lsd-compare-on i, {{WRAPPER}} .lsd-template-element-compare .lsd-compare > .lsd-compare-toggle.lsd-compare-on i span' => 'color:{{VALUE}};',
                '{{WRAPPER}} .lsd-template-element-compare .lsd-compare > .lsd-compare-toggle.lsd-compare-on i::before' => 'color:{{VALUE}} !important;',
            ],
        ]);

        $this->add_control('active_background_color', [
            'type'       => 'colorpicker',
            'label'      => esc_html__('Background Color', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsd-template-element-compare .lsd-compare > .lsd-compare-toggle.lsd-compare-on' => 'background-color:{{VALUE}};',
            ],
        ]);

        $this->add_control('active_border', [
            'type'            => 'border',
            'label'           => esc_html__('Border', 'listdom'),
            'responsive'      => true,
            'radius_fallback' => 'active_border_radius',
            'selectors'       => [
                '{{WRAPPER}} .lsd-template-element-compare .lsd-compare > .lsd-compare-toggle.lsd-compare-on' => '{{VALUE}}',
            ],
        ]);


        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->end_controls_section();
    }

    public function render(array $args = []): string
    {
        $listing_id = isset($args['listing_id']) ? (int) $args['listing_id'] : 0;

        if (!$listing_id)
        {
            global $post;
            if ($post instanceof WP_Post) $listing_id = (int) $post->ID;
        }

        if (!$listing_id) return '';
        if (!class_exists(\LSDPACCMP\Base::class)) return LSD_Main::alert(esc_html__('Compare addon should be installed and activated!', 'listdom'), 'warning');

        $listing = new LSD_Entity_Listing($listing_id);
        $output = LSD_Kses::element($listing->get_compare_button());
        if (trim((string) $output) === '') return '';

        $html = '<div class="lsd-template-element-compare lsdtb-card-compare">' . $output . '</div>';

        return apply_filters('lsd_template_element_compare_output', $html, $this, $args);
    }
}
