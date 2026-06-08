<?php

class LSD_Entity extends LSD_Base
{
    public array $settings;

    public function __construct()
    {
        // Listdom Settings
        $this->settings = LSD_Options::settings();
    }
}
