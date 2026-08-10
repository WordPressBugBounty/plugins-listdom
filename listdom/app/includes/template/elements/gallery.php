<?php

class LSD_Template_Elements_Gallery extends LSD_Template
{
    public function __construct($settings = [])
    {
        $this->key            = 'gallery';
        $this->label          = $this->label();
        $this->category       = 'media';
        $this->template_types = ['single_listing', 'listing_card', 'info_window'];
        $this->icon           = self::icons($this->key);

        parent::__construct($settings);
    }

    protected function label()
    {
        return esc_html__('Listing Gallery', 'listdom');
    }

    public function controls(): void
    {
        $this->start_controls_section('layout_' . $this->key,
            [
                'label' => esc_html__('Layout', 'listdom'),
                'tab'   => self::TAB_CONTENT,
            ]);

        $this->add_control('style',
            [
                'type'    => 'select',
                'label'   => esc_html__('Layout', 'listdom'),
                'class'   => 'lsd-admin-input',
                'default' => 'list',
                'options' => [
                    'list'   => esc_html__('List', 'listdom'),
                    'slider' => esc_html__('Slider', 'listdom'),
                    'linear' => esc_html__('Linear Gallery', 'listdom'),
                ],
            ]);

        $this->add_control('include_thumbnail',
            [
                'type'    => 'switcher',
                'label'   => esc_html__('Include Featured Image', 'listdom'),
                'default' => 1,
            ]);

        $this->add_control('lightbox',
            [
                'type'    => 'switcher',
                'label'   => esc_html__('Image Lightbox', 'listdom'),
                'default' => 1,
            ]);

        $this->add_control('link_method',
            [
                'type'      => 'select',
                'label'     => esc_html__('Link Method', 'listdom'),
                'class'     => 'lsd-admin-input',
                'default'   => 'normal',
                'options'   => LSD_Base::get_listing_link_methods(),
                'condition' => [
                    'lightbox' => false,
                ],
            ]);

        $this->add_control('image_limit',
            [
                'type'    => 'number',
                'class'   => 'lsd-admin-input',
                'label'   => esc_html__('Number of Images', 'listdom'),
                'default' => 4,
            ]);

        $this->end_controls_section();

        $this->start_controls_section('slider_' . $this->key,
            [
                'label'     => esc_html__('Slider', 'listdom'),
                'tab'       => self::TAB_CONTENT,
                'condition' => [
                    'style' => 'slider',
                ],
            ]);

        $this->add_control('slider_autoplay',
            [
                'type'    => 'switcher',
                'label'   => esc_html__('Autoplay', 'listdom'),
                'default' => 1,
            ]);

        $this->add_control('slider_auto_height',
            [
                'type'    => 'switcher',
                'label'   => esc_html__('Auto-height', 'listdom'),
                'default' => 1,
            ]);

        $this->add_control('slider_loop',
            [
                'type'    => 'switcher',
                'label'   => esc_html__('Loop', 'listdom'),
                'default' => 0,
            ]);

        $this->add_control('thumbnail_status',
            [
                'type'    => 'select',
                'label'   => esc_html__('Thumbnails', 'listdom'),
                'class'   => 'lsd-admin-input',
                'default' => 'image',
                'options' => [
                    'list'     => esc_html__('List', 'listdom'),
                    'image'    => esc_html__('On the Image', 'listdom'),
                    'disabled' => esc_html__('Disabled', 'listdom'),
                ],
            ]);

        $this->add_control('navigation_method',
            [
                'type'    => 'select',
                'label'   => esc_html__('Navigation Method', 'listdom'),
                'class'   => 'lsd-admin-input',
                'default' => 'dots',
                'options' => [
                    'dots'     => esc_html__('Dots', 'listdom'),
                    'nav'      => esc_html__('Arrows', 'listdom'),
                    'disabled' => esc_html__('Disabled', 'listdom'),
                ],
            ]);

        $this->end_controls_section();

        $this->start_controls_section('box_' . $this->key,
            [
                'label'      => esc_html__('Box', 'listdom'),
                'responsive' => true,
                'tab'        => self::TAB_STYLE,
            ]);

        $this->add_control('box_background',
            [
                'type'       => 'colorpicker',
                'label'      => esc_html__('Background', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsd-template-element-gallery' => 'background-color:{{VALUE}};',
                    '{{WRAPPER}} .lsdtb-card-gallery' => 'background-color:{{VALUE}};',
                ],
            ]);

        $this->add_control('box_padding',
            [
                'type'       => 'padding',
                'label'      => esc_html__('Padding', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsd-template-element-gallery' => '{{VALUE}}',
                    '{{WRAPPER}} .lsdtb-card-gallery' => '{{VALUE}}',
                ],
            ]);

        $this->add_control('box_border',
            [
                'type'            => 'border',
                'label'           => esc_html__('Border', 'listdom'),
                'responsive'      => true,
                'radius_fallback' => 'box_border_radius',
                'selectors'       => [
                    '{{WRAPPER}} .lsd-template-element-gallery' => '{{VALUE}}',
                    '{{WRAPPER}} .lsdtb-card-gallery' => '{{VALUE}}',
                ],
            ]);

        $this->end_controls_section();

        $this->start_controls_section('image_style_' . $this->key,
            [
                'label'      => esc_html__('Image', 'listdom'),
                'responsive' => true,
                'tab'        => self::TAB_STYLE,
            ]);

        $this->add_control('image_radius',
            [
                'type'       => 'number',
                'class'      => 'lsd-admin-input',
                'label'      => esc_html__('Image Radius', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsdtb-card-gallery img' => 'border-radius:{{VALUE}}px;',
                ],
            ]);

        $this->add_control('image_height',
            [
                'type'       => 'number',
                'class'      => 'lsd-admin-input',
                'label'      => esc_html__('Image Height', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsdtb-card-gallery .lsd-gallery-linear .lsd-gallery-grid-item img' => 'min-height:{{VALUE}}px; max-height:{{VALUE}}px;',
                    '{{WRAPPER}} .lsdtb-card-gallery .lsd-image-gallery img' => 'min-height:{{VALUE}}px; max-height:{{VALUE}}px;',
                ],
            ]);

        $this->add_control('slider_image_height',
            [
                'type'       => 'number',
                'class'      => 'lsd-admin-input',
                'label'      => esc_html__('Slider Image Height', 'listdom'),
                'responsive' => true,
                'condition'  => [
                    'style' => 'slider',
                ],
                'selectors'  => [
                    '{{WRAPPER}} .lsdtb-card-gallery .lsd-gallery-slider .lsd-gallery-item a img' => 'min-height:{{VALUE}}px; max-height:{{VALUE}}px;',
                ],
            ]);

        $this->add_control('image_object_fit',
            [
                'type'    => 'select',
                'label'   => esc_html__('Image Object Fit', 'listdom'),
                'class'   => 'lsd-admin-input',
                'default' => 'cover',
                'options' => [
                    'fill'       => esc_html__('Fill', 'listdom'),
                    'contain'    => esc_html__('Contain', 'listdom'),
                    'cover'      => esc_html__('Cover', 'listdom'),
                    'none'       => esc_html__('None', 'listdom'),
                    'scale-down' => esc_html__('Scale Down', 'listdom'),
                ],
                'selectors' => [
                    '{{WRAPPER}} .lsdtb-card-gallery img' => 'object-fit:{{VALUE}};',
                ],
            ]);

        $this->add_control('image_align',
            [
                'type'    => 'select',
                'label'   => esc_html__('Image Align', 'listdom'),
                'class'   => 'lsd-admin-input',
                'default' => 'start',
                'options' => [
                    'start'  => esc_html__('Start', 'listdom'),
                    'center' => esc_html__('Center', 'listdom'),
                    'end'    => esc_html__('End', 'listdom'),
                ],
                'selectors' => [
                    '{{WRAPPER}} .lsdtb-card-gallery .lsd-image-gallery' => 'justify-content:{{VALUE}};',
                ],
            ]);

        $this->end_controls_section();

        $this->start_controls_section('arrows_' . $this->key,
            [
                'label'      => esc_html__('Arrows', 'listdom'),
                'responsive' => true,
                'tab'        => self::TAB_STYLE,
                'condition'  => [
                    'style'             => 'slider',
                    'navigation_method' => 'nav',
                ],
            ]);

        $this->start_controls_tabs('arrows_style_tabs');

        $this->start_controls_tab('arrows_normal_tab',
            [
                'label' => esc_html__('Normal', 'listdom'),
            ]);

        $this->add_control('arrow_color',
            [
                'type'       => 'colorpicker',
                'label'      => esc_html__('Color', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsdtb-card-gallery .owl-nav .owl-next' => 'color:{{VALUE}};',
                    '{{WRAPPER}} .lsdtb-card-gallery .owl-nav .owl-prev' => 'color:{{VALUE}};',
                ],
            ]);

        $this->add_control('arrow_icon_size',
            [
                'type'       => 'number',
                'class'      => 'lsd-admin-input',
                'label'      => esc_html__('Icon Size', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsdtb-card-gallery .owl-nav .owl-next' => 'font-size:{{VALUE}}px;',
                    '{{WRAPPER}} .lsdtb-card-gallery .owl-nav .owl-prev' => 'font-size:{{VALUE}}px;',
                ],
            ]);

        $this->add_control('arrow_padding',
            [
                'type'       => 'padding',
                'label'      => esc_html__('Padding', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsdtb-card-gallery .owl-nav .owl-next' => '{{VALUE}}',
                    '{{WRAPPER}} .lsdtb-card-gallery .owl-nav .owl-prev' => '{{VALUE}}',
                ],
            ]);

        $this->end_controls_tab();

        $this->start_controls_tab('arrows_hover_tab',
            [
                'label' => esc_html__('Hover', 'listdom'),
            ]);

        $this->add_control('arrow_hover_color',
            [
                'type'       => 'colorpicker',
                'label'      => esc_html__('Color', 'listdom'),
                'responsive' => true,
                'selectors'  => [
                    '{{WRAPPER}} .lsdtb-card-gallery .owl-nav .owl-next:hover' => 'color:{{VALUE}};',
                    '{{WRAPPER}} .lsdtb-card-gallery .owl-nav .owl-prev:hover' => 'color:{{VALUE}};',
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

        $lightbox = !isset($content_settings['lightbox']) || !empty($content_settings['lightbox']);
        $link_method = isset($content_settings['link_method']) && trim((string) $content_settings['link_method']) ? $content_settings['link_method'] : 'normal';
        $include_thumbnail = !empty($content_settings['include_thumbnail']);
        $autoplay = !isset($content_settings['slider_autoplay']) || !empty($content_settings['slider_autoplay']);
        $auto_height = !isset($content_settings['slider_auto_height']) || !empty($content_settings['slider_auto_height']);
        $loop = !empty($content_settings['slider_loop']);
        $thumbnail_status = $content_settings['thumbnail_status'] ?? 'image';
        $image_limit = isset($content_settings['image_limit']) ? (int) $content_settings['image_limit'] : 4;
        $navigation_method = isset($content_settings['navigation_method']) && trim((string) $content_settings['navigation_method']) ? $content_settings['navigation_method'] : 'dots';
        $style = $content_settings['style'] ?? 'list';

        $listing = new LSD_Entity_Listing($listing_id);
        $output = $listing->get_gallery([
            'lightbox'          => $lightbox,
            'link_method'       => $link_method,
            'style'             => $style,
            'include_thumbnail' => $include_thumbnail,
            'image_limit'       => $image_limit,
            'thumbnail_status'  => $thumbnail_status,
            'navigation_method' => $navigation_method,
            'autoplay'          => $autoplay,
            'auto_height'       => $auto_height,
            'loop'              => $loop,
        ]);

        if (trim((string) $output) === '') return '';

        $html = '<div class="lsd-template-element-gallery lsdtb-card-gallery">' . $output . '</div>';

        return apply_filters('lsd_template_element_gallery_output', $html, $this, $args);
    }
}
