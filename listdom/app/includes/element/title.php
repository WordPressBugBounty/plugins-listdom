<?php
// no direct access
defined('ABSPATH') || die();

if(!class_exists('LSD_Element_Title')):

/**
 * Listdom Title Element Class.
 *
 * @class LSD_Element_Title
 * @version	1.0.0
 */
class LSD_Element_Title extends LSD_Element
{
    public $key = 'title';
    public $label;

    /**
	 * Constructor method
	 */
	public function __construct()
    {
        // Call the parent constructor
        parent::__construct();

        $this->label = esc_html__('Listing Title', 'listdom');
	}

    public function get($post_id)
    {
        if(is_null($post_id))
        {
            global $post;
            $post_id = $post->ID;
        }

        $title = get_the_title($post_id);
        $words = explode(' ', $title);
        $count = count($words);

        if($count == 1) $output = $title;
        else
        {
            $t = '';

            $i = 1;
            foreach($words as $word)
            {
                if($i == $count) $t .= '<strong>'.esc_html($word).'</strong> ';
                else $t .= esc_html($word).' ';

                $i++;
            }

            $output = trim($t, ' ');
        }

        return $this->content(
            $output,
            $this,
            [
                'post_id' => $post_id,
            ]
        );
    }
}

endif;