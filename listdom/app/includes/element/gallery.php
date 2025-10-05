<?php

class LSD_Element_Gallery extends LSD_Element
{
    public $key = 'gallery';
    public $label;

    public function __construct()
    {
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
        if (isset($params['style']) && $params['style'] === 'linear') $tpl = 'elements/gallery/linear.php';

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

    protected function general_settings(array $data): string
    {
        return '<div>
            <label class="lsd-fields-label-tiny" for="lsd_elements_' . esc_attr($this->key) . '_lightbox">' . esc_html__('Lightbox', 'listdom') . '</label>
            ' . LSD_Form::select([
                'class' => 'lsd-admin-input',
                'id' => 'lsd_elements_' . esc_attr($this->key) . '_lightbox',
                'name' => 'lsd[elements][' . esc_attr($this->key) . '][lightbox]',
                'value' => $data['lightbox'] ?? '1',
                'options' => [
                    '1' => esc_html__('Enabled', 'listdom'),
                    '0' => esc_html__('Disabled', 'listdom'),
                ],
            ]) . '
        </div>
        <div>
            <label class="lsd-fields-label-tiny" for="lsd_elements_' . esc_attr($this->key) . '_style">' . esc_html__('Style', 'listdom') . '</label>
            ' . LSD_Form::select([
                'id' => 'lsd_elements_' . esc_attr($this->key) . '_style',
                'name' => 'lsd[elements][' . esc_attr($this->key) . '][style]',
                'class' => 'lsd-gallery-element-style-toggle lsd-admin-input',
                'value' => $data['style'] ?? 'list',
                'options' => [
                    'list' => esc_html__('List', 'listdom'),
                    'slider' => esc_html__('Slider', 'listdom'),
                    'linear' => esc_html__('Linear Gallery', 'listdom'),
                ],
                'attributes' => [
                    'data-parent' => '.lsd-gallery-element'
                ]
            ]) . '
        </div>
        <div class="lsd-gallery-element">
             <div class="lsd-gallery-element-style-dependency lsd-gallery-element-style-dependency-list lsd-gallery-element-style-dependency-slider">

                <label class="lsd-fields-label-tiny" for="lsd_elements_' . esc_attr($this->key) . '_thumbnail_status">' . esc_html__('Thumbnail Status', 'listdom') . '</label>
                ' . LSD_Form::select([
                    'class' => 'lsd-admin-input',
                    'id' => 'lsd_elements_' . esc_attr($this->key) . '_thumbnail_status',
                    'name' => 'lsd[elements][' . esc_attr($this->key) . '][thumbnail_status]',
                    'value' => $data['thumbnail_status'] ?? 'image',
                    'options' => [
                        'list' => esc_html__('List', 'listdom'),
                        'image' => esc_html__('On the Image', 'listdom'),
                        'disabled' => esc_html__('Disabled', 'listdom'),
                    ],
                ]) . '
            </div>
        </div>
        <div>
            <label class="lsd-fields-label-tiny" for="lsd_elements_' . esc_attr($this->key) . '_thumbnail">' . esc_html__('Add Featured Image', 'listdom') . '</label>
            ' . LSD_Form::switcher([
                'id' => 'lsd_elements_' . esc_attr($this->key) . '_thumbnail',
                'name' => 'lsd[elements][' . esc_attr($this->key) . '][thumbnail]',
                'value' => $data['thumbnail'] ?? 0,
            ]) . '
        </div>

        <div class="lsd-gallery-element">
            <div class="lsd-gallery-element-style-dependency lsd-gallery-element-style-dependency-linear">
                <label class="lsd-fields-label-tiny" for="lsd_elements_' . esc_attr($this->key) . '_image_height">' . esc_html__('Image Height (px)', 'listdom') . '</label>
                <input class="lsd-admin-input" type="number" id="lsd_elements_' . esc_attr($this->key) . '_image_height" name="lsd[elements][' . esc_attr($this->key) . '][image_height]" value="' . esc_attr($data['image_height'] ?? '300') . '" min="0" step="1" required>
            </div>
        </div>
        <div class="lsd-gallery-element">
            <div class="lsd-gallery-element-style-dependency lsd-gallery-element-style-dependency-linear">
                <label class="lsd-fields-label-tiny" for="lsd_elements_' . esc_attr($this->key) . '_image_limit">' . esc_html__('Number of Images', 'listdom') . '</label>
                <input class="lsd-admin-input" type="number" id="lsd_elements_' . esc_attr($this->key) . '_image_limit" name="lsd[elements][' . esc_attr($this->key) . '][image_limit]" value="' . esc_attr($data['image_limit'] ?? '4') . '" min="1" step="1">
            </div>
        </div>
         <div class="lsd-gallery-element">
             <div class="lsd-gallery-element-style-dependency lsd-gallery-element-style-dependency-linear">
                <label class="lsd-fields-label-tiny" for="lsd_elements_' . esc_attr($this->key) . '_image_fit">' . esc_html__('Image Fit', 'listdom') . '</label>
                ' . LSD_Form::select([
                    'class' => 'lsd-admin-input',
                    'id' => 'lsd_elements_' . esc_attr($this->key) . '_image_fit',
                    'name' => 'lsd[elements][' . esc_attr($this->key) . '][image_fit]',
                    'value' => $data['image_fit'] ?? 'cover',
                    'options' => [
                        'none' => esc_html__('None', 'listdom'),
                        'fill' => esc_html__('Fill', 'listdom'),
                        'contain' => esc_html__('Contain', 'listdom'),
                        'cover' => esc_html__('Cover', 'listdom'),
                        'scale-down' => esc_html__('Scale Down', 'listdom'),
                    ],
                ]) . '
            </div>
        </div>';
    }
}
