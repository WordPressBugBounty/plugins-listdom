<?php

class LSD_Template_Elements_Container extends LSD_Template
{
    public function __construct($settings = [])
    {
        $this->key            = 'container';
        $this->label          = $this->label();
        $this->category       = 'layout';
        $this->template_types = [];
        $this->icon           = self::icons($this->key);

        parent::__construct($settings);
    }

    protected function label()
    {
        return esc_html__('Container', 'listdom');
    }

    public function controls(): void
    {
        $this->start_controls_section('layout_' . $this->key,
        [
            'label'      => esc_html__('Layout', 'listdom'),
            'responsive' => true,
            'tab'        => self::TAB_CONTENT
        ]);

        $this->add_control('direction',
        [
            'type'        => 'iconset_direction',
            'label'       => esc_html__('Direction', 'listdom'),
            'default'     => 'column',
            'description' => '',
            'responsive'  => true,
            'selectors'   => [
                '{{WRAPPER}} > .lsd-template-editor-canvas__container > .lsd-template-editor-canvas__container-inner' => 'display:flex; flex-direction:{{VALUE}};',
                '{{WRAPPER}} > .lsd-template-editor-canvas__container-item > .lsd-template-editor-canvas__container > .lsd-template-editor-canvas__container-inner' => 'display:flex; flex-direction:{{VALUE}};',
            ],
        ]);

        $this->add_control('justify_content',
        [
            'type'        => 'iconset_justify',
            'label'       => esc_html__('Justify Content', 'listdom'),
            'default'     => 'flex-start',
            'description' => '',
            'responsive'  => true,
            'selectors'   => [
                '{{WRAPPER}} > .lsd-template-editor-canvas__container > .lsd-template-editor-canvas__container-inner' => 'justify-content:{{VALUE}};',
                '{{WRAPPER}} > .lsd-template-editor-canvas__container-item > .lsd-template-editor-canvas__container > .lsd-template-editor-canvas__container-inner' => 'justify-content:{{VALUE}};',
            ],
        ]);

        $this->add_control('align_items',
        [
            'type'        => 'iconset_align',
            'label'       => esc_html__('Align Items', 'listdom'),
            'default'     => 'stretch',
            'description' => '',
            'responsive'  => true,
            'selectors'   => [
                '{{WRAPPER}} > .lsd-template-editor-canvas__container > .lsd-template-editor-canvas__container-inner' => 'align-items:{{VALUE}};',
                '{{WRAPPER}} > .lsd-template-editor-canvas__container-item > .lsd-template-editor-canvas__container > .lsd-template-editor-canvas__container-inner' => 'align-items:{{VALUE}};',
            ],
        ]);

        $this->add_control('wrap',
        [
            'type'        => 'iconset_wrap',
            'label'       => esc_html__('Wrap', 'listdom'),
            'default'     => 'wrap',
            'description' => '',
            'responsive'  => true,
            'selectors'   => [
                '{{WRAPPER}} > .lsd-template-editor-canvas__container > .lsd-template-editor-canvas__container-inner' => 'flex-wrap:{{VALUE}};',
                '{{WRAPPER}} > .lsd-template-editor-canvas__container-item > .lsd-template-editor-canvas__container > .lsd-template-editor-canvas__container-inner' => 'flex-wrap:{{VALUE}};',
            ],
        ]);

        $this->add_control('gap',
        [
            'type'        => 'gap',
            'label'       => esc_html__('Gap', 'listdom'),
            'default'     => ['row' => 16, 'column' => 16],
            'min'         => 0,
            'step'        => 1,
            'description' => '',
            'responsive'  => true,
            'selectors'   => [
                '{{WRAPPER}} > .lsd-template-editor-canvas__container > .lsd-template-editor-canvas__container-inner' => '{{VALUE}}',
                '{{WRAPPER}} > .lsd-template-editor-canvas__container-item > .lsd-template-editor-canvas__container > .lsd-template-editor-canvas__container-inner' => '{{VALUE}}',
            ],
        ]);

        $this->end_controls_section();

        $this->start_controls_section('style_' . $this->key,
        [
            'label'      => esc_html__('Style', 'listdom'),
            'responsive' => true,
            'tab'        => self::TAB_STYLE
        ]);

        $this->add_control('bg_color',
        [
            'type'        => 'colorpicker',
            'label'       => esc_html__('Background', 'listdom'),
            'placeholder' => '#f5f5f5',
            'description' => '',
            'responsive'  => true,
            'selectors'   => [
                '{{WRAPPER}} > .lsd-template-editor-canvas__container' => 'background-color:{{VALUE}};',
                '{{WRAPPER}} > .lsd-template-editor-canvas__container-item > .lsd-template-editor-canvas__container' => 'background-color:{{VALUE}};',
            ],
        ]);

        $this->add_control('border',
        [
            'type'        => 'border',
            'label'       => esc_html__('Border', 'listdom'),
            'default'     => [
                'top'    => '',
                'right'  => '',
                'bottom' => '',
                'left'   => '',
                'style'  => 'none',
                'color'  => '',
                'radius' => '0'
            ],
            'description' => '',
            'responsive'  => true,
            'selectors'   => [
                '{{WRAPPER}} > .lsd-template-editor-canvas__container' => '{{VALUE}}',
                '{{WRAPPER}} > .lsd-template-editor-canvas__container-item > .lsd-template-editor-canvas__container' => '{{VALUE}}',
            ],
        ]);

        $this->add_control('padding',
        [
            'type'        => 'padding',
            'label'       => esc_html__('Padding', 'listdom'),
            'default'     => [
                'top'    => '16',
                'right'  => '16',
                'bottom' => '16',
                'left'   => '16',
            ],
            'description' => '',
            'responsive'  => true,
            'selectors'   => [
                '{{WRAPPER}} > .lsd-template-editor-canvas__container' => '{{VALUE}}',
                '{{WRAPPER}} > .lsd-template-editor-canvas__container-item > .lsd-template-editor-canvas__container' => '{{VALUE}}',
            ],
        ]);

        $this->end_controls_section();
    }

    /**
     * Containers act as structural wrappers; no direct frontend output for now.
     *
     * @param array $args
     * @return string
     */
    public function render(array $args = []): string
    {
        $settings          = isset($args['settings']) ? (array) $args['settings'] : [];
        $context           = isset($args['context']) ? (string) $args['context'] : '';
        $content           = isset($args['children']) ? (string) $args['children'] : '';

        ob_start();
        include lsd_template('template-builder/elements/container.php');

        return $this->content(
            ob_get_clean(),
            $this,
            [
                'context' => $context,
                'template_type' => $args['template_type'] ?? '',
                'content'   => $content
            ]
        );
    }
}
