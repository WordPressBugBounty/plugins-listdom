<?php

class LSD_Shortcodes extends LSD_Base
{
    public function init()
    {
        // Listdom Shortcode
        (new LSD_Shortcodes_Listdom())->init();

        // SimpleMap Shortcode
        (new LSD_Shortcodes_Simplemap())->init();

        // Location Shortcode
        (new LSD_Shortcodes_Location())->init();

        // Category Shortcode
        (new LSD_Shortcodes_Category())->init();

        // Tag Shortcode
        (new LSD_Shortcodes_Tag())->init();

        // Feature Shortcode
        (new LSD_Shortcodes_Feature())->init();

        // Label Shortcode
        (new LSD_Shortcodes_Label())->init();

        // Taxonomy Cloud Shortcode
        (new LSD_Shortcodes_TaxonomyCloud())->init();

        // Terms Shortcode
        (new LSD_Shortcodes_Terms())->init();

        // Auth Shortcode
        (new LSD_Shortcodes_Auth())->init();

        // Profile Shortcode
        (new LSD_Shortcodes_Profile())->init();

        // Checkout Shortcode
        (new LSD_Shortcodes_Checkout())->init();

        // Order Summary Shortcode
        (new LSD_Shortcodes_OrderSummary())->init();
    }

    public function parse($post_id, $atts = []): array
    {
        $post_atts = [];
        if ($post_id) $post_atts = $this->get_post_meta($post_id);

        // Overwrite values passed directly from shortcode
        $skin = $post_atts['lsd_display']['skin'] ?? $this->get_default_skin();
        foreach ($atts as $key => $value)
        {
            if (isset($post_atts['lsd_display'][$skin][$key]))
            {
                $post_atts['lsd_display'][$skin][$key] = $value;
            }
        }

        return wp_parse_args($atts, $post_atts);
    }

    public function get_default_skin(): string
    {
        return 'grid';
    }

    protected function is_block_editor(): bool
    {
        if (!is_admin() || !function_exists('get_current_screen')) return false;

        $screen = get_current_screen();
        return $screen && method_exists($screen, 'is_block_editor') && $screen->is_block_editor();
    }

    protected function shortcode_placeholder(string $tag, $atts = []): string
    {
        $shortcode = '[' . $tag;

        if (is_array($atts))
        {
            foreach ($atts as $key => $value)
            {
                if (!is_string($key) || is_array($value) || is_object($value)) continue;

                $shortcode .= ' ' . $key . '="' . esc_attr((string) $value) . '"';
            }
        }

        return '<code>' . esc_html($shortcode . ']') . '</code>';
    }
}
