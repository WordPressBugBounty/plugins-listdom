<?php

class LSD_Privacy extends LSD_Base
{
    public function init()
    {
        if (function_exists('wp_add_privacy_policy_content'))
        {
            add_action('admin_init', [$this, 'register_policy_content']);
        }

        add_filter('wp_privacy_personal_data_exporters', [$this, 'add_exporter']);
        add_filter('wp_privacy_personal_data_erasers', [$this, 'add_eraser']);
    }

    public function register_policy_content()
    {
        $settings = self::privacy_settings();

        $paragraphs = self::policy_paragraphs($settings);

        if (empty($paragraphs)) return;

        $paragraphs = array_map(function ($paragraph)
        {
            $paragraph = self::replace_privacy_placeholder($paragraph, false);
            $paragraph = wp_kses_post($paragraph);

            return trim($paragraph);
        }, $paragraphs);

        $paragraphs = array_filter($paragraphs, 'strlen');
        if (empty($paragraphs)) return;

        wp_add_privacy_policy_content(
            'Listdom',
            '<p>' . implode('</p><p>', $paragraphs) . '</p>'
        );
    }

    public function add_exporter($exporters): array
    {
        $exporters['listdom'] = [
            'exporter_friendly_name' => esc_html__('Listdom', 'listdom'),
            'callback' => [$this, 'export_personal_data'],
        ];

        return $exporters;
    }

    public function add_eraser($erasers): array
    {
        $erasers['listdom'] = [
            'eraser_friendly_name' => esc_html__('Listdom', 'listdom'),
            'callback' => [$this, 'erase_personal_data'],
        ];

        return $erasers;
    }

    public function export_personal_data(string $email_address, int $page = 1): array
    {
        $page = max(1, $page);
        $per_page = 20;
        $groups = [];

        $user = get_user_by('email', $email_address);
        if ($user && $page === 1)
        {
            $user_data = $this->user_export_data($user);
            if (!empty($user_data)) $groups[] = ['group_id' => 'listdom-user-' . $user->ID, 'group_label' => esc_html__('Listdom User Data', 'listdom'), 'item_id' => 'user-' . $user->ID, 'data' => $user_data,];
        }

        $query = $this->listing_query($email_address, $page, $per_page);
        foreach ($query->posts as $listing_id)
        {
            $listing_data = $this->listing_export_data((int) $listing_id, $email_address);
            if (!empty($listing_data)) $groups[] = ['group_id' => 'listdom-listing', 'group_label' => esc_html__('Listdom Listings', 'listdom'), 'item_id' => 'listing-' . $listing_id, 'data' => $listing_data,];
        }

        $max_pages = max(1, $query->max_num_pages);

        return [
            'data' => $groups,
            'done' => $page >= $max_pages,
        ];
    }

    public function erase_personal_data(string $email_address, int $page = 1): array
    {
        $page = max(1, $page);
        $per_page = 20;
        $items_removed = false;

        $user = get_user_by('email', $email_address);
        if ($user && $page === 1) $items_removed = $this->erase_user_data($user);

        $query = $this->listing_query($email_address, $page, $per_page);
        foreach ($query->posts as $listing_id)
        {
            if ($this->erase_listing_data((int) $listing_id, $email_address)) $items_removed = true;
        }

        $max_pages = max(1, $query->max_num_pages);

        return [
            'items_removed' => $items_removed,
            'items_retained' => false,
            'messages' => [],
            'done' => $page >= $max_pages,
        ];
    }

    public static function consent_field(array $args = []): string
    {
        $defaults = [
            'id' => 'lsd_privacy_consent',
            'name' => 'lsd_privacy_consent',
            'required' => true,
            'checked' => false,
            'class' => 'lsd-privacy-consent-checkbox',
            'wrapper_class' => 'lsd-privacy-consent',
            'label' => '',
            'description' => '',
            'context' => '',
        ];

        $field = wp_parse_args($args, $defaults);
        $field['context'] = sanitize_key($field['context']);

        if (!self::is_consent_enabled($field['context'])) return '';

        $field['required'] = !empty($field['required']);
        $field['checked'] = !empty($field['checked']);

        if (!isset($field['required_message']) || $field['required_message'] === '') $field['required_message'] = self::consent_required_text();

        if ($field['label'] === '') $field['label'] = self::context_label($field['context']);
        if ($field['label'] === '') $field['label'] = self::default_label();

        $field['label'] = apply_filters('lsd_pc_label', self::replace_privacy_placeholder($field['label']), $field);
        $field['wrapper_class'] = trim('lsd-privacy-consent-wrapper ' . $field['wrapper_class']);

        // Generate output
        ob_start();
        include lsd_template('privacy/consent-field.php');
        return ob_get_clean();
    }

    public static function consent_required_text(): string
    {
        $settings = self::privacy_settings();
        $message = $settings['required_message'] ?? '';

        if ($message === '') $message = esc_html__('Please confirm that you agree to our privacy policy before continuing.', 'listdom');

        $message = apply_filters('lsd_privacy_consent_required_text', $message, $settings);

        return sanitize_text_field($message);
    }

    public static function is_consent_enabled(string $context): bool
    {
        $context = sanitize_key($context);

        $settings = self::privacy_settings();

        $enabled = !empty($settings['enabled']);

        if ($enabled && $context !== '')
        {
            $contexts = isset($settings['contexts']) && is_array($settings['contexts']) ? $settings['contexts'] : [];
            if (isset($contexts[$context])) $enabled = !empty($contexts[$context]['enabled']);
        }

        return (bool) apply_filters('lsd_pc_enabled', $enabled, $context, $settings);
    }

    public static function create_log(array $extra = []): array
    {
        array_walk_recursive($extra, function (&$value)
        {
            $value = sanitize_text_field((string) $value);
        });

        // Main
        $main = new LSD_Main();

        return [
            'consent' => true,
            'timestamp' => current_time('mysql'),
            'ip' => sanitize_text_field($main->current_ip()),
            'extra' => $extra,
        ];
    }

    public static function format_consent_export_value($value): string
    {
        if (!is_array($value)) return sanitize_text_field((string) $value);

        $parts = [];

        if (!empty($value['timestamp'])) $parts[] = sprintf(
            /* translators: %s: Timestamp value. */
            esc_html__('Timestamp: %s', 'listdom'),
            sanitize_text_field($value['timestamp'])
        );

        if (!empty($value['ip'])) $parts[] = sprintf(
            /* translators: %s: IP address value. */
            esc_html__('IP Address: %s', 'listdom'),
            sanitize_text_field($value['ip'])
        );

        if (!empty($value['extra']) && is_array($value['extra']))
        {
            $labels = [
                'context' => esc_html__('Context', 'listdom'),
                'email' => esc_html__('Email', 'listdom'),
                'listing_id' => esc_html__('Listing ID', 'listdom'),
                'user_id' => esc_html__('User ID', 'listdom'),
            ];

            foreach ($value['extra'] as $key => $extra_value)
            {
                if (is_array($extra_value)) continue;

                $label = $labels[$key] ?? ucfirst(str_replace('_', ' ', sanitize_text_field($key)));
                $parts[] = sprintf('%s: %s', $label, sanitize_text_field((string) $extra_value));
            }
        }

        return implode(' | ', $parts);
    }

    private function user_export_data(WP_User $user): array
    {
        $meta_map = [
            'lsd_job_title' => esc_html__('Job Title', 'listdom'),
            'lsd_profile_image' => esc_html__('Profile Image', 'listdom'),
            'lsd_hero_image' => esc_html__('Hero Image', 'listdom'),
            'lsd_phone' => esc_html__('Phone', 'listdom'),
            'lsd_mobile' => esc_html__('Mobile', 'listdom'),
            'lsd_website' => esc_html__('Website', 'listdom'),
            'lsd_fax' => esc_html__('Fax', 'listdom'),
            'lsd_facebook' => esc_html__('Facebook', 'listdom'),
            'lsd_twitter' => esc_html__('Twitter', 'listdom'),
            'lsd_pinterest' => esc_html__('Pinterest', 'listdom'),
            'lsd_linkedin' => esc_html__('LinkedIn', 'listdom'),
            'lsd_instagram' => esc_html__('Instagram', 'listdom'),
            'lsd_whatsapp' => esc_html__('WhatsApp', 'listdom'),
            'lsd_youtube' => esc_html__('YouTube', 'listdom'),
            'lsd_tiktok' => esc_html__('TikTok', 'listdom'),
            'lsd_telegram' => esc_html__('Telegram', 'listdom'),
            'lsd_privacy_consent' => esc_html__('Privacy Consent', 'listdom'),
        ];

        $data = [];

        foreach ($meta_map as $key => $label)
        {
            $value = get_user_meta($user->ID, $key, true);
            if ($value === '' || $value === null) continue;

            if ($key === 'lsd_privacy_consent')
            {
                $formatted = self::format_consent_export_value($value);
            }
            else if (is_array($value))
            {
                $formatted = implode(', ', array_map('sanitize_text_field', $value));
            }
            else if (in_array($key, ['lsd_profile_image', 'lsd_hero_image'], true) && is_numeric($value))
            {
                $url = wp_get_attachment_url((int) $value);
                $formatted = trim($value . ($url ? ' (' . esc_url($url) . ')' : ''));
            }
            else if ($key === 'lsd_website')
            {
                $formatted = esc_url((string) $value);
            }
            else
            {
                $formatted = sanitize_text_field((string) $value);
            }

            if ($formatted === '') continue;

            $data[] = [
                'name' => $label,
                'value' => $formatted,
            ];
        }

        return $data;
    }

    private function listing_export_data(int $listing_id, string $email_address): array
    {
        $data = [];

        $data[] = [
            'name' => esc_html__('Listing Title', 'listdom'),
            'value' => get_the_title($listing_id),
        ];

        $permalink = get_permalink($listing_id);
        if ($permalink)
        {
            $data[] = [
                'name' => esc_html__('Listing URL', 'listdom'),
                'value' => esc_url($permalink),
            ];
        }

        return array_merge(
            $data,
            $this->listing_contact_export_data($listing_id, $email_address),
            $this->listing_guest_export_data($listing_id, $email_address),
            $this->listing_consent_export_data($listing_id, $email_address)
        );
    }

    private function listing_contact_export_data(int $listing_id, string $email_address): array
    {
        $listing_email = sanitize_email(get_post_meta($listing_id, 'lsd_email', true));
        if (!$this->emails_match($listing_email, $email_address)) return [];

        $map = [
            'lsd_email' => esc_html__('Listing Email', 'listdom'),
            'lsd_phone' => esc_html__('Listing Phone', 'listdom'),
            'lsd_website' => esc_html__('Listing Website', 'listdom'),
            'lsd_contact_address' => esc_html__('Listing Contact Address', 'listdom'),
        ];

        $data = [];

        foreach ($map as $meta_key => $label)
        {
            $value = get_post_meta($listing_id, $meta_key, true);
            if ($value === '' || $value === null) continue;

            if ($meta_key === 'lsd_website') $formatted = esc_url((string) $value);
            else $formatted = sanitize_text_field((string) $value);

            if ($formatted === '') continue;

            $data[] = [
                'name' => $label,
                'value' => $formatted,
            ];
        }

        return $data;
    }

    private function listing_guest_export_data(int $listing_id, string $email_address): array
    {
        $guest_email = sanitize_email(get_post_meta($listing_id, 'lsd_guest_email', true));
        if (!$this->emails_match($guest_email, $email_address)) return [];

        $map = [
            'lsd_guest_email' => esc_html__('Guest Email', 'listdom'),
            'lsd_guest_fullname' => esc_html__('Guest Name', 'listdom'),
            'lsd_guest_message' => esc_html__('Guest Message', 'listdom'),
        ];

        $data = [];

        foreach ($map as $meta_key => $label)
        {
            $value = get_post_meta($listing_id, $meta_key, true);
            if ($value === '' || $value === null) continue;

            $formatted = sanitize_text_field((string) $value);
            if ($formatted === '') continue;

            $data[] = [
                'name' => $label,
                'value' => $formatted,
            ];
        }

        return $data;
    }

    private function listing_consent_export_data(int $listing_id, string $email_address): array
    {
        $consent = get_post_meta($listing_id, 'lsd_privacy_consent', true);
        if (!$consent) return [];

        if (!$this->consent_matches_email($consent, $email_address)) return [];

        $value = self::format_consent_export_value($consent);
        if ($value === '') return [];

        return [[
            'name' => esc_html__('Privacy Consent', 'listdom'),
            'value' => $value,
        ]];
    }

    private function erase_user_data(WP_User $user): bool
    {
        $keys = [
            'lsd_job_title',
            'lsd_profile_image',
            'lsd_hero_image',
            'lsd_phone',
            'lsd_mobile',
            'lsd_website',
            'lsd_fax',
            'lsd_facebook',
            'lsd_twitter',
            'lsd_pinterest',
            'lsd_linkedin',
            'lsd_instagram',
            'lsd_whatsapp',
            'lsd_youtube',
            'lsd_tiktok',
            'lsd_telegram',
            'lsd_privacy_consent',
        ];

        $removed = false;

        foreach ($keys as $key)
        {
            $value = get_user_meta($user->ID, $key, true);
            if ($value === '' || $value === null) continue;

            delete_user_meta($user->ID, $key);
            $removed = true;
        }

        return $removed;
    }

    private function erase_listing_data(int $listing_id, string $email_address): bool
    {
        $this->clear_listing_contacts($listing_id, $email_address);
        $this->clear_listing_guest_data($listing_id, $email_address);
        $this->maybe_erase_consent($listing_id, $email_address);

        return true;
    }


    private function clear_listing_contacts(int $listing_id, string $email_address): void
    {
        $listing_email = sanitize_email(get_post_meta($listing_id, 'lsd_email', true));
        if (!$this->emails_match($listing_email, $email_address)) return;

        $keys = ['lsd_email', 'lsd_phone', 'lsd_website', 'lsd_contact_address'];
        foreach ($keys as $key)
        {
            $value = get_post_meta($listing_id, $key, true);
            if ($value === '' || $value === null) continue;

            update_post_meta($listing_id, $key, '');
        }
    }

    private function clear_listing_guest_data(int $listing_id, string $email_address): void
    {
        $guest_email = sanitize_email(get_post_meta($listing_id, 'lsd_guest_email', true));
        if (!$this->emails_match($guest_email, $email_address)) return;

        $keys = ['lsd_guest_email', 'lsd_guest_fullname', 'lsd_guest_message'];
        foreach ($keys as $key)
        {
            if (get_post_meta($listing_id, $key, true) === '') continue;

            delete_post_meta($listing_id, $key);
        }
    }

    private function maybe_erase_consent(int $listing_id, string $email_address): void
    {
        $consent = get_post_meta($listing_id, 'lsd_privacy_consent', true);
        if (!$consent) return;

        if (!$this->consent_matches_email($consent, $email_address)) return;
        delete_post_meta($listing_id, 'lsd_privacy_consent');
    }

    private function listing_query(string $email_address, int $page, int $per_page): WP_Query
    {
        return new WP_Query([
            'post_type' => LSD_Base::PTYPE_LISTING,
            'post_status' => 'any',
            'fields' => 'ids',
            'posts_per_page' => $per_page,
            'paged' => $page,
            'meta_query' => [
                'relation' => 'OR',
                [
                    'key' => 'lsd_email',
                    'value' => $email_address,
                ],
                [
                    'key' => 'lsd_guest_email',
                    'value' => $email_address,
                ],
            ],
        ]);
    }

    private static function context_label(string $context): string
    {
        $settings = self::privacy_settings();

        if ($context !== '')
        {
            $contexts = isset($settings['contexts']) && is_array($settings['contexts']) ? $settings['contexts'] : [];
            if (isset($contexts[$context]))
            {
                $label = $contexts[$context]['label'] ?? '';
                if ($label !== '') return $label;
            }
        }

        return $settings['default_label'] ?? '';
    }

    private static function default_label(): string
    {
        return esc_html__('I agree to the {{privacy_policy}}.', 'listdom');
    }

    public static function default_policy_content(): string
    {
        return implode("\n\n", self::default_policy_paragraphs());
    }

    private static function default_policy_paragraphs(): array
    {
        return [
            esc_html__('Listdom stores personal information that you provide when you submit listings, send contact or report forms, register for an account, or manage your profile. This data can include names, email addresses, phone numbers, and any messages that you send through the plugin.', 'listdom'),
            esc_html__('The information is saved inside your WordPress database as user metadata or listing metadata and is retained until you delete the related record (for example, removing a listing or user account) or process an erasure request with the WordPress privacy tools.', 'listdom'),
            esc_html__('Administrators can review, export, or erase Listdom data from WordPress admin by using Tools → Export Personal Data and Tools → Erase Personal Data.', 'listdom'),
            esc_html__('Listdom can load map services (Google Maps or OpenStreetMap) and Google reCAPTCHA when they are enabled. These services may receive visitor IP addresses or other usage data. Please reference their privacy documentation in your site policy.', 'listdom'),
        ];
    }

    private static function policy_paragraphs(array $settings): array
    {
        $raw = '';
        if (isset($settings['policy_content']) && is_string($settings['policy_content'])) $raw = $settings['policy_content'];

        $raw = apply_filters('lsd_privacy_policy_content_raw', $raw, $settings);

        $raw_trimmed = trim($raw);
        if ($raw_trimmed === '') return self::default_policy_paragraphs();

        $paragraphs = preg_split('/\r?\n\s*\r?\n/', $raw);
        if ($paragraphs === false) $paragraphs = [$raw];

        $paragraphs = array_map('trim', $paragraphs);
        $paragraphs = array_filter($paragraphs, 'strlen');

        if (empty($paragraphs)) $paragraphs = self::default_policy_paragraphs();

        $paragraphs = apply_filters('lsd_privacy_policy_paragraphs', $paragraphs, $settings);
        if (!is_array($paragraphs)) $paragraphs = [];

        return $paragraphs;
    }

    private static function replace_privacy_placeholder(string $label, bool $escape = true): string
    {
        $label = trim($label);
        if ($label === '') return '';

        $placeholder = '{{privacy_policy}}';
        $value = $escape ? esc_html($label) : $label;

        if (strpos($value, $placeholder) !== false)
        {
            $link = self::privacy_policy_link();
            $replacement = $link !== '' ? $link : esc_html__('privacy policy', 'listdom');
            return str_replace($placeholder, $replacement, $value);
        }

        return $value;
    }

    public static function privacy_settings(): array
    {
        $settings = LSD_Options::settings();
        $raw_privacy = (array) ($settings['privacy_consent'] ?? []);

        // Establish default values and merge with raw settings
        $privacy = array_merge([
            'enabled' => 1,
            'default_label' => self::default_label(),
            'required_message' => esc_html__('Please confirm that you agree to our privacy policy before continuing.', 'listdom'),
            'policy_content' => self::default_policy_content(),
        ], $raw_privacy);

        // Handle legacy key 'default_enabled' and sanitize final values
        $privacy['enabled'] = $privacy['enabled'] ?? $raw_privacy['default_enabled'] ?? 1;
        $privacy['enabled'] = (int) !empty($privacy['enabled']);
        $privacy['default_label'] = sanitize_text_field((string) $privacy['default_label']);
        $privacy['required_message'] = sanitize_text_field((string) $privacy['required_message']);
        $privacy['policy_content'] = is_string($privacy['policy_content']) ? $privacy['policy_content'] : self::default_policy_content();

        $global_defaults = ['enabled' => $privacy['enabled'], 'label' => $privacy['default_label']];
        $auth = LSD_Options::auth();
        $details = LSD_Options::details_page();
        $addons = LSD_Options::addons('pro');

        // This declarative array defines the data sources for each privacy context.
        $context_definitions = [
            'register' => [
                'primary' => $auth['register'] ?? [],
                'keys' => ['enabled' => 'pc_enabled', 'label' => 'pc_label'],
            ],
            'dashboard' => [
                'primary' => $settings,
                'keys' => ['enabled' => 'submission_pc_enabled', 'label' => 'submission_pc_label'],
            ],
            'contact' => [
                'primary' => (array) ($details['elements']['owner'] ?? []),
                'keys' => ['enabled' => 'pc_enabled', 'label' => 'pc_label'],
            ],
            'profile' => [
                'primary' => $auth['profile'] ?? [],
                'keys' => ['enabled' => 'pc_enabled', 'label' => 'pc_label'],
            ],
            'report' => [
                'primary' => (array) ($details['elements']['abuse'] ?? []),
                'keys' => ['enabled' => 'pc_enabled', 'label' => 'pc_label'],
                'fallbacks' => [
                    ['enabled' => $addons['privacy_consent']['report_enabled'] ?? null, 'label' => $addons['privacy_consent']['report_label'] ?? null],
                ],
            ],
        ];

        $contexts = [];
        foreach ($context_definitions as $name => $def)
        {
            // Build a prioritized list of setting sources for the current context
            $sources = [
                ['enabled' => $def['primary'][$def['keys']['enabled']] ?? null, 'label' => $def['primary'][$def['keys']['label']] ?? null], // Primary source
                ['enabled' => $raw_privacy[$name . '_enabled'] ?? null, 'label' => $raw_privacy[$name . '_label'] ?? null], // Raw privacy fallback
            ];

            // Merge extra fallbacks (if any) and the global default
            $all_sources = array_merge($sources, $def['fallbacks'] ?? [], [$global_defaults]);

            // Find the first valid 'enabled' and 'label' from the prioritized sources
            $enabled = null;
            $label = '';
            foreach ($all_sources as $source)
            {
                if ($enabled === null && isset($source['enabled'])) $enabled = $source['enabled'];
                if ($label === '' && !empty($source['label'])) $label = $source['label'];
            }

            // Assign the sanitized, final values
            $contexts[$name] = [
                'enabled' => (int) !empty($enabled ?? true), // Default to true if no value found
                'label' => sanitize_text_field((string) $label),
            ];
        }

        $privacy['contexts'] = $contexts;
        return $privacy;
    }

    private static function privacy_policy_link(): string
    {
        if (!function_exists('get_privacy_policy_url')) return '';

        $url = get_privacy_policy_url();
        if (!$url) return '';

        $label = apply_filters('lsd_privacy_policy_link_text', esc_html__('Privacy Policy', 'listdom'), $url);
        return '<a href="' . esc_url($url) . '" target="_blank" rel="noopener noreferrer">' . esc_html($label) . '</a>';
    }

    private function emails_match(?string $stored_email, string $email_address): bool
    {
        if (!$stored_email || !$email_address) return false;

        return strcasecmp($stored_email, $email_address) === 0;
    }

    private function consent_matches_email($consent, string $email_address): bool
    {
        if (!is_array($consent)) return true;

        $stored_email = isset($consent['extra']['email']) ? sanitize_email($consent['extra']['email']) : '';
        if ($stored_email === '') return true;

        return strcasecmp($stored_email, $email_address) === 0;
    }
}
