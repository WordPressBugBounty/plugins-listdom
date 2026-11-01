<?php

use Elementor\Widgets_Manager;

class LSD_Integrations_Elementor extends LSD_Integrations
{
    /** Elementor's persisted document mode; stale element trees are not frontend content. */
    public static function saved_document_enabled($mode): bool
    {
        return $mode === 'builder' && class_exists(\Elementor\Plugin::class);
    }

    /** These saved widget types belong to Elementor core and Elementor Pro respectively. */
    public static function saved_widget_available(string $type): bool
    {
        if ($type === 'shortcode') return class_exists(\Elementor\Plugin::class);
        if ($type === 'template') return class_exists(\ElementorPro\Plugin::class);
        return false;
    }

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
