<?php

class LSD_Portal_Access
{
    public const SHORTCODE = 'listdom-portal-access';

    public static function init(): void
    {
        add_shortcode(self::SHORTCODE, [self::class, 'shortcode']);
        add_filter('the_content', [self::class, 'filter_content'], 0);
        add_filter('the_excerpt', [self::class, 'filter_content'], 0);
        add_action('template_redirect', [self::class, 'protect_page']);
    }

    public static function protect_page(): void
    {
        $post = get_queried_object();
        if ($post instanceof WP_Post && has_shortcode($post->post_content, self::SHORTCODE)) LSD_Portal::private_response();
    }

    public static function allows(string $entitlement): bool
    {
        LSD_Portal::private_response();
        $user_id = get_current_user_id();
        if ($entitlement === '' || !$user_id || is_feed()) return false;
        return apply_filters('lsd_portal_has_entitlement', false, $user_id, $entitlement) === true;
    }

    public static function prompt(string $entitlement): string
    {
        return (string) apply_filters(
            'lsd_portal_entitlement_prompt',
            LSD_Base::alert(esc_html__('This content is unavailable.', 'listdom'), 'info'),
            $entitlement
        );
    }

    public static function shortcode($atts, ?string $content = null): string
    {
        $entitlement = self::key($atts);
        if (!self::allows($entitlement)) return self::prompt($entitlement);
        return do_shortcode($content ?? '');
    }

    public static function filter_content(string $content): string
    {
        if (strpos($content, '[' . self::SHORTCODE) === false) return $content;

        // Remove inaccessible sections before WordPress processes blocks, embeds, or inner shortcodes.
        $filtered = preg_replace_callback('/' . get_shortcode_regex([self::SHORTCODE]) . '/s', static function (array $match): string
        {
            if ($match[1] === '[' && $match[6] === ']') return $match[0];
            $entitlement = self::key(shortcode_parse_atts($match[3]));
            return self::allows($entitlement) ? $match[0] : self::prompt($entitlement);
        }, $content);
        return $filtered ?? self::prompt('');
    }

    private static function key($atts): string
    {
        return isset($atts['entitlement']) && is_string($atts['entitlement']) ? sanitize_key($atts['entitlement']) : '';
    }
}
