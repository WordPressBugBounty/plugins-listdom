<?php

/** Native WordPress abilities stay separate from Listdom's domain services. */
class LSD_Abilities
{
    /** Attach registration to the native Abilities API hooks. */
    public function init(): void
    {
        if (function_exists('wp_register_ability')) LSD_Repairs_Media::init();
        add_action('wp_abilities_api_categories_init', [$this, 'categories']);
        add_action('wp_abilities_api_init', [$this, 'register']);
    }

    /** Register Listdom's ability category when the API is available. */
    public function categories(): void
    {
        if (!function_exists('wp_register_ability_category')) return;
        wp_register_ability_category('listdom', [
            'label' => __('Listdom', 'listdom'),
            'description' => __('Directory search, listing management, taxonomies, interactions, settings, and supported add-on operations.', 'listdom'),
        ]);
    }

    /** Register core providers, then let active add-ons register theirs. */
    public function register(): void
    {
        if (!function_exists('wp_register_ability') || !function_exists('wp_register_ability_category')) return;
        (new LSD_Abilities_Settings())->register();
        (new LSD_Abilities_Search())->register();
        (new LSD_Abilities_Listings())->register();
        (new LSD_Abilities_Taxonomies())->register();
        (new LSD_Abilities_Management())->register();
        (new LSD_Abilities_Contact())->register();
        (new LSD_Abilities_Media())->register();
        (new LSD_Abilities_Diagnostics())->register();
        (new LSD_Abilities_Repairs())->register();

        // Add-ons register their own providers only while active.
        do_action('lsd_abilities_register');
    }
}
