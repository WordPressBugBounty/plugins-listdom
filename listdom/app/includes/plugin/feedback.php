<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom Plugin Feedback Class.
 *
 * @class LSD_Plugin_Feedback
 */
class LSD_Plugin_Feedback extends LSD_Base
{
    public function init()
    {
        add_action('current_screen', function ()
        {
            if (!$this->is_plugins_screen()) return;

            // Print Dialog
            add_action('admin_footer', [$this, 'dialog']);
        });

        // Ajax
        add_action('wp_ajax_lsd_deactivation_feedback', [$this, 'save']);
    }

    /**
     * Print deactivate feedback dialog.
     */
    public function dialog()
    {
        return $this->include_html_file('menus/plugins/deactivation-feedback.php');
    }

    /**
     * Ajax listdom deactivate feedback.
     */
    public function save()
    {
        // Check nonce for security
        check_ajax_referer('_lsd_deactivation_feedback_nonce');

        // Retrieve the action type to determine which button was clicked
        $action_type = isset($_POST['action_type']) ? sanitize_text_field($_POST['action_type']) : '';

        // If the user clicked "Skip & Deactivate," deactivate without feedback
        if ($action_type === 'skip_deactivate')
        {
            $this->response(['success' => 1, 'message' => esc_html__('Plugin deactivated.', 'listdom')]);
        }

        // Retrieve the reason selected by the user
        $reason_key = isset($_POST['reason_key']) ? sanitize_text_field($_POST['reason_key']) : '';
        $pro = isset($_POST['pro']) ? (int) sanitize_text_field($_POST['pro']) : 0;
        $reason_detail = isset($_POST['reason_' . $reason_key]) ? sanitize_text_field($_POST['reason_' . $reason_key]) : '';

        // If no reason was selected, return an error response
        if (trim($reason_key) === '')
        {
            $this->response(['success' => 0, 'message' => esc_html__('Please select a reason before deactivating the plugin.', 'listdom')]);
        }

        // Save feedback if a reason was provided
        if ($reason_key)
        {
            // Make the POST request
            $response = wp_remote_post('https://api.webilia.com/deactivation-feedback', [
                'body' => [
                    'url' => get_site_url(),
                    'basename' => $pro && defined('LSDADDPRO_BASENAME') ? LSDADDPRO_BASENAME : LSD_BASENAME,
                    'reason' => $reason_key,
                    'details' => $reason_detail,
                ],
            ]);

            // Optionally handle the response
            if (is_wp_error($response)) $this->response(['success' => 0, 'message' => sprintf(esc_html__('Error sending deactivation feedback: %s', 'listdom'), $response->get_error_message())]);
        }

        // Return success response
        $this->response(['success' => 1, 'message' => esc_html__('Thank you for your feedback!', 'listdom')]);
    }

    /**
     * Check to see if we're in plugins menu
     */
    private function is_plugins_screen(): bool
    {
        return in_array(get_current_screen()->id, ['plugins', 'plugins-network']);
    }
}
