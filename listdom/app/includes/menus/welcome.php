<?php

class LSD_Menus_Welcome extends LSD_Menus
{
    /**
     * @var string
     */
    public $tab;

    public function __construct()
    {
        // Initialize the Menu
        $this->init();
    }

    public function init()
    {
        // Newsletter Subscription
        add_action('wp_ajax_lsd_submit_newsletter', [$this, 'newsletter']);

        add_filter('admin_body_class', [$this, 'listdom_welcome_class']);
    }

    public function listdom_welcome_class($classes)
    {
        if (isset($_GET['page']) && $_GET['page'] === LSD_Base::WELCOME_SLUG) $classes .= ' lsd-welcome-wizard-page';
        return $classes;
    }

    public function output()
    {
        // Get the current tab
        $this->tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'setup';

        // Generate output
        $this->include_html_file('menus/welcome/tpl.php');
    }

    public function newsletter()
    {
        $nonce = isset($_POST['lsd_submit_newsletter_nonce'])
            ? sanitize_text_field(wp_unslash($_POST['lsd_submit_newsletter_nonce']))
            : '';

        if (!$nonce || !wp_verify_nonce($nonce, 'lsd_submit_newsletter'))
        {
            $this->response(['success' => 0, 'message' => esc_html__('Security check failed.', 'listdom')]);
        }

        if (!current_user_can('manage_options'))
        {
            $this->response(['success' => 0, 'message' => esc_html__('You are not allowed to perform this action.', 'listdom')]);
        }

        $email = isset($_POST['email']) ? sanitize_email(wp_unslash($_POST['email'])) : '';
        if (!is_email($email)) $this->response(['success' => 0, 'message' => esc_html__('Please enter a valid email address.', 'listdom')]);

        $response = \Webilia\WP\EmailSubscription::subscribe([
            'basename' => LSD_BASENAME,
            'email' => $email,
        ]);

        if (is_wp_error($response)) $this->response(['success' => 0, 'message' => $response->get_error_message()]);

        $this->response(['success' => 1, 'message' => $response['message']]);
    }
}
