<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom Skins Side by Side Class.
 *
 * @class LSD_Skins_Side
 * @version    1.0.0
 */
class LSD_Skins_Side extends LSD_Skins
{
    public $skin = 'side';
    public $default_style = 'style1';
    public $layout_width = '4060';

    /**
     * Constructor method
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function after_start()
    {
        // Layout Width
        $this->layout_width = $this->skin_options['layout_width'] ?? '4060';
    }

    public function init()
    {
        add_action('wp_ajax_lsd_side_load_more', [$this, 'filter']);
        add_action('wp_ajax_nopriv_lsd_side_load_more', [$this, 'filter']);

        add_action('wp_ajax_lsd_side_sort', [$this, 'filter']);
        add_action('wp_ajax_nopriv_lsd_side_sort', [$this, 'filter']);
    }

    public function output()
    {
        // Pro is needed
        if (LSD_Base::isLite())
        {
            return LSD_Base::alert(
                LSD_Base::missFeatureMessage(
                    esc_html__('Side by Side Skin', 'listdom')
                ),
                'warning'
            );
        }

        return parent::output();
    }
}
