<?php

class LSD_Integrations_Blockeditor_Layout
{
    /** Native current and legacy layout blocks only render published saved views. */
    public static function eligible($layout): bool
    {
        return $layout instanceof WP_Post && $layout->post_type === LSD_Base::PTYPE_SHORTCODE && $layout->post_status === 'publish';
    }

    /** Legacy blocks store the ID in their exact saved shortcode, not the block-name suffix. */
    public static function legacy_id(string $content): int
    {
        $saved = html_entity_decode($content, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        return preg_match('~^\s*\[listdom id="([1-9][0-9]*)"\]\s*$~', $saved, $matches) ? (int) $matches[1] : 0;
    }

    /** Synced blocks are reusable public content only when published and unprotected. */
    public static function reusable_eligible(WP_Post $post): bool
    {
        return $post->post_status === 'publish' && $post->post_password === '';
    }

    public function render($attributes): string
    {
        $id = isset($attributes['layoutId']) ? absint($attributes['layoutId']) : 0;
        if (!$id) return '';

        $layout = get_post($id);
        if (!self::eligible($layout)) return '';

        $styles_before = wp_styles()->queue;
        $html = do_shortcode('[listdom id="' . $id . '"]');
        if (defined('REST_REQUEST') && REST_REQUEST && !empty($_REQUEST['lsd_editor_preview'])) return $html;

        if (function_exists('get_block_wrapper_attributes'))
        {
            $wrapper = get_block_wrapper_attributes(['class' => 'wp-block-listdom-layout wui']);
        }
        else
        {
            $class = 'wp-block-listdom-layout wui';
            if (!empty($attributes['className'])) $class .= ' ' . $attributes['className'];
            $wrapper = 'class="' . esc_attr($class) . '"';
        }

        return LSD_Integrations_Blockeditor_Assets::frontend($styles_before) . '<div ' . $wrapper . '>' . $html . '</div>';
    }
}
