<?php

abstract class LSD_Blueprints_Blueprint extends LSD_Base implements LSD_Blueprints_Interface
{
    public function definition(array $options = []): array
    {
        return apply_filters('lsd_blueprint_definition_' . $this->get_id(), $this->build_definition($options), $options, $this);
    }

    abstract protected function build_definition(array $options = []): array;

    protected function search_form(string $title, array $filters = [], array $form = []): array
    {
        return [
            'title' => $title,
            'fields' => $this->search_fields($filters),
            'form' => wp_parse_args($form, [
                'style' => 'default',
                'page' => '',
                'shortcode' => '',
            ]),
            'reuse_existing' => true,
        ];
    }

    protected function search_fields(array $filters): array
    {
        $filter_definitions = [];
        foreach ($filters as $filter)
        {
            $filter_definitions[$filter['key']] = wp_parse_args($filter, [
                'placeholder' => '',
                'default_value' => '',
                'default_values' => '',
                'max_default_value' => '',
                'max_placeholder' => '',
                'min' => '0',
                'max' => '100',
                'increment' => '10',
                'th_separator' => '1',
            ]);
        }

        if (!isset($filter_definitions['s']))
        {
            $filter_definitions = array_merge([
                's' => [
                    'key' => 's',
                    'title' => 'Text Search',
                    'method' => 'text-input',
                    'placeholder' => '',
                    'default_value' => '',
                    'default_values' => '',
                    'max_default_value' => '',
                    'max_placeholder' => '',
                    'min' => '0',
                    'max' => '100',
                    'increment' => '10',
                    'th_separator' => '1',
                ],
            ], $filter_definitions);
        }

        return [
            1 => [
                'type' => 'row',
                'filters' => $filter_definitions,
                'buttons' => '1',
                'clear' => ['status' => 1],
            ],
        ];
    }

    protected function category(string $name, array $extra = []): array
    {
        return wp_parse_args($extra, [
            'name' => $name,
            'taxonomy' => LSD_Base::TAX_CATEGORY,
            'reuse_existing' => true,
        ]);
    }

    protected function location(string $name, array $extra = []): array
    {
        return wp_parse_args($extra, [
            'name' => $name,
            'taxonomy' => LSD_Base::TAX_LOCATION,
            'reuse_existing' => true,
        ]);
    }

    protected function label(string $name, array $extra = []): array
    {
        return wp_parse_args($extra, [
            'name' => $name,
            'taxonomy' => LSD_Base::TAX_LABEL,
            'reuse_existing' => true,
        ]);
    }

    protected function custom_field(string $name, string $field_type, array $extra = []): array
    {
        return wp_parse_args($extra, [
            'name' => $name,
            'field_type' => $field_type,
            'reuse_existing' => true,
            'all_categories' => true,
        ]);
    }

    protected function page(string $title, string $content, array $extra = []): array
    {
        return wp_parse_args($extra, [
            'title' => $title,
            'content' => $content,
            'reuse_existing' => true,
        ]);
    }

    protected function demo_listing(string $title, string $category_name, array $extra = []): array
    {
        return wp_parse_args($extra, [
            'title' => $title,
            'category_name' => $category_name,
            'reuse_existing' => true,
        ]);
    }
}
