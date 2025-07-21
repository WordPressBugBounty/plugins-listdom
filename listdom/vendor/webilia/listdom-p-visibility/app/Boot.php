<?php
namespace LSDPACVIS;

class Boot extends Base
{
    protected $addon;
    private static $ran = false;

    public function __construct()
    {
        // Addon
        $this->addon = new Addon();
    }

    public function init()
    {
        // Run Only Once
        if (self::$ran) return;
        self::$ran = true;

        // Register Actions
        $this->actions();

        // Register Filters
        $this->filters();

        // Init Module
        (new Module())->init();

        // Init IX
        (new IX())->init();

        // Init API
        (new API())->init();
    }

    public function actions()
    {
        // General Options
        add_action('lsd_addon_form', [$this->addon, 'form']);

        // Visibility Cronjob
        if (!wp_next_scheduled('lsdaddvis_visibility')) wp_schedule_event(time(), 'twicedaily', 'lsdaddvis_visibility');
        add_action('lsdaddvis_visibility', [$this->addon, 'cron']);

        // Check visits on each listing visit
        add_action('lsd_listing_visited', [$this->addon, 'listing']);
    }

    public function filters()
    {
        // Inform Listdom About Existence of an Addon
        add_filter('lsd_is_addon_installed', '__return_true');

        // Register Addon
        add_filter('lsd_addons', [$this, 'addon']);
    }

    public function addon($addons)
    {
        $key = 'visibility';
        $addons[$key] = [
            'key' => $key,
            'name' => esc_html__('Listing Visibility Addon', 'listdom-visibility'),
            'options' => \LSD_Options::addons($key),
        ];

        return $addons;
    }
}
