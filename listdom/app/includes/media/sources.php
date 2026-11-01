<?php

/** First-party image source selection; no rendering or metadata changes. */
class LSD_Media_Sources
{
    public static function gallery(int $listing_id, array $settings): array
    {
        $gallery = get_post_meta($listing_id, 'lsd_gallery', true);
        if (!is_array($gallery)) $gallery = [];
        $policy = isset($settings['gallery_featured_image']) && trim($settings['gallery_featured_image']) ? $settings['gallery_featured_image'] : 'always';
        $featured = (int) get_post_thumbnail_id($listing_id);
        $added = false;
        if (($policy === 'always' || ($policy === 'fallback' && !count($gallery))) && $featured)
        {
            array_unshift($gallery, $featured);
            $added = true;
        }
        return ['ids' => array_values(array_unique($gallery)), 'featured_added' => $added, 'featured_id' => $featured, 'policy' => $policy];
    }
}
