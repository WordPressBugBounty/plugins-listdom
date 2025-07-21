<?php
namespace LSDPACVIS;

class Addon extends Base
{
    protected $options;

    public function __construct()
    {
        $this->options = \LSD_Options::addons('visibility');
    }

    public function form()
    {
        $this->include_html_file('form.php');
    }

    public function cron()
    {
        // Get Listings which have visibility options
        $listings = get_posts([
            'post_type' => \LSD_Base::PTYPE_LISTING,
            'post_status' => ['publish', \LSD_Base::STATUS_OFFLINE],
            'posts_per_page' => -1,
            'meta_query' => [
                'relation' => 'OR',
                [
                    'key' => 'lsd_visible_from',
                    'value' => 0,
                    'compare' => '!=',
                ],
                [
                    'key' => 'lsd_visible_until',
                    'value' => 0,
                    'compare' => '!=',
                ],
            ],
        ]);

        foreach ($listings as $listing) $this->listing($listing);
    }

    public function listing(\WP_Post $listing)
    {
        // Current Time
        $now = current_time('timestamp');

        $visible_from = (int) get_post_meta($listing->ID, 'lsd_visible_from', true);
        $visible_until = (int) get_post_meta($listing->ID, 'lsd_visible_until', true);

        // Max Visits
        $max_visits = $this->options['max_visits'] ?? '';
        $max_visits = apply_filters('lsd_visibility_max_visits', $max_visits, $listing);
        $visible = true;

        // Listing Visits
        if (is_numeric($max_visits) && $max_visits)
        {
            $visits = (new \LSD_Entity_Listing($listing))->get_visits();
            if ($visits >= (int) $max_visits) $visible = false;
        }

        if ($visible && $visible_from && $visible_from > $now) $visible = false;
        if ($visible && $visible_until && $visible_until <= $now) $visible = false;

        // Listing Status
        $status = $visible ? 'publish' : \LSD_Base::STATUS_OFFLINE;

        if ($listing->post_status !== $status)
        {
            wp_update_post([
                'ID' => $listing->ID,
                'post_status' => $status,
            ]);

            if ($status === \LSD_Base::STATUS_OFFLINE)
            {
                update_post_meta($listing->ID, 'lsd_offlined_at', current_time('Y-m-d H:i:s'));
                do_action('lsd_listing_offlined', $listing->ID);
            }
        }
    }
}
