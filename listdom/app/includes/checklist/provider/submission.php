<?php

class LSD_Checklist_Provider_Submission extends LSD_Checklist_Provider_Base
{
    public function register(LSD_Checklist_Registry $registry): void
    {
        $category = $this->category(esc_html__('Submission & User Flow', 'listdom'));

        $this->register_checks($registry, [
            $this->check([
                'id' => 'dashboard_page',
                'title' => esc_html__('User dashboard page', 'listdom'),
                'description' => esc_html__('Users need a frontend dashboard to manage listings, orders, and profile actions.', 'listdom'),
                'category' => $category,
                'importance' => 'high',
                'optional' => true,
                'action_label' => esc_html__('Configure', 'listdom'),
                'action_url' => $this->helper->settings_action_url('frontend-dashboard', 'general'),
                'action_focus_target' => '#lsd_settings_submission_page',
            ], [$this, 'dashboard_page']),
            $this->check([
                'id' => 'user_pages',
                'title' => esc_html__('Required authentication pages', 'listdom'),
                'description' => esc_html__('Dedicated login, register, and reset flows reduce friction for contributors.', 'listdom'),
                'category' => $category,
                'importance' => 'medium',
                'optional' => true,
                'action_label' => esc_html__('Configure', 'listdom'),
                'action_url' => $this->helper->settings_action_url('auth', 'authentication'),
                'action_focus_target' => '#lsd_auth_login_form',
            ], [$this, 'user_pages']),
            $this->check([
                'id' => 'listing_form',
                'title' => esc_html__('Frontend Listing Form Settings', 'listdom'),
                'description' => esc_html__('Submission modules and required fields should match your directory model.', 'listdom'),
                'category' => $category,
                'importance' => 'medium',
                'action_label' => esc_html__('Configure', 'listdom'),
                'action_url' => $this->helper->settings_action_url('frontend-dashboard', 'restrictions'),
            ], [$this, 'listing_form']),
            $this->check([
                'id' => 'notification_emails',
                'title' => esc_html__('Notification emails', 'listdom'),
                'description' => esc_html__('Admin and user notifications help keep approvals, verification, and lead flows visible.', 'listdom'),
                'category' => $category,
                'importance' => 'medium',
                'optional' => true,
                'action_label' => esc_html__('Manage', 'listdom'),
                'action_url' => admin_url('edit.php?post_type=' . LSD_Base::PTYPE_NOTIFICATION),
            ], [$this, 'notification_emails']),
        ]);
    }

    protected function dashboard_page(?LSD_Checklist_Check_Interface $check = null): array
    {
        $page_id = (int) LSD_Options::post_id('submission_page');
        $ready = $this->helper->published_post_has_shortcodes($page_id, ['listdom-dashboard']);

        return [
            'status' => $ready ? LSD_Checklist_Result::STATUS_COMPLETE : LSD_Checklist_Result::STATUS_OPTIONAL,
            'message' => $ready ? esc_html__('Dashboard page is assigned, published, and contains the dashboard shortcode.', 'listdom') : esc_html__('Assign a published page containing the Listdom dashboard shortcode.', 'listdom'),
        ];
    }

    protected function user_pages(?LSD_Checklist_Check_Interface $check = null): array
    {
        $auth = LSD_Options::auth();
        $settings = isset($auth['auth']) && is_array($auth['auth']) ? $auth['auth'] : [];
        $configured = 0;
        $available = 0;
        $first_missing = null;
        $flows = [
            [
                'form_key' => 'login_form',
                'hide_key' => 'hide_login_form',
                'page_key' => 'login_page',
                'shortcodes' => ['listdom-auth', 'listdom-login'],
                'page_focus_target' => '#lsd_login_page_select',
                'label' => esc_html__('login page', 'listdom'),
            ],
            [
                'form_key' => 'register_form',
                'hide_key' => 'hide_register_form',
                'page_key' => 'register_page',
                'shortcodes' => ['listdom-auth', 'listdom-register'],
                'page_focus_target' => '#lsd_register_page_select',
                'label' => esc_html__('registration page', 'listdom'),
            ],
            [
                'form_key' => 'forgot_password_form',
                'hide_key' => 'hide_forgot_password_form',
                'page_key' => 'forgot_password_page',
                'shortcodes' => ['listdom-auth', 'listdom-forgot-password'],
                'page_focus_target' => '#lsd_forgot_password_page_select',
                'label' => esc_html__('forgot password page', 'listdom'),
            ],
        ];

        foreach ($flows as $flow)
        {
            if (empty($settings[$flow['form_key']]) || !empty($settings[$flow['hide_key']])) continue;

            $available++;
            $page_id = (int) ($settings[$flow['page_key']] ?? 0);

            if ($this->helper->published_post_has_shortcodes($page_id, $flow['shortcodes']))
            {
                $configured++;
                continue;
            }

            if ($first_missing === null) $first_missing = $flow;
        }

        if ($available === 0)
        {
            return [
                'status' => LSD_Checklist_Result::STATUS_OPTIONAL,
                'message' => esc_html__('Enable at least one dedicated login, registration, or forgot password page.', 'listdom'),
                'action_focus_target' => '#lsd_auth_login_form',
            ];
        }

        if ($configured === $available) return ['status' => LSD_Checklist_Result::STATUS_COMPLETE, 'message' => esc_html__('All enabled auth pages are assigned.', 'listdom'),];

        return [
            'status' => LSD_Checklist_Result::STATUS_OPTIONAL,
            'message' => sprintf(esc_html__('Assign a published %s.', 'listdom'), $first_missing['label'] ?? esc_html__('auth page', 'listdom')),
            'action_focus_target' => $first_missing['page_focus_target'] ?? '#lsd_auth_login_form',
        ];
    }

    protected function listing_form(?LSD_Checklist_Check_Interface $check = null): array
    {
        $settings = LSD_Options::settings();

        // Modules
        $available_modules = array_column((new LSD_Dashboard())->modules(), 'key');
        $configured_modules = isset($settings['submission_module']) && is_array($settings['submission_module']) ? $settings['submission_module'] : [];
        $enabled_modules = array_filter(array_intersect_key($configured_modules, array_flip($available_modules)));

        $count = count($enabled_modules);

        return [
            'status' => $count >= 4 ? LSD_Checklist_Result::STATUS_COMPLETE : LSD_Checklist_Result::STATUS_WARNING,
            'message' => $count >= 4 ? sprintf(esc_html__('%d submission modules are enabled.', 'listdom'), $count) : esc_html__('The submission form still needs a quick review.', 'listdom'),
        ];
    }

    protected function notification_post_id(string $hook): int
    {
        $posts = get_posts([
            'post_type' => LSD_Base::PTYPE_NOTIFICATION,
            'post_status' => ['draft', 'pending', 'future', 'private'],
            'posts_per_page' => 1,
            'fields' => 'ids',
            'meta_key' => 'lsd_hook',
            'meta_value' => $hook,
            'orderby' => 'ID',
            'order' => 'DESC',
            'suppress_filters' => false,
        ]);

        return isset($posts[0]) ? (int) $posts[0] : 0;
    }

    protected function notification_emails(?LSD_Checklist_Check_Interface $check = null): array
    {
        $notifications = new LSD_Notifications();

        $required = [
            'lsd_new_listing' => esc_html__('New Listing', 'listdom'),
            'lsd_listing_status_changed' => esc_html__('Listing Status Changed', 'listdom'),
        ];

        $missing = [];

        foreach ($required as $hook => $label)
        {
            if (count($notifications->get($hook)) > 0) continue;
            $missing[$hook] = $label;
        }

        if (!count($missing)) return ['status' => LSD_Checklist_Result::STATUS_COMPLETE, 'message' => esc_html__('New-listing and listing-status notifications are published.', 'listdom'),];

        $first_missing_hook = (string) array_key_first($missing);
        $notification_id = $this->notification_post_id($first_missing_hook);

        return [
            'status' => LSD_Checklist_Result::STATUS_OPTIONAL,
            'message' => sprintf(esc_html__('Publish the following notification emails: %s.', 'listdom'), implode(', ', $missing)),
            'action_label' => $notification_id > 0 ? esc_html__('Edit', 'listdom') : esc_html__('Create', 'listdom'),
            'action_url' => $notification_id > 0 ? $this->helper->edit_post_url($notification_id) : $this->helper->post_new_url(LSD_Base::PTYPE_NOTIFICATION),
            'action_focus_target' => '#lsd_notification_content_hook',
        ];
    }
}
