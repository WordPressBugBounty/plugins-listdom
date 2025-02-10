<?php
// no direct access
defined('ABSPATH') || die();

class LSD_i18n
{
    public function init()
    {
        // Register Language Files
        add_action('init', [$this, 'load_languages']);
    }

    public function load_languages()
    {
        // Listdom File
        $file = new LSD_File();

        // Get current locale
        $locale = apply_filters('plugin_locale', get_locale(), 'listdom');

        // WordPress' language directory /wp-content/languages/listdom-en_US.mo
        $path = WP_LANG_DIR . '/listdom-' . $locale . '.mo';

        // If language file exists on WordPress' language directory use it
        if ($file->exists($path))
        {
            load_textdomain('listdom', $path);
        }
        // Otherwise use Listdom plugin directory /path/to/plugin/i18n/languages/listdom-en_US.mo
        else
        {
            load_plugin_textdomain('listdom', false, dirname(LSD_BASENAME) . '/i18n/languages/');
        }
    }

    public static function set($locale)
    {
        // WPML
        if (class_exists('SitePress'))
        {
            global $sitepress;

            do_action('wpml_switch_language', $locale);
            $sitepress->switch_lang($locale);
        }
    }

    public static function languages()
    {
        // WPML
        if (class_exists('SitePress'))
        {
            global $sitepress;
            $langs = [];

            $languages = $sitepress->get_active_languages();
            foreach ($languages as $language) $langs[] = $language['code'];

            return $langs;
        }
        // Polylang
        else if (function_exists('pll_languages_list')) return pll_languages_list();
        else return [];
    }
}
