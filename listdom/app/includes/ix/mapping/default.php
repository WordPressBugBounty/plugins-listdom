<?php

class LSD_IX_Mapping_Default
{
    public function date($args)
    {
        echo LSD_Form::input([
            'name' => $args['name'],
            'id' => 'lsd_ix_mapping_field_' . $args['key'] . '_default',
            'class' => $args['class'] ?? '',
            'value' => $args['value'] ?? '',
        ], 'date');
    }

    public function text($args)
    {
        echo LSD_Form::text([
            'name' => $args['name'],
            'id' => 'lsd_ix_mapping_field_' . $args['key'] . '_default',
            'class' => $args['class'] ?? '',
            'value' => $args['value'] ?? '',
        ]);
    }

    public function number($args)
    {
        echo LSD_Form::input([
            'name' => $args['name'],
            'id' => 'lsd_ix_mapping_field_' . $args['key'] . '_default',
            'class' => $args['class'] ?? '',
            'value' => $args['value'] ?? '',
        ], 'number');
    }

    public function email($args)
    {
        echo LSD_Form::input([
            'name' => $args['name'],
            'id' => 'lsd_ix_mapping_field_' . $args['key'] . '_default',
            'class' => $args['class'] ?? '',
            'value' => $args['value'] ?? '',
        ], 'email');
    }

    public function url($args)
    {
        echo LSD_Form::input([
            'name' => $args['name'],
            'id' => 'lsd_ix_mapping_field_' . $args['key'] . '_default',
            'class' => $args['class'] ?? '',
            'value' => $args['value'] ?? '',
        ], 'url');
    }

    public function tel($args)
    {
        echo LSD_Form::input([
            'name' => $args['name'],
            'id' => 'lsd_ix_mapping_field_' . $args['key'] . '_default',
            'class' => $args['class'] ?? '',
            'value' => $args['value'] ?? '',
        ], 'tel');
    }

    public function currency($args)
    {
        echo LSD_Form::currency([
            'name' => $args['name'],
            'id' => 'lsd_ix_mapping_field_' . $args['key'] . '_default',
            'class' => $args['class'] ?? '',
            'value' => $args['value'] ?? '',
            'show_empty' => true,
        ]);
    }

    public function status($args)
    {
        $options = [];
        foreach (['publish', 'pending', 'draft'] as $status)
        {
            $st = get_post_status_object($status);
            $options[$status] = $st && isset($st->label)
                ? $st->label
                : ucwords(str_replace('_', ' ', $status));
        }

        echo LSD_Form::select([
            'name' => $args['name'],
            'id' => 'lsd_ix_mapping_field_' . $args['key'] . '_default',
            'class' => $args['class'] ?? '',
            'options' => $options,
            'value' => $args['value'] ?? 'publish',
        ]);
    }
}
