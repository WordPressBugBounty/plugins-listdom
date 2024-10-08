<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom Integrations VC Class.
 *
 * @class LSD_Integrations_VC
 * @version    1.0.0
 */
class LSD_Integrations_VC extends LSD_Integrations
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
        // Visual Composer is not installed
        if (!function_exists('vc_map')) return false;

        add_action('vc_before_init', [$this, 'listdom']);
        return true;
    }

    public function listdom()
    {
        $shortcodes = get_posts([
            'post_type' => LSD_Base::PTYPE_SHORTCODE,
            'posts_per_page' => '-1',
            'meta_query' => [
                [
                    'key' => 'lsd_skin',
                    'value' => [
                        'singlemap',
                        'grid',
                        'list',
                        'listgrid',
                    ],
                    'compare' => 'IN',
                ],
            ],
        ]);

        $options = [];
        foreach ($shortcodes as $shortcode) $options[$shortcode->post_title] = $shortcode->ID;

        vc_map([
            'name' => esc_html__('Listdom', 'listdom'),
            'base' => 'listdom',
            'class' => '',
            'controls' => 'full',
            'category' => esc_html__('Content', 'listdom'),
            'params' => [
                [
                    'type' => 'dropdown',
                    'holder' => 'div',
                    'class' => '',
                    'heading' => esc_html__('Shortcode', 'listdom'),
                    'param_name' => 'id',
                    'value' => $options,
                    'description' => esc_html__("Select one of predefined shortcodes.", 'listdom'),
                ],
            ],
        ]);
    }
}
