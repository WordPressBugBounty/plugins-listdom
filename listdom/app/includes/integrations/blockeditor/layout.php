<?php

class LSD_Integrations_Blockeditor_Layout
{
    public function render($attributes): string
    {
        $id = isset($attributes['layoutId']) ? absint($attributes['layoutId']) : 0;
        if (!$id) return '';

        $layout = get_post($id);
        if (!$layout || $layout->post_type !== LSD_Base::PTYPE_SHORTCODE || $layout->post_status !== 'publish') return '';

        $styles_before = wp_styles()->queue;
        $html = do_shortcode('[listdom id="' . $id . '"]');
        if (defined('REST_REQUEST') && REST_REQUEST && !empty($_REQUEST['lsd_editor_preview'])) return $html;

        if (function_exists('get_block_wrapper_attributes'))
        {
            $wrapper = get_block_wrapper_attributes(['class' => 'wp-block-listdom-layout']);
        }
        else
        {
            $class = 'wp-block-listdom-layout';
            if (!empty($attributes['className'])) $class .= ' ' . $attributes['className'];
            $wrapper = 'class="' . esc_attr($class) . '"';
        }

        return LSD_Integrations_Blockeditor_Assets::frontend($styles_before) . '<div ' . $wrapper . '>' . $html . '</div>';
    }
}
