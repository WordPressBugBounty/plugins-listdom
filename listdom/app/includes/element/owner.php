<?php

class LSD_Element_Owner extends LSD_Element
{
    public $key = 'owner';
    public $label;
    public $layout;
    public $args;

    public function __construct(string $layout = 'details', array $args = [])
    {
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
            <label class="lsd-fields-label-tiny" for="lsd_elements_' . esc_attr($this->key) . '_display_avatar">' . esc_html__('Avatar', 'listdom') . '</label>
            ' . LSD_Form::switcher([
                'id' => 'lsd_elements_' . esc_attr($this->key) . '_display_avatar',
                'name' => 'lsd[elements][' . esc_attr($this->key) . '][display_avatar]',
                'value' => $data['display_avatar'] ?? 1,
            ]) . '
        </div>
        <div>
            <label class="lsd-fields-label-tiny" for="lsd_elements_' . esc_attr($this->key) . '_display_name">' . esc_html__('Name', 'listdom') . '</label>
            ' . LSD_Form::switcher([
                'id' => 'lsd_elements_' . esc_attr($this->key) . '_display_name',
                'name' => 'lsd[elements][' . esc_attr($this->key) . '][display_name]',
                'value' => $data['display_name'] ?? 1,
            ]) . '
        </div>
        <div>
            <label class="lsd-fields-label-tiny" for="lsd_elements_' . esc_attr($this->key) . '_display_job_title">' . esc_html__('Job Title', 'listdom') . '</label>
            ' . LSD_Form::switcher([
                'id' => 'lsd_elements_' . esc_attr($this->key) . '_display_job_title',
                'name' => 'lsd[elements][' . esc_attr($this->key) . '][display_job_title]',
                'value' => $data['display_job_title'] ?? 1,
            ]) . '
        </div>
        <div>
            <label class="lsd-fields-label-tiny" for="lsd_elements_' . esc_attr($this->key) . '_display_bio">' . esc_html__('Bio', 'listdom') . '</label>
            ' . LSD_Form::switcher([
                'id' => 'lsd_elements_' . esc_attr($this->key) . '_display_bio',
                'name' => 'lsd[elements][' . esc_attr($this->key) . '][display_bio]',
                'value' => $data['display_bio'] ?? 1,
            ]) . '
        </div>
        ' . (LSD_Components::socials() ? '<div>
            <label class="lsd-fields-label-tiny" for="lsd_elements_' . esc_attr($this->key) . '_display_socials">' . esc_html__('Socials', 'listdom') . '</label>
            ' . LSD_Form::switcher([
                'id' => 'lsd_elements_' . esc_attr($this->key) . '_display_socials',
                'name' => 'lsd[elements][' . esc_attr($this->key) . '][display_socials]',
                'value' => $data['display_socials'] ?? 1,
            ]) . '
        </div>' : '') . '
        <div>
            <label class="lsd-fields-label-tiny" for="lsd_elements_' . esc_attr($this->key) . '_display_tel">' . esc_html__('Tel', 'listdom') . '</label>
            ' . LSD_Form::switcher([
                'id' => 'lsd_elements_' . esc_attr($this->key) . '_display_tel',
                'name' => 'lsd[elements][' . esc_attr($this->key) . '][display_tel]',
                'value' => $data['display_tel'] ?? 1,
            ]) . '
        </div>
        <div>
            <label class="lsd-fields-label-tiny" for="lsd_elements_' . esc_attr($this->key) . '_display_email">' . esc_html__('Email', 'listdom') . '</label>
            ' . LSD_Form::switcher([
                'id' => 'lsd_elements_' . esc_attr($this->key) . '_display_email',
                'name' => 'lsd[elements][' . esc_attr($this->key) . '][display_email]',
                'value' => $data['display_email'] ?? 1,
            ]) . '
        </div>
        <div>
            <label class="lsd-fields-label-tiny" for="lsd_elements_' . esc_attr($this->key) . '_display_mobile">' . esc_html__('Mobile', 'listdom') . '</label>
            ' . LSD_Form::switcher([
                'id' => 'lsd_elements_' . esc_attr($this->key) . '_display_mobile',
                'name' => 'lsd[elements][' . esc_attr($this->key) . '][display_mobile]',
                'value' => $data['display_mobile'] ?? 1,
            ]) . '
        </div>
        <div>
            <label class="lsd-fields-label-tiny" for="lsd_elements_' . esc_attr($this->key) . '_display_website">' . esc_html__('Website', 'listdom') . '</label>
            ' . LSD_Form::switcher([
                'id' => 'lsd_elements_' . esc_attr($this->key) . '_display_website',
                'name' => 'lsd[elements][' . esc_attr($this->key) . '][display_website]',
                'value' => $data['display_website'] ?? 0,
            ]) . '
        </div>
        <div>
            <label class="lsd-fields-label-tiny" for="lsd_elements_' . esc_attr($this->key) . '_display_fax">' . esc_html__('Fax', 'listdom') . '</label>
            ' . LSD_Form::switcher([
                'id' => 'lsd_elements_' . esc_attr($this->key) . '_display_fax',
                'name' => 'lsd[elements][' . esc_attr($this->key) . '][display_fax]',
                'value' => $data['display_fax'] ?? 1,
            ]) . '
        </div>
        <div>
            <label class="lsd-fields-label-tiny" for="lsd_elements_' . esc_attr($this->key) . '_display_form">' . esc_html__('Contact Form', 'listdom') . '</label>
            ' . LSD_Form::switcher([
                'id' => 'lsd_elements_' . esc_attr($this->key) . '_display_form',
                'name' => 'lsd[elements][' . esc_attr($this->key) . '][display_form]',
                'value' => $display_form,
                'toggle' => '.lsd-elements-' . esc_attr($this->key) . '-field-options',
            ]) . '
        </div>
        <div class="lsd-elements-' . esc_attr($this->key) . '-field-options ' . ($display_form ? '' : 'lsd-util-hide') . '">
            <label class="lsd-fields-label-tiny" for="lsd_elements_' . esc_attr($this->key) . '_name_field">' . esc_html__('Name Field', 'listdom') . '</label>
            ' . LSD_Form::switcher([
                'id' => 'lsd_elements_' . esc_attr($this->key) . '_name_field',
                'name' => 'lsd[elements][' . esc_attr($this->key) . '][name_field]',
                'value' => $data['name_field'] ?? 1,
            ]) . '
        </div>
        <div class="lsd-elements-' . esc_attr($this->key) . '-field-options ' . ($display_form ? '' : 'lsd-util-hide') . '">
            <label class="lsd-fields-label-tiny" for="lsd_elements_' . esc_attr($this->key) . '_phone_field">' . esc_html__('Phone Field', 'listdom') . '</label>
            ' . LSD_Form::switcher([
                'id' => 'lsd_elements_' . esc_attr($this->key) . '_phone_field',
                'name' => 'lsd[elements][' . esc_attr($this->key) . '][phone_field]',
                'value' => $data['phone_field'] ?? 1,
            ]) . '
        </div>
        <div class="lsd-elements-' . esc_attr($this->key) . '-field-options ' . ($display_form ? '' : 'lsd-util-hide') . '">
            <label class="lsd-fields-label-tiny" for="lsd_elements_' . esc_attr($this->key) . '_pc_enabled">' . esc_html__('Privacy Consent', 'listdom') . '</label>
            ' . LSD_Form::switcher([
                'id' => 'lsd_elements_' . esc_attr($this->key) . '_pc_enabled',
                'name' => 'lsd[elements][' . esc_attr($this->key) . '][pc_enabled]',
                'value' => $data['pc_enabled'],
                'toggle' => '.lsd-elements-' . esc_attr($this->key) . '-privacy-consent-label',
            ]) . '
        </div>
        <div>
            <label class="lsd-fields-label-tiny" for="lsd_elements_' . esc_attr($this->key) . '_author_link">' . esc_html__('Author Link', 'listdom') . '</label>
            ' . LSD_Form::switcher([
                'id' => 'lsd_elements_' . esc_attr($this->key) . '_author_link',
                'name' => 'lsd[elements][' . esc_attr($this->key) . '][author_link]',
                'value' => $data['author_link'] ?? 0,
            ]) . '
        </div>  
        <div>
            <label class="lsd-fields-label-tiny" for="lsd_elements_' . esc_attr($this->key) . '_link_avatar">' . esc_html__('Avatar Link', 'listdom') . '</label>
            ' . LSD_Form::switcher([
                'id' => 'lsd_elements_' . esc_attr($this->key) . '_link_avatar',
                'name' => 'lsd[elements][' . esc_attr($this->key) . '][link_avatar]',
                'value' => $data['link_avatar'] ?? 0,
            ]) . '
        </div>
        <div class="lsd-elements-' . esc_attr($this->key) . '-field-options lsd-elements-' . esc_attr($this->key) . '-privacy-consent-label ' . ($display_form && $data['pc_enabled'] ? '' : 'lsd-util-hide') . '">
            <label class="lsd-fields-label-tiny" for="lsd_elements_' . esc_attr($this->key) . '_pc_label">' . esc_html__('Consent Label', 'listdom') . '</label>
            ' . LSD_Form::text([
                'id' => 'lsd_elements_' . esc_attr($this->key) . '_pc_label',
                'name' => 'lsd[elements][' . esc_attr($this->key) . '][pc_label]',
                'value' => $data['pc_label'],
                'class' => 'lsd-admin-input',
            ]) . '
            <p class="lsd-admin-description-tiny lsd-mb-0 lsd-mt-2">' . esc_html__('Leave empty to use the default label. Use {{privacy_policy}} to automatically include the default privacy policy page link.', 'listdom') . '</p>
        </div>';
        
    }
}
