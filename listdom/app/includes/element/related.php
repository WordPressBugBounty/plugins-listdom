<?php

class LSD_Element_Related extends LSD_Element
{
    public $key = 'related';
    public $label;
    public $args;

    public function __construct(array $args = [])
    {
        parent::__construct();

        $this->label = esc_html__('Related', 'listdom');
        $this->inline_title = true;
        $this->args = $args;
    }

    public function get($post_id = null)
    {
        if (is_null($post_id))
        {
            global $post;
            $post_id = $post->ID;
        }

        // Generate output
        ob_start();
        include lsd_template('elements/related.php');

        return $this->content(
            ob_get_clean(),
            $this,
            [
                'post_id' => $post_id,
            ]
        );
    }

    protected function general_settings(array $data): string
    {
        $taxonomies = [
            LSD_Base::TAX_CATEGORY => esc_html__('Category', 'listdom'),
            LSD_Base::TAX_LOCATION => esc_html__('Location', 'listdom'),
            LSD_Base::TAX_LABEL => esc_html__('Label', 'listdom'),
            LSD_Base::TAX_TAG => esc_html__('Tag', 'listdom'),
            LSD_Base::TAX_FEATURE => esc_html__('Feature', 'listdom'),
        ];

        $output = '<div class="lsd-w-full"><label>' . esc_html__('Taxonomies', 'listdom') . '</label>';
        $output .= '<div class="lsd-taxonomy-checkboxes">';

        foreach ($taxonomies as $value => $label)
        {
            $output .= '<div>';
            $output .= LSD_Form::label([
                'title' => $label,
                'for' => 'lsd_elements_' . esc_attr($this->key) . '_taxonomy_' . esc_attr($value),
            ]);
            $output .= LSD_Form::switcher([
                'id' => 'lsd_elements_' . esc_attr($this->key) . '_taxonomy_' . esc_attr($value),
                'name' => 'lsd[elements][' . esc_attr($this->key) . '][taxonomy][' . esc_attr($value) . ']',
                'value' => $data['taxonomy'][$value] ?? 1,
            ]);
            $output .= '</div>';
        }
        $output .= '</div></div>';

        $output .= '<div>';
        $output .= LSD_Form::label([
            'title' => esc_html__('Radius (m)', 'listdom'),
            'for' => 'lsd_elements_' . esc_attr($this->key) . '_radius',
        ]);
        $output .= LSD_Form::input([
            'id' => 'lsd_elements_' . esc_attr($this->key) . '_radius',
            'name' => 'lsd[elements][' . esc_attr($this->key) . '][radius]',
            'value' => $data['radius'] ?? '',
            'placeholder' => esc_html__('500m', 'listdom'),
        ]) . '</div>';

        $output .= '<div>';
        $output .= LSD_Form::label([
            'title' => esc_html__('Shortcode', 'listdom'),
            'for' => 'lsd_elements_' . esc_attr($this->key) . '_shortcode',
        ]);
        $output .= LSD_Form::shortcodes([
            'id' => 'lsd_elements_' . esc_attr($this->key) . '_shortcode',
            'name' => 'lsd[elements][' . esc_attr($this->key) . '][shortcode]',
            'value' => $data['shortcode'] ?? '',
            'show_empty' => true,
            'only_archive_skins' => true,
        ]);
        $output .= '</div>';

        return $output;
    }

    /**
     * Get taxonomy term IDs assigned to a post.
     *
     * @param int $post_id
     * @return array Associative array of taxonomy => array of term IDs
     */
    public function get_post_taxonomies_id(int $post_id): array
    {
        $taxonomy_term_ids = [];

        $taxonomies = get_object_taxonomies(get_post_type($post_id));

        foreach ($taxonomies as $taxonomy)
        {
            $terms = get_the_terms($post_id, $taxonomy);
            if (!empty($terms) && !is_wp_error($terms))
            {
                foreach ($terms as $term) $taxonomy_term_ids[$taxonomy][] = $term->term_id;
            }
        }

        return $taxonomy_term_ids;
    }

    /**
     * Filter taxonomy terms by allowed taxonomies.
     *
     * @param array $terms Associative array taxonomy => term IDs
     * @param array $taxonomies_activation
     * @return array Filtered associative array with only allowed taxonomies
     */
    public function filter_terms_by_taxonomies(array $terms, array $taxonomies_activation): array
    {
        $filtered = [];

        foreach ($taxonomies_activation as $taxonomy => $is_enabled)
        {
            if ($is_enabled && !empty($terms[$taxonomy]))
            {
                $filtered[$taxonomy] = $terms[$taxonomy];
            }
        }

        return $filtered;
    }
}
