<?php

class LSD_Template_Elements_Owner extends LSD_Template
{
    public function __construct($settings = [])
    {
        $this->key            = 'owner';
        $this->label          = $this->label();
        $this->category       = 'contact_owner';
        $this->template_types = ['single_listing', 'listing_card', 'info_window'];
        $this->icon           = self::icons($this->key);

        parent::__construct($settings);
    }

    protected function label()
    {
        return esc_html__('Listing Owner', 'listdom');
    }

    public function controls(): void
    {
        $this->register_content_controls();
        $this->register_box_controls();
        $this->register_avatar_controls();
        $this->register_identity_controls();
        $this->register_contact_info_controls();
        $this->register_form_controls();
        $this->register_icon_controls();
    }

    private function register_content_controls(): void
    {
        $default_privacy_consent = LSD_Privacy::is_consent_enabled('contact') ? 1 : 0;

        $this->start_controls_section('content_' . $this->key,
            [
                'label' => esc_html__('Owner Details', 'listdom'),
                'tab'   => self::TAB_CONTENT,
            ]);

        $this->add_control('show_avatar',
            [
                'type'    => 'switcher',
                'label'   => esc_html__('Show Avatar', 'listdom'),
                'default' => 1,
            ]);

        $this->add_control('link_avatar',
            [
                'type'      => 'switcher',
                'label'     => esc_html__('Link Avatar to Author URL', 'listdom'),
                'default'   => 0,
                'condition' => [
                    'show_avatar' => true,
                ],
            ]);

        $this->add_control('show_name',
            [
                'type'    => 'switcher',
                'label'   => esc_html__('Show Name', 'listdom'),
                'default' => 1,
            ]);

        $this->add_control('author_link',
            [
                'type'      => 'switcher',
                'label'     => esc_html__('Link Name to Author URL', 'listdom'),
                'default'   => 0,
                'condition' => [
                    'show_name' => true,
                ],
            ]);

        $this->add_control('show_job_title',
            [
                'type'    => 'switcher',
                'label'   => esc_html__('Show Job Title', 'listdom'),
                'default' => 1,
            ]);

        $this->add_control('show_bio',
            [
                'type'    => 'switcher',
                'label'   => esc_html__('Show Bio', 'listdom'),
                'default' => 1,
            ]);

        if (LSD_Components::socials())
        {
            $this->add_control('show_socials',
                [
                    'type'      => 'switcher',
                    'label'     => esc_html__('Show Social Networks', 'listdom'),
                    'default'   => 1,
                    'condition' => [
                        'show_name' => true,
                    ],
                ]);
        }

        $this->add_control('show_tel',
            [
                'type'    => 'switcher',
                'label'   => esc_html__('Show Telephone', 'listdom'),
                'default' => 1,
            ]);

        $this->add_control('show_email',
            [
                'type'    => 'switcher',
                'label'   => esc_html__('Show Email', 'listdom'),
                'default' => 1,
            ]);

        $this->add_control('show_mobile',
            [
                'type'    => 'switcher',
                'label'   => esc_html__('Show Mobile', 'listdom'),
                'default' => 1,
            ]);

        $this->add_control('show_website',
            [
                'type'    => 'switcher',
                'label'   => esc_html__('Show Website', 'listdom'),
                'default' => 1,
            ]);

        $this->add_control('show_fax',
            [
                'type'    => 'switcher',
                'label'   => esc_html__('Show Fax', 'listdom'),
                'default' => 1,
            ]);

        $this->add_control('show_form',
            [
                'type'    => 'switcher',
                'label'   => esc_html__('Show Form', 'listdom'),
                'default' => 1,
            ]);

        $this->add_control('name_field',
            [
                'type'      => 'switcher',
                'label'     => esc_html__('Show Name Field', 'listdom'),
                'default'   => 1,
                'condition' => [
                    'show_form' => true,
                ],
            ]);

        $this->add_control('phone_field',
            [
                'type'      => 'switcher',
                'label'     => esc_html__('Show Phone Field', 'listdom'),
                'default'   => 1,
                'condition' => [
                    'show_form' => true,
                ],
            ]);

        $this->add_control('pc_enabled',
            [
                'type'      => 'switcher',
                'label'     => esc_html__('Privacy Consent', 'listdom'),
                'default'   => $default_privacy_consent,
                'condition' => [
                    'show_form' => true,
                ],
            ]);

        $this->add_control('pc_label',
            [
                'type'        => 'text',
                'class'       => 'lsd-admin-input',
                'label'       => esc_html__('Consent Label', 'listdom'),
                'description' => esc_html__('Leave empty to use the default label. Use {{privacy_policy}} to automatically include the default privacy policy page link.', 'listdom'),
                'default'     => '',
                'condition'   => [
                    'show_form'  => true,
                    'pc_enabled' => true,
                ],
            ]);

        $this->end_controls_section();
    }

    private function register_box_controls(): void
    {
        $this->start_controls_section('box_' . $this->key,
            [
                'label'      => esc_html__('Box & Layout', 'listdom'),
                'responsive' => true,
                'tab'        => self::TAB_STYLE,
            ]);

        $this->start_controls_tabs('owner_box_layout_tabs');

        $this->start_controls_tab('owner_box_tab',
            [
                'label' => esc_html__('Box', 'listdom'),
            ]);

        $this->add_control('box_background',
            [
                'type'       => 'colorpicker',
                'label'      => esc_html__('Background', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsdtb-card-owner' => 'background-color:{{VALUE}};',
                ],
            ]);

        $this->add_control('box_border',
            [
                'type'            => 'border',
                'label'           => esc_html__('Border', 'listdom'),
                'responsive'      => true,
                'radius_fallback' => 'border_radius',
                'selectors'       => [
                    '{{WRAPPER}} .lsdtb-card-owner' => '{{VALUE}}',
                ],
            ]);

        $this->add_control('box_padding',
            [
                'type'       => 'padding',
                'label'      => esc_html__('Padding', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsdtb-card-owner' => '{{VALUE}}',
                ],
            ]);

        $this->end_controls_tab();

        $this->start_controls_tab('owner_layout_tab',
            [
                'label' => esc_html__('Layout', 'listdom'),
            ]);

        $this->add_control('layout_items_alignment',
            [
                'type'       => 'select',
                'class'      => 'lsd-admin-input',
                'label'      => esc_html__('Content Alignment', 'listdom'),
                'responsive' => true,
                'options'    => [
                    'start'  => esc_html__('Start', 'listdom'),
                    'center' => esc_html__('Center', 'listdom'),
                    'end'    => esc_html__('End', 'listdom'),
                ],
                'selectors'  => [
                    '{{WRAPPER}} .lsd-owner-information' => 'align-items:{{VALUE}};',
                    '{{WRAPPER}} .lsd-owner-first-part' => 'align-items:{{VALUE}};',
                    '{{WRAPPER}} .lsd-owner-information-part-1' => 'align-items:{{VALUE}};',
                    '{{WRAPPER}} .lsd-owner-information-part-2' => 'align-items:{{VALUE}};',
                ],
            ]);

        $this->add_control('layout_items_gap',
            [
                'type'       => 'number',
                'class'      => 'lsd-admin-input',
                'label'      => esc_html__('Content Gap', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsd-owner-information' => 'gap:{{VALUE}}px;',
                    '{{WRAPPER}} .lsd-owner-first-part' => 'gap:{{VALUE}}px;',
                    '{{WRAPPER}} .lsd-owner-information-part-1' => 'gap:{{VALUE}}px;',
                    '{{WRAPPER}} .lsd-owner-information-part-2' => 'gap:{{VALUE}}px;',
                ],
            ]);

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->end_controls_section();
    }

    private function register_avatar_controls(): void
    {
        $this->start_controls_section('image_' . $this->key,
            [
                'label'      => esc_html__('Avatar', 'listdom'),
                'responsive' => true,
                'tab'        => self::TAB_STYLE,
                'condition'  => [
                    'show_avatar' => true,
                ],
            ]);

        $this->add_control('image_width',
            [
                'type'       => 'number',
                'class'      => 'lsd-admin-input',
                'label'      => esc_html__('Avatar Width', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsdtb-card-owner .lsd-owner-image-wrapper img' => 'width:{{VALUE}}px;',
                    '{{WRAPPER}} .lsdtb-card-owner img.avatar' => 'width:{{VALUE}}px;',
                ],
            ]);

        $this->add_control('image_height',
            [
                'type'       => 'number',
                'class'      => 'lsd-admin-input',
                'label'      => esc_html__('Avatar Height', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsdtb-card-owner .lsd-owner-image-wrapper img' => 'height:{{VALUE}}px; object-fit:cover;',
                    '{{WRAPPER}} .lsdtb-card-owner img.avatar' => 'height:{{VALUE}}px; object-fit:cover;',
                ],
            ]);

        $this->add_control('image_border',
            [
                'type'            => 'border',
                'label'           => esc_html__('Border', 'listdom'),
                'responsive'      => true,
                'radius_fallback' => 'image_border_radius',
                'selectors'       => [
                    '{{WRAPPER}} .lsdtb-card-owner .lsd-owner-image-wrapper img' => '{{VALUE}}',
                    '{{WRAPPER}} .lsdtb-card-owner img.avatar' => '{{VALUE}}',
                ],
            ]);

        $this->end_controls_section();
    }

    private function register_identity_controls(): void
    {
        $this->start_controls_section('identity_' . $this->key,
        [
            'label'      => esc_html__('Identity', 'listdom'),
            'responsive' => true,
            'tab'        => self::TAB_STYLE,
            'condition'  => [
                'relation'       => 'OR',
                'show_name'      => true,
                'show_job_title' => true,
                'show_bio'       => true,
            ],
        ]);

        $this->start_controls_tabs('owner_identity_tabs');

        $this->start_controls_tab('owner_name_tab',
        [
            'label'     => esc_html__('Name', 'listdom'),
            'condition' => [
                'show_name' => true,
            ],
        ]);

        $this->add_control('owner_name_color',
            [
                'type'       => 'colorpicker',
                'label'      => esc_html__('Color', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} h4.lsd-owner-name' => 'color:{{VALUE}};',
                    '{{WRAPPER}} h4.lsd-owner-name a' => 'color:{{VALUE}};',
                ],
            ]);

        $this->add_control('owner_typography',
            [
                'type'       => 'typography',
                'label'      => esc_html__('Typography', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} h4.lsd-owner-name' => '{{VALUE}}',
                    '{{WRAPPER}} h4.lsd-owner-name a' => '{{VALUE}}',
                ],
            ]);

        $this->end_controls_tab();

        $this->start_controls_tab('owner_job_tab',
        [
            'label'     => esc_html__('Job', 'listdom'),
            'condition' => [
                'show_job_title' => true,
            ],
        ]);

        $this->add_control('job_title_text_align',
            [
                'type'       => 'select',
                'class'      => 'lsd-admin-input',
                'label'      => esc_html__('Alignment', 'listdom'),
                'responsive' => true,
                'options'    => [
                    'left'    => esc_html__('Left', 'listdom'),
                    'center'  => esc_html__('Center', 'listdom'),
                    'right'   => esc_html__('Right', 'listdom'),
                    'justify' => esc_html__('Justify', 'listdom'),
                ],
                'selectors'  => [
                    '{{WRAPPER}} .lsd-owner-job-title' => 'text-align:{{VALUE}};',
                ],
            ]);

        $this->add_control('owner_job_title_color',
            [
                'type'       => 'colorpicker',
                'label'      => esc_html__('Color', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsd-owner-job-title' => 'color:{{VALUE}};',
                ],
            ]);

        $this->add_control('owner_job_title_typography',
            [
                'type'       => 'typography',
                'label'      => esc_html__('Typography', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsd-owner-job-title' => '{{VALUE}}',
                ],
            ]);

        $this->end_controls_tab();

        $this->start_controls_tab('owner_bio_tab',
            [
                'label'     => esc_html__('Bio', 'listdom'),
                'condition' => [
                    'show_bio' => true,
                ],
            ]);

        $this->add_control('bio_text_align',
            [
                'type'       => 'select',
                'class'      => 'lsd-admin-input',
                'label'      => esc_html__('Alignment', 'listdom'),
                'responsive' => true,
                'options'    => [
                    'left'    => esc_html__('Left', 'listdom'),
                    'center'  => esc_html__('Center', 'listdom'),
                    'right'   => esc_html__('Right', 'listdom'),
                    'justify' => esc_html__('Justify', 'listdom'),
                ],
                'selectors'  => [
                    '{{WRAPPER}} .lsd-owner-biography' => 'text-align:{{VALUE}};',
                ],
            ]);

        $this->add_control('owner_bio_color',
            [
                'type'       => 'colorpicker',
                'label'      => esc_html__('Color', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsd-owner-biography' => 'color:{{VALUE}};',
                ],
            ]);

        $this->add_control('owner_bio_typography',
            [
                'type'       => 'typography',
                'label'      => esc_html__('Typography', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsd-owner-biography' => '{{VALUE}}',
                ],
            ]);

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->end_controls_section();
    }

    private function register_contact_info_controls(): void
    {
        $this->start_controls_section('contact_info_' . $this->key,
            [
                'label'      => esc_html__('Contact Info', 'listdom'),
                'responsive' => true,
                'tab'        => self::TAB_STYLE,
            ]);

        $this->start_controls_tabs('owner_contact_info_tabs');

        $this->start_controls_tab('owner_contact_info_normal_tab',
            [
                'label' => esc_html__('Normal', 'listdom'),
            ]);

        $this->add_control('contact_info_typography',
            [
                'type'       => 'typography',
                'label'      => esc_html__('Typography', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsd-owner-information-part-2 div, {{WRAPPER}} .lsd-owner-information-part-2 div a' => '{{VALUE}}',
                ],
            ]);

        $this->add_control('contact_info_color',
            [
                'type'       => 'colorpicker',
                'label'      => esc_html__('Color', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsd-owner-information-part-2 div' => 'color:{{VALUE}};',
                    '{{WRAPPER}} .lsd-owner-information-part-2 div a' => 'color:{{VALUE}};',
                ],
            ]);

        $this->end_controls_tab();

        $this->start_controls_tab('owner_contact_info_hover_tab',
            [
                'label' => esc_html__('Hover', 'listdom'),
            ]);

        $this->add_control('contact_info_link_hover_color',
            [
                'type'       => 'colorpicker',
                'label'      => esc_html__('Link Color', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsd-owner-information-part-2 div a:hover' => 'color:{{VALUE}};',
                ],
            ]);

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->end_controls_section();
    }

    private function register_form_controls(): void
    {
        $this->start_controls_section('contact_form_' . $this->key,
            [
                'label'      => esc_html__('Form', 'listdom'),
                'responsive' => true,
                'tab'        => self::TAB_STYLE,
                'condition'  => [
                    'show_form' => true,
                ],
            ]);

        $this->start_controls_tabs('owner_form_tabs');

        $this->start_controls_tab('owner_form_fields_tab',
            [
                'label' => esc_html__('Fields', 'listdom'),
            ]);

        $this->add_control('form_input_typography',
            [
                'type'       => 'typography',
                'label'      => esc_html__('Typography', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsd-owner-contact-form input:not([type="submit"])' => '{{VALUE}}',
                    '{{WRAPPER}} .lsd-owner-contact-form textarea' => '{{VALUE}}',
                ],
            ]);

        $this->add_control('contact_info_input_bg_color',
            [
                'type'       => 'colorpicker',
                'label'      => esc_html__('Background', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsd-owner-contact-form input:not([type="submit"])' => 'background-color:{{VALUE}};',
                    '{{WRAPPER}} .lsd-owner-contact-form textarea' => 'background-color:{{VALUE}};',
                ],
            ]);

        $this->add_control('contact_info_input_text_color',
            [
                'type'       => 'colorpicker',
                'label'      => esc_html__('Text Color', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsd-owner-contact-form input:not([type="submit"])' => 'color:{{VALUE}};',
                    '{{WRAPPER}} .lsd-owner-contact-form textarea' => 'color:{{VALUE}};',
                ],
            ]);

        $this->add_control('contact_info_input_border',
            [
                'type'            => 'border',
                'label'           => esc_html__('Border', 'listdom'),
                'responsive'      => true,
                'radius_fallback' => 'contact_info_input_border_radius',
                'selectors'       => [
                    '{{WRAPPER}} .lsd-owner-contact-form input:not([type="submit"])' => '{{VALUE}}',
                    '{{WRAPPER}} .lsd-owner-contact-form textarea' => '{{VALUE}}',
                ],
            ]);

        $this->add_control('contact_info_input_padding',
            [
                'type'       => 'padding',
                'label'      => esc_html__('Padding', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsd-owner-contact-form input:not([type="submit"])' => '{{VALUE}}',
                    '{{WRAPPER}} .lsd-owner-contact-form textarea' => '{{VALUE}}',
                ],
            ]);

        $this->end_controls_tab();

        $this->start_controls_tab('owner_form_button_tab',
            [
                'label' => esc_html__('Button', 'listdom'),
            ]);

        $this->add_control('form_button_typography',
            [
                'type'       => 'typography',
                'label'      => esc_html__('Typography', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsd-owner-contact-form input[type="submit"]' => '{{VALUE}}',
                    '{{WRAPPER}} .lsd-owner-contact-form button[type="submit"]' => '{{VALUE}}',
                ],
            ]);

        $this->add_control('form_button_text_color',
            [
                'type'       => 'colorpicker',
                'label'      => esc_html__('Text Color', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsd-owner-contact-form input[type="submit"]' => 'color:{{VALUE}};',
                    '{{WRAPPER}} .lsd-owner-contact-form button[type="submit"]' => 'color:{{VALUE}};',
                ],
            ]);

        $this->add_control('form_button_background',
            [
                'type'       => 'colorpicker',
                'label'      => esc_html__('Background', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsd-owner-contact-form input[type="submit"]' => 'background-color:{{VALUE}};',
                    '{{WRAPPER}} .lsd-owner-contact-form button[type="submit"]' => 'background-color:{{VALUE}};',
                ],
            ]);

        $this->add_control('form_button_border',
            [
                'type'            => 'border',
                'label'           => esc_html__('Border', 'listdom'),
                'responsive'      => true,
                'radius_fallback' => 'form_button_border_radius',
                'selectors'       => [
                    '{{WRAPPER}} .lsd-owner-contact-form input[type="submit"]' => '{{VALUE}}',
                    '{{WRAPPER}} .lsd-owner-contact-form button[type="submit"]' => '{{VALUE}}',
                ],
            ]);

        $this->add_control('form_button_padding',
            [
                'type'       => 'padding',
                'label'      => esc_html__('Padding', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsd-owner-contact-form input[type="submit"]' => '{{VALUE}}',
                    '{{WRAPPER}} .lsd-owner-contact-form button[type="submit"]' => '{{VALUE}}',
                ],
            ]);

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->end_controls_section();
    }

    private function register_icon_controls(): void
    {
        $this->start_controls_section('icons_' . $this->key,
            [
                'label'      => esc_html__('Icons', 'listdom'),
                'responsive' => true,
                'tab'        => self::TAB_STYLE,
            ]);

        $this->start_controls_tabs('owner_icons_tabs');

        $this->start_controls_tab('owner_icons_normal_tab',
            [
                'label' => esc_html__('Normal', 'listdom'),
            ]);

        $this->add_control('contact_info_icons_bg_color',
            [
                'type'       => 'colorpicker',
                'label'      => esc_html__('Background', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsd-owner-information-part-2 div i' => 'background-color:{{VALUE}};',
                    '{{WRAPPER}} .lsd-owner-social-networks a' => 'background-color:{{VALUE}};',
                ],
            ]);

        $this->add_control('contact_info_icons_color',
            [
                'type'       => 'colorpicker',
                'label'      => esc_html__('Color', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsd-owner-information-part-2 div i' => 'color:{{VALUE}};',
                    '{{WRAPPER}} .lsd-owner-social-networks i' => 'color:{{VALUE}};',
                ],
            ]);

        $this->add_control('contact_info_icons_border',
            [
                'type'            => 'border',
                'label'           => esc_html__('Border', 'listdom'),
                'responsive'      => true,
                'radius_fallback' => 'contact_info_icons_border_radius',
                'selectors'       => [
                    '{{WRAPPER}} .lsd-owner-information-part-2 div i' => '{{VALUE}}',
                    '{{WRAPPER}} .lsd-owner-social-networks a' => '{{VALUE}}',
                ],
            ]);

        $this->add_control('icons_padding',
            [
                'type'       => 'padding',
                'label'      => esc_html__('Padding', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsd-owner-information-part-2 div i' => '{{VALUE}}',
                    '{{WRAPPER}} .lsd-owner-social-networks i' => '{{VALUE}}',
                ],
            ]);

        $this->add_control('icons_gap',
            [
                'type'       => 'gap',
                'class'      => 'lsd-admin-input',
                'label'      => esc_html__('Items Gap', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsd-owner-information-part-2' => 'gap:{{VALUE}}px;',
                    '{{WRAPPER}} .lsd-owner-social-networks ul' => 'gap:{{VALUE}}px;',
                ],
            ]);

        $this->add_control('contact_info_icons_gap',
            [
                'type'       => 'gap',
                'class'      => 'lsd-admin-input',
                'label'      => esc_html__('Contact Icon Gap', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsd-owner-information-part-2 div' => 'gap:{{VALUE}}px;',
                ],
            ]);

        $this->end_controls_tab();

        $this->start_controls_tab('owner_icons_hover_tab',
            [
                'label' => esc_html__('Hover', 'listdom'),
            ]);

        $this->add_control('contact_info_icons_hover_bg_color',
            [
                'type'       => 'colorpicker',
                'label'      => esc_html__('Background', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsd-owner-information-part-2 div:hover i' => 'background-color:{{VALUE}};',
                    '{{WRAPPER}} .lsd-owner-social-networks a:hover' => 'background-color:{{VALUE}};',
                ],
            ]);

        $this->add_control('contact_info_icons_hover_color',
            [
                'type'       => 'colorpicker',
                'label'      => esc_html__('Color', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsd-owner-information-part-2 div:hover i' => 'color:{{VALUE}};',
                    '{{WRAPPER}} .lsd-owner-social-networks a:hover i' => 'color:{{VALUE}};',
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
        $output = $listing->get_owner('details', $this->get_owner_options($content_settings));

        if (trim((string) $output) === '') return '';

        $html = '<div class="lsd-template-element-owner lsdtb-card-owner">' . $output . '</div>';

        return apply_filters('lsd_template_element_owner_output', $html, $this, $args);
    }

    private function get_owner_options(array $content_settings): array
    {
        $default_privacy_consent = LSD_Privacy::is_consent_enabled('contact');

        return [
            'display_avatar'    => $this->enabled($content_settings, 'show_avatar', true),
            'display_name'      => $this->enabled($content_settings, 'show_name', true),
            'author_link'       => $this->enabled($content_settings, 'author_link', false),
            'link_avatar'       => $this->enabled($content_settings, 'link_avatar', false),
            'display_job_title' => $this->enabled($content_settings, 'show_job_title', true),
            'display_bio'       => $this->enabled($content_settings, 'show_bio', true),
            'display_socials'   => (LSD_Components::socials() && $this->enabled($content_settings, 'show_socials', true)) ? 1 : 0,
            'display_tel'       => $this->enabled($content_settings, 'show_tel', true),
            'display_mobile'    => $this->enabled($content_settings, 'show_mobile', true),
            'display_email'     => $this->enabled($content_settings, 'show_email', true),
            'display_website'   => $this->enabled($content_settings, 'show_website', true),
            'display_fax'       => $this->enabled($content_settings, 'show_fax', true),
            'display_form'      => $this->enabled($content_settings, 'show_form', true),
            'name_field'        => $this->enabled($content_settings, 'name_field', true),
            'phone_field'       => $this->enabled($content_settings, 'phone_field', true),
            'pc_enabled'        => $this->enabled($content_settings, 'pc_enabled', $default_privacy_consent),
            'pc_label'          => isset($content_settings['pc_label']) ? (string) $content_settings['pc_label'] : '',
        ];
    }
}
