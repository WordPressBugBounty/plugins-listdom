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

        // Listing Visibility Data
        add_filter('lsd_api_resource_listing', [$this, 'listing'], 10, 2);
    }

    public function save($post_id, \WP_REST_Request $request)
    {
        // Post
        $post = get_post($post_id);

        // It's not a listing
        if ($post->post_type !== \LSD_Base::PTYPE_LISTING) return;

        // Get Listdom Data
        $lsd = $request->get_params();

        // Visibility Dates
        $visible_from = $lsd['visible_from'] ?? 0;
        $visible_until = $lsd['visible_until'] ?? 0;

        update_post_meta($post_id, 'lsd_visible_from', ($visible_from ? strtotime($visible_from) : 0));
        update_post_meta($post_id, 'lsd_visible_until', ($visible_until ? strtotime($visible_until) : 0));

        if ($visible_from || $visible_until) (new Addon())->cron();
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
