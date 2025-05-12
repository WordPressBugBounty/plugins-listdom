<?php

class LSD_Customizer_Single extends LSD_Customizer
{
    public function options(): array
    {
        return [
            'single' => [
                'title' => esc_html__('Single Listing', 'listdom'),
                'sections' => [
                    'features' => [
                        'title' => esc_html__('Features', 'listdom'),
                        'groups' => [
                            'icons' => [
                                'title' => esc_html__('Icons', 'listdom'),
                                'divisions' => [
                                    '_' => [
                                        'fields' => [
                                            'bg_color' => [
                                                'type' => 'color',
                                                'title' => esc_html__('Icon Background Color', 'listdom'),
                                                'default' => '#e6f7ff',
                                            ],
                                            'text_color' => [
                                                'type' => 'color',
                                                'title' => esc_html__('Icon Text Color', 'listdom'),
                                                'default' => '#0ab0fe',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'reviews' => [
                        'title' => esc_html__('Reviews', 'listdom'),
                        'groups' => [
                            'review_tabs' => [
                                'title' => esc_html__('Tabs', 'listdom'),
                                'divisions' => [
                                    'normal' => [
                                        'title' => esc_html__('Normal', 'listdom'),
                                        'sub_title' => esc_html__("Define the tab's appearance in normal state.", 'listdom'),
                                        'fields' => LSD_Customizer_Tabs::fields(),
                                    ],
                                    'hover' => [
                                        'title' => esc_html__('Hover / Active', 'listdom'),
                                        'sub_title' => esc_html__("Define the tab's appearance in hover state, where 'hover' applies styling when the user moves the cursor over it.", 'listdom'),
                                        'fields' => LSD_Customizer_Tabs::fields([
                                            'bg_color' => '#0ab0fe',
                                            'text_color' => '#fff',
                                        ]),
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'price' => [
                        'title' => esc_html__('Price', 'listdom'),
                        'groups' => [
                            'price' => [
                                'title' => esc_html__('Price', 'listdom'),
                                'divisions' => [
                                    'normal' => [
                                        'title' => esc_html__('Normal', 'listdom'),
                                        'fields' => [
                                            'bg_color' => [
                                                'type' => 'color',
                                                'title' => esc_html__('Background Color', 'listdom'),
                                                'default' => '#0ab0fe',
                                            ],
                                            'text_color' => [
                                                'type' => 'color',
                                                'title' => esc_html__('Text Color', 'listdom'),
                                                'default' => '#e6f7ff',
                                            ],
                                            'typography' => [
                                                'type' => 'typography',
                                                'title' => esc_html__('Typography', 'listdom'),
                                                'default' => [
                                                    'family' => $defaults['typography']['family'] ?? 'inherit',
                                                    'weight' => $defaults['typography']['weight'] ?? 'inherit',
                                                    'align' => $defaults['typography']['align'] ?? 'inherit',
                                                    'size' => $defaults['typography']['size'] ?? '16',
                                                    'line_height' => $defaults['typography']['line_height'] ?? '25',
                                                ],
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
                                                    'color' => $defaults['border']['color'] ?? '#fff',
                                                    'radius' => $defaults['border']['radius'] ?? 27,
                                                ],
                                            ],
                                            'padding' => [
                                                'type' => 'padding',
                                                'title' => esc_html__('Padding', 'listdom'),
                                                'default' => [
                                                    'top' => $defaults['padding']['top'] ?? 6,
                                                    'right' => $defaults['padding']['right'] ?? 20,
                                                    'bottom' => $defaults['padding']['bottom'] ?? 6,
                                                    'left' => $defaults['padding']['left'] ?? 20,
                                                ],
                                            ],
                                        ],
                                    ],
                                    'hover' => [
                                        'title' => esc_html__('Hover', 'listdom'),
                                        'fields' => [
                                            'bg_color' => [
                                                'type' => 'color',
                                                'title' => esc_html__('Background Color', 'listdom'),
                                                'default' => '#0ab0fe',
                                            ],
                                            'text_color' => [
                                                'type' => 'color',
                                                'title' => esc_html__('Text Color', 'listdom'),
                                                'default' => '#e6f7ff',
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
                                                    'color' => $defaults['border']['color'] ?? '#fff',
                                                    'radius' => $defaults['border']['radius'] ?? 27,
                                                ],
                                            ],
                                            'padding' => [
                                                'type' => 'padding',
                                                'title' => esc_html__('Padding', 'listdom'),
                                                'default' => [
                                                    'top' => $defaults['padding']['top'] ?? 6,
                                                    'right' => $defaults['padding']['right'] ?? 20,
                                                    'bottom' => $defaults['padding']['bottom'] ?? 6,
                                                    'left' => $defaults['padding']['left'] ?? 20,
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
