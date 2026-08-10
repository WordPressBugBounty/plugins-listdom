<?php

class LSD_Template_Elements_Locations extends LSD_Template
{
    public function __construct($settings = [])
    {
        $this->key            = 'locations';
        $this->label          = $this->label();
        $this->category       = 'taxonomies';
        $this->template_types = ['single_listing', 'listing_card', 'info_window'];
        $this->icon           = self::icons($this->key);

        parent::__construct($settings);
    }

    protected function label()
    {
        return esc_html__('Listing Locations', 'listdom');
    }

    public function controls(): void
    {
        $this->start_controls_section('content_' . $this->key,
        [
            'label' => esc_html__('Options', 'listdom'),
            'tab'   => self::TAB_CONTENT,
        ]);

        $this->add_control('enable_location_link',
        [
            'type'    => 'switcher',
            'label'   => esc_html__('Link to Location Archive', 'listdom'),
            'default' => 1,
        ]);

        $this->add_control('icon',
        [
            'type'  => 'iconpicker',
            'label' => esc_html__('Icon', 'listdom'),
        ]);

        $this->add_control('layout',
        [
            'type'    => 'select',
            'class' => 'lsd-admin-input',
            'label'   => esc_html__('Layout', 'listdom'),
            'default' => 'row',
            'options' => [
                'row'    => esc_html__('Inline', 'listdom'),
                'column' => esc_html__('Block', 'listdom'),
            ],
            'selectors' => [
                '{{WRAPPER}} .lsdtb-card-locations .lsd-locations-list' => 'flex-direction:{{VALUE}};',
            ],
        ]);

        $this->end_controls_section();

        $this->start_controls_section('style_' . $this->key,
        [
            'label'      => esc_html__('General', 'listdom'),
            'responsive' => true,
            'tab'        => self::TAB_STYLE,
        ]);

        $this->add_control('locations_typography',
        [
            'type'       => 'typography',
            'label'      => esc_html__('Typography', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsdtb-card-locations li, {{WRAPPER}} .lsdtb-card-locations a' => '{{VALUE}}',
            ],
        ]);

        $this->add_control('text_color',
        [
            'type'       => 'colorpicker',
            'label'      => esc_html__('Text Color', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsdtb-card-locations li, {{WRAPPER}} .lsdtb-card-locations a' => 'color:{{VALUE}};',
            ],
        ]);

        $this->add_control('icon_color',
        [
            'type'       => 'colorpicker',
            'label'      => esc_html__('Icon Color', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsdtb-card-locations .lsd-fe-icon' => 'color:{{VALUE}};',
                '{{WRAPPER}} .lsdtb-card-locations svg' => 'color:{{VALUE}};',
            ],
        ]);

        $this->add_control('padding',
        [
            'type'       => 'padding',
            'label'      => esc_html__('Padding', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsdtb-card-locations' => '{{VALUE}}',
            ],
        ]);

        $this->add_control('gap',
        [
            'type'       => 'number',
            'class'      => 'lsd-admin-input',
            'label'      => esc_html__('Gap Between Items', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsdtb-card-locations .lsd-locations-list' => 'gap:{{VALUE}}px;',
            ],
        ]);

        $this->end_controls_section();

        $this->start_controls_section('items_' . $this->key,
        [
            'label'      => esc_html__('Item', 'listdom'),
            'responsive' => true,
            'tab'        => self::TAB_STYLE,
        ]);

        $this->add_control('item_background_color',
        [
            'type'       => 'colorpicker',
            'label'      => esc_html__('Background Color', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsdtb-card-locations .lsd-locations-list-item' => 'background-color:{{VALUE}};',
            ],
        ]);

        $this->add_control('item_border',
        [
            'type'       => 'border',
            'label'      => esc_html__('Border', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsdtb-card-locations .lsd-locations-list-item' => '{{VALUE}}',
            ],
        ]);

        $this->add_control('items_padding',
        [
            'type'       => 'padding',
            'label'      => esc_html__('Padding', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsdtb-card-locations .lsd-locations-list-item' => '{{VALUE}}',
            ],
        ]);

        $this->add_control('item_gap',
        [
            'type'       => 'number',
            'class'      => 'lsd-admin-input',
            'label'      => esc_html__('Item Gap', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsdtb-card-locations .lsd-locations-list li' => 'gap:{{VALUE}}px;',
                '{{WRAPPER}} .lsdtb-card-locations .lsd-locations-list li a' => 'gap:{{VALUE}}px;',
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

        $locations = wp_get_post_terms($listing_id, LSD_Base::TAX_LOCATION);
        if (!is_array($locations) || !count($locations)) return '';

        $icon_class = $content_settings['icon'] ?? '';
        if (is_array($icon_class) && isset($icon_class['value'])) $icon_class = $icon_class['value'];
        $enable_link = !empty($content_settings['enable_location_link']);

        $items = [];
        foreach ($locations as $location)
        {
            if (!$location instanceof WP_Term) continue;

            $icon = '';
            if (trim($icon_class) !== '')
            {
                $icon = '<i class="lsd-fe-icon ' . esc_attr($icon_class) . '"></i>';
            }

            if ($enable_link)
            {
                $link = '<a href="' . esc_url(get_term_link($location->term_id, LSD_Base::TAX_LOCATION)) . '" ' . lsd_schema()->name() . '>' . $icon . esc_html($location->name) . '</a>';
            }
            else
            {
                $link = $icon . esc_html($location->name);
            }

            $items[] = '<li class="lsd-locations-list-item" ' . lsd_schema()->scope()->type('https://schema.org/Place')->prop('areaServed') . '>' . $link . '</li>';
        }

        if (!$items) return '';

        $output = '<div class="lsd-template-element-locations lsdtb-card-locations">';
        $output .= '<ul class="lsd-locations-list">' . implode('', $items) . '</ul>';
        $output .= '</div>';

        return $output;
    }
}
