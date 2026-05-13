<?php

class LSD_Roles extends LSD_Base
{
    public static function add(bool $force = false): bool
    {
        $updated = false;

        foreach (self::definitions() as $role => $definition)
        {
            $existing = get_role($role);
            $capabilities = $definition['capabilities'];

            if (!$force && $existing)
            {
                $missing_capability = false;
                foreach ($capabilities as $capability => $granted)
                {
                    if ($existing->has_cap($capability) !== (bool) $granted)
                    {
                        $missing_capability = true;
                        break;
                    }
                }

                if (!$missing_capability) continue;
            }

            if ($existing) remove_role($role);

            add_role($role, $definition['label'], $capabilities);
            $updated = true;
        }

        return $updated;
    }

    public static function ensure(): void
    {
        static $checked = false;

        if ($checked) return;
        $checked = true;

        foreach (array_keys(self::definitions()) as $role)
        {
            if (!get_role($role))
            {
                self::add();
                break;
            }
        }
    }

    private static function definitions(): array
    {
        return [
            'listdom_author' => [
                'label' => 'Listdom Author',
                'capabilities' => [
                    'read' => true,
                    'edit_posts' => true,
                    'delete_posts' => true,
                    'delete_published_posts' => true,
                    'edit_published_posts' => true,
                    'edit_listings' => true,
                    'delete_listings' => true,
                    'edit_listing' => true,
                    'upload_files' => true,
                ],
            ],
            'listdom_publisher' => [
                'label' => 'Listdom Publisher',
                'capabilities' => [
                    'read' => true,
                    'edit_posts' => true,
                    'delete_posts' => true,
                    'publish_posts' => true,
                    'delete_published_posts' => true,
                    'edit_published_posts' => true,
                    'edit_listings' => true,
                    'delete_listings' => true,
                    'edit_listing' => true,
                    'upload_files' => true,
                ],
            ],
        ];
    }

    public static function all(): array
    {
        $roles = [];
        foreach (wp_roles()->roles as $key => $details) $roles[$key] = translate_user_role($details['name']);

        return $roles;
    }

    public static function supported(bool $only_keys = false): array
    {
        $roles = apply_filters('lsd_user_supported_roles', [
            'subscriber' => esc_html__('Subscriber', 'listdom'),
            'contributor' => esc_html__('Contributor', 'listdom'),
            'listdom_author' => esc_html__('Listdom Author', 'listdom'),
            'listdom_publisher' => esc_html__('Listdom Publisher', 'listdom'),
        ]);

        // Only Keys
        if ($only_keys) return array_keys($roles);

        return $roles;
    }
}
