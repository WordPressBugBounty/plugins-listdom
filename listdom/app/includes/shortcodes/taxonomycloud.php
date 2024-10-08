<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom Taxonomy Cloud Shortcode Class.
 *
 * @class LSD_Shortcodes_TaxonomyCloud
 * @version    1.0.0
 */
class LSD_Shortcodes_TaxonomyCloud extends LSD_Shortcodes
{
    protected $atts = [];
    protected $terms = [];

    /**
     * Constructor method
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function init()
    {
        add_shortcode('listdom_cloud', [$this, 'output']);
    }

    public function output($atts = [])
    {
        // Listdom Pre Shortcode
        $pre = apply_filters('lsd_pre_shortcode', '', $atts, 'listdom_cloud');
        if (trim($pre)) return $pre;

        // Set the Attributes
        $this->atts = $atts;

        // Terms
        $this->terms = $this->get_terms();

        // Generate output
        ob_start();
        include lsd_template('taxonomy-shortcodes/cloud.php');
        return LSD_Kses::element(ob_get_clean());
    }

    public function get_terms()
    {
        // Taxonomy
        $TX = $this->atts['taxonomy'] ?? LSD_Base::TAX_TAG;

        $args = [];

        // Hide Empty
        $args['hide_empty'] = $this->atts['hide_empty'] ?? false;

        // Filter by Parent
        if (isset($this->atts['parent']) && trim($this->atts['parent']))
        {
            $parent = $this->atts['parent'];

            /**
             * Client inserted term name instead of term ID
             * So we should convert it to ID first
             **/
            if (!is_numeric($parent))
            {
                $term = get_term_by('name', $parent, $TX);
                if (isset($term->term_id)) $parent = $term->term_id;
            }

            $args['parent'] = $parent;
        }

        // Term IDs
        if (isset($this->atts['ids']) && trim($this->atts['ids'])) $args['object_ids'] = explode(',', $this->atts['ids']);

        // Filter by Keyword
        if (isset($this->atts['search']) && trim($this->atts['search'])) $args['search'] = $this->atts['search'];

        // Order Options
        $args['orderby'] = $this->atts['orderby'] ?? 'name';

        if (isset($this->atts['order'])) $args['order'] = $this->atts['order'];
        else if ($args['orderby'] == 'name') $args['order'] = 'ASC';
        else $args['order'] = 'DESC';

        // Limit Options
        $args['number'] = $this->atts['limit'] ?? 8;

        // Get the terms
        return get_terms($TX, $args);
    }
}

