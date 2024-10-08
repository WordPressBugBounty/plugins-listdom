<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom Lite Class.
 *
 * @class LSD_Lite
 * @version    1.0.0
 */
class LSD_Lite extends LSD_Base
{
    /**
     * Constructor method
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function init()
    {
        // Disable Data Deletion
        add_filter('lsd_purge_options', [$this, 'purge']);

        // Add Action Links
        add_filter('plugin_action_links_' . LSD_BASENAME, [$this, 'actionLinks']);

        // Add Meta Links
        add_filter('plugin_row_meta', [$this, 'metaLinks'], 10, 2);
    }

    public function purge(): bool
    {
        return false;
    }

    public function actionLinks($links)
    {
        $links[] = '<a href="' . esc_url($this->getUpgradeURL()) . '" target="_blank">' . esc_html__('Upgrade', 'listdom') . '</a>';
        return $links;
    }

    public function metaLinks($links, $file)
    {
        if (strpos($file, LSD_BASENAME) !== false)
        {
            $links = array_merge($links, [
                'documentation' => '<a href="https://webilia.com/docs/doc-category/listdom/" target="_blank"><strong>' . esc_html__('Documentation', 'listdom') . '</strong></a>',
                'support' => '<a href="https://webilia.com/support/" target="_blank">' . esc_html__('Support', 'listdom') . '</a>',
            ]);
        }

        return $links;
    }
}
