<?php

class LSD_Labels extends LSD_Base
{
    protected static $instance = null;

    public static function instance(): LSD_Labels
    {
        if (is_null(self::$instance)) self::$instance = new self();

        return self::$instance;
    }

    protected function __construct()
    {
    }

    public static function default_taxonomy_labels(): array
    {
        return [
            LSD_Base::TAX_CATEGORY => [
                'singular' => 'Category',
                'plural' => 'Categories',
            ],
            LSD_Base::TAX_LOCATION => [
                'singular' => 'Location',
                'plural' => 'Locations',
            ],
            LSD_Base::TAX_TAG => [
                'singular' => 'Tag',
                'plural' => 'Tags',
            ],
            LSD_Base::TAX_FEATURE => [
                'singular' => 'Feature',
                'plural' => 'Features',
            ],
            LSD_Base::TAX_LABEL => [
                'singular' => 'Label',
                'plural' => 'Labels',
            ],
            LSD_Base::TAX_ATTRIBUTE => [
                'singular' => 'Custom Field',
                'plural' => 'Custom Fields',
            ],
        ];
    }

    public static function taxonomy_titles(): array
    {
        return [
            LSD_Base::TAX_CATEGORY => esc_html__('Category', 'listdom'),
            LSD_Base::TAX_LOCATION => esc_html__('Location', 'listdom'),
            LSD_Base::TAX_TAG => esc_html__('Tag', 'listdom'),
            LSD_Base::TAX_FEATURE => esc_html__('Feature', 'listdom'),
            LSD_Base::TAX_LABEL => esc_html__('Label', 'listdom'),
            LSD_Base::TAX_ATTRIBUTE => esc_html__('Custom Field', 'listdom'),
        ];
    }

    public static function current_locale(): string
    {
        $locale = '';

        if (function_exists('pll_current_language'))
        {
            $pll_locale = pll_current_language('locale');
            if (is_string($pll_locale) && trim($pll_locale) !== '') $locale = $pll_locale;
        }

        if (!$locale)
        {
            $lang = apply_filters('wpml_current_language', null);
            if (is_string($lang) && trim($lang) !== '')
            {
                $wpml_locale = apply_filters('wpml_locale', '', $lang);
                if (is_string($wpml_locale) && trim($wpml_locale) !== '') $locale = $wpml_locale;
                else $locale = $lang;
            }
        }

        if (!$locale) $locale = determine_locale();

        $locale = is_string($locale) ? trim($locale) : '';
        if ($locale === '') $locale = determine_locale();

        return (string) apply_filters('lsd_labels_locale', $locale);
    }

    public static function available_locales(): array
    {
        $locales = [];

        $locales[] = self::current_locale();

        $settings = LSD_Options::settings();
        $saved = $settings['labels']['locales'] ?? [];
        if (is_array($saved)) $locales = array_merge($locales, array_keys($saved));

        if (function_exists('pll_languages_list'))
        {
            $pll_locales = pll_languages_list(['fields' => 'locale']);
            if (is_array($pll_locales)) $locales = array_merge($locales, $pll_locales);
        }

        $wpml_languages = apply_filters('wpml_active_languages', null, ['skip_missing' => 0]);
        if (is_array($wpml_languages))
        {
            foreach ($wpml_languages as $language)
            {
                if (isset($language['default_locale']) && is_string($language['default_locale']) && trim($language['default_locale']) !== '')
                {
                    $locales[] = $language['default_locale'];
                    continue;
                }

                if (isset($language['language_code']) && is_string($language['language_code']) && trim($language['language_code']) !== '')
                {
                    $locale = apply_filters('wpml_locale', '', $language['language_code']);
                    $locales[] = trim($locale) !== '' ? $locale : $language['language_code'];
                }
            }
        }

        $locales = array_filter(array_map('sanitize_text_field', $locales));
        $locales = array_values(array_unique($locales));

        return $locales;
    }

    public static function get(string $taxonomy, string $form = 'singular', string $locale = ''): string
    {
        $form = trim($form);
        if ($form === '') $form = 'singular';

        if ($locale === '') $locale = self::current_locale();

        $custom = self::get_custom($taxonomy, $form, $locale);
        if ($custom !== '') return $custom;

        return self::default_label($taxonomy, $form);
    }

    public static function get_custom(string $taxonomy, string $form = 'singular', string $locale = ''): string
    {
        $settings = LSD_Options::settings();
        $labels = $settings['labels']['locales'] ?? [];
        if (!is_array($labels)) return '';

        $locale = $locale !== '' ? $locale : self::current_locale();

        $value = $labels[$locale][$taxonomy][$form] ?? '';
        if (!is_string($value)) return '';

        return trim($value);
    }

    public static function default_label(string $taxonomy, string $form = 'singular'): string
    {
        $defaults = self::default_taxonomy_labels();
        $label = $defaults[$taxonomy][$form] ?? '';
        if (!is_string($label) || trim($label) === '') return '';

        return translate($label, 'listdom');
    }

    public static function lowercase(string $text): string
    {
        if (function_exists('mb_strtolower')) return mb_strtolower($text);

        return strtolower($text);
    }
}
