<?php

class LSD_Customizer_Buttons extends LSD_Customizer
{
    public function options(): array
    {
        // Button Groups
        $button_groups = [
            'normal' => [
                'title' => esc_html__('Normal', 'listdom'),
                'sub_title' => esc_html__("Define the button's appearance in normal state.", 'listdom'),
                'divisions' => [
                    '_' => [
                        'fields' => self::fields([
                            'padding' => [
                                'top' => 6,
                                'right' => 20,
                                'bottom' => 6,
                                'left' => 20,
                            ],
                        ]),
                    ],
                ],
            ],
            'hover' => [
                'title' => esc_html__('Hover', 'listdom'),
                'sub_title' => esc_html__("Define the button's appearance in hover state, where 'hover' applies styling when the user moves the cursor over it.", 'listdom'),
                'divisions' => [
                    '_' => [
                        'fields' => self::fields(['bg1' => '#306be6'], ['bg1', 'bg2', 'border', 'text']),
                    ],
                ],
            ],
        ];

        return [
            'buttons' => [
                'title' => esc_html__('Buttons', 'listdom'),
                'sections' => [
                    'primary_button' => [
                        'title' => esc_html__('General', 'listdom'),
                        'groups' => $button_groups,
                    ],
                    'search_button' => [
                        'title' => esc_html__('Search', 'listdom'),
                        'inherit' => [
                            'key' => 'buttons.primary_button',
                            'text' => esc_html__('Inherit from General Button.', 'listdom'),
                            'enabled' => 0,
                        ],
                        'groups' => [
                            'normal' => [
                                'title' => esc_html__('Normal', 'listdom'),
                                'sub_title' => esc_html__("Define the button's appearance in normal state.", 'listdom'),
                                'divisions' => [
                                    '_' => [
                                        'fields' => self::fields([
                                            'padding' => [
                                                'top' => 9,
                                                'right' => 20,
                                                'bottom' => 9,
                                                'left' => 20,
                                            ],
                                        ]),
                                    ],
                                ],
                            ],
                            'hover' => [
                                'title' => esc_html__('Hover', 'listdom'),
                                'sub_title' => esc_html__("Define the button's appearance in hover state, where 'hover' applies styling when the user moves the cursor over it.", 'listdom'),
                                'divisions' => [
                                    '_' => [
                                        'fields' => self::fields(['bg1' => '#306be6'], ['bg1', 'bg2', 'border', 'text']),
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'pagination_button' => [
                        'title' => esc_html__('Pagination', 'listdom'),
                        'description' => esc_html__('Load More and Pagination Buttons.', 'listdom'),
                        'inherit' => [
                            'key' => 'buttons.primary_button',
                            'text' => esc_html__('Inherit from General Button.', 'listdom'),
                            'enabled' => 0,
                        ],
                        'groups' => [
                            'normal' => [
                                'title' => esc_html__('Normal', 'listdom'),
                                'divisions' => [
                                    '_' => [
                                        'fields' => self::fields([
                                            'bg1' => '#e6f7ff',
                                            'bg2' => '#e6f7ff',
                                            'text' => '#0ab0fe',
                                            'border' => [
                                                'style' => 'solid',
                                                'color' => '#bceaff',
                                                'top' => 1,
                                                'left' => 1,
                                                'right' => 1,
                                                'bottom' => 1,
                                                'radius' => 4,
                                            ],
                                            'padding' => [
                                                'top' => 6,
                                                'right' => 20,
                                                'bottom' => 6,
                                                'left' => 20,
                                            ],
                                        ]),
                                    ],
                                ],
                            ],
                            'hover' => [
                                'title' => esc_html__('Hover', 'listdom'),
                                'divisions' => [
                                    '_' => [
                                        'fields' => self::fields([
                                            'bg1' => '#306be6',
                                            'bg2' => '#306be6',
                                            'text' => '#fff',
                                            'border' => [
                                                'style' => 'solid',
                                                'color' => '#bceaff',
                                                'top' => 1,
                                                'left' => 1,
                                                'right' => 1,
                                                'bottom' => 1,
                                                'radius' => 4,
                                            ],
                                        ], ['bg1', 'bg2', 'border', 'text']),
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'secondary_button' => [
                        'title' => esc_html__('Light', 'listdom'),
                        'inherit' => [
                            'key' => 'buttons.primary_button',
                            'text' => esc_html__('Inherit from General Button.', 'listdom'),
                            'enabled' => 0,
                        ],
                        'groups' => [
                            'normal' => [
                                'title' => esc_html__('Normal', 'listdom'),
                                'divisions' => [
                                    '_' => [
                                        'fields' => self::fields([
                                            'bg1' => '#e6f7ff',
                                            'bg2' => '#e6f7ff',
                                            'text' => '#0ab0fe',
                                            'border' => [
                                                'style' => 'solid',
                                                'color' => '#bceaff',
                                                'top' => 1,
                                                'left' => 1,
                                                'right' => 1,
                                                'bottom' => 1,
                                                'radius' => 4,
                                            ],
                                            'padding' => [
                                                'top' => 6,
                                                'right' => 20,
                                                'bottom' => 6,
                                                'left' => 20,
                                            ],
                                        ]),
                                    ],
                                ],
                            ],
                            'hover' => [
                                'title' => esc_html__('Hover', 'listdom'),
                                'divisions' => [
                                    '_' => [
                                        'fields' => self::fields([
                                            'bg1' => '#306be6',
                                            'bg2' => '#306be6',
                                            'text' => '#fff',
                                            'border' => [
                                                'style' => 'solid',
                                                'color' => '#bceaff',
                                                'top' => 1,
                                                'left' => 1,
                                                'right' => 1,
                                                'bottom' => 1,
                                                'radius' => 4,
                                            ],
                                        ], ['bg1', 'bg2', 'border', 'text']),
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'solid_button' => [
                        'title' => esc_html__('Solid', 'listdom'),
                        'inherit' => [
                            'key' => 'buttons.primary_button',
                            'text' => esc_html__('Inherit from General Button.', 'listdom'),
                            'enabled' => 0,
                        ],
                        'groups' => [
                            'normal' => [
                                'title' => esc_html__('Normal', 'listdom'),
                                'divisions' => [
                                    '_' => [
                                        'fields' => self::fields([
                                            'bg1' => '#33c6ff',
                                            'bg2' => '#33c6ff',
                                            'text' => '#fff',
                                            'border' => [
                                                'style' => 'none',
                                                'color' => 'transparent',
                                                'top' => 0,
                                                'left' => 0,
                                                'right' => 0,
                                                'bottom' => 0,
                                                'radius' => 4,
                                            ],
                                            'padding' => [
                                                'top' => 6,
                                                'right' => 20,
                                                'bottom' => 6,
                                                'left' => 20,
                                            ],
                                        ]),
                                    ],
                                ],
                            ],
                            'hover' => [
                                'title' => esc_html__('Hover', 'listdom'),
                                'divisions' => [
                                    '_' => [
                                        'fields' => self::fields([
                                            'bg1' => '#29a6e1',
                                            'bg2' => '#29a6e1',
                                            'text' => '#eee',
                                            'border' => [
                                                'style' => 'none',
                                                'color' => 'transparent',
                                                'top' => 0,
                                                'left' => 0,
                                                'right' => 0,
                                                'bottom' => 0,
                                                'radius' => 4,
                                            ],
                                        ], ['bg1', 'bg2', 'border', 'text']),
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
            'bg1' => [
                'type' => 'color',
                'title' => esc_html__('Background Color 1', 'listdom'),
                'default' => $defaults['bg1'] ?? '#33c6ff',
            ],
            'bg2' => [
                'type' => 'color',
                'title' => esc_html__('Background Color 2', 'listdom'),
                'default' => $defaults['bg2'] ?? '#306be6',
            ],
            'text' => [
                'type' => 'color',
                'title' => esc_html__('Text Color', 'listdom'),
                'default' => $defaults['text'] ?? '#ffffff',
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
                    'color' => $defaults['border']['color'] ?? '',
                    'radius' => $defaults['border']['radius'] ?? 5,
                ],
            ],
            'typography' => [
                'type' => 'typography',
                'title' => esc_html__('Typography', 'listdom'),
                'default' => [
                    'family' => $defaults['typography']['family'] ?? 'inherit',
                    'weight' => $defaults['typography']['weight'] ?? 'inherit',
                    'align' => $defaults['typography']['align'] ?? 'center',
                    'size' => $defaults['typography']['size'] ?? 15,
                    'line_height' => $defaults['typography']['line_height'] ?? 32,
                ],
                'size_units' => ['px', 'em', 'rem', '%'],
                'line_height_units' => ['px', 'em', 'rem', '%'],
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
        ];

        $fields = empty($selected_fields_key) ? $fields : array_intersect_key($fields, array_flip($selected_fields_key));

        return apply_filters('lsd_customizer_button_fields', $fields);
    }
}
