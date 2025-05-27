<?php

class LSD_Breadcrumb extends LSD_Base
{
    /**
     * Renders the full breadcrumb HTML
     */
    public static function breadcrumb($icon = true, $taxonomy = LSD_Base::TAX_CATEGORY): string
    {
        return self::trail($icon, $taxonomy);
    }

    /**
     * Builds the breadcrumb trail
     *
     * @param $icon
     * @param $taxonomy
     *
     * @return string
     */
    protected static function trail($icon, $taxonomy): string
    {
        global $post;

        $items = [];

        // Home link with optional icon
        $home_label = ($icon ? '<i class="fas fa-home"></i> ' : '') . __('Home', 'listdom');
        $items[] = self::item(home_url(), $home_label, 'lsd-home-page');

        if (is_singular())
        {
            $items = array_merge($items, self::singular($post, $taxonomy));
        }

        return implode("\n", $items);
    }

    /**
     * Handles singular post/page breadcrumb items
     *
     * @param $post
     * @param $taxonomy
     *
     * @return array
     */
    protected static function singular($post, $taxonomy): array
    {
        $items = [];

        $post_type = get_post_type_object(get_post_type());

        // Post type archive link
        if ($post_type && $post_type->has_archive)
        {
            $items[] = self::item(get_post_type_archive_link($post_type->name), $post_type->labels->name, 'lsd-taxonomy-page');
        }

        // Page ancestors
        if (is_page() && $post->post_parent)
        {
            $ancestors = array_reverse(get_post_ancestors($post->ID));
            foreach ($ancestors as $ancestor_id)
            {
                $items[] = self::item(get_permalink($ancestor_id), get_the_title($ancestor_id), 'lsd-taxonomy-page');
            }
        }

        // Selected taxonomy only
        $terms = get_the_terms($post->ID, $taxonomy);

        if (!empty($terms) && !is_wp_error($terms))
        {
            $term = $terms[0]; // Use the first term
            $ancestors = get_ancestors($term->term_id, $taxonomy);

            foreach (array_reverse($ancestors) as $ancestor_id)
            {
                $ancestor = get_term($ancestor_id, $taxonomy);
                if (!is_wp_error($ancestor))
                {
                    $items[] = self::item(get_term_link($ancestor), $ancestor->name, 'lsd-taxonomy-page');
                }
            }

            $items[] = self::item(get_term_link($term), $term->name, 'lsd-taxonomy-page');
        }

        // Current post/page
        $items[] = self::current(get_the_title(), 'lsd-current-page');

        return $items;
    }

    /**
     * Renders a breadcrumb item with a link
     */
    protected static function item($url, $label, $class = ''): string
    {
        return sprintf(
            '<li class="lsd-breadcrumb-item %s"><a href="%s">%s</a></li>',
            esc_attr($class),
            esc_url($url),
            $label
        );
    }

    /**
     * Renders the current breadcrumb item
     */
    protected static function current($label, $class = 'current'): string
    {
        return sprintf(
            '<li class="lsd-breadcrumb-item %s"><span aria-current="page">%s</span></li>',
            esc_attr($class),
            esc_html($label)
        );
    }
}
