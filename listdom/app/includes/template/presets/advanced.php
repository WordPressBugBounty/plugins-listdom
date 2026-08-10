<?php

class LSD_Template_Presets_Advanced
{
    /**
     * Responsive visibility controls (hide per device).
     *
     * @param string $element_key
     * @return array
     */
    public static function responsive_visibility(string $element_key): array
    {
        return [
            'advanced_visibility_' . $element_key => [
                'label'  => esc_html__('Responsive', 'listdom'),
                'fields' => [
                    [
                        'id'          => 'responsive_visibility_heading',
                        'type'        => 'subsection_title',
                        'label'       => esc_html__('Visibility', 'listdom'),
                        'description' => esc_html__('Control which devices should hide this element.', 'listdom'),
                    ],
                    [
                        'id'          => 'hide_on_desktop',
                        'type'        => 'switcher',
                        'class'       => 'lsd-admin-input',
                        'label'       => esc_html__('Hide on Desktop', 'listdom'),
                        'default'     => 0,
                        'options'     => [
                            0 => esc_html__('No', 'listdom'),
                            1 => esc_html__('Yes', 'listdom'),
                        ],
                        'description' => '',
                    ],
                    [
                        'id'          => 'hide_on_tablet',
                        'type'        => 'switcher',
                        'class'       => 'lsd-admin-input',
                        'label'       => esc_html__('Hide on Tablet', 'listdom'),
                        'default'     => 0,
                        'options'     => [
                            0 => esc_html__('No', 'listdom'),
                            1 => esc_html__('Yes', 'listdom'),
                        ],
                        'description' => '',
                    ],
                    [
                        'id'          => 'hide_on_mobile',
                        'type'        => 'switcher',
                        'class'       => 'lsd-admin-input',
                        'label'       => esc_html__('Hide on Mobile', 'listdom'),
                        'default'     => 0,
                        'options'     => [
                            0 => esc_html__('No', 'listdom'),
                            1 => esc_html__('Yes', 'listdom'),
                        ],
                        'description' => '',
                    ],
                ],
            ],
        ];
    }
}
