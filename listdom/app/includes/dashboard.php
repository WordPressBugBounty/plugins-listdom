<?php

class LSD_Dashboard extends LSD_Base
{
    public function init()
    {
        // Dashboard Shortcode
        $dashboard = new LSD_Shortcodes_Dashboard();
        $dashboard->init();

        // Payments and Billing Dashboard
        $payments = new LSD_Dashboard_Payments();
        $payments->init();
    }

    public function modules()
    {
        $modules = [
            ['label' => esc_html__('Contact Details', 'listdom'), 'key' => 'contact'],
            ['label' => esc_html__('Remark', 'listdom'), 'key' => 'remark'],
            ['label' => esc_html__('Gallery', 'listdom'), 'key' => 'gallery'],
            ['label' => esc_html(lsd_t_label(LSD_Base::TAX_ATTRIBUTE, 'plural')), 'key' => 'attributes'],
            ['label' => esc_html(lsd_t_label(LSD_Base::TAX_LOCATION, 'plural')), 'key' => 'locations'],
            ['label' => esc_html(lsd_t_label(LSD_Base::TAX_TAG, 'plural')), 'key' => 'tags'],
            ['label' => esc_html(lsd_t_label(LSD_Base::TAX_FEATURE, 'plural')), 'key' => 'features'],
            ['label' => esc_html(lsd_t_label(LSD_Base::TAX_LABEL, 'plural')), 'key' => 'labels'],
            ['label' => esc_html__('Featured Image', 'listdom'), 'key' => 'image'],
            ['label' => esc_html__('Embed Codes', 'listdom'), 'key' => 'embed'],
            ['label' => esc_html__('FAQs', 'listdom'), 'key' => 'faq'],
            ['label' => esc_html__('Excerpt', 'listdom'), 'key' => 'excerpt'],
        ];

        if (LSD_Components::cta())
            $modules[] = ['label' => esc_html__('Call to Action', 'listdom'), 'key' => 'cta'];

        if (LSD_Components::related())
            $modules[] = ['label' => esc_html__('Related Listings', 'listdom'), 'key' => 'related'];

        // Map Module
        if (LSD_Components::map()) $modules[] = ['label' => esc_html__('Address / Map', 'listdom'), 'key' => 'address'];

        // Price Module
        if (LSD_Components::pricing()) $modules[] = ['label' => esc_html__('Price Options', 'listdom'), 'key' => 'price'];

        // Work Hours Module
        if (LSD_Components::work_hours()) $modules[] = ['label' => esc_html__('Work Hours', 'listdom'), 'key' => 'availability'];

        return apply_filters('lsd_dashboard_modules', $modules);
    }

    public function fields()
    {
        $fields = [
            'title' => ['label' => esc_html__('Listing Title', 'listdom'), 'always_enabled' => true],
            'content' => ['label' => esc_html__('Listing Description', 'listdom')],
            'excerpt' => ['label' => esc_html__('Listing Excerpt', 'listdom')],
            'guest_email' => ['label' => esc_html__('Guest Email', 'listdom'), 'always_enabled' => true, 'guest' => true],
            'guest_password' => ['label' => esc_html__('Guest Password', 'listdom'), 'always_enabled' => true, 'guest' => true],
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
            '_faqs' => ['label' => esc_html__('FAQs', 'listdom'), 'module' => 'faq'],
            'listing_category' => ['label' => esc_html(lsd_t_label(LSD_Base::TAX_CATEGORY)), 'always_enabled' => true],
            'tags' => ['label' => esc_html(lsd_t_label(LSD_Base::TAX_TAG, 'plural')), 'module' => 'locations'],
            LSD_Base::TAX_LOCATION => ['label' => esc_html(lsd_t_label(LSD_Base::TAX_LOCATION, 'plural')), 'module' => 'tags'],
            LSD_Base::TAX_FEATURE => ['label' => esc_html(lsd_t_label(LSD_Base::TAX_FEATURE, 'plural')), 'module' => 'features'],
            LSD_Base::TAX_LABEL => ['label' => esc_html(lsd_t_label(LSD_Base::TAX_LABEL, 'plural')), 'module' => 'labels'],
            'featured_image' => ['label' => esc_html__('Featured Image', 'listdom'), 'module' => 'image', 'capability' => 'upload_files'],
        ];

        if (LSD_Components::related())
            $fields['related_listings'] = ['label' => esc_html__('Related Listings', 'listdom'), 'module' => 'related'];

        if (LSD_Components::map()) $fields['address'] = ['label' => esc_html__('Address', 'listdom'), 'module' => 'address'];

        if (LSD_Components::work_hours()) $fields['ava'] = ['label' => esc_html__('Work Hours', 'listdom'), 'module' => 'availability'];

        $SN = new LSD_Socials();
        $networks = LSD_Options::socials();

        foreach ($networks as $network => $values)
        {
            $obj = $SN->get($network, $values);
            $fields[$obj->key()] = ['label' => $obj->label(), 'module' => 'contact'];
        }

        // Price Components
        $price_components = LSD_Options::price_components();

        // Remove Price Fields if pricing component is disabled
        if (!LSD_Components::pricing()) unset($fields['price']);

        if (!$price_components['max']) unset($fields['price_max']);
        if (!$price_components['after']) unset($fields['price_after']);
        if (!$price_components['class']) unset($fields['price_class']);

        return apply_filters('lsd_dashboard_fields', $fields);
    }

    public static function is_dashboard(): bool
    {
        return self::is_fd_page() || self::is_add_page();
    }

    public static function is_fd_page(): bool
    {
        return self::is_page_with_shortcode('submission_page', 'listdom-dashboard');
    }

    public static function is_add_page(): bool
    {
        return self::is_page_with_shortcode('add_listing_page', 'listdom-add-listing');
    }

    protected static function is_page_with_shortcode(string $option_key, string $shortcode): bool
    {
        $current_page_id = get_queried_object_id();
        $page_id = absint(LSD_Options::post_id($option_key));

        if ($current_page_id && $current_page_id == $page_id) return true;

        $post = get_post();
        if (!($post instanceof WP_Post)) return false;

        $content = $post->post_content ?? '';
        if (!is_string($content) || trim($content) === '') return false;

        return (bool) preg_match('/\[(' . preg_quote($shortcode, '/') . ')(\s|]|\/)/i', $content);
    }
}
