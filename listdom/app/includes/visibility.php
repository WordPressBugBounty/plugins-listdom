<?php

class LSD_Visibility extends LSD_Base
{
    public function init()
    {
        // Initialize
        if (class_exists(\LSDPACVIS\Boot::class) && LSD_Components::visibility())
            (new \LSDPACVIS\Boot())->init();
    }
}
