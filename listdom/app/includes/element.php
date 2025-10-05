<?php

class LSD_Element extends LSD_Base
{
    public $key;
    public $label;
    protected $settings;

    /**
     * @var LSD_Entity_Listing object
     */
    public $listing;

    protected $pro_needed = false;
    protected $has_title_settings = true;
    protected $inline_title = false;

    public function __construct()
    {
        // Listdom Settings
        $this->settings = LSD_Options::settings();
    }

    public function form($data = [])
    {
        // Disabled in Lite
        if ($this->isLite() && $this->pro()) return '<div class="lsd-form-row">
            <div class="lsd-col-12 lsd-handler">
                <input type="hidden" name="lsd[elements][' . esc_attr($this->key) . ']">
                <input type="hidden" name="lsd[elements][' . esc_attr($this->key) . '][enabled]" value="0">
                ' . $this->missFeatureMessage(esc_html($this->label)) . '
            </div>
        </div>';

        // Third Party Fields
        ob_start();
        do_action('lsd_element_form_options', $this->key, $data);
        $additional = LSD_Kses::form(ob_get_clean());

        // Title Settings
        $title = $this->title_settings($data);

        // General Settings
        $general = $this->general_settings($data);

        // Has General Options
        $has_general_options = trim($general) || trim($additional);

        // Has Options
        $has_options = $has_general_options || trim($title);

        return '<div class="lsd-form-row">
            <div class="lsd-col-10 lsd-handler">
                <input type="hidden" name="lsd[elements][' . esc_attr($this->key) . ']">
                <input type="hidden" name="lsd[elements][' . esc_attr($this->key) . '][enabled]" value="' . esc_attr($data['enabled']) . '">
                ' . $this->label . '
            </div>
            <div class="lsd-col-2 lsd-actions lsd-details-page-element-toggle-status" id="lsd_actions_' . esc_attr($this->key) . '" data-key="' . esc_attr($this->key) . '">
                ' . ($has_options ? '<span class="lsd-toggle lsd-mr-2" data-for="#lsd_options_' . esc_attr($this->key) . '" data-all=".lsd-element-options">
                    <i class="lsd-icon fa fa-cog fa-lg"></i>
                </span>' : '') . '
                <strong class="lsd-enabled ' . ($data['enabled'] ? '' : 'lsd-util-hide') . '"><i class="lsd-icon fa fa-check"></i></strong>
                <strong class="lsd-disabled ' . ($data['enabled'] ? 'lsd-util-hide' : '') . '"><i class="lsd-icon fa fa-minus-circle"></i></strong>
            </div>
        </div>
        <div class="lsd-element-options lsd-util-hide" id="lsd_options_' . esc_attr($this->key) . '">
            ' . ($has_general_options ? '<ul class="lsd-tab-switcher lsd-level-5-menu lsd-sub-tabs lsd-flex lsd-gap-3" id="lsd_element_option_switcher_' . esc_attr($this->key) . '" data-for=".lsd-tab-switcher-element-' . esc_attr($this->key) . '-content">
                ' . (trim($title) ? '<li data-tab="title-' . esc_attr($this->key) . '" class="lsd-sub-tabs-active"><a href="#">' . esc_html__('Title', 'listdom') . '</a></li>' : '') . '
                <li data-tab="options-' . esc_attr($this->key) . '" ' . (trim($title) ? '' : 'class="lsd-sub-tabs-active"') . '><a href="#">' . esc_html__('Options', 'listdom') . '</a></li>
            </ul>' : '') . '
            ' . (trim($title) ? '<div class="lsd-element-option-wrapper ' . ($has_general_options ? 'lsd-tab-switcher-content lsd-tab-switcher-element-' . esc_attr($this->key) . '-content lsd-tab-switcher-content-active' : '') . '" id="lsd-tab-switcher-title-' . esc_attr($this->key) . '-content">
                <div>' . $title . '</div>
            </div>' : '') . '
            ' . ($has_general_options ? '<div class="lsd-element-option-wrapper lsd-tab-switcher-content lsd-tab-switcher-element-' . esc_attr($this->key) . '-content ' . (trim($title) ? '' : 'lsd-tab-switcher-content-active') . '" id="lsd-tab-switcher-options-' . esc_attr($this->key) . '-content">
                <div>
                    ' . $general . '
                    ' . $additional . '
                </div>
            </div>' : '') . '
        </div>';
    }

    private function title_settings(array $data): string
    {
        // No Title Settings
        if (!$this->has_title_settings) return '';

        // Show Title
        $show_title = isset($data['show_title']) && $data['show_title'] == 1;

        // Title Alignment
        $title_alignment = '';

        if (!$this->inline_title)
        {
            $title_alignment .= '<div class="lsd-title-dependent-' . esc_attr($this->key) . '-option ' . ($show_title ? '' : 'lsd-util-hide') . '">
                <label class="lsd-fields-label-tiny" for="lsd_elements_' . esc_attr($this->key) . '_title_align">' . esc_html__('Title Alignment', 'listdom') . '</label>
                <select class="lsd-admin-input" name="lsd[elements][' . esc_attr($this->key) . '][title_align]" id="lsd_elements_' . esc_attr($this->key) . '_title_align">
                    <option value="">' . esc_html__('Default', 'listdom') . '</option>
                    <option value="lsd-text-left" ' . (isset($data['title_align']) && $data['title_align'] === 'lsd-text-left' ? 'selected="selected"' : '') . '>' . esc_html__('Left', 'listdom') . '</option>
                    <option value="lsd-text-center" ' . (isset($data['title_align']) && $data['title_align'] === 'lsd-text-center' ? 'selected="selected"' : '') . '>' . esc_html__('Center', 'listdom') . '</option>
                    <option value="lsd-text-right" ' . (isset($data['title_align']) && $data['title_align'] === 'lsd-text-right' ? 'selected="selected"' : '') . '>' . esc_html__('Right', 'listdom') . '</option>
                </select>
            </div>';
        }

        return '<div>
            <label class="lsd-fields-label-tiny" for="lsd_elements_' . esc_attr($this->key) . '_show_title">' . esc_html__('Show Title', 'listdom') . '</label>
            <select class="lsd-trigger-select-options lsd-admin-input" name="lsd[elements][' . esc_attr($this->key) . '][show_title]" id="lsd_elements_' . esc_attr($this->key) . '_show_title">
                <option value="1" ' . ($show_title ? 'selected="selected"' : '') . ' data-lsd-show=".lsd-title-dependent-' . esc_attr($this->key) . '-option">' . esc_html__('Yes', 'listdom') . '</option>
                <option value="0" ' . (!$show_title ? 'selected="selected"' : '') . ' data-lsd-hide=".lsd-title-dependent-' . esc_attr($this->key) . '-option">' . esc_html__('No', 'listdom') . '</option>
            </select>
        </div>
        ' . $title_alignment . '
        <div class="lsd-title-dependent-' . esc_attr($this->key) . '-option ' . ($show_title ? '' : 'lsd-util-hide') . '">
            <label class="lsd-fields-label-tiny" for="lsd_elements_' . esc_attr($this->key) . '_custom_title">' . esc_html__('Custom Title', 'listdom') . '</label>
            <input class="lsd-admin-input" type="text" name="lsd[elements][' . esc_attr($this->key) . '][custom_title]" id="lsd_elements_' . esc_attr($this->key) . '_custom_title" value="' . (isset($data['custom_title']) && trim($data['custom_title']) ? esc_attr($data['custom_title']) : '') . '" placeholder="' . esc_attr__('Custom Title', 'listdom') . '">
            <p class="lsd-admin-description-tiny lsd-mb-0 lsd-mt-2">' . esc_html__('If a custom title is provided, it will replace the default title.', 'listdom') . '</p>
        </div>';
    }

    protected function general_settings(array $data): string
    {
        return '';
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

    public function pro(): bool
    {
        return $this->pro_needed;
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
