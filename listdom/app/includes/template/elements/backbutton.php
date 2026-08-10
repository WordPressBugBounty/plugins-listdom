<?php

class LSD_Template_Elements_Backbutton extends LSD_Template
{
    public function __construct($settings = [])
    {
        $this->key            = 'backbutton';
        $this->label          = $this->label();
        $this->category       = 'basic';
        $this->template_types = ['single_listing'];
        $this->icon           = self::icons($this->key);

        parent::__construct($settings);
    }

    protected function label()
    {
        return esc_html__('Back Button', 'listdom');
    }

    public function controls(): void
    {
        $this->start_controls_section('content_' . $this->key,
        [
            'label' => esc_html__('Options', 'listdom'),
            'tab'   => self::TAB_CONTENT,
        ]);

        $this->add_control('label',
        [
            'type'    => 'text',
            'class'   => 'lsd-admin-input',
            'label'   => esc_html__('Button Label', 'listdom'),
            'default' => esc_html__('Back', 'listdom'),
        ]);

        $this->add_control('target',
        [
            'type'    => 'select',
            'class'   => 'lsd-admin-input',
            'label'   => esc_html__('Back Page Option', 'listdom'),
            'default' => 'default',
            'options' => [
                'default' => esc_html__('Default', 'listdom'),
                'page' => esc_html__('Select Page', 'listdom'),
            ],
        ]);

        $this->add_control('selected_page',
        [
            'type'      => 'pages',
            'class'     => 'lsd-admin-input',
            'label'     => esc_html__('Selected Page', 'listdom'),
            'show_empty' => true,
            'condition' => [
                'target' => 'page',
            ],
        ]);

        $this->add_control('fallback_page',
        [
            'type'       => 'pages',
            'class'      => 'lsd-admin-input',
            'label'      => esc_html__('Fallback Page', 'listdom'),
            'show_empty' => true,
            'description' => esc_html__('Used when there is no valid previous page.', 'listdom'),
            'condition'  => [
                'target' => 'default',
            ],
        ]);

        $this->add_control('button_style',
        [
            'type'    => 'select',
            'class'   => 'lsd-admin-input',
            'label'   => esc_html__('Button Style', 'listdom'),
            'default' => 'text',
            'options' => [
                'text' => esc_html__('Text', 'listdom'),
                'light' => esc_html__('Light', 'listdom'),
                'solid' => esc_html__('Solid', 'listdom'),
            ],
        ]);

        $this->end_controls_section();

        $this->start_controls_section('style_' . $this->key,
        [
            'label'      => esc_html__('Style', 'listdom'),
            'responsive' => true,
            'tab'        => self::TAB_STYLE,
        ]);

        $this->add_control('typography',
        [
            'type'       => 'typography',
            'label'      => esc_html__('Typography', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsd-template-element-backbutton .lsd-back-button' => '{{VALUE}}',
            ],
        ]);

        $this->add_control('text_color',
        [
            'type'       => 'colorpicker',
            'label'      => esc_html__('Text Color', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsd-template-element-backbutton .lsd-back-button' => 'color:{{VALUE}};',
                '{{WRAPPER}} .lsd-template-element-backbutton .lsd-back-button i' => 'color:{{VALUE}};',
            ],
        ]);

        $this->add_control('background',
        [
            'type'       => 'colorpicker',
            'label'      => esc_html__('Background', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsd-template-element-backbutton .lsd-back-button' => 'background-color:{{VALUE}};',
            ],
        ]);

        $this->add_control('border',
        [
            'type'            => 'border',
            'label'           => esc_html__('Border', 'listdom'),
            'responsive'      => true,
            'radius_fallback' => 'border_radius',
            'selectors'       => [
                '{{WRAPPER}} .lsd-template-element-backbutton .lsd-back-button' => '{{VALUE}}',
            ],
        ]);

        $this->add_control('padding',
        [
            'type'       => 'padding',
            'label'      => esc_html__('Padding', 'listdom'),
            'responsive' => true,
            'selectors'  => [
                '{{WRAPPER}} .lsd-template-element-backbutton .lsd-back-button' => '{{VALUE}}',
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

        $label = isset($content_settings['label']) && trim((string) $content_settings['label'])
            ? trim((string) $content_settings['label'])
            : esc_html__('Back', 'listdom');

        $target = isset($content_settings['target']) ? sanitize_key($content_settings['target']) : 'default';
        if (!in_array($target, ['default', 'page'], true)) $target = 'default';

        $selected_page_id = isset($content_settings['selected_page']) ? (int) $content_settings['selected_page'] : 0;
        $fallback_page_id = isset($content_settings['fallback_page']) ? (int) $content_settings['fallback_page'] : 0;

        $button_style = isset($content_settings['button_style']) ? sanitize_key($content_settings['button_style']) : 'text';
        if (!in_array($button_style, ['text', 'light', 'solid'], true)) $button_style = 'text';

        $button_class = 'lsd-' . $button_style . '-button';
        $url = $this->resolve_url($target, $selected_page_id, $fallback_page_id);

        ob_start();
        include lsd_template('elements/backbutton.php');
        $output = ob_get_clean();

        if (trim((string) $output) === '') return '';

        return '<div class="lsd-template-element-backbutton lsdtb-card-backbutton">' . $output . '</div>';
    }

    private function resolve_url(string $target, int $selected_page_id, int $fallback_page_id): string
    {
        $url = '';

        if ($target === 'default')
        {
            $referer = wp_get_referer();

            if ($referer && wp_validate_redirect($referer, false))
            {
                $referer_path = wp_parse_url($referer, PHP_URL_PATH);
                $referer_query = wp_parse_url($referer, PHP_URL_QUERY);

                parse_str((string) $referer_query, $referer_query_args);

                $admin_path = wp_parse_url(admin_url(), PHP_URL_PATH);
                $home_path = wp_parse_url(home_url('/'), PHP_URL_PATH);
                $normalized_referer_path = is_string($referer_path) ? trailingslashit($referer_path) : '/';
                $normalized_home_path = is_string($home_path) ? trailingslashit($home_path) : '/';
                $is_admin_referer = is_string($referer_path)
                    && is_string($admin_path)
                    && strpos(trailingslashit($referer_path), trailingslashit($admin_path)) === 0;

                $is_paged_referer = isset($referer_query_args['paged']);
                $has_only_paged_query = $is_paged_referer
                    && count($referer_query_args) === 1
                    && $normalized_referer_path === $normalized_home_path;
                $is_root_path_paged_referer = (bool) preg_match(
                    '#^' . preg_quote($normalized_home_path, '#') . 'page/[0-9]+/?$#',
                    $normalized_referer_path
                );

                if (!$is_admin_referer && !$has_only_paged_query && !$is_root_path_paged_referer) $url = $referer;
            }

            if (!$url && $fallback_page_id) $url = get_permalink($fallback_page_id);
        }
        else if ($target === 'page' && $selected_page_id)
        {
            $url = get_permalink($selected_page_id);
        }

        if (!$url) $url = home_url('/');

        return $url;
    }
}
