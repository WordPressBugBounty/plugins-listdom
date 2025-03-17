<?php

class LSD_Element_Map extends LSD_Element
{
    public $key = 'map';
    public $label;

    public function __construct()
    {
        parent::__construct();

        $this->label = esc_html__('Map', 'listdom');
    }

    public function get($post_id = null, $args = [])
    {
        if (is_null($post_id))
        {
            global $post;
            $post_id = $post->ID;
        }

        // Generate output
        $HTML = lsd_map([$post_id], [
            'provider' => $args['provider'] ?? LSD_Map_Provider::def(),
            'clustering' => false,
            'id' => $post_id,
            'canvas_class' => 'lsd-map-canvas',
            'mapstyle' => $args['style'] ?? null,
            'gplaces' => $args['gplaces'] ?? 0,
            'onclick' => isset($args['onclick']) && $args['onclick'] === 'none' ? $args['onclick'] : 'infowindow',
            'mapcontrols' => $args['mapcontrols'] ?? [],
            'args' => $args['args'] ?? [],
            'direction' => apply_filters('lsd_map_direction', 0),
            'infowindow' => $args['infowindow'] ?? 1,
            'zoomlevel' => $args['zoomlevel'] ?? 14,
        ]);

        return $this->content(
            $HTML,
            $this,
            [
                'post_id' => $post_id,
                'args' => $args,
            ]
        );
    }

    protected function general_settings(array $data): string
    {
        // Positions Array
        $positions = $this->get_map_control_positions();
        $map_control_options = array_merge(['0' => esc_html__('Disabled', 'listdom')], $positions);

        return '<div>
            <label for="lsd_elements_' . esc_attr($this->key) . '_map_provider">' . esc_html__('Map Provider', 'listdom') . '</label>
            ' . LSD_Form::providers([
                'id' => 'lsd_elements_' . esc_attr($this->key) . '_map_provider',
                'name' => 'lsd[elements][' . esc_attr($this->key) . '][map_provider]',
                'value' => $data['map_provider'] ?? LSD_Map_Provider::def(),
                'class' => 'lsd-map-provider-toggle',
                'attributes' => [
                    'data-parent' => '#lsd_options_map',
                ],
            ]) . '
        </div>
        <div>
            <label for="lsd_elements_' . esc_attr($this->key) . '_infowindow">' . esc_html__('Infowindow', 'listdom') . '</label>
            ' . LSD_Form::select([
                'id' => 'lsd_elements_' . esc_attr($this->key) . '_infowindow',
                'name' => 'lsd[elements][' . esc_attr($this->key) . '][infowindow]',
                'options' => ['1' => esc_html__('Enabled', 'listdom'), '0' => esc_html__('Disabled', 'listdom')],
                'value' => $data['infowindow'] ?? 0,
            ]) . '
        </div>
        <div>
            <label for="lsd_elements_' . esc_attr($this->key) . '_zoomlevel">' . esc_html__('Zoom Level', 'listdom') . '</label>
            ' . LSD_Form::select([
                'id' => 'lsd_elements_' . esc_attr($this->key) . '_zoomlevel',
                'name' => 'lsd[elements][' . esc_attr($this->key) . '][zoomlevel]',
                'options' => ['4' => 4, '5' => 5, '6' => 6, '7' => 7, '8' => 8, '9' => 9, '10' => 10, '11' => 11, '12' => 12, '13' => 13, '14' => 14, '15' => 15, '16' => 16],
                'value' => $data['zoomlevel'] ?? 14,
            ]) . '
        </div>
        <div class="lsd-map-provider-dependency lsd-map-provider-dependency-googlemap">
            <label for="lsd_elements_' . esc_attr($this->key) . '_style">' . esc_html__('Style', 'listdom') . '</label>
            ' . LSD_Form::mapstyle([
                'id' => 'lsd_elements_' . esc_attr($this->key) . '_style',
                'name' => 'lsd[elements][' . esc_attr($this->key) . '][style]',
                'value' => $data['style'] ?? '',
            ]) . '
        </div>
        <div class="lsd-map-provider-dependency lsd-map-provider-dependency-googlemap">
            <label for="lsd_elements_' . esc_attr($this->key) . '_gplaces">' . esc_html__('Google Places', 'listdom') . '</label>
            ' . LSD_Form::select([
                'id' => 'lsd_elements_' . esc_attr($this->key) . '_gplaces',
                'name' => 'lsd[elements][' . esc_attr($this->key) . '][gplaces]',
                'options' => ['0' => esc_html__('Disabled', 'listdom'), '1' => esc_html__('Enabled', 'listdom')],
                'value' => $data['gplaces'] ?? 0,
            ]) . '
        </div>
        <div class="lsd-map-provider-dependency lsd-map-provider-dependency-googlemap">
            <label for="lsd_elements_' . esc_attr($this->key) . '_control_zoom">' . esc_html__('Zoom Control', 'listdom') . '</label>
            ' . LSD_Form::select([
                'id' => 'lsd_elements_' . esc_attr($this->key) . '_control_zoom',
                'name' => 'lsd[elements][' . esc_attr($this->key) . '][control_zoom]',
                'options' => $map_control_options,
                'value' => $data['control_zoom'] ?? 'RIGHT_BOTTOM',
            ]) . '
        </div>
        <div class="lsd-map-provider-dependency lsd-map-provider-dependency-googlemap">
            <label for="lsd_elements_' . esc_attr($this->key) . '_control_maptype">' . esc_html__('Map Type', 'listdom') . '</label>
            ' . LSD_Form::select([
                'id' => 'lsd_elements_' . esc_attr($this->key) . '_control_maptype',
                'name' => 'lsd[elements][' . esc_attr($this->key) . '][control_maptype]',
                'options' => $map_control_options,
                'value' => $data['control_maptype'] ?? 'TOP_LEFT',
            ]) . '
        </div>
        <div class="lsd-map-provider-dependency lsd-map-provider-dependency-googlemap">
            <label for="lsd_elements_' . esc_attr($this->key) . '_control_streetview">' . esc_html__('Street View', 'listdom') . '</label>
            ' . LSD_Form::select([
                'id' => 'lsd_elements_' . esc_attr($this->key) . '_control_streetview',
                'name' => 'lsd[elements][' . esc_attr($this->key) . '][control_streetview]',
                'options' => $map_control_options,
                'value' => $data['control_streetview'] ?? 'RIGHT_BOTTOM',
            ]) . '
        </div>
        <div class="lsd-map-provider-dependency lsd-map-provider-dependency-googlemap">
            <label for="lsd_elements_' . esc_attr($this->key) . '_control_scale">' . esc_html__('Scale Control', 'listdom') . '</label>
            ' . LSD_Form::select([
                'id' => 'lsd_elements_' . esc_attr($this->key) . '_control_scale',
                'name' => 'lsd[elements][' . esc_attr($this->key) . '][control_scale]',
                'options' => ['0' => esc_html__('Disabled', 'listdom'), '1' => esc_html__('Enabled', 'listdom')],
                'value' => $data['control_scale'] ?? '0',
            ]) . '
        </div>
        <div class="lsd-map-provider-dependency lsd-map-provider-dependency-googlemap">
            <label for="lsd_elements_' . esc_attr($this->key) . '_control_fullscreen">' . esc_html__('Fullscreen', 'listdom') . '</label>
            ' . LSD_Form::select([
                'id' => 'lsd_elements_' . esc_attr($this->key) . '_control_fullscreen',
                'name' => 'lsd[elements][' . esc_attr($this->key) . '][control_fullscreen]',
                'options' => ['0' => esc_html__('Disabled', 'listdom'), '1' => esc_html__('Enabled', 'listdom')],
                'value' => $data['control_fullscreen'] ?? '1',
            ]) . '
        </div>';
    }
}
