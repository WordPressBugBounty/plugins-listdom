<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom Skins Masonry Class.
 *
 * @class LSD_Skins_Masonry
 * @version    1.0.0
 */
class LSD_Skins_Masonry extends LSD_Skins
{
    public $skin = 'masonry';
    public $default_style = 'style1';
    public $filter_by = LSD_Base::TAX_CATEGORY;
    public $list_view = false;

    /**
     * Constructor method
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function init()
    {
    }

    public function after_start()
    {
        // Masonry Filter Option
        $this->filter_by = $this->skin_options['filter_by'] ?? LSD_Base::TAX_CATEGORY;

        // List View
        $this->list_view = isset($this->skin_options['list_view']) && $this->skin_options['list_view'];
    }

    public function filters(): string
    {
        $output = '<div class="lsd-masonry-filters"><a href="#" class="lsd-selected" data-filter="*">' . esc_html__('All', 'listdom') . '<div class="lsd-border lsd-color-m-bg"></div></a>';
        $terms = get_terms($this->filter_by, [
            'hide_empty' => true,
            'include' => isset($this->atts[$this->filter_by]) && trim($this->atts[$this->filter_by]) ? $this->atts[$this->filter_by] : '',
        ]);

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
