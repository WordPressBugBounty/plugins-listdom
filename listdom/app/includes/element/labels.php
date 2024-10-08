<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom Labels Element Class.
 *
 * @class LSD_Element_Labels
 * @version    1.0.0
 */
class LSD_Element_Labels extends LSD_Element
{
    public $key = 'labels';
    public $style = 'tags';
    public $label;

    /**
     * Constructor method
     */
    public function __construct($style = 'tags')
    {
        // Call the parent constructor
        parent::__construct();

        $this->label = esc_html__('Labels', 'listdom');
        $this->style = $style;
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
        include lsd_template('elements/labels.php');

        return $this->content(
            ob_get_clean(),
            $this,
            [
                'post_id' => $post_id,
            ]
        );
    }

    public static function styles($label_id): string
    {
        $color = get_term_meta($label_id, 'lsd_color', true);
        $text = LSD_Base::get_text_color($color);

        return 'style="background-color: ' . esc_attr($color) . '; color: ' . esc_attr($text) . ';"';
    }
}
