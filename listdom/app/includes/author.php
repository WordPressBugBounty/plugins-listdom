<?php

class LSD_Author extends LSD_Base
{
    public function init()
    {
        add_action('show_user_profile', [$this, 'form']);
        add_action('edit_user_profile', [$this, 'form']);

        add_action('personal_options_update', [$this, 'save']);
        add_action('edit_user_profile_update', [$this, 'save']);
    }

    public function form($user)
    {
        // Generate output
        include $this->include_html_file('metaboxes/author/form.php', ['return_path' => true]);
    }

    public function save($user_id): bool
    {
        // No Access
        if (!current_user_can('edit_user', $user_id)) return false;

        $nonce = isset($_POST['_wpnonce']) ? sanitize_text_field(wp_unslash($_POST['_wpnonce'])) : '';
        if (!$nonce || !wp_verify_nonce($nonce, 'update-user_' . $user_id)) return false;

        // Job Title
        update_user_meta($user_id, 'lsd_job_title', isset($_POST['lsd_job_title'])
            ? sanitize_text_field(wp_unslash($_POST['lsd_job_title']))
            : ''
        );

        // Save Social Networks
        do_action('lsd_social_networks_profile_save', $user_id);

        // Phone
        update_user_meta($user_id, 'lsd_phone', isset($_POST['lsd_phone'])
            ? sanitize_text_field(wp_unslash($_POST['lsd_phone']))
            : ''
        );

        // Mobile
        update_user_meta($user_id, 'lsd_mobile', isset($_POST['lsd_mobile'])
            ? sanitize_text_field(wp_unslash($_POST['lsd_mobile']))
            : ''
        );

        // Website
        update_user_meta($user_id, 'lsd_website', isset($_POST['lsd_website'])
            ? sanitize_text_field(wp_unslash($_POST['lsd_website']))
            : ''
        );

        // Fax
        update_user_meta($user_id, 'lsd_fax', isset($_POST['lsd_fax'])
            ? sanitize_text_field(wp_unslash($_POST['lsd_fax']))
            : ''
        );

        return true;
    }
}
