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
}
