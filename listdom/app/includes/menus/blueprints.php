<?php

class LSD_Menus_Blueprints extends LSD_Menus
{
    public function __construct()
    {
        $this->init();
    }

    public function init()
    {
        add_action('wp_ajax_lsd_blueprints_preview', [$this, 'preview']);
        add_action('wp_ajax_lsd_blueprints_apply', [$this, 'apply']);
    }

    public function output()
    {
        wp_safe_redirect(admin_url('admin.php?page=listdom-ix&tab=dummy-data&subtab=blueprint'));
        exit;
    }

    public function preview()
    {
        $this->check_request('lsd_blueprints_preview');

        $blueprint_id = isset($_POST['blueprint']) ? sanitize_key(wp_unslash($_POST['blueprint'])) : '';
        $options = [
            'include_demo' => !empty($_POST['include_demo']),
        ];

        $result = LSD_Blueprints::instance()->preview($blueprint_id, $options);
        $this->respond($result);
    }

    public function apply()
    {
        $this->check_request('lsd_blueprints_apply');

        $blueprint_id = isset($_POST['blueprint']) ? sanitize_key(wp_unslash($_POST['blueprint'])) : '';
        $options = [
            'include_demo' => !empty($_POST['include_demo']),
        ];

        $result = LSD_Blueprints::instance()->apply($blueprint_id, $options);
        $this->respond($result);
    }

    protected function check_request(string $action): void
    {
        $nonce = isset($_POST['_wpnonce']) ? sanitize_text_field(wp_unslash($_POST['_wpnonce'])) : '';
        if (!$nonce || !wp_verify_nonce($nonce, $action)) $this->respond(['success' => 0, 'message' => esc_html__('Security check failed.', 'listdom')]);
        if (!current_user_can('manage_options')) $this->respond(['success' => 0, 'message' => esc_html__('You are not allowed to perform this action.', 'listdom')]);
    }

    protected function respond(array $result): void
    {
        wp_send_json($result);
    }
}
