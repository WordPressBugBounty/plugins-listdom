<?php

class LSD_Components
{
    public static function get(): array
    {
        $settings = LSD_Options::settings();
        $current = $settings['components'] ?? [];

        // Normalize values to boolean
        foreach ($current as $key => $value) $current[$key] = (bool) $value;

        return array_merge([
            'map' => true,
            'pricing' => true,
            'work_hours' => true,
            'visibility' => true,
            'related' => true,
            'socials' => true,
        ], $current);
    }

    public static function map(): bool
    {
        $components = self::get();
        return (bool) ($components['map'] ?? true);
    }

    public static function pricing(): bool
    {
        $components = self::get();
        return (bool) ($components['pricing'] ?? true);
    }

    public static function work_hours(): bool
    {
        $components = self::get();
        return (bool) ($components['work_hours'] ?? true);
    }

    public static function visibility(): bool
    {
        $components = self::get();
        return (bool) ($components['visibility'] ?? true);
    }

    public static function related(): bool
    {
        $components = self::get();
        return (bool) ($components['related'] ?? true);
    }

    public static function socials(): bool
    {
        $components = self::get();
        return (bool) ($components['socials'] ?? true);
    }
}

