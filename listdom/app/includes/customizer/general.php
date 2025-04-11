<?php

class LSD_Customizer_General extends LSD_Customizer
{
    public function options($fields = []): array
    {
        return [
            'general' => [
                'title' => esc_html__('General', 'listdom'),
                'sections' => [
                    '_' => [
                        'title' => esc_html__('Colors', 'listdom'),
                        'groups' => [
                            'colors' => [
                                'title' => esc_html__('Colors', 'listdom'),
                                'divisions' => [
                                    '_' => [
                                        'fields' => [
                                            'primary_color' => $this->fields->color(
                                                esc_html__('General Color', 'listdom'),
                                                '#33c6ff'
                                            ),
                                        ],
                                    ],
                                ],
                            ],
                            'fonts' => [
                                'title' => esc_html__('Fonts', 'listdom'),
                                'divisions' => [
                                    '_' => [
                                        'fields' => [
                                            'primary_font' => [
                                                'type' => 'typography',
                                                'title' => esc_html__('Primary Font', 'listdom'),
                                                'default' => [
                                                    'family' => 'poppins',
                                                ],
                                                'include' => ['family']
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                            'gaps' => [
                                'title' => esc_html__('Gaps', 'listdom'),
                                'divisions' => [
                                    '_' => [
                                        'fields' => [
                                            'gap' => [
                                                'type' => 'select',
                                                'title' => esc_html__('Base Gap (Pixels)', 'listdom'),
                                                'default' => 8,
                                                'options' => [
                                                    4 => 4,
                                                    6 => 6,
                                                    8 => 8,
                                                    10 => 10,
                                                    12 => 12,
                                                    14 => 14,
                                                    16 => 16,
                                                    18 => 18,
                                                    20 => 20,
                                                ],
                                                'description' => esc_html__('Enter a base gap value, and the system will automatically generate a range of spacing values to maintain consistent layout scaling. Smaller gaps are derived from the base, while larger gaps expand proportionally for flexible design. Default value is 8.', 'listdom')
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

    public static function fields($defaults = [], $selected_fields_key = []): array
    {
        return apply_filters('lsd_customizer_general_fields', $defaults);
    }
}
