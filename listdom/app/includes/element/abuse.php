<?php

class LSD_Element_Abuse extends LSD_Element
{
    public $key = 'abuse';
    public $label;
    protected $args;

    public function __construct(array $args = [])
    {
        parent::__construct();

        $this->label = esc_html__('Report Abuse', 'listdom');
        $this->args = $args;
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
        include lsd_template('elements/abuse.php');

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
        return '<div class="lsd-elements-' . esc_attr($this->key) . '-field-options">
            <label for="lsd_elements_' . esc_attr($this->key) . '_name_field">' . esc_html__('Name Field', 'listdom') . '</label>
            ' . LSD_Form::switcher([
                'id' => 'lsd_elements_' . esc_attr($this->key) . '_name_field',
                'name' => 'lsd[elements][' . esc_attr($this->key) . '][name_field]',
                'value' => $data['name_field'] ?? 1,
            ]) . '
        </div>
        <div class="lsd-elements-' . esc_attr($this->key) . '-field-options">
            <label for="lsd_elements_' . esc_attr($this->key) . '_phone_field">' . esc_html__('Phone Field', 'listdom') . '</label>
            ' . LSD_Form::switcher([
                'id' => 'lsd_elements_' . esc_attr($this->key) . '_phone_field',
                'name' => 'lsd[elements][' . esc_attr($this->key) . '][phone_field]',
                'value' => $data['phone_field'] ?? 1,
            ]) . '
        </div>';
    }
}
