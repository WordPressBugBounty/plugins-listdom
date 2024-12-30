<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom Owner Element Class.
 *
 * @class LSD_Element_Owner
 * @version    1.0.0
 */
class LSD_Element_Owner extends LSD_Element
{
    public $key = 'owner';
    public $label;
    public $layout;
    public $args;

    /**
     * Constructor method
     * @param array $args
     * @param string $layout
     */
    public function __construct(string $layout = 'details', array $args = [])
    {
        // Call the parent constructor
        parent::__construct();

        $this->label = esc_html__('Owner', 'listdom');
        $this->layout = $layout;
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
        include lsd_template('elements/owner.php');

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
        $display_form = $data['display_form'] ?? 1;

        return '<div>
            <label for="lsd_elements_' . esc_attr($this->key) . '_display_tel">' . esc_html__('Tel', 'listdom') . '</label>
            ' . LSD_Form::switcher([
                'id' => 'lsd_elements_' . esc_attr($this->key) . '_display_tel',
                'name' => 'lsd[elements][' . esc_attr($this->key) . '][display_tel]',
                'value' => $data['display_tel'] ?? 1,
            ]) . '
        </div>
        <div>
            <label for="lsd_elements_' . esc_attr($this->key) . '_display_email">' . esc_html__('Email', 'listdom') . '</label>
            ' . LSD_Form::switcher([
                'id' => 'lsd_elements_' . esc_attr($this->key) . '_display_email',
                'name' => 'lsd[elements][' . esc_attr($this->key) . '][display_email]',
                'value' => $data['display_email'] ?? 1,
            ]) . '
        </div>
        <div>
            <label for="lsd_elements_' . esc_attr($this->key) . '_display_mobile">' . esc_html__('Mobile', 'listdom') . '</label>
            ' . LSD_Form::switcher([
                'id' => 'lsd_elements_' . esc_attr($this->key) . '_display_mobile',
                'name' => 'lsd[elements][' . esc_attr($this->key) . '][display_mobile]',
                'value' => $data['display_mobile'] ?? 1,
            ]) . '
        </div>
        <div>
            <label for="lsd_elements_' . esc_attr($this->key) . '_display_website">' . esc_html__('Website', 'listdom') . '</label>
            ' . LSD_Form::switcher([
                'id' => 'lsd_elements_' . esc_attr($this->key) . '_display_website',
                'name' => 'lsd[elements][' . esc_attr($this->key) . '][display_website]',
                'value' => $data['display_website'] ?? 0,
            ]) . '
        </div>
        <div>
            <label for="lsd_elements_' . esc_attr($this->key) . '_display_fax">' . esc_html__('Fax', 'listdom') . '</label>
            ' . LSD_Form::switcher([
                'id' => 'lsd_elements_' . esc_attr($this->key) . '_display_fax',
                'name' => 'lsd[elements][' . esc_attr($this->key) . '][display_fax]',
                'value' => $data['display_fax'] ?? 1,
            ]) . '
        </div>
        <div>
            <label for="lsd_elements_' . esc_attr($this->key) . '_display_form">' . esc_html__('Contact Form', 'listdom') . '</label>
            ' . LSD_Form::switcher([
                'id' => 'lsd_elements_' . esc_attr($this->key) . '_display_form',
                'name' => 'lsd[elements][' . esc_attr($this->key) . '][display_form]',
                'value' => $display_form,
                'toggle' => '.lsd-elements-' . esc_attr($this->key) . '-field-options',
            ]) . '
        </div>
        <div class="lsd-elements-' . esc_attr($this->key) . '-field-options '.($display_form ? '' : 'lsd-util-hide').'">
            <label for="lsd_elements_' . esc_attr($this->key) . '_name_field">' . esc_html__('Name Field', 'listdom') . '</label>
            ' . LSD_Form::switcher([
                'id' => 'lsd_elements_' . esc_attr($this->key) . '_name_field',
                'name' => 'lsd[elements][' . esc_attr($this->key) . '][name_field]',
                'value' => $data['name_field'] ?? 1,
            ]) . '
        </div>
        <div class="lsd-elements-' . esc_attr($this->key) . '-field-options '.($display_form ? '' : 'lsd-util-hide').'">
            <label for="lsd_elements_' . esc_attr($this->key) . '_phone_field">' . esc_html__('Phone Field', 'listdom') . '</label>
            ' . LSD_Form::switcher([
                'id' => 'lsd_elements_' . esc_attr($this->key) . '_phone_field',
                'name' => 'lsd[elements][' . esc_attr($this->key) . '][phone_field]',
                'value' => $data['phone_field'] ?? 1,
            ]) . '
        </div>';
    }
}
