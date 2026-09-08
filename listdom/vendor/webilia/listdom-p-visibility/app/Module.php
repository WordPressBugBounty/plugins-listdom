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

        // Validate Visibility Dates
        add_filter('lsd_dashboard_validate_request', [$this, 'validate'], 20);

        // Save Visibility from the Frontend Dashboard
        add_action('wp_ajax_lsd_dashboard_listing_visibility', [$this, 'dashboard_action']);
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
        if ($dashboard && !$dashboard->is_enabled('visibility', $post->ID)) return;

        (new Addon())->migrate_legacy_dates($post->ID);

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
        if (!array_key_exists('visible_from', $lsd) && !array_key_exists('visible_until', $lsd)) return;

        // Visibility Dates
        $visible_from = $lsd['visible_from'] ?? '';
        $visible_until = $lsd['visible_until'] ?? '';
        $visible_from_timestamp = $this->timestamp($visible_from);
        $visible_until_timestamp = $this->timestamp($visible_until);

        if ($visible_from_timestamp === false || $visible_until_timestamp === false) return;
        if ($visible_from_timestamp && $visible_until_timestamp && $visible_until_timestamp <= $visible_from_timestamp) return;

        // Visible From
        (new Addon())->migrate_legacy_dates($post_id);
        delete_post_meta($post_id, Addon::OVERRIDE_META);
        update_post_meta($post_id, 'lsd_visible_from', $visible_from_timestamp);

        // Visible Until
        update_post_meta($post_id, 'lsd_visible_until', $visible_until_timestamp);

        update_post_meta($post_id, Addon::CLOCK_META, Addon::CLOCK_VERSION);
        $publish_approved = $post->post_status === \LSD_Base::STATUS_PUBLISHED
            || (bool) get_post_meta($post_id, Addon::PUBLISH_APPROVED_META, true);
        if ($publish_approved) update_post_meta($post_id, Addon::PUBLISH_APPROVED_META, 1);
        else delete_post_meta($post_id, Addon::PUBLISH_APPROVED_META);

        // Update Listing Status
        (new Addon())->listing($post, $publish_approved);
    }

    public function validate($valid)
    {
        if ($valid !== true) return $valid;

        $lsd = isset($_POST['lsd']) && is_array($_POST['lsd']) ? wp_unslash($_POST['lsd']) : [];
        if (!array_key_exists('visible_from', $lsd) && !array_key_exists('visible_until', $lsd)) return $valid;

        $visible_from = $this->timestamp($lsd['visible_from'] ?? '');
        $visible_until = $this->timestamp($lsd['visible_until'] ?? '');

        if ($visible_from === false || $visible_until === false) return esc_html__('Please enter valid visibility dates.', 'listdom-visibility');
        if ($visible_from && $visible_until && $visible_until <= $visible_from) return esc_html__('Visible Until must be later than Visible From.', 'listdom-visibility');

        return $valid;
    }

    public function dashboard_action()
    {
        if (!isset($_POST['_lsdnonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['_lsdnonce'])), 'lsd_dashboard')) $this->response(['success' => 0, 'message' => esc_html__('Security nonce is not valid!', 'listdom-visibility')]);

        $id = isset($_POST['id']) ? absint(wp_unslash($_POST['id'])) : 0;
        $listing = $id ? get_post($id) : null;
        $visible_from = isset($_POST['visible_from']) ? sanitize_text_field(wp_unslash($_POST['visible_from'])) : '';
        $visible_until = isset($_POST['visible_until']) ? sanitize_text_field(wp_unslash($_POST['visible_until'])) : '';

        if (!$listing instanceof \WP_Post || $listing->post_type !== \LSD_Base::PTYPE_LISTING || $listing->post_status !== \LSD_Base::STATUS_OFFLINE) $this->response(['success' => 0]);

        if (!current_user_can('edit_post', $id) || ((int) $listing->post_author !== get_current_user_id() && !current_user_can('edit_others_posts'))) $this->response(['success' => 0, 'message' => esc_html__('You are not allowed to update this listing.', 'listdom-visibility')]);

        $valid = apply_filters('lsd_dashboard_validate_request', true);
        if ($valid !== true) $this->response(['success' => 0, 'message' => $valid]);

        $settings = \LSD_Options::settings();
        $visibility_setting = $settings['submission_module']['visibility'] ?? null;
        $visibility_enabled = $visibility_setting === null || (bool) $visibility_setting;
        if ($visibility_enabled && $visibility_setting == 2 && !current_user_can('edit_others_pages')) $visibility_enabled = false;
        if (!$visibility_enabled || !apply_filters('lsd_dashboard_modules_status', true, 'visibility', $id)) $this->response(['success' => 0, 'message' => esc_html__('You are not allowed to update this listing.', 'listdom-visibility')]);

        $visible_from_timestamp = $this->timestamp($visible_from);
        $visible_until_timestamp = $this->timestamp($visible_until);
        if ($visible_from_timestamp === false || $visible_until_timestamp === false) $this->response(['success' => 0, 'message' => esc_html__('Please enter valid visibility dates.', 'listdom-visibility')]);
        if ($visible_from_timestamp && $visible_until_timestamp && $visible_until_timestamp <= $visible_from_timestamp) $this->response(['success' => 0, 'message' => esc_html__('Visible Until must be later than Visible From.', 'listdom-visibility')]);

        (new Addon())->migrate_legacy_dates($id);
        update_post_meta($id, 'lsd_visible_from', $visible_from_timestamp);
        update_post_meta($id, 'lsd_visible_until', $visible_until_timestamp);
        delete_post_meta($id, Addon::OVERRIDE_META);

        update_post_meta($id, Addon::CLOCK_META, Addon::CLOCK_VERSION);
        $publish_approved = (bool) get_post_meta($id, Addon::PUBLISH_APPROVED_META, true);
        if ($publish_approved) update_post_meta($id, Addon::PUBLISH_APPROVED_META, 1);
        else delete_post_meta($id, Addon::PUBLISH_APPROVED_META);

        (new Addon())->listing($listing, $publish_approved);
        $updated_listing = get_post($id);
        if ($updated_listing instanceof \WP_Post) do_action('lsd_listing_quick_updated', $updated_listing);

        $this->response(['success' => 1]);
    }

    private function timestamp($date)
    {
        if ($date === '') return 0;

        $date_time = \DateTimeImmutable::createFromFormat('!Y-m-d', $date, wp_timezone());
        if (!$date_time || $date_time->format('Y-m-d') !== $date) return false;

        return $date_time->getTimestamp();
    }
}
