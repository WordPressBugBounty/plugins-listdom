<?php

/** Shared listing selection for native listing element blocks. */
class LSD_Integrations_Blockeditor_Listing
{
    private static $content_stack = [];

    public static function begin_content($content)
    {
        $post = get_post();
        self::$content_stack[] = self::listing_post($post) && $content === $post->post_content ? (int) $post->ID : 0;
        return $content;
    }

    public static function end_content($content)
    {
        array_pop(self::$content_stack);
        return $content;
    }

    public static function in_own_content(LSD_Entity_Listing $listing): bool
    {
        if ((int) end(self::$content_stack) === $listing->id()) return true;

        return defined('REST_REQUEST') && REST_REQUEST
            && !empty($_REQUEST['lsd_editor_preview'])
            && (int) ($_REQUEST['post_id'] ?? 0) === $listing->id();
    }

    public static function get(): ?LSD_Entity_Listing
    {
        // A valid template preview nonce grants access to the template, not to every draft listing.
        $preview = class_exists('LSD_PTypes_Template') && method_exists('LSD_PTypes_Template', 'get_active_preview_request')
            ? LSD_PTypes_Template::get_active_preview_request()
            : [];
        $preview_id = (int) ($preview['preview_listing_id'] ?? 0);
        $preview_post = $preview_id ? get_post($preview_id) : null;
        if ($preview_id && !self::can_preview($preview_post)) return null;

        $post = get_post();
        $listing = LSD_Payload::get('listing');
        // Builders leave the last listing in the payload after they restore $post.
        if (self::valid($listing) && $post instanceof WP_Post && (int) $post->ID === (int) $listing->post->ID) return $listing;

        if (self::listing_post($post)) return new LSD_Entity_Listing($post);

        $post = get_queried_object();
        if (self::listing_post($post)) return new LSD_Entity_Listing($post);

        if ($preview_id) return new LSD_Entity_Listing($preview_post);

        // This flag is supplied only by the Gutenberg edit component's REST preview.
        if (!defined('REST_REQUEST') || !REST_REQUEST || empty($_REQUEST['lsd_editor_preview']) || !current_user_can('edit_posts')) return null;

        // Ordinary posts and pages cannot resolve an arbitrary listing on the frontend.
        $preview_post = get_post();
        if ($preview_post instanceof WP_Post && !in_array($preview_post->post_type, [LSD_Base::PTYPE_TEMPLATE, 'wp_template', 'wp_template_part'], true)) return null;

        $posts = get_posts([
            'post_type' => LSD_Base::PTYPE_LISTING,
            'post_status' => 'publish',
            'has_password' => false,
            'posts_per_page' => 1,
            'fields' => 'ids',
            'no_found_rows' => true,
        ]);

        return !empty($posts) ? new LSD_Entity_Listing((int) $posts[0]) : null;
    }

    public static function prepare(LSD_Entity_Listing $listing): ?string
    {
        // Description blocks render before the traditional content filter replaces their output.
        // Let that outer renderer display the denial message instead of consuming it here.
        $defer_access = self::in_own_content($listing)
            && is_singular(LSD_Base::PTYPE_LISTING) && in_the_loop() && is_main_query();
        $access = LSD_PTypes_Listing_Single::access_output($listing, !$defer_access);
        if ($access !== null) return $access;

        LSD_PTypes_Listing_Single::visit($listing);
        return null;
    }

    public static function with_post(LSD_Entity_Listing $listing, callable $callback): string
    {
        global $post;

        $previous = $post ?? null;
        $post = $listing->post;

        try
        {
            return (string) $callback();
        }
        finally
        {
            $post = $previous;
        }
    }

    private static function valid($listing): bool
    {
        return $listing instanceof LSD_Entity_Listing && self::listing_post($listing->post);
    }

    private static function can_preview($post): bool
    {
        if (!self::listing_post($post)) return false;

        return ($post->post_status === 'publish' && $post->post_password === '') || current_user_can('edit_post', $post->ID);
    }

    private static function listing_post($post): bool
    {
        return $post instanceof WP_Post && $post->post_type === LSD_Base::PTYPE_LISTING;
    }
}
