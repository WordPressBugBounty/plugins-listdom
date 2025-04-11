<?php

class LSD_Customizer_Taxonomies extends LSD_Customizer
{
    public function options($fields = []): array
    {
        return [
            'taxonomies' => [
                'title' => esc_html__('Taxonomies', 'listdom'),
                'display_sections_force' => 1,
                'sections' => [
                    'widgets' => [
                        'title' => esc_html__('Widgets', 'listdom'),
                        'groups' => [
                            'cloud' => [
                                'title' => esc_html__('Cloud Widgets', 'listdom'),
                                'divisions' => [
                                    'normal' => [
                                        'title' => esc_html__('Normal', 'listdom'),
                                        'fields' => self::fields([
                                            'bg' => '#dadadb',
                                            'text' => '#3b3b3b',
                                            'border' => [
                                                'radius' => 27,
                                            ],
                                            'typography' => [
                                                'size' => 13,
                                                'line_height' => 26,
                                            ],
                                            'padding' => [
                                                'top' => 5,
                                                'right' => 10,
                                                'bottom' => 5,
                                                'left' => 10,
                                            ],
                                        ]),
                                    ],
                                    'hover' => [
                                        'title' => esc_html__('Hover', 'listdom'),
                                        'fields' => self::fields([
                                            'bg' => '#dadadb',
                                            'text' => '#33c6ff',
                                            'border' => [
                                                'radius' => 27,
                                            ],
                                            'padding' => [
                                                'top' => 5,
                                                'right' => 10,
                                                'bottom' => 5,
                                                'left' => 10,
                                            ],
                                        ], ['bg', 'text', 'border', 'padding']),
                                    ],
                                ],
                            ],
                            'terms' => [
                                'title' => esc_html__('Terms Widgets', 'listdom'),
                                'divisions' => [
                                    'normal' => [
                                        'title' => esc_html__('Normal', 'listdom'),
                                        'fields' => self::fields([
                                            'bg' => 'transparent',
                                            'text' => '#8d919c',
                                            'typography' => [
                                                'size' => 13,
                                                'line_height' => 26,
                                            ],
                                            'padding' => [
                                                'top' => 2,
                                                'right' => 2,
                                                'bottom' => 2,
                                                'left' => 2,
                                            ],
                                        ]),
                                    ],
                                    'hover' => [
                                        'title' => esc_html__('Hover', 'listdom'),
                                        'fields' => self::fields([
                                            'bg' => 'transparent',
                                            'text' => '#33c6ff',
                                            'padding' => [
                                                'top' => 2,
                                                'right' => 2,
                                                'bottom' => 2,
                                                'left' => 2,
                                            ],
                                        ], ['bg', 'text', 'border', 'padding']),
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    public static function fields($defaults = [], $selected_fields_key = []): array
    {
        $fields = [
            'bg' => [
                'type' => 'color',
                'title' => esc_html__('Background Color', 'listdom'),
                'default' => $defaults['bg'] ?? '#dadadb',
            ],
            'text' => [
                'type' => 'color',
                'title' => esc_html__('Text Color', 'listdom'),
                'default' => $defaults['text'] ?? '#3b3b3b',
            ],
            'border' => [
                'type' => 'border',
                'title' => esc_html__('Border', 'listdom'),
                'default' => [
                    'top' => $defaults['border']['top'] ?? 0,
                    'right' => $defaults['border']['right'] ?? 0,
                    'bottom' => $defaults['border']['bottom'] ?? 0,
                    'left' => $defaults['border']['left'] ?? 0,
                    'style' => $defaults['border']['style'] ?? 'none',
                    'color' => $defaults['border']['color'] ?? '#ececec',
                    'radius' => $defaults['border']['radius'] ?? 5,
                ],
            ],
            'padding' => [
                'type' => 'padding',
                'title' => esc_html__('Padding', 'listdom'),
                'default' => [
                    'top' => $defaults['padding']['top'] ?? 0,
                    'right' => $defaults['padding']['right'] ?? 0,
                    'bottom' => $defaults['padding']['bottom'] ?? 0,
                    'left' => $defaults['padding']['left'] ?? 0,
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

        return apply_filters('lsd_customizer_widgets_fields', $fields);
    }
}
