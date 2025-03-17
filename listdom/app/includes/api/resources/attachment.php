<?php

class LSD_API_Resources_Attachment extends LSD_API_Resource
{
    public static function get($id): array
    {
        return apply_filters('lsd_api_resource_attachment', [
            'id' => $id ?: null,
            'url' => $id ? wp_get_attachment_url($id) : null,
        ], $id);
    }

    public static function collection($ids): array
    {
        $items = [];
        foreach ($ids as $id) $items[] = self::get($id);

        return $items;
    }
}
