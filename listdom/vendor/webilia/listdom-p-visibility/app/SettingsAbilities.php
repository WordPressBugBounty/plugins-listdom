<?php
namespace LSDPACVIS;

/** Package-owned declarations; independent of YBee and native API availability. */
class SettingsAbilities
{
    public static function register(array $domains): array
    {
        if (!class_exists(\LSD_Settings_Addon::class)) return $domains;
        $s = \LSD_Settings_Addon::class;
        $fields = [
            'max_visits' => array_merge($s::reference('max_visits', '', 'restrictions'), ['tab' => 'general', 'subtab' => 'general',
                'available' => \LSD_Components::visibility(), 'dependencies' => [__('Core visibility component enabled', 'listdom-visibility')]]),
        ];
        $domains['addons.visibility'] = $s::domain('visibility', __('Listdom Visibility', 'listdom-visibility'), 'visibility', $fields, null, true);
        $domains['addons.visibility']['read'] = static function (): array
        {
            // Enforcement uses the core setting, not the obsolete add-on form leaf.
            $raw = \LSD_Settings_Addon::stored_option('lsd_settings');
            \LSD_Options::clear_settings_cache();
            $settings = \LSD_Options::settings();
            $value = $settings['visibility_max_visits'] ?? '';
            $value = is_numeric($value) && (int) $value >= 0 ? (int) $value : '';
            $stored = [];
            if (is_array($raw) && array_key_exists('visibility_max_visits', $raw)) $stored['max_visits'] = $raw['visibility_max_visits'];
            return ['stored' => $stored, 'defaults' => ['max_visits' => ''], 'effective' => ['max_visits' => $value],
                'invalidFields' => ['addons.visibility.max_visits' => \LSD_Settings_Addon::malformed_path($raw, ['visibility_max_visits'])]];
        };
        $domains['addons.visibility']['fields']['addons.visibility.max_visits']['storagePath'] = ['visibility_max_visits'];
        // Enable only the package’s reviewed setting leaves.
        if (class_exists(\LSD_Settings_Writer::class))
            $domains['addons.visibility'] = $s::writes($domains['addons.visibility'], ['max_visits'], self::writer());
        return $domains;
    }

    private static function writer(): \LSD_Settings_Writer
    {
        return new \LSD_Settings_Writer('lsd_settings', [], [
            'context' => [\LSD_Settings_Addon::class, 'context'],
            'validate' => static function (array $candidate, array $keys, array $domain)
            {
                return \LSD_Settings_Addon::validate_rules($candidate, $keys, $domain,
                    ['max_visits' => \LSD_Components::visibility()], __('Enable the core visibility component first.', 'listdom-visibility'));
            },
        ]);
    }
}
