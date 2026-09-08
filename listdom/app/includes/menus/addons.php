<?php

class LSD_Menus_Addons extends LSD_Menus
{
    private ?array $plugins = null;

    public function __construct()
    {
        $this->init();
    }

    public function init(): void
    {
        add_action('wp_ajax_lsd_install_addon_via_connect', [$this, 'installViaConnect']);
    }

    public function output()
    {
        // Generate output
        $this->include_html_file('menus/addons/tpl.php');
    }

    public function get(bool $all = false)
    {
        $addons = get_transient('lsd_addons');
        if (!$addons)
        {
            $JSON = LSD_File::download('https://api.webilia.com/products', [
                'platform' => 'WordPress',
                'solution' => 'Listdom',
                'url' => get_site_url(),
                'types' => 'toolkit,app,addon',
            ]);

            if (!$JSON || !trim($JSON)) return false;

            $response = json_decode($JSON);
            $addons = is_array($response) ? $response : false;

            set_transient('lsd_addons', $addons, WEEK_IN_SECONDS);
        }

        if (is_array($addons)) $addons = apply_filters('lsd_addons_catalog', $addons);

        // Return All Addons
        if ($all) return $addons;

        $installed = [];
        $others = [];

        if ($addons)
        {
            foreach ($addons as $addon)
            {
                $basename = isset($addon->basename) && is_scalar($addon->basename) ? trim((string) $addon->basename) : '';
                $state = $basename === '' ? 'missing' : $this->pluginState($basename);
                $addon->installation_state = $state;

                if ($state !== 'missing') $installed[] = $addon;
                else $others[] = $addon;
            }
        }

        return [$installed, $others];
    }

    public function get_type_descriptions(): array
    {
        return [
            'addon' => esc_html__('Listdom has the most complete set of addons for listing directory industry. Use these addons to achieve what you want.', 'listdom'),
            'toolkit' => esc_html__('Toolkits are used to use Listdom demos without activating several addons.', 'listdom'),
            'app' => esc_html__('Apps are advanced directory solutions designated for those who aim for big targets.', 'listdom'),
        ];
    }

    public function get_type_label(string $type): string
    {
        if ($type === 'toolkit') return esc_html__('Toolkits', 'listdom');
        if ($type === 'app') return esc_html__('Apps', 'listdom');

        return esc_html__('Addons', 'listdom');
    }

    public function get_download_labels(): array
    {
        return [
            'addon' => esc_html__('Get This Addon', 'listdom'),
            'toolkit' => esc_html__('Get This Toolkit', 'listdom'),
            'app' => esc_html__('Get This App', 'listdom'),
        ];
    }

    public function get_badge(string $status): array
    {
        if ($status === 'active') return ['class' => 'lsd-success', 'icon' => 'wbli-checkmark-circle', 'text' => esc_html__('Installed', 'listdom')];
        if ($status === 'inactive') return ['class' => 'lsd-warning', 'icon' => 'wbli-alert', 'text' => esc_html__('Inactive', 'listdom')];

        return ['class' => '', 'icon' => 'wbli-alert', 'text' => esc_html__('Not Installed', 'listdom')];
    }

    public function get_grouped_addons(): array
    {
        [$installed, $others] = $this->get();

        $grouped = [];
        foreach (['installed' => $installed ?? [], 'available' => $others ?? []] as $status => $addons)
        {
            foreach ($addons as $addon)
            {
                $addon->status = $addon->installation_state ?? ($status === 'installed' ? 'active' : 'missing');
                $grouped[$addon->type][] = $addon;
            }
        }

        $ordered = [];
        foreach (['addon', 'app', 'toolkit'] as $type)
        {
            if (isset($grouped[$type])) $ordered[$type] = $grouped[$type];
        }

        foreach ($grouped as $type => $addons)
        {
            if (!isset($ordered[$type])) $ordered[$type] = $addons;
        }

        return $ordered;
    }

    public function installViaConnect(): void
    {
        if (!current_user_can('manage_options') || !current_user_can('activate_plugins'))
        {
            $this->response(['success' => 0, 'message' => esc_html__('You are not allowed to activate Listdom products.', 'listdom')]);
        }
        $nonce = isset($_POST['_ajax_nonce']) ? sanitize_text_field(wp_unslash($_POST['_ajax_nonce'])) : '';
        if (!wp_verify_nonce($nonce, 'lsd_install_addon_via_connect'))
        {
            $this->response(['success' => 0, 'message' => esc_html__('Your installation request has expired. Please reload the page and try again.', 'listdom')]);
        }

        $basename = isset($_POST['basename']) ? sanitize_text_field(wp_unslash($_POST['basename'])) : '';
        $addon = $this->catalogAddon($basename);
        if (!$addon || !$this->isInstallableBasename($basename))
        {
            $this->response(['success' => 0, 'message' => esc_html__('This product is not available for installation.', 'listdom')]);
        }

        $state = $this->pluginState($basename);
        if ($state === 'missing' && !current_user_can('install_plugins'))
        {
            $this->response(['success' => 0, 'message' => esc_html__('You are not allowed to install Listdom products.', 'listdom')]);
        }

        if (!LSD_Webilia_Connect::enabled() || !LSD_Webilia_Connect::isConnected())
        {
            $this->response(['success' => 0, 'message' => esc_html__('Connect this website to Webilia before installing products from the catalog.', 'listdom')]);
        }

        if (!LSD_Webilia_Connect::isAllowed($basename, 'use'))
        {
            $this->response([
                'success' => 0,
                'message' => esc_html__('Your connected Webilia account is not entitled to this product.', 'listdom'),
                'cta_url' => isset($addon->url) && is_scalar($addon->url) ? esc_url_raw((string) $addon->url) : '',
                'cta_label' => esc_html__('View plans and upgrade', 'listdom'),
            ]);
        }

        if ($state === 'active')
        {
            $this->response(['success' => 1, 'reload' => true, 'message' => sprintf(esc_html__('%s is already installed and active.', 'listdom'), $this->addonName($addon))]);
        }

        if ($state === 'missing')
        {
            try { $package = LSD_Webilia_Connect::package($basename); }
            catch (Throwable $e) { $this->response(['success' => 0, 'message' => esc_html($e->getMessage())]); }

            require_once ABSPATH . 'wp-admin/includes/file.php';
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
            require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

            $skin = new WP_Ajax_Upgrader_Skin();
            $upgrader = new Plugin_Upgrader($skin);
            $result = $upgrader->install($package['download_link']);

            if ($result === null)
            {
                $this->response(['success' => 0, 'message' => esc_html__('WordPress needs filesystem access to install this product. Please install it from the Plugins page instead.', 'listdom')]);
            }
            if (is_wp_error($result)) $this->response(['success' => 0, 'message' => wp_kses_post($result->get_error_message())]);
            if (is_wp_error($skin->result)) $this->response(['success' => 0, 'message' => wp_kses_post($skin->result->get_error_message())]);
            if ($result !== true)
            {
                $this->response(['success' => 0, 'message' => esc_html__('WordPress could not install this product. Please try again.', 'listdom')]);
            }

            $installed_basename = (string) $upgrader->plugin_info();
            if ($installed_basename !== $basename)
            {
                $this->response(['success' => 0, 'message' => esc_html__('The downloaded package did not contain the expected Listdom product.', 'listdom')]);
            }
        }

        require_once ABSPATH . 'wp-admin/includes/plugin.php';
        $activation = activate_plugin($basename);
        if (is_wp_error($activation)) $this->response(['success' => 0, 'message' => wp_kses_post($activation->get_error_message())]);

        $message = $state === 'missing'
            ? sprintf(esc_html__('%s was installed and activated with Webilia Connect.', 'listdom'), $this->addonName($addon))
            : sprintf(esc_html__('%s was activated with Webilia Connect.', 'listdom'), $this->addonName($addon));

        $this->response(['success' => 1, 'reload' => true, 'message' => $message]);
    }

    private function pluginState(string $basename): string
    {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
        $active = (bool) apply_filters('lsd_addons_is_installed', is_plugin_active($basename), $basename);
        if ($active) return 'active';

        if ($this->plugins === null) $this->plugins = get_plugins();
        if (!isset($this->plugins[$basename])) return 'missing';

        return 'inactive';
    }

    private function catalogAddon(string $basename): ?object
    {
        foreach ((array) $this->get(true) as $addon)
        {
            if (is_object($addon) && isset($addon->basename) && (string) $addon->basename === $basename) return $addon;
        }

        return null;
    }

    private function isInstallableBasename(string $basename): bool
    {
        return plugin_basename($basename) === $basename && LSD_Webilia_Connect::integration($basename) !== '';
    }

    private function addonName(object $addon): string
    {
        return isset($addon->name) && is_scalar($addon->name) ? (string) $addon->name : esc_html__('This product', 'listdom');
    }

}
