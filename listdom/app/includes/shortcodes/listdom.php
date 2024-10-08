<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom Main Shortcode Class.
 *
 * @class LSD_Shortcodes_Listdom
 * @version    1.0.0
 */
class LSD_Shortcodes_Listdom extends LSD_Shortcodes
{
    /**
     * Constructor method
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function init()
    {
        add_shortcode('listdom', [$this, 'output']);
    }

    public function output($atts = [], $override = [])
    {
        $shortcode_id = isset($atts['id']) ? (int) $atts['id'] : 0;

        $atts = wp_parse_args($override, apply_filters('lsd_shortcode_atts', $this->parse($shortcode_id, $atts)));
        $skin = $atts['lsd_display']['skin'] ?? $this->get_default_skin();

        // Listdom Pre Shortcode
        $pre = apply_filters('lsd_pre_shortcode', '', $atts, 'listdom');
        if (trim($pre)) return $pre;

        return $this->skin($skin, $atts);
    }

    public function skin($skin, $atts)
    {
        // Get Skin Object
        $SKO = $this->SKO($skin);

        // Start the skin
        $SKO->start($atts);
        $SKO->after_start();

        // Generate the Query
        $SKO->query();

        // Apply Search
        $SKO->apply_search($_GET);

        // Fetch the listings
        $SKO->fetch();

        return $SKO->output();
    }

    public function widget($shortcode_id)
    {
        $atts = apply_filters('lsd_shortcode_atts', $this->parse($shortcode_id, [
            'id' => $shortcode_id,
            'html_class' => 'lsd-widget lsd-shortcode-widget',
            'widget' => true,
        ]));

        $skin = $atts['lsd_display']['skin'] ?? $this->get_default_skin();

        return $this->skin($skin, $atts);
    }

    public function embed($shortcode_id)
    {
        $atts = apply_filters('lsd_shortcode_atts', $this->parse($shortcode_id, [
            'id' => $shortcode_id,
            'html_class' => 'lsd-embed lsd-shortcode-embed',
            'embed' => true,
        ]));

        $skin = $atts['lsd_display']['skin'] ?? $this->get_default_skin();

        return $this->skin($skin, $atts);
    }

    public function SKO($skin)
    {
        if ($skin === 'singlemap') $SKO = new LSD_Skins_Singlemap();
        else if ($skin === 'list') $SKO = new LSD_Skins_List();
        else if ($skin === 'grid') $SKO = new LSD_Skins_Grid();
        else if ($skin === 'side') $SKO = new LSD_Skins_Side();
        else if ($skin === 'listgrid') $SKO = new LSD_Skins_Listgrid();
        else if ($skin === 'halfmap') $SKO = new LSD_Skins_Halfmap();
        else if ($skin === 'table') $SKO = new LSD_Skins_Table();
        else if ($skin === 'cover') $SKO = new LSD_Skins_Cover();
        else if ($skin === 'carousel') $SKO = new LSD_Skins_Carousel();
        else if ($skin === 'slider') $SKO = new LSD_Skins_Slider();
        else if ($skin === 'masonry') $SKO = new LSD_Skins_Masonry();
        else $SKO = new LSD_Skins_Singlemap();

        return $SKO;
    }
}
