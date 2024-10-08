<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom Integrations Elementor Class.
 *
 * @class LSD_Integrations_Elementor
 * @version    1.0.0
 */
class LSD_Integrations_Elementor extends LSD_Integrations
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
        // Register Widgets
        add_action('elementor/widgets/register', [$this, 'register_widgets'], 10);
    }

    /**
     * Register Other Widgets
     * @param Elementor\Widgets_Manager $widget_manager
     */
    public function register_widgets($widget_manager)
    {
    }
}
