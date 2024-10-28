<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom Owner Element Class.
 *
 * @class LSD_Element_Owner
 * @version    1.0.0
 */
class LSD_Element_Owner extends LSD_Element
{
    public $key = 'owner';
    public $label;
    public $layout;
    public $args;

    /**
     * Constructor method
     * @param array $args
     * @param string $layout
     */
    public function __construct(string $layout = 'details', array $args = [])
    {
        // Call the parent constructor
        parent::__construct();

        $this->label = esc_html__('Owner', 'listdom');
        $this->layout = $layout;
        $this->args = $args;
    }

    public function get($post_id = null)
    {
        if (is_null($post_id))
        {
            global $post;
            $post_id = $post->ID;
        }

        // Generate output
        ob_start();
        include lsd_template('elements/owner.php');

        return $this->content(
            ob_get_clean(),
            $this,
            [
                'post_id' => $post_id,
            ]
        );
    }

    public function form($data = [])
    {
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
                    <label for="lsd_elements_' . esc_attr($this->key) . '_display_form">' . esc_html__('Contact Form', 'listdom') . '</label>
                    ' . LSD_Form::switcher([
                        'id' => 'lsd_elements_' . esc_attr($this->key) . '_display_form',
                        'name' => 'lsd[elements][' . esc_attr($this->key) . '][display_form]',
                        'options' => ['1' => esc_html__('Yes', 'listdom'), '0' => esc_html__('No', 'listdom')],
                        'value' => $data['display_form'] ?? 1,
                    ]) . '
                </div>
            </div>
            <div class="lsd-form-row">
                <div class="lsd-col-2">
                    <label for="lsd_elements_' . esc_attr($this->key) . '_display_tel">' . esc_html__('Tel', 'listdom') . '</label>
                    ' . LSD_Form::switcher([
                        'id' => 'lsd_elements_' . esc_attr($this->key) . '_display_tel',
                        'name' => 'lsd[elements][' . esc_attr($this->key) . '][display_tel]',
                        'options' => ['1' => esc_html__('Yes', 'listdom'), '0' => esc_html__('No', 'listdom')],
                        'value' => $data['display_tel'] ?? 1,
                    ]) . '
                </div>
                <div class="lsd-col-2">
                    <label for="lsd_elements_' . esc_attr($this->key) . '_display_email">' . esc_html__('Email', 'listdom') . '</label>
                    ' . LSD_Form::switcher([
                        'id' => 'lsd_elements_' . esc_attr($this->key) . '_display_email',
                        'name' => 'lsd[elements][' . esc_attr($this->key) . '][display_email]',
                        'options' => ['1' => esc_html__('Yes', 'listdom'), '0' => esc_html__('No', 'listdom')],
                        'value' => $data['display_email'] ?? 1,
                    ]) . '
                </div>
                <div class="lsd-col-2">
                    <label for="lsd_elements_' . esc_attr($this->key) . '_display_mobile">' . esc_html__('Mobile', 'listdom') . '</label>
                    ' . LSD_Form::switcher([
                        'id' => 'lsd_elements_' . esc_attr($this->key) . '_display_mobile',
                        'name' => 'lsd[elements][' . esc_attr($this->key) . '][display_mobile]',
                        'options' => ['1' => esc_html__('Yes', 'listdom'), '0' => esc_html__('No', 'listdom')],
                        'value' => $data['display_mobile'] ?? 1,
                    ]) . '
                </div>
                <div class="lsd-col-2">
                    <label for="lsd_elements_' . esc_attr($this->key) . '_display_website">' . esc_html__('Website', 'listdom') . '</label>
                    ' . LSD_Form::switcher([
                        'id' => 'lsd_elements_' . esc_attr($this->key) . '_display_website',
                        'name' => 'lsd[elements][' . esc_attr($this->key) . '][display_website]',
                        'options' => ['1' => esc_html__('Yes', 'listdom'), '0' => esc_html__('No', 'listdom')],
                        'value' => $data['display_website'] ?? 0,
                    ]) . '
                </div>
                <div class="lsd-col-2">
                    <label for="lsd_elements_' . esc_attr($this->key) . '_display_fax">' . esc_html__('Fax', 'listdom') . '</label>
                        ' . LSD_Form::switcher([
                            'id' => 'lsd_elements_' . esc_attr($this->key) . '_display_fax',
                            'name' => 'lsd[elements][' . esc_attr($this->key) . '][display_fax]',
                            'options' => ['1' => esc_html__('Yes', 'listdom'), '0' => esc_html__('No', 'listdom')],
                            'value' => $data['display_fax'] ?? 1,
                        ]) . '
                </div>
            </div>
            ' . $additional . '
        </div>';
    }
}
