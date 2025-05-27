<?php

class LSD_Element_Attributes extends LSD_Element
{
    public $key = 'attributes';
    public $label;

    public function __construct()
    {
        parent::__construct();

        $this->label = esc_html__('Custom Fields', 'listdom');
    }

    public function get($post_id = null, $show_icons = 0, $show_attribute_title = 1)
    {
        if (is_null($post_id))
        {
            global $post;
            $post_id = $post->ID;
        }

        // Generate output
        ob_start();
        include lsd_template('elements/attributes.php');

        return $this->content(
            ob_get_clean(),
            $this,
            [
                'post_id' => $post_id,
                'show_icons' => $show_icons,
                'show_attribute_title' => $show_attribute_title,
            ]
        );
    }

    protected function general_settings(array $data): string
    {
        return '<div>
            <label for="lsd_elements_' . esc_attr($this->key) . '_show_icons">' . esc_html__('Show Icons', 'listdom') . '</label>
            <select name="lsd[elements][' . esc_attr($this->key) . '][show_icons]" id="lsd_elements_' . esc_attr($this->key) . '_show_icons">
                <option value="0" ' . (isset($data['show_icons']) && $data['show_icons'] == 0 ? 'selected="selected"' : '') . '>' . esc_html__('No', 'listdom') . '</option>
                <option value="1" ' . (isset($data['show_icons']) && $data['show_icons'] == 1 ? 'selected="selected"' : '') . '>' . esc_html__('Yes', 'listdom') . '</option>
            </select>
        </div>
        <div>
            <label for="lsd_elements_' . esc_attr($this->key) . '_show_attribute_title">' . esc_html__('Show Custom Field Title', 'listdom') . '</label>
            <select name="lsd[elements][' . esc_attr($this->key) . '][show_attribute_title]" id="lsd_elements_' . esc_attr($this->key) . '_show_attribute_title">
                <option value="1" ' . (isset($data['show_attribute_title']) && $data['show_attribute_title'] == 1 ? 'selected="selected"' : '') . '>' . esc_html__('Yes', 'listdom') . '</option>
                <option value="0" ' . (isset($data['show_attribute_title']) && $data['show_attribute_title'] == 0 ? 'selected="selected"' : '') . '>' . esc_html__('No', 'listdom') . '</option>
            </select>
        </div>';
    }
}
