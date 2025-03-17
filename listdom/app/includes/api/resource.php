<?php

class LSD_API_Resource extends LSD_API
{
    public static function get($id): array
    {
        return [];
    }

    public static function collection($ids): array
    {
        $items = [];
        foreach ($ids as $id) $items[] = self::get($id);

        return $items;
    }
}
