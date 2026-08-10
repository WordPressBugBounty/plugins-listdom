<?php

class LSD_Template_Elements_Google_places extends LSD_Template
{
    public function __construct($settings = [])
    {
        $this->key            = 'google_places';
        $this->label          = $this->label();
        $this->category       = 'location';
        $this->template_types = ['single_listing', 'listing_card', 'info_window'];
        $this->icon           = self::icons($this->key);

        parent::__construct($settings);
    }

    protected function label()
    {
        return esc_html__('Google Places', 'listdom-google-places');
    }

    public function controls(): void
    {
        $this->register_content_controls();
        $this->register_box_controls();
        $this->register_rating_controls();
        $this->register_button_controls();
    }

    private function register_content_controls(): void
    {
        $this->start_controls_section('content_' . $this->key,
            [
                'label' => esc_html__('Options', 'listdom-google-places'),
                'tab'   => self::TAB_CONTENT,
            ]);

        $this->add_control('display_google_maps',
            [
                'type'    => 'switcher',
                'label'   => esc_html__('View on Google Button', 'listdom-google-places'),
                'default' => 1,
            ]);

        $this->add_control('display_rate_reviews',
            [
                'type'    => 'switcher',
                'label'   => esc_html__('Google Rating & Reviews', 'listdom-google-places'),
                'default' => 1,
            ]);

        $this->end_controls_section();
    }

    private function register_box_controls(): void
    {
        $this->start_controls_section('style_' . $this->key,
            [
                'label'      => esc_html__('Style', 'listdom-google-places'),
                'responsive' => true,
                'tab'        => self::TAB_STYLE,
            ]);

        $this->add_control('alignment',
            [
                'type'       => 'select',
                'class'      => 'lsd-admin-input',
                'label'      => esc_html__('Alignment', 'listdom-google-places'),
                'default'    => 'left',
                'responsive' => true,
                'options'    => [
                    'left'   => esc_html__('Left', 'listdom-google-places'),
                    'center' => esc_html__('Center', 'listdom-google-places'),
                    'right'  => esc_html__('Right', 'listdom-google-places'),
                ],
                'selectors'  => [
                    '{{WRAPPER}} .lsdtb-card-google-places' => 'text-align:{{VALUE}};',
                ],
            ]);

        $this->start_controls_tabs('google_places_style_tabs');

        $this->start_controls_tab('google_places_normal_tab',
            [
                'label' => esc_html__('Normal', 'listdom-google-places'),
            ]);

        $this->add_control('typography',
            [
                'type'       => 'typography',
                'label'      => esc_html__('Typography', 'listdom-google-places'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsdtb-card-google-places' => '{{VALUE}}',
                    '{{WRAPPER}} .lsdtb-card-google-places a' => '{{VALUE}}',
                ],
            ]);

        $this->add_control('text_color',
            [
                'type'       => 'colorpicker',
                'label'      => esc_html__('Text Color', 'listdom-google-places'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsdtb-card-google-places' => 'color:{{VALUE}};',
                    '{{WRAPPER}} .lsdtb-card-google-places a' => 'color:{{VALUE}};',
                ],
            ]);

        $this->add_control('background',
            [
                'type'       => 'colorpicker',
                'label'      => esc_html__('Background', 'listdom-google-places'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsdtb-card-google-places' => 'background-color:{{VALUE}};',
                ],
            ]);

        $this->add_control('border',
            [
                'type'            => 'border',
                'label'           => esc_html__('Border', 'listdom-google-places'),
                'responsive'      => true,
                'radius_fallback' => 'border_radius',
                'selectors'       => [
                    '{{WRAPPER}} .lsdtb-card-google-places' => '{{VALUE}}',
                ],
            ]);

        $this->add_control('padding',
            [
                'type'       => 'padding',
                'label'      => esc_html__('Padding', 'listdom-google-places'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsdtb-card-google-places' => '{{VALUE}}',
                ],
            ]);

        $this->end_controls_tab();

        $this->start_controls_tab('google_places_hover_tab',
            [
                'label' => esc_html__('Hover', 'listdom-google-places'),
            ]);

        $this->add_control('link_hover_color',
            [
                'type'       => 'colorpicker',
                'label'      => esc_html__('Link Hover Color', 'listdom-google-places'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsdtb-card-google-places a:hover' => 'color:{{VALUE}};',
                ],
            ]);

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->end_controls_section();
    }

    private function register_rating_controls(): void
    {
        $this->start_controls_section('rating_' . $this->key,
            [
                'label'      => esc_html__('Rating Block', 'listdom-google-places'),
                'responsive' => true,
                'tab'        => self::TAB_STYLE,
                'condition'  => [
                    'display_rate_reviews' => true,
                ],
            ]);

        $this->add_control('rating_icon_color',
            [
                'type'       => 'colorpicker',
                'label'      => esc_html__('Star Color', 'listdom-google-places'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsdaddgpl-gp-rate-review .lsd-fe-icon' => 'color:{{VALUE}};',
                ],
            ]);

        $this->add_control('rating_gap',
            [
                'type'       => 'number',
                'class'      => 'lsd-admin-input',
                'label'      => esc_html__('Items Gap', 'listdom-google-places'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsdaddgpl-gp-rate-review' => 'gap:{{VALUE}}px;',
                ],
            ]);

        $this->end_controls_section();
    }

    private function register_button_controls(): void
    {
        $this->start_controls_section('button_' . $this->key,
            [
                'label'      => esc_html__('Button', 'listdom-google-places'),
                'responsive' => true,
                'tab'        => self::TAB_STYLE,
                'condition'  => [
                    'display_google_maps' => true,
                ],
            ]);

        $this->start_controls_tabs('google_places_button_tabs');

        $this->start_controls_tab('google_places_button_normal_tab',
            [
                'label' => esc_html__('Normal', 'listdom-google-places'),
            ]);

        $this->add_control('button_text_color',
            [
                'type'       => 'colorpicker',
                'label'      => esc_html__('Text Color', 'listdom-google-places'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsdaddgpl-google-places-card .lsd-general-button' => 'color:{{VALUE}};',
                ],
            ]);

        $this->add_control('button_background',
            [
                'type'       => 'colorpicker',
                'label'      => esc_html__('Background', 'listdom-google-places'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsdaddgpl-google-places-card .lsd-general-button' => 'background-color:{{VALUE}};',
                ],
            ]);

        $this->add_control('button_border',
            [
                'type'            => 'border',
                'label'           => esc_html__('Border', 'listdom-google-places'),
                'responsive'      => true,
                'radius_fallback' => 'button_border_radius',
                'selectors'       => [
                    '{{WRAPPER}} .lsdaddgpl-google-places-card .lsd-general-button' => '{{VALUE}}',
                ],
            ]);

        $this->end_controls_tab();

        $this->start_controls_tab('google_places_button_hover_tab',
            [
                'label' => esc_html__('Hover', 'listdom-google-places'),
            ]);

        $this->add_control('button_hover_text_color',
            [
                'type'       => 'colorpicker',
                'label'      => esc_html__('Text Color', 'listdom-google-places'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsdaddgpl-google-places-card .lsd-general-button:hover' => 'color:{{VALUE}};',
                ],
            ]);

        $this->add_control('button_hover_background',
            [
                'type'       => 'colorpicker',
                'label'      => esc_html__('Background', 'listdom-google-places'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsdaddgpl-google-places-card .lsd-general-button:hover' => 'background-color:{{VALUE}};',
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

        $output = $this->get_google_places_output($listing_id, $args);

        if (trim((string) $output) === '') return '';

        $html = '<div class="lsd-template-element-google-places lsdtb-card-google-places">' . $output . '</div>';

        return apply_filters('lsd_template_element_google_places_output', $html, $this, $args);
    }

    private function get_google_places_output(int $listing_id, array $args = []): string
    {
        if (!class_exists(\LSDPACGPL\Base::class) || !class_exists(\LSDPACGPL\GooglePlaces::class))
        {
            return LSD_Main::alert(esc_html__('Google Places addon should be installed and activated!', 'listdom-google-places'), 'warning');
        }

        $settings = isset($args['settings']) && is_array($args['settings']) ? $args['settings'] : [];
        $content_settings = isset($settings['content']) && is_array($settings['content']) ? $settings['content'] : [];

        $element = new \LSDPACGPL\GooglePlaces();

        return (string) $element->get($listing_id, $this->get_google_places_options($content_settings));
    }

    private function get_google_places_options(array $content_settings): array
    {
        return [
            'display_google_maps'  => $this->enabled($content_settings, 'display_google_maps', true),
            'display_rate_reviews' => $this->enabled($content_settings, 'display_rate_reviews', true),
        ];
    }
}
