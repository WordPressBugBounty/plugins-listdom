<?php

use Elementor\Widgets_Manager;

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
