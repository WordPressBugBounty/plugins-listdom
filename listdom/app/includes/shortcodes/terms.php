<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom Terms Shortcode Class.
 *
 * @class LSD_Shortcodes_Terms
 * @version    1.0.0
 */
class LSD_Shortcodes_Terms extends LSD_Shortcodes
{
    protected $atts = [];

    /**
     * Constructor method
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function init()
    {
        add_shortcode('listdom_terms', [$this, 'output']);
    }

    public function output($atts = [])
    {
        // Listdom Pre Shortcode
        $pre = apply_filters('lsd_pre_shortcode', '', $atts, 'listdom_terms');
        if (trim($pre)) return $pre;

        // Set the Attributes
        $this->atts = $atts;

        // Parameters
        $style = isset($this->atts['dropdown']) && $this->atts['dropdown'] ? 'dropdown' : 'list';
        $hierarchical = isset($this->atts['hierarchical']) && $this->atts['hierarchical'];
        $show_count = isset($this->atts['show_count']) && $this->atts['show_count'];
        $taxonomy = isset($this->atts['taxonomy']) && in_array($this->atts['taxonomy'], $this->taxonomies()) ? $this->atts['taxonomy'] : LSD_Base::TAX_CATEGORY;
        $id = LSD_id::get($this->atts['id'] ?? mt_rand(100, 999));

        // Generate output
        ob_start();

        switch ($style)
        {
            case 'dropdown':

                echo '<form action="' . esc_url(home_url()) . '" method="get" class="lsd-terms-dropdown">';

                wp_dropdown_categories([
                    'show_option_none' => esc_html__('Select Item', 'listdom'),
                    'id' => $id,
                    'name' => $taxonomy,
                    'orderby' => 'name',
                    'hierarchical' => $hierarchical,
                    'show_count' => $show_count,
                    'taxonomy' => $taxonomy,
                ]);

                echo '</form>';

                break;
            default:

                echo '<ul>';

                wp_list_categories([
                    'orderby' => 'name',
                    'title_li' => '',
                    'hierarchical' => $hierarchical,
                    'show_count' => $show_count,
                    'taxonomy' => $taxonomy,
                ]);

                echo '</ul>';
        }

        return LSD_Kses::form(ob_get_clean());
    }
}
