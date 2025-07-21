<?php
namespace LSDPACVIS;

class Module extends Base
{
    public function init()
    {
        // Register Module
        add_filter('lsd_dashboard_modules', [$this, 'register']);

        // Output
        add_action('lsd_listing_details_metabox', [$this, 'output'], 10, 2);

        // Save Team
        add_action('save_post', [$this, 'save'], 50, 2);
    }

    public function register($modules)
    {
        // Offline Module
        $modules[] = ['label' => esc_html__('Visibility', 'listdom-visibility'), 'key' => 'visibility'];

        return $modules;
    }

    public function output($post, ?\LSD_Shortcodes_Dashboard $dashboard = null)
    {
        // Module is not enabled
        if ($dashboard && !$dashboard->is_enabled('visibility')) return;

        // Print the Module
        $this->include_html_file('module.php', [
            'parameters' => compact('post', 'dashboard'),
        ]);
    }

    public function save($post_id, $post)
    {
        // It's not a listing
        if ($post->post_type !== \LSD_Base::PTYPE_LISTING) return;

        // Nonce is not set!
        if (!isset($_POST['_lsdnonce'])) return;

        // Nonce is not valid!
        if (!wp_verify_nonce(sanitize_text_field($_POST['_lsdnonce']), 'lsd_listing_cpt')) return;

        // We don't need to do anything on post auto save
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

        // Get Listdom Data
        $lsd = $_POST['lsd'] ?? [];

        // Visibility Dates
        $visible_from = $lsd['visible_from'] ?? null;
        $visible_until = $lsd['visible_until'] ?? null;

        // Visible From
        if ($visible_from) update_post_meta($post_id, 'lsd_visible_from', strtotime($visible_from));
        else update_post_meta($post_id, 'lsd_visible_from', 0);

        // Visible Until
        if ($visible_until) update_post_meta($post_id, 'lsd_visible_until', strtotime($visible_until));
        else update_post_meta($post_id, 'lsd_visible_until', 0);

        // Update Listing Status
        if ($visible_from || $visible_until) (new Addon())->listing($post);
    }
}
