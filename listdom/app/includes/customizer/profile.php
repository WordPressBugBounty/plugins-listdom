<?php

class LSD_Customizer_Profile extends LSD_Customizer
{
    public function options(): array
    {
        return [
            'profile' => [
                'title' => esc_html__('Profile', 'listdom'),
                'display_sections_force' => 1,
                'sections' => [
                    'profile' => [
                        'title' => esc_html__('Profile', 'listdom'),
                        'groups' => self::profile_groups(),
                    ],
                    'users' => [
                        'title' => esc_html__('Users', 'listdom'),
                        'groups' => self::users_groups(),
                    ],
                ],
            ],
        ];
    }

    public static function profile_groups(): array
    {
        return [
            'section' => [
                'title' => esc_html__('Sections', 'listdom'),
                'sub_title' => esc_html__("Define the profile's section appearance.", 'listdom'),
                'divisions' => [
                    'container' => [
                        'title' => esc_html__('Container', 'listdom'),
                        'fields' => self::fields([],['container_bg', 'border', 'padding' ]),
                    ],
                    'title' => [
                        'title' => esc_html__('Title', 'listdom'),
                        'fields' => self::fields([
                            'text_color' => '#000',
                            'typography' => [
                                'family' => 'poppins',
                                'size' => 23,
                                'line_height' => 32,
                                'weight' => 500,
                                'align' => 'inherit'
                            ]
                        ],['text_color' ,'typography' ]),
                    ],
                ],
            ],
            'content' => [
                'title' => esc_html__('Content', 'listdom'),
                'sub_title' => esc_html__("Define the frontend profile's appearance in hover state, where 'hover' applies styling when the user moves the cursor over it.", 'listdom'),
                'divisions' => [
                    'name' => [
                        'title' => esc_html__('Author Name', 'listdom'),
                        'fields' => self::fields([
                             'text_color' => '#000',
                            'typography' => [
                                'family' => 'poppins',
                                'size' => 23,
                                'line_height' => 26,
                                'weight' => 500,
                                'align' => 'inherit'
                            ]
                        ], ['text_color', 'typography']),
                    ],
                    'job_title' => [
                        'title' => esc_html__('Job Title', 'listdom'),
                        'fields' => self::fields([
                             'text_color' => '#000',
                            'typography' => [
                                'family' => 'poppins',
                                'size' => 16,
                                'line_height' => 'inherit',
                                'weight' => 400,
                                'align' => 'inherit'
                            ]
                        ], ['text_color', 'typography']),
                    ],
                    'count' => [
                        'title' => esc_html__('Count', 'listdom'),
                        'fields' => self::fields([
                             'text_color' => '#7E8086',
                            'icon_color' => '#0AB0FE',
                            'typography' => [
                                'family' => 'poppins',
                                'size' => 16,
                                'line_height' => 32,
                                'weight' => 500,
                                'align' => 'inherit'
                            ]
                        ], ['text_color', 'typography', 'icon_color']),
                    ],
                    'contact_info' => [
                        'title' => esc_html__('Contact Info', 'listdom'),
                        'fields' => self::fields([
                             'text_color' => '#333',
                             'icon_color' => '#000',
                             'social_icon_color' => '#000',
                            'typography' => [
                                'family' => 'poppins',
                                'size' => 16,
                                'line_height' => 32,
                                'weight' => 400,
                                'align' => 'inherit'
                            ]
                        ], ['text_color', 'typography', 'icon_color', 'socials_icon_color']),
                    ],
                    'bio' => [
                        'title' => esc_html__('Bio', 'listdom'),
                        'fields' => self::fields([
                             'text_color' => '#7E8086',
                            'typography' => [
                                'family' => 'poppins',
                                'size' => 16,
                                'line_height' => 25,
                                'weight' => 400,
                                'align' => 'inherit'
                            ]
                        ], ['text_color', 'typography']),
                    ],
                ],
            ],
        ];
    }
    public static function users_groups(): array
    {
        return [
            'cards' => [
                'title' => esc_html__('Cards', 'listdom'),
                'sub_title' => esc_html__("Define the frontend profile's appearance in hover state, where 'hover' applies styling when the user moves the cursor over it.", 'listdom'),
                'divisions' => [
                    'item' => [
                        'title' => esc_html__('Item', 'listdom'),
                        'fields' => self::fields([
                            'padding' => [
                                'top' => 20,
                                'bottom' => 20,
                                'left' => 20,
                                'right' => 20,
                            ],
                            'border' => [
                                'top' => 1,
                                'right' => 1,
                                'bottom' => 1,
                                'left' => 1,
                                'style' => 'solid',
                                'color' => '#EEEEEE',
                                'radius' => 30
                            ]
                        ],['container_bg', 'border', 'padding' ]),
                    ],
                    'name' => [
                        'title' => esc_html__('Author Name', 'listdom'),
                        'fields' => self::fields([
                            'text_color' => '#000',
                            'typography' => [
                                'family' => 'poppins',
                                'size' => 18,
                                'line_height' => 25,
                                'weight' => 500,
                                'align' => 'inherit'
                            ]
                        ], ['text_color', 'typography']),
                    ],
                    'job_title' => [
                        'title' => esc_html__('Job Title', 'listdom'),
                        'fields' => self::fields([
                            'text_color' => '#666',
                            'typography' => [
                                'family' => 'poppins',
                                'size' => 16,
                                'line_height' => 25,
                                'weight' => 400,
                                'align' => 'inherit'
                            ]
                        ], ['text_color', 'typography']),
                    ],
                    'contact_info' => [
                        'title' => esc_html__('Contact Info', 'listdom'),
                        'fields' => self::fields([
                            'icon_color' => '#333333',
                        ], ['icon_color']),
                    ],
                    'bio' => [
                        'title' => esc_html__('Bio', 'listdom'),
                        'fields' => self::fields([
                            'text_color' => '#666',
                            'typography' => [
                                'family' => 'poppins',
                                'size' => 14,
                                'line_height' => 25,
                                'weight' => 400,
                                'align' => 'justify'
                            ]
                        ], ['text_color', 'typography']),
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
                'default' => $defaults['container_bg'] ?? '#fff',
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
                'default' => $defaults['icon_color'] ?? '#000',
            ],
            'socials_icon_color' => [
                'type' => 'color',
                'title' => esc_html__('Social Icon Color', 'listdom'),
                'default' => $defaults['socials_icon_color'] ?? '#000',
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
                    'radius' => $defaults['border']['radius'] ?? 5,
                ],
            ],
            'padding' => [
                'type' => 'padding',
                'title' => esc_html__('Padding', 'listdom'),
                'default' => [
                    'top' => $defaults['padding']['top'] ?? 30,
                    'right' => $defaults['padding']['right'] ?? 30,
                    'bottom' => $defaults['padding']['bottom'] ?? 30,
                    'left' => $defaults['padding']['left'] ?? 30,
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
                'size_units' => ['px', 'em', 'rem', '%'],
                'line_height_units' => ['px', 'em', 'rem', '%'],
            ],
        ];

        $fields = empty($selected_fields_key) ? $fields : array_intersect_key($fields, array_flip($selected_fields_key));

        return apply_filters('lsd_customizer_frontend_profile_fields', $fields);
    }
}
