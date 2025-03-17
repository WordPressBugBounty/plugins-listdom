<?php

class LSD_Customizer_Tabs extends LSD_Customizer
{
    public static function fields($defaults = [], $selected_fields_key = []): array
    {
        $fields = [
            'bg_color' => [
                'type' => 'color',
                'title' => esc_html__('Background Color', 'listdom'),
                'default' => $defaults['bg_color'] ?? '#fff',
            ],
            'text_color' => [
                'type' => 'color',
                'title' => esc_html__('Tabs Text Color', 'listdom'),
                'default' => $defaults['text_color'] ?? '#0ab0fe',
            ],
            'border' => [
                'type' => 'border',
                'title' => esc_html__('Tabs Border', 'listdom'),
                'default' => [
                    'top' => $defaults['border']['top'] ?? 0,
                    'right' => $defaults['border']['right'] ?? 0,
                    'bottom' => $defaults['border']['bottom'] ?? 0,
                    'left' => $defaults['border']['left'] ?? 0,
                    'style' => $defaults['border']['style'] ?? 'none',
                    'color' => $defaults['border']['color'] ?? '',
                    'radius' => $defaults['border']['radius'] ?? 5,
                ],
            ],
        ];

        $fields = empty($selected_fields_key) ? $fields : array_intersect_key($fields, array_flip($selected_fields_key));

        return apply_filters('lsd_customizer_tabs_fields', $fields);
    }
}
