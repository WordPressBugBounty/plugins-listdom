<?php

class LSD_Element_Image extends LSD_Element
{
    public $key = 'image';
    public $label;

    public function __construct()
    {
        parent::__construct();

        $this->label = esc_html__('Featured Image', 'listdom');
    }

    public function get($size, $post_id = null, string $itemprop = 'image')
    {
        if (is_null($post_id))
        {
            global $post;
            $post_id = $post->ID;
        }

        // Generate output
        ob_start();
        include lsd_template('elements/featured-image.php');

        return $this->content(
            ob_get_clean(),
            $this,
            [
                'post_id' => $post_id,
                'size' => $size,
                'method' => 'get',
            ]
        );
    }

    protected function general_settings(array $data): string
    {
        return '<div>
            <label class="lsd-fields-label-tiny" for="lsd_elements_' . esc_attr($this->key) . '_custom_alt">' . esc_html__('Use Custom Alt Text', 'listdom') . '</label>
            ' . LSD_Form::switcher([
                'id' => 'lsd_elements_' . esc_attr($this->key) . '_custom_alt',
                'name' => 'lsd[elements][' . esc_attr($this->key) . '][custom_alt]',
                'value' => $data['custom_alt'] ?? 0,
            ]) . '
            <p class="lsd-admin-description-tiny lsd-mb-0 lsd-mt-2">' . esc_html__('Use listing-specific alt text instead of the Media Library alt text.', 'listdom') . '</p>
        </div>';
    }

    public function cover($size = [350, 220], $post_id = null, $link_method = 'normal', string $style = '')
    {
        if (is_null($post_id))
        {
            global $post;
            $post_id = $post->ID;
        }

        // Generate output
        ob_start();
        include lsd_template('elements/cover-image.php');

        return $this->content(
            ob_get_clean(),
            $this,
            [
                'post_id' => $post_id,
                'size' => $size,
                'method' => 'cover',
            ]
        );
    }

    public function slider($size = [350, 220], $post_id = null, $link_method = 'normal', string $style = '')
    {
        if (is_null($post_id))
        {
            global $post;
            $post_id = $post->ID;
        }

        // Generate output
        ob_start();
        include lsd_template('elements/image-slider.php');

        return $this->content(
            ob_get_clean(),
            $this,
            [
                'post_id' => $post_id,
                'size' => $size,
                'method' => 'slider',
            ]
        );
    }
}
