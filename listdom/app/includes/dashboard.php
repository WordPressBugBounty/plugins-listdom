<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom Dashboard Class.
 *
 * @class LSD_Dashboard
 * @version    1.0.0
 */
class LSD_Dashboard extends LSD_Base
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
        // Dashboard Shortcode
        $Dashboard = new LSD_Shortcodes_Dashboard();
        $Dashboard->init();
    }

    public function modules()
    {
        $modules = [
            ['label' => esc_html__('Address / Map', 'listdom'), 'key' => 'address'],
            ['label' => esc_html__('Price Options', 'listdom'), 'key' => 'price'],
            ['label' => esc_html__('Work Hours', 'listdom'), 'key' => 'availability'],
            ['label' => esc_html__('Contact Details', 'listdom'), 'key' => 'contact'],
            ['label' => esc_html__('Remark', 'listdom'), 'key' => 'remark'],
            ['label' => esc_html__('Gallery', 'listdom'), 'key' => 'gallery'],
            ['label' => esc_html__('Attributes', 'listdom'), 'key' => 'attributes'],
            ['label' => esc_html__('Locations', 'listdom'), 'key' => 'locations'],
            ['label' => esc_html__('Tags', 'listdom'), 'key' => 'tags'],
            ['label' => esc_html__('Features', 'listdom'), 'key' => 'features'],
            ['label' => esc_html__('Labels', 'listdom'), 'key' => 'labels'],
            ['label' => esc_html__('Featured Image', 'listdom'), 'key' => 'image'],
            ['label' => esc_html__('Embed Codes', 'listdom'), 'key' => 'embed'],
        ];

        return apply_filters('lsd_dashboard_modules', $modules);
    }

    public function fields()
    {
        $fields = [
            'title' => ['label' => esc_html__('Listing Title', 'listdom'), 'always_enabled' => true],
            'content' => ['label' => esc_html__('Listing Description', 'listdom')],
            'guest_email' => ['label' => esc_html__('Guest Email', 'listdom'), 'always_enabled' => true, 'guest' => true],
            'guest_password' => ['label' => esc_html__('Guest Password', 'listdom'), 'always_enabled' => true, 'guest' => true],
            'address' => ['label' => esc_html__('Address', 'listdom'), 'module' => 'address'],
            'remark' => ['label' => esc_html__('Remark', 'listdom'), 'module' => 'remark'],
            'price' => ['label' => esc_html__('Price', 'listdom'), 'module' => 'price'],
            'price_max' => ['label' => esc_html__('Price (Max)', 'listdom'), 'module' => 'price'],
            'price_after' => ['label' => esc_html__('Price Description', 'listdom'), 'module' => 'price'],
            'price_class' => ['label' => esc_html__('Price Class', 'listdom'), 'module' => 'price'],
            'email' => ['label' => esc_html__('Email', 'listdom'), 'module' => 'contact'],
            'phone' => ['label' => esc_html__('Phone', 'listdom'), 'module' => 'contact'],
            'website' => ['label' => esc_html__('Website', 'listdom'), 'module' => 'contact'],
            'contact_address' => ['label' => esc_html__('Contact Address', 'listdom'), 'module' => 'contact'],
            'link' => ['label' => esc_html__('Listing Custom Link', 'listdom'), 'module' => 'contact'],
            '_gallery' => ['label' => esc_html__('Gallery', 'listdom'), 'module' => 'gallery', 'capability' => 'upload_files'],
            '_embeds' => ['label' => esc_html__('Embed', 'listdom'), 'module' => 'embed'],
            'listing_category' => ['label' => esc_html__('Category', 'listdom'), 'always_enabled' => true],
            'tags' => ['label' => esc_html__('Tags', 'listdom'), 'module' => 'locations'],
            LSD_Base::TAX_LOCATION => ['label' => esc_html__('Locations', 'listdom'), 'module' => 'tags'],
            LSD_Base::TAX_FEATURE => ['label' => esc_html__('Features', 'listdom'), 'module' => 'features'],
            LSD_Base::TAX_LABEL => ['label' => esc_html__('Labels', 'listdom'), 'module' => 'labels'],
            'featured_image' => ['label' => esc_html__('Featured Image', 'listdom'), 'module' => 'image', 'capability' => 'upload_files'],
        ];

        $SN = new LSD_Socials();
        $networks = LSD_Options::socials();

        foreach ($networks as $network => $values)
        {
            $obj = $SN->get($network, $values);
            $fields[$obj->key()] = ['label' => $obj->label(), 'module' => 'contact'];
        }

        return apply_filters('lsd_dashboard_fields', $fields);
    }
}
