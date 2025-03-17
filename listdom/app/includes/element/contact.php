<?php

class LSD_Element_Contact extends LSD_Element
{
    public $key = 'contact';
    public $label;

    public function __construct()
    {
        parent::__construct();

        $this->label = esc_html__('Contact Information', 'listdom');
    }

    public function get($post_id = null, array $args = [])
    {
        if (is_null($post_id))
        {
            global $post;
            $post_id = $post->ID;
        }

        // Generate output
        ob_start();
        include lsd_template('elements/contact-info.php');

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
        return '<div>
            <label for="lsd_elements_' . esc_attr($this->key) . '_display_icon">' . esc_html__('Display Icon', 'listdom') . '</label>
            ' . LSD_Form::switcher([
                'id' => 'lsd_elements_' . esc_attr($this->key) . '_display_icon',
                'name' => 'lsd[elements][' . esc_attr($this->key) . '][display_icon]',
                'value' => $data['display_icon'] ?? 1,
            ]) . '
        </div>
        <div>
            <label for="lsd_elements_' . esc_attr($this->key) . '_display_label">' . esc_html__('Display Label', 'listdom') . '</label>
            ' . LSD_Form::switcher([
                'id' => 'lsd_elements_' . esc_attr($this->key) . '_display_label',
                'name' => 'lsd[elements][' . esc_attr($this->key) . '][display_label]',
                'value' => $data['display_label'] ?? 0,
            ]) . '
        </div>';
    }
}
