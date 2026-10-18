<?php

/** Dynamic rendering shared by the basic listing blocks. */
class LSD_Integrations_Blockeditor_Elements
{
    private static $instance_count = 0;
    private static $rendering_content = false;

    public static function rendering_description(): bool
    {
        return self::$rendering_content;
    }

    public static function title($attributes): string
    {
        return self::output('title', $attributes);
    }

    public static function address($attributes): string
    {
        return self::output('address', $attributes);
    }

    public static function excerpt($attributes): string
    {
        return self::output('excerpt', $attributes);
    }

    public static function image($attributes): string
    {
        return self::output('image', $attributes);
    }

    public static function gallery($attributes): string
    {
        return self::output('gallery', $attributes);
    }

    public static function content($attributes): string
    {
        return self::output('content', $attributes);
    }

    public static function video($attributes): string
    {
        return self::output('video', $attributes);
    }

    public static function embeds($attributes): string
    {
        return self::output('embeds', $attributes);
    }

    public static function faqs($attributes): string
    {
        return self::output('faqs', $attributes);
    }

    public static function categories($attributes): string
    {
        return self::output('categories', $attributes);
    }

    public static function locations($attributes): string
    {
        return self::output('locations', $attributes);
    }

    public static function tags($attributes): string
    {
        return self::output('tags', $attributes);
    }

    public static function labels($attributes): string
    {
        return self::output('labels', $attributes);
    }

    public static function features($attributes): string
    {
        return self::output('features', $attributes);
    }

    public static function attributes($attributes): string
    {
        return self::output('attributes', $attributes);
    }

    public static function price($attributes): string
    {
        return self::output('price', $attributes);
    }

    public static function price_class($attributes): string
    {
        return self::output('price-class', $attributes);
    }

    public static function availability($attributes): string
    {
        return self::output('availability', $attributes);
    }

    public static function remark($attributes): string
    {
        return self::output('remark', $attributes);
    }

    public static function rate($attributes): string
    {
        return self::output('rate', $attributes);
    }

    public static function contact($attributes): string
    {
        return self::output('contact', $attributes);
    }

    public static function owner($attributes): string
    {
        return self::output('owner', $attributes);
    }

    public static function map($attributes): string
    {
        return self::output('map', $attributes);
    }

    public static function direction($attributes): string
    {
        return self::output('direction', $attributes);
    }

    public static function breadcrumb($attributes): string
    {
        return self::output('breadcrumb', $attributes);
    }

    public static function share($attributes): string
    {
        return self::output('share', $attributes);
    }

    public static function back_button($attributes): string
    {
        return self::output('back-button', $attributes);
    }

    private static function output(string $kind, $attributes): string
    {
        if ($kind === 'rate' && has_filter('lsd_rate_stars') === false)
        {
            if (defined('REST_REQUEST') && REST_REQUEST && !empty($_REQUEST['lsd_editor_preview']))
                return '<div class="lsd-listing-block-placeholder">' . esc_html__('Activate the Listdom Review add-on to display ratings.', 'listdom') . '</div>';

            return '';
        }

        $listing = LSD_Integrations_Blockeditor_Listing::get();
        if (!$listing)
        {
            if (defined('REST_REQUEST') && REST_REQUEST && !empty($_REQUEST['lsd_editor_preview']))
                return '<div class="lsd-listing-block-placeholder">' . esc_html__('No listing available for preview.', 'listdom') . '</div>';

            return '';
        }

        $access = LSD_Integrations_Blockeditor_Listing::prepare($listing);
        if ($access !== null) return $access;

        if ($kind === 'content')
        {
            if (self::$rendering_content) return '';
            if (LSD_Integrations_Blockeditor_Listing::in_own_content($listing))
            {
                if (defined('REST_REQUEST') && REST_REQUEST && !empty($_REQUEST['lsd_editor_preview']))
                    return '<div class="lsd-listing-block-placeholder">' . esc_html__('Listing Content is available in listing templates, outside the listing description.', 'listdom') . '</div>';

                return '';
            }
        }

        $name = 'listdom/listing-' . $kind;
        $styles_before = wp_styles()->queue;
        $html = LSD_Integrations_Blockeditor_Listing::with_post($listing, function () use ($name, $attributes, $listing): string
        {
            switch ($name)
            {
                case 'listdom/listing-title':
                    $tag = $attributes['titleTag'] ?? 'h2';
                    if (!in_array($tag, ['h1', 'h2', 'h3', 'h4', 'p', 'strong'], true)) $tag = 'h2';
                    $method = $attributes['linkMethod'] ?? 'normal';
                    if (!array_key_exists($method, LSD_Base::get_listing_link_methods())) $method = 'normal';
                    return '<' . $tag . '>' . $listing->get_title_tag($method) . '</' . $tag . '>';

                case 'listdom/listing-address':
                    return $listing->get_address(!isset($attributes['showIcon']) || (bool) $attributes['showIcon']);

                case 'listdom/listing-excerpt':
                    $words = isset($attributes['words']) ? max(1, (int) $attributes['words']) : 12;
                    return $listing->get_excerpt($words);

                case 'listdom/listing-image':
                    $size = $attributes['imageSize'] ?? 'full';
                    if (!in_array($size, get_intermediate_image_sizes(), true) && $size !== 'full') $size = 'full';
                    return $listing->get_featured_image($size);

                case 'listdom/listing-gallery':
                    $style = $attributes['galleryStyle'] ?? 'list';
                    if (!in_array($style, ['list', 'slider', 'linear'], true)) $style = 'list';
                    $link_method = $attributes['linkMethod'] ?? 'normal';
                    if (!array_key_exists($link_method, LSD_Base::get_listing_link_methods())) $link_method = 'normal';
                    $thumbnails = $attributes['thumbnailStatus'] ?? 'image';
                    if (!in_array($thumbnails, ['image', 'list', 'disabled'], true)) $thumbnails = 'image';
                    $navigation = $attributes['navigationMethod'] ?? 'dots';
                    if (!in_array($navigation, ['dots', 'nav', 'disabled'], true)) $navigation = 'dots';
                    return $listing->get_gallery([
                        'style' => $style,
                        'lightbox' => !isset($attributes['lightbox']) || (bool) $attributes['lightbox'],
                        'include_thumbnail' => !empty($attributes['includeThumbnail']),
                        'image_limit' => isset($attributes['imageLimit']) ? max(1, (int) $attributes['imageLimit']) : 4,
                        'autoplay' => !isset($attributes['autoplay']) || (bool) $attributes['autoplay'],
                        'link_method' => $link_method,
                        'thumbnail_status' => $thumbnails,
                        'navigation_method' => $navigation,
                        'auto_height' => !isset($attributes['autoHeight']) || (bool) $attributes['autoHeight'],
                        'loop' => !empty($attributes['loop']),
                    ]);

                case 'listdom/listing-content':
                    // A content block stored in the description must not render itself again.
                    self::$rendering_content = true;
                    // Keep paragraph formatting controlled by the block attribute.
                    $wpautop_priority = has_filter('the_content', 'wpautop');
                    $shortcode_unautop_priority = has_filter('the_content', 'shortcode_unautop');
                    if ($wpautop_priority !== false) remove_filter('the_content', 'wpautop', $wpautop_priority);
                    if ($shortcode_unautop_priority !== false) remove_filter('the_content', 'shortcode_unautop', $shortcode_unautop_priority);
                    try
                    {
                        $content = apply_filters('the_content', $listing->post->post_content);
                    }
                    finally
                    {
                        if ($wpautop_priority !== false) add_filter('the_content', 'wpautop', $wpautop_priority);
                        if ($shortcode_unautop_priority !== false) add_filter('the_content', 'shortcode_unautop', $shortcode_unautop_priority);
                        self::$rendering_content = false;
                    }
                    $words = isset($attributes['contentLength']) ? max(0, (int) $attributes['contentLength']) : 0;
                    if ($words) $content = wp_trim_words($content, $words, '');
                    if (!empty($attributes['autoParagraph'])) $content = wpautop($content);
                    return $listing->get_content($content);

                case 'listdom/listing-video':
                    return (string) $listing->get_featured_video();

                case 'listdom/listing-embeds':
                    return (string) $listing->get_embeds();

                case 'listdom/listing-faqs':
                    return $listing->get_faqs(isset($attributes['faqLimit']) ? max(0, (int) $attributes['faqLimit']) : 0);

                case 'listdom/listing-categories':
                    return $listing->get_categories([
                        'show_color' => !isset($attributes['showColors']) || (bool) $attributes['showColors'],
                        'multiple_categories' => isset($attributes['multipleCategories']) ? (bool) $attributes['multipleCategories'] : (bool) apply_filters('lsd_listing_display_multiple_categories', false),
                        'display_name' => !isset($attributes['showNames']) || (bool) $attributes['showNames'],
                        'display_icon' => !empty($attributes['showIcons']),
                        'enable_link' => !isset($attributes['enableLinks']) || (bool) $attributes['enableLinks'],
                    ]);

                case 'listdom/listing-locations':
                    return $listing->get_locations(['enable_link' => !isset($attributes['enableLinks']) || (bool) $attributes['enableLinks']]);

                case 'listdom/listing-tags':
                    return $listing->get_tags(!isset($attributes['enableLinks']) || (bool) $attributes['enableLinks']);

                case 'listdom/listing-labels':
                    $style = $attributes['labelStyle'] ?? 'tags';
                    if (!in_array($style, ['tags', 'links'], true)) $style = 'tags';
                    return $listing->get_labels($style, !isset($attributes['enableLinks']) || (bool) $attributes['enableLinks']);

                case 'listdom/listing-features':
                    $method = $attributes['featureMethod'] ?? 'list';
                    $style = $attributes['featureStyle'] ?? 'per-row';
                    if (!in_array($style, ['per-row', 'inline'], true)) $style = 'per-row';
                    return $listing->get_features(
                        $method === 'text' ? 'text' : 'list',
                        !empty($attributes['showIcons']),
                        !isset($attributes['enableLinks']) || (bool) $attributes['enableLinks'],
                        $style
                    );

                case 'listdom/listing-attributes':
                    $layout = $attributes['attributeLayout'] ?? 'column';
                    if (!in_array($layout, ['column', 'row', 'table'], true)) $layout = 'column';
                    return $listing->get_attributes(
                        !empty($attributes['showIcons']),
                        !isset($attributes['showTitles']) || (bool) $attributes['showTitles'],
                        !empty($attributes['showSeparators']),
                        $layout
                    );

                case 'listdom/listing-price':
                    return (string) $listing->get_price(!empty($attributes['minimized']));

                case 'listdom/listing-price-class':
                    return $listing->get_price_class();

                case 'listdom/listing-availability':
                    return (string) $listing->get_availability(!isset($attributes['oneDay']) || (bool) $attributes['oneDay']);

                case 'listdom/listing-remark':
                    return (string) $listing->get_remark();

                case 'listdom/listing-rate':
                    $type = $attributes['rateType'] ?? 'stars';
                    // Keep the stored showCount attribute for existing blocks; the add-on treats it as the link flag.
                    return (string) $listing->get_rate_stars($type === 'summary' ? 'summary' : 'stars', !isset($attributes['showCount']) || (bool) $attributes['showCount']);

                case 'listdom/listing-contact':
                    return (string) $listing->get_contact_info([
                        'show_email' => !isset($attributes['showEmail']) || (bool) $attributes['showEmail'],
                        'show_phone' => !isset($attributes['showPhone']) || (bool) $attributes['showPhone'],
                        'show_website' => !isset($attributes['showWebsite']) || (bool) $attributes['showWebsite'],
                        'show_address' => !isset($attributes['showAddress']) || (bool) $attributes['showAddress'],
                        'show_socials' => !isset($attributes['showSocials']) || (bool) $attributes['showSocials'],
                        'display_icon' => !isset($attributes['showIcons']) || (bool) $attributes['showIcons'],
                        'display_label' => !empty($attributes['showLabels']),
                    ]);

                case 'listdom/listing-owner':
                    return (string) $listing->get_owner('details', [
                        'display_avatar' => !isset($attributes['showAvatar']) || (bool) $attributes['showAvatar'],
                        'display_name' => !isset($attributes['showName']) || (bool) $attributes['showName'],
                        'display_bio' => !isset($attributes['showBio']) || (bool) $attributes['showBio'],
                        'display_socials' => !isset($attributes['showSocials']) || (bool) $attributes['showSocials'],
                        'display_tel' => !isset($attributes['showPhone']) || (bool) $attributes['showPhone'],
                        'display_mobile' => !isset($attributes['showPhone']) || (bool) $attributes['showPhone'],
                        'display_email' => !isset($attributes['showEmail']) || (bool) $attributes['showEmail'],
                        'display_form' => !isset($attributes['showForm']) || (bool) $attributes['showForm'],
                        'form_id' => '1000000000' . ++self::$instance_count,
                    ]);

                case 'listdom/listing-map':
                    if (!LSD_Components::map()) return '';
                    if (
                        !$listing->is_shape()
                        && (!is_numeric(get_post_meta($listing->id(), 'lsd_latitude', true)) || !is_numeric(get_post_meta($listing->id(), 'lsd_longitude', true)))
                    )
                    {
                        if (defined('REST_REQUEST') && REST_REQUEST && !empty($_REQUEST['lsd_editor_preview']))
                            return '<div class="lsd-listing-block-placeholder">' . esc_html__('This listing has no map coordinates.', 'listdom') . '</div>';

                        return '';
                    }
                    if (defined('REST_REQUEST') && REST_REQUEST && !empty($_REQUEST['lsd_editor_preview']))
                    {
                        $address = trim(wp_strip_all_tags($listing->get_address(false)));
                        if ($address === '') $address = get_the_title($listing->id());
                        return '<div class="lsd-listing-block-placeholder">' . sprintf(esc_html__('Map location: %s', 'listdom'), esc_html($address)) . '</div>';
                    }

                    $zoom = isset($attributes['zoomLevel']) ? (int) $attributes['zoomLevel'] : 14;
                    if ($zoom < 4 || $zoom > 16) $zoom = 14;
                    return (string) $listing->get_map([
                        // Map templates use this ID as an unquoted JavaScript number.
                        'id' => '1000000000' . ++self::$instance_count,
                        'provider' => LSD_Map_Provider::get($attributes['mapProvider'] ?? null),
                        'zoomlevel' => $zoom,
                        'infowindow' => !isset($attributes['showInfowindow']) || (bool) $attributes['showInfowindow'],
                    ]);

                case 'listdom/listing-direction':
                    if (!LSD_Components::map()) return '';
                    if (!is_numeric(get_post_meta($listing->id(), 'lsd_latitude', true)) || !is_numeric(get_post_meta($listing->id(), 'lsd_longitude', true))) return '';
                    if (!class_exists('LSDPACAM\\Direction') || !method_exists('LSDPACAM\\Direction', 'link'))
                    {
                        if (defined('REST_REQUEST') && REST_REQUEST && !empty($_REQUEST['lsd_editor_preview']))
                            return '<div class="lsd-listing-block-placeholder">' . esc_html__('Activate the Listdom Advanced Map add-on to display directions.', 'listdom') . '</div>';

                        return '';
                    }

                    $label = trim((string) ($attributes['directionLabel'] ?? ''));
                    if ($label === '') $label = esc_html__('Directions', 'listdom');
                    return (new LSDPACAM\Direction())->link($listing, esc_html($label), !isset($attributes['showIcon']) || (bool) $attributes['showIcon']);

                case 'listdom/listing-breadcrumb':
                    $taxonomy = $attributes['breadcrumbTaxonomy'] ?? LSD_Base::TAX_CATEGORY;
                    if (!in_array($taxonomy, [LSD_Base::TAX_CATEGORY, LSD_Base::TAX_LOCATION, LSD_Base::TAX_LABEL, LSD_Base::TAX_TAG, LSD_Base::TAX_FEATURE], true)) $taxonomy = LSD_Base::TAX_CATEGORY;
                    return (string) $listing->get_breadcrumb(!isset($attributes['showHomeIcon']) || (bool) $attributes['showHomeIcon'], $taxonomy);

                case 'listdom/listing-share':
                    return (string) $listing->get_share_buttons(
                        ($attributes['shareLayout'] ?? 'archive') === 'full' ? 'full' : 'archive',
                        ['modal_id' => '1000000000' . ++self::$instance_count]
                    );

                case 'listdom/listing-back-button':
                    $style = $attributes['buttonStyle'] ?? 'text';
                    if (!in_array($style, ['text', 'light', 'solid'], true)) $style = 'text';
                    return (string) (new LSD_Element_Backbutton())->get($listing->id(), ['button_style' => $style]);
            }

            return '';
        });

        if (trim($html) === '') return '';

        $class = 'wp-block-listdom-listing-' . $kind;
        $class .= ' lsd-listing-block ' . str_replace('listdom/', 'lsd-', $name);

        $typography = $attributes['style']['typography'] ?? [];
        $colors = $attributes['style']['color'] ?? [];

        $support_class = '';
        if (!empty($attributes['fontSize']) || !empty($typography['fontSize'])) $support_class .= ' lsd-block-font-size';
        if (!empty($typography['lineHeight'])) $support_class .= ' lsd-block-line-height';
        if (!empty($attributes['textColor']) || !empty($colors['text'])) $support_class .= ' lsd-block-text-color';
        $class .= $support_class;

        if (defined('REST_REQUEST') && REST_REQUEST && !empty($_REQUEST['lsd_editor_preview']))
        {
            // useBlockProps applies block supports and theme block styles to the outer editor wrapper.
            $wrapper = 'class="' . esc_attr('lsd-listing-block ' . str_replace('listdom/', 'lsd-', $name) . $support_class) . '"';
        }
        elseif (function_exists('get_block_wrapper_attributes'))
        {
            $wrapper = get_block_wrapper_attributes(['class' => $class]);
            // Core added anchor IDs to wrapper attributes in WordPress 6.2.
            if (!empty($attributes['anchor']) && version_compare(get_bloginfo('version'), '6.2', '<')) $wrapper .= ' id="' . esc_attr($attributes['anchor']) . '"';
        }
        else
        {
            if (!empty($attributes['align']) && in_array($attributes['align'], ['wide', 'full'], true)) $class .= ' align' . $attributes['align'];
            $class .= !empty($attributes['className']) ? ' ' . $attributes['className'] : '';
            $wrapper = 'class="' . esc_attr($class) . '"';
            if (!empty($attributes['anchor'])) $wrapper .= ' id="' . esc_attr($attributes['anchor']) . '"';
        }

        return LSD_Integrations_Blockeditor_Assets::frontend($styles_before) . '<div ' . $wrapper . '>' . $html . '</div>';
    }
}
