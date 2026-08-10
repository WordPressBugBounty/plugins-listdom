<?php

class LSD_Template_Elements_Related extends LSD_Template
{
    public function __construct($settings = [])
    {
        $this->key            = 'related';
        $this->label          = $this->label();
        $this->category       = 'advanced';
        $this->template_types = ['single_listing'];
        $this->icon           = self::icons($this->key);

        parent::__construct($settings);
    }

    protected function label()
    {
        return esc_html__('Related Listings', 'listdom');
    }

    public function controls(): void
    {
        $this->start_controls_section('taxonomies_' . $this->key,
        [
            'label' => esc_html__('Taxonomies', 'listdom'),
            'tab'   => self::TAB_CONTENT,
        ]);

        $this->add_control('taxonomy_category',
        [
            'type'    => 'switcher',
            'label'   => esc_html__('Category', 'listdom'),
            'default' => 1,
        ]);

        $this->add_control('taxonomy_location',
        [
            'type'    => 'switcher',
            'label'   => esc_html__('Location', 'listdom'),
            'default' => 1,
        ]);

        $this->add_control('taxonomy_label',
        [
            'type'    => 'switcher',
            'label'   => esc_html__('Label', 'listdom'),
            'default' => 1,
        ]);

        $this->add_control('taxonomy_tag',
        [
            'type'    => 'switcher',
            'label'   => esc_html__('Tag', 'listdom'),
            'default' => 1,
        ]);

        $this->add_control('taxonomy_feature',
        [
            'type'    => 'switcher',
            'label'   => esc_html__('Feature', 'listdom'),
            'default' => 1,
        ]);

        $this->end_controls_section();

        $this->start_controls_section('filters_' . $this->key,
        [
            'label' => esc_html__('Filters', 'listdom'),
            'tab'   => self::TAB_CONTENT,
        ]);

        $this->add_control('radius',
        [
            'type'        => 'number',
            'class'       => 'lsd-admin-input',
            'label'       => esc_html__('Radius (m)', 'listdom'),
            'default'     => 0,
            'attributes'  => [
                'min' => 0,
                'step' => 1,
            ],
        ]);

        $this->add_control('shortcode',
        [
            'type'              => 'shortcodes',
            'class'             => 'lsd-admin-input',
            'label'             => esc_html__('Shortcode', 'listdom'),
            'show_empty'        => true,
            'only_archive_skins' => true,
        ]);

        $this->end_controls_section();
    }

    public function render(array $args = []): string
    {
        $listing_id = isset($args['listing_id']) ? (int) $args['listing_id'] : 0;
        if (!$listing_id) return '';

        $settings = isset($args['settings']) && is_array($args['settings']) ? $args['settings'] : [];
        $content_settings = isset($settings['content']) && is_array($settings['content']) ? $settings['content'] : [];

        $taxonomies = [];
        if (isset($content_settings['taxonomy']) && is_array($content_settings['taxonomy']))
        {
            $taxonomies = $content_settings['taxonomy'];
        }
        else
        {
            $taxonomies = [
                LSD_Base::TAX_CATEGORY => (int) ($content_settings['taxonomy_category'] ?? 1),
                LSD_Base::TAX_LOCATION => (int) ($content_settings['taxonomy_location'] ?? 1),
                LSD_Base::TAX_LABEL => (int) ($content_settings['taxonomy_label'] ?? 1),
                LSD_Base::TAX_TAG => (int) ($content_settings['taxonomy_tag'] ?? 1),
                LSD_Base::TAX_FEATURE => (int) ($content_settings['taxonomy_feature'] ?? 1),
            ];
        }

        $radius = isset($content_settings['radius']) ? (float) $content_settings['radius'] : 0;
        $shortcode = $content_settings['shortcode'] ?? '';

        $listing = new LSD_Entity_Listing($listing_id);
        $output = $listing->get_related_listings([
            'taxonomy' => $taxonomies,
            'radius' => $radius,
            'shortcode' => $shortcode,
        ]);

        if (trim((string) $output) === '') return '';

        $html = '<div class="lsd-template-element-related lsdtb-card-related">' . $output . '</div>';

        return apply_filters('lsd_template_element_related_output', $html, $this, $args);
    }
}
