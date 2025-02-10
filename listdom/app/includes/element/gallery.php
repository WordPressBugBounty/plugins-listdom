<?php
// no direct access
defined('ABSPATH') || die();

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
        <div>
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
        <div>
            <label for="lsd_elements_' . esc_attr($this->key) . '_thumbnail_status">' . esc_html__('Thumbnail Status', 'listdom') . '</label>
            ' . LSD_Form::select([
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
        <div>
            <label for="lsd_elements_' . esc_attr($this->key) . '_thumbnail">' . esc_html__('Add Featured Image', 'listdom') . '</label>
            ' . LSD_Form::switcher([
                'id' => 'lsd_elements_' . esc_attr($this->key) . '_thumbnail',
                'name' => 'lsd[elements][' . esc_attr($this->key) . '][thumbnail]',
                'value' => $data['thumbnail'] ?? 0,
            ]) . '
        </div>';
    }
}
