<?php

class LSD_API_Resources_Embed extends LSD_API_Resource
{
    public static function get($embed): array
    {
        return apply_filters('lsd_api_resource_embed', $embed);
    }

    public static function collection($embeds): array
    {
        $items = [];
        foreach ($embeds as $embed) $items[] = self::get($embed);

        return $items;
    }
}
