<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom Map Element Class.
 *
 * @class LSD_Element_Map
 * @version    1.0.0
 */
class LSD_Element_Map extends LSD_Element
{
    public $key = 'map';
    public $label;

    /**
     * Constructor method
     */
    public function __construct()
    {
        // Call the parent constructor
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

    public function form($data = [])
    {
        // Positions Array
        $positions = $this->get_map_control_positions();
        $map_control_options = array_merge(['0' => esc_html__('Disabled', 'listdom')], $positions);

        // Third Party Fields
        ob_start();
        do_action('lsd_element_form_options', $this->key, $data);
        $additional = LSD_Kses::form(ob_get_clean());

        return '<div class="lsd-form-row">
            <div class="lsd-col-10 lsd-handler">
                <input type="hidden" name="lsd[elements][' . esc_attr($this->key) . ']" />
                <input type="hidden" name="lsd[elements][' . esc_attr($this->key) . '][enabled]" value="' . esc_attr($data['enabled']) . '" />
                ' . $this->label . '
            </div>
            <div class="lsd-col-2 lsd-actions lsd-details-page-element-toggle-status" id="lsd_actions_' . esc_attr($this->key) . '" data-key="' . esc_attr($this->key) . '">
                <span class="lsd-toggle lsd-mr-2" data-for="#lsd_options_' . esc_attr($this->key) . '" data-all=".lsd-element-options">
                    <i class="lsd-icon fa fa-cog fa-lg"></i>
                </span>
                <strong class="lsd-enabled ' . ($data['enabled'] ? '' : 'lsd-util-hide') . '"><i class="lsd-icon fa fa-check"></i></strong>
                <strong class="lsd-disabled ' . ($data['enabled'] ? 'lsd-util-hide' : '') . '"><i class="lsd-icon fa fa-minus-circle"></i></strong>
            </div>
        </div>
        <div class="lsd-element-options lsd-util-hide" id="lsd_options_' . esc_attr($this->key) . '">
            <div class="lsd-form-row lsd-trigger-select-parent">
                <div class="lsd-col-2">
                    <label for="lsd_elements_' . esc_attr($this->key) . '_show_title">' . esc_html__('Show Title', 'listdom') . '</label>
                    <select class="lsd-trigger-select-options" name="lsd[elements][' . esc_attr($this->key) . '][show_title]" id="lsd_elements_' . esc_attr($this->key) . '_show_title">
                        <option value="1" ' . (isset($data['show_title']) && $data['show_title'] == 1 ? 'selected="selected"' : '') . ' data-lsd-show="#lsd_title_alignment_'. esc_attr($this->key) .'_option">' . esc_html__('Yes', 'listdom') . '</option>
                        <option value="0" ' . (isset($data['show_title']) && $data['show_title'] == 0 ? 'selected="selected"' : '') . '>' . esc_html__('No', 'listdom') . '</option>
                    </select>
                </div>
                <div class="lsd-col-2 lsd-trigger-select-content'. (isset($data['show_title']) && $data['show_title'] != 1 ? ' lsd-util-hide' : '') .'" id="lsd_title_alignment_'. esc_attr($this->key) .'_option">
                    <label for="lsd_elements_' . esc_attr($this->key) . '_title_align">' . esc_html__('Title Alignment', 'listdom') . '</label>
                    <select name="lsd[elements][' . esc_attr($this->key) . '][title_align]" id="lsd_elements_' . esc_attr($this->key) . '_title_align">
                        <option value="">' . esc_html__('Default', 'listdom') . '</option>
                        <option value="lsd-text-left" ' . (isset($data['title_align']) && $data['title_align'] === 'lsd-text-left' ? 'selected="selected"' : '') . '>' . esc_html__('Left', 'listdom') . '</option>
                        <option value="lsd-text-center" ' . (isset($data['title_align']) && $data['title_align'] === 'lsd-text-center' ? 'selected="selected"' : '') . '>' . esc_html__('Center', 'listdom') . '</option>
                        <option value="lsd-text-right" ' . (isset($data['title_align']) && $data['title_align'] === 'lsd-text-right' ? 'selected="selected"' : '') . '>' . esc_html__('Right', 'listdom') . '</option>
                    </select>
                </div>
                <div class="lsd-col-2">
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
                <div class="lsd-col-2 lsd-map-provider-dependency lsd-map-provider-dependency-googlemap">
                    <label for="lsd_elements_' . esc_attr($this->key) . '_style">' . esc_html__('Style', 'listdom') . '</label>
                    ' . LSD_Form::mapstyle([
                        'id' => 'lsd_elements_' . esc_attr($this->key) . '_style',
                        'name' => 'lsd[elements][' . esc_attr($this->key) . '][style]',
                        'value' => $data['style'] ?? '',
                    ]) . '
                </div>
                <div class="lsd-col-2 lsd-map-provider-dependency lsd-map-provider-dependency-googlemap">
                    <label for="lsd_elements_' . esc_attr($this->key) . '_gplaces">' . esc_html__('Google Places', 'listdom') . '</label>
                    ' . LSD_Form::select([
                        'id' => 'lsd_elements_' . esc_attr($this->key) . '_gplaces',
                        'name' => 'lsd[elements][' . esc_attr($this->key) . '][gplaces]',
                        'options' => ['0' => esc_html__('Disabled', 'listdom'), '1' => esc_html__('Enabled', 'listdom')],
                        'value' => $data['gplaces'] ?? 0,
                    ]) . '
                </div>
            </div>
            <div class="lsd-form-row lsd-map-provider-dependency lsd-map-provider-dependency-googlemap">
                <div class="lsd-col-2">
                    <label for="lsd_elements_' . esc_attr($this->key) . '_control_zoom">' . esc_html__('Zoom Control', 'listdom') . '</label>
                    ' . LSD_Form::select([
                        'id' => 'lsd_elements_' . esc_attr($this->key) . '_control_zoom',
                        'name' => 'lsd[elements][' . esc_attr($this->key) . '][control_zoom]',
                        'options' => $map_control_options,
                        'value' => $data['control_zoom'] ?? 'RIGHT_BOTTOM',
                    ]) . '
                </div>
                <div class="lsd-col-2">
                    <label for="lsd_elements_' . esc_attr($this->key) . '_control_maptype">' . esc_html__('Map Type', 'listdom') . '</label>
                    ' . LSD_Form::select([
                        'id' => 'lsd_elements_' . esc_attr($this->key) . '_control_maptype',
                        'name' => 'lsd[elements][' . esc_attr($this->key) . '][control_maptype]',
                        'options' => $map_control_options,
                        'value' => $data['control_maptype'] ?? 'TOP_LEFT',
                    ]) . '
                </div>
                <div class="lsd-col-2">
                    <label for="lsd_elements_' . esc_attr($this->key) . '_control_streetview">' . esc_html__('Street View', 'listdom') . '</label>
                    ' . LSD_Form::select([
                        'id' => 'lsd_elements_' . esc_attr($this->key) . '_control_streetview',
                        'name' => 'lsd[elements][' . esc_attr($this->key) . '][control_streetview]',
                        'options' => $map_control_options,
                        'value' => $data['control_streetview'] ?? 'RIGHT_BOTTOM',
                    ]) . '
                </div>
                <div class="lsd-col-2">
                    <label for="lsd_elements_' . esc_attr($this->key) . '_control_scale">' . esc_html__('Scale Control', 'listdom') . '</label>
                    ' . LSD_Form::select([
                        'id' => 'lsd_elements_' . esc_attr($this->key) . '_control_scale',
                        'name' => 'lsd[elements][' . esc_attr($this->key) . '][control_scale]',
                        'options' => ['0' => esc_html__('Disabled', 'listdom'), '1' => esc_html__('Enabled', 'listdom')],
                        'value' => $data['control_scale'] ?? '0',
                    ]) . '
                </div>
                <div class="lsd-col-2">
                    <label for="lsd_elements_' . esc_attr($this->key) . '_control_fullscreen">' . esc_html__('Fullscreen', 'listdom') . '</label>
                    ' . LSD_Form::select([
                        'id' => 'lsd_elements_' . esc_attr($this->key) . '_control_fullscreen',
                        'name' => 'lsd[elements][' . esc_attr($this->key) . '][control_fullscreen]',
                        'options' => ['0' => esc_html__('Disabled', 'listdom'), '1' => esc_html__('Enabled', 'listdom')],
                        'value' => $data['control_fullscreen'] ?? '1',
                    ]) . '
                </div>
            </div>
            ' . $additional . '
        </div>';
    }
}
