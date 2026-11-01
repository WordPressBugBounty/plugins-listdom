<?php

/** First-party domain definitions for the shared settings catalog. */
class LSD_Settings_Core
{
    public static function domains(LSD_Settings_Catalog $catalog): array
    {
        $domains = [];
        foreach (['settings', 'customizer', 'details_page', 'auth', 'payments', 'socials', 'styles', 'ai', 'api'] as $name)
        {
            $id = 'core.' . $name;
            $domains[$id] = [
                'owner' => 'Listdom', 'addon' => '', 'available' => true,
                'read' => static function () use ($name): array { return self::snapshot($name); },
                'fields' => [],
            ];
        }
        $definitions = &$domains['core.settings']['fields'];
        $defaults = LSD_Options::defaults();
        foreach ($catalog->definitions() as $key => $field)
        {
            $definitions[$key] = $field + ['sensitivity' => 'configuration', 'write' => 'leaf'];
        }
        $number = ['type' => 'number', 'minimum' => -2147483647, 'maximum' => 2147483647];
        $integer = ['type' => 'integer', 'minimum' => 0, 'maximum' => 2147483647];
        $switch = ['type' => 'integer', 'enum' => [0, 1]];
        $text = ['type' => 'string', 'maxLength' => 512, 'pattern' => '^[^<>]*$'];
        $reference = ['type' => ['integer', 'string'], 'maxLength' => 191, 'pattern' => '^[a-zA-Z0-9_./% -]*$'];
        $strings = ['type' => 'array', 'maxItems' => 50, 'items' => ['type' => 'string', 'maxLength' => 191, 'pattern' => '^[a-zA-Z0-9_./% -]*$']];
        $enum = static function (array $values): array { return ['type' => is_int($values[0]) ? 'integer' : 'string', 'enum' => $values]; };
        $add = static function (string $domain, string $path, array $schema, string $group, string $tab, $fallback = null) use (&$domains): void
        {
            $key = $domain . '.' . $path;
            $domains[$domain]['fields'][$key] = self::field($key, explode('.', $path), $schema, $group, $tab, $fallback);
        };
        foreach ([
            'general' => ['advanced_datetimepicker', 'listing_link_status', 'rss_status', 'rss_include_sensitive_details', 'help_improve_listdom', 'powered_by_message', 'advanced_slug_status', 'price_component_currency', 'price_component_max', 'price_component_after', 'price_component_class'],
            'integration' => ['grecaptcha_status', 'mailchimp_status', 'mailchimp_checked'],
            'submission' => ['submission_pc_enabled', 'submission_category_children_only', 'submission_guest', 'submission_guest_redirect', 'add_listing_page_status'],
            'advanced' => ['compact_menu', 'fontawesome_status', 'blockeditor_status', 'block_admin_subscriber', 'block_admin_contributor', 'block_admin_listdom_author', 'block_admin_listdom_publisher'],
        ] as $group => $keys)
        {
            foreach ($keys as $key) $add('core.settings', $key, $switch, $group, $group === 'submission' ? 'frontend-dashboard' : ($group === 'advanced' ? 'advanced' : 'general'), $defaults[$key] ?? 0);
        }
        foreach (['default_currency', 'currency_position', 'currency_thousand_separator', 'currency_decimal_separator', 'rss_slug', 'listings_slug', 'category_slug', 'location_slug', 'tag_slug', 'feature_slug', 'label_slug', 'advanced_slug', 'dply_main_color', 'dply_main_font', 'address_placeholder', 'datepicker_format', 'no_listings_message', 'submission_pc_label', 'mailchimp_list_id', 'mailchimp_message'] as $key)
            $add('core.settings', $key, $text, 'general', 'general', $defaults[$key] ?? '');
        // Select controls use their owning form's supported values, not arbitrary text.
        foreach ([
            'currency_position' => ['before', 'before_ws', 'after', 'after_ws'],
            'datepicker_format' => ['yyyy-mm-dd', 'dd-mm-yyyy', 'yyyy/mm/dd', 'dd/mm/yyyy', 'yyyy.mm.dd', 'dd.mm.yyyy'],
            'default_currency' => array_values(LSD_Base::get_currencies()),
            'currency_thousand_separator' => array_map('strval', array_keys(LSD_Options::currency_separators())),
            'currency_decimal_separator' => array_map('strval', array_keys(LSD_Options::currency_separators('decimal'))),
        ] as $key => $values)
        {
            $domains['core.settings']['fields']['core.settings.' . $key]['schema'] = $enum($values);
            $domains['core.settings']['fields']['core.settings.' . $key]['default'] = $defaults[$key] ?? $values[0];
        }
        foreach (['submission_page', 'add_listing_page', 'location_archive', 'category_archive', 'tag_archive', 'feature_archive', 'label_archive'] as $key)
            $add('core.settings', $key, $reference, 'pages', strpos($key, 'archive') === false ? 'frontend-dashboard' : 'general', $defaults[$key] ?? '');
        foreach (['map_gps_zl', 'map_gps_zl_current', 'map_backend_zl'] as $key)
            $add('core.settings', $key, ['type' => 'integer', 'minimum' => 0, 'maximum' => 22], 'map', 'general', $defaults[$key]);
        foreach (['map_backend_lt' => 90, 'map_backend_ln' => 180, 'map_shape_fill_opacity' => 1, 'map_shape_stroke_opacity' => 1] as $key => $maximum)
            $add('core.settings', $key, ['type' => 'number', 'minimum' => strpos($key, 'opacity') !== false ? 0 : -$maximum, 'maximum' => $maximum], 'map', 'general', $defaults[$key]);
        $add('core.settings', 'map_shape_stroke_weight', $number, 'map', 'general', $defaults['map_shape_stroke_weight']);
        foreach (['map_shape_fill_color', 'map_shape_stroke_color'] as $key) $add('core.settings', $key, $text, 'map', 'general', $defaults[$key]);
        $add('core.settings', 'map_provider', $enum(array_keys(LSD_Map_Provider::get_providers())), 'map', 'general', $defaults['map_provider']);
        $add('core.settings', 'timepicker_format', $enum([12, 24]), 'general', 'general', 24);
        $add('core.settings', 'dashboard_listing_status', $enum(['', 'publish', 'pending']), 'submission', 'frontend-dashboard', '');
        $add('core.settings', 'dashboard_listing_status_guest', $switch, 'submission', 'frontend-dashboard', 0);
        $add('core.settings', 'submission_guest_registration', $enum(['approval', 'submission', '0']), 'submission', 'frontend-dashboard', 'approval');
        $add('core.settings', 'submission_gallery_method', $enum(['wp', 'uploader']), 'submission', 'frontend-dashboard', 'wp');
        foreach (['listdom-category', 'listdom-location', 'listdom-feature', 'listdom-tag'] as $tax)
            $add('core.settings', 'submission_tax_' . $tax . '_method', $enum($tax === LSD_Base::TAX_TAG ? ['textarea', 'checkboxes', 'dropdown'] : ['checkboxes', 'dropdown']), 'submission', 'frontend-dashboard', $defaults['submission_tax_' . $tax . '_method'] ?? ($tax === LSD_Base::TAX_CATEGORY ? 'dropdown' : ($tax === LSD_Base::TAX_TAG ? 'textarea' : 'checkboxes')));
        $add('core.settings', 'submission_tax_additional_listdom-category_method', $enum(['checkboxes', 'dropdown']), 'submission', 'frontend-dashboard', 'checkboxes');
        foreach (['listdom-category', 'listdom-location', 'listdom-label', 'listdom-tag', 'listdom-feature'] as $tax)
            $add('core.settings', 'submission_term_builder_' . $tax, $enum(['disabled', 'express', 'detailed']), 'submission', 'frontend-dashboard', 'disabled');
        foreach (['email', 'phone', 'website', 'contact_address'] as $key) $add('core.settings', 'submission_contact_fields.' . $key, $switch, 'submission', 'frontend-dashboard', 1);
        foreach (['map', 'pricing', 'work_hours', 'visibility', 'related', 'socials', 'cta', 'remark', 'embed', 'faq'] as $key)
            $add('core.settings', 'components.' . $key, $switch, 'components', 'advanced', 1);
        foreach (['title', 'content', 'excerpt', 'guest_email', 'guest_password', 'remark', 'price', 'price_max', 'price_after', 'price_class', 'email', 'phone', 'website', 'contact_address', 'link', '_gallery', '_embeds', '_faqs', 'listing_category', 'tags', 'listdom-location', 'listdom-feature', 'listdom-label', 'featured_image', 'related_listings', 'address', 'ava'] as $key)
        {
            $always = in_array($key, ['title', 'guest_email', 'guest_password', 'listing_category'], true);
            $add('core.settings', 'submission_fields.' . $key . '.required', $switch, 'submission', 'frontend-dashboard', $always ? 1 : 0);
        }
        $add('core.settings', 'dashboard_menus', ['type' => 'array', 'maxItems' => 50, 'items' => ['type' => 'string', 'maxLength' => 80, 'pattern' => '^[a-zA-Z0-9_-]+$']], 'dashboard', 'frontend-dashboard', []);
        $add('core.settings', 'assets.load', $switch, 'assets', 'advanced', 1);
        $add('core.settings', 'privacy_consent.enabled', $switch, 'privacy', 'general', 1);
        foreach (['default_label', 'required_message'] as $key) $add('core.settings', 'privacy_consent.' . $key, $text, 'privacy', 'general', $defaults['privacy_consent'][$key]);
        foreach (['structured_data', 'public_feed', 'llms_txt', 'robots_txt', 'html_links', 'include_verified_status', 'include_reviews', 'include_booking_summary'] as $key)
            $add('core.settings', 'ai_visibility.' . $key, $switch, 'seo', 'seo', $defaults['ai_visibility'][$key] ?? 0);
        foreach (['googlemaps_api_key', 'mapbox_access_token', 'grecaptcha_sitekey', 'grecaptcha_secretkey', 'mailchimp_api_key', 'google_geocoding_api_key'] as $key)
            self::status($domains['core.settings']['fields'], 'core.settings.' . $key . '_configured', [$key], 'integration', 'general');

        // Paths are reviewed explicitly: filtered defaults cannot advertise an
        // integration's newly added credential or private configuration field.
        $auth = LSD_Options::defaults('auth');
        // Auth forms use switches, page IDs and bounded choices, independently
        // of whether filtered/legacy defaults happen to be strings or integers.
        $page = ['type' => ['integer', 'string'], 'anyOf' => [$integer, ['type' => 'string', 'enum' => ['']]]];
        $auth_paths = [
            'auth' => ['register_tab_label', 'register_link_label', 'login_tab_label', 'login_link_label', 'forgot_password_tab_label', 'forgot_password_link_label'],
            'register' => ['username_label', 'username_placeholder', 'password_label', 'password_placeholder', 'email_label', 'email_placeholder', 'submit_label'],
            'login' => ['username_label', 'password_label', 'username_placeholder', 'password_placeholder', 'remember_label', 'submit_label'],
            'forgot_password' => ['email_label', 'email_placeholder', 'submit_label'],
        ];
        foreach ($auth_paths as $section => $keys)
            foreach ($keys as $key) $add('core.auth', $section . '.' . $key, $text, $section, 'auth', $auth[$section][$key] ?? '');
        foreach ([
            'auth' => ['hide_login_form', 'hide_register_form', 'hide_forgot_password_form', 'login_form', 'register_form', 'forgot_password_form'],
            'register' => ['email_verification', 'pc_enabled', 'login_after_register', 'strong_password', 'contain_uppercase', 'contain_lowercase', 'contain_numbers', 'contain_specials'],
            'profile' => ['pc_enabled'],
        ] as $section => $keys)
            foreach ($keys as $key) $add('core.auth', $section . '.' . $key, $switch, $section, 'auth', $auth[$section][$key] ?? 0);
        foreach ([
            'auth' => ['login_page', 'register_page', 'forgot_password_page'],
            'register' => ['redirect'], 'login' => ['redirect'], 'forgot_password' => ['redirect'],
            'account' => ['redirect'], 'logout' => ['redirect'], 'profile' => ['page'],
        ] as $section => $keys)
            foreach ($keys as $key) $add('core.auth', $section . '.' . $key, $page, $section, 'auth', $auth[$section][$key] ?? '');
        $add('core.auth', 'auth.switch_style', $enum(['both', 'tabs', 'links']), 'auth', 'auth', $auth['auth']['switch_style']);
        $add('core.auth', 'register.password_length', ['type' => 'integer', 'minimum' => 8, 'maximum' => 24], 'register', 'auth', $auth['register']['password_length']);
        // "list" is the built-in legacy default; saved views are numeric IDs.
        $add('core.auth', 'profile.shortcode', ['type' => ['integer', 'string'], 'anyOf' => [$integer, ['type' => 'string', 'enum' => ['', 'list']]]], 'profile', 'auth', $auth['profile']['shortcode']);
        foreach (['login', 'register'] as $section)
        foreach (['subscriber', 'contributor', 'listdom_author', 'listdom_publisher'] as $role)
        {
            $add('core.auth', $section . '.redirect_' . $role, $switch, $section, 'auth', $auth[$section]['redirect_' . $role]);
            $add('core.auth', $section . '.' . $role . '.redirect', $page, $section, 'auth', $auth[$section]['redirect']);
        }
        foreach (['job_title', 'bio', 'profile_image', 'hero_image', 'email', 'phone', 'mobile', 'website', 'fax'] as $key)
            $add('core.auth', 'profile.fields.' . $key, $switch, 'profile', 'auth', 1);
        foreach (['facebook', 'twitter', 'pinterest', 'linkedin', 'instagram', 'whatsapp', 'youtube', 'tiktok', 'telegram'] as $network)
        {
            $add('core.auth', 'profile.fields.social.' . $network, $switch, 'profile', 'auth', 1);
            foreach (['profile', 'archive_share', 'single_share', 'listing'] as $key)
                $add('core.socials', $network . '.' . $key, $switch, 'socials', 'general', LSD_Options::defaults('socials')[$network][$key]);
        }
        foreach (['listdom', 'appreciation_invoice_button', 'pc_enabled', 'taxes.enable', 'taxes.prices_include_tax', 'empty.show_buttons'] as $path)
            $add('core.payments', $path, $switch, 'payments', 'payments', 0);
        foreach (['checkout_page', 'appreciation_page', 'empty.logo', 'invoice.logo'] as $path)
            $add('core.payments', $path, $reference, 'payments', 'payments', '');
        $add('core.payments', 'taxes.rate', ['type' => 'number', 'minimum' => 0, 'maximum' => 100], 'taxes', 'payments', 0);
        $add('core.payments', 'taxes.label', $text, 'taxes', 'payments', 'Tax');
        foreach (['gateways', 'agreement', 'appreciation_message', 'free_checkout_comment'] as $path)
            self::status($domains['core.payments']['fields'], 'core.payments.' . $path . '_configured', [$path], 'payments', 'payments');
        self::status($domains['core.details_page']['fields'], 'core.details_page.builder_configured', ['builder'], 'layout', 'single-listing');
        self::status($domains['core.styles']['fields'], 'core.styles.custom_css_configured', ['CSS'], 'appearance', 'advanced');
        self::status($domains['core.api']['fields'], 'core.api.tokens_configured', ['tokens'], 'integration', 'api');
        self::status($domains['core.ai']['fields'], 'core.ai.profiles_configured', ['profiles'], 'integration', 'ai');
        $add('core.ai', 'semantic_search.enabled', $switch, 'search', 'ai', 0);
        $add('core.ai', 'semantic_search.profile_id', $reference, 'search', 'ai', '');
        $add('core.ai', 'semantic_search.min_score', ['type' => 'number', 'minimum' => 0, 'maximum' => 1], 'search', 'ai', 0.45);
        $add('core.ai', 'semantic_search.attributes', ['type' => 'array', 'maxItems' => 50, 'items' => array_merge($integer, ['minimum' => 1])], 'search', 'ai', []);
        foreach (['working_hours', 'content_generation', 'auto_mapping'] as $module)
            $add('core.ai', 'modules.' . $module . '.access', $strings, 'access', 'ai', ['administrator']);

        $details = LSD_Options::defaults('details_page');
        foreach (['style', 'theme_template', 'displ'] as $key) $add('core.details_page', 'general.' . $key, $text, 'layout', 'single-listing', $details['general'][$key]);
        $add('core.details_page', 'general.comments', $switch, 'layout', 'single-listing', 1);
        foreach (['labels', 'image', 'video', 'title', 'categories', 'price', 'tags', 'content', 'faq', 'gallery', 'embed', 'attributes', 'features', 'contact', 'remark', 'locations', 'address', 'breadcrumb', 'backbutton', 'map', 'availability', 'owner', 'cta', 'share', 'related', 'abuse', 'excerpt'] as $element)
        {
            foreach (['enabled', 'show_title', 'show_icons', 'show_attribute_title', 'show_separator', 'icon', 'infowindow', 'pc_enabled'] as $key)
            {
                if (isset($details['elements'][$element][$key])) $add('core.details_page', 'elements.' . $element . '.' . $key, $switch, $element, 'single-listing', $details['elements'][$element][$key]);
            }
        }
        foreach (['elements.faq.count' => $integer, 'elements.attributes.layout' => $enum(['column', 'row', 'table']),
            'elements.breadcrumb.taxonomy' => $enum([LSD_Base::TAX_CATEGORY, LSD_Base::TAX_LOCATION, LSD_Base::TAX_LABEL, LSD_Base::TAX_TAG, LSD_Base::TAX_FEATURE]),
            'elements.backbutton.target' => $enum(['default', 'page']), 'elements.backbutton.page' => $reference, 'elements.backbutton.fallback_page' => $reference,
            'elements.backbutton.label' => $text, 'elements.cta.alignment' => $enum(['left', 'center', 'right', 'stretch']),
            'elements.cta.target' => $enum(array_merge(['details', 'lightbox', 'custom'], LSD_Base::isPro() ? ['popup'] : [])), 'elements.cta.text' => $text] as $path => $schema)
            $add('core.details_page', $path, $schema, 'layout', 'single-listing', null);
        // The current form/runtime use selected_page; retain the legacy leaf for reads only.
        $add('core.details_page', 'elements.backbutton.selected_page', $reference, 'layout', 'single-listing', '');
        $domains['core.details_page']['fields']['core.details_page.elements.backbutton.page']['write'] = 'read_only';
        $domains['core.details_page']['fields']['core.details_page.elements.backbutton.page']['description'] = __('Legacy back-button page value. Use selected_page for the current destination or fallback_page for the fallback destination.', 'listdom');
        foreach ($domains['core.details_page']['fields'] as &$field)
        {
            $element = $field['path'][0] === 'elements' ? $field['path'][1] : '';
            $component = ['video' => 'embed', 'address' => 'map', 'share' => 'socials', 'availability' => 'work_hours', 'price' => 'pricing'][$element] ?? $element;
            if (in_array($component, ['map', 'socials', 'work_hours', 'pricing', 'related', 'cta', 'remark', 'embed', 'faq'], true))
            {
                $field['available'] = (bool) call_user_func([LSD_Components::class, $component]);
                $field['dependencies'] = [$component . ' component available'];
            }
        }
        unset($field);
        $domains['core.socials']['available'] = (bool) LSD_Components::socials();
        // Consent/description HTML is represented by presence only.
        self::status($domains['core.settings']['fields'], 'core.settings.privacy_policy_configured', ['privacy_consent', 'policy_content'], 'privacy', 'general');
        self::status($domains['core.settings']['fields'], 'core.settings.custom_dashboard_menu_configured', ['dashboard_menu_custom'], 'dashboard', 'frontend-dashboard');
        self::status($domains['core.details_page']['fields'], 'core.details_page.cta_content_configured', ['elements', 'cta', 'content'], 'cta', 'single-listing');
        foreach (['register', 'profile'] as $section)
            $add('core.auth', $section . '.pc_label', $text, $section, 'auth', 'I agree to the {{privacy_policy}}.');
        $add('core.settings', 'submission_max_tags_count', ['type' => ['integer', 'string'], 'anyOf' => [['type' => 'integer', 'minimum' => 0, 'maximum' => 2147483647], ['type' => 'string', 'enum' => ['']]]], 'submission', 'frontend-dashboard', '');
        $add('core.settings', 'visibility_max_visits', ['type' => ['integer', 'string'], 'anyOf' => [['type' => 'integer', 'minimum' => 0, 'maximum' => 2147483647], ['type' => 'string', 'enum' => ['']]]], 'general', 'general', '');
        foreach (['submission_guest', 'submission_guest_registration', 'submission_guest_redirect', 'submission_fields.guest_email.required', 'submission_fields.guest_password.required'] as $path)
        {
            $key = 'core.settings.' . $path;
            $domains['core.settings']['fields'][$key]['available'] = LSD_Base::isPro();
            $domains['core.settings']['fields'][$key]['dependencies'] = ['Listdom Pro guest submission'];
        }
        $domains['core.customizer']['fields'] = self::customizer_fields();
        $customizer = LSD_Options::customizer();
        foreach ($domains['core.customizer']['fields'] as &$field)
        {
            if (!isset($field['customizer'])) continue;
            $section = $catalog->at($customizer, $field['customizer']);
            $field['writeAvailable'] = empty($section['inherit']);
            $field['dependencies'][] = __('Section inheritance disabled for direct visual changes', 'listdom');
        }
        unset($field);
        foreach (['submission_page', 'add_listing_page'] as $path)
            $domains['core.settings']['fields']['core.settings.' . $path]['resource'] = ['postType' => 'page'];
        foreach (['location_archive', 'category_archive', 'tag_archive', 'feature_archive', 'label_archive'] as $path)
            $domains['core.settings']['fields']['core.settings.' . $path]['resource'] = ['postType' => 'listdom-shortcode'];
        foreach (['selected_page', 'fallback_page'] as $path)
            $domains['core.details_page']['fields']['core.details_page.elements.backbutton.' . $path]['resource'] = ['postType' => 'page'];
        foreach ($domains['core.auth']['fields'] as &$field)
        {
            $last = end($field['path']);
            if (in_array($last, ['login_page', 'register_page', 'forgot_password_page', 'redirect', 'page', 'shortcode'], true))
                $field['resource'] = ['postType' => $last === 'shortcode' ? 'listdom-shortcode' : 'page', 'sentinels' => $last === 'shortcode' ? ['list'] : []];
        }
        unset($field);
        foreach (['checkout_page', 'appreciation_page', 'empty.logo', 'invoice.logo'] as $path)
            $domains['core.payments']['fields']['core.payments.' . $path]['resource'] = ['postType' => strpos($path, 'logo') === false ? 'page' : 'attachment'];
        $domains['core.ai']['fields']['core.ai.semantic_search.attributes']['resource'] = ['taxonomy' => LSD_Base::TAX_ATTRIBUTE];
        $domains['core.details_page']['fields']['core.details_page.general.displ']['schema'] = ['type' => ['integer', 'string'], 'enum' => [0, 'admin', 'owner']];
        foreach (['settings', 'customizer', 'details_page', 'auth', 'payments', 'socials', 'ai'] as $name)
            $domains['core.' . $name]['writer'] = self::writer($name);
        return $domains;
    }

    private static function writer(string $name): LSD_Settings_Writer
    {
        return new LSD_Settings_Writer('lsd_' . $name, [], [
            'validate' => static function (array $candidate, array $keys, array $domain) use ($name)
            {
                return self::validate($name, $candidate, $keys, $domain);
            },
            'sanitize' => static function (array $candidate) use ($name): array
            {
                if ($name === 'settings') return LSD_Menus_Settings::sanitize_general($candidate);
                if ($name === 'payments') return LSD_Menus_Settings::sanitize_payments($candidate);
                return LSD_Sanitize::deep($candidate);
            },
            'context' => static function (): array
            {
                return [LSD_Base::isPro(), LSD_Components::get()];
            },
            'after' => static function (array $stored, array $paths = []) use ($name): bool
            {
                if ($name === 'details_page')
                {
                    $cleared = [];
                    foreach ($paths as $path)
                        if (count($path) === 3 && $path[0] === 'elements' && $path[2] === 'enabled' && empty($stored['elements'][$path[1]]['enabled'])) $cleared[] = $path[1];
                    return LSD_Menus_Settings::save_details_pattern(LSD_Base::parse_args($stored, LSD_Options::defaults('details_page')), $cleared);
                }
                if (in_array($name, ['settings', 'customizer'], true)) return LSD_Settings_Service::after_save($name === 'settings');
                return true;
            },
        ]);
    }

    private static function validate(string $name, array $candidate, array $keys, array $domain)
    {
        $components = ['address' => 'map', 'price' => 'pricing', 'availability' => 'work_hours', 'video' => 'embed', 'share' => 'socials'];
        foreach ($keys as $key)
        {
            $field = $domain['fields'][$key];
            $path = $field['path'];
            $component = null;
            if ($name === 'settings')
            {
                if ($path[0] === 'address_autosuggest') $component = 'map';
                if ($path[0] === 'submission_module') $component = $components[$path[1]] ?? $path[1];
                if ($component && array_key_exists($component, LSD_Components::get()))
                {
                    if (empty($candidate['components'][$component]) && !LSD_Settings_Addon::cleared($candidate, $field))
                        return LSD_Settings_Writer::error('listdom_setting_dependency', __('Enable the owning component explicitly in this patch or a separate core settings update.', 'listdom'), ['keys' => [$key, 'core.settings.components.' . $component]]);
                }
                elseif (!$field['available'] && !LSD_Settings_Addon::cleared($candidate, $field)) return LSD_Settings_Writer::error('listdom_setting_dependency', __('This setting requires an available module or license.', 'listdom'), ['keys' => [$key]]);
                if ($path[0] === 'submission_fields' && in_array($path[1], ['title', 'guest_email', 'guest_password', 'listing_category'], true)
                    && empty($candidate['submission_fields'][$path[1]]['required']))
                    return LSD_Settings_Writer::error('listdom_setting_dependency', __('This submission field is always required.', 'listdom'), ['keys' => [$key]]);
            }
            elseif (!$field['available'] && !LSD_Settings_Addon::cleared($candidate, $field))
                return LSD_Settings_Writer::error('listdom_setting_dependency', __('Enable this setting’s dependency in its owning domain first.', 'listdom'), ['keys' => [$key]]);
            if ($name === 'customizer' && end($path) === 'inherit' && !empty((new LSD_Settings_Catalog())->at($candidate, $path)))
            {
                $section = (new LSD_Settings_Catalog())->at($candidate, array_slice($path, 0, -1));
                if (empty($section['inherit_from']))
                    return LSD_Settings_Writer::error('listdom_setting_dependency', __('Configure an inheritance source in the customizer before enabling inheritance.', 'listdom'), ['keys' => [$key]]);
            }
            if ($name === 'customizer' && isset($field['customizer']))
            {
                $section = (new LSD_Settings_Catalog())->at($candidate, $field['customizer']);
                if (!empty($section['inherit']))
                    return LSD_Settings_Writer::error('listdom_setting_dependency', __('Disable inheritance for this section explicitly before configuring its values.', 'listdom'), ['keys' => [$key, 'core.customizer.' . implode('.', $field['customizer']) . '.inherit']]);
            }
        }
        if ($name === 'settings' && in_array('dashboard_menu_sidebar_horizontal_mode', $keys, true) && ($candidate['dashboard_menu_sidebar_status'] ?? '') !== 'horizontal')
            return LSD_Settings_Writer::error('listdom_setting_dependency', __('Horizontal menu mode requires horizontal dashboard navigation.', 'listdom'));
        if ($name === 'details_page')
        {
            if (in_array('core.details_page.general.style', $keys, true) && !array_key_exists($candidate['general']['style'], LSD_Styles::details()))
                return LSD_Settings_Writer::error('listdom_invalid_value', __('Select an available details-page style.', 'listdom'));
            if (in_array('core.details_page.general.theme_template', $keys, true) && $candidate['general']['theme_template'] !== '' && !in_array($candidate['general']['theme_template'], array_values(get_page_templates()), true))
                return LSD_Settings_Writer::error('listdom_invalid_value', __('Select an available theme layout.', 'listdom'));
        }
        if ($name === 'ai')
        {
            foreach ($keys as $key)
                if ($domain['fields'][$key]['path'][0] === 'modules')
                {
                    $roles = (new LSD_Settings_Catalog())->at($candidate, $domain['fields'][$key]['path']);
                    if (array_diff($roles, array_keys(wp_roles()->roles)))
                        return LSD_Settings_Writer::error('listdom_invalid_value', __('Select existing WordPress roles for AI module access.', 'listdom'));
                }
            $semantic_changed = false;
            foreach ($keys as $key)
                if ($domain['fields'][$key]['path'][0] === 'semantic_search') $semantic_changed = true;
            // Dormant references must not prevent disabling search or changing unrelated modules.
            if (!$semantic_changed || empty($candidate['semantic_search']['enabled'])) return true;
            $profile = $candidate['semantic_search']['profile_id'] ?? '';
            if ($profile === '')
                return LSD_Settings_Writer::error('listdom_setting_dependency', __('Enabled semantic search requires an embedding-capable AI profile.', 'listdom'));
            if ($profile !== '')
            {
                $selected = null;
                foreach ($candidate['profiles'] ?? [] as $definition)
                    if (($definition['id'] ?? '') === (string) $profile) $selected = $definition;
                if (!$selected || !LSD_AI_Models::embedding_capable($selected['model'] ?? LSD_AI_Models::def()))
                    return LSD_Settings_Writer::error('listdom_setting_dependency', __('Select an existing embedding-capable AI profile.', 'listdom'));
            }
        }
        return true;
    }

    private static function field(string $key, array $path, array $schema, string $group, string $tab, $default): array
    {
        return ['key' => $key, 'path' => $path, 'label' => ucwords(str_replace(['_', '.'], ' ', implode('.', $path))),
            'description' => __('Reviewed Listdom configuration; write policies and dependencies are checked before saving.', 'listdom'),
            'write' => 'leaf',
            'group' => $group, 'tab' => $tab, 'subtab' => '', 'schema' => $schema, 'default' => $default,
            'available' => true, 'dependencies' => [], 'sensitivity' => 'configuration'];
    }

    private static function status(array &$fields, string $key, array $path, string $group, string $tab): void
    {
        $fields[$key] = self::field($key, $path, ['type' => 'boolean'], $group, $tab, false) + ['projection' => 'configured'];
        $fields[$key]['sensitivity'] = 'status_only';
        $fields[$key]['write'] = 'status_only';
        $fields[$key]['description'] = __('Configuration presence only; the underlying value is never returned.', 'listdom');
    }

    public static function snapshot(string $name): array
    {
        // These are fixed provider domains, never option names supplied by a caller.
        $option = 'lsd_' . $name;
        wp_cache_delete('alloptions', 'options');
        wp_cache_delete('notoptions', 'options');
        wp_cache_delete($option, 'options');
        $physical = $name === 'styles' || $name === 'api' ? null : LSD_Options::option_snapshot($option);
        $raw = $physical ? $physical['stored'] : get_option($option, []);
        $stored = is_array($raw) ? $raw : [];
        // api()/ai() initialize options; diagnostic reads must not invoke them.
        $defaults = $name === 'api' ? ['tokens' => []] : LSD_Options::defaults($name);
        if ($name === 'settings')
        {
            LSD_Options::clear_settings_cache();
            $effective = LSD_Options::settings();
            $effective['ai_visibility'] = LSD_AI_Visibility_Settings::settings();
        }
        elseif ($name === 'customizer') $effective = LSD_Options::customizer();
        // Retain disabled configuration for clear verification; discovery still withholds unavailable fields.
        elseif ($name === 'details_page')
        {
            $effective = LSD_Options::details_page(true);
            // The runtime treats the legacy search target as the canonical default target.
            if (($effective['elements']['backbutton']['target'] ?? '') === 'search') $effective['elements']['backbutton']['target'] = 'default';
        }
        elseif ($name === 'socials') $effective = LSD_Components::socials() ? LSD_Options::socials() : LSD_Base::parse_args((array) get_option($option, []), $defaults);
        else $effective = LSD_Base::parse_args((array) get_option($option, []), $defaults);
        if ($name === 'auth')
        {
            // Match role_based_redirect(): an unset role destination uses the
            // current general destination, while stored presence stays separate.
            foreach (['login', 'register'] as $section)
            foreach (['subscriber', 'contributor', 'listdom_author', 'listdom_publisher'] as $role)
                $effective[$section][$role]['redirect'] = $effective[$section][$role]['redirect'] ?? $effective[$section]['redirect'];
        }
        return ['stored' => $stored, 'effective' => $effective, 'defaults' => $defaults];
    }

    private static function customizer_fields(): array
    {
        $fields = [];
        // Only first-party field kinds are readable; unknown form fields do not
        // become ability fields or acquire a write path through UI registration.
        foreach (['General', 'Buttons', 'Forms', 'Single', 'Skins', 'Taxonomies', 'Dashboard', 'Profile'] as $name)
        {
            $class = 'LSD_Customizer_' . $name;
            foreach ((new $class())->options() as $ck => $category)
            foreach ($category['sections'] ?? [] as $sk => $section)
            foreach ($section['groups'] ?? [] as $gk => $group)
            foreach ($group['divisions'] ?? [] as $dk => $division)
            foreach ($division['fields'] ?? [] as $fk => $definition)
            {
                if (!in_array($fk, ['primary_color', 'primary_font', 'gap', 'bg1', 'bg2', 'bg', 'text', 'border', 'typography', 'padding',
                    'container_bg', 'bg_color', 'text_color', 'icon_color', 'socials_icon_color', 'color', 'field_padding', 'choice_range_padding',
                    'score_color', 'stars_color', 'empty_stars_color', 'count_color', 'input_bg_color', 'placeholder', 'form_bg_color'], true)) continue;
                $schema = self::customizer_schema($definition);
                if (!$schema) continue;
                $path = [$ck, $sk, $gk, $dk, $fk];
                $key = 'core.customizer.' . implode('.', $path);
                $fields[$key] = self::field($key, $path, $schema, $ck, 'customizer', $definition['default'] ?? null);
                $fields[$key]['label'] = sanitize_text_field($definition['title'] ?? $fk);
                $fields[$key]['description'] = mb_substr(sanitize_text_field($definition['description'] ?? __('First-party visual configuration; custom code is excluded.', 'listdom')), 0, 300);
                // Canonical per-field reads resolve customizer inheritance.
                $fields[$key]['customizer'] = [$ck, $sk];
            }
        }
        foreach (['General', 'Buttons', 'Forms', 'Single', 'Skins', 'Taxonomies', 'Dashboard', 'Profile'] as $name)
        {
            $class = 'LSD_Customizer_' . $name;
            foreach ((new $class())->options() as $ck => $category)
            foreach ($category['sections'] ?? [] as $sk => $section)
            {
                if (!isset($section['inherit'])) continue;
                foreach (['inherit', 'inherit_from'] as $property)
                {
                    $key = 'core.customizer.' . $ck . '.' . $sk . '.' . $property;
                    $schema = $property === 'inherit' ? ['type' => 'integer', 'enum' => [0, 1]] : ['type' => 'string', 'maxLength' => 191, 'pattern' => '^[a-zA-Z0-9_.-]*$'];
                    $fields[$key] = self::field($key, [$ck, $sk, $property], $schema, $ck, 'customizer', ($property === 'inherit' ? 0 : ''));
                    if ($property === 'inherit_from') $fields[$key]['write'] = 'read_only';
                }
            }
        }
        ksort($fields);
        return $fields;
    }

    private static function customizer_schema(array $field): ?array
    {
        $type = $field['type'] ?? '';
        $number = ['type' => ['number', 'string'], 'maxLength' => 20, 'pattern' => '^(inherit|-?[0-9]+([.][0-9]+)?|)$', 'minimum' => -10000, 'maximum' => 10000];
        $color = ['type' => 'string', 'maxLength' => 64, 'pattern' => '^(#[a-fA-F0-9]{3,8}|rgba?\([0-9., %]+\)|transparent|inherit|)$'];
        if ($type === 'color') return $color;
        if ($type === 'select')
        {
            $values = array_keys($field['options'] ?? []);
            return $values ? ['type' => ['string', 'integer'], 'enum' => $values] : null;
        }
        $unit = static function (array $units) use ($number): array
        {
            return ['type' => 'object', 'required' => ['value', 'unit'], 'properties' => ['value' => $number, 'unit' => ['type' => 'string', 'enum' => $units]],
                'additionalProperties' => false, 'maxProperties' => 2];
        };
        if ($type === 'unit_number') return $unit($field['units'] ?? ['px', 'em', 'rem', '%']);
        elseif (in_array($type, ['padding', 'border', 'typography'], true))
        {
            $properties = $type === 'typography'
                ? ['family' => ['type' => 'string', 'maxLength' => 100, 'pattern' => '^[a-zA-Z0-9 _, -]*$'], 'weight' => $number,
                    'align' => ['type' => 'string', 'enum' => ['inherit', 'left', 'right', 'center', 'justify', 'initial']],
                    // Defaults/legacy values are scalars; the form saves unit objects.
                    'size' => ['type' => ['object', 'number', 'string'], 'anyOf' => [$unit($field['size_units'] ?? ['px', 'em', 'rem', '%']), $number]],
                    'line_height' => ['type' => ['object', 'number', 'string'], 'anyOf' => [$unit($field['line_height_units'] ?? ['px', 'em', 'rem', '%']), $number]], 'size_unit' => ['type' => 'string', 'enum' => ['px', 'em', 'rem', '%']],
                    'line_height_unit' => ['type' => 'string', 'enum' => ['px', 'em', 'rem', '%']]]
                : array_fill_keys(['top', 'right', 'bottom', 'left'], $number);
            if ($type === 'border') $properties += ['radius' => $number, 'color' => $color, 'style' => ['type' => 'string', 'enum' => ['none', 'solid', 'dotted', 'dashed', 'double', 'inherit']]];
        }
        else return null;
        // Replacing a structured leaf requires all applicable form/default members.
        $required = $type === 'typography' ? ($field['include'] ?? ['family', 'weight', 'align', 'size', 'line_height']) : array_keys($properties);
        $required = array_values(array_unique(array_merge($required, array_keys((array) ($field['default'] ?? [])))));
        return ['type' => 'object', 'required' => $required, 'properties' => $properties, 'additionalProperties' => false, 'maxProperties' => 10];
    }
}
