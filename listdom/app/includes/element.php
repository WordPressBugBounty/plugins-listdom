<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom Element Class.
 *
 * @class LSD_Element
 * @version    1.0.0
 */
class LSD_Element extends LSD_Base
{
    public $key;
    public $label;
    protected $settings;

    /**
     * @var LSD_Entity_Listing object
     */
    public $listing;

    /**
     * Constructor method
     */
    public function __construct()
    {
        parent::__construct();

        // Listdom Settings
        $this->settings = LSD_Options::settings();
    }

    public function form($data = [])
    {
        // Third Party Fields
        ob_start();
        do_action('lsd_element_form_options', $this->key, $data);
        $additional = LSD_Kses::form(ob_get_clean());
        $additional_settings = $this->specific_elements_setting($this->key, $data);

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
                        <option value="1" ' . (isset($data['show_title']) && $data['show_title'] == 1 ? 'selected="selected"' : '') . ' data-lsd-show="#lsd_title_alignment_' . esc_attr($this->key) . '_option">' . esc_html__('Yes', 'listdom') . '</option>
                        <option value="0" ' . (isset($data['show_title']) && $data['show_title'] == 0 ? 'selected="selected"' : '') . '>' . esc_html__('No', 'listdom') . '</option>
                    </select>
                </div>
                ' . $additional_settings . '
            </div>
            ' . $additional . '
        </div>';
    }

    /**
     * Add additional setting for specific elements.
     * @param $element
     * @param $data
     * @return mixed
     */
    protected function specific_elements_setting($element, $data)
    {
        $settings = '';

        // Title Alignment
        $bypass_elements = ['share', 'title', 'tags'];
        if (!in_array($element, $bypass_elements))
        {
            $settings .= '<div class="lsd-col-2 lsd-trigger-select-content' . (isset($data['show_title']) && $data['show_title'] != 1 ? ' lsd-util-hide' : '') . '" id="lsd_title_alignment_' . esc_attr($element) . '_option">
                <label for="lsd_elements_' . esc_attr($element) . '_title_align">' . esc_html__('Title Alignment', 'listdom') . '</label>
                <select name="lsd[elements][' . esc_attr($element) . '][title_align]" id="lsd_elements_' . esc_attr($element) . '_title_align">
                    <option value="">' . esc_html__('Default', 'listdom') . '</option>
                    <option value="lsd-text-left" ' . (isset($data['title_align']) && $data['title_align'] === 'lsd-text-left' ? 'selected="selected"' : '') . '>' . esc_html__('Left', 'listdom') . '</option>
                    <option value="lsd-text-center" ' . (isset($data['title_align']) && $data['title_align'] === 'lsd-text-center' ? 'selected="selected"' : '') . '>' . esc_html__('Center', 'listdom') . '</option>
                    <option value="lsd-text-right" ' . (isset($data['title_align']) && $data['title_align'] === 'lsd-text-right' ? 'selected="selected"' : '') . '>' . esc_html__('Right', 'listdom') . '</option>
                </select>
            </div>';
        }

        return $settings;
    }

    /**
     * @param LSD_Entity_Listing $listing
     */
    public function set_listing(LSD_Entity_Listing $listing)
    {
        $this->listing = $listing;
    }

    /**
     * @param $key
     * @return mixed
     */
    public static function instance($key)
    {
        $element = 'LSD_Element_' . ucfirst($key);

        // Element Not Found!
        if (!class_exists($element)) return apply_filters('lsd_addon_elements', false, $key);

        return new $element();
    }

    /**
     * @param string $content
     * @param LSD_Element $element
     * @param array $args
     * @return mixed|void
     */
    final protected function content($content, $element, $args = [])
    {
        // Hook Name
        $hook = strtolower(get_called_class()) . '_content'; // e.g. lsd_element_address_content

        // Filter the Results
        return apply_filters($hook, $content, $element, $args);
    }
}
