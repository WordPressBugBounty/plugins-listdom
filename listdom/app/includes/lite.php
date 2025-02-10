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
    public function init()
    {
        // Disable Data Deletion
        add_filter('lsd_purge_options', '__return_false');

        // Add Action Links
        add_filter('plugin_action_links_' . LSD_BASENAME, function ($links)
        {
            $links[] = '<a href="' . esc_url($this->getUpgradeURL()) . '" target="_blank">' . esc_html__('Upgrade', 'listdom') . '</a>';
            return $links;
        });
    }
}
