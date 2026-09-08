<?php
namespace LSDPACVIS;

class IX extends Base
{
    public function init()
    {
        /**
         * Import
         */

        // Visibility Field
        add_filter('lsd_ix_listdom_fields', [$this, 'mapping']);

        // Import Data
        add_action('lsd_listing_imported', [$this, 'import'], 20, 2);

        // Import Finished
        add_action('lsd_import_finished', [$this, 'after']);

        /**
         * Export
         */

        // Add Column
        add_filter('lsd_export_columns', [$this, 'columns'], 99);

        // Populate Data
        add_filter('lsd_export_data', [$this, 'data'], 99, 2);
    }

    public function mapping($fields)
    {
        // Default Value
        $default = new \LSD_IX_Mapping_Default();

        $fields['lsd_visible_from'] = [
            'label' => esc_html__('Visible From', 'listdom-visibility'),
            'type' => 'date',
            'mandatory' => false,
            'description' => esc_html__("A date field should be mapped.", 'listdom-visibility'),
            'default' => [$default, 'date'],
        ];

        $fields['lsd_visible_until'] = [
            'label' => esc_html__('Visible Until', 'listdom-visibility'),
            'type' => 'date',
            'mandatory' => false,
            'description' => esc_html__("A date field should be mapped.", 'listdom-visibility'),
            'default' => [$default, 'date'],
        ];

        return $fields;
    }

    public function import($listing_id, $mapped)
    {
        // Metas
        $metas = isset($mapped['meta']) && is_array($mapped['meta']) ? $mapped['meta'] : [];

        // Listing Visibility
        $visible_from = isset($metas['lsd_visible_from']) && trim($metas['lsd_visible_from']) ? $metas['lsd_visible_from'] : 0;
        $visible_until = isset($metas['lsd_visible_until']) && trim($metas['lsd_visible_until']) ? $metas['lsd_visible_until'] : 0;

        delete_post_meta($listing_id, Addon::OVERRIDE_META);
        update_post_meta($listing_id, 'lsd_visible_from', ($visible_from ? \LSD_Base::strtotime($visible_from) : 0));
        update_post_meta($listing_id, 'lsd_visible_until', ($visible_until ? \LSD_Base::strtotime($visible_until) : 0));
        update_post_meta($listing_id, Addon::CLOCK_META, Addon::CLOCK_VERSION);

        $post_status = get_post_status($listing_id);
        $author_id = (int) get_post_field('post_author', $listing_id);
        $listing = get_post($listing_id);
        $auto_confirmed = $post_status === \LSD_Base::STATUS_SCHEDULED && $listing instanceof \WP_Post
            && apply_filters('lsd_dashboard_listing_status', \LSD_Base::STATUS_PENDING, [
                'id' => $listing_id,
                'listing' => $listing,
                'subscription_id' => get_post_meta($listing_id, 'lsd_subscription', true),
            ]) === \LSD_Base::STATUS_PUBLISHED;
        if ($post_status === \LSD_Base::STATUS_PUBLISHED || ($post_status === \LSD_Base::STATUS_SCHEDULED && (($author_id && user_can($author_id, 'publish_posts')) || $auto_confirmed)))
            update_post_meta($listing_id, Addon::PUBLISH_APPROVED_META, 1);
        else delete_post_meta($listing_id, Addon::PUBLISH_APPROVED_META);
    }

    public function after()
    {
        // Make Listings Offline
        (new Addon())->cron();
    }

    public function columns($columns)
    {
        // Visible From Column
        $columns[] = esc_html__('Visible From', 'listdom-visibility');

        // Visible Until Column
        $columns[] = esc_html__('Visible Until', 'listdom-visibility');

        return $columns;
    }

    public function data($data, $listing_id)
    {
        (new Addon())->migrate_legacy_dates($listing_id);

        $visible_from = get_post_meta($listing_id, 'lsd_visible_from', true);
        $data[] = $visible_from ? lsd_date('Y-m-d', $visible_from) : '';

        $visible_until = get_post_meta($listing_id, 'lsd_visible_until', true);
        $data[] = $visible_until ? lsd_date('Y-m-d', $visible_until) : '';

        return $data;
    }
}
