<?php
// no direct access
defined('ABSPATH') || die();

if(!class_exists('LSD_Element_Features')):

/**
 * Listdom Features Element Class.
 *
 * @class LSD_Element_Features
 * @version	1.0.0
 */
class LSD_Element_Features extends LSD_Element
{
    public $key = 'features';
    public $label;
    public $show_icons;
    public $separator = '/';

    /**
	 * Constructor method
     * @param boolean $show_icons
	 */
	public function __construct($show_icons = false)
    {
        // Call the parent constructor
        parent::__construct();

        $this->label = esc_html__('Features', 'listdom');
        $this->show_icons = $show_icons;
	}

    public function get($post_id = null)
    {
        if(is_null($post_id))
        {
            global $post;
            $post_id = $post->ID;
        }

        // Generate output
        ob_start();
        include lsd_template('elements/features/list.php');

        return $this->content(
            ob_get_clean(),
            $this,
            [
                'post_id' => $post_id,
            ]
        );
    }

    public function text($post_id, $separator = '/')
    {
        if(is_null($post_id))
        {
            global $post;
            $post_id = $post->ID;
        }

        // Set the Separator
        $this->separator = $separator;

        // Generate output
        ob_start();
        include lsd_template('elements/features/text.php');
        return ob_get_clean();
    }

    public function form($data = [])
    {
        // Third Party Fields
        ob_start();
        do_action('lsd_element_form_options', $this->key, $data);
        $additional = LSD_Kses::form(ob_get_clean());

        return '<div class="lsd-form-row">
            <div class="lsd-col-10 lsd-handler">
                <input type="hidden" name="lsd[elements]['.esc_attr($this->key).']" />
                <input type="hidden" name="lsd[elements]['.esc_attr($this->key).'][enabled]" value="'.esc_attr($data['enabled']).'" />
                '.$this->label.'
            </div>
            <div class="lsd-col-2 lsd-actions lsd-details-page-element-toggle-status" id="lsd_actions_'.esc_attr($this->key).'" data-key="'.esc_attr($this->key).'">
                <span class="lsd-toggle lsd-mr-2" data-for="#lsd_options_'.esc_attr($this->key).'" data-all=".lsd-element-options">
                    <i class="lsd-icon fa fa-cog fa-lg"></i>
                </span>
                <strong class="lsd-enabled '.($data['enabled'] ? '' : 'lsd-util-hide').'"><i class="lsd-icon fa fa-check"></i></strong>
                <strong class="lsd-disabled '.($data['enabled'] ? 'lsd-util-hide' : '').'"><i class="lsd-icon fa fa-minus-circle"></i></strong>
            </div>
        </div>
        <div class="lsd-element-options lsd-util-hide" id="lsd_options_'.esc_attr($this->key).'">
            <div class="lsd-form-row">
                <div class="lsd-col-2">
                    <label for="lsd_elements_'.esc_attr($this->key).'_show_title">'.esc_html__('Show Title', 'listdom').'</label>
                    <select name="lsd[elements]['.esc_attr($this->key).'][show_title]" id="lsd_elements_'.esc_attr($this->key).'_show_title">
                        <option value="1" '.((isset($data['show_title']) and $data['show_title'] == 1) ? 'selected="selected"' : '').'>'.esc_html__('Yes', 'listdom').'</option>
                        <option value="0" '.((isset($data['show_title']) and $data['show_title'] == 0) ? 'selected="selected"' : '').'>'.esc_html__('No', 'listdom').'</option>
                    </select>
                </div>
                <div class="lsd-col-2">
                    <label for="lsd_elements_'.esc_attr($this->key).'_show_icons">'.esc_html__('Show Icons', 'listdom').'</label>
                    <select name="lsd[elements]['.esc_attr($this->key).'][show_icons]" id="lsd_elements_'.esc_attr($this->key).'_show_icons">
                        <option value="0" '.((isset($data['show_icons']) and $data['show_icons'] == 0) ? 'selected="selected"' : '').'>'.esc_html__('No', 'listdom').'</option>
                        <option value="1" '.((isset($data['show_icons']) and $data['show_icons'] == 1) ? 'selected="selected"' : '').'>'.esc_html__('Yes', 'listdom').'</option>
                    </select>
                </div>
            </div>
            '.$additional.'
        </div>';
    }
}

endif;