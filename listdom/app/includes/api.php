<?php

class LSD_API extends LSD_Base
{
    public $namespace = 'listdom/v1';
    public $version = '1';

    /**
     * @var LSD_db
     */
    protected $db;

    public function __construct()
    {
        // DB Library
        $this->db = new LSD_db();
    }

    public function init()
    {
        add_action('rest_api_init', [$this, 'language'], 20);

        $routes = new LSD_API_Routes();
        $routes->init();
    }

    public function language()
    {
        // Requested Language
        $locale = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? null;

        // No Locale
        if (!$locale) return;

        // Switch the Language
        LSD_i18n::set($locale);
    }
}
