<?php

use Webilia\WP\Plugin\Feedback;

class LSD_Plugin_Feedback extends Feedback
{
    public function reasons(): array
    {
        return [
            'missing-a-feature' => [
                'title' => esc_html__('Missing a feature', 'listdom'),
                'placeholder' => esc_html__('Anything that can help', 'listdom'),
                'icon' => 'lsdi-puzzle',
            ],
            'had-conflicts' => [
                'title' => esc_html__("Didn't work / Had conflicts", 'listdom'),
                'placeholder' => esc_html__('Anything that can help', 'listdom'),
                'icon' => 'lsdi-alert',
            ],
            'hard-to-use' => [
                'title' => esc_html__('Hard to use', 'listdom'),
                'placeholder' => esc_html__('Anything that can help', 'listdom'),
                'icon' => 'lsdi-sad-dizzy',
            ],
            'found-a-better-plugin' => [
                'title' => esc_html__('Found a better plugin', 'listdom'),
                'placeholder' => esc_html__('Please share which plugin', 'listdom'),
                'icon' => 'lsdi-search-magnifier',
            ],
            'no-longer-needed' => [
                'title' => esc_html__("Don't need it anymore", 'listdom'),
                'placeholder' => esc_html__('Anything that can help', 'listdom'),
                'icon' => 'lsdi-waving-hand',
            ],
            'temporary-deactivation' => [
                'title' => esc_html__("Temporary deactivation", 'listdom'),
                'placeholder' => esc_html__('Anything that can help', 'listdom'),
                'icon' => 'lsdi-time-half-pass',
            ],
            'other' => [
                'title' => esc_html__('Other', 'listdom'),
                'placeholder' => esc_html__('Please share the reason', 'listdom'),
                'icon' => 'lsdi-three-dots',
            ],
        ];
    }
}
