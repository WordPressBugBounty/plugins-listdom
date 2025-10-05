<?php

class LSD_Skins_Masonry extends LSD_Skins
{
    public $skin = 'masonry';
    public $default_style = 'style1';
    public $filter_by = LSD_Base::TAX_CATEGORY;
    public $filter_all_label;
    public $list_view = false;
    public $duration = 400;

    public function init()
    {
        add_action('wp_ajax_lsd_masonry_load_more', [$this, 'filter']);
        add_action('wp_ajax_nopriv_lsd_masonry_load_more', [$this, 'filter']);
    }

    public function after_start()
    {
        // Masonry Filter Option
        $this->filter_by = $this->skin_options['filter_by'] ?? LSD_Base::TAX_CATEGORY;

        // List View
        $this->list_view = isset($this->skin_options['list_view']) && $this->skin_options['list_view'];

        // Animation Duration
        $this->duration = $this->skin_options['duration'] ?? 400;

        // All Listings Tab Label
        $this->filter_all_label = $this->skin_options['filter_all_label'] ?? esc_html__('All', 'listdom');
    }

    public function filters(): string
    {
        $output = '<div class="lsd-masonry-filters"><a href="#" class="lsd-selected" data-filter="*">' . esc_html($this->filter_all_label) . '<div class="lsd-border lsd-color-m-bg"></div></a>';

        $include = [];
        if (isset($this->atts[$this->filter_by]) && trim($this->atts[$this->filter_by]))
        {
            $include = array_filter(array_map('intval', explode(',', $this->atts[$this->filter_by])));
        }

        $args = [
            'taxonomy'   => $this->filter_by,
            'hide_empty' => true,
        ];

        if (count($include)) $args['include'] = $include;

        $terms = get_terms($args);
        foreach ($terms as $term)
        {
            // Current term doesn't have listing in current results
            if (!$this->has_listing($term)) continue;

            $output .= '<a href="#" data-filter=".lsd-t' . esc_attr($term->term_id) . '">' . esc_html($term->name) . '<div class="lsd-border lsd-color-m-bg"></div></a>';
        }

        $output .= '</div>';
        return $output;
    }

    public function filters_classes($id): string
    {
        $output = '';
        $terms = wp_get_post_terms($id, $this->filter_by, [
            'hide_empty' => true,
        ]);

        foreach ($terms as $term) $output .= ' lsd-t' . esc_attr($term->term_id);
        return trim($output);
    }

    public function has_listing($term): bool
    {
        $has = false;
        foreach ($this->listings as $id)
        {
            if (has_term($term->term_id, $this->filter_by, $id))
            {
                $has = true;
                break;
            }
        }

        return $has;
    }
}
