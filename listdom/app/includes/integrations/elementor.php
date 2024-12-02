<?php
// no direct access
defined('ABSPATH') || die();

use Elementor\Widgets_Manager;

/**
 * Listdom Integrations Elementor Class.
 *
 * @class LSD_Integrations_Elementor
 * @version    1.0.0
 */
class LSD_Integrations_Elementor extends LSD_Integrations
{
    public function init()
    {
        // Register Widgets
        add_action('elementor/widgets/register', [$this, 'register_widgets'], 10);
    }

    /**
     * Register Other Widgets
     * @param Widgets_Manager $widget_manager
     */
    public function register_widgets(Widgets_Manager $widget_manager)
    {
    }
}
