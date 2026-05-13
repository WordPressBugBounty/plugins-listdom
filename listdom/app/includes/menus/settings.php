<?php

class LSD_Menus_Settings extends LSD_Menus
{
    public $subtab;

    public function __construct()
    {
        // Initialize the Menu
        $this->init();
    }

    public function init()
    {
        add_action('wp_ajax_lsd_save_settings', [$this, 'save_settings']);
        add_action('wp_ajax_lsd_save_dashboard', [$this, 'save_dashboard']);
        add_action('wp_ajax_lsd_save_customizer', [$this, 'save_customizer']);
        add_action('wp_ajax_lsd_reset_customizer', [$this, 'reset_customizer']);
        add_action('wp_ajax_lsd_save_details_page', [$this, 'save_details_page']);
        add_action('wp_ajax_lsd_save_addons', [$this, 'save_addons']);
        add_action('wp_ajax_lsd_save_advanced', [$this, 'save_advanced']);
        add_action('wp_ajax_lsd_save_auth', [$this, 'save_auth']);
        add_action('wp_ajax_lsd_save_payments', [$this, 'save_payments']);

        // API
        add_action('wp_ajax_lsd_api_add_token', [$this, 'token_add']);
        add_action('wp_ajax_lsd_api_remove_token', [$this, 'token_remove']);
        add_action('wp_ajax_lsd_save_api', [$this, 'save_api']);

        // AI
        add_action('wp_ajax_lsd_ai_add_profile', [$this, 'ai_add']);
        add_action('wp_ajax_lsd_ai_remove_profile', [$this, 'ai_remove']);
        add_action('wp_ajax_lsd_save_ai', [$this, 'save_ai']);
    }

    public function output()
    {
        // Get the current tab
        $this->tab = isset($_GET['tab']) ? sanitize_text_field(wp_unslash($_GET['tab'])) : 'general';
        $this->subtab = isset($_GET['subtab']) ? sanitize_text_field(wp_unslash($_GET['subtab'])) : $this->get_default_subtab();

        // Generate output
        $this->include_html_file('menus/settings/tpl.php');
    }

    public function get_default_subtab(): string
    {
        if ($this->tab === 'frontend-dashboard') return 'general';
        if ($this->tab === 'auth') return 'authentication';
        if ($this->tab === 'advanced') return 'assets-loading';
        if ($this->tab === 'single-listing') return 'style-elements';
        if ($this->tab === 'payments') return 'engine';

        return 'general';
    }


    public function addons_tab($type = 'addon'): string
    {
        [$addons, $default] = $this->get_addons_default($type);
        if (!count($addons)) return '';

        $subtab = isset($_GET['subtab']) ? sanitize_text_field(wp_unslash($_GET['subtab'])) : $default;

        $output = '';
        foreach ($addons as $key => $addon)
        {
            // No Settings for Addon
            if (!apply_filters('lsd_addons_has_settings_' . $key, true)) continue;

            $name = ucwords(strtolower($addon['name']));

            $output .= '<li class="lsd-nav-tab ' . esc_attr($key === $subtab ? 'lsd-nav-tab-active' : '') . '" data-key="' . esc_attr($key) . '">';
            $output .= esc_html($name);
            $output .= '</li>';
        }

        return $output;
    }

    public function get_addons_default(string $type = 'addon'): array
    {
        $addons = LSD_Base::addons();
        $addons = array_filter($addons, function ($addon) use ($type)
        {
            $addon_type = isset($addon['type']) ? strtolower((string) $addon['type']) : 'addon';

            return $type === 'toolkit' ? $addon_type === 'toolkit' : $addon_type !== 'toolkit';
        });

        if (!count($addons)) return [$addons, ''];

        uasort($addons, function ($a, $b)
        {
            return strcasecmp($a['name'], $b['name']);
        });

        return [$addons, (string) array_key_first($addons)];
    }

    public function check_access(string $action = 'lsd_settings_form', string $capability = 'manage_options')
    {
        $wpnonce = isset($_POST['_wpnonce']) ? sanitize_text_field(wp_unslash($_POST['_wpnonce'])) : '';

        // Check if nonce is not set
        if (!trim($wpnonce)) $this->response(['success' => 0, 'code' => 'NONCE_MISSING']);

        // Verify that the nonce is valid.
        if (!wp_verify_nonce($wpnonce, $action)) $this->response(['success' => 0, 'code' => 'NONCE_IS_INVALID']);

        // Current User is not Permitted
        if (!current_user_can($capability)) $this->response(['success' => 0, 'code' => 'NO_ACCESS']);
    }

    public function save_settings()
    {
        // Check Access
        $this->check_access();

        // Get Listdom options
        $lsd_raw = isset($_POST['lsd']) ? wp_unslash($_POST['lsd']) : [];
        $lsd_raw = is_array($lsd_raw) ? $lsd_raw : [];

        $lsd = LSD_Sanitize::deep($lsd_raw);
        if (isset($lsd_raw['advanced_slug'])) $lsd['advanced_slug'] = LSD_Sanitize::advanced_slug($lsd_raw['advanced_slug']);
        if (isset($lsd_raw['rss_slug']))
        {
            $lsd['rss_slug'] = LSD_Sanitize::slug($lsd_raw['rss_slug']);

            if ($lsd['rss_slug'] === '')
            {
                $defaults = LSD_Options::defaults();
                $lsd['rss_slug'] = $defaults['rss_slug'] ?? 'listdom-listings';
            }
        }

        // Separate social settings
        $social_settings = array_filter($lsd, function ($key)
        {
            return in_array($key, [
                'twitter',
                'pinterest',
                'linkedin',
                'facebook',
                'instagram',
                'whatsapp',
                'youtube',
                'tiktok',
                'telegram',
            ]);
        }, ARRAY_FILTER_USE_KEY);

        // Remove social settings from main settings
        $lsd = array_diff_key($lsd, $social_settings);

        // Save Settings
        LSD_Options::merge('lsd_settings', $lsd);

        // Save social settings
        update_option('lsd_socials', $social_settings);

        // Generate personalized CSS File
        LSD_Personalize::generate();

        // Add WordPress flush rewrite rules in to-do list
        LSD_RewriteRules::todo();

        // Print the response
        $this->response(['success' => 1]);
    }

    public function save_advanced()
    {
        // Check Access
        $this->check_access();

        // Get Listdom options
        $lsd = isset($_POST['lsd']) ? wp_unslash($_POST['lsd']) : [];

        // Preserve the raw CSS, so we can store it without altering the formatting.
        $CSS = isset($lsd['CSS']) ? (string) $lsd['CSS'] : '';

        $lsd = is_array($lsd) ? LSD_Sanitize::deep($lsd) : [];
        $lsd['CSS'] = trim($CSS);

        if (isset($lsd['labels']['custom_locale']))
        {
            $custom_locale = is_string($lsd['labels']['custom_locale']) ? trim($lsd['labels']['custom_locale']) : '';
            $custom_labels = $lsd['labels']['custom'] ?? [];

            if ($custom_locale !== '' && is_array($custom_labels) && count($custom_labels))
            {
                if (!isset($lsd['labels']['locales']) || !is_array($lsd['labels']['locales'])) $lsd['labels']['locales'] = [];
                $lsd['labels']['locales'][$custom_locale] = $custom_labels;
            }

            unset($lsd['labels']['custom_locale'], $lsd['labels']['custom']);
        }
        else if (isset($lsd['labels']['custom']))
        {
            unset($lsd['labels']['custom']);
        }

        // Save Styles
        LSD_Options::merge('lsd_styles', $lsd);

        // Save Settings
        LSD_Options::merge('lsd_settings', $lsd);

        // Generate personalized CSS File
        LSD_Personalize::generate();

        // Add WordPress flush rewrite rules in to do list
        LSD_RewriteRules::todo();

        // Print the response
        $this->response(['success' => 1]);
    }

    public function save_dashboard()
    {
        // Check Access
        $this->check_access();

        // Get Listdom options
        $lsd_raw = isset($_POST['lsd']) && is_array($_POST['lsd']) ? wp_unslash($_POST['lsd']) : [];

        // Take custom menus out before deep sanitize
        $custom_menus_raw = $lsd_raw['dashboard_menu_custom'] ?? [];
        unset($lsd_raw['dashboard_menu_custom']);
        $builtin_menus_raw = $lsd_raw['dashboard_menu_builtin'] ?? [];
        unset($lsd_raw['dashboard_menu_builtin']);

        // Sanitize the rest normally
        $lsd = LSD_Sanitize::deep($lsd_raw);

        if (is_array($custom_menus_raw))
        {
            $custom_menus = [];
            foreach ($custom_menus_raw as $key => $menu)
            {
                if (!is_array($menu)) continue;

                $label = isset($menu['label']) ? sanitize_text_field($menu['label']) : '';
                $slug = isset($menu['slug']) ? sanitize_title($menu['slug']) : '';
                $icon = isset($menu['icon']) ? sanitize_text_field($menu['icon']) : '';
                $content = isset($menu['content']) ? wp_kses_post($menu['content']) : '';
                $enabled = isset($menu['enabled']) && (int) $menu['enabled'] === 0 ? 0 : 1;

                // login_status (required / optional, default required)
                $raw_login_status = $menu['login_status'] ?? 'required';
                $login_status = in_array($raw_login_status, ['required', 'optional'], true)
                    ? $raw_login_status
                    : 'required';

                if ($slug === '')
                {
                    $slug = sanitize_title(is_string($key) ? $key : '');
                    if ($slug === '') continue;
                }

                $custom_menus[$slug] = [
                    'label' => $label,
                    'slug' => $slug,
                    'icon' => $icon,
                    'content' => $content,
                    'login_status' => $login_status,
                    'enabled' => $enabled,
                ];
            }

            $lsd['dashboard_menu_custom'] = $custom_menus;
        }
        else
        {
            $lsd['dashboard_menu_custom'] = [];
        }

        if (is_array($builtin_menus_raw))
        {
            $builtin_menus = [];
            foreach ($builtin_menus_raw as $key => $menu)
            {
                if (!is_array($menu)) continue;

                $label = isset($menu['label']) ? sanitize_text_field($menu['label']) : '';
                $icon = isset($menu['icon']) ? sanitize_text_field($menu['icon']) : '';

                if ($label === '' && $icon === '') continue;

                $builtin_menus[$key] = [
                    'label' => $label,
                    'icon' => $icon,
                ];
            }

            $lsd['dashboard_menu_builtin'] = $builtin_menus;
        }
        else
        {
            $lsd['dashboard_menu_builtin'] = [];
        }

        // Get current Listdom options
        $current = get_option('lsd_settings', []);
        if (!is_array($current)) $current = [];

        // Clear existing custom menus values if present and sanitize shortcode
        if (isset($current['dashboard_menu_custom'])) $current['dashboard_menu_custom'] = [];

        // Merge new options with previous options
        $final = array_merge($current, $lsd);

        // Save final options
        update_option('lsd_settings', $final);

        // Generate personalized CSS File
        LSD_Personalize::generate();

        // Print the response
        $this->response(['success' => 1]);
    }

    public function save_customizer()
    {
        // Check Access
        $this->check_access();

        // Get Listdom options
        $lsd = isset($_POST['lsd']) ? wp_unslash($_POST['lsd']) : [];
        $lsd = is_array($lsd) ? LSD_Sanitize::deep($lsd) : [];

        // Get current options
        $current = get_option('lsd_customizer', []);
        if (!is_array($current)) $current = [];

        // Merge new options with previous options
        $final = array_merge($current, $lsd);

        // Save final options
        update_option('lsd_customizer', $final);

        // Generate personalized CSS File
        LSD_Personalize::generate();

        // Print the response
        $this->response(['success' => 1]);
    }

    public function reset_customizer()
    {
        // Check Access
        $this->check_access();

        // Get Listdom options
        $category = isset($_POST['category']) ? sanitize_text_field(wp_unslash($_POST['category'])) : '';

        // Reset Only a Category
        if ($category)
        {
            // Get current options
            $current = get_option('lsd_customizer', []);
            if (!is_array($current)) $current = [];

            // Category Path
            $paths = explode('.', trim($category, '. '));
            $paths = array_reverse($paths);

            // Default Options
            $default = [];
            $prev = [];

            $p = 1;
            foreach ($paths as $path)
            {
                $default = [];
                $default[$path] = $p === 1 ? LSD_Customizer::defaults($category) : $prev;

                $prev = $default;
                $p++;
            }

            // Merge new options with previous options
            $final = array_replace_recursive($current, $default);

            // Save final options
            update_option('lsd_customizer', $final);
        }
        // Reset All
        else
        {
            update_option('lsd_customizer', LSD_Customizer::defaults());
        }

        // Generate personalized CSS File
        LSD_Personalize::generate();

        // Print the response
        $this->response(['success' => 1]);
    }

    public function save_details_page()
    {
        // Check Access
        $this->check_access();

        // Get Listdom options
        $lsd = isset($_POST['lsd']) ? wp_unslash($_POST['lsd']) : [];
        $lsd = is_array($lsd) ? LSD_Sanitize::deep($lsd) : [];

        $pattern = '';
        if (isset($lsd['elements']) && is_array($lsd['elements']))
        {
            // Element is disabled
            foreach ($lsd['elements'] as $key => $element)
            {
                if (!isset($element['enabled']) || !$element['enabled']) continue;

                $pattern .= '{' . $key . '}';
            }
        }

        // Save single listing pattern
        update_option('lsd_details_page_pattern', trim($pattern));

        // Save Settings
        LSD_Options::merge('lsd_details_page', $lsd);

        // Print the response
        $this->response(['success' => 1]);
    }

    public function save_addons()
    {
        // Check Access
        $this->check_access('lsd_addons_form');

        // Get Listdom options
        $lsd = isset($_POST['addons']) ? wp_unslash($_POST['addons']) : [];
        $lsd = is_array($lsd) ? LSD_Sanitize::deep($lsd) : [];

        // Get current Addons options
        $current = get_option('lsd_addons', []);
        if (!is_array($current)) $current = [];

        // Merge new options with previous options
        $final = array_merge($current, $lsd);

        // Save options
        update_option('lsd_addons', $final);

        // Print the response
        $this->response(['success' => 1]);
    }

    public function token_add()
    {
        // Check Access
        $this->check_access('lsd_api_add_token');

        // Get API Options
        $api = LSD_Options::api();

        // Add New Token
        $api['tokens'][] = ['name' => esc_html__('New Token', 'listdom'), 'key' => LSD_Base::str_random(40)];

        // Save options
        update_option('lsd_api', $api);

        // Print the response
        $this->response(['success' => 1]);
    }

    public function token_remove()
    {
        // Check Access
        $this->check_access('lsd_api_remove_token');

        // Index
        $i = isset($_POST['i']) ? sanitize_text_field(wp_unslash($_POST['i'])) : '';

        // Invalid Index
        if ($i === '') $this->response(['success' => 0, 'code' => 'INVALID_INDEX']);

        // Get API Options
        $api = LSD_Options::api();

        // Remove Token
        unset($api['tokens'][$i]);

        // Save options
        update_option('lsd_api', $api);

        // Print the response
        $this->response(['success' => 1]);
    }

    public function save_api()
    {
        // Check Access
        $this->check_access('lsd_api_form');

        // Get API options
        $lsd = isset($_POST['lsd']) ? wp_unslash($_POST['lsd']) : [];
        $lsd = is_array($lsd) ? LSD_Sanitize::deep($lsd) : [];

        // Save options
        update_option('lsd_api', $lsd);

        // Print the response
        $this->response(['success' => 1]);
    }

    public function ai_add()
    {
        // Check Access
        $this->check_access('lsd_ai_add_profile');

        // Get AI Options
        $ai = LSD_Options::ai();

        // Add New Profile
        $ai['profiles'][] = [
            'id' => LSD_Base::str_random(10),
            'name' => esc_html__('New Profile', 'listdom'),
            'model' => LSD_AI_Models::def(),
            'api_key' => '',
        ];

        // Save options
        update_option('lsd_ai', $ai);

        // Print the response
        $this->response(['success' => 1]);
    }

    public function ai_remove()
    {
        // Check Access
        $this->check_access('lsd_ai_remove_profile');

        // Index
        $i = isset($_POST['i']) ? sanitize_text_field(wp_unslash($_POST['i'])) : '';

        // Invalid Index
        if ($i === '') $this->response(['success' => 0, 'code' => 'INVALID_INDEX']);

        // Get AI Options
        $ai = LSD_Options::ai();

        // Remove Profile
        unset($ai['profiles'][$i]);

        // Save options
        update_option('lsd_ai', $ai);

        // Print the response
        $this->response(['success' => 1]);
    }

    public function save_ai()
    {
        // Check Access
        $this->check_access('lsd_ai_form');

        // Get AI options
        $lsd = isset($_POST['lsd']) ? wp_unslash($_POST['lsd']) : [];
        $lsd = is_array($lsd) ? LSD_Sanitize::deep($lsd) : [];

        // Save options
        update_option('lsd_ai', $lsd);

        // Print the response
        $this->response(['success' => 1]);
    }

    public function save_auth()
    {
        // Check Access
        $this->check_access('lsd_auth_form');

        // Get Auth
        $lsd = isset($_POST['lsd']) ? wp_unslash($_POST['lsd']) : [];
        $lsd = is_array($lsd) ? LSD_Sanitize::deep($lsd) : [];

        // Save Auth
        update_option('lsd_auth', $lsd);

        // Get Settings
        $settings = isset($_POST['settings']) ? wp_unslash($_POST['settings']) : [];
        $settings = is_array($settings) ? LSD_Sanitize::deep($settings) : [];

        // Save Settings
        LSD_Options::merge('lsd_settings', $settings);

        // Print the response
        $this->response(['success' => 1]);
    }

    public function save_payments()
    {
        // Check Access
        $this->check_access('lsd_payments_form');

        // Get Payment Options
        $lsd = $_POST['lsd']['payments'] ?? [];
        $lsd = is_array($lsd) ? wp_unslash($lsd) : [];

        // Keep HTML fields
        $appreciation_message = wp_kses_post($lsd['appreciation_message'] ?? '');
        $agreement = wp_kses_post($lsd['agreement'] ?? '');
        $free_checkout_comment = wp_kses_post($lsd['free_checkout_comment'] ?? '');

        $consent_enabled = $lsd['pc_enabled'] ?? 0;
        $consent_label = $lsd['pc_label'] ?? '';

        $empty = $lsd['empty'] ?? [];
        $empty_logo = isset($empty['logo']) ? absint($empty['logo']) : 0;
        $empty_content = wp_kses_post($empty['content'] ?? '');
        $empty_show_buttons = isset($empty['show_buttons']) && $empty['show_buttons'] ? 1 : 0;

        $invoice = $lsd['invoice'] ?? [];
        $invoice_logo = isset($invoice['logo']) ? absint($invoice['logo']) : 0;
        $invoice_from = isset($invoice['from']) ? sanitize_textarea_field($invoice['from']) : '';
        $invoice_footer = wp_kses_post($invoice['footer'] ?? '');

        $taxes = $lsd['taxes'] ?? [];
        $tax_enabled = isset($taxes['enable']) && $taxes['enable'] ? 1 : 0;
        $tax_label = isset($taxes['label']) ? sanitize_text_field($taxes['label']) : '';
        $tax_rate = isset($taxes['rate']) ? (float) $taxes['rate'] : 0;
        $tax_prices_include_tax = isset($taxes['prices_include_tax']) && $taxes['prices_include_tax'] ? 1 : 0;
        $tax_locations = isset($taxes['locations']) ? (new LSD_Payments_Tax())->sanitize_locations($taxes['locations']) : [];

        unset($lsd['appreciation_message'], $lsd['agreement'], $lsd['free_checkout_comment'], $lsd['invoice'], $lsd['empty'], $lsd['taxes'], $lsd['consent']);

        // Sanitization
        array_walk_recursive($lsd, 'sanitize_text_field');

        $lsd['appreciation_message'] = $appreciation_message;
        $lsd['agreement'] = $agreement;
        $lsd['pc_enabled'] = $consent_enabled ? 1 : 0;
        $lsd['pc_label'] = sanitize_text_field((string) $consent_label);
        $lsd['free_checkout_comment'] = $free_checkout_comment;
        $lsd['invoice'] = [
            'logo' => $invoice_logo,
            'from' => $invoice_from,
            'footer' => $invoice_footer,
        ];
        $lsd['empty'] = [
            'logo' => $empty_logo,
            'content' => $empty_content,
            'show_buttons' => $empty_show_buttons,
        ];
        $lsd['taxes'] = [
            'enable' => $tax_enabled,
            'prices_include_tax' => $tax_prices_include_tax,
            'rate' => $tax_rate,
            'label' => $tax_label,
            'locations' => $tax_locations,
        ];

        // Save Payments
        LSD_Options::merge('lsd_payments', $lsd);

        // Print the response
        $this->response(['success' => 1]);
    }
}
