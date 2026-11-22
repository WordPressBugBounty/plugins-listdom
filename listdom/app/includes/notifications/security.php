<?php

class LSD_Notifications_Security extends LSD_Notifications
{
    public function init(): void
    {
        add_filter('lsd_notification_hooks', [$this, 'hooks']);
        add_filter('lsd_notification_placeholders', [$this, 'register_placeholders']);
        add_action('lsd_user_email_change_confirmation', [$this, 'prepare'], 10, 3);
        add_action('lsd_user_email_change_requested', [$this, 'prepare'], 10, 3);
        add_action('lsd_user_email_change_completed', [$this, 'prepare'], 10, 3);
        add_action('lsd_user_totp_recovery_requested', [$this, 'prepare'], 10, 3);
        add_action('lsd_user_totp_recovery_completed', [$this, 'prepare'], 10, 3);
    }

    public function hooks(array $hooks): array
    {
        $hooks['lsd_user_email_change_confirmation'] = esc_html__('Email Address Change Confirmation', 'listdom');
        $hooks['lsd_user_email_change_requested'] = esc_html__('Email Address Change Requested', 'listdom');
        $hooks['lsd_user_email_change_completed'] = esc_html__('Email Address Change Completed', 'listdom');
        $hooks['lsd_user_totp_recovery_requested'] = esc_html__('Two-Factor Recovery Requested', 'listdom');
        $hooks['lsd_user_totp_recovery_completed'] = esc_html__('Two-Factor Recovery Completed', 'listdom');
        return $hooks;
    }

    public function register_placeholders(array $placeholders): array
    {
        $common = [
            'user_login' => esc_html__('Username of the account', 'listdom'),
            'user_name' => esc_html__('Display name of the account', 'listdom'),
            'user_email' => esc_html__('Current email address of the account', 'listdom'),
            'recipient_email' => esc_html__('Original recipient for this notification stage', 'listdom'),
            'event' => esc_html__('Workflow stage: confirmation, requested, or completed', 'listdom'),
            'expires_in' => esc_html__('Link lifetime in minutes; zero for informational notices', 'listdom'),
            'expires_at' => esc_html__('Link expiry in the site timezone; empty for informational notices', 'listdom'),
        ];
        $email = $common + [
            'old_email' => esc_html__('Email address before the requested change', 'listdom'),
            'new_email' => esc_html__('Proposed or confirmed new email address', 'listdom'),
        ];
        $placeholders['lsd_user_email_change_confirmation'] = $email + [
            'confirmation_link' => esc_html__('Email-only new-address confirmation link', 'listdom'),
        ];
        $placeholders['lsd_user_email_change_requested'] = array_diff_key($email, array_flip(['expires_in', 'expires_at']));
        $placeholders['lsd_user_email_change_completed'] = $placeholders['lsd_user_email_change_requested'];
        $placeholders['lsd_user_totp_recovery_requested'] = $common + [
            'recovery_link' => esc_html__('Email-only two-factor reset link', 'listdom'),
        ];
        $placeholders['lsd_user_totp_recovery_completed'] = array_diff_key($common, array_flip(['expires_in', 'expires_at']));
        return $placeholders;
    }

    public static function defaults(): array
    {
        $defaults = [];
        foreach (self::email_stages() as $event => $stage)
        {
            $content = __('Hi #user_name#,', 'listdom') . "\n\n" . $stage['message'];
            if ($event === 'confirmation') $content .= "\n\n#confirmation_link#";
            $content .= "\n\n" . __('Regards,', 'listdom') . "\n#site_name#";
            $defaults['lsd_user_email_change_' . $event] = ['status' => 'publish', 'title' => '[#site_name#] ' . $stage['subject'], 'content' => $content];
        }
        foreach (self::totp_stages() as $event => $stage)
        {
            $content = __('Hi #user_name#,', 'listdom') . "\n\n" . $stage['message'];
            if ($event === 'requested') $content .= "\n\n#recovery_link#";
            $content .= "\n\n" . __('Regards,', 'listdom') . "\n#site_name#";
            $defaults['lsd_user_totp_recovery_' . $event] = ['status' => 'publish', 'title' => '[#site_name#] ' . $stage['subject'], 'content' => $content];
        }
        return $defaults;
    }

    /** Expand the old stage placeholders without discarding a site's customized copy. */
    public static function split_totp_templates(): void
    {
        if (get_option('lsd_totp_recovery_templates_split')) return;
        $legacy = get_posts(['post_type' => self::PTYPE_NOTIFICATION, 'post_status' => 'any', 'posts_per_page' => -1, 'meta_key' => 'lsd_hook', 'meta_value' => 'lsd_user_totp_recovery']);
        $existing = [];
        foreach (array_keys(self::totp_stages()) as $event)
        {
            $existing[$event] = get_posts(['post_type' => self::PTYPE_NOTIFICATION, 'post_status' => 'any', 'posts_per_page' => 1, 'fields' => 'ids', 'meta_key' => 'lsd_hook', 'meta_value' => 'lsd_user_totp_recovery_' . $event]);
        }
        foreach ($legacy as $notification)
        {
            $meta = get_post_meta($notification->ID);
            foreach (self::totp_stages() as $event => $stage)
            {
                if ($existing[$event]) continue;
                $replace = ['#notification_subject#' => $stage['subject'], '#notification_message#' => $stage['message'], '#action_link#' => $event === 'requested' ? '#recovery_link#' : '', '#recovery_link#' => $event === 'requested' ? '#recovery_link#' : ''];
                $fields = ['post_type' => self::PTYPE_NOTIFICATION, 'post_status' => $notification->post_status, 'post_title' => strtr($notification->post_title, $replace), 'post_content' => $notification->post_content];
                // Keep the original edit link for the requested-stage template.
                if ($event === 'requested') $fields['ID'] = $notification->ID;
                $id = $event === 'requested' ? wp_update_post(wp_slash($fields), true) : wp_insert_post(wp_slash($fields), true);
                if (!$id || is_wp_error($id)) return;
                if ($event === 'completed')
                {
                    foreach ($meta as $key => $values)
                    {
                        if (in_array($key, ['lsd_hook', 'lsd_content', '_edit_lock', '_edit_last'], true)) continue;
                        foreach ($values as $value) add_post_meta($id, $key, wp_slash(maybe_unserialize($value)));
                    }
                }
                update_post_meta($id, 'lsd_hook', 'lsd_user_totp_recovery_' . $event);
                update_post_meta($id, 'lsd_content', wp_slash(strtr((string) ($meta['lsd_content'][0] ?? ''), $replace)));
            }
        }
        update_option('lsd_totp_recovery_templates_split', 1, false);
    }

    /** Preserve customized combined templates while making each email independently editable. */
    public static function split_email_change_templates(): void
    {
        if (get_option('lsd_email_change_templates_split')) return;
        $legacy = get_posts(['post_type' => self::PTYPE_NOTIFICATION, 'post_status' => 'any', 'posts_per_page' => -1, 'meta_key' => 'lsd_hook', 'meta_value' => 'lsd_user_email_change']);
        $existing = [];
        foreach (array_keys(self::email_stages()) as $event)
        {
            $existing[$event] = get_posts(['post_type' => self::PTYPE_NOTIFICATION, 'post_status' => 'any', 'posts_per_page' => 1, 'fields' => 'ids', 'meta_key' => 'lsd_hook', 'meta_value' => 'lsd_user_email_change_' . $event]);
        }
        foreach ($legacy as $notification)
        {
            $meta = get_post_meta($notification->ID);
            foreach (self::email_stages() as $event => $stage)
            {
                if ($existing[$event]) continue;
                $replace = ['#notification_subject#' => $stage['subject'], '#notification_message#' => $stage['message'], '#action_link#' => $event === 'confirmation' ? '#confirmation_link#' : '', '#confirmation_link#' => $event === 'confirmation' ? '#confirmation_link#' : ''];
                $fields = ['post_type' => self::PTYPE_NOTIFICATION, 'post_status' => $notification->post_status, 'post_title' => strtr($notification->post_title, $replace), 'post_content' => $notification->post_content];
                // Keep the original edit link for the new-address confirmation template.
                if ($event === 'confirmation') $fields['ID'] = $notification->ID;
                $id = $event === 'confirmation' ? wp_update_post(wp_slash($fields), true) : wp_insert_post(wp_slash($fields), true);
                if (!$id || is_wp_error($id)) return;
                if ($event !== 'confirmation')
                {
                    foreach ($meta as $key => $values)
                    {
                        if (in_array($key, ['lsd_hook', 'lsd_content', '_edit_lock', '_edit_last'], true)) continue;
                        foreach ($values as $value) add_post_meta($id, $key, wp_slash(maybe_unserialize($value)));
                    }
                }
                update_post_meta($id, 'lsd_hook', 'lsd_user_email_change_' . $event);
                update_post_meta($id, 'lsd_content', wp_slash(strtr((string) ($meta['lsd_content'][0] ?? ''), $replace)));
            }
        }
        update_option('lsd_email_change_templates_split', 1, false);
    }

    private static function email_stages(): array
    {
        return [
            'confirmation' => [
                'subject' => __('Confirm your new email address', 'listdom'),
                'message' => __('Confirm this email change within 24 hours. Your current address will remain active until confirmation:', 'listdom'),
            ],
            'requested' => [
                'subject' => __('Email change requested', 'listdom'),
                'message' => __('A change to your account email was requested. Your current address remains active until the new address is confirmed. Contact the administrator if this was not you.', 'listdom'),
            ],
            'completed' => [
                'subject' => __('Account email address changed', 'listdom'),
                'message' => __('Your account email address has been changed after confirmation. Contact the administrator if you did not authorize this.', 'listdom'),
            ],
        ];
    }

    public static function email_change(int $user_id, string $event, string $old_email, string $new_email, string $url = ''): bool
    {
        $stages = self::email_stages();
        if (!isset($stages[$event])) return false;
        // Old-address notices must never receive the new-address confirmation link.
        $link = $event === 'confirmation' ? $url : '';
        return self::dispatch('lsd_user_email_change_' . $event, $user_id, [
            'event' => $event,
            'recipient_email' => $event === 'confirmation' ? $new_email : $old_email,
            'notification_subject' => $stages[$event]['subject'],
            'notification_message' => $stages[$event]['message'],
            'old_email' => $old_email,
            'new_email' => $new_email,
            'confirmation_link' => $link,
            'action_link' => $link,
            'expires_in' => $link !== '' ? 1440 : 0,
        ]);
    }

    private static function totp_stages(): array
    {
        return [
            'requested' => [
                'subject' => __('Confirm two-factor authentication reset', 'listdom'),
                'message' => __('Use this link within 30 minutes to reset your authenticator. Ignore this email if you did not request it:', 'listdom'),
            ],
            'completed' => [
                'subject' => __('Two-factor authentication reset', 'listdom'),
                'message' => __('Your authenticator and recovery codes were removed using email recovery. Sign in to enroll again if your role requires it.', 'listdom'),
            ],
        ];
    }

    public static function totp_recovery(int $user_id, string $event, string $url = ''): bool
    {
        $stages = self::totp_stages();
        $user = get_userdata($user_id);
        if (!$user || !isset($stages[$event])) return false;
        $link = $event === 'requested' ? $url : '';
        return self::dispatch('lsd_user_totp_recovery_' . $event, $user_id, [
            'event' => $event,
            'recipient_email' => $user->user_email,
            'notification_subject' => $stages[$event]['subject'],
            'notification_message' => $stages[$event]['message'],
            'recovery_link' => $link,
            'action_link' => $link,
            'expires_in' => $link !== '' ? 30 : 0,
        ]);
    }

    private static function dispatch(string $hook, int $user_id, array $args): bool
    {
        $user = get_userdata($user_id);
        if (!$user) return false;
        $args += [
            'user_login' => $user->user_login,
            'user_name' => $user->display_name ?: $user->user_login,
            'user_email' => $user->user_email,
            'expires_at' => $args['expires_in'] ? wp_date(get_option('date_format') . ' ' . get_option('time_format'), time() + $args['expires_in'] * MINUTE_IN_SECONDS) : '',
        ];
        // Preserve confirmation-mail failure handling while dispatching a standard action.
        $delivery = (object) ['sent' => false];
        do_action($hook, $user_id, $args, $delivery);
        if (strpos($hook, 'lsd_user_email_change_') === 0) do_action('lsd_user_email_change', $user_id, $args, $delivery);
        if (strpos($hook, 'lsd_user_totp_recovery_') === 0) do_action('lsd_user_totp_recovery', $user_id, $args, $delivery);
        return $delivery->sent;
    }

    public function prepare(int $user_id, array $args, ?stdClass $delivery = null): array
    {
        $hook = current_filter();
        $defaults = self::defaults();
        if (!isset($defaults[$hook]) || !get_userdata($user_id)) return [];
        $notifications = $this->get($hook);
        // As with email verification, keep essential security mail working without a published template.
        if (!$notifications) $notifications = [null];
        $mails = [];
        $plain = [];
        $html = [];
        foreach ($args as $key => $value)
        {
            $placeholder = '#' . $key . '#';
            $plain[$placeholder] = (string) $value;
            $html[$placeholder] = esc_html((string) $value);
        }
        $html['#notification_message#'] = nl2br(esc_html($args['notification_message']));
        foreach (['action_link', 'confirmation_link', 'recovery_link'] as $key)
        {
            $url = $args[$key] ?? '';
            $html['#' . $key . '#'] = $url !== '' ? '<a href="' . esc_url($url) . '">' . esc_html($url) . '</a>' : '';
        }
        // SMS and other channels receive the event, but mailbox proof stays email-only.
        $channel_args = $args;
        foreach (['action_link', 'confirmation_link', 'recovery_link'] as $key)
        {
            if (isset($channel_args[$key])) $channel_args[$key] = '';
        }
        if ($args['action_link'] !== '')
        {
            $channel_args['notification_message'] = strpos($hook, 'lsd_user_email_change_') === 0
                ? __('An email address change was requested. Check the proposed new email address to confirm it within 24 hours.', 'listdom')
                : __('A two-factor authentication reset was requested. Check your account email to confirm it within 30 minutes.', 'listdom');
        }
        foreach ($notifications as $notification)
        {
            $id = $notification ? $notification->ID : 0;
            $subject = $notification ? get_the_title($notification) : $defaults[$hook]['title'];
            $content = $notification ? get_post_meta($id, 'lsd_content', true) : $defaults[$hook]['content'];
            $original_to = !$notification || get_post_meta($id, 'lsd_original_to', true);
            $to = $original_to ? $args['recipient_email'] : trim(get_post_meta($id, 'lsd_to', true), ', ');
            $cc = $notification ? trim(get_post_meta($id, 'lsd_cc', true), ', ') : '';
            $bcc = $notification ? trim(get_post_meta($id, 'lsd_bcc', true), ', ') : '';
            // Bearer links must reach the mailbox being verified, without recipient overrides or copies.
            if ($args['action_link'] !== '')
            {
                $to = $args['recipient_email'];
                $cc = '';
                $bcc = '';
            }
            $sender = new LSD_Notifications_Email_Sender();
            $mails[] = $sender->boot($id)
                ->to($to)
                ->cc($cc)
                ->bcc($bcc)
                ->subject(strtr($subject, $plain))
                ->content(strtr($content, $html))
                ->render()
                ->send();
            do_action('lsd_send_notification', $user_id, $channel_args, $id, null);
        }
        if ($delivery) $delivery->sent = in_array(true, $mails, true);
        return $mails;
    }
}
