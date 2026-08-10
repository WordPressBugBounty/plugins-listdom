<?php

class LSD_Template_Elements_Map extends LSD_Template
{
    public function __construct($settings = [])
    {
        $this->key            = 'map';
        $this->label          = $this->label();
        $this->category       = 'location';
        $this->template_types = ['single_listing', 'listing_card', 'info_window'];
        $this->icon           = self::icons($this->key);

        parent::__construct($settings);
    }

    protected function label()
    {
        return esc_html__('Listing Map', 'listdom');
    }

    public function controls(): void
    {
        $this->register_content_controls();
        $this->register_google_controls();
        $this->register_style_controls();
    }

    private function register_content_controls(): void
    {
        $this->start_controls_section('content_' . $this->key,
            [
                'label' => esc_html__('Options', 'listdom'),
                'tab'   => self::TAB_CONTENT,
            ]);

        $this->add_control('provider',
            [
                'type'    => 'select',
                'class'   => 'lsd-admin-input',
                'label'   => esc_html__('Map Provider', 'listdom'),
                'default' => LSD_Map_Provider::def(),
                'options' => LSD_Map_Provider::get_providers(),
            ]);

        $this->add_control('infowindow',
            [
                'type'    => 'select',
                'class'   => 'lsd-admin-input',
                'label'   => esc_html__('Infowindow', 'listdom'),
                'default' => '0',
                'options' => [
                    '0' => esc_html__('Disabled', 'listdom'),
                    '1' => esc_html__('Enabled', 'listdom'),
                ],
            ]);

        $this->add_control('zoomlevel',
            [
                'type'    => 'select',
                'class'   => 'lsd-admin-input',
                'label'   => esc_html__('Zoom Level', 'listdom'),
                'default' => '14',
                'options' => $this->zoom_options(),
            ]);

        $this->add_control('map_height',
            [
                'type'        => 'unit_number',
                'label'       => esc_html__('Map Height', 'listdom'),
                'default'     => [
                    'value' => 400,
                    'unit'  => 'px',
                ],
                'units'       => ['px', 'vh'],
                'attributes'  => [
                    'number' => [
                        'min'  => 0,
                        'step' => 1,
                    ],
                ],
            ]);

        $this->end_controls_section();
    }

    private function register_google_controls(): void
    {
        $this->start_controls_section('google_' . $this->key,
            [
                'label'     => esc_html__('Google Map Settings', 'listdom'),
                'tab'       => self::TAB_CONTENT,
                'condition' => [
                    'provider' => 'googlemap',
                ],
            ]);

        $this->add_control('style',
            [
                'type'    => 'select',
                'class'   => 'lsd-admin-input',
                'label'   => esc_html__('Style', 'listdom'),
                'default' => '',
                'options' => LSD_Base::get_map_styles(),
            ]);

        $this->add_control('gplaces',
            [
                'type'    => 'select',
                'class'   => 'lsd-admin-input',
                'label'   => esc_html__('Google Places', 'listdom'),
                'default' => '0',
                'options' => $this->enabled_disabled_options(),
            ]);

        $map_control_options = $this->get_map_control_options();

        $this->add_control('control_zoom',
            [
                'type'    => 'select',
                'class'   => 'lsd-admin-input',
                'label'   => esc_html__('Zoom Control', 'listdom'),
                'default' => 'RIGHT_BOTTOM',
                'options' => $map_control_options,
            ]);

        $this->add_control('control_maptype',
            [
                'type'    => 'select',
                'class'   => 'lsd-admin-input',
                'label'   => esc_html__('Map Type Control', 'listdom'),
                'default' => 'TOP_LEFT',
                'options' => $map_control_options,
            ]);

        $this->add_control('control_streetview',
            [
                'type'    => 'select',
                'class'   => 'lsd-admin-input',
                'label'   => esc_html__('Street View Control', 'listdom'),
                'default' => 'RIGHT_BOTTOM',
                'options' => $map_control_options,
            ]);

        $this->add_control('control_scale',
            [
                'type'    => 'select',
                'class'   => 'lsd-admin-input',
                'label'   => esc_html__('Scale Control', 'listdom'),
                'default' => '0',
                'options' => $this->enabled_disabled_options(),
            ]);

        $this->add_control('control_camera',
            [
                'type'    => 'select',
                'class'   => 'lsd-admin-input',
                'label'   => esc_html__('Camera Control', 'listdom'),
                'default' => '0',
                'options' => $this->enabled_disabled_options(),
            ]);

        $this->add_control('control_fullscreen',
            [
                'type'    => 'select',
                'class'   => 'lsd-admin-input',
                'label'   => esc_html__('Fullscreen Control', 'listdom'),
                'default' => '1',
                'options' => $this->enabled_disabled_options(),
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

        $this->add_control('background',
            [
                'type'       => 'colorpicker',
                'label'      => esc_html__('Background', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsdtb-card-map' => 'background-color:{{VALUE}};',
                ],
            ]);

        $this->add_control('border',
            [
                'type'            => 'border',
                'label'           => esc_html__('Border', 'listdom'),
                'responsive'      => true,
                'radius_fallback' => 'border_radius',
                'selectors'       => [
                    '{{WRAPPER}} .lsdtb-card-map' => '{{VALUE}}',
                ],
            ]);

        $this->add_control('padding',
            [
                'type'       => 'padding',
                'label'      => esc_html__('Padding', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsdtb-card-map' => '{{VALUE}}',
                ],
            ]);

        $this->add_control('overflow',
            [
                'type'    => 'select',
                'class'   => 'lsd-admin-input',
                'label'   => esc_html__('Overflow', 'listdom'),
                'default' => 'hidden',
                'options' => [
                    'hidden'  => esc_html__('Hidden', 'listdom'),
                    'visible' => esc_html__('Visible', 'listdom'),
                ],
                'selectors'  => [
                    '{{WRAPPER}} .lsdtb-card-map' => 'overflow:{{VALUE}};',
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
        $map = $listing->get_map($this->map_options($content_settings));

        if (trim((string) $map) === '') return '';

        LSD_Assets::map();

        $html = '<div class="lsd-template-element-map lsdtb-card-map">' . $map . '</div>';

        return apply_filters('lsd_template_element_map_output', $html, $this, $args);
    }

    private function map_options(array $content_settings): array
    {
        return [
            'provider'    => $content_settings['provider'] ?? LSD_Map_Provider::def(),
            'style'       => $this->map_style($content_settings),
            'gplaces'     => $content_settings['gplaces'] ?? '0',
            'infowindow'  => $content_settings['infowindow'] ?? '0',
            'zoomlevel'   => $content_settings['zoomlevel'] ?? '14',
            'map_height'  => $this->map_height($content_settings),
            'mapcontrols' => $this->map_controls($content_settings),
        ];
    }

    private function map_controls(array $content_settings): array
    {
        $mapcontrols = LSD_Options::defaults('mapcontrols');

        $mapcontrols['zoom']       = $content_settings['control_zoom'] ?? ($mapcontrols['zoom'] ?? 'RIGHT_BOTTOM');
        $mapcontrols['maptype']    = $content_settings['control_maptype'] ?? ($mapcontrols['maptype'] ?? 'TOP_LEFT');
        $mapcontrols['streetview'] = $content_settings['control_streetview'] ?? ($mapcontrols['streetview'] ?? 'RIGHT_BOTTOM');
        $mapcontrols['scale']      = $content_settings['control_scale'] ?? ($mapcontrols['scale'] ?? '0');
        $mapcontrols['camera']     = $content_settings['control_camera'] ?? ($mapcontrols['camera'] ?? '0');
        $mapcontrols['fullscreen'] = $content_settings['control_fullscreen'] ?? ($mapcontrols['fullscreen'] ?? '1');

        $mapcontrols['draw'] = '0';
        $mapcontrols['gps']  = '0';

        return $mapcontrols;
    }

    private function map_height(array $content_settings): string
    {
        if (!isset($content_settings['map_height']) || !is_array($content_settings['map_height']))
        {
            return '';
        }

        $value = $content_settings['map_height']['value'] ?? '';
        $unit = $content_settings['map_height']['unit'] ?? 'px';

        $value = is_numeric($value) ? (float) $value : '';
        $unit = in_array($unit, ['px', 'vh'], true) ? $unit : 'px';

        return $value !== '' ? $value . $unit : '';
    }

    private function map_style(array $content_settings)
    {
        $style = isset($content_settings['style']) ? trim((string) $content_settings['style']) : '';

        return $style !== '' ? $style : null;
    }

    private function zoom_options(): array
    {
        $options = [];

        for ($zoom = 4; $zoom <= 16; $zoom++)
        {
            $options[(string) $zoom] = $zoom;
        }

        return $options;
    }

    private function enabled_disabled_options(): array
    {
        return [
            '0' => esc_html__('Disabled', 'listdom'),
            '1' => esc_html__('Enabled', 'listdom'),
        ];
    }

    private function get_map_control_options(): array
    {
        $base = new LSD_Base();
        $positions = $base->get_map_control_positions();

        return array_merge(
            ['0' => esc_html__('Disabled', 'listdom')],
            $positions
        );
    }
}
