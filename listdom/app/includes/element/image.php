<?php
// no direct access
defined('ABSPATH') || die();

class LSD_Element_Image extends LSD_Element
{
    public $key = 'image';
    public $label;

    public function __construct()
    {
        parent::__construct();

        $this->label = esc_html__('Featured Image', 'listdom');
    }

    public function get($size, $post_id = null)
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

    public function cover($size = [350, 220], $post_id = null, $link_method = 'normal')
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

    public function slider($size = [350, 220], $post_id = null)
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
