<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom Video Element Class.
 *
 * @class LSD_Element_Video
 * @version    1.0.0
 */
class LSD_Element_Video extends LSD_Element
{
    public $key = 'video';
    public $label;

    /**
     * Constructor method
     */
    public function __construct()
    {
        // Call the parent constructor
        parent::__construct();

        $this->label = esc_html__('Featured Video', 'listdom');
    }

    public function get($post_id = null)
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
        include lsd_template('elements/video.php');

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
        // Disabled in Lite
        if ($this->isLite()) return '<div class="lsd-form-row">
            <div class="lsd-col-12 lsd-handler">
                <input type="hidden" name="lsd[elements][' . esc_attr($this->key) . ']" />
                <input type="hidden" name="lsd[elements][' . esc_attr($this->key) . '][enabled]" value="0" />
                ' . $this->missFeatureMessage(esc_html__('Featured Video Element', 'listdom')) . '
            </div>
        </div>';

        return parent::form($data);
    }
}
