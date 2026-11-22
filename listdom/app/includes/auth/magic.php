<?php

class LSD_Auth_Magic
{
    public static function enabled(): bool
    {
        return LSD_Base::isPro() && !empty(LSD_Options::auth()['passwordless']['magic']) && LSD_Security_Totp::available() && wp_parse_url(home_url('/'), PHP_URL_SCHEME) === 'https';
    }

    public static function request(string $identifier, array $context)
    {
        if (!self::enabled()) return new WP_Error('lsd_magic_disabled', __('Email sign-in links are not enabled.', 'listdom'));
        if (trim($identifier) === '') return new WP_Error('lsd_identifier_required', __('Enter your username or email address.', 'listdom'));
        $limit = LSD_Security_Rate::check('magic', $identifier);
        if (is_wp_error($limit)) return $limit;
        $user = LSD_Security::find_user($identifier);
        $data = ['user' => $user instanceof WP_User ? $user->ID : 0, 'context' => $context, 'fingerprint' => $user instanceof WP_User ? LSD_Security_Challenge::fingerprint($user) : ''];
        $token = LSD_Auth_State::issue('magic', $data, 600);
        if ($token === '') return LSD_Auth_State::expired();
        // Queue a uniform record. The raw email bearer is encrypted until dispatch.
        $mail = LSD_Security_State::issue('native_magicmail', ['payload' => LSD_Auth_State::seal(wp_json_encode(['token' => $token, 'user' => $data['user']]))], 600);
        wp_schedule_single_event(time(), 'lsd_auth_magic_mail', [$mail]);
        if ((!defined('DISABLE_WP_CRON') || !DISABLE_WP_CRON) && !wp_doing_cron()) spawn_cron();
        return true;
    }

    public static function send(string $mail): void
    {
        $record = LSD_Security_State::consume('native_magicmail', $mail);
        $data = $record ? json_decode(LSD_Auth_State::open($record['payload']), true) : [];
        $state = $data ? LSD_Security_State::read('native_magic', $data['token']) : [];
        $user = $state && $state['user'] ? get_userdata($state['user']) : false;
        if (!self::enabled() || !$user || is_wp_error(LSD_Auth::eligible($user->ID, $state['context'], 'magic')) || !hash_equals($state['fingerprint'], LSD_Security_Challenge::fingerprint($user))) return;
        LSD_Notifications_Auth::send('magic', $user->user_email, LSD_Auth::url('magic-confirm', ['proof' => $data['token']]));
    }

    public static function screen(): void
    {
        $proof = LSD_Security_Account::input('proof', $_GET);
        $state = LSD_Auth_State::read('magic', $proof);
        if (!$state) LSD_Auth::failure(LSD_Auth_State::expired());
        $user = get_userdata($state['user']);
        if (!$user || !self::enabled()) LSD_Auth::failure(LSD_Auth_State::expired(), $state['context']);
        LSD_Auth::show(['stage' => 'magic', 'proof' => $proof, 'account' => $user->user_login], $state['context']);
    }

    public static function confirm(string $view, array $data)
    {
        $proof = is_string($data['proof'] ?? null) ? $data['proof'] : '';
        $state = LSD_Auth_State::read('magic', $proof);
        $user = $state ? get_userdata($state['user']) : false;
        if (!self::enabled() || !$user || is_user_logged_in() || !hash_equals($state['fingerprint'], LSD_Security_Challenge::fingerprint($user))) return LSD_Auth_State::expired();
        if (!LSD_Auth_State::consume('view', $view) || !LSD_Auth_State::consume('magic', $data['proof'])) return LSD_Auth_State::expired();
        return LSD_Auth::complete($user->ID, $state['context'], 'magic');
    }
}
