<?php

class LSD_Template_Elements_Spacer extends LSD_Template
{
    public function __construct($settings = [])
    {
        $this->key            = 'spacer';
        $this->label          = $this->label();
        $this->category       = 'general';
        $this->template_types = ['single_listing', 'listing_card', 'info_window'];
        $this->icon           = self::icons($this->key);

        parent::__construct($settings);
    }

    protected function label()
    {
        return esc_html__('Spacer', 'listdom');
    }

    public function controls(): void
    {
        $this->start_controls_section('content_' . $this->key, [
            'label'      => esc_html__('Options', 'listdom'),
            'responsive' => true,
            'tab'        => self::TAB_CONTENT,
        ]);

        $this->add_control('height', [
            'type'       => 'number',
            'class'      => 'lsd-admin-input',
            'label'      => esc_html__('Height', 'listdom'),
            'default'    => 32,
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsd-template-element-spacer' => 'height:{{VALUE}}px; min-height:{{VALUE}}px;',
            ],
        ]);

        $this->end_controls_section();
    }

    public function render(array $args = []): string
    {
        $settings = isset($args['settings']) && is_array($args['settings']) ? $args['settings'] : [];
        $content_settings = isset($settings['content']) && is_array($settings['content']) ? $settings['content'] : [];

        $style = '';
        if (!array_key_exists('height', $content_settings) || $content_settings['height'] === '')
        {
            $style = ' style="height:32px; min-height:32px;"';
        }

        return '<div class="lsd-template-element-spacer lsdtb-card-spacer" aria-hidden="true"' . $style . '></div>';
    }
}
