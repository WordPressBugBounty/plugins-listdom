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
     * Initialize a new instance of the WordPress Auto-Update class
     *
     * @param array $args
     */
    function __construct(array $args = [])
    {
        $this->basename = $args['basename'];
        $this->prefix = $args['prefix'] ?? '';
        $this->license_gate = $args['license_gate'] ?? true;

        // Webilia Update Server
        new Update(
            $args['version'],
            $this->basename,
            null,
            LSD_VERSION,
            $args['server'] ?? 'https://api.webilia.com/update'
        );

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
            $valid = LSD_Licensing::isValid($this->basename, $this->prefix);
            if ($valid !== 1)
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
                        '<a href="' . admin_url('admin.php?page=listdom-licenses') . '" target="_parent">' . esc_html__('activate', 'listdom') . '</a>',
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

        $valid = LSD_Licensing::isValid($this->basename, $this->prefix);
        if ($valid === 1) return;

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
                '<a href="' . admin_url('admin.php?page=listdom-licenses') . '" target="_parent">' . esc_html__('activate', 'listdom') . '</a>',
                wp_kses($renew_link, ['a' => ['href' => [], 'target' => []]])
            )
            : esc_html__('A new version is available. You need an active license to update this add-on.', 'listdom');

        printf(
            '<br>%s',
            $message
        );
    }
}
