<?php

class LSD_Template_Elements_Share extends LSD_Template
{
    public function __construct($settings = [])
    {
        $this->key            = 'share';
        $this->label          = $this->label();
        $this->category       = 'contact_owner';
        $this->template_types = ['single_listing', 'listing_card', 'info_window'];
        $this->icon           = self::icons($this->key);

        parent::__construct($settings);
    }

    protected function label()
    {
        return esc_html__('Listing Share', 'listdom');
    }

    public function controls(): void
    {
        $this->start_controls_section('content_' . $this->key,
        [
            'label' => esc_html__('Options', 'listdom'),
            'tab'   => self::TAB_CONTENT,
        ]);

        $this->add_control('layout',
        [
            'type'    => 'select',
            'class'   => 'lsd-admin-input',
            'label'   => esc_html__('Layout', 'listdom'),
            'default' => 'single',
            'options' => [
                'single' => esc_html__('Single', 'listdom'),
                'archive' => esc_html__('Archive Modal', 'listdom'),
            ],
        ]);

        $this->end_controls_section();

        $this->start_controls_section('main_icon_' . $this->key,
        [
            'label'      => esc_html__('Main Icon', 'listdom'),
            'responsive' => true,
            'tab'        => self::TAB_STYLE,
            'condition'  => [
                'layout' => 'archive',
            ],
        ]);

        $this->start_controls_tabs('main_icon_tabs_' . $this->key);

        $this->start_controls_tab('main_icon_trigger_tab_' . $this->key, [
            'label' => esc_html__('Trigger', 'listdom'),
        ]);

        $this->add_control('icon_bg_color',
        [
            'type'       => 'colorpicker',
            'label'      => esc_html__('Background Color', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsdtb-card-share' => 'background-color:{{VALUE}};',
            ],
        ]);

        $this->add_control('icon_color',
        [
            'type'       => 'colorpicker',
            'label'      => esc_html__('Text Color', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsdtb-card-share .lsd-main-icon' => 'color:{{VALUE}};',
            ],
        ]);

        $this->add_control('icon_padding',
        [
            'type'       => 'padding',
            'label'      => esc_html__('Padding', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsdtb-card-share' => '{{VALUE}}',
            ],
        ]);

        $this->add_control('icon_border_radius',
        [
            'type'       => 'border',
            'label'      => esc_html__('Border', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsdtb-card-share' => '{{VALUE}}',
            ],
        ]);

        $this->add_control('icon_border',
        [
            'type'       => 'border',
            'label'      => esc_html__('Border', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsdtb-card-share' => '{{VALUE}}',
            ],
        ]);

        $this->add_control('main_icon_size',
        [
            'type'       => 'number',
            'class'      => 'lsd-admin-input',
            'label'      => esc_html__('Icon Size', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsdtb-card-share .lsd-main-icon i' => 'font-size:{{VALUE}}px;',
            ],
        ]);

        $this->add_control('main_icon_color',
        [
            'type'       => 'colorpicker',
            'label'      => esc_html__('Icon Color', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsdtb-card-share .lsd-main-icon i' => 'color:{{VALUE}};',
            ],
        ]);

        $this->add_control('main_icon_hover_color',
        [
            'type'       => 'colorpicker',
            'label'      => esc_html__('Icon Hover Color', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsdtb-card-share .lsd-main-icon i:hover' => 'color:{{VALUE}};',
            ],
        ]);

        $this->end_controls_tab();

        $this->start_controls_tab('main_icon_text_tab_' . $this->key, [
            'label' => esc_html__('Modal Text', 'listdom'),
        ]);

        $this->add_control('modal_title_color',
        [
            'type'       => 'colorpicker',
            'label'      => esc_html__('Title Color', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsdtb-card-share .lsd-modal-title' => 'color:{{VALUE}};',
            ],
        ]);

        $this->add_control('modal_title_typography',
        [
            'type'       => 'typography',
            'label'      => esc_html__('Title Typography', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsdtb-card-share .lsd-modal-title' => '{{VALUE}}',
            ],
        ]);

        $this->add_control('modal_title_align',
        [
            'type'       => 'select',
            'class'      => 'lsd-admin-input',
            'label'      => esc_html__('Title Alignment', 'listdom'),
            'responsive' => true,
            'options'    => [
                'left' => esc_html__('Left', 'listdom'),
                'center' => esc_html__('Center', 'listdom'),
                'right' => esc_html__('Right', 'listdom'),
            ],
            'selectors'  => [
                '{{WRAPPER}} .lsdtb-card-share .lsd-modal-title' => 'text-align:{{VALUE}};',
            ],
        ]);

        $this->end_controls_tab();
        $this->end_controls_tabs();
        $this->end_controls_section();

        $this->start_controls_section('sub_icons_' . $this->key,
        [
            'label'      => esc_html__('Sub Icons', 'listdom'),
            'responsive' => true,
            'tab'        => self::TAB_STYLE,
        ]);

        $this->start_controls_tabs('sub_icons_tabs_' . $this->key);

        $this->start_controls_tab('sub_icons_icon_tab_' . $this->key, [
            'label' => esc_html__('Icons', 'listdom'),
        ]);

        $this->add_control('icon_color',
        [
            'type'       => 'colorpicker',
            'label'      => esc_html__('Sub Icon Color', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsdtb-card-share .lsd-share-list-item a i' => 'color:{{VALUE}};',
            ],
        ]);

        $this->add_control('icon_bg_color',
        [
            'type'       => 'colorpicker',
            'label'      => esc_html__('Sub Icon Background', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsdtb-card-share .lsd-share-list-item a' => 'background-color:{{VALUE}};',
            ],
        ]);

        $this->add_control('icon_size',
        [
            'type'       => 'number',
            'class'      => 'lsd-admin-input',
            'label'      => esc_html__('Sub Icon Size', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsdtb-card-share .lsd-share-list-item a i' => 'font-size:{{VALUE}}px;',
            ],
        ]);

        $this->add_control('items_gap',
        [
            'type'       => 'number',
            'class'      => 'lsd-admin-input',
            'label'      => esc_html__('Items Gap', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsdtb-card-share .lsd-share-list' => 'gap:{{VALUE}}px;',
            ],
        ]);

        $this->add_control('item_padding',
        [
            'type'       => 'padding',
            'label'      => esc_html__('Sub Icon Padding', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsdtb-card-share .lsd-share-list-item a' => '{{VALUE}}',
            ],
        ]);

        $this->add_control('item_border',
        [
            'type'       => 'border',
            'label'      => esc_html__('Sub Icon Border', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsdtb-card-share .lsd-share-list-item a' => '{{VALUE}}',
            ],
        ]);

        $this->add_control('icon_hover_color',
        [
            'type'       => 'colorpicker',
            'label'      => esc_html__('Sub Icon Hover Color', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsdtb-card-share .lsd-share-list-item a i:hover' => 'color:{{VALUE}};',
            ],
        ]);

        $this->add_control('icon_hover_bg_color',
        [
            'type'       => 'colorpicker',
            'label'      => esc_html__('Sub Icon Hover Background', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsdtb-card-share .lsd-share-list-item a:hover' => 'background-color:{{VALUE}};',
            ],
        ]);

        $this->end_controls_tab();

        $this->start_controls_tab('sub_icons_text_tab_' . $this->key, [
            'label' => esc_html__('Text', 'listdom'),
            'condition' => [
                'layout' => 'archive',
            ],
        ]);

        $this->add_control('modal_text_color',
        [
            'type'       => 'colorpicker',
            'label'      => esc_html__('Modal Text Color', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsdtb-card-share .lsd-share-modal' => 'color:{{VALUE}};',
            ],
        ]);

        $this->add_control('modal_text_typography',
        [
            'type'       => 'typography',
            'label'      => esc_html__('Modal Text Typography', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsdtb-card-share .lsd-share-modal' => '{{VALUE}}',
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
        $layout = isset($content_settings['layout']) ? sanitize_key($content_settings['layout']) : 'single';
        if (!in_array($layout, ['single', 'archive'], true)) $layout = 'single';

        $listing = new LSD_Entity_Listing($listing_id);
        $output = $listing->get_share_buttons($layout);
        if (trim($output) === '') return '';

        return '<div class="lsd-template-element-share lsdtb-card-share">' . $output . '</div>';
    }
}
