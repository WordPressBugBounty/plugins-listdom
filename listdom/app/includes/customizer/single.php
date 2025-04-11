<?php

class LSD_Customizer_Single extends LSD_Customizer
{
    public function options($fields = []): array
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
                ],
            ],
        ];
    }

    public static function fields($defaults = [], $selected_fields_key = []): array
    {
        return apply_filters('lsd_customizer_icons_fields', $defaults);
    }
}
