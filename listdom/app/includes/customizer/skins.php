<?php

class LSD_Customizer_Skins extends LSD_Customizer
{
    public function options(): array
    {
        return [
            'skins' => [
                'title' => esc_html__('Skins', 'listdom'),
                'display_sections_force' => 1,
                'sections' => [
                    'masonry' => [
                        'title' => esc_html__('Masonry', 'listdom'),
                        'groups' => [
                            'tabs' => [
                                'title' => esc_html__('Tabs', 'listdom'),
                                'divisions' => [
                                    'normal' => [
                                        'title' => esc_html__('Normal', 'listdom'),
                                        'sub_title' => esc_html__("Define the tab's appearance in normal state.", 'listdom'),
                                        'fields' => LSD_Customizer_Tabs::fields(),
                                    ],
                                    'hover' => [
                                        'title' => esc_html__('Tabs Hover / Active', 'listdom'),
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
                    'slider' => [
                        'title' => esc_html__('Slider', 'listdom'),
                        'groups' => [
                            'arrows' => [
                                'title' => esc_html__('Arrows', 'listdom'),
                                'divisions' => [
                                    '_' => [
                                        'fields' => [
                                            'icon_color' => [
                                                'type' => 'color',
                                                'title' => esc_html__('Icon Color', 'listdom'),
                                                'default' => '#33c6ff',
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
        return apply_filters('lsd_customizer_skins_fields', $defaults);
    }
}
