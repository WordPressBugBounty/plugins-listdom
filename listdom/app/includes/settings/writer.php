<?php

/** A fixed, owner-declared adapter; never constructed from ability inputs. */
class LSD_Settings_Writer
{
    private string $option;
    private array $path;
    private $validate;
    private $sanitize;
    private $after;
    private $permission;
    private $context;

    public function __construct(string $option, array $path = [], array $callbacks = [])
    {
        $this->option = $option;
        $this->path = $path;
        $this->validate = $callbacks['validate'] ?? null;
        $this->sanitize = $callbacks['sanitize'] ?? null;
        $this->after = $callbacks['after'] ?? null;
        $this->permission = $callbacks['permission'] ?? null;
        $this->context = $callbacks['context'] ?? null;
    }

    public function permitted(): bool
    {
        return LSD_Settings_Service::permitted() && (!$this->permission || (bool) call_user_func($this->permission));
    }

    public function snapshot(): array
    {
        return LSD_Options::option_snapshot($this->option);
    }

    public function state(array $physical, array $domain): array
    {
        $catalog = new LSD_Settings_Catalog();
        $stored = $catalog->at($physical['stored'], $this->path, $present);
        // Aliases own only their mapped leaves, not the entire physical option.
        $mapped = [];
        foreach ($domain['fields'] as $key => $field)
            if (isset($field['storagePath'])) $mapped[$key] = $catalog->at($physical['stored'], $field['storagePath']);
        return ['present' => $present, 'stored' => $mapped ?: $stored,
            'context' => $this->context ? call_user_func($this->context) : []];
    }

    /** Scope data and effective reads must describe the same persisted state. */
    public function capture(array $domain)
    {
        $before = $this->snapshot();
        $before_state = $this->state($before, $domain);
        $values = call_user_func($domain['read']);
        if (is_wp_error($values)) return $values;
        $after = $this->snapshot();
        if ($before['blogId'] !== $after['blogId'] || serialize($before_state) !== serialize($this->state($after, $domain)))
            return self::error('listdom_settings_conflict', __('Settings changed during the read. Read them again.', 'listdom'));
        if (!is_array($values) || !is_array($values['stored'] ?? null) || !is_array($values['effective'] ?? null) || !is_array($values['defaults'] ?? null))
            return self::error('listdom_settings_read_failed', __('The settings reader did not provide a valid snapshot.', 'listdom'));
        return ['physical' => $after, 'values' => $values];
    }

    /** HMAC binds protected state without disclosing it or permitting guessing. */
    public static function revision(string $id, array $domain, array $values, ?array $physical = null): string
    {
        $catalog = new LSD_Settings_Catalog();
        $definitions = [];
        $effective = [];
        foreach ($domain['fields'] as $key => $field)
        {
            $definitions[$key] = array_intersect_key($field, array_flip(['path', 'schema', 'write', 'storagePath', 'projection', 'available', 'readable', 'capability', 'sensitivity', 'dependencies', 'resource', 'writeAvailable']));
            $effective[$key] = $catalog->scoped_describe($field, $values, false)['effectiveValue'];
        }
        $writer = $domain['writer'] ?? null;
        $state = $writer instanceof self && $physical !== null ? $writer->state($physical, $domain) : $values['stored'];
        return hash_hmac('sha256', serialize(['settings-domain-write-5', get_current_blog_id(), $id,
            $state, $values['stored'], $effective, $definitions, !empty($domain['available'])]), wp_salt('auth'));
    }

    public static function put(array &$values, array $path, $value): void
    {
        $cursor = &$values;
        foreach ($path as $index => $part)
        {
            if ($index === count($path) - 1) $cursor[$part] = $value;
            else
            {
                if (!isset($cursor[$part])) $cursor[$part] = [];
                $cursor = &$cursor[$part];
            }
        }
    }

    private function field_path(array $field): array
    {
        return $field['storagePath'] ?? array_merge($this->path, $field['path']);
    }

    private static function arrays($value)
    {
        if (is_object($value)) $value = (array) $value;
        if (is_array($value)) foreach ($value as &$item) $item = self::arrays($item);
        unset($item);
        return $value;
    }

    private function encode(array $field, $value)
    {
        if (($field['projection'] ?? '') === 'integer_keys') return array_fill_keys($value, 1);
        if (($field['projection'] ?? '') === 'first_reference') return $value === '' || $value === 0 ? [] : [$value];
        return self::arrays($value);
    }

    /** Complete candidate sections feed canonical form sanitizers; only approved leaves persist. */
    public function prepare(array $domain, array $capture, array $patch)
    {
        if (!$this->permitted()) return self::error('listdom_forbidden', __('The owning settings policy does not permit this update.', 'listdom'));
        $physical = $capture['physical'];
        $catalog = new LSD_Settings_Catalog();
        if (!is_array($physical['stored']) || ($this->option === 'lsd_addons' && $physical['serialized'] === null)) return self::error('listdom_settings_invalid', __('The owning option is malformed. Repair it before updating settings.', 'listdom'));
        $next = $physical['stored'];
        $candidate = LSD_Base::parse_args($capture['values']['stored'], $capture['values']['defaults']);
        $paths = [];
        $changes = [];
        foreach ($patch as $key => $value)
        {
            $field = $domain['fields'][$key] ?? null;
            if (!$field || ($field['write'] ?? '') !== 'leaf' || ($field['sensitivity'] ?? '') !== 'configuration')
                return self::error('listdom_setting_read_only', __('The patch contains an unknown or read-only setting.', 'listdom'));
            if (!current_user_can($field['capability'])) return self::error('listdom_forbidden', __('A setting requires additional permission.', 'listdom'));
            $valid = rest_validate_value_from_schema($value, $field['schema'], $key);
            // WP permits integer-like strings; exact approved writes require JSON numbers.
            if (is_wp_error($valid) || !self::strict_value($field['schema'], $value))
                return self::error('listdom_invalid_value', __('A value does not match its setting schema.', 'listdom'), ['keys' => [$key]]);
            $resource = self::resource($field, $value);
            if (is_wp_error($resource)) return $resource;
            $path = $this->field_path($field);
            if (LSD_Settings_Addon::malformed_path($next, $path))
                return self::error('listdom_settings_invalid', __('A settings container is malformed. Repair it before updating.', 'listdom'), ['keys' => [$key]]);
            $encoded = $this->encode($field, $value);
            $previous = $catalog->at($physical['stored'], $path, $present);
            $default = $catalog->at($capture['values']['defaults'], $field['path'], $has_default);
            $previous = $present ? $previous : ($has_default ? $default : $field['default']);
            if (!self::equal($catalog->scoped_value($field, $previous), $value))
            {
                self::put($next, $path, $encoded);
                $changes[$key] = true;
            }
            self::put($candidate, $field['path'], $encoded);
            $paths[$key] = $path;
        }
        // Unavailable domains permit only explicit clears, without waiving owner policy.
        if (empty($domain['available']))
            foreach ($patch as $key => $value)
                if (!LSD_Settings_Addon::cleared($candidate, $domain['fields'][$key]))
                    return self::error('listdom_setting_dependency', __('Enable this domain’s dependency before assigning active settings.', 'listdom'), ['keys' => [$key]]);
        if ($this->validate)
        {
            $valid = call_user_func($this->validate, $candidate, array_keys($patch), $domain);
            if (is_wp_error($valid)) return $valid;
        }
        $sanitized = $this->sanitize ? call_user_func($this->sanitize, $candidate) : LSD_Sanitize::deep($candidate);
        if (is_wp_error($sanitized)) return $sanitized;
        if (!is_array($sanitized)) return self::error('listdom_settings_invalid', __('The settings sanitizer rejected the candidate.', 'listdom'));
        foreach ($patch as $key => $requested)
        {
            $field = $domain['fields'][$key];
            $value = $catalog->at($sanitized, $field['path'], $present);
            if (!$present || !self::equal($catalog->scoped_value($field, $value), $requested))
                return self::error('listdom_setting_normalized', __('Use the canonical setting value shown by the owning form.', 'listdom'), ['keys' => [$key]]);
            if (isset($changes[$key])) self::put($next, $paths[$key], $value);
        }
        // Sanitizers may normalize coupled settings, but never silently authorize them.
        $required = [];
        foreach ($domain['fields'] as $key => $field)
        {
            if (isset($patch[$key]) || ($field['write'] ?? '') !== 'leaf') continue;
            $before = $catalog->at($candidate, $field['path']);
            $after = $catalog->at($sanitized, $field['path']);
            if (!self::equal($catalog->scoped_value($field, $before), $catalog->scoped_value($field, $after))) $required[] = $key;
        }
        if ($required) return self::error('listdom_setting_coupled', __('Include the required accompanying settings explicitly.', 'listdom'), ['keys' => array_slice($required, 0, 20)]);
        $leaves = [];
        foreach ($changes as $key => $changed) $leaves[] = ['path' => $paths[$key], 'value' => $catalog->at($next, $paths[$key])];
        return ['stored' => $next, 'leaves' => $leaves, 'paths' => $paths, 'changed' => serialize($next) !== serialize($physical['stored'])];
    }

    /** Resource selectors retain their owning WordPress capability checks. */
    public static function resource(array $field, $value)
    {
        $resource = $field['resource'] ?? null;
        if (!$resource) return true;
        $collection = is_array($value);
        foreach ($collection ? $value : [$value] as $id)
        {
            // Only scalar references have clear/sentinel values; collections clear with [].
            if (!$collection && ($id === '' || $id === 0 || in_array($id, $resource['sentinels'] ?? [], true))) continue;
            if (!is_int($id) || $id < 1) return self::error('listdom_invalid_value', __('Select an existing resource ID.', 'listdom'));
            if (isset($resource['taxonomy']))
            {
                $taxonomy = get_taxonomy($resource['taxonomy']);
                $term = get_term($id, $resource['taxonomy']);
                if (!$taxonomy || !$term || is_wp_error($term) || !current_user_can($taxonomy->cap->manage_terms))
                    return self::error('listdom_forbidden', __('This category or field is unavailable or cannot be managed by the current user.', 'listdom'));
            }
            else
            {
                $post = get_post($id);
                if (!$post || ($resource['postType'] !== '*' && $post->post_type !== $resource['postType']) || !current_user_can('edit_post', $id))
                    return self::error('listdom_forbidden', __('This resource is unavailable or cannot be edited by the current user.', 'listdom'));
            }
        }
        return true;
    }

    private static function strict_value(array $schema, $value): bool
    {
        foreach ($schema['anyOf'] ?? [] as $choice)
            if (!is_wp_error(rest_validate_value_from_schema($value, $choice, 'setting')) && self::strict_value($choice, $value)) return true;
        if (isset($schema['anyOf'])) return false;
        $types = (array) ($schema['type'] ?? []);
        if (is_string($value) && !in_array('string', $types, true)) return false;
        if (in_array('object', $types, true) && (is_object($value) || is_array($value)))
            foreach ((array) $value as $key => $item)
                if (isset($schema['properties'][$key]) && !self::strict_value($schema['properties'][$key], $item)) return false;
        if (is_array($value) && isset($schema['items']))
            foreach ($value as $item) if (!self::strict_value($schema['items'], $item)) return false;
        return true;
    }

    public static function equal($left, $right): bool
    {
        // JSON object member order is immaterial; number values use their schema's representation.
        return wp_json_encode(self::canonical($left)) === wp_json_encode(self::canonical($right));
    }

    private static function canonical($value)
    {
        if (is_object($value))
        {
            $members = (array) $value;
            ksort($members);
            foreach ($members as &$item) $item = self::canonical($item);
            unset($item);
            return (object) $members;
        }
        if (is_array($value)) foreach ($value as &$item) $item = self::canonical($item);
        unset($item);
        return $value;
    }

    public function commit(array $domain, array $capture, array &$prepared, callable $guard)
    {
        if (!$prepared['changed']) return true;
        // Rebase approved leaves once onto the fresh physical row. Another
        // add-on's completed save is preserved; the CAS itself is never retried.
        $physical = $this->snapshot();
        if ($physical['blogId'] !== $capture['physical']['blogId'] || !is_array($physical['stored'])
            || serialize($this->state($physical, $domain)) !== serialize($this->state($capture['physical'], $domain)))
            return self::error('listdom_settings_conflict', __('The owning settings changed before commit. Read them again.', 'listdom'));
        $expected = $physical['stored'];
        foreach ($prepared['leaves'] as $leaf) self::put($expected, $leaf['path'], $leaf['value']);
        $prepared['stored'] = $expected;
        return LSD_Options::compare_option($this->option, $physical, $expected, static function ($filtered) use ($expected, $guard)
        {
            if (serialize($filtered) !== serialize($expected))
                return self::error('listdom_settings_filtered', __('An option filter changed unapproved values. Nothing was saved.', 'listdom'));
            return $guard();
        });
    }

    public function after(array $prepared): bool
    {
        return !$this->after || call_user_func($this->after, $prepared['stored'], $prepared['paths'] ?? []) !== false;
    }

    public static function error(string $code, string $message, array $data = [], bool $uncertain = false): WP_Error
    {
        return new WP_Error($code, $message, ['outcome' => $uncertain ? 'uncertain' : 'failed', 'verified' => false] + $data);
    }
}
