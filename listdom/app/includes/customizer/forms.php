<?php

class LSD_Customizer_Forms extends LSD_Customizer
{
    public static function options($fields = []): array
    {
        return [
            'forms' => [
                'title' => esc_html__('Forms', 'listdom'),
                'sections' => [
                    'general_forms' => [
                        'title' => esc_html__('General', 'listdom'),
                        'groups' => self::get_form_groups(),
                    ],
                    'search_forms' => [
                        'title' => esc_html__('Search', 'listdom'),
                        'inherit' => [
                            'key' => 'forms.general_forms',
                            'text' => esc_html__('Inherit from General Forms.', 'listdom'),
                            'enabled' => 0,
                        ],
                        'groups' => self::get_form_groups(true),
                    ],
                ],
            ],
        ];
    }

    private static function get_form_groups($is_search_form = false): array
    {
        return [
            'normal' => [
                'title' => esc_html__('Normal', 'listdom'),
                'sub_title' => esc_html__("Define the form's appearance in normal state.", 'listdom'),
                'divisions' => [
                    '_' => [
                        'fields' => self::fields([
                            'typography' => [
                                'size' => 12,
                                'line_height' => 32,
                            ]
                        ], [], $is_search_form),
                    ],
                ],
            ],
            'hover' => [
                'title' => esc_html__('Hover / Focus', 'listdom'),
                'sub_title' => esc_html__("Define the form's appearance in hover state.", 'listdom'),
                'divisions' => [
                    '_' => [
                        'fields' => self::fields(['border' => ['color' => '#306be6']], ['input_bg_color', 'border'], $is_search_form),
                    ],
                ],
            ],
        ];
    }

    public static function fields($defaults = [], $selected_fields_key = [], $is_search_form = false): array
    {
        $fields = [
            'input_bg_color' => self::color_field('Input Background Color', 'input_bg_color', $defaults, '#fff'),
            'text' => self::color_field('Text Color', 'text', $defaults, '#000'),
            'placeholder' => self::color_field('Placeholder Color', 'placeholder', $defaults, '#a4a8b5'),
            'border' => [
                'type' => 'border',
                'title' => esc_html__('Border', 'listdom'),
                'default' => array_merge([
                    'top' => 1, 'right' => 1, 'bottom' => 1, 'left' => 1,
                    'style' => 'solid', 'color' => '#f4f5f7', 'radius' => 10,
                ], $defaults['border'] ?? []),
            ],
            'typography' => [
                'type' => 'typography',
                'title' => esc_html__('Typography', 'listdom'),
                'default' => array_merge([
                    'family' => 'inherit', 'weight' => 'inherit', 'align' => 'inherit',
                    'size' => 'inherit', 'line_height' => 'inherit',
                ], $defaults['typography'] ?? []),
            ],
        ];

        if ($is_search_form)
        {
            $fields['form_bg_color'] = self::color_field('Form Background Color', 'form_bg_color', $defaults, '#fff');
            $fields['icon_color'] = self::color_field('Icon Color', 'icon_color', $defaults, '#33c6ff');
        }

        return empty($selected_fields_key) ? $fields : array_intersect_key($fields, array_flip($selected_fields_key));
    }

    private static function color_field($title, $key, $defaults, $default_color): array
    {
        return [
            'type' => 'color',
            'title' => esc_html__($title, 'listdom'),
            'default' => $defaults[$key] ?? $default_color,
        ];
    }
}
