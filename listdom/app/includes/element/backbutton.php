<?php

class LSD_Element_Backbutton extends LSD_Element
{
    public $key = 'backbutton';
    public $label;

    protected $has_title_settings = false;

    public function __construct()
    {
        parent::__construct();

        $this->label = esc_html__('Back Button', 'listdom');
    }

    public function get($post_id = null, array $args = [])
    {
        if (is_null($post_id))
        {
            global $post;
            $post_id = $post->ID ?? null;
        }

        if (!$post_id) return '';

        $settings = LSD_Options::details_page();
        $data = $settings['elements'][$this->key] ?? [];

        $label = isset($data['label']) && trim($data['label'])
            ? $data['label']
            : esc_html__('Back', 'listdom');

        $target = $data['target'] ?? 'default';
        if ($target === 'search') $target = 'default';
        if (!in_array($target, ['default', 'page'], true)) $target = 'default';

        $selected_page_id = isset($data['selected_page']) ? (int) $data['selected_page'] : 0;
        $fallback_page_id = isset($data['fallback_page']) ? (int) $data['fallback_page'] : 0;

        // Backward compatibility with the previous single page field.
        if (!$selected_page_id && !$fallback_page_id && isset($data['page']))
        {
            $legacy_page_id = (int) $data['page'];

            if ($target === 'page') $selected_page_id = $legacy_page_id;
            else $fallback_page_id = $legacy_page_id;
        }

        $button_style = isset($args['button_style']) ? sanitize_key($args['button_style']) : 'text';
        if (!in_array($button_style, ['text', 'light', 'solid'], true)) $button_style = 'text';

        $button_class = 'lsd-' . $button_style . '-button';

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

                // Avoid admin referrers and weak Elementor/Listdom Load More referers like /?paged=2 or /page/2/.
                if (!$is_admin_referer && !$has_only_paged_query && !$is_root_path_paged_referer) $url = $referer;
            }

            if (!$url && $fallback_page_id) $url = get_permalink($fallback_page_id);
        } else if ($target === 'page' && $selected_page_id) $url = get_permalink($selected_page_id);

        if (!$url) $url = home_url('/');

        ob_start();
        include lsd_template('elements/backbutton.php');

        return $this->content(
            ob_get_clean(),
            $this,
            [
                'post_id' => $post_id,
                'url' => $url,
                'label' => $label,
                'target' => $target,
                'selected_page_id' => $selected_page_id,
                'fallback_page_id' => $fallback_page_id,
                'button_style' => $button_style,
                'button_class' => $button_class,
            ]
        );
    }

    protected function general_settings(array $data): string
    {
        $target = $data['target'] ?? 'default';
        if ($target === 'search') $target = 'default';
        if (!in_array($target, ['default', 'page'], true)) $target = 'default';

        $selected_page = $data['selected_page'] ?? '';
        $fallback_page = $data['fallback_page'] ?? '';

        if ($selected_page === '' && $fallback_page === '' && isset($data['page']))
        {
            if ($target === 'page') $selected_page = $data['page'];
            else $fallback_page = $data['page'];
        }

        $label = isset($data['label']) && trim($data['label'])
            ? $data['label']
            : esc_html__('Back', 'listdom');

        $selected_page_class = $target === 'page' ? '' : 'lsd-util-hide';
        $fallback_page_class = $target === 'default' ? '' : 'lsd-util-hide';

        return '<div>
        <label class="lsd-fields-label-tiny" for="lsd_elements_' . esc_attr($this->key) . '_label">' . esc_html__('Button Label', 'listdom') . '</label>
        ' . LSD_Form::text([
                'class' => 'lsd-admin-input',
                'id' => 'lsd_elements_' . esc_attr($this->key) . '_label',
                'name' => 'lsd[elements][' . esc_attr($this->key) . '][label]',
                'value' => $label,
            ]) . '
    </div>
    <div>
        <label class="lsd-fields-label-tiny" for="lsd_elements_' . esc_attr($this->key) . '_target">' . esc_html__('Back Page Option', 'listdom') . '</label>
        ' . LSD_Form::select([
                'class' => 'lsd-admin-input lsd-trigger-select-options',
                'id' => 'lsd_elements_' . esc_attr($this->key) . '_target',
                'name' => 'lsd[elements][' . esc_attr($this->key) . '][target]',
                'value' => $target,
                'options' => [
                    'default' => [
                        'label' => esc_html__('Default', 'listdom'),
                        'attributes' => [
                            'data-lsd-show' => '#lsd_elements_' . esc_attr($this->key) . '_fallback_page_wrapper',
                            'data-lsd-hide' => '#lsd_elements_' . esc_attr($this->key) . '_selected_page_wrapper',
                        ],
                    ],
                    'page' => [
                        'label' => esc_html__('Select Page', 'listdom'),
                        'attributes' => [
                            'data-lsd-show' => '#lsd_elements_' . esc_attr($this->key) . '_selected_page_wrapper',
                            'data-lsd-hide' => '#lsd_elements_' . esc_attr($this->key) . '_fallback_page_wrapper',
                        ],
                    ],
                ],
            ]) . '
    </div>
    <div id="lsd_elements_' . esc_attr($this->key) . '_selected_page_wrapper" class="' . esc_attr($selected_page_class) . '">
        <label class="lsd-fields-label-tiny" for="lsd_elements_' . esc_attr($this->key) . '_selected_page">' . esc_html__('Selected Page', 'listdom') . '</label>
        ' . LSD_Form::pages([
                'class' => 'lsd-admin-input',
                'id' => 'lsd_elements_' . esc_attr($this->key) . '_selected_page',
                'name' => 'lsd[elements][' . esc_attr($this->key) . '][selected_page]',
                'value' => $selected_page,
                'show_empty' => true,
            ]) . '
    </div>
    <div id="lsd_elements_' . esc_attr($this->key) . '_fallback_page_wrapper" class="' . esc_attr($fallback_page_class) . '">
        <label class="lsd-fields-label-tiny" for="lsd_elements_' . esc_attr($this->key) . '_fallback_page">' . esc_html__('Fallback Page', 'listdom') . '</label>
        ' . LSD_Form::pages([
                'class' => 'lsd-admin-input',
                'id' => 'lsd_elements_' . esc_attr($this->key) . '_fallback_page',
                'name' => 'lsd[elements][' . esc_attr($this->key) . '][fallback_page]',
                'value' => $fallback_page,
                'show_empty' => true,
            ]) . '
        <p class="lsd-admin-description-tiny lsd-mb-0 lsd-mt-2">' . esc_html__('If Default is selected, this page is used when there is no valid previous page.', 'listdom') . '</p>
    </div>';
    }
}
