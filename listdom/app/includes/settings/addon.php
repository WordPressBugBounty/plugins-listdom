<?php

/** Shared descriptor helpers; each owning package declares its own fields. */
class LSD_Settings_Addon
{
    public static function field(string $path, array $schema, $default, string $group = 'general', array $metadata = []): array
    {
        return $metadata + ['path' => explode('.', $path), 'schema' => $schema, 'default' => $default,
            'label' => ucwords(str_replace(['_', '.'], ' ', $path)),
            'description' => __('Reviewed add-on configuration; write policies and dependencies are checked before saving.', 'listdom'),
            'group' => $group, 'tab' => 'addons', 'subtab' => '', 'available' => true,
            'dependencies' => [], 'capability' => 'manage_options', 'sensitivity' => 'configuration'];
    }

    public static function toggle(string $path, int $default = 0, string $group = 'general', array $metadata = []): array
    {
        return self::field($path, ['type' => 'integer', 'enum' => [0, 1]], $default, $group, $metadata);
    }

    public static function choice(string $path, array $values, $default, string $group = 'general', array $metadata = []): array
    {
        return self::field($path, ['type' => is_int($values[0]) ? 'integer' : 'string', 'enum' => $values], $default, $group, $metadata);
    }

    public static function integer(string $path, int $default = 0, int $minimum = 0, int $maximum = 2147483647, string $group = 'general'): array
    {
        return self::field($path, ['type' => 'integer', 'minimum' => $minimum, 'maximum' => $maximum], $default, $group);
    }

    public static function reference(string $path, $default = '', string $group = 'general'): array
    {
        return self::field($path, ['type' => ['integer', 'string'], 'anyOf' => [
            ['type' => 'integer', 'minimum' => 0, 'maximum' => 2147483647], ['type' => 'string', 'enum' => ['']]]], $default, $group);
    }

    public static function text(string $path, string $default = '', string $group = 'general'): array
    {
        return self::field($path, ['type' => 'string', 'maxLength' => 512, 'pattern' => '^[^<>]*$'], $default, $group);
    }

    public static function ids(string $path, array $default = [], string $group = 'general', array $metadata = []): array
    {
        return self::field($path, ['type' => 'array', 'maxItems' => 50,
            'items' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 2147483647]], $default, $group, $metadata);
    }

    public static function status(string $path, string $group = 'integration'): array
    {
        return self::field($path, ['type' => 'boolean'], '', $group, ['projection' => 'configured',
            'sensitivity' => 'status_only', 'description' => __('Configuration presence only; the underlying value is never returned.', 'listdom')]);
    }

    /** Fixed package arguments only; callers never select physical options. */
    public static function domain(string $slug, string $owner, string $storage, array $fields, ?callable $reader = null, bool $available = true): array
    {
        $definitions = [];
        foreach ($fields as $name => $field)
        {
            $field['subtab'] = $field['subtab'] ?: $storage;
            $definitions['addons.' . $slug . '.' . $name] = $field;
        }
        return ['owner' => $owner, 'addon' => $slug, 'available' => $available, 'fields' => $definitions,
            'read' => static function () use ($storage, $definitions, $reader): array
            {
                $snapshot = self::snapshot($storage, $definitions);
                return $reader ? $reader($snapshot) : $snapshot;
            }];
    }

    /** Explicit package allowlist; a new form or descriptor never grants write access. */
    public static function writes(array $domain, array $keys, LSD_Settings_Writer $writer): array
    {
        foreach ($keys as $key)
        {
            $id = 'addons.' . $domain['addon'] . '.' . $key;
            if (isset($domain['fields'][$id]) && $domain['fields'][$id]['sensitivity'] === 'configuration') $domain['fields'][$id]['write'] = 'leaf';
        }
        $domain['writer'] = $writer;
        return $domain;
    }

    /** Shared persistence mechanics; the owning package supplies policy callbacks. */
    public static function writer(string $storage, array $callbacks = []): LSD_Settings_Writer
    {
        return new LSD_Settings_Writer('lsd_addons', [$storage], $callbacks + [
            'sanitize' => static function (array $candidate) use ($storage): array
            {
                $sanitized = LSD_Menus_Settings::sanitize_addons([$storage => $candidate]);
                return $sanitized[$storage] ?? [];
            },
            'validate' => [self::class, 'validate_available'],
            'context' => [self::class, 'context'],
        ]);
    }

    public static function validate_available(array $candidate, array $keys, array $domain)
    {
        foreach ($keys as $key)
            if (!$domain['fields'][$key]['available'] && !self::cleared($candidate, $domain['fields'][$key]))
                return LSD_Settings_Writer::error('listdom_setting_dependency', __('Enable the setting’s dependency in its owning domain first.', 'listdom'), ['keys' => [$key]]);
        return true;
    }

    /** Only explicit empty values bypass dependency gates, after schema and owner validation. */
    public static function cleared(array $candidate, array $field): bool
    {
        $value = (new LSD_Settings_Catalog())->at($candidate, $field['path'], $present);
        return $present && in_array($value, [0, 0.0, false, '', []], true);
    }

    /** Rules are executable package policy, never parsed from translated descriptions. */
    public static function validate_rules(array $candidate, array $keys, array $domain, array $rules, string $message)
    {
        foreach ($keys as $key)
        {
            $name = substr($key, strlen('addons.' . $domain['addon'] . '.'));
            $allowed = array_key_exists($name, $rules) ? $rules[$name] : $domain['fields'][$key]['available'];
            if (!$allowed && !self::cleared($candidate, $domain['fields'][$key]))
                return LSD_Settings_Writer::error('listdom_setting_dependency', $message, ['keys' => [$key]]);
        }
        return true;
    }

    /** Fresh external prerequisites also participate in the domain revision. */
    public static function context(): array
    {
        foreach (['lsd_settings', 'lsd_payments', 'lsd_ai'] as $option) wp_cache_delete($option, 'options');
        wp_cache_delete('alloptions', 'options');
        LSD_Options::clear_settings_cache();
        LSD_Payments_Engine::clear_cache();
        return [LSD_Components::get(), LSD_Base::isPro(),
            function_exists('lsd_payment') && lsd_payment()->requirements_meet(), LSD_Payments_Engine::instance()->wc()];
    }

    /** Unfiltered persisted state; only the fixed options owned by these adapters. */
    public static function stored_option(string $option)
    {
        if (!in_array($option, ['lsd_addons', 'lsd_settings', 'lsd_details_page', 'lsdaddwl_settings'], true))
            throw new InvalidArgumentException('Unsupported settings option.');
        global $wpdb;
        $serialized = $wpdb->get_var($wpdb->prepare("SELECT option_value FROM {$wpdb->options} WHERE option_name = %s", $option));
        return $serialized === null ? [] : maybe_unserialize($serialized);
    }

    /** Missing leaves are defaults; scalar containers are corrupt configuration. */
    public static function malformed_path($values, array $path): bool
    {
        foreach ($path as $part)
        {
            if (!is_array($values)) return true;
            if (!array_key_exists($part, $values)) return false;
            $values = $values[$part];
        }
        return false;
    }

    public static function snapshot(string $storage, array $fields): array
    {
        // Bypass option caches that may predate a save or switch_to_blog().
        wp_cache_delete('alloptions', 'options');
        wp_cache_delete('notoptions', 'options');
        wp_cache_delete('lsd_addons', 'options');
        $raw = self::stored_option('lsd_addons');
        $stored = is_array($raw) && is_array($raw[$storage] ?? null) ? $raw[$storage] : [];
        $all_defaults = LSD_Options::defaults('addons');
        $defaults = is_array($all_defaults[$storage] ?? null) ? $all_defaults[$storage] : [];
        foreach ($fields as $field)
        {
            $cursor = &$defaults;
            foreach ($field['path'] as $index => $part)
            {
                if ($index === count($field['path']) - 1)
                {
                    if (!array_key_exists($part, $cursor)) $cursor[$part] = $field['default'];
                }
                else
                {
                    if (!isset($cursor[$part]) || !is_array($cursor[$part])) $cursor[$part] = [];
                    $cursor = &$cursor[$part];
                }
            }
            unset($cursor);
        }
        // Preserve canonical option filters without invoking a save-oriented reader.
        $effective = LSD_Options::addons($storage);
        $invalid = [];
        foreach ($fields as $key => $field)
        {
            if (self::malformed_path($raw, array_merge([$storage], $field['path']))
                || self::malformed_path($effective, $field['path'])) $invalid[$key] = true;
        }
        return ['stored' => $stored, 'defaults' => $defaults, 'invalidFields' => $invalid,
            'effective' => LSD_Base::parse_args(is_array($effective) ? $effective : [], $defaults)];
    }
}
