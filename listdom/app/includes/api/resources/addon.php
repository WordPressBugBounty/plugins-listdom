<?php

class LSD_API_Resources_Addon extends LSD_API_Resource
{
    public static function all()
    {
        return apply_filters('lsd_api_resource_addon', LSD_Base::addons());
    }
}
