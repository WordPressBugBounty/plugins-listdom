<?php

class LSD_Element_Cta extends LSD_Element
{
    public $key = 'cta';
    public $label;

    protected $has_title_settings = true;

    public function __construct()
    {
        parent::__construct();

        $this->label = esc_html__('Call to Action', 'listdom');
    }

    public function get($post_id = null, array $args = [])
    {
        if (!LSD_Components::cta()) return '';

        if (is_null($post_id))
        {
            global $post;
            $post_id = $post->ID;
        }

        $listing = $args['listing'] ?? new LSD_Entity_Listing($post_id);
        $override = isset($args['cta_override']) && is_array($args['cta_override'])
            ? $args['cta_override']
            : [];
        $context = $args['context'] ?? 'archive';

        if ($context === 'single' && !$this->is_single_cta_enabled($listing)) return '';

        $cta = $this->get_data($listing, $override, $context);
        if (!$cta['enabled']) return '';

        $defaults = [
            'context' => 'archive',
            'button_class' => 'lsd-light-button lsd-cta-button',
            'alignment' => '',
            'popup_width' => null,
        ];

        $args = wp_parse_args($args, $defaults);

        ob_start();
        include lsd_template('elements/cta.php');

        return $this->content(
            ob_get_clean(),
            $this,
            [
                'post_id' => $post_id,
                'cta' => $cta,
                'args' => $args,
                'listing' => $listing,
            ]
        );
    }

    public function get_data(LSD_Entity_Listing $listing, array $overrides = [], string $context = 'archive'): array
    {
        $permalink = get_the_permalink($listing->id());

        if (!LSD_Components::cta()) return ['text' => '', 'target' => 'details', 'url' => '', 'content' => '', 'enabled' => false, 'mode' => 'disabled', 'permalink' => $permalink,];

        $details = LSD_Options::details_page();
        $global_settings = $details['elements']['cta'] ?? [];
        if (!is_array($global_settings)) $global_settings = [];

        $cta = $this->normalize_settings($global_settings);
        $default_text = esc_html__('Click Here', 'listdom');

        if (!empty($overrides))
        {
            $mode = 'custom';
            $cta = $this->normalize_settings($overrides);
        }
        else
        {
            $call_to_action = get_post_meta($listing->id(), 'lsd_call_to_action', true);
            if (!is_array($call_to_action)) $call_to_action = [];

            $mode = $this->resolve_mode($call_to_action);
            if ($mode === 'custom') $cta = $this->normalize_settings($call_to_action);
        }

        if (trim($cta['text']) === '') $cta['text'] = $default_text;

        $enabled = $this->is_enabled($cta);

        return [
            'text' => $cta['text'],
            'target' => $cta['target'],
            'url' => $cta['url'],
            'content' => $cta['content'],
            'enabled' => $enabled,
            'mode' => $mode,
            'permalink' => $permalink,
        ];
    }

    protected function normalize_settings(array $settings): array
    {
        $text = trim((string) wp_unslash($settings['text'] ?? ''));
        $target = trim((string) wp_unslash($settings['target'] ?? 'details'));
        $url = trim((string) wp_unslash($settings['url'] ?? ''));
        $content = (string) ($settings['content'] ?? '');

        $options = ['details', 'lightbox', 'custom', 'popup'];
        if (!in_array($target, $options, true)) $target = 'details';

        if ($target === 'popup' && !LSD_Base::isPro()) $target = 'details';

        return ['text' => $text, 'target' => $target, 'url' => $url, 'content' => $content,];
    }

    protected function resolve_mode(array $settings): string
    {
        $mode = isset($settings['mode']) ? (string) $settings['mode'] : '';
        if (in_array($mode, ['inherit', 'custom'], true)) return $mode;

        $has_custom = trim((string) ($settings['text'] ?? '')) !== ''
            || trim((string) ($settings['url'] ?? '')) !== ''
            || trim((string) ($settings['content'] ?? '')) !== '';

        return $has_custom ? 'custom' : 'inherit';
    }

    protected function is_enabled(array $cta): bool
    {
        $text = trim($cta['text'] ?? '');
        if ($text === '') return false;

        if ($cta['target'] === 'custom')
        {
            $url = trim((string) ($cta['url'] ?? ''));

            return $url !== '' && esc_url($url) !== '';
        }
        if ($cta['target'] === 'popup') return trim($cta['content']) !== '';

        return true;
    }

    protected function is_single_cta_enabled(LSD_Entity_Listing $listing): bool
    {
        if (!LSD_Components::cta()) return false;

        static $cache = [];

        $post_id = $listing->id();
        if (array_key_exists($post_id, $cache)) return $cache[$post_id];

        $details = LSD_Options::details_page();
        $enabled = !empty($details['elements']['cta']['enabled']);

        if ($enabled && class_exists(LSD_PTypes_Listing_Single::class))
        {
            $listing_elements = LSD_PTypes_Listing_Single::get_listing_elements($post_id);
            if (isset($listing_elements['cta']['enabled'])) $enabled = (bool) $listing_elements['cta']['enabled'];
        }

        $cache[$post_id] = $enabled;

        return $enabled;
    }

    protected function general_settings(array $data): string
    {
        $alignment = $data['alignment'] ?? 'center';
        $text = $data['text'] ?? '';
        $target = $data['target'] ?? 'details';
        $url = $data['url'] ?? '';
        $content = $data['content'] ?? '';

        if (!in_array($target, array_merge(['details', 'lightbox', 'custom'], LSD_Base::isPro() ? ['popup'] : []), true)) $target = 'details';

        return '<div class="lsd-flex lsd-flex-wrap lsd-gap-3 lsd-element-cta-flex" data-lsd-cta-root>'
            . '<div>'
            . LSD_Form::label([
                'class' => 'lsd-fields-label-tiny',
                'title' => esc_html__('Button Text', 'listdom'),
                'for' => 'lsd_elements_' . esc_attr($this->key) . '_text',
            ])
            . LSD_Form::text([
                'class' => 'lsd-admin-input',
                'name' => 'lsd[elements][' . esc_attr($this->key) . '][text]',
                'id' => 'lsd_elements_' . esc_attr($this->key) . '_text',
                'value' => $text ?? esc_html__('Click Here', 'listdom'),
            ])
            . '<p class="lsd-admin-description-tiny lsd-mb-0 lsd-mt-2">' . esc_html__('Leave empty to use the default "Click Here" button text when no listing level override is provided.', 'listdom') . '</p>' . '</div>'
            . '<div>'
            . LSD_Form::label([
                'class' => 'lsd-fields-label-tiny',
                'title' => esc_html__('Button Action', 'listdom'),
                'for' => 'lsd_elements_' . esc_attr($this->key) . '_target',
            ])
            . LSD_Form::select([
                'class' => 'lsd-admin-input',
                'name' => 'lsd[elements][' . esc_attr($this->key) . '][target]',
                'id' => 'lsd_elements_' . esc_attr($this->key) . '_target',
                'value' => $target,
                'options' => array_merge([
                    'details' => esc_html__('Open listing details page', 'listdom'),
                    'lightbox' => esc_html__('Open listing details in lightbox', 'listdom'),
                    'custom' => esc_html__('Open a custom link', 'listdom'),
                ], LSD_Base::isPro() ? [
                    'popup' => esc_html__('Popup with custom content', 'listdom'),
                ] : []),
                'attributes' => [
                    'data-lsd-cta-target' => '1',
                ],
            ])
            . (LSD_Base::isPro()
                ? ''
                : '<div class="lsd-alert-no-my lsd-mt-2">'
                . LSD_Base::alert(
                    LSD_Base::missFeatureMessage(esc_html__('Popup CTA', 'listdom'))
                )
                . '</div>')
            . '</div>'
            . '<div class="lsd-cta-target-field ' . ($target === 'custom' ? '' : 'lsd-util-hide') . '" data-lsd-cta-target-field="custom">'
            . LSD_Form::label([
                'class' => 'lsd-fields-label-tiny',
                'title' => esc_html__('Custom Link', 'listdom'),
                'for' => 'lsd_elements_' . esc_attr($this->key) . '_url',
            ])
            . LSD_Form::url([
                'class' => 'lsd-admin-input',
                'name' => 'lsd[elements][' . esc_attr($this->key) . '][url]',
                'id' => 'lsd_elements_' . esc_attr($this->key) . '_url',
                'value' => $url,
                'placeholder' => esc_attr__('https://example.com', 'listdom'),
            ])
            . '</div>'
            . (LSD_Base::isPro()
                ? '<div class="lsd-cta-target-field ' . ($target === 'popup' ? '' : 'lsd-util-hide') . '" data-lsd-cta-target-field="popup">'
                . '<button type="button" class="lsd-secondary-button" data-lsd-cta-open-modal="' . esc_attr('lsd_elements_' . $this->key . '_popup_modal') . '">' . esc_html__('Edit Popup Content', 'listdom') . '</button>'
                . '<p class="lsd-admin-description-tiny lsd-mb-0 lsd-mt-2">' . esc_html__('HTML and shortcodes are supported.', 'listdom') . '</p>'
                . '</div>'
                : '')
            . '<div>'
            . LSD_Form::label([
                'class' => 'lsd-fields-label-tiny',
                'title' => esc_html__('Alignment', 'listdom'),
                'for' => 'lsd_elements_' . esc_attr($this->key) . '_alignment',
            ])
            . LSD_Form::select([
                'class' => 'lsd-admin-input',
                'name' => 'lsd[elements][' . esc_attr($this->key) . '][alignment]',
                'id' => 'lsd_elements_' . esc_attr($this->key) . '_alignment',
                'value' => $alignment,
                'options' => [
                    'left' => esc_html__('Left', 'listdom'),
                    'center' => esc_html__('Center', 'listdom'),
                    'right' => esc_html__('Right', 'listdom'),
                    'stretch' => esc_html__('Stretch', 'listdom'),
                ],
            ])
            . '</div>'
            . '</div>'
            . (LSD_Base::isPro()
                ? '<div class="lsd-modal lsd-cta-modal lsd-util-hide" id="' . esc_attr('lsd_elements_' . $this->key . '_popup_modal') . '">'
                . '<div class="lsd-modal-content">'
                . '<a href="#" class="lsd-modal-close" aria-label="' . esc_attr__('Close popup', 'listdom') . '"><i class="fa fa-times"></i></a>'
                . '<div class="lsd-modal-body">'
                . LSD_Form::editor([
                    'id' => 'lsd_elements_' . esc_attr($this->key) . '_popup',
                    'name' => 'lsd[elements][' . esc_attr($this->key) . '][content]',
                    'value' => $content,
                    'rows' => 8,
                    'media_buttons' => true,
                    'quicktags' => false,
                ])
                . '</div>'
                . '</div>'
                . '</div>'
                . '<textarea class="lsd-util-hide" data-lsd-cta-storage="' . esc_attr('lsd_elements_' . $this->key . '_popup') . '" name="lsd[elements][' . esc_attr($this->key) . '][content]">' . esc_textarea($content) . '</textarea>'
                : '<input type="hidden" name="lsd[elements][' . esc_attr($this->key) . '][content]" value="' . esc_attr($content) . '">');
    }
}
