<?php

class LSD_Slug extends LSD_Base
{
    public static function get(): string
    {
        // Advanced Slug
        if (LSD_Slug::is_advanced()) return LSD_Slug::advanced();

        // Basic Slug
        return LSD_Options::slug();
    }

    public static function advanced(): string
    {
        // Prefix
        $prefix = LSD_Options::slug();

        // General Settings
        $settings = LSD_Options::settings();

        return $prefix.'/'.preg_replace('/\s+/', '', strtolower($settings['advanced_slug']));
    }

    public static function populate($link, $post)
    {
        // Not Advanced Slug
        if (!LSD_Slug::is_advanced()) return $link;

        // Ensure Listing Post Type
        if ($post instanceof WP_Post && $post->post_type === LSD_Base::PTYPE_LISTING)
        {
            // Primary Category
            if (strpos($link, '%category%') !== false)
            {
                $link = str_replace('%category%', LSD_Slug::term_slugs($post->ID, LSD_Base::TAX_CATEGORY), $link);
            }

            // Primary Categories
            if (strpos($link, '%categories%') !== false)
            {
                $link = str_replace('%categories%', LSD_Slug::term_slugs($post->ID, LSD_Base::TAX_CATEGORY, true), $link);
            }

            // Location
            if (strpos($link, '%location%') !== false)
            {
                $link = str_replace('%location%', LSD_Slug::term_slugs($post->ID, LSD_Base::TAX_LOCATION), $link);
            }

            // Locations
            if (strpos($link, '%locations%') !== false)
            {
                $link = str_replace('%locations%', LSD_Slug::term_slugs($post->ID, LSD_Base::TAX_LOCATION, true), $link);
            }
        }

        return $link;
    }

    public static function term_slugs(int $listing_id, string $tax, bool $hierarchical = false): string
    {
        if ($tax === LSD_Base::TAX_CATEGORY)
        {
            $term = LSD_Entity_Listing::get_primary_category($listing_id);
        }
        else
        {
            $terms = wp_get_object_terms($listing_id, $tax);
            $term = is_array($terms) && count($terms) && isset($terms[0]) ? $terms[0] : null;
        }

        $slug = $term && isset($term->slug) ? $term->slug : 'uncategorized';

        while ($hierarchical && $term && $term->parent)
        {
            $term = get_term($term->parent, $tax);
            if ($term && isset($term->slug)) $slug = $term->slug . '/' . $slug;
        }

        return $slug;
    }

    public static function add_rules()
    {
        // Not Advanced Slug
        if (!LSD_Slug::is_advanced()) return;

        // Basic Slug
        $prefix = LSD_Options::slug();

        // Endpoints
        $endpoints = LSD_Endpoints::get();

        foreach($endpoints as $endpoint)
        {
            add_rewrite_rule(
                '^'.$prefix.'/(.*)/(.*)/'.$endpoint.'/?$',
                'index.php?post_type=' . LSD_Base::PTYPE_LISTING . '&name=$matches[2]&'.$endpoint.'=1',
                'top'
            );
        }

        add_rewrite_rule(
            '^'.$prefix.'/(.*)/(.*)/?$',
            'index.php?post_type=' . LSD_Base::PTYPE_LISTING . '&name=$matches[2]',
            'top'
        );
    }

    public static function is_advanced(): bool
    {
        // General Settings
        $settings = LSD_Options::settings();

        // Advanced Status
        $advanced = LSD_Base::isPro() && isset($settings['advanced_slug_status']) && $settings['advanced_slug_status']
            ? 1
            : 0;

        // Advanced Slug
        if ($advanced && isset($settings['advanced_slug']) && trim($settings['advanced_slug'])) return true;

        return false;
    }
}
