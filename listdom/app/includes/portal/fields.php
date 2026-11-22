<?php

class LSD_Portal_Fields
{
    private const OPTION = 'lsd_portal_user_fields';

    public static function types(): array
    {
        return [
            'text' => __('Text', 'listdom'),
            'textarea' => __('Textarea', 'listdom'),
            'email' => __('Email', 'listdom'),
            'tel' => __('Phone', 'listdom'),
            'url' => __('URL', 'listdom'),
            'number' => __('Number', 'listdom'),
            'select' => __('Select', 'listdom'),
            'multi' => __('Multi-select', 'listdom'),
            'checkbox' => __('Checkbox', 'listdom'),
            'radio' => __('Radio', 'listdom'),
            'date' => __('Date', 'listdom'),
        ];
    }

    public static function stored(): array
    {
        return (array) get_option(self::OPTION, []);
    }

    public static function definitions(bool $filtered = true): array
    {
        if (!LSD_Base::isPro()) return [];
        $fields = array_filter(self::stored(), static function (array $field): bool
        {
            return empty($field['retired']);
        });
        return $filtered ? apply_filters('lsd_portal_user_field_definitions', $fields) : $fields;
    }

    public static function prefix(): string
    {
        return '_lsd_portal_field_' . get_current_blog_id() . '_';
    }

    public static function profile_editor(array $field): string
    {
        return !empty($field['edit']) ? 'editable' : (!empty($field['display']) ? 'readonly' : 'hidden');
    }

    public static function profile_editor_flags($mode): ?array
    {
        if (!in_array($mode, ['hidden', 'readonly', 'editable'], true)) return null;

        return ['display' => $mode !== 'hidden', 'edit' => $mode === 'editable'];
    }

    public static function sanitize_definition(array $input): ?array
    {
        $label = sanitize_text_field($input['label'] ?? '');
        $type = sanitize_key($input['type'] ?? '');
        if ($label === '' || !isset(self::types()[$type])) return null;
        $profile = self::profile_editor_flags($input['profile_editor'] ?? 'hidden');
        if ($profile === null) return null;

        $choices = [];
        foreach (explode(',', (string) ($input['choices'] ?? '')) as $choice)
        {
            $choice = sanitize_text_field($choice);
            if ($choice !== '') $choices[] = $choice;
        }

        $roles = LSD_Dashboard_Access::keys($input['roles'] ?? '');
        return [
            'label' => $label,
            'type' => $type,
            'choices' => array_values(array_unique($choices)),
            'roles' => $roles,
            'enabled' => !empty($input['enabled']),
            'required' => !empty($input['required']),
            'register' => !empty($input['register']),
            'display' => $profile['display'],
            'edit' => $profile['edit'],
            'public' => !empty($input['public']),
        ];
    }

    public static function store(array $fields): void
    {
        update_option(self::OPTION, $fields);
    }

    public static function visible(array $field, array $roles, string $context): bool
    {
        // Legacy invalid definitions must not render an impossible required input.
        if (in_array($context, ['register', 'edit'], true) && !self::has_input_choices($field)) return false;
        return !empty($field['enabled']) && !empty($field[$context]) && (empty($field['roles']) || (bool) array_intersect($field['roles'], $roles));
    }

    public static function has_input_choices(array $field): bool
    {
        return !in_array($field['type'], ['select', 'multi', 'radio'], true) || !empty($field['choices']);
    }

    public static function validate($input, array $roles, string $context)
    {
        $input = is_array($input) ? $input : [];
        $result = [];

        $fields = self::definitions();
        if ($context === 'edit')
        {
            foreach ($input as $key => $value)
            {
                if (!isset($fields[$key]) || !self::visible($fields[$key], $roles, 'edit'))
                    return LSD_Portal_Fieldlayout::inaccessible($fields[$key]['label'] ?? '');
            }
        }

        foreach ($fields as $key => $field)
        {
            if (!self::visible($field, $roles, $context)) continue;
            if (!array_key_exists($key, $input) && $context !== 'register') continue;
            $value = self::value($field, $input[$key] ?? '');
            if ($value === null || (!empty($field['required']) && self::blank($field, $value)))
            {
                return new WP_Error('lsd_invalid_user_field', sprintf(
                    /* translators: %s: field label. */
                    __('Enter a valid value for %s.', 'listdom'),
                    $field['label']
                ), ['status' => 400]);
            }
            $result[$key] = $value;
        }

        return $result;
    }

    private static function blank(array $field, $value): bool
    {
        return $value === '' || $value === [] || ($field['type'] === 'checkbox' && $value !== '1');
    }

    public static function value(array $field, $value)
    {
        $type = $field['type'];
        if ($type === 'multi')
        {
            if ($value === '') return [];
            if (!is_array($value) || count(array_filter($value, 'is_string')) !== count($value)) return null;
            $values = array_values(array_unique(array_filter($value, static function (string $choice): bool { return $choice !== ''; })));
            return array_diff($values, $field['choices']) ? null : $values;
        }

        if (!is_scalar($value)) return null;
        $value = trim((string) $value);
        if ($value === '') return '';
        switch ($type)
        {
            case 'checkbox':
                return in_array($value, ['0', '1'], true) ? $value : null;
            case 'radio':
            case 'select':
                return in_array($value, $field['choices'], true) ? $value : null;
            case 'email':
                return is_email($value) ? sanitize_email($value) : null;
            case 'url':
                $url = esc_url_raw($value, ['http', 'https']);
                return $url !== '' ? $url : null;
            case 'tel':
                return preg_match('/^\+?[\d(). -]+$/D', $value) && preg_match('/\d/', $value) ? $value : null;
            case 'number':
                return is_numeric($value) && is_finite((float) $value) ? $value : null;
            case 'date':
                if (!preg_match('/^\d{4}-\d{2}-\d{2}$/D', $value)) return null;
                $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
                return $date && $date->format('Y-m-d') === $value ? $value : null;
            case 'textarea':
                return sanitize_textarea_field($value);
            default:
                return sanitize_text_field($value);
        }
    }

    public static function meta_input(array $values): array
    {
        $meta = [];
        foreach ($values as $key => $value) $meta[self::prefix() . $key] = wp_slash($value);
        return $meta;
    }

    public static function persist(int $id, array $values): void
    {
        foreach (self::meta_input($values) as $key => $value)
        {
            update_user_meta($id, $key, $value);
        }
    }

    public static function read(WP_User $user): array
    {
        $values = [];
        if (!$user->exists() || $user->ID !== get_current_user_id()) return $values;
        foreach (self::definitions() as $key => $field)
        {
            if (self::visible($field, $user->roles, 'display') || self::visible($field, $user->roles, 'edit'))
            {
                $values[$key] = self::value($field, get_user_meta($user->ID, self::prefix() . $key, true)) ?? '';
            }
        }
        return $values;
    }

    public static function registration(string $role): void
    {
        $role = LSD_Security::registration_role($role);
        if (is_wp_error($role)) return;

        foreach (LSD_Portal_Fieldlayout::view('register', [$role]) as $field)
        {
            if ($field['kind'] === 'layout')
            {
                include lsd_template('auth/user-field-layout.php');
                continue;
            }

            $id = wp_unique_id('lsd-user-field-');
            $name = ($field['kind'] === 'custom' ? 'lsd_user_fields' : 'lsd_registration_fields') . '[' . $field['key'] . ']';
            $value = '';
            $editable = true;
            include lsd_template('auth/user-field.php');
        }
    }
}
