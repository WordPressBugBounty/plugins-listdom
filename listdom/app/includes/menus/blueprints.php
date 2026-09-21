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
        $options = $this->options();

        $result = LSD_Blueprints::instance()->preview($blueprint_id, $options);
        if (!empty($result['success']) && !empty($options['wizard'])) $result['markup'] = apply_filters('lsd_blueprints_preview_markup', '', $result['preview'], $options);
        $this->respond($result);
    }

    public function apply()
    {
        $this->check_request('lsd_blueprints_apply');

        $blueprint_id = isset($_POST['blueprint']) ? sanitize_key(wp_unslash($_POST['blueprint'])) : '';
        $options = $this->options();

        $result = LSD_Blueprints::instance()->apply($blueprint_id, $options);
        if (!empty($result['success']) && !empty($options['wizard']))
        {
            $result['application']['next_steps'] = $result['next_steps'] ?? [];
            $result['markup'] = apply_filters('lsd_blueprints_apply_markup', '', $result['application'], $options);
        }
        $this->respond($result);
    }

    protected function check_request(string $action): void
    {
        $nonce = isset($_POST['_wpnonce']) ? sanitize_text_field(wp_unslash($_POST['_wpnonce'])) : '';
        if (!$nonce || !wp_verify_nonce($nonce, $action)) $this->respond(['success' => 0, 'message' => esc_html__('Security check failed.', 'listdom')]);
        if (!current_user_can('manage_options')) $this->respond(['success' => 0, 'message' => esc_html__('You are not allowed to perform this action.', 'listdom')]);
    }

    protected function options(): array
    {
        $raw = isset($_POST['options']) ? wp_unslash($_POST['options']) : [];
        $raw = is_array($raw) ? $raw : [];

        return [
            'wizard' => isset($_POST['options']),
            'include_demo' => $this->option_enabled($raw, 'include_demo') || (!isset($_POST['options']) && !empty($_POST['include_demo'])),
            'location' => $this->option_enabled($raw, 'location'),
            'pricing' => $this->option_enabled($raw, 'pricing'),
            'work_hours' => $this->option_enabled($raw, 'work_hours'),
            'reviews' => $this->option_enabled($raw, 'reviews'),
            'booking' => $this->option_enabled($raw, 'booking'),
            'frontend_dashboard' => $this->option_enabled($raw, 'frontend_dashboard'),
            'memberships' => $this->option_enabled($raw, 'memberships'),
            'ai_visibility' => $this->option_enabled($raw, 'ai_visibility'),
            'usage_data' => $this->option_enabled($raw, 'usage_data'),
        ];
    }

    protected function option_enabled(array $options, string $key): bool
    {
        return isset($options[$key]) && in_array($options[$key], [true, 1, '1'], true);
    }

    protected function respond(array $result): void
    {
        wp_send_json($result);
    }
}
