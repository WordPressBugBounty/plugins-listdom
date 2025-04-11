<?php

class LSD_Skins_Carousel extends LSD_Skins
{
    public $skin = 'carousel';
    public $default_style = 'style1';

    public function init()
    {
    }

    public function query_meta(): array
    {
        return [['key' => '_thumbnail_id']];
    }
}
