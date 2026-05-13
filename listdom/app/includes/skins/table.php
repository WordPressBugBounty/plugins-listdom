<?php

class LSD_Skins_Table extends LSD_Skins
{
    public $skin = 'table';
    public $default_style = 'style1';
    public $table_device_columns = [];
    public $table_columns_union = [];
    public $table_columns_config = [];
    protected $table_columns_display = null;

    public function init()
    {
        add_action('wp_ajax_lsd_table_load_more', [$this, 'filter']);
        add_action('wp_ajax_nopriv_lsd_table_load_more', [$this, 'filter']);

        add_action('wp_ajax_lsd_table_sort', [$this, 'filter']);
        add_action('wp_ajax_nopriv_lsd_table_sort', [$this, 'filter']);
    }

    public function after_start()
    {
        $this->style = $this->default_style;

        $fields = new LSD_Fields();
        $fields_data = $fields->get();

        $device_settings = $this->get_table_device_settings($fields_data);

        $this->table_device_columns = $device_settings;
        $this->table_columns_union = $this->build_table_columns_union($device_settings, $fields_data);
        $this->table_columns_config = $this->build_table_columns_config($device_settings, $fields_data);
    }

    public function get_table_columns_display()
    {
        if ($this->table_columns_display !== null) return $this->table_columns_display;

        $fields = new LSD_Fields();
        $default_columns = $fields->get();

        $has_table_settings = is_array($this->table_device_columns) && count($this->table_device_columns);

        $columns_union = $has_table_settings
            ? (is_array($this->table_columns_union) ? $this->table_columns_union : [])
            : $default_columns;

        $desktop_columns = isset($this->table_device_columns['desktop']['columns'])
        && is_array($this->table_device_columns['desktop']['columns'])
            ? $this->table_device_columns['desktop']['columns']
            : [];

        $desktop_enabled = [];
        $desktop_width = [];

        foreach ($columns_union as $key => $column)
        {
            $desktop_enabled[$key] = isset($desktop_columns[$key]['enabled'])
                ? (int) $desktop_columns[$key]['enabled']
                : (int) ($column['enabled'] ?? 0);

            if (isset($desktop_columns[$key]['width']) && $desktop_columns[$key]['width'] !== '')
            {
                $desktop_width[$key] = (int) $desktop_columns[$key]['width'];
            }
            else if (isset($column['width']) && $column['width'] !== '')
            {
                $desktop_width[$key] = (int) $column['width'];
            }
        }

        $this->table_columns_display = [
            'fields' => $fields,
            'default_columns' => $default_columns,
            'columns_union' => $columns_union,
            'desktop_columns' => $desktop_columns,
            'desktop_enabled' => $desktop_enabled,
            'desktop_width' => $desktop_width,
            'titles' => count($columns_union) ? $fields->titles($columns_union) : [],
        ];

        return $this->table_columns_display;
    }

    public function get_table_columns_config(): array
    {
        if (!count($this->table_columns_config))
        {
            $fields = new LSD_Fields();
            $fields_data = $fields->get();
            $defaults = $this->normalize_device_columns([], $fields_data);

            $default_config = $this->prepare_columns_for_config($defaults, $fields_data);

            $this->table_columns_config = [
                'desktop' => ['inherit' => 0, 'columns' => $default_config],
                'tablet' => ['inherit' => 1, 'columns' => $default_config],
                'mobile' => ['inherit' => 1, 'columns' => $default_config],
            ];
        }

        return $this->table_columns_config;
    }

    protected function get_table_device_settings(array $fields_data): array
    {
        $devices = isset($this->skin_options['devices']) && is_array($this->skin_options['devices'])
            ? $this->skin_options['devices']
            : [];

        $raw_desktop = isset($this->skin_options['columns']) && is_array($this->skin_options['columns'])
            ? $this->skin_options['columns']
            : [];

        $raw_tablet = isset($devices['tablet']['columns']) && is_array($devices['tablet']['columns'])
            ? $devices['tablet']['columns']
            : [];

        $raw_mobile = isset($devices['mobile']['columns']) && is_array($devices['mobile']['columns'])
            ? $devices['mobile']['columns']
            : [];

        $tablet_inherit = isset($devices['tablet']['inherit']) ? (int) $devices['tablet']['inherit'] : 1;
        $mobile_inherit = isset($devices['mobile']['inherit']) ? (int) $devices['mobile']['inherit'] : 1;

        return [
            'desktop' => [
                'inherit' => 0,
                'columns' => $this->normalize_device_columns($raw_desktop, $fields_data),
            ],
            'tablet' => [
                'inherit' => $tablet_inherit,
                'columns' => $this->normalize_device_columns($raw_tablet, $fields_data),
            ],
            'mobile' => [
                'inherit' => $mobile_inherit,
                'columns' => $this->normalize_device_columns($raw_mobile, $fields_data),
            ],
        ];
    }

    public function normalize_device_columns($columns, array $fields_data): array
    {
        $columns = is_array($columns) ? $columns : [];

        $normalized = [];
        foreach ($columns as $key => $column)
        {
            if (!isset($fields_data[$key])) continue;

            $column = is_array($column) ? $column : [];
            $normalized[$key] = array_merge($fields_data[$key], $column);
        }

        $has_custom_columns = count($normalized) > 0;

        foreach ($fields_data as $key => $field)
        {
            if (isset($normalized[$key])) continue;

            // Custom table layouts should not silently re-enable fields that were removed.
            if ($has_custom_columns) $field['enabled'] = 0;

            $normalized[$key] = $field;
        }

        return $normalized;
    }

    protected function build_table_columns_union(array $device_columns, array $fields_data): array
    {
        $device_columns = $this->get_effective_table_device_columns($device_columns);
        $columns_union = [];
        foreach ($device_columns as $data)
        {
            foreach ($data['columns'] as $key => $column)
            {
                if (!isset($fields_data[$key])) continue;

                $enabled = isset($column['enabled'])
                    ? (int) $column['enabled']
                    : (int) ($fields_data[$key]['enabled'] ?? 0);

                if (!$enabled) continue;
                if (!isset($columns_union[$key])) $columns_union[$key] = $column;
            }
        }

        return $columns_union;
    }

    protected function get_effective_table_device_columns(array $device_columns): array
    {
        $desktop = $device_columns['desktop'] ?? ['inherit' => 0, 'columns' => []];
        $desktop_columns = isset($desktop['columns']) && is_array($desktop['columns']) ? $desktop['columns'] : [];

        $effective = [
            'desktop' => [
                'inherit' => 0,
                'columns' => $desktop_columns,
            ],
        ];

        foreach (['tablet', 'mobile'] as $device)
        {
            $data = $device_columns[$device] ?? [];
            $inherit = isset($data['inherit']) ? (int) $data['inherit'] : 1;
            $columns = isset($data['columns']) && is_array($data['columns']) ? $data['columns'] : [];

            $effective[$device] = [
                'inherit' => $inherit,
                'columns' => $inherit ? $desktop_columns : $columns,
            ];
        }

        return $effective;
    }

    protected function build_table_columns_config(array $device_columns, array $fields_data): array
    {
        $config = [];
        foreach ($device_columns as $device => $data)
        {
            $config[$device] = [
                'inherit' => $data['inherit'],
                'columns' => $this->prepare_columns_for_config($data['columns'], $fields_data),
            ];
        }

        return $config;
    }

    protected function prepare_columns_for_config(array $columns, array $fields_data): array
    {
        $prepared = [];
        foreach ($columns as $key => $column)
        {
            if (!isset($fields_data[$key])) continue;

            $prepared[] = [
                'key' => $key,
                'label' => $column['label'] ?? ($fields_data[$key]['label'] ?? ''),
                'enabled' => isset($column['enabled'])
                    ? (int) $column['enabled']
                    : (int) ($fields_data[$key]['enabled'] ?? 0),
                'width' => isset($column['width']) && $column['width'] !== ''
                    ? (int) $column['width']
                    : '',
            ];
        }

        return $prepared;
    }

    public function get_device_configs(array $fields_data, array $titles, array $table = []): array
    {
        $devices = isset($table['devices']) && is_array($table['devices']) ? $table['devices'] : [];

        $normalized = [
            'desktop' => $this->normalize_device_columns($table['columns'] ?? [], $fields_data),
            'tablet'  => $this->normalize_device_columns($devices['tablet']['columns'] ?? [], $fields_data),
            'mobile'  => $this->normalize_device_columns($devices['mobile']['columns'] ?? [], $fields_data),
        ];

        $device_configs = [
            'desktop' => [
                'label' => esc_html__('Desktop', 'listdom'),
                'icon' => 'fas fa-desktop',
                'name_prefix' => 'lsd[display][table][columns]',
                'inherit_value' => 0,
                'columns' => $normalized['desktop'],
            ],
            'tablet' => [
                'label' => esc_html__('Tablet', 'listdom'),
                'icon' => 'fas fa-tablet-alt',
                'name_prefix' => 'lsd[display][table][devices][tablet][columns]',
                'inherit_value' => (int)($devices['tablet']['inherit'] ?? 1),
                'inherit_field' => [
                    'id' => 'lsd_display_options_tablet_inherit',
                    'name' => 'lsd[display][table][devices][tablet][inherit]',
                    'label' => esc_html__('Inherit from Desktop.', 'listdom'),
                    'target' => '#lsd_display_options_table_device_tablet_settings',
                ],
                'settings_id' => 'lsd_display_options_table_device_tablet_settings',
                'columns' => $normalized['tablet'],
            ],
            'mobile' => [
                'label' => esc_html__('Mobile', 'listdom'),
                'icon' => 'fas fa-mobile-alt',
                'name_prefix' => 'lsd[display][table][devices][mobile][columns]',
                'inherit_value' => (int)($devices['mobile']['inherit'] ?? 1),
                'inherit_field' => [
                    'id' => 'lsd_display_options_mobile_inherit',
                    'name' => 'lsd[display][table][devices][mobile][inherit]',
                    'label' => esc_html__('Inherit from Desktop.', 'listdom'),
                    'target' => '#lsd_display_options_table_device_mobile_settings',
                ],
                'settings_id' => 'lsd_display_options_table_device_mobile_settings',
                'columns' => $normalized['mobile'],
            ],
        ];

        if (!$this->isPro()) return ['desktop' => $device_configs['desktop'],];

        return $device_configs;
    }
}
