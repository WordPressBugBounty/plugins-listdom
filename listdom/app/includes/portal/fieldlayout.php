<?php

class LSD_Portal_Fieldlayout
{
    private const OPTION = 'lsd_user_field_layout';

    private static function defaults(): array
    {
        $legacy = LSD_User::profile_edit_fields(false);
        $groups = [
            'about' => [
                'label' => __('About', 'listdom'),
                'fields' => [
                    'first_name' => [__('First Name', 'listdom'), 'text'],
                    'last_name' => [__('Last Name', 'listdom'), 'text'],
                    'job_title' => [__('Job Title', 'listdom'), 'text'],
                    'bio' => [__('Bio', 'listdom'), 'textarea'],
                    'profile_image' => [__('Profile Image', 'listdom'), 'image'],
                    'hero_image' => [__('Cover Image', 'listdom'), 'image'],
                ],
            ],
            'contact' => [
                'label' => __('Contact Info', 'listdom'),
                'fields' => [
                    'email' => [__('Account Email', 'listdom'), 'email'],
                    'phone' => [__('Phone', 'listdom'), 'tel'],
                    'mobile' => [__('Mobile', 'listdom'), 'tel'],
                    'website' => [__('Website', 'listdom'), 'url'],
                    'fax' => [__('Fax', 'listdom'), 'tel'],
                ],
            ],
            'social' => ['label' => __('Social Media', 'listdom'), 'fields' => []],
        ];

        $socials = new LSD_Socials();
        $options = LSD_Options::socials();
        foreach ($legacy['social'] as $key => $enabled)
        {
            $network = $socials->get($key, $options[$key] ?? []);
            if ($network) $groups['social']['fields'][$key] = [$network->label(), $network->get_input_type()];
        }

        $rows = [];
        foreach ($groups as $group => $section)
        {
            $rows['layout:' . $group] = [
                'kind' => 'layout',
                'label' => $section['label'],
                'type' => 'heading',
                'enabled' => true,
            ];
            foreach ($section['fields'] as $key => [$label, $type])
            {
                $locked = in_array($key, ['first_name', 'last_name'], true);
                $social = $group === 'social';
                $rows['core:' . $key] = [
                    'kind' => 'core',
                    'key' => $key,
                    'label' => $label,
                    'type' => $type,
                    'locked' => $locked,
                    'social' => $social,
                    'enabled' => true,
                    'collectable' => $type !== 'image' && $key !== 'email',
                    'register' => false,
                    'required' => false,
                    'display' => $locked || !empty($social ? $legacy['social'][$key] : $legacy[$key]),
                    'edit' => $key !== 'email' && ($locked || !empty($social ? $legacy['social'][$key] : $legacy[$key])),
                    'public' => !$social || !empty($options[$key]['profile']),
                ];
            }
        }

        return $rows;
    }

    public static function core(): array
    {
        $saved = get_option(self::OPTION, []);
        $fields = [];
        foreach (self::defaults() as $id => $field)
        {
            if ($field['kind'] !== 'core') continue;
            $fields[$field['key']] = array_replace($field, $saved[$id] ?? []);
        }
        return $fields;
    }

    public static function rows(bool $for_settings = false): array
    {
        $saved = get_option(self::OPTION, []);
        $defaults = self::defaults();
        $rows = [];
        foreach ($saved as $id => $settings)
        {
            if (isset($defaults[$id]))
            {
                if (empty($settings['removed'])) $rows[$id] = array_replace($defaults[$id], $settings);
                unset($defaults[$id]);
            }
            else if (strpos($id, 'layout:') === 0) $rows[$id] = $settings + ['kind' => 'layout'];
        }

        $custom = LSD_Portal_Fields::definitions(!$for_settings);
        uasort($custom, static function (array $a, array $b): int
        {
            return ($a['order'] ?? 0) <=> ($b['order'] ?? 0);
        });
        foreach ($custom as $key => $field)
        {
            $defaults['custom:' . $key] = $field + ['kind' => 'custom', 'key' => $key];
        }

        // Start with the persisted order, including slots for active custom fields.
        $ordered = [];
        foreach ($saved as $id => $settings)
        {
            if (isset($rows[$id])) $ordered[$id] = $rows[$id];
            else if (isset($defaults[$id])) $ordered[$id] = $defaults[$id];
        }
        return $ordered + $defaults;
    }

    public static function prepare(array $input)
    {
        $rows = self::rows(true);
        $defaults = self::defaults();
        $previous = get_option(self::OPTION, []);
        $stored = LSD_Portal_Fields::stored();
        $custom = $stored;
        $saved = [];
        $removed = [];

        foreach ($input['removed'] ?? [] as $id)
        {
            if (!is_string($id) || !isset($rows[$id])) continue;
            $field = $rows[$id];
            if ($field['kind'] === 'layout') $removed[$id] = true;
            if ($field['kind'] === 'custom' && isset($custom[$field['key']])) $custom[$field['key']]['retired'] = true;
        }

        foreach ($input['rows'] ?? [] as $id => $values)
        {
            if (!is_array($values) || isset($removed[$id])) continue;
            $field = $rows[$id] ?? null;
            if (!$field && strpos($id, 'new-layout:') === 0)
            {
                $id = 'layout:' . wp_generate_uuid4();
                $field = ['kind' => 'layout'];
            }
            if (!$field && LSD_Base::isPro() && strpos($id, 'new-custom:') === 0)
            {
                $key = wp_generate_uuid4();
                $id = 'custom:' . $key;
                $field = ['kind' => 'custom', 'key' => $key];
            }
            if (!$field) continue;

            if ($field['kind'] === 'custom')
            {
                $key = $field['key'];
                if (!empty($custom[$key]['retired'])) continue;
                $definition = LSD_Portal_Fields::sanitize_definition($values);
                if (!$definition) return self::invalid_settings();
                if ($definition['enabled'] && ($definition['register'] || $definition['edit']) && !LSD_Portal_Fields::has_input_choices($definition))
                {
                    return new WP_Error('lsd_invalid_user_field_choices', sprintf(
                        /* translators: %s: field label. */
                        __('Add at least one choice for %s, or disable its registration and editing inputs.', 'listdom'),
                        $definition['label']
                    ));
                }
                $custom[$key] = $definition + ['order' => count($saved)];
                $saved[$id] = [];
            }
            else if ($field['kind'] === 'layout')
            {
                $type = $values['type'] ?? '';
                $label = sanitize_text_field($values['label'] ?? '');
                if (!in_array($type, ['heading', 'separator'], true) || ($type === 'heading' && $label === '')) return self::invalid_settings();
                $saved[$id] = ['type' => $type, 'label' => $label, 'enabled' => !empty($values['enabled'])];
            }
            else
            {
                $register = $field['collectable'] && !empty($values['register']);
                $locked = $field['locked'];
                $profile_editor = $locked ? 'editable' : ($values['profile_editor'] ?? 'hidden');
                $profile = LSD_Portal_Fields::profile_editor_flags($profile_editor);
                if ($profile === null) return self::invalid_settings();
                $saved[$id] = [
                    'enabled' => $locked || !empty($values['enabled']),
                    'display' => $profile['display'],
                    'edit' => $field['key'] !== 'email' && $profile['edit'],
                    'public' => $locked || !empty($values['public']),
                    'register' => $register,
                    'required' => $register && !empty($values['required']),
                ];
            }
        }

        foreach ($rows as $id => $field)
        {
            if (isset($saved[$id])) continue;
            if (isset($removed[$id]))
            {
                if (isset($defaults[$id])) $saved[$id] = ['removed' => true];
                continue;
            }
            if ($field['kind'] === 'custom' && !empty($custom[$field['key']]['retired'])) continue;
            $saved[$id] = $previous[$id] ?? [];
        }

        // Keep the positions of Pro fields when saving the visible list in Free.
        if (!LSD_Base::isPro())
        {
            $ordered = [];
            $visible = array_keys($saved);
            foreach ($previous as $id => $settings)
            {
                if (strpos($id, 'custom:') === 0) $ordered[$id] = $settings;
                else if ($visible)
                {
                    $next = array_shift($visible);
                    $ordered[$next] = $saved[$next];
                }
            }
            $saved = $ordered + $saved;
        }

        // Keep tombstones for removed default headings so they are never seeded again.
        foreach ($previous as $id => $settings)
        {
            if (!empty($settings['removed'])) $saved[$id] = $settings;
        }

        return ['layout' => $saved, 'custom' => $custom];
    }

    private static function invalid_settings(): WP_Error
    {
        return new WP_Error('lsd_invalid_user_field_settings', __('Give each field and heading a label and choose a valid type.', 'listdom'));
    }

    public static function save(array $data): void
    {
        if (LSD_Base::isPro()) LSD_Portal_Fields::store($data['custom']);
        update_option(self::OPTION, $data['layout']);
    }

    public static function is_public(string $key): bool
    {
        $field = self::core()[$key] ?? [];
        return !empty($field['enabled']) && !empty($field['public']);
    }

    public static function view(string $context, array $roles = [], int $user_id = 0, bool $limited = false): array
    {
        $rows = [];
        $options = $context === 'public' ? LSD_Options::socials() : [];
        foreach (self::rows() as $field)
        {
            if (empty($field['enabled'])) continue;
            if ($field['kind'] === 'layout')
            {
                $rows[] = $field;
                continue;
            }

            $key = $field['key'];
            $custom = $field['kind'] === 'custom';
            $editable = $context !== 'public' && LSD_Portal_Fields::visible($field, $roles, $context);
            if ($context === 'edit' && !$editable && !LSD_Portal_Fields::visible($field, $roles, 'display')) continue;
            if ($context === 'register' && !$editable) continue;
            if ($context === 'public')
            {
                if (empty($field['public'])) continue;
                if ($custom && !LSD_Portal::public_profiles()) continue;
                if (!$custom && ($limited ? $key !== 'bio' : in_array($key, ['first_name', 'last_name', 'job_title', 'profile_image', 'hero_image'], true))) continue;
                if (!empty($field['social']) && empty($options[$key]['profile'])) continue;
            }

            $field['editable'] = $editable;
            if ($user_id)
            {
                $meta = $custom ? LSD_Portal_Fields::prefix() . $key : self::storage($key)[1];
                $value = $key === 'email' && !$custom ? get_userdata($user_id)->user_email : get_user_meta($user_id, $meta, true);
                $field['value'] = $custom ? (LSD_Portal_Fields::value($field, $value) ?? '') : $value;
                if ($context === 'public' && ($field['value'] === '' || $field['value'] === [])) continue;
            }
            $rows[] = $field;
        }

        // A heading needs a visible field before the next heading. A separator needs one on both sides.
        $following = false;
        foreach (array_reverse($rows, true) as $index => $field)
        {
            if ($field['kind'] !== 'layout') $following = true;
            else
            {
                if (!$following) unset($rows[$index]);
                if ($field['type'] === 'heading') $following = false;
            }
        }

        $preceding = false;
        foreach ($rows as $index => $field)
        {
            if ($field['kind'] !== 'layout') $preceding = true;
            else
            {
                if ($field['type'] === 'separator' && !$preceding) unset($rows[$index]);
                $preceding = false;
            }
        }
        return $rows;
    }

    // WordPress user properties retain their native storage. Other standard fields use Listdom meta.
    public static function storage(string $key): array
    {
        if ($key === 'bio') return [true, 'description'];
        if (in_array($key, ['first_name', 'last_name'], true)) return [true, $key];
        return [false, 'lsd_' . $key];
    }

    public static function check_profile_write(array $input, array $editable)
    {
        foreach (self::core() as $key => $field)
        {
            // Email is displayed in profiles, but changes belong to Account Security.
            $allowed = $key !== 'email' && ($field['social'] ? ($editable['social'][$key] ?? false) : ($editable[$key] ?? false));
            if ($allowed || !array_key_exists($key, $input)) continue;
            return self::inaccessible($field['label']);
        }
        return true;
    }

    public static function inaccessible(string $label = ''): WP_Error
    {
        $message = $label === '' ? __('This profile field is unavailable for editing.', 'listdom') : sprintf(
            /* translators: %s: field label. */
            __('You cannot edit the %s field.', 'listdom'),
            $label
        );
        return new WP_Error('lsd_user_field_inaccessible', $message, ['status' => 400]);
    }

    public static function registration_values($input, $custom, string $role)
    {
        $input = is_array($input) ? $input : [];
        $standard = [];
        foreach (self::core() as $key => $field)
        {
            if (!$field['enabled'] || !$field['register']) continue;
            $value = LSD_Portal_Fields::value($field, $input[$key] ?? '');
            if ($value === null || ($field['required'] && $value === ''))
            {
                return new WP_Error('lsd_invalid_user_field', sprintf(
                    /* translators: %s: field label. */
                    __('Enter a valid value for %s.', 'listdom'),
                    $field['label']
                ), ['status' => 400]);
            }
            if ($value !== '') $standard[$key] = $value;
        }
        $custom = LSD_Portal_Fields::validate($custom, [$role], 'register');
        if (is_wp_error($custom)) return $custom;
        return ['standard' => $standard, 'custom' => $custom];
    }

    public static function registration_user_data(array $fields): array
    {
        $data = ['meta_input' => LSD_Portal_Fields::meta_input($fields['custom'])];
        foreach ($fields['standard'] as $key => $value)
        {
            [$native, $meta] = self::storage($key);
            if ($native) $data[$meta] = wp_slash($value);
            else $data['meta_input'][$meta] = wp_slash($value);
        }
        return $data;
    }
}
