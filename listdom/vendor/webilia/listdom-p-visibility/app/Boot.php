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

        // Init Module
        (new Module())->init();

        // Init IX
        (new IX())->init();

        // Init API
        (new API())->init();
    }

    public function actions()
    {
        // Visibility Cronjob
        if (!wp_next_scheduled('lsdaddvis_visibility')) wp_schedule_event(time(), 'twicedaily', 'lsdaddvis_visibility');
        add_action('lsdaddvis_visibility', [$this->addon, 'cron']);

        // Check visits on each listing visit
        add_action('lsd_listing_visited', [$this->addon, 'listing']);
    }
}
