<?php

class LSD_IX_Mapping_Default
{
    public function date($args)
    {
        echo LSD_Form::input([
            'name' => $args['name'],
            'id' => 'lsd_ix_mapping_field_' . $args['key'] . '_default',
        ], 'date');
    }

    public function text($args)
    {
        echo LSD_Form::text([
            'name' => $args['name'],
            'id' => 'lsd_ix_mapping_field_' . $args['key'] . '_default',
        ]);
    }

    public function number($args)
    {
        echo LSD_Form::input([
            'name' => $args['name'],
            'id' => 'lsd_ix_mapping_field_' . $args['key'] . '_default',
        ], 'number');
    }

    public function email($args)
    {
        echo LSD_Form::input([
            'name' => $args['name'],
            'id' => 'lsd_ix_mapping_field_' . $args['key'] . '_default',
        ], 'email');
    }

    public function url($args)
    {
        echo LSD_Form::input([
            'name' => $args['name'],
            'id' => 'lsd_ix_mapping_field_' . $args['key'] . '_default',
        ], 'url');
    }

    public function tel($args)
    {
        echo LSD_Form::input([
            'name' => $args['name'],
            'id' => 'lsd_ix_mapping_field_' . $args['key'] . '_default',
        ], 'tel');
    }

    public function currency($args)
    {
        echo LSD_Form::currency([
            'name' => $args['name'],
            'id' => 'lsd_ix_mapping_field_' . $args['key'] . '_default',
            'show_empty' => true,
        ]);
    }
}
