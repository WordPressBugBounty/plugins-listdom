<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom Gallery Element Class.
 *
 * @class LSD_Element_Gallery
 * @version    1.0.0
 */
class LSD_Element_Gallery extends LSD_Element
{
    public $key = 'gallery';
    public $label;

    /**
     * Constructor method
     */
    public function __construct()
    {
        // Call the parent constructor
        parent::__construct();

        $this->label = esc_html__('Image Gallery', 'listdom');
    }

    public function get($params = [], $post_id = null)
    {
        if (is_null($post_id))
        {
            global $post;
            $post_id = $post->ID;
        }

        // Template
        $tpl = 'elements/gallery/lightbox.php';
        if (isset($params['style']) && $params['style'] === 'slider') $tpl = 'elements/gallery/slider.php';

        // Generate output
        ob_start();
        include lsd_template($tpl);

        return $this->content(
            ob_get_clean(),
            $this,
            [
                'post_id' => $post_id,
                'params' => $params,
            ]
        );
    }

    public function get_gallery($post_id, $include_thumbnail = false)
    {
        $gallery = get_post_meta($post_id, 'lsd_gallery', true);
        if (!is_array($gallery)) $gallery = [];

        if ($include_thumbnail)
        {
            $thumbnail_id = get_post_thumbnail_id($post_id);
            if ($thumbnail_id) array_unshift($gallery, $thumbnail_id);
        }

        return $gallery;
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
                    <label for="lsd_elements_' . esc_attr($this->key) . '_lightbox">' . esc_html__('Lightbox', 'listdom') . '</label>
                    ' . LSD_Form::select([
                        'id' => 'lsd_elements_' . esc_attr($this->key) . '_lightbox',
                        'name' => 'lsd[elements][' . esc_attr($this->key) . '][lightbox]',
                        'value' => $data['lightbox'] ?? '1',
                        'options' => [
                            '1' => esc_html__('Enabled', 'listdom'),
                            '0' => esc_html__('Disabled', 'listdom'),
                        ],
                    ]) . '
                </div>
                <div class="lsd-col-2">
                    <label for="lsd_elements_' . esc_attr($this->key) . '_style">' . esc_html__('Style', 'listdom') . '</label>
                    ' . LSD_Form::select([
                        'id' => 'lsd_elements_' . esc_attr($this->key) . '_style',
                        'name' => 'lsd[elements][' . esc_attr($this->key) . '][style]',
                        'value' => $data['style'] ?? 'lightbox',
                        'options' => [
                            'lightbox' => esc_html__('List', 'listdom'),
                            'slider' => esc_html__('Slider', 'listdom'),
                        ],
                    ]) . '
                </div>
                <div class="lsd-col-4">
                    <label for="lsd_elements_' . esc_attr($this->key) . '_thumbnail">' . esc_html__('Add Featured Image', 'listdom') . '</label>
                    ' . LSD_Form::switcher([
                        'id' => 'lsd_elements_' . esc_attr($this->key) . '_thumbnail',
                        'name' => 'lsd[elements][' . esc_attr($this->key) . '][thumbnail]',
                        'value' => $data['thumbnail'] ?? 0,
                    ]) . '
                </div>
            </div>
            ' . $additional . '
        </div>';
    }
}
