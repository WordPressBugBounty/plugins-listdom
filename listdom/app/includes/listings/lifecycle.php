<?php

/** Defer announcements for managed drafts without replacing ordinary save hooks. */
class LSD_Listings_Lifecycle
{
    public const META = '_lsd_managed_new_listing';

    public static function init(): void
    {
        // After post/meta saves, including publication through the normal editor.
        add_action('wp_after_insert_post', [self::class, 'announce'], 20);
    }

    public static function unannounced(int $id): bool
    {
        return in_array(get_post_meta($id, self::META, true), ['preparing', 'waiting'], true);
    }

    public static function announce(int $id): void
    {
        if (get_post_meta($id, self::META, true) !== 'waiting') return;
        if (get_post_type($id) !== LSD_Base::PTYPE_LISTING || !in_array(get_post_status($id), ['pending', 'publish'], true)) return;

        // Claim before external callbacks: retries must never dispatch twice after
        // a callback interruption. This does not promise delivery by third parties.
        if (!update_post_meta($id, self::META, 'announced', 'waiting')) return;

        do_action('lsd_new_listing', $id);
    }
}
