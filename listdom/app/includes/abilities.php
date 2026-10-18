<?php

/** Minimal shared native bootstrap; providers keep their own domain logic. */
class LSD_Abilities
{
    public function init(): void
    {
        add_action('wp_abilities_api_categories_init', [$this, 'categories']);
        add_action('wp_abilities_api_init', [$this, 'register']);
    }

    public function categories(): void
    {
        if (!function_exists('wp_register_ability_category')) return;
        wp_register_ability_category('listdom', [
            'label' => __('Listdom', 'listdom'),
            'description' => __('Directory configuration and supported Listdom operations.', 'listdom'),
        ]);
    }

    public function register(): void
    {
        if (!function_exists('wp_register_ability')) return;
        (new LSD_Abilities_Settings())->register();

        do_action('lsd_abilities_register');
    }
}
