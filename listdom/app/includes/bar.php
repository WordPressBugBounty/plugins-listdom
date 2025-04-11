<?php

class LSD_Bar extends LSD_Base
{
    /**
     * The single instance of the class.
     *
     * @var LSD_id
     * @since 1.0.0
     */
    protected static $instance = null;

    /**
     * All Listdom stuff that are loaded
     * @var array
     */
    protected static $list = [];

    /**
     * LSD_Bar Instance.
     * @return LSD_Bar
     * @since 1.0.0
     * @static
     */
    public static function instance(): LSD_Bar
    {
        // Get an instance of Class
        if (is_null(self::$instance)) self::$instance = new self();

        // Return the instance
        return self::$instance;
    }

    /**
     * Listdom Constructor.
     */
    protected function __construct()
    {
    }

    public function init()
    {
        add_action('wp_footer', [$this, 'menus']);
    }

    public function menus()
    {
        // Only Frontend
        if (is_admin()) return;

        // Only Admin User
        if (!current_user_can('manage_options')) return;

        // No Listdom Item
        if (!count(self::$list)) return;

        // Main
        $main = new LSD_Main();

        // Unique List
        $list = array_unique(self::$list);

        $items = '';
        foreach ($list as $id)
        {
            $post = get_post($id);
            if (!($post instanceof WP_Post)) continue;

            $url = get_edit_post_link($post);
            $type = esc_html__('Shortcode', 'listdom');

            // Elementor
            if (class_exists(LSDPACELM\Base::class) && $post->post_type === LSDPACELM\Base::PTYPE_DETAILS)
            {
                $url = $main->update_qs_var('action', 'elementor', $url);
                $template_type = get_post_meta($id, 'lsd_type', true);

                if ($template_type === 'card') $type = esc_html__('Listing Card', 'listdom');
                else if ($template_type === 'infowindow') $type = esc_html__('Infowindow', 'listdom');
                else $type = esc_html__('Single Listing', 'listdom');
            }

            // Divi
            if (class_exists(LSDPACDIV\Base::class) && $post->post_type === LSDPACDIV\Base::PTYPE_DETAILS)
            {
                $template_type = get_post_meta($id, 'lsd_type', true);

                if ($template_type === 'card') $type = esc_html__('Listing Card', 'listdom');
                else if ($template_type === 'infowindow') $type = esc_html__('Infowindow', 'listdom');
                else $type = esc_html__('Single Listing', 'listdom');
            }

            // Search
            if ($post->post_type === LSD_Base::PTYPE_SEARCH) $type = esc_html__('Search', 'listdom');

            $items .= '<li class="lsd-bar-'.esc_attr($post->post_type).'" id="lsd-bar-'.esc_attr($id).'"><a class="ab-item" href="'.esc_url_raw($url).'"><span class="lsd-bar-title">'.esc_html($post->post_title).'</span><span class="lsd-bar-type">'.esc_html($type).'</span></a></li>';
        }

        // Listdom Bar Menu
        $menu = '<li id="wp-admin-bar-listdom" class="menupop"><a aria-haspopup="true" class="ab-item" href="'.admin_url('admin.php?page=listdom').'"><span class="lsd-bar-link-title"><img src="'.$main->lsd_asset_url('img/listdom-icon.svg').'" alt=""><span>Listdom</span></span></a><div class="ab-sub-wrapper"><ul class="ab-submenu" id="wp-admin-bar-listdom-default">'.$items.'</ul></div></li>';

        // Add to WP Bar
        echo "<script>jQuery('#wp-admin-bar-root-default').append('".$menu."')</script>";
    }

    public function add(int $id)
    {
        self::$list[] = $id;
    }
}
