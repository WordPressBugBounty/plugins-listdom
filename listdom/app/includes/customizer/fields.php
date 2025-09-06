<?php

class LSD_Customizer_Fields
{
    public function color(string $title, string $default = '#ffffff'): array
    {
        return [
            'type' => 'color',
            'title' => $title,
            'default' => $default,
        ];
    }

    public function unit_number(string $title, array $default = []): array
    {
        $value = $default['value'] ?? '';
        $unit = isset($default['unit']) && $default['unit'] ? $default['unit'] : 'px';

        return [
            'type' => 'unit_number',
            'title' => $title,
            'default' => ['value' => $value, 'unit' => $unit],
            'units' => isset($default['units']) && is_array($default['units'])
                ? $default['units']
                : ['px', 'em', 'rem', '%'],
        ];
    }
}
