<?php

class LSD_Customizer_Forms extends LSD_Customizer
{
    public function options(): array
    {
        return [
            'forms' => [
                'title' => esc_html__('Forms', 'listdom'),
                'sections' => [
                    'general_forms' => [
                        'title' => esc_html__('General', 'listdom'),
                        'groups' => $this->general_groups(),
                    ],
                    'search_forms' => [
                        'title' => esc_html__('Search', 'listdom'),
                        'groups' => $this->search_groups(),
                    ],
                    'auth_forms' => [
                        'title' => esc_html__('Authentication', 'listdom'),
                        'groups' => $this->auth_groups(),
                    ],
                ],
            ],
        ];
    }

    private function general_groups(): array
    {
        return [
            'inputs' => [
                'title' => esc_html__('Inputs', 'listdom'),
                'sub_title' => esc_html__("Define the input's appearance.", 'listdom'),
                'divisions' => [
                    'normal' => [
                        'title' => esc_html__('Normal', 'listdom'),
                        'fields' => $this->fields(['typography' => ['size' => 12, 'line_height' => 26]], ['input_bg_color', 'text', 'placeholder', 'border', 'typography']),
                    ],
                    'hover' => [
                        'title' => esc_html__('Hover / Focus', 'listdom'),
                        'fields' => $this->fields(['border' => ['color' => '#306be6']], ['input_bg_color', 'border']),
                    ],
                ],
            ],
        ];
    }

    private function search_groups(): array
    {
        return [
            'form' => [
                'title' => esc_html__('Form', 'listdom'),
                'sub_title' => esc_html__("Define the form's appearance.", 'listdom'),
                'divisions' => [
                    '_' => [
                        'fields' => $this->fields([], ['form_bg_color']),
                    ],
                ]
            ],
            'icon' => [
                'title' => esc_html__('Icon', 'listdom'),
                'sub_title' => esc_html__("Define the icon's appearance.", 'listdom'),
                'divisions' => [
                    '_' => [
                        'fields' => $this->fields([], ['icon_color']),
                    ],
                ]
            ],
            'inputs' => [
                'title' => esc_html__('Inputs', 'listdom'),
                'sub_title' => esc_html__("Define the input's appearance.", 'listdom'),
                'divisions' => [
                    'normal' => [
                        'title' => esc_html__('Normal', 'listdom'),
                        'fields' => $this->fields(['typography' => ['size' => 12, 'line_height' => 26]], ['input_bg_color', 'text', 'placeholder', 'border', 'typography']),
                    ],
                    'hover' => [
                        'title' => esc_html__('Hover / Focus', 'listdom'),
                        'fields' => $this->fields(['border' => ['color' => '#306be6']], ['input_bg_color', 'border']),
                    ],
                ],
            ],
        ];
    }

    private function auth_groups(): array
    {
        return [
            'box' => [
                'title' => esc_html__('Box', 'listdom'),
                'sub_title' => esc_html__("Define the box's appearance.", 'listdom'),
                'divisions' => [
                    '_' => [
                        'fields' => [
                            'bg' => [
                                'type' => 'color',
                                'title' => esc_html__('Background Color', 'listdom'),
                                'default' => '',
                            ],
                            'padding' => [
                                'type' => 'padding',
                                'title' => esc_html__('Padding', 'listdom'),
                                'default' => [
                                    'top' => 0,
                                    'right' => 0,
                                    'bottom' => 0,
                                    'left' => 0,
                                ],
                            ],
                            'border' => [
                                'type' => 'border',
                                'title' => esc_html__('Border', 'listdom'),
                                'default' => [
                                    'top' => 0,
                                    'right' => 0,
                                    'bottom' => 0,
                                    'left' => 0,
                                    'style' => 'none',
                                    'color' => '',
                                    'radius' => 0,
                                ],
                            ],
                        ],
                    ],
                ]
            ],
            'tabs' => [
                'title' => esc_html__('Tabs', 'listdom'),
                'sub_title' => esc_html__("Define the tabs appearance.", 'listdom'),
                'divisions' => [
                    'normal' => [
                        'title' => esc_html__('Normal', 'listdom'),
                        'fields' => $this->tab_fields([
                            'bg1' => '#ffffff',
                            'bg2' => '#ffffff',
                            'text' => '#306be6',
                        ]),
                    ],
                    'hover' => [
                        'title' => esc_html__('Hover', 'listdom'),
                        'fields' => $this->tab_fields([
                            'bg1' => '#306be6',
                            'bg2' => '#306be6',
                            'text' => '#ffffff',
                        ]),
                    ],
                    'active' => [
                        'title' => esc_html__('Active', 'listdom'),
                        'fields' => $this->tab_fields([
                            'bg1' => '#33c6ff',
                            'bg2' => '#306be6',
                            'text' => '#ffffff',
                            'border' => [
                                'top' => 0,
                                'right' => 0,
                                'bottom' => 0,
                                'left' => 0,
                                'style' => 'none',
                                'color' => '',
                            ],
                        ]),
                    ],
                ],
            ],
            'labels' => [
                'title' => esc_html__('Labels', 'listdom'),
                'sub_title' => esc_html__("Define the label's appearance.", 'listdom'),
                'divisions' => [
                    '_' => [
                        'fields' => $this->fields([
                            'typography' => [
                                'size' => 12,
                                'line_height' => 26,
                                'align' => 'left'
                            ]
                        ], ['text', 'typography']),
                    ]
                ],
            ],
        ];
    }

    public function fields($defaults = [], array $include = []): array
    {
        $fields = [
            'input_bg_color' => $this->fields->color(
                esc_html__('Background Color', 'listdom'),
                $defaults['input_bg_color'] ?? '#ffffff'
            ),
            'text' => $this->fields->color(
                esc_html__('Text Color', 'listdom'),
                $defaults['text'] ?? '#000000'
            ),
            'placeholder' => $this->fields->color(
                esc_html__('Placeholder Color', 'listdom'),
                $defaults['placeholder'] ?? '#a4a8b5'
            ),
            'border' => [
                'type' => 'border',
                'title' => esc_html__('Border', 'listdom'),
                'default' => array_merge([
                    'top' => 1,
                    'right' => 1,
                    'bottom' => 1,
                    'left' => 1,
                    'style' => 'solid',
                    'color' => '#f4f5f7',
                    'radius' => 10,
                ], $defaults['border'] ?? []),
            ],
            'typography' => [
                'type' => 'typography',
                'title' => esc_html__('Typography', 'listdom'),
                'default' => array_merge([
                    'family' => 'inherit',
                    'weight' => 'inherit',
                    'align' => 'inherit',
                    'size' => 'inherit',
                    'line_height' => 'inherit',
                ], $defaults['typography'] ?? []),
                'size_units' => ['px', 'em', 'rem', '%'],
                'line_height_units' => ['px', 'em', 'rem', '%'],
            ],
            'form_bg_color' => $this->fields->color(
                esc_html__('Background Color', 'listdom'),
                $defaults['form_bg_color'] ?? '#ffffff'
            ),
            'icon_color' => $this->fields->color(
                esc_html__('Icon Color', 'listdom'),
                $defaults['icon_color'] ?? '#33c6ff'
            )
        ];

        return empty($include) ? $fields : array_intersect_key($fields, array_flip($include));
    }

    public function tab_fields(array $defaults = []): array
    {
        return [
            'bg1' => [
                'type' => 'color',
                'title' => esc_html__('Background Color 1', 'listdom'),
                'default' => $defaults['bg1'] ?? '#306be6',
            ],
            'bg2' => [
                'type' => 'color',
                'title' => esc_html__('Background Color 2', 'listdom'),
                'default' => $defaults['bg2'] ?? '#306be6',
            ],
            'text' => [
                'type' => 'color',
                'title' => esc_html__('Text Color', 'listdom'),
                'default' => $defaults['text'] ?? '',
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
                    'color' => $defaults['border']['color'] ?? '#306be6',
                    'radius' => $defaults['border']['radius'] ?? 4,
                ],
            ],
            'padding' => [
                'type' => 'padding',
                'title' => esc_html__('Padding', 'listdom'),
                'default' => [
                    'top' => $defaults['padding']['top'] ?? 0,
                    'right' => $defaults['padding']['right'] ?? 16,
                    'bottom' => $defaults['padding']['bottom'] ?? 0,
                    'left' => $defaults['padding']['left'] ?? 16,
                ],
            ],
            'typography' => [
                'type' => 'typography',
                'title' => esc_html__('Typography', 'listdom'),
                'default' => [
                    'family' => $defaults['typography']['family'] ?? 'inherit',
                    'weight' => $defaults['typography']['weight'] ?? 400,
                    'align' => $defaults['typography']['align'] ?? 'center',
                    'size' => $defaults['typography']['size'] ?? 14,
                    'line_height' => $defaults['typography']['line_height'] ?? 38,
                ],
                'size_units' => ['px', 'em', 'rem', '%'],
                'line_height_units' => ['px', 'em', 'rem', '%'],
            ],
        ];
    }
}
