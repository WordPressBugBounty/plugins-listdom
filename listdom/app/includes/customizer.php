<?php

class LSD_Customizer
{
    protected $fields;

    public function __construct()
    {
        $this->fields = new LSD_Customizer_Fields();
    }

    public function options(): array
    {
        $options = [];

        $options = array_merge_recursive($options, (new LSD_Customizer_General())->options());
        $options = array_merge_recursive($options, (new LSD_Customizer_Buttons())->options());
        $options = array_merge_recursive($options, (new LSD_Customizer_Forms())->options());
        $options = array_merge_recursive($options, (new LSD_Customizer_Single())->options());
        $options = array_merge_recursive($options, (new LSD_Customizer_Skins())->options());
        $options = array_merge_recursive($options, (new LSD_Customizer_Taxonomies())->options());
        $options = array_merge_recursive($options, (new LSD_Customizer_Dashboard())->options());

        return apply_filters('lsd_customizer_options', $options);
    }

    public static function values(string $key = '')
    {
        // Stored Options
        $values = (array) get_option('lsd_customizer', []);

        // Default Options
        if (!count($values)) $values = LSD_Customizer::defaults($key);

        if ($key)
        {
            $paths = explode('.', $key);
            foreach ($paths as $path)
            {
                if (!$path || !isset($values[$path])) break;
                $values = $values[$path];
            }

            // Inherit
            if (is_array($values) && isset($values['inherit']) && $values['inherit'] && isset($values['inherit_from']) && trim($values['inherit_from']))
            {
                return LSD_Customizer::values($values['inherit_from']);
            }

            return $values;
        }

        return $values;
    }

    public static function defaults(string $key = ''): array
    {
        // Options
        $options = (new LSD_Customizer())->options();

        $default = [];
        foreach ($options as $ck => $category)
        {
            $default[$ck] = [];
            foreach ($category['sections'] as $sk => $section)
            {
                $default[$ck][$sk] = [];
                foreach ($section['groups'] as $gk => $group)
                {
                    $default[$ck][$sk][$gk] = [];
                    foreach ($group['divisions'] as $dk => $division)
                    {
                        $default[$ck][$sk][$gk][$dk] = [];
                        if (isset($division['fields']) && is_array($division['fields']))
                        {
                            foreach ($division['fields'] as $fk => $field)
                            {
                                $default[$ck][$sk][$gk][$dk][$fk] = $field['default'] ?? '';
                            }
                        }
                    }
                }
            }
        }

        // Handle key-based retrieval
        if ($key)
        {
            $paths = explode('.', $key);
            foreach ($paths as $path)
            {
                if (!$path || !isset($default[$path])) break;
                $default = $default[$path];
            }

            return $default;
        }

        return $default;
    }

    public static function pairs(): array
    {
        $options = (new LSD_Customizer())->options();

        $values = [];
        $pairs = [];

        foreach ($options as $ck => $category)
        {
            foreach ($category['sections'] as $sk => $section)
            {
                foreach ($section['groups'] as $gk => $group)
                {
                    foreach ($group['divisions'] as $dk => $division)
                    {
                        foreach ($division['fields'] as $fk => $field)
                        {
                            $key = $ck . '.' . $sk . '.' . $gk . '.' . $dk . '.' . $fk;

                            $pairs[$key] = $field;
                            $values[$key] = LSD_Options::customizer($key);
                        }
                    }
                }
            }
        }

        return [$pairs, $values];
    }

    public static function fonts(): string
    {
        // Cached Fonts
        $cached = get_transient('lsd-customizer-fonts');
        if ($cached && is_string($cached)) return $cached;

        // Option & Values Pair
        [$options, $values] = LSD_Customizer::pairs();

        $fonts = [];
        foreach ($options as $key => $field)
        {
            if (!isset($field['type']) || $field['type'] !== 'typography') continue;

            $typography = $values[$key] ?? [];
            if (!isset($typography['family']) || $typography['family'] === 'inherit' || !trim($typography['family'])) continue;

            $weight = $typography['weight'] ?? '';
            if ($weight === 'inherit') $weight = '';

            $font = ucwords(str_replace('-', ' ', $typography['family']));

            if (!isset($fonts[$font])) $fonts[$font] = $weight;
            else $fonts[$font] .= ',' . $weight;
        }

        $fonts_str = '';
        foreach ($fonts as $key => $weights)
        {
            $fonts_str .= $key . ':' . trim($weights, ', ') . '|';
        }

        $fonts_str = trim($fonts_str, '|: ');

        // Set to Transient
        set_transient('lsd-customizer-fonts', $fonts_str, DAY_IN_SECONDS);

        return $fonts_str;
    }
}
