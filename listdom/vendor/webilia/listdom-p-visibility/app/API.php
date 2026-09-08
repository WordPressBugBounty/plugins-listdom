<?php
namespace LSDPACVIS;

class API extends Base
{
    public $namespace = 'listdom/visibility.v1';

    public function init()
    {
        // Save Visibility Options
        add_action('lsd_api_listing_created', [$this, 'save'], 50, 2);
        add_action('lsd_api_listing_updated', [$this, 'save'], 50, 2);

        // Add Visibility Fields
        add_filter('lsd_api_resource_fields', [$this, 'fields']);

        // Validate effective visibility dates before an API listing is persisted.
        add_filter('lsd_api_listing_validate', [$this, 'validate'], 10, 3);

        // Listing Visibility Data
        add_filter('lsd_api_resource_listing', [$this, 'listing'], 10, 2);
    }

    public function validate($valid, array $data, ?\WP_Post $post = null)
    {
        if (is_wp_error($valid) || (!array_key_exists('visible_from', $data) && !array_key_exists('visible_until', $data))) return $valid;

        if ($post instanceof \WP_Post) (new Addon())->migrate_legacy_dates($post->ID);

        $visible_from = array_key_exists('visible_from', $data) ? $this->timestamp($data['visible_from']) : (int) get_post_meta($post ? $post->ID : 0, 'lsd_visible_from', true);
        $visible_until = array_key_exists('visible_until', $data) ? $this->timestamp($data['visible_until']) : (int) get_post_meta($post ? $post->ID : 0, 'lsd_visible_until', true);

        if ($visible_from === false || $visible_until === false) return new \WP_Error('invalid_visibility_date', esc_html__('Please enter valid visibility dates.', 'listdom-visibility'));
        if ($visible_from && $visible_until && $visible_until <= $visible_from) return new \WP_Error('invalid_visibility_range', esc_html__('Visible Until must be later than Visible From.', 'listdom-visibility'));

        return $valid;
    }

    private function timestamp($value)
    {
        if ($value === '' || $value === null) return 0;
        if (is_numeric($value)) return (int) $value;

        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $value, wp_timezone());
        if (!$date || $date->format('Y-m-d') !== $value) return false;

        return $date->getTimestamp();
    }

    public function save($post_id, \WP_REST_Request $request)
    {
        // Post
        $post = get_post($post_id);

        // Guard: if the post lookup fails, exit to avoid accessing properties on null.
        if (!($post instanceof \WP_Post)) return;

        // It's not a listing
        if ($post->post_type !== \LSD_Base::PTYPE_LISTING) return;

        // Get Listdom Data
        $lsd = $request->get_params();
        if (!array_key_exists('visible_from', $lsd) && !array_key_exists('visible_until', $lsd)) return;

        (new Addon())->migrate_legacy_dates($post_id);

        // Visibility Dates
        if (array_key_exists('visible_from', $lsd))
        {
            $visible_from = $lsd['visible_from'];
            update_post_meta($post_id, 'lsd_visible_from', $this->timestamp($visible_from));
        }
        if (array_key_exists('visible_until', $lsd))
        {
            $visible_until = $lsd['visible_until'];
            update_post_meta($post_id, 'lsd_visible_until', $this->timestamp($visible_until));
        }

        delete_post_meta($post_id, Addon::OVERRIDE_META);
        update_post_meta($post_id, Addon::CLOCK_META, Addon::CLOCK_VERSION);
        $publish_approved = $post->post_status === \LSD_Base::STATUS_PUBLISHED
            || (bool) get_post_meta($post_id, Addon::PUBLISH_APPROVED_META, true);
        if ($publish_approved) update_post_meta($post_id, Addon::PUBLISH_APPROVED_META, 1);
        else delete_post_meta($post_id, Addon::PUBLISH_APPROVED_META);
        (new Addon())->listing($post, $publish_approved);
    }

    public function fields($form)
    {
        // Visibility Fields
        if (\LSD_API_Resources_Fields::is_enabled('visibility'))
        {
            $form['visibility'] = [
                'section' => [
                    'title' => esc_html__('Visibility', 'listdom-visibility'),
                ],
                'fields' => [
                    'visible_from' => [
                        'key' => 'lsd[visible_from]',
                        'method' => 'date-input',
                        'format' => 'yyyy-mm-dd',
                        'label' => esc_html__('Start Date', 'listdom-visibility'),
                        'required' => false,
                    ],
                    'visible_until' => [
                        'key' => 'lsd[visible_until]',
                        'method' => 'date-input',
                        'format' => 'yyyy-mm-dd',
                        'label' => esc_html__('End Date', 'listdom-visibility'),
                        'required' => false,
                    ],
                ],
            ];
        }

        return $form;
    }

    public function listing($listing, $id)
    {
        (new Addon())->migrate_legacy_dates($id);

        $visible_from = get_post_meta($id, 'lsd_visible_from', true);
        $visible_until = get_post_meta($id, 'lsd_visible_until', true);
        $date_format = get_option('date_format');

        $listing['visibility'] = [
            'from_date' => $visible_from ? lsd_date($date_format, $visible_from) : '',
            'from_timestamp' => $visible_from,
            'until_date' => $visible_until ? lsd_date($date_format, $visible_until) : '',
            'until_timestamp' => $visible_until,
        ];

        return $listing;
    }
}
