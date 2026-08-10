<?php

class LSD_Template_Elements_Availability extends LSD_Template
{
    public function __construct($settings = [])
    {
        $this->key            = 'availability';
        $this->label          = $this->label();
        $this->category       = 'advanced';
        $this->template_types = ['single_listing', 'listing_card', 'info_window'];
        $this->icon           = self::icons($this->key);

        parent::__construct($settings);
    }

    protected function label()
    {
        return esc_html__('Listing Working Hours', 'listdom');
    }

    public function controls(): void
    {
        $this->start_controls_section('content_' . $this->key,
        [
            'label' => esc_html__('Options', 'listdom'),
            'tab'   => self::TAB_CONTENT,
        ]);

        $this->add_control('type',
        [
            'type'    => 'select',
            'class'   => 'lsd-admin-input',
            'label'   => esc_html__('Type', 'listdom'),
            'default' => 'one-day',
            'options' => [
                'one-day' => esc_html__('One Day', 'listdom'),
                'full'    => esc_html__('Full Week', 'listdom'),
            ],
        ]);

        $this->add_control('display_icon',
        [
            'type'    => 'select',
            'class'   => 'lsd-admin-input',
            'label'   => esc_html__('Display Icon', 'listdom'),
            'default' => 'inline',
            'options' => [
                'inline' => esc_html__('Yes', 'listdom'),
                'none'   => esc_html__('No', 'listdom'),
            ],
            'condition' => [
                'type' => 'one-day',
            ],
            'selectors' => [
                '{{WRAPPER}} .lsd-template-element-availability .lsd-ava-one-day .lsd-fe-icon' => 'display: {{VALUE}};',
                '{{WRAPPER}} .lsd-template-element-availability .lsd-ava-one-day .lsd-icon' => 'display: {{VALUE}};',
                '{{WRAPPER}} .lsdtb-card-availability .lsd-ava-one-day .lsd-fe-icon' => 'display: {{VALUE}};',
                '{{WRAPPER}} .lsdtb-card-availability .lsd-ava-one-day .lsd-icon' => 'display: {{VALUE}};',
            ],
        ]);

        $this->end_controls_section();

        $this->start_controls_section('one_day_text_' . $this->key,
        [
            'label'      => esc_html__('One Day Text', 'listdom'),
            'responsive' => true,
            'tab'        => self::TAB_STYLE,
            'condition' => [
                'type' => 'one-day',
            ],
        ]);

        $this->add_control('one_day_typography',
        [
            'type'       => 'typography',
            'label'      => esc_html__('Typography', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsd-template-element-availability .lsd-ava-hour' => '{{VALUE}}',
                '{{WRAPPER}} .lsdtb-card-availability .lsd-ava-hour' => '{{VALUE}}',
            ],
        ]);

        $this->add_control('one_day_text_color',
        [
            'type'       => 'colorpicker',
            'label'      => esc_html__('Text Color', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsd-template-element-availability .lsd-ava-hour' => 'color:{{VALUE}};',
                '{{WRAPPER}} .lsdtb-card-availability .lsd-ava-hour' => 'color:{{VALUE}};',
            ],
        ]);

        $this->add_control('one_day_off_text_color',
        [
            'type'       => 'colorpicker',
            'label'      => esc_html__('Off Text Color', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsd-template-element-availability .lsd-ava-one-day-off .lsd-ava-hour' => 'color:{{VALUE}} !important;',
                '{{WRAPPER}} .lsdtb-card-availability .lsd-ava-one-day-off .lsd-ava-hour' => 'color:{{VALUE}} !important;',
            ],
        ]);

        $this->end_controls_section();

        $this->start_controls_section('one_day_icon_' . $this->key,
        [
            'label'      => esc_html__('One Day Icon', 'listdom'),
            'responsive' => true,
            'tab'        => self::TAB_STYLE,
            'condition' => [
                'type' => 'one-day',
            ],
        ]);

        $this->add_control('one_day_icon_color',
        [
            'type'       => 'colorpicker',
            'label'      => esc_html__('Icon Color', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsd-template-element-availability .lsd-ava-one-day .lsd-fe-icon' => 'color:{{VALUE}};',
                '{{WRAPPER}} .lsd-template-element-availability .lsd-ava-one-day .lsd-icon' => 'color:{{VALUE}};',
                '{{WRAPPER}} .lsdtb-card-availability .lsd-ava-one-day .lsd-fe-icon' => 'color:{{VALUE}};',
                '{{WRAPPER}} .lsdtb-card-availability .lsd-ava-one-day .lsd-icon' => 'color:{{VALUE}};',
            ],
        ]);

        $this->add_control('one_day_icon_gap',
        [
            'type'       => 'number',
            'class'      => 'lsd-admin-input',
            'label'      => esc_html__('Icon Gap', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsd-template-element-availability .lsd-ava-one-day .lsd-fe-icon' => 'margin-right:{{VALUE}}px;',
                '{{WRAPPER}} .lsd-template-element-availability .lsd-ava-one-day .lsd-icon' => 'margin-right:{{VALUE}}px;',
                '{{WRAPPER}} .lsdtb-card-availability .lsd-ava-one-day .lsd-fe-icon' => 'margin-right:{{VALUE}}px;',
                '{{WRAPPER}} .lsdtb-card-availability .lsd-ava-one-day .lsd-icon' => 'margin-right:{{VALUE}}px;',
            ],
        ]);

        $this->end_controls_section();

        $this->start_controls_section('full_week_text_' . $this->key,
        [
            'label'      => esc_html__('Full Week Text', 'listdom'),
            'responsive' => true,
            'tab'        => self::TAB_STYLE,
            'condition' => [
                'type' => 'full',
            ],
        ]);

        $this->add_control('weekday_typography',
        [
            'type'       => 'typography',
            'label'      => esc_html__('Weekday Typography', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsd-template-element-availability .lsd-ava-weekday-column' => '{{VALUE}}',
                '{{WRAPPER}} .lsdtb-card-availability .lsd-ava-weekday-column' => '{{VALUE}}',
            ],
        ]);

        $this->add_control('weekday_text_color',
        [
            'type'       => 'colorpicker',
            'label'      => esc_html__('Weekday Text Color', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsd-template-element-availability .lsd-ava-weekday-column' => 'color:{{VALUE}};',
                '{{WRAPPER}} .lsdtb-card-availability .lsd-ava-weekday-column' => 'color:{{VALUE}};',
            ],
        ]);

        $this->add_control('hour_typography',
        [
            'type'       => 'typography',
            'label'      => esc_html__('Hour Typography', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsd-template-element-availability .lsd-ava-hours-column' => '{{VALUE}}',
                '{{WRAPPER}} .lsdtb-card-availability .lsd-ava-hours-column' => '{{VALUE}}',
            ],
        ]);

        $this->add_control('hour_text_color',
        [
            'type'       => 'colorpicker',
            'label'      => esc_html__('Hour Text Color', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsd-template-element-availability .lsd-ava-hours-column' => 'color:{{VALUE}};',
                '{{WRAPPER}} .lsdtb-card-availability .lsd-ava-hours-column' => 'color:{{VALUE}};',
            ],
        ]);

        $this->add_control('off_text_color',
        [
            'type'       => 'colorpicker',
            'label'      => esc_html__('Off Text Color', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsd-template-element-availability .lsd-ava-offday .lsd-ava-hours-column' => 'color:{{VALUE}} !important;',
                '{{WRAPPER}} .lsdtb-card-availability .lsd-ava-offday .lsd-ava-hours-column' => 'color:{{VALUE}} !important;',
            ],
        ]);

        $this->end_controls_section();

        $this->start_controls_section('full_week_gap_' . $this->key,
        [
            'label'      => esc_html__('Full Week Gaps', 'listdom'),
            'responsive' => true,
            'tab'        => self::TAB_STYLE,
            'condition' => [
                'type' => 'full',
            ],
        ]);

        $this->add_control('gap_weekday_hour',
        [
            'type'       => 'number',
            'class'      => 'lsd-admin-input',
            'label'      => esc_html__('Text Gap', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsd-template-element-availability .lsd-ava-weekday-wrapper .lsd-row' => 'column-gap:{{VALUE}}px;',
                '{{WRAPPER}} .lsdtb-card-availability .lsd-ava-weekday-wrapper .lsd-row' => 'column-gap:{{VALUE}}px;',
            ],
        ]);

        $this->add_control('gap_rows',
        [
            'type'       => 'number',
            'class'      => 'lsd-admin-input',
            'label'      => esc_html__('Row Gap', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsd-template-element-availability .lsd-ava-week .lsd-ava-weekday-wrapper' => 'margin-bottom:{{VALUE}}px;',
                '{{WRAPPER}} .lsdtb-card-availability .lsd-ava-week .lsd-ava-weekday-wrapper' => 'margin-bottom:{{VALUE}}px;',
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
        $type = $content_settings['type'] ?? 'one-day';

        $listing = new LSD_Entity_Listing($listing_id);
        $output = $listing->get_availability($type === 'one-day');
        if (trim($output) === '') return '';

        return '<div class="lsd-template-element-availability lsdtb-card-availability">' . $output . '</div>';
    }
}
