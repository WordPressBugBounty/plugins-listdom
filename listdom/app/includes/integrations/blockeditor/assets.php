<?php

/** Load Listdom frontend assets when a block renders outside the queried post. */
class LSD_Integrations_Blockeditor_Assets
{
    public static function frontend(?array $before = null): string
    {
        if (is_admin() || (defined('REST_REQUEST') && REST_REQUEST)) return '';

        if ($before === null) $before = wp_styles()->queue;
        if (!wp_style_is('lsd-frontend', 'enqueued')) (new LSD_Assets())->site(true);

        // Blocks in patterns, template parts, and widgets may render after wp_head.
        if (!did_action('wp_head')) return '';

        $added = array_diff(wp_styles()->queue, $before);
        if (!$added) return '';

        ob_start();
        wp_print_styles($added);
        return (string) ob_get_clean();
    }
}
