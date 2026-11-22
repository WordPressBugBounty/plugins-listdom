<?php

/** Email-only bearer notifications using the existing notification templates and sender. */
class LSD_Notifications_Auth extends LSD_Notifications
{
    public function init(): void
    {
        add_filter('lsd_notification_hooks', [$this, 'hooks']);
        add_filter('lsd_notification_placeholders', [$this, 'register_placeholders']);
        add_action('init', [$this, 'seed'], 20);
    }

    public static function definitions(): array
    {
        return [
            'registration' => ['hook' => 'lsd_auth_registration_confirmation', 'label' => __('Social Registration Email Confirmation', 'listdom'), 'subject' => __('Confirm your email address', 'listdom'), 'message' => __('Confirm this email address in the browser where you started registration. This link expires in 30 minutes. Opening the link does not create an account until you confirm.', 'listdom')],
            'magic' => ['hook' => 'lsd_auth_magic_link', 'label' => __('Email Sign-In Link', 'listdom'), 'subject' => __('Your sign-in link', 'listdom'), 'message' => __('Use this link in the browser where you requested it, then confirm sign-in. It expires in 10 minutes and can be used once. If you did not request this, ignore this email.', 'listdom')],
        ];
    }

    public function hooks(array $hooks): array
    {
        foreach (self::definitions() as $definition) $hooks[$definition['hook']] = $definition['label'];
        return $hooks;
    }

    public function register_placeholders(array $placeholders): array
    {
        foreach (self::definitions() as $definition) $placeholders[$definition['hook']] = ['confirmation_link' => __('Email-only confirmation link', 'listdom'), 'recipient_email' => __('Recipient email address', 'listdom'), 'expires_in' => __('Link lifetime in minutes', 'listdom')];
        return $placeholders;
    }

    public function seed(): void
    {
        if (get_option('lsd_native_auth_notifications_seeded')) return;
        foreach (self::definitions() as $definition)
        {
            if ($this->get($definition['hook'])) continue;
            $content = $definition['message'] . "\n\n#confirmation_link#\n\n#site_name#";
            $id = wp_insert_post(['post_type' => self::PTYPE_NOTIFICATION, 'post_status' => 'publish', 'post_title' => '[#site_name#] ' . $definition['subject']]);
            if (!$id || is_wp_error($id)) return;
            update_post_meta($id, 'lsd_hook', $definition['hook']);
            update_post_meta($id, 'lsd_content', $content);
            update_post_meta($id, 'lsd_original_to', 1);
        }
        update_option('lsd_native_auth_notifications_seeded', 1, false);
    }

    public static function send(string $type, string $email, string $url): bool
    {
        $definition = self::definitions()[$type];
        $templates = (new self())->get($definition['hook']);
        if (!$templates) $templates = [null];
        $plain = ['#site_name#' => wp_strip_all_tags(get_bloginfo('name')), '#recipient_email#' => $email, '#confirmation_link#' => $url, '#expires_in#' => $type === 'magic' ? '10' : '30'];
        $html = array_map('esc_html', $plain);
        $html['#confirmation_link#'] = '<a href="' . esc_url($url) . '">' . esc_html($url) . '</a>';
        $sent = false;
        foreach ($templates as $template)
        {
            $id = $template ? $template->ID : 0;
            $subject = $template ? get_the_title($template) : '[#site_name#] ' . $definition['subject'];
            $content = $template ? get_post_meta($id, 'lsd_content', true) : $definition['message'] . "\n\n#confirmation_link#";
            // Bearer links never use custom recipients, CC, BCC or non-email channels.
            $sender = new LSD_Notifications_Email_Sender();
            $sent = $sender->boot($id)->to($email)->cc('')->bcc('')->subject(strtr($subject, $plain))->content(strtr($content, $html))->render()->send() || $sent;
        }
        do_action('lsd_auth_mail_sent', $type, $sent);
        return $sent;
    }
}
