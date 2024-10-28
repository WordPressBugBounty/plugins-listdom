<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom Taxonomy Shortcode Class.
 *
 * @class LSD_Shortcodes_Taxonomy
 * @version    1.0.0
 */
class LSD_Shortcodes_Taxonomy extends LSD_Shortcodes
{
    protected $TX;
    protected $id;
    protected $atts = [];
    protected $terms = [];
    protected $default_style = 'clean';
    protected $valid_styles = [];
    protected $columns = 1;
    protected $hierarchical = false;

    /**
     * Constructor method
     */
    public function __construct()
    {
        parent::__construct();

        // Unique ID
        $this->id = LSD_id::get(mt_rand(100, 999));
    }

    public function init()
    {
    }

    public function output($atts = [])
    {
        // Listdom Pre Shortcode
        $pre = apply_filters('lsd_pre_shortcode', '', $atts, 'listdom-tax-terms');
        if (trim($pre)) return $pre;

        // If taxonomy is invalid
        if (!trim($this->TX)) return $this->alert(esc_html__('Taxonomy is invalid!', 'listdom'), 'warning');

        // Shortcode attributes
        $this->atts = is_array($atts) ? $atts : [];

        // Shortcode Style
        $style = $this->atts['style'] ?? $this->default_style;

        // The style is invalid!
        if (!in_array($style, $this->valid_styles))
        {
            $valid_styles = '';
            foreach ($this->valid_styles as $valid_style) $valid_styles .= '<strong>' . esc_html($valid_style) . '</strong>, ';

            return $this->alert(sprintf(esc_html__('Style is invalid! Valid styles are: %s', 'listdom'), trim($valid_styles, ', ')), 'warning');
        }

        // Hierarchical
        $this->hierarchical = isset($this->atts['hierarchical']) && $this->atts['hierarchical'];

        // Parent
        $parent = $this->hierarchical ? (isset($this->atts['parent']) && trim($this->atts['parent']) != '' ? $this->atts['parent'] : 0) : null;

        // Terms
        $this->terms = $this->get_terms($parent);

        // Output
        switch ($style)
        {
            case 'image';
                $output = $this->image();
                break;

            case 'simple';
                $output = $this->simple();
                break;

            case 'carousel';
                $output = $this->carousel();
                break;

            default:
                $output = $this->clean();
                break;
        }

        // Shortcode Output
        return $output;
    }

    public function get_terms($parent = null)
    {
        $args = [];

        // Hide Empty
        $args['hide_empty'] = $this->atts['hide_empty'] ?? false;

        // Filter by Parent
        if (is_null($parent) && isset($this->atts['parent']) && trim($this->atts['parent']) != '')
        {
            $parent = $this->atts['parent'];

            /**
             * Client inserted term name instead of term ID
             * So we should convert it to ID first
             **/
            if (!is_numeric($parent))
            {
                $term = get_term_by('name', $parent, $this->TX);
                if (isset($term->term_id)) $parent = $term->term_id;
            }
        }

        if (!is_null($parent)) $args['parent'] = $parent;

        // Term IDs
        if (isset($this->atts['ids']) && trim($this->atts['ids'])) $args['include'] = explode(',', $this->atts['ids']);

        // Filter by Keyword
        if (isset($this->atts['search']) && trim($this->atts['search'])) $args['search'] = $this->atts['search'];

        // Order Options
        $args['orderby'] = $this->atts['orderby'] ?? 'name';
        $args['order'] = $this->atts['order'] ?? 'ASC';

        // Limit Options
        $args['number'] = $this->atts['limit'] ?? 8;

        // Get the terms
        return get_terms($this->TX, $args);
    }

    public function image(): string
    {
        // Generate output
        ob_start();
        include lsd_template('taxonomy-shortcodes/image.php');
        return LSD_Kses::element(ob_get_clean());
    }

    public function simple(): string
    {
        // Generate output
        ob_start();
        include lsd_template('taxonomy-shortcodes/simple.php');
        return LSD_Kses::element(ob_get_clean());
    }

    public function clean(): string
    {
        // Generate output
        ob_start();
        include lsd_template('taxonomy-shortcodes/clean.php');
        return LSD_Kses::element(ob_get_clean());
    }

    public function carousel(): string
    {
        // Generate output
        ob_start();
        include lsd_template('taxonomy-shortcodes/carousel.php');
        return LSD_Kses::element(ob_get_clean());
    }
}
