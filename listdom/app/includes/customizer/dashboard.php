<?php

class LSD_Customizer_Dashboard extends LSD_Customizer
{
    public function options(): array
    {
        return [
            'dashboard' => [
                'title' => esc_html__('Frontend Dashboard', 'listdom'),
                'display_sections_force' => 1,
                'sections' => [
                    'menu' => [
                        'title' => esc_html__('Menu', 'listdom'),
                        'groups' => self::menu_groups(),
                    ],
                ],
            ],
        ];
    }

    public static function menu_groups(): array
    {
        return [
            'normal' => [
                'title' => esc_html__('Normal', 'listdom'),
                'sub_title' => esc_html__("Define the frontend dashboard's appearance in normal state.", 'listdom'),
                'divisions' => [
                    '_' => [
                        'fields' => self::fields([
                            'typography' => [
                                'size' => 14,
                                'line_height' => 26,
                            ],
                        ]),
                    ],
                ],
            ],
            'hover' => [
                'title' => esc_html__('Hover / Focus', 'listdom'),
                'sub_title' => esc_html__("Define the frontend dashboard's appearance in hover state, where 'hover' applies styling when the user moves the cursor over it.", 'listdom'),
                'divisions' => [
                    '_' => [
                        'fields' => self::fields([
                            'bg_color' => '#24b7ff',
                            'border' => [
                                'color' => '#ffffff29',
                            ],
                        ], ['bg_color', 'text_color', 'border']),
                    ],
                ],
            ],
        ];
    }

    public static function fields($defaults = [], $selected_fields_key = []): array
    {
        $fields = [
            'container_bg' => [
                'type' => 'color',
                'title' => esc_html__('Container Background Color', 'listdom'),
                'default' => $defaults['container_bg'] ?? '#0ab0fe',
            ],
            'bg_color' => [
                'type' => 'color',
                'title' => esc_html__('Background Color', 'listdom'),
                'default' => $defaults['bg_color'] ?? '#0ab0fe',
            ],
            'text_color' => [
                'type' => 'color',
                'title' => esc_html__('Text Color', 'listdom'),
                'default' => $defaults['text_color'] ?? '#fff',
            ],
            'icon_color' => [
                'type' => 'color',
                'title' => esc_html__('Icon Color', 'listdom'),
                'default' => $defaults['icon_color'] ?? '#fff',
            ],
            'border' => [
                'type' => 'border',
                'title' => esc_html__('Border', 'listdom'),
                'default' => [
                    'top' => $defaults['border']['top'] ?? 1,
                    'right' => $defaults['border']['right'] ?? 1,
                    'bottom' => $defaults['border']['bottom'] ?? 1,
                    'left' => $defaults['border']['left'] ?? 1,
                    'style' => $defaults['border']['style'] ?? 'solid',
                    'color' => $defaults['border']['color'] ?? '#0ab0fe',
                    'radius' => $defaults['border']['radius'] ?? 5,
                ],
            ],
            'typography' => [
                'type' => 'typography',
                'title' => esc_html__('Typography', 'listdom'),
                'default' => [
                    'family' => $defaults['typography']['family'] ?? 'inherit',
                    'weight' => $defaults['typography']['weight'] ?? 'inherit',
                    'align' => $defaults['typography']['align'] ?? 'inherit',
                    'size' => $defaults['typography']['size'] ?? 'inherit',
                    'line_height' => $defaults['typography']['line_height'] ?? 'inherit',
                ],
            ],
        ];

        $fields = empty($selected_fields_key) ? $fields : array_intersect_key($fields, array_flip($selected_fields_key));

        return apply_filters('lsd_customizer_frontend_dashboard_fields', $fields);
    }
}
