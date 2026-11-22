<?php

/** Native authentication orchestrator. Only validated server proofs reach complete(). */
class LSD_Auth extends LSD_Base
{
    private static bool $rendering = false;
    private static bool $rendered = false;
    private static array $page_result = [];
    private static bool $primary_policy = false;

    public static function authenticating_primary(): bool
    {
        return self::$primary_policy;
    }

    public function init(): void
    {
        add_action('template_redirect', [$this, 'route'], -1);
        add_action('init', [self::class, 'upgrade'], 1);
        add_action('switch_blog', [self::class, 'upgrade']);
        add_action('lsd_auth_magic_mail', [LSD_Auth_Magic::class, 'send']);
        add_action('delete_user', [LSD_Auth_Store::class, 'delete_user']);
        add_action('remove_user_from_blog', static function (int $user, int $blog): void
        {
            switch_to_blog($blog);
            try { LSD_Auth_Store::delete_user($user); }
            finally { restore_current_blog(); }
        }, 10, 2);
        add_filter('wp_privacy_personal_data_erasers', static function (array $erasers): array
        {
            $erasers['listdom-auth'] = ['eraser_friendly_name' => __('Listdom authentication credentials', 'listdom'), 'callback' => [LSD_Auth_Store::class, 'erase']];
            return $erasers;
        });
        add_filter('wp_privacy_personal_data_exporters', static function (array $exporters): array
        {
            $exporters['listdom-auth'] = ['exporter_friendly_name' => __('Listdom authentication', 'listdom'), 'callback' => [LSD_Auth_Store::class, 'export']];
            return $exporters;
        });
        add_action('lsd_dashboard_social_login', static function ($dashboard): void
        {
            echo self::methods('login', '', $dashboard->auth_redirect(), true);
        });
    }

    public static function url(string $action, array $args = []): string
    {
        // Explicit front-controller paths work without pretty-permalink rewrite rules.
        if ($action === 'callback') return home_url('/index.php/listdom-auth/callback/' . rawurlencode($args['provider'] ?? '') . '/');
        return add_query_arg(['lsd_auth_action' => $action] + $args, home_url('/'));
    }

    private static function callback_provider(): string
    {
        $path = wp_parse_url(wp_unslash($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH);
        foreach (LSD_Auth_Providers::all() as $provider => $definition)
        {
            if ($path === wp_parse_url(self::url('callback', ['provider' => $provider]), PHP_URL_PATH)) return $provider;
        }
        return '';
    }

    public static function upgrade(): void
    {
        if (is_multisite() && function_exists('wp_is_site_initialized') && !wp_is_site_initialized(get_current_blog_id())) return;
        // Frontend callbacks may be the first request after an update or site switch.
        if (get_option('lsd_settings') && (int) get_option('lsd_db_version', 0) < 4) LSD_Plugin_Hooks::db_update();
    }

    public static function context(string $form = 'login', string $role = '', string $redirect = '', bool $explicit = false): array
    {
        $return = remove_query_arg(['lsd_auth_flow', 'lsd_auth_action', 'state', 'code', 'error'], (new LSD_Main())->current_url());
        if (LSD_Security_Account::input('lsd_auth_action', $_GET) === 'screen') $return = self::url('screen');
        return ['form' => $form === 'register' ? 'register' : 'login', 'role' => $role, 'redirect' => wp_validate_redirect($redirect, home_url('/')), 'explicit' => $explicit, 'return' => self::local_url($return), 'dropdown' => LSD_Shortcodes_Auth::inside_dropdown()];
    }

    public static function local_url(string $url): string
    {
        $home = wp_parse_url(home_url('/'));
        $parts = wp_parse_url($url);
        return $parts && ($parts['scheme'] ?? '') === ($home['scheme'] ?? '') && ($parts['host'] ?? '') === ($home['host'] ?? '') && ($parts['port'] ?? null) === ($home['port'] ?? null) ? wp_validate_redirect($url, home_url('/')) : home_url('/');
    }

    public static function sign(array $context): string
    {
        $payload = LSD_Auth_Providers::encode(wp_json_encode($context));
        return $payload . '.' . hash_hmac('sha256', $payload . '|' . get_current_blog_id(), wp_salt('auth'));
    }

    public static function unsign(string $signed): array
    {
        $parts = explode('.', $signed);
        if (count($parts) !== 2 || !hash_equals(hash_hmac('sha256', $parts[0] . '|' . get_current_blog_id(), wp_salt('auth')), $parts[1])) return [];
        $context = json_decode(LSD_Auth_Providers::decode($parts[0]), true);
        return is_array($context) ? $context : [];
    }

    public static function methods(string $form = 'login', string $role = '', string $redirect = '', bool $explicit = false): string
    {
        if (is_user_logged_in()) return '';
        $providers = array_filter(LSD_Auth_Providers::all(), static function ($definition, $provider): bool { return LSD_Auth_Providers::enabled($provider); }, ARRAY_FILTER_USE_BOTH);
        $magic = $form !== 'register' && LSD_Auth_Magic::enabled();
        $passkeys = $form !== 'register' && LSD_Auth_Passkeys::enabled();
        if (!$providers && !$magic && !$passkeys) return '';
        (new LSD_Assets())->site(true);
        self::assets();
        $context = self::sign(self::context($form, $role, $redirect, $explicit));
        $instance = wp_unique_id('lsd-native-');
        ob_start();
        include lsd_template('auth/methods.php');
        return apply_filters('lsd_auth_methods_output', ob_get_clean(), $form);
    }

    public static function assets(): void
    {
        wp_enqueue_script('lsd-native-auth', plugins_url('assets/js/native-auth.min.js', LSD_ABSPATH . '/listdom.php'), ['jquery'], LSD_VERSION, true);
    }

    public static function requested(array $atts = []): bool
    {
        $token = LSD_Security_Account::input('lsd_auth_flow', $_GET);
        if (self::$rendering || self::$rendered || LSD_Shortcodes_Auth::inside_dropdown() || $token === '') return false;
        $data = LSD_Auth_State::read('view', $token);
        $result = self::$page_result;
        if (($result['token'] ?? '') === $token && !empty($result['data'])) $data = $result['data'];
        if (is_user_logged_in() && !empty($data['context']['inline_passkey'])) return false;
        $dropdown = strtolower((string) ($atts['layout'] ?? '')) === 'dropdown';
        return $dropdown === !empty($data['context']['dropdown']);
    }

    public static function frontend(array $atts = [], string $form = 'auth'): string
    {
        if (self::$rendered) return '';
        // Headers can contain several auth modules; present the active flow only once.
        self::$rendered = true;
        $token = LSD_Security_Account::input('lsd_auth_flow', $_GET);
        $data = LSD_Auth_State::read('view', $token);
        $result = self::$page_result;
        if (($result['token'] ?? '') === $token && !empty($result['data'])) $data = $result['data'];
        $instance = wp_unique_id('lsd-native-flow-');
        (new LSD_Assets())->site(true);
        self::assets();
        ob_start();
        include lsd_template('auth/native.php');
        $markup = ob_get_clean();
        self::$rendering = true;
        try { return (new LSD_Shortcodes_Auth())->security_interface($markup, $atts, $form); }
        finally { self::$rendering = false; }
    }

    public static function show(array $data, array $context): void
    {
        if (($data['stage'] ?? '') === 'register')
        {
            $auth = new LSD_Shortcodes_Auth();
            $page = $auth->get_login_url() ?: $auth->get_register_url();
            if ($page !== '')
            {
                $context['return'] = self::local_url($page);
                $context['dropdown'] = false;
            }
        }
        $token = LSD_Auth_State::issue('view', $data + ['context' => $context], 1800);
        if ($token === '') wp_die(esc_html__('Enable cookies and try again.', 'listdom'));
        $return = $context['return'] ?? self::url('screen');
        if (!empty($context['inline_passkey'])) $return = explode('#', $return, 2)[0] . '#lsd-account-passkeys';
        wp_safe_redirect(add_query_arg('lsd_auth_flow', $token, self::local_url($return)));
        exit;
    }

    public static function failure(WP_Error $error, array $context = [], string $provider = '', bool $json = false): void
    {
        $message = $error->get_error_message();
        if ($provider !== '' && isset(LSD_Auth_Providers::all()[$provider])) $message = LSD_Auth_Providers::all()[$provider]['label'] . ': ' . $message;
        do_action('lsd_auth_failed', $error->get_error_code(), $provider);
        if ($json)
        {
            $data = $error->get_error_data();
            wp_send_json(['success' => 0, 'message' => $message, 'countdown' => is_array($data) ? ($data['countdown'] ?? null) : null]);
        }
        self::show(['stage' => 'message', 'message' => $message, 'error' => true], $context);
    }

    public function route(): void
    {
        $callback_provider = self::callback_provider();
        $action = $callback_provider !== '' ? 'callback' : LSD_Security_Account::input('lsd_auth_action', $_GET);
        $flow = LSD_Security_Account::input('lsd_auth_flow', $_GET);
        if ($action === '' && $flow === '') return;
        if (!defined('DONOTCACHEPAGE')) define('DONOTCACHEPAGE', true);
        nocache_headers();
        header('Referrer-Policy: no-referrer');
        if ($action === 'callback') $this->callback($callback_provider);
        if ($action === 'resume') $this->resume();
        if ($action === 'passkey') LSD_Auth_Passkeys::request();
        if ($action === 'passkey-complete') LSD_Auth_Passkeys::complete();
        if ($action === 'diagnose') LSD_Auth_Settings::diagnose();
        if ($action === 'start' || $action === 'magic' || $action === 'manage' || $action === 'reauth')
        {
            $inline_passkey = $action === 'manage' && LSD_Security_Account::input('operation') === 'passkey_create:new' && LSD_Security_Account::input('reauth_provider') === '';
            $json = ($action === 'magic' || $inline_passkey) && ($_SERVER['HTTP_ACCEPT'] ?? '') === 'application/json';
            $fail = static function (WP_Error $error, array $context = []) use ($json): void
            {
                self::failure($error, $context, '', $json);
            };
            if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST' || !wp_verify_nonce(LSD_Security_Account::input('_wpnonce'), 'lsd_native_auth')) $fail(LSD_Auth_State::expired());
            $context = self::unsign(LSD_Security_Account::input('context'));
            if (!$context) $fail(LSD_Auth_State::expired());
            $limited = LSD_Security_Rate::check('native_start', LSD_Auth_State::browser(true));
            // Starts use login quotas, not mail cooldowns; see the shared rate service.
            if (is_wp_error($limited)) $fail($limited, $context);
            if ($action === 'magic')
            {
                $result = LSD_Auth_Magic::request(LSD_Security_Account::input('identifier'), $context);
                if (is_wp_error($result)) $fail($result, $context);
                $message = LSD_Security::recovery_message();
                if ($json) wp_send_json(['success' => 1, 'message' => $message]);
                self::show(['stage' => 'message', 'message' => $message], $context);
            }
            if ($action === 'manage' || $action === 'reauth') LSD_Auth_Accounts::request($action, $context, $json);
            if (is_user_logged_in()) self::failure(new WP_Error('lsd_auth_signed_in', __('You are already signed in. Manage connections from Account Security.', 'listdom')), $context);
            $this->start(sanitize_key(LSD_Security_Account::input('provider')), $context);
        }
        if ($flow !== '' && ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST')
        {
            $data = LSD_Auth_State::read('view', $flow);
            if (!$data || !wp_verify_nonce(LSD_Security_Account::input('_wpnonce'), 'lsd_native_view_' . $flow)) self::$page_result = ['error' => LSD_Auth_State::expired()];
            else
            {
                $result = ($data['stage'] ?? '') === 'register' ? LSD_Auth_Registration::submit($flow, $data, $_POST) : (($data['stage'] ?? '') === 'magic' ? LSD_Auth_Magic::confirm($flow, $data) : LSD_Auth_State::expired());
                if (is_wp_error($result))
                {
                    // Validation can be retried only while this browser still owns an unused view.
                    // Once consumed, never offer the original registration form again.
                    if (($data['stage'] ?? '') === 'register' && !LSD_Auth_State::read('view', $flow)) $data = ['stage' => 'message', 'restart' => true, 'context' => $data['context']];
                    self::$page_result = ['error' => $result, 'token' => $flow, 'data' => $data];
                }
                else self::$page_result = ['message' => $result['message'] ?? __('Request completed.', 'listdom'), 'token' => $flow, 'data' => $data];
            }
        }
        if ($action === 'verify-email') LSD_Auth_Registration::email_screen();
        if ($action === 'magic-confirm') LSD_Auth_Magic::screen();
        if ($action === 'screen')
        {
            get_header();
            echo $flow === '' ? (new LSD_Shortcodes_Auth())->auth() : self::frontend();
            get_footer();
            exit;
        }
    }

    public function start(string $provider, array $context, array $operation = []): void
    {
        if (!is_ssl() || !LSD_Auth_Providers::enabled($provider)) self::failure(LSD_Auth_Providers::error('disabled'), $context, $provider);
        $discovery = LSD_Auth_Providers::discovery($provider);
        if (is_wp_error($discovery)) self::failure($discovery, $context, $provider);
        $definition = LSD_Auth_Providers::all()[$provider];
        $data = ['provider' => $provider, 'nonce' => bin2hex(random_bytes(32)), 'pkce' => $definition['pkce'] ? LSD_Auth_Providers::encode(random_bytes(32)) : '', 'config' => LSD_Auth_Providers::fingerprint($provider), 'context' => $context, 'operation' => $operation, 'started' => time()];
        $state = LSD_Auth_State::issue('oidc', $data, $operation ? 300 : 600);
        if ($state === '') self::failure(LSD_Auth_State::expired(), $context);
        $args = ['client_id' => LSD_Auth_Providers::config($provider)['client_id'], 'response_type' => 'code', 'scope' => $definition['scopes'], 'redirect_uri' => self::url('callback', ['provider' => $provider]), 'state' => $state, 'nonce' => $data['nonce']];
        if ($data['pkce'] !== '') $args += ['code_challenge_method' => 'S256', 'code_challenge' => LSD_Auth_Providers::encode(hash('sha256', $data['pkce'], true))];
        if ($provider === 'apple') $args['response_mode'] = 'form_post';
        if ($operation) $args += ['prompt' => 'login', 'max_age' => 0];
        wp_redirect(add_query_arg($args, $discovery['authorization_endpoint']));
        exit;
    }

    private function callback(string $provider): void
    {
        $input = ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' ? $_POST : $_GET;
        $state = LSD_Security_Account::input('state', $input);
        if ($provider === 'apple' && ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST')
        {
            // SameSite=Lax cookies are absent on Apple's cross-site POST. Do not authenticate here.
            $pending = LSD_Security_State::read('native_oidc', $state);
            if (!$pending || $pending['provider'] !== $provider) self::failure(LSD_Auth_State::expired());
            $payload = LSD_Auth_State::seal(wp_json_encode(['code' => LSD_Security_Account::input('code', $input), 'error' => LSD_Security_Account::input('error', $input)]));
            $handoff = LSD_Security_State::issue('native_apple', ['state' => $state, 'payload' => $payload], 60);
            wp_safe_redirect(self::url('resume', ['handoff' => $handoff]), 303);
            exit;
        }
        $this->finish_callback($provider, $state, LSD_Security_Account::input('code', $input), LSD_Security_Account::input('error', $input));
    }

    private function resume(): void
    {
        $token = LSD_Security_Account::input('handoff', $_GET);
        $handoff = LSD_Security_State::read('native_apple', $token);
        if (!$handoff || !LSD_Auth_State::read('oidc', $handoff['state'])) self::failure(LSD_Auth_State::expired());
        if (!LSD_Security_State::consume('native_apple', $token)) self::failure(LSD_Auth_State::expired());
        $payload = json_decode(LSD_Auth_State::open($handoff['payload']), true);
        if (!is_array($payload)) self::failure(LSD_Auth_State::expired());
        $this->finish_callback('apple', $handoff['state'], $payload['code'], $payload['error']);
    }

    private function finish_callback(string $provider, string $token, string $code, string $error): void
    {
        $state = LSD_Auth_State::read('oidc', $token);
        if (!$state || $state['provider'] !== $provider || !LSD_Auth_Providers::enabled($provider) || !hash_equals($state['config'], LSD_Auth_Providers::fingerprint($provider))) self::failure(LSD_Auth_State::expired());
        if (!LSD_Auth_State::consume('oidc', $token)) self::failure(LSD_Auth_State::expired(), $state['context']);
        if ($error !== '' || $code === '') self::failure(new WP_Error('lsd_auth_cancelled', __('Sign-in was cancelled or declined. You can try again.', 'listdom')), $state['context'], $provider);
        $identity = LSD_Auth_Providers::exchange($provider, $code, $state);
        if (is_wp_error($identity)) self::failure($identity, $state['context'], $provider);
        if ($state['operation']) LSD_Auth_Accounts::callback($state, $identity);
        if (is_user_logged_in()) self::failure(new WP_Error('lsd_auth_session_changed', __('Your sign-in session changed. Please start again.', 'listdom')), $state['context']);
        $linked = LSD_Auth_Store::identity($identity['issuer'], $identity['subject']);
        if (is_wp_error($linked)) self::failure($linked, $state['context'], $provider);
        if ($linked)
        {
            if ($linked['provider'] !== $provider) self::failure(LSD_Auth_State::expired(), $state['context']);
            $result = self::complete((int) $linked['user_id'], $state['context'], $provider);
            self::failure($result, $state['context'], $provider);
        }
        if (!LSD_Security::registration_allowed()) self::failure(new WP_Error('lsd_registration_disabled', __('Public registration is disabled. Sign in to an existing account to connect this identity.', 'listdom')), $state['context']);
        if ($identity['email'] !== '' && email_exists($identity['email'])) self::failure(LSD_Auth_Registration::collision(), $state['context']);
        self::show(['stage' => 'register', 'identity' => $identity, 'config' => $state['config']], $state['context']);
    }

    public static function eligible(int $user_id, array $context = [], string $method = '')
    {
        $user = get_userdata($user_id);
        if (!LSD_Security_API::valid_user($user) || (is_multisite() && !is_user_member_of_blog($user_id))) return new WP_Error('lsd_auth_ineligible', __('This account cannot sign in here. Complete email verification or contact the administrator.', 'listdom'));
        if (!empty($context['role']) && !in_array($context['role'], $user->roles, true)) return new WP_Error('lsd_auth_role', __('This account cannot use this role-specific sign-in form.', 'listdom'));
        $valid = apply_filters('wp_authenticate_user', $user, '');
        if (is_wp_error($valid)) return $valid;
        // Supply an already verified principal, preserving WordPress/site authenticate policies.
        // Our second-factor adapter defers only inside this scope; finish() always enforces it.
        $previous = self::$primary_policy;
        self::$primary_policy = true;
        try { $valid = apply_filters('authenticate', $valid, $user->user_login, ''); }
        finally { self::$primary_policy = $previous; }
        if (is_wp_error($valid)) return $valid;
        $valid = apply_filters('lsd_auth_user_eligible', $valid, $method, $context);
        return $valid instanceof WP_User && $valid->ID === $user->ID ? $valid : (is_wp_error($valid) ? $valid : new WP_Error('lsd_auth_denied', __('Sign-in is not available for this account.', 'listdom')));
    }

    public static function complete(int $user_id, array $context, string $method)
    {
        $result = self::finish($user_id, $context, $method);
        if (is_wp_error($result)) return $result;
        wp_safe_redirect($result['redirect']);
        exit;
    }

    /** Internal transport-neutral completion after a validated primary identity proof. */
    public static function finish(int $user_id, array $context, string $method)
    {
        $enabled = isset(LSD_Auth_Providers::all()[$method]) ? LSD_Auth_Providers::enabled($method) : ($method === 'magic' ? LSD_Auth_Magic::enabled() : ($method === 'passkey' && LSD_Auth_Passkeys::enabled()));
        if (!$enabled) return new WP_Error('lsd_auth_method_disabled', __('This sign-in method is no longer enabled.', 'listdom'));
        $user = self::eligible($user_id, $context, $method);
        if (is_wp_error($user)) return $user;
        if (isset(LSD_Auth_Providers::all()[$method])) $context['_provider_config'] = LSD_Auth_Providers::fingerprint($method);
        $destination = (new LSD_Shortcodes_Auth())->resolve_redirect($context['form'] ?? 'login', $user, $context['redirect'] ?? '', !empty($context['explicit']));
        if (LSD_Security_API::protected_account($user_id))
        {
            $challenge = LSD_Security_Challenge::issue_browser($user, $destination);
            if ($challenge->get_error_code() !== 'lsd_totp_required') return $challenge;
            // Carry the validated context through the existing second-factor verifier.
            $token = $challenge->get_error_data()['challenge'];
            $data = LSD_Security_State::read('challenge', $token);
            $data['native_auth'] = ['context' => $context, 'method' => $method];
            update_option(LSD_Security_State::key('challenge', $token), $data, false);
            return ['redirect' => LSD_Security_Challenge::url($challenge), 'challenge' => $token];
        }
        wp_set_current_user($user_id);
        wp_set_auth_cookie($user_id, false, is_ssl());
        do_action('wp_login', $user->user_login, $user);
        do_action('lsd_auth_completed', $user_id, $method);
        return ['redirect' => wp_validate_redirect(apply_filters('login_redirect', $destination, $destination, $user), home_url('/'))];
    }
}
