<?php

use Webilia\WP\Plugin\Update;

class LSD_Plugin_Update
{
    /**
     * @var string
     */
    private $basename;

    /**
     * @var string
     */
    private $prefix;

    /**
     * @var bool
     */
    private $license_gate;

    /**
     * @var string
     */
    private $version;

    /**
     * @var string
     */
    private $server;

    /**
     * Initialize a new instance of the WordPress Auto-Update class
     *
     * @param array $args
     */
    function __construct(array $args = [])
    {
        $this->basename = $args['basename'];
        $this->prefix = $args['prefix'] ?? '';
        $this->license_gate = $args['license_gate'] ?? true;
        $this->version = $args['version'];
        $this->server = $args['server'] ?? 'https://api.webilia.com/update';

        // A connected site uses one Connect request per product. The API
        // performs the update-capability check as part of that request; the
        // legacy endpoint is contacted only when Connect cannot serve it.
        $connect_update_client = LSD_Webilia_Connect::enabled()
            && LSD_Webilia_Connect::hasConnection()
            && LSD_Webilia_Connect::updateClient($this->basename, $this->version, function () {
                return $this->legacyRemoteInformation();
            });

        if (!$connect_update_client)
        {
            new Update(
                $this->version,
                $this->basename,
                null,
                LSD_VERSION,
                $this->server
            );
        }

        if ($this->license_gate)
        {
            add_filter('upgrader_pre_download', [$this, 'block'], 10, 4);
            add_action('in_plugin_update_message-' . $this->basename, [$this, 'message'], 10, 2);
        }
    }

    public function block($reply, $package, $upgrader, $hook_extra = [])
    {
        if (!$this->license_gate) return $reply;
        if (!$this->basename || !$this->prefix) return $reply;
        if (!isset($upgrader->skin) || !is_object($upgrader->skin)) return $reply;

        if (is_array($hook_extra) && isset($hook_extra['plugin']) && $hook_extra['plugin'] === $this->basename)
        {
            if (!LSD_Licensing::isUpdateAllowed($this->basename, $this->prefix))
            {
                $renew_link = sprintf(
                    '<a href="%s" target="_blank">%s</a>',
                    esc_url(LSD_Base::getWebiliaShopURL()),
                    esc_html__('renew', 'listdom')
                );

                return new WP_Error(
                    'lsd_license_required',
                    sprintf(
                        esc_html__(
                            'An error occurred while updating this add-on: please %1$s or %2$s your license to receive updates.',
                            'listdom'
                        ),
                        '<a href="' . admin_url('admin.php?page=listdom-connect&tab=licenses') . '" target="_parent">' . esc_html__('activate', 'listdom') . '</a>',
                        wp_kses($renew_link, ['a' => ['href' => [], 'target' => []]])
                    )
                );
            }
        }

        return $reply;
    }

    public function message($plugin_data, $response): void
    {
        if (!$this->license_gate) return;
        if (!$this->basename || !$this->prefix || !is_array($plugin_data)) return;

        if (LSD_Licensing::isUpdateAllowed($this->basename, $this->prefix)) return;

        $version = $response->new_version ?? '';
        $renew_link = sprintf(
            '<a href="%s" target="_blank">%s</a>',
            esc_url(LSD_Base::getWebiliaShopURL()),
            esc_html__('renew', 'listdom')
        );

        $message = $version
            ? sprintf(
            /* translators: 1: plugin name, 2: version number, 3: renew link. */
                esc_html__('%1$s %2$s is available. Please %3$s or %4$s your license to receive this update.', 'listdom'),
                esc_html($plugin_data['Name'] ?? esc_html__('This add-on', 'listdom')),
                esc_html($version),
                '<a href="' . admin_url('admin.php?page=listdom-connect&tab=licenses') . '" target="_parent">' . esc_html__('activate', 'listdom') . '</a>',
                wp_kses($renew_link, ['a' => ['href' => [], 'target' => []]])
            )
            : esc_html__('A new version is available. You need an active license to update this add-on.', 'listdom');

        printf(
            '<br>%s',
            $message
        );
    }

    /**
     * Keep the legacy update channel available for a connected site when
     * Connect denies the product or cannot serve it through its update channel.
     * The SDK skips this callback for transient API failures.
     *
     * @return object|false
     */
    private function legacyRemoteInformation()
    {
        $request = wp_remote_post($this->server, [
            'body' => [
                'action' => 'info',
                'basename' => $this->basename,
                'current' => $this->version,
                'core' => LSD_VERSION,
                'code' => '',
                'url' => get_site_url(),
            ],
        ]);

        if (!is_wp_error($request) && wp_remote_retrieve_response_code($request) === 200)
        {
            return json_decode(wp_remote_retrieve_body($request));
        }

        return false;
    }
}
