<?php
namespace LSDPACVIS;

class Addon extends Base
{
    // Set when a publisher explicitly puts an offline listing online from the dashboard.
    const OVERRIDE_META = 'lsd_visibility_override';
    const CLOCK_META = 'lsd_visibility_clock';
    const CLOCK_VERSION = 'local';
    const PUBLISH_APPROVED_META = 'lsd_visibility_publish_approved';

    protected function get_max_visits()
    {
        $settings = \LSD_Options::settings();
        
        $max_visits = $settings['visibility_max_visits'] ?? '';
        if (!is_numeric($max_visits)) return '';

        $max_visits = (int) $max_visits;
        return $max_visits >= 0 ? $max_visits : '';
    }

    public function form($default)
    {
        $subtab = isset($_GET['subtab']) ? sanitize_text_field($_GET['subtab']) : $default;
        $this->include_html_file('form.php', ['parameters' => compact('subtab')]);
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

        foreach ($listings as $listing)
        {
            $this->migrate_legacy_dates($listing->ID);
            $can_publish = (bool) get_post_meta($listing->ID, self::PUBLISH_APPROVED_META, true);
            $this->listing($listing, $can_publish);
        }
    }

    public function listing(\WP_Post $listing, ?bool $can_publish = null)
    {
        // Visibility only manages published and offline listings.
        if (!in_array($listing->post_status, [\LSD_Base::STATUS_PUBLISHED, \LSD_Base::STATUS_OFFLINE], true)) return;

        $this->migrate_legacy_dates($listing->ID);
        if ($can_publish === null) $can_publish = (bool) get_post_meta($listing->ID, self::PUBLISH_APPROVED_META, true);

        $evidence = $this->inspection($listing);
        $visible = $evidence['visible'];

        // Listing Status
        $status = $visible ? ($can_publish ? \LSD_Base::STATUS_PUBLISHED : \LSD_Base::STATUS_PENDING) : \LSD_Base::STATUS_OFFLINE;

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

    /** Pure rule evaluation shared by lifecycle saves and read-only diagnostics. */
    public static function evaluate(int $now, int $from, int $until, bool $override, int $visits, $maximum): bool
    {
        if ($override) return true;
        if (is_numeric($maximum) && $maximum && $visits >= (int) $maximum) return false;
        if ($from && $from > $now) return false;
        if ($until && $until <= $now) return false;
        return true;
    }

    /** Interpret legacy date-only values without persisting a migration. */
    public static function date_value(int $timestamp, bool $local): int
    {
        return !$local && $timestamp && $timestamp % DAY_IN_SECONDS === 0
            ? (int) \LSD_Base::strtotime(gmdate('Y-m-d', $timestamp)) : $timestamp;
    }

    /** Read evidence; diagnostic callers supply a reviewed maximum rather than running filters. */
    public function inspection(\WP_Post $listing, bool $diagnostic = false, $maximum = null): array
    {
        $local = get_post_meta($listing->ID, self::CLOCK_META, true) === self::CLOCK_VERSION;
        $from = self::date_value((int) get_post_meta($listing->ID, 'lsd_visible_from', true), $local);
        $until = self::date_value((int) get_post_meta($listing->ID, 'lsd_visible_until', true), $local);
        $override = (bool) get_post_meta($listing->ID, self::OVERRIDE_META, true);
        $now = (int) current_time('timestamp', true);
        if (!$diagnostic && !$override) $maximum = apply_filters('lsd_visibility_max_visits', $this->get_max_visits(), $listing);
        $visits = $diagnostic ? (int) get_post_meta($listing->ID, 'lsd_visits', true) : ($override || !is_numeric($maximum) || !$maximum ? 0 : (int) (new \LSD_Entity_Listing($listing))->get_visits());
        return ['from' => $from, 'until' => $until, 'override' => $override, 'visits' => $visits,
            'maximum' => is_numeric($maximum) ? (int) $maximum : null, 'evaluated_at' => $now,
            'visible' => self::evaluate($now, $from, $until, $override, $visits, $maximum)];
    }

    public function put_online(\WP_Post $listing, $action)
    {
        if ($action !== 'publish') return;

        update_post_meta($listing->ID, self::OVERRIDE_META, 1);
        update_post_meta($listing->ID, self::PUBLISH_APPROVED_META, 1);
    }

    public function dashboard_publish(\WP_Post $listing)
    {
        update_post_meta($listing->ID, self::PUBLISH_APPROVED_META, 1);

        // Apply visibility rules immediately after an explicit dashboard publish.
        $this->listing($listing, true);
    }

    public function renewed($listing_id)
    {
        $listing = get_post($listing_id);
        if (!$listing instanceof \WP_Post) return;

        $this->listing($listing);
    }

    public function publication_transition($new_status, $old_status, \WP_Post $listing)
    {
        if ($listing->post_type !== \LSD_Base::PTYPE_LISTING) return;

        // Authorized schedules must retain approval when WordPress publishes
        // them later without a logged-in user.
        if ($new_status === \LSD_Base::STATUS_SCHEDULED && current_user_can('publish_posts'))
        {
            update_post_meta($listing->ID, self::PUBLISH_APPROVED_META, 1);
        }

        // Direct publication is an approval decision. An unattended scheduled
        // publication must keep relying on the approval recorded when scheduled.
        if ($new_status === \LSD_Base::STATUS_PUBLISHED && ($old_status !== \LSD_Base::STATUS_SCHEDULED || current_user_can('publish_posts')))
        {
            update_post_meta($listing->ID, self::PUBLISH_APPROVED_META, 1);
        }
    }

    public function migrate_legacy_dates($listing_id)
    {
        if (get_post_meta($listing_id, self::CLOCK_META, true) === self::CLOCK_VERSION) return;

        foreach (['lsd_visible_from', 'lsd_visible_until'] as $meta_key)
        {
            $timestamp = (int) get_post_meta($listing_id, $meta_key, true);
            if (!$timestamp || $timestamp % DAY_IN_SECONDS !== 0) continue;

            update_post_meta($listing_id, $meta_key, self::date_value($timestamp, false));
        }

        if (!metadata_exists('post', $listing_id, self::PUBLISH_APPROVED_META))
        {
            update_post_meta($listing_id, self::PUBLISH_APPROVED_META, $this->legacy_publish_approved($listing_id) ? 1 : 0);
        }

        update_post_meta($listing_id, self::CLOCK_META, self::CLOCK_VERSION);
    }

    private function legacy_publish_approved($listing_id): bool
    {
        $listing = get_post($listing_id);
        $subscription_id = (int) get_post_meta($listing_id, 'lsd_subscription', true);
        if ($subscription_id && $listing instanceof \WP_Post && class_exists('\LSDPACSUB\Subscription'))
        {
            if (!get_post($subscription_id)) return false;

            $subscription = new \LSDPACSUB\Subscription($subscription_id);
            if (!$subscription->validate($listing->post_author, 'edit')) return false;
        }

        if ($listing instanceof \WP_Post && $listing->post_status === \LSD_Base::STATUS_PUBLISHED) return true;

        $author_id = (int) get_post_field('post_author', $listing_id);
        if ($author_id && user_can($author_id, 'publish_posts')) return true;

        $settings = \LSD_Options::settings();
        if (($settings['dashboard_listing_status'] ?? '') === \LSD_Base::STATUS_PUBLISHED) return true;

        if ($listing instanceof \WP_Post && apply_filters('lsd_dashboard_listing_status', \LSD_Base::STATUS_PENDING, [
            'id' => $listing_id,
            'listing' => $listing,
            'subscription_id' => get_post_meta($listing_id, 'lsd_subscription', true),
        ]) === \LSD_Base::STATUS_PUBLISHED) return true;

        return (bool) get_post_meta($listing_id, 'lsd_offlined_at', true);
    }
}
