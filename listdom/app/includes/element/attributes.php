<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom Attributes Element Class.
 *
 * @class LSD_Element_Attributes
 * @version    1.0.0
 */
class LSD_Element_Attributes extends LSD_Element
{
    public $key = 'attributes';
    public $label;

    /**
     * Constructor method
     */
    public function __construct()
    {
        // Call the parent constructor
        parent::__construct();

        $this->label = esc_html__('Attributes', 'listdom');
    }

    public function get($post_id = null, $show_icons = 0, $show_attribute_title = 1)
    {
        // Disabled in Lite
        if ($this->isLite()) return false;

        if (is_null($post_id))
        {
            global $post;
            $post_id = $post->ID;
        }

        // Generate output
        ob_start();
        include lsd_template('elements/attributes.php');

        return $this->content(
            ob_get_clean(),
            $this,
            [
                'post_id' => $post_id,
                'show_icons' => $show_icons,
                'show_attribute_title' => $show_attribute_title,
            ]
        );
    }

    public function form($data = [])
    {
        // Disabled in Lite
        if ($this->isLite()) return '<div class="lsd-form-row">
            <div class="lsd-col-12 lsd-handler">
                <input type="hidden" name="lsd[elements][' . esc_attr($this->key) . ']" />
                <input type="hidden" name="lsd[elements][' . esc_attr($this->key) . '][enabled]" value="0" />
                ' . $this->missFeatureMessage(esc_html__('Attributes Element', 'listdom')) . '
            </div>
        </div>';

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
                    <label for="lsd_elements_' . esc_attr($this->key) . '_show_icons">' . esc_html__('Show Icons', 'listdom') . '</label>
                    <select name="lsd[elements][' . esc_attr($this->key) . '][show_icons]" id="lsd_elements_' . esc_attr($this->key) . '_show_icons">
                        <option value="0" ' . (isset($data['show_icons']) && $data['show_icons'] == 0 ? 'selected="selected"' : '') . '>' . esc_html__('No', 'listdom') . '</option>
                        <option value="1" ' . (isset($data['show_icons']) && $data['show_icons'] == 1 ? 'selected="selected"' : '') . '>' . esc_html__('Yes', 'listdom') . '</option>
                    </select>
                </div>
                <div class="lsd-col-2">
                    <label for="lsd_elements_' . esc_attr($this->key) . '_show_attribute_title">' . esc_html__('Show Attribute Title', 'listdom') . '</label>
                    <select name="lsd[elements][' . esc_attr($this->key) . '][show_attribute_title]" id="lsd_elements_' . esc_attr($this->key) . '_show_attribute_title">
                        <option value="1" ' . (isset($data['show_attribute_title']) && $data['show_attribute_title'] == 1 ? 'selected="selected"' : '') . '>' . esc_html__('Yes', 'listdom') . '</option>
                        <option value="0" ' . (isset($data['show_attribute_title']) && $data['show_attribute_title'] == 0 ? 'selected="selected"' : '') . '>' . esc_html__('No', 'listdom') . '</option>
                    </select>
                </div>
            </div>
            ' . $additional . '
        </div>';
    }
}
