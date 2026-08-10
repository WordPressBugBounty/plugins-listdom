<?php

class LSD_Template_Elements_Contact extends LSD_Template
{
    public function __construct($settings = [])
    {
        $this->key            = 'contact';
        $this->label          = $this->label();
        $this->category       = 'contact_owner';
        $this->template_types = ['single_listing', 'listing_card', 'info_window'];
        $this->icon           = self::icons($this->key);

        parent::__construct($settings);
    }

    protected function label()
    {
        return esc_html__('Contact Info', 'listdom');
    }

    public function controls(): void
    {
        $this->start_controls_section('content_' . $this->key,
            [
                'label' => esc_html__('Options', 'listdom'),
                'tab'   => self::TAB_CONTENT,
            ]);

        $this->add_control('style_type',
            [
                'type'    => 'select',
                'class'   => 'lsd-admin-input',
                'label'   => esc_html__('Style Type', 'listdom'),
                'default' => 'default',
                'options' => [
                    'default' => esc_html__('Default', 'listdom'),
                    'custom'  => esc_html__('Custom', 'listdom'),
                ],
            ]);

        $this->add_control('show_email',
            [
                'type'      => 'switcher',
                'label'     => esc_html__('Show Email', 'listdom'),
                'default'   => 1,
                'condition' => [
                    'style_type' => 'default',
                ],
            ]);

        $this->add_control('show_phone',
            [
                'type'      => 'switcher',
                'label'     => esc_html__('Show Phone', 'listdom'),
                'default'   => 1,
                'condition' => [
                    'style_type' => 'default',
                ],
            ]);

        $this->add_control('show_website',
            [
                'type'      => 'switcher',
                'label'     => esc_html__('Show Website', 'listdom'),
                'default'   => 1,
                'condition' => [
                    'style_type' => 'default',
                ],
            ]);

        $this->add_control('show_address',
            [
                'type'      => 'switcher',
                'label'     => esc_html__('Show Address', 'listdom'),
                'default'   => 1,
                'condition' => [
                    'style_type' => 'default',
                ],
            ]);

        if (LSD_Components::socials())
        {
            $this->add_control('show_socials',
                [
                    'type'      => 'switcher',
                    'label'     => esc_html__('Show Socials', 'listdom'),
                    'default'   => 1,
                    'condition' => [
                        'style_type' => 'default',
                    ],
                ]);
        }

        $this->add_control('display_icon',
            [
                'type'      => 'switcher',
                'label'     => esc_html__('Display Icon', 'listdom'),
                'default'   => 1,
                'condition' => [
                    'style_type' => 'default',
                ],
            ]);

        $this->add_control('display_label',
            [
                'type'      => 'switcher',
                'label'     => esc_html__('Display Label', 'listdom'),
                'default'   => 0,
                'condition' => [
                    'style_type' => 'default',
                ],
            ]);

        $this->add_control('fields',
            [
                'type'        => 'repeater',
                'label'       => esc_html__('Fields', 'listdom'),
                'description' => esc_html__('Select your desired fields to display', 'listdom'),
                'default'     => $this->get_default_repeater_items(),
                'condition'   => [
                    'style_type' => 'custom',
                ],
                'fields'      => [
                    [
                        'id'      => 'field_id',
                        'type'    => 'select',
                        'class'   => 'lsd-admin-input',
                        'label'   => esc_html__('Field', 'listdom'),
                        'options' => $this->get_contact_field_options(),
                    ],
                    [
                        'id'      => 'field_icon',
                        'type'    => 'iconpicker',
                        'class'   => 'lsd-admin-input',
                        'label'   => esc_html__('Icon', 'listdom'),
                        'condition' => [
                            'field_id' => array_keys($this->get_contact_field_options()),
                        ],
                    ],
                    [
                        'id'      => 'field_display_name',
                        'type'    => 'switcher',
                        'label'   => esc_html__('Name', 'listdom'),
                        'default' => 1,
                    ],
                    [
                        'id'        => 'custom_display_name',
                        'type'      => 'text',
                        'class'     => 'lsd-admin-input',
                        'label'     => esc_html__('Custom Name', 'listdom'),
                        'default'   => '',
                        'condition' => [
                            'field_display_name' => true,
                        ],
                    ],
                ],
            ]);

        $this->end_controls_section();

        $this->start_controls_section('box_' . $this->key,
            [
                'label'      => esc_html__('Box', 'listdom'),
                'responsive' => true,
                'tab'        => self::TAB_STYLE,
            ]);

        $this->add_control('layout',
            [
                'type'    => 'select',
                'class'   => 'lsd-admin-input',
                'label'   => esc_html__('Layout', 'listdom'),
                'default' => 'column',
                'options' => [
                    'column' => esc_html__('Default', 'listdom'),
                    'row'    => esc_html__('Inline', 'listdom'),
                ],
                'selectors' => [
                    '{{WRAPPER}} .lsdtb-card-contact .lsd-contact-info ul' => 'flex-direction:{{VALUE}};',
                ],
            ]);

        if (LSD_Components::socials())
        {
            $this->add_control('social_layout',
                [
                    'type'    => 'select',
                    'class'   => 'lsd-admin-input',
                    'label'   => esc_html__('Social Icons Layout', 'listdom'),
                    'default' => 'column',
                    'options' => [
                        'column' => esc_html__('Default', 'listdom'),
                        'row'    => esc_html__('Inline', 'listdom'),
                    ],
                    'selectors' => [
                        '{{WRAPPER}} .lsdtb-card-contact .lsd-listing-social-networks ul' => 'flex-direction:{{VALUE}};',
                    ],
                ]);
        }

        $this->add_control('gap',
            [
                'type'       => 'number',
                'class'      => 'lsd-admin-input',
                'label'      => esc_html__('Gap', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsdtb-card-contact .lsd-contact-info ul' => 'gap:{{VALUE}}px;',
                    '{{WRAPPER}} .lsdtb-card-contact .lsd-listing-social-networks ul' => 'gap:{{VALUE}}px;',
                ],
            ]);

        $this->add_control('background',
            [
                'type'       => 'colorpicker',
                'label'      => esc_html__('Background', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsdtb-card-contact' => 'background-color:{{VALUE}};',
                ],
            ]);

        $this->add_control('padding',
            [
                'type'       => 'padding',
                'label'      => esc_html__('Padding', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsdtb-card-contact' => '{{VALUE}}',
                ],
            ]);

        $this->add_control('border',
            [
                'type'            => 'border',
                'label'           => esc_html__('Border', 'listdom'),
                'responsive'      => true,
                'radius_fallback' => 'border_radius',
                'selectors'       => [
                    '{{WRAPPER}} .lsdtb-card-contact' => '{{VALUE}}',
                ],
            ]);

        $this->end_controls_section();

        $this->start_controls_section('items_' . $this->key,
            [
                'label'      => esc_html__('Items', 'listdom'),
                'responsive' => true,
                'tab'        => self::TAB_STYLE,
            ]);

        $this->start_controls_tabs('contact_items_style_tabs');

        $this->start_controls_tab('contact_items_normal_tab',
            [
                'label' => esc_html__('Normal', 'listdom'),
            ]);

        $this->add_control('contact_typography',
            [
                'type'       => 'typography',
                'label'      => esc_html__('Typography', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsdtb-card-contact ul li a, {{WRAPPER}} .lsdtb-card-contact ul li span' => '{{VALUE}}',
                ],
            ]);

        $this->add_control('name_color',
            [
                'type'       => 'colorpicker',
                'label'      => esc_html__('Name Color', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsdtb-card-contact .lsd-contact-name' => 'color:{{VALUE}};',
                ],
            ]);

        $this->add_control('value_color',
            [
                'type'       => 'colorpicker',
                'label'      => esc_html__('Value Color', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsdtb-card-contact .lsd-contact-value, {{WRAPPER}} .lsdtb-card-contact .lsd-contact-value a' => 'color:{{VALUE}};',
                ],
            ]);

        $this->add_control('name_typography',
            [
                'type'       => 'typography',
                'label'      => esc_html__('Name Typography', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsdtb-card-contact .lsd-contact-name' => '{{VALUE}}',
                ],
            ]);

        $this->add_control('value_typography',
            [
                'type'       => 'typography',
                'label'      => esc_html__('Value Typography', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsdtb-card-contact .lsd-contact-value, {{WRAPPER}} .lsdtb-card-contact .lsd-contact-value a' => '{{VALUE}}',
                ],
            ]);

        $this->end_controls_tab();

        $this->start_controls_tab('contact_items_hover_tab',
            [
                'label' => esc_html__('Hover', 'listdom'),
            ]);

        $this->add_control('value_hover_color',
            [
                'type'       => 'colorpicker',
                'label'      => esc_html__('Value Hover Color', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsdtb-card-contact .lsd-contact-value a:hover' => 'color:{{VALUE}};',
                ],
            ]);

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->end_controls_section();

        $this->start_controls_section('icons_' . $this->key,
            [
                'label'      => esc_html__('Icons', 'listdom'),
                'responsive' => true,
                'tab'        => self::TAB_STYLE,
            ]);

        $this->start_controls_tabs('contact_icons_style_tabs');

        $this->start_controls_tab('contact_icons_normal_tab',
            [
                'label' => esc_html__('Normal', 'listdom'),
            ]);

        $this->add_control('icon_size',
            [
                'type'       => 'number',
                'class'      => 'lsd-admin-input',
                'label'      => esc_html__('Icon Size', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsdtb-card-contact i' => 'font-size:{{VALUE}}px;',
                ],
            ]);

        $this->add_control('icon_color',
            [
                'type'       => 'colorpicker',
                'label'      => esc_html__('Icon Color', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsdtb-card-contact i' => 'color:{{VALUE}};',
                ],
            ]);

        $this->end_controls_tab();

        $this->start_controls_tab('contact_icons_hover_tab',
            [
                'label' => esc_html__('Hover', 'listdom'),
            ]);

        $this->add_control('icon_hover_color',
            [
                'type'       => 'colorpicker',
                'label'      => esc_html__('Icon Hover Color', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsdtb-card-contact li:hover i' => 'color:{{VALUE}};',
                    '{{WRAPPER}} .lsdtb-card-contact a:hover i' => 'color:{{VALUE}};',
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

        $style_type = $content_settings['style_type'] ?? 'default';

        $listing = new LSD_Entity_Listing($listing_id);

        if ($style_type === 'custom')
        {
            return $this->render_custom_contact($listing_id, $content_settings);
        }

        return $this->render_default_contact($listing, $content_settings);
    }

    private function render_default_contact(LSD_Entity_Listing $listing, array $content_settings): string
    {
        $output = $listing->get_contact_info([
            'show_email'    => isset($content_settings['show_email']) ? (int) $content_settings['show_email'] : 1,
            'show_phone'    => isset($content_settings['show_phone']) ? (int) $content_settings['show_phone'] : 1,
            'show_website'  => isset($content_settings['show_website']) ? (int) $content_settings['show_website'] : 1,
            'show_address'  => isset($content_settings['show_address']) ? (int) $content_settings['show_address'] : 1,
            'show_socials'  => LSD_Components::socials() ? (int) ($content_settings['show_socials'] ?? 1) : 0,
            'display_icon'  => isset($content_settings['display_icon']) ? (int) $content_settings['display_icon'] : 1,
            'display_label' => isset($content_settings['display_label']) ? (int) $content_settings['display_label'] : 0,
        ]);

        if (trim((string) $output) === '') return '';

        $html = '<div class="lsd-template-element-contact lsdtb-card-contact lsd-no-bullets">' . $output . '</div>';

        return apply_filters('lsd_template_element_contact_output', $html, $this, [
            'style_type' => 'default',
        ]);
    }

    private function render_custom_contact(int $listing_id, array $content_settings): string
    {
        $fields = isset($content_settings['fields']) && is_array($content_settings['fields']) ? $content_settings['fields'] : [];
        if (empty($fields)) return '';

        $items = '';

        foreach ($fields as $field)
        {
            $items .= $this->render_custom_contact_item($listing_id, $field);
        }

        if (trim($items) === '') return '';

        $html = '<div class="lsd-template-element-contact lsdtb-card-contact lsd-no-bullets">'
            . '<div class="lsd-contact-info"><ul>'
            . $items
            . '</ul></div></div>';

        return apply_filters('lsd_template_element_contact_output', $html, $this, [
            'style_type' => 'custom',
        ]);
    }

    private function render_custom_contact_item(int $listing_id, array $field): string
    {
        $field_id = isset($field['field_id']) ? sanitize_key((string) $field['field_id']) : '';
        if ($field_id === '') return '';

        $value = get_post_meta($listing_id, 'lsd_' . $field_id, true);
        if (trim((string) $value) === '') return '';

        $icon = $this->get_repeater_icon($field);
        $name = $this->get_repeater_field_name($field_id, $field);
        $display_name = !empty($field['field_display_name']);

        $html = '<li>';

        if ($icon !== '')
        {
            $html .= '<i class="' . esc_attr($icon) . '"></i> ';
        }

        if ($display_name)
        {
            $html .= '<span class="lsd-contact-name">' . esc_html($name) . ': </span>';
        }

        $html .= '<span class="lsd-contact-value">' . $this->format_contact_value($field_id, $value) . '</span>';
        $html .= '</li>';

        return $html;
    }

    private function get_repeater_icon(array $field): string
    {
        $icon = $field['field_icon'] ?? '';

        if (is_array($icon) && isset($icon['value']))
        {
            $icon = $icon['value'];
        }

        return trim((string) $icon);
    }

    private function get_repeater_field_name(string $field_id, array $field): string
    {
        $custom_display_name = isset($field['custom_display_name']) ? trim((string) $field['custom_display_name']) : '';

        if ($custom_display_name !== '')
        {
            return $custom_display_name;
        }

        return ucfirst(str_replace('_', ' ', $field_id));
    }

    private function format_contact_value(string $field_id, $value): string
    {
        $value = trim((string) $value);

        if (LSD_Base::is_url($value))
        {
            return '<a href="' . esc_url($value) . '" target="_blank" rel="noopener noreferrer">' . esc_html(LSD_Base::remove_protocols($value)) . '</a>';
        }

        if (is_email($value))
        {
            return '<a href="mailto:' . esc_attr($value) . '">' . esc_html($value) . '</a>';
        }

        if ($field_id === 'phone')
        {
            return '<a href="tel:' . esc_attr($this->normalize_phone($value)) . '">' . esc_html($value) . '</a>';
        }

        if ($field_id === 'whatsapp')
        {
            $tel = $this->normalize_phone($value);

            return '<a href="https://wa.me/' . esc_attr($tel) . '" target="_blank" rel="noopener noreferrer">' . esc_html($value) . '</a>';
        }

        return esc_html($value);
    }

    private function normalize_phone(string $value): string
    {
        return str_replace([' ', '-', '(', ')', '+'], '', $value);
    }

    private function get_contact_fields(): array
    {
        return [
            'email' => [
                'value' => '',
                'icon'  => 'fas fa-envelope',
            ],
            'phone' => [
                'value' => '',
                'icon'  => 'fas fa-phone',
            ],
            'website' => [
                'value' => '',
                'icon'  => 'fas fa-globe',
            ],
            'contact_address' => [
                'value' => '',
                'icon'  => 'fas fa-map-marker-alt',
            ],
        ];
    }

    private function get_contact_field_options(): array
    {
        $options = [];

        foreach ($this->get_all_contact_fields() as $key => $data)
        {
            $options[$key] = ucfirst(str_replace('_', ' ', $key));
        }

        return $options;
    }

    private function get_default_repeater_items(): array
    {
        $items = [];

        foreach ($this->get_all_contact_fields() as $key => $data)
        {
            $items[] = [
                'field_id'            => $key,
                'field_icon'          => $data['icon'] ?? '',
                'field_display_name'  => '0',
                'custom_display_name' => '',
            ];
        }

        return $items;
    }

    private function get_all_contact_fields(): array
    {
        $fields = $this->get_contact_fields();

        if (LSD_Components::socials())
        {
            foreach (LSD_Options::socials() as $key => $value)
            {
                $fields[$key] = [
                    'value' => $value,
                    'icon'  => $this->get_share_icon($key),
                ];
            }
        }

        return $fields;
    }

    private function get_share_icon($key): string
    {
        $icons = [
            'facebook'  => 'fab fa-facebook',
            'twitter'   => 'fa fa-times',
            'instagram' => 'fab fa-instagram',
            'linkedin'  => 'fab fa-linkedin',
            'pinterest' => 'fab fa-pinterest',
            'whatsapp'  => 'fab fa-whatsapp',
            'youtube'   => 'fab fa-youtube',
            'tiktok'    => 'fab fa-tiktok',
            'telegram'  => 'fab fa-telegram',
        ];

        return $icons[$key] ?? 'fas fa-share-alt';
    }
}
