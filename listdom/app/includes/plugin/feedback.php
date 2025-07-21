<?php

use Webilia\WP\Plugin\Feedback;

class LSD_Plugin_Feedback extends Feedback
{
    public function reasons(): array
    {
        return [
            'no-longer-needed' => [
                'title' => esc_html__('I no longer need the plugin', 'listdom'),
                'placeholder' => '',
            ],
            'found-a-better-plugin' => [
                'title' => esc_html__('I found a better plugin', 'listdom'),
                'placeholder' => esc_html__('Please share which plugin', 'listdom'),
            ],
            'cannot-get-the-plugin-to-work' => [
                'title' => esc_html__("I couldn't get the plugin to work", 'listdom'),
                'placeholder' => '',
            ],
            'temporary-deactivation' => [
                'title' => esc_html__("It's a temporary deactivation", 'listdom'),
                'placeholder' => '',
            ],
            'other' => [
                'title' => esc_html__('Other', 'listdom'),
                'placeholder' => esc_html__('Please share the reason', 'listdom'),
            ],
        ];
    }
}
