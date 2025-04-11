<?php

class LSD_Skins_Slider extends LSD_Skins
{
    public $skin = 'slider';
    public $default_style = 'style1';

    public function init()
    {
    }

    public function query_meta(): array
    {
        return [['key' => '_thumbnail_id']];
    }
}
