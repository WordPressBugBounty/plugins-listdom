<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom Integrations KC Class.
 *
 * @class LSD_Integrations_KC
 * @version    1.0.0
 */
class LSD_Integrations_KC extends LSD_Integrations
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
        // King Composer is not installed
        if (!function_exists('kc_add_map')) return false;

        add_action('init', [$this, 'listdom']);
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

        kc_add_map([
            'listdom' => [
                'name' => esc_html__('Listdom', 'listdom'),
                'category' => esc_html__('Content', 'listdom'),
                'params' => [
                    'General' => [
                        [
                            'name' => 'id',
                            'label' => esc_html__('Shortcode', 'listdom'),
                            'type' => 'select',
                            'options' => $options,
                            'description' => esc_html__('Select one of predefined shortcodes.', 'listdom'),
                        ],
                    ],
                ],
            ],
        ]);
    }
}
