<?php

class LSD_Element_Locations extends LSD_Element
{
    public $key = 'locations';
    public $label;
    public $enable_link;

    public function __construct($args = [])
    {
        parent::__construct();

        $this->label = esc_html__('Locations', 'listdom');
        $this->has_title_settings = false;
        $this->enable_link = $args['enable_link'] ?? true;
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
        include lsd_template('elements/locations.php');

        return $this->content(
            ob_get_clean(),
            $this,
            [
                'post_id' => $post_id,
            ]
        );
    }

    protected function general_settings(array $data): string
    {
        $enable_link = isset($data['enable_link']) ? (int) $data['enable_link'] : 1;

        return '<div>
            <label for="lsd_elements_' . esc_attr($this->key) . '_enable_link">' . esc_html__('Enable Link To Archive', 'listdom') . '</label>
            <select name="lsd[elements][' . esc_attr($this->key) . '][enable_link]" id="lsd_elements_' . esc_attr($this->key) . '_enable_link">
                <option value="1" ' . ($enable_link === 1 ? 'selected="selected"' : '') . '>' . esc_html__('Yes', 'listdom') . '</option>
                <option value="0" ' . ($enable_link === 0 ? 'selected="selected"' : '') . '>' . esc_html__('No', 'listdom') . '</option>
            </select>
        </div>';
    }
}
