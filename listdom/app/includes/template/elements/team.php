<?php

class LSD_Template_Elements_Team extends LSD_Template
{
    public function __construct($settings = [])
    {
        $this->key            = 'team';
        $this->label          = $this->label();
        $this->category       = 'contact_owner';
        $this->template_types = ['single_listing', 'listing_card', 'info_window'];
        $this->icon           = self::icons($this->key);

        parent::__construct($settings);
    }

    protected function label()
    {
        return esc_html__('Team Addon', 'listdom');
    }

    public function controls(): void
    {
        $this->register_content_controls();
        $this->register_box_controls();
        $this->register_text_controls();
        $this->register_avatar_controls();
        $this->register_name_controls();
    }

    private function register_content_controls(): void
    {
        $this->start_controls_section('content_' . $this->key,
            [
                'label' => esc_html__('Options', 'listdom'),
                'tab'   => self::TAB_CONTENT,
            ]);

        $this->add_control('display_tel',
            [
                'type'    => 'switcher',
                'label'   => esc_html__('Tel', 'listdom'),
                'default' => 1,
            ]);

        $this->add_control('display_email',
            [
                'type'    => 'switcher',
                'label'   => esc_html__('Email', 'listdom'),
                'default' => 1,
            ]);

        $this->add_control('display_mobile',
            [
                'type'    => 'switcher',
                'label'   => esc_html__('Mobile', 'listdom'),
                'default' => 1,
            ]);

        $this->add_control('display_website',
            [
                'type'    => 'switcher',
                'label'   => esc_html__('Website', 'listdom'),
                'default' => 0,
            ]);

        $this->add_control('display_fax',
            [
                'type'    => 'switcher',
                'label'   => esc_html__('Fax', 'listdom'),
                'default' => 1,
            ]);

        $this->add_control('display_avatar',
            [
                'type'    => 'switcher',
                'label'   => esc_html__('Avatar', 'listdom'),
                'default' => 1,
            ]);

        $this->add_control('display_job_title',
            [
                'type'    => 'switcher',
                'label'   => esc_html__('Job Title', 'listdom'),
                'default' => 1,
            ]);

        $this->add_control('display_socials',
            [
                'type'    => 'switcher',
                'label'   => esc_html__('Social Networks', 'listdom'),
                'default' => 1,
            ]);

        $this->add_control('display_bio',
            [
                'type'    => 'switcher',
                'label'   => esc_html__('Biography', 'listdom'),
                'default' => 1,
            ]);

        $this->end_controls_section();
    }

    private function register_box_controls(): void
    {
        $this->start_controls_section('box_' . $this->key,
            [
                'label'      => esc_html__('Box', 'listdom'),
                'responsive' => true,
                'tab'        => self::TAB_STYLE,
            ]);

        $this->add_control('background',
            [
                'type'       => 'colorpicker',
                'label'      => esc_html__('Background', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsdtb-card-team' => 'background-color:{{VALUE}};',
                ],
            ]);

        $this->add_control('border',
            [
                'type'            => 'border',
                'label'           => esc_html__('Border', 'listdom'),
                'responsive'      => true,
                'radius_fallback' => 'border_radius',
                'selectors'       => [
                    '{{WRAPPER}} .lsdtb-card-team' => '{{VALUE}}',
                ],
            ]);

        $this->add_control('padding',
            [
                'type'       => 'padding',
                'label'      => esc_html__('Padding', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsdtb-card-team' => '{{VALUE}}',
                ],
            ]);

        $this->end_controls_section();
    }

    private function register_text_controls(): void
    {
        $this->start_controls_section('text_' . $this->key,
            [
                'label'      => esc_html__('Text', 'listdom'),
                'responsive' => true,
                'tab'        => self::TAB_STYLE,
            ]);

        $this->start_controls_tabs('team_text_tabs');

        $this->start_controls_tab('team_text_normal_tab',
            [
                'label' => esc_html__('Normal', 'listdom'),
            ]);

        $this->add_control('typography',
            [
                'type'       => 'typography',
                'label'      => esc_html__('Typography', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsdtb-card-team' => '{{VALUE}}',
                    '{{WRAPPER}} .lsdtb-card-team a' => '{{VALUE}}',
                ],
            ]);

        $this->add_control('text_color',
            [
                'type'       => 'colorpicker',
                'label'      => esc_html__('Text Color', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsdtb-card-team' => 'color:{{VALUE}};',
                    '{{WRAPPER}} .lsdtb-card-team a' => 'color:{{VALUE}};',
                ],
            ]);

        $this->add_control('job_title_color',
            [
                'type'       => 'colorpicker',
                'label'      => esc_html__('Job Title Color', 'listdom'),
                'responsive' => true,
                'condition'  => [
                    'display_job_title' => true,
                ],
                'selectors'  => [
                    '{{WRAPPER}} .lsdtb-card-team .lsd-user-job-title' => 'color:{{VALUE}};',
                ],
            ]);

        $this->end_controls_tab();

        $this->start_controls_tab('team_text_hover_tab',
            [
                'label' => esc_html__('Hover', 'listdom'),
            ]);

        $this->add_control('link_hover_color',
            [
                'type'       => 'colorpicker',
                'label'      => esc_html__('Link Hover Color', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsdtb-card-team a:hover' => 'color:{{VALUE}};',
                ],
            ]);

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->end_controls_section();
    }

    private function register_avatar_controls(): void
    {
        $this->start_controls_section('avatar_' . $this->key,
            [
                'label'      => esc_html__('Avatar', 'listdom'),
                'responsive' => true,
                'tab'        => self::TAB_STYLE,
                'condition'  => [
                    'display_avatar' => true,
                ],
            ]);

        $this->add_control('avatar_width',
            [
                'type'       => 'number',
                'class'      => 'lsd-admin-input',
                'label'      => esc_html__('Width', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsdtb-card-team .lsd-user-image-wrapper img' => 'width:{{VALUE}}px;',
                ],
            ]);

        $this->add_control('avatar_height',
            [
                'type'       => 'number',
                'class'      => 'lsd-admin-input',
                'label'      => esc_html__('Height', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsdtb-card-team .lsd-user-image-wrapper img' => 'height:{{VALUE}}px; object-fit:cover;',
                ],
            ]);

        $this->add_control('avatar_border',
            [
                'type'            => 'border',
                'label'           => esc_html__('Border', 'listdom'),
                'responsive'      => true,
                'radius_fallback' => 'avatar_border_radius',
                'selectors'       => [
                    '{{WRAPPER}} .lsdtb-card-team .lsd-user-image-wrapper img' => '{{VALUE}}',
                ],
            ]);

        $this->end_controls_section();
    }

    private function register_name_controls(): void
    {
        $this->start_controls_section('name_' . $this->key,
            [
                'label'      => esc_html__('Name', 'listdom'),
                'responsive' => true,
                'tab'        => self::TAB_STYLE,
            ]);

        $this->start_controls_tabs('team_name_tabs');

        $this->start_controls_tab('team_name_normal_tab',
            [
                'label' => esc_html__('Normal', 'listdom'),
            ]);

        $this->add_control('name_color',
            [
                'type'       => 'colorpicker',
                'label'      => esc_html__('Color', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsdtb-card-team .lsd-user-name' => 'color:{{VALUE}};',
                    '{{WRAPPER}} .lsdtb-card-team .lsd-user-name a' => 'color:{{VALUE}};',
                ],
            ]);

        $this->add_control('name_typography',
            [
                'type'       => 'typography',
                'label'      => esc_html__('Typography', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsdtb-card-team .lsd-user-name' => '{{VALUE}}',
                    '{{WRAPPER}} .lsdtb-card-team .lsd-user-name a' => '{{VALUE}}',
                ],
            ]);

        $this->end_controls_tab();

        $this->start_controls_tab('team_name_hover_tab',
            [
                'label' => esc_html__('Hover', 'listdom'),
            ]);

        $this->add_control('name_hover_color',
            [
                'type'       => 'colorpicker',
                'label'      => esc_html__('Color', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsdtb-card-team .lsd-user-name:hover' => 'color:{{VALUE}};',
                    '{{WRAPPER}} .lsdtb-card-team .lsd-user-name a:hover' => 'color:{{VALUE}};',
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

        $output = $this->get_team_output($listing_id, $args);

        if (trim((string) $output) === '') return '';

        $html = '<div class="lsd-template-element-team lsdtb-card-team">' . $output . '</div>';

        return apply_filters('lsd_template_element_team_output', $html, $this, $args);
    }

    private function get_team_output(int $listing_id, array $args): string
    {
        if (!class_exists(\LSDPACTIM\Base::class))
        {
            return LSD_Main::alert(esc_html__('Team addon should be installed and activated!', 'listdom'), 'warning');
        }

        $settings = isset($args['settings']) && is_array($args['settings']) ? $args['settings'] : [];
        $content_settings = isset($settings['content']) && is_array($settings['content']) ? $settings['content'] : [];

        $element = new \LSDPACTIM\Element();

        return (string) $element->get($listing_id, $this->get_team_options($content_settings));
    }

    private function get_team_options(array $content_settings): array
    {
        return [
            'display_tel'       => $this->enabled($content_settings, 'display_tel', true),
            'display_email'     => $this->enabled($content_settings, 'display_email', true),
            'display_mobile'    => $this->enabled($content_settings, 'display_mobile', true),
            'display_website'   => $this->enabled($content_settings, 'display_website', false),
            'display_fax'       => $this->enabled($content_settings, 'display_fax', true),
            'display_avatar'    => $this->enabled($content_settings, 'display_avatar', true),
            'display_job_title' => $this->enabled($content_settings, 'display_job_title', true),
            'display_socials'   => $this->enabled($content_settings, 'display_socials', true),
            'display_bio'       => $this->enabled($content_settings, 'display_bio', true),
        ];
    }
}
