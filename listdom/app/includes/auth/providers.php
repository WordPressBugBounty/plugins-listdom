<?php

/** Provider metadata is server-owned; request parameters cannot choose endpoints. */
class LSD_Auth_Providers
{
    public static function all(): array
    {
        return apply_filters('lsd_auth_providers', [
            'google' => ['label' => 'Google', 'discovery' => 'https://accounts.google.com/.well-known/openid-configuration', 'hosts' => ['accounts.google.com', 'oauth2.googleapis.com', 'www.googleapis.com'], 'issuer' => 'https://accounts.google.com', 'scopes' => 'openid email profile', 'pkce' => true],
            'yahoo' => ['label' => 'Yahoo', 'discovery' => 'https://api.login.yahoo.com/.well-known/openid-configuration', 'hosts' => ['api.login.yahoo.com'], 'issuer' => 'https://api.login.yahoo.com', 'scopes' => 'openid email profile', 'pkce' => true, 'reauth' => true],
            'apple' => ['label' => 'Apple', 'discovery' => 'https://appleid.apple.com/.well-known/openid-configuration', 'hosts' => ['appleid.apple.com', 'account.apple.com'], 'issuer' => 'https://appleid.apple.com', 'scopes' => 'name email', 'pkce' => false],
            'microsoft' => ['label' => 'Microsoft', 'discovery' => 'https://login.microsoftonline.com/{tenant}/v2.0/.well-known/openid-configuration', 'hosts' => ['login.microsoftonline.com'], 'issuer' => '', 'scopes' => 'openid email profile', 'pkce' => true, 'reauth' => true],
        ]);
    }

    public static function available(): bool
    {
        return LSD_Security_Totp::available() && extension_loaded('mbstring') && function_exists('openssl_sign') && function_exists('openssl_verify');
    }

    public static function config(string $provider): array
    {
        $auth = LSD_Options::auth();
        return array_replace(['enabled' => 0, 'client_id' => '', 'tenant' => 'common', 'team_id' => '', 'key_id' => ''], (array) ($auth['social_login'][$provider] ?? []));
    }

    public static function enabled(string $provider): bool
    {
        $config = self::config($provider);
        return isset(self::all()[$provider]) && !empty($config['enabled']) && $config['client_id'] !== '' && ($provider !== 'apple' || ($config['team_id'] !== '' && $config['key_id'] !== '')) && self::available() && self::secret($provider) !== '';
    }

    public static function secret(string $provider): string
    {
        $secrets = get_option('lsd_auth_secrets', []);
        return LSD_Auth_State::open(is_array($secrets) ? ($secrets[$provider] ?? '') : '');
    }

    public static function fingerprint(string $provider): string
    {
        return hash_hmac('sha256', wp_json_encode(self::config($provider)) . '|' . self::secret($provider), wp_salt('auth'));
    }

    public static function sanitize(array $settings): array
    {
        $result = [];
        foreach (self::all() as $provider => $definition)
        {
            $value = isset($settings[$provider]) && is_array($settings[$provider]) ? $settings[$provider] : [];
            $tenant = is_string($value['tenant'] ?? null) ? $value['tenant'] : 'common';
            if (!in_array($tenant, ['common', 'organizations', 'consumers'], true) && !preg_match('/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/iD', $tenant)) $tenant = 'common';
            $result[$provider] = ['enabled' => (int) !empty($value['enabled']), 'client_id' => sanitize_text_field(is_string($value['client_id'] ?? null) ? $value['client_id'] : ''), 'tenant' => $tenant];
            foreach (['team_id', 'key_id'] as $key) $result[$provider][$key] = sanitize_text_field(is_string($value[$key] ?? null) ? $value[$key] : '');
        }
        return $result;
    }

    public static function endpoint(string $url, array $definition): bool
    {
        $parts = wp_parse_url($url);
        return is_array($parts) && ($parts['scheme'] ?? '') === 'https' && in_array($parts['host'] ?? '', $definition['hosts'], true) && empty($parts['user']) && empty($parts['pass']) && empty($parts['fragment']) && (!isset($parts['port']) || $parts['port'] === 443);
    }

    public static function request(string $url, array $definition, ?array $body = null, array $headers = [])
    {
        if (!self::endpoint($url, $definition)) return self::error('endpoint');
        $args = ['timeout' => 15, 'redirection' => 0, 'sslverify' => true, 'limit_response_size' => 262144, 'headers' => $headers + ['Accept' => 'application/json']];
        $response = $body === null ? wp_remote_get($url, $args) : wp_remote_post($url, $args + ['body' => $body]);
        if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) return self::error('connection');
        $data = json_decode(wp_remote_retrieve_body($response), true);
        return is_array($data) ? $data : self::error('response');
    }

    public static function discovery(string $provider)
    {
        $definition = self::all()[$provider] ?? null;
        if (!$definition) return self::error('provider');
        $url = str_replace('{tenant}', rawurlencode(self::config($provider)['tenant']), $definition['discovery']);
        $cache = 'lsd_oidc_' . md5($url);
        $data = get_transient($cache);
        if (is_array($data)) return $data;
        $data = self::request($url, $definition);
        if (is_wp_error($data)) return $data;
        foreach (['authorization_endpoint', 'token_endpoint', 'jwks_uri'] as $key)
        {
            if (!isset($data[$key]) || !is_string($data[$key]) || !self::endpoint($data[$key], $definition)) return self::error('discovery');
        }
        $issuer = $provider === 'microsoft' ? 'https://login.microsoftonline.com/{tenantid}/v2.0' : $definition['issuer'];
        if ($provider === 'microsoft' && self::config($provider)['tenant'] === 'consumers') $issuer = 'https://login.microsoftonline.com/9188040d-6c67-4c5b-b112-36a304b66dad/v2.0';
        if ($provider === 'microsoft' && !in_array(self::config($provider)['tenant'], ['common', 'consumers', 'organizations'], true)) $issuer = str_replace('{tenantid}', self::config($provider)['tenant'], $issuer);
        if (($data['issuer'] ?? '') !== $issuer) return self::error('issuer');
        set_transient($cache, $data, HOUR_IN_SECONDS);
        return $data;
    }

    public static function client_secret(string $provider): string
    {
        if ($provider !== 'apple') return self::secret($provider);
        $config = self::config($provider);
        $signer = \Lcobucci\JWT\Signer\Ecdsa\Sha256::create();
        $key = \Lcobucci\JWT\Signer\Key\InMemory::plainText(self::secret($provider));
        $jwt = \Lcobucci\JWT\Configuration::forAsymmetricSigner($signer, $key, $key);
        $now = new DateTimeImmutable();
        return $jwt->builder(\Lcobucci\JWT\Encoding\ChainedFormatter::withUnixTimestampDates())->withHeader('kid', $config['key_id'])->issuedBy($config['team_id'])->relatedTo($config['client_id'])->permittedFor('https://appleid.apple.com')->issuedAt($now)->expiresAt($now->modify('+5 minutes'))->getToken($signer, $key)->toString();
    }

    public static function exchange(string $provider, string $code, array $state)
    {
        $discovery = self::discovery($provider);
        if (is_wp_error($discovery)) return $discovery;
        try
        {
            $body = ['grant_type' => 'authorization_code', 'code' => $code, 'redirect_uri' => LSD_Auth::url('callback', ['provider' => $provider]), 'client_id' => self::config($provider)['client_id'], 'client_secret' => self::client_secret($provider)];
            if (!empty($state['pkce'])) $body['code_verifier'] = $state['pkce'];
            $headers = [];
            if ($provider === 'yahoo')
            {
                $headers['Authorization'] = 'Basic ' . base64_encode(rawurlencode($body['client_id']) . ':' . rawurlencode($body['client_secret']));
                unset($body['client_secret'], $body['client_id']);
            }
            $tokens = self::request($discovery['token_endpoint'], self::all()[$provider], $body, $headers);
            if (is_wp_error($tokens)) return $tokens;
            if (!is_string($tokens['id_token'] ?? null)) return self::error('token');
            return self::verify($provider, $tokens['id_token'], $state, $discovery);
        }
        catch (Throwable $e) { return self::error('credentials'); }
    }

    public static function verify(string $provider, string $encoded, array $state, array $discovery)
    {
        try
        {
            $parser = new \Lcobucci\JWT\Token\Parser(new \Lcobucci\JWT\Encoding\JoseEncoder());
            $token = $parser->parse($encoded);
            $algorithm = $token->headers()->get('alg');
            if (!$token instanceof \Lcobucci\JWT\Token\Plain || !in_array($algorithm, $provider === 'yahoo' ? ['RS256', 'ES256'] : ['RS256'], true) || $token->headers()->has('crit')) return self::error('signature');
            $kid = $token->headers()->get('kid');
            if (!is_string($kid) || $kid === '') return self::error('signature');
            $key = self::key($provider, $discovery['jwks_uri'], $kid, $algorithm);
            if (is_wp_error($key)) return $key;
            $signer = $algorithm === 'ES256' ? \Lcobucci\JWT\Signer\Ecdsa\Sha256::create() : new \Lcobucci\JWT\Signer\Rsa\Sha256();
            if (!$signer->verify($token->signature()->hash(), $token->payload(), \Lcobucci\JWT\Signer\Key\InMemory::plainText($key))) return self::error('signature');
            $claims = $token->claims();
            $config = self::config($provider);
            $issuer = self::all()[$provider]['issuer'];
            if ($provider === 'microsoft')
            {
                $tenant = $claims->get('tid');
                if (!is_string($tenant) || !preg_match('/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/iD', $tenant)) return self::error('issuer');
                $personal = '9188040d-6c67-4c5b-b112-36a304b66dad';
                if (($config['tenant'] === 'consumers' && $tenant !== $personal) || ($config['tenant'] === 'organizations' && $tenant === $personal)) return self::error('tenant');
                if (!in_array($config['tenant'], ['common', 'consumers', 'organizations'], true) && strcasecmp($config['tenant'], $tenant) !== 0) return self::error('tenant');
                $issuer = 'https://login.microsoftonline.com/' . $tenant . '/v2.0';
                $key_issuer = self::$key_issuer;
                if ($key_issuer !== '' && str_replace('{tenantid}', $tenant, $key_issuer) !== $issuer) return self::error('issuer');
            }
            $actual_issuer = $claims->get('iss');
            if ($actual_issuer !== $issuer && !($provider === 'google' && $actual_issuer === 'accounts.google.com')) return self::error('issuer');
            $audience = $claims->get('aud');
            if (!is_array($audience) || !in_array($config['client_id'], $audience, true) || (count($audience) > 1 && $claims->get('azp') !== $config['client_id']) || ($claims->has('azp') && $claims->get('azp') !== $config['client_id'])) return self::error('audience');
            $now = time();
            foreach (['exp', 'iat'] as $claim) if (!$claims->get($claim) instanceof DateTimeImmutable) return self::error('expiry');
            if ($claims->get('exp')->getTimestamp() <= $now || $claims->get('iat')->getTimestamp() > $now + 60 || ($claims->has('nbf') && $claims->get('nbf')->getTimestamp() > $now + 60)) return self::error('expiry');
            if (!is_string($claims->get('nonce')) || !hash_equals($state['nonce'], $claims->get('nonce'))) return self::error('nonce');
            if (($state['operation']['phase'] ?? '') === 'reauth')
            {
                $auth_time = $claims->get('auth_time');
                if (!is_numeric($auth_time) || (int) $auth_time < $state['started'] - 60 || (int) $auth_time > $now + 60) return self::error('reauthentication');
            }
            $subject = $claims->get('sub');
            if (!is_string($subject) || $subject === '' || strlen($subject) > 1024) return self::error('subject');
            $email = $claims->get('email', '');
            // Normalize Google's two documented issuers only after validating the signed claims.
            return ['provider' => $provider, 'issuer' => $provider === 'google' ? 'https://accounts.google.com' : $actual_issuer, 'subject' => $subject, 'email' => is_string($email) && is_email($email) ? $email : '', 'email_verified' => in_array($claims->get('email_verified'), [true, 'true'], true), 'name' => is_string($claims->get('name')) ? sanitize_text_field($claims->get('name')) : ''];
        }
        catch (Throwable $e) { return self::error('token'); }
    }

    private static string $key_issuer = '';

    private static function key(string $provider, string $url, string $kid, string $algorithm)
    {
        $cache = 'lsd_jwks_' . md5($url);
        $keys = get_transient($cache);
        for ($attempt = 0; $attempt < 2; $attempt++)
        {
            if (!is_array($keys))
            {
                $keys = self::request($url, self::all()[$provider]);
                if (is_wp_error($keys)) return $keys;
                set_transient($cache, $keys, HOUR_IN_SECONDS);
            }
            foreach ((array) ($keys['keys'] ?? []) as $key)
            {
                if (($key['kid'] ?? '') !== $kid || ($key['kty'] ?? '') !== ($algorithm === 'ES256' ? 'EC' : 'RSA') || (isset($key['use']) && $key['use'] !== 'sig') || (isset($key['alg']) && $key['alg'] !== $algorithm)) continue;
                self::$key_issuer = is_string($key['issuer'] ?? null) ? $key['issuer'] : '';
                if ($algorithm === 'ES256')
                {
                    if (($key['crv'] ?? '') !== 'P-256' || !is_string($key['x'] ?? null) || !is_string($key['y'] ?? null)) return self::error('key');
                    $x = self::decode($key['x']);
                    $y = self::decode($key['y']);
                    if (strlen($x) !== 32 || strlen($y) !== 32) return self::error('key');
                    $spki = self::der(48, hex2bin('301306072a8648ce3d020106082a8648ce3d030107') . self::der(3, "\0\4" . $x . $y));
                    return "-----BEGIN PUBLIC KEY-----\n" . chunk_split(base64_encode($spki), 64, "\n") . "-----END PUBLIC KEY-----\n";
                }
                if (!is_string($key['n'] ?? null) || !is_string($key['e'] ?? null)) return self::error('key');
                $n = self::decode($key['n']);
                $e = self::decode($key['e']);
                if ($n === '' || $e === '') return self::error('key');
                // DER encoding only; parsing and signature verification belong to the JWT library.
                $integer = static function (string $value): string
                {
                    $value = ltrim($value, "\0");
                    return self::der(2, (ord($value[0]) & 0x80 ? "\0" : '') . $value);
                };
                $rsa = self::der(48, $integer($n) . $integer($e));
                $spki = self::der(48, hex2bin('300d06092a864886f70d0101010500') . self::der(3, "\0" . $rsa));
                return "-----BEGIN PUBLIC KEY-----\n" . chunk_split(base64_encode($spki), 64, "\n") . "-----END PUBLIC KEY-----\n";
            }
            // An unknown kid triggers one refresh, never an attacker-controlled URL.
            $refresh = 'lsd_jwks_refresh_' . md5($url);
            if (get_transient($refresh)) break;
            set_transient($refresh, 1, 60);
            $keys = false;
        }
        return self::error('key');
    }

    private static function der(int $tag, string $value): string
    {
        $length = strlen($value);
        $encoded = $length < 128 ? chr($length) : ($length < 256 ? chr(0x81) . chr($length) : chr(0x82) . pack('n', $length));
        return chr($tag) . $encoded . $value;
    }

    public static function encode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    public static function decode(string $value): string
    {
        $decoded = base64_decode(strtr($value, '-_', '+/'), true);
        return $decoded === false ? '' : $decoded;
    }

    public static function error(string $stage): WP_Error
    {
        if ($stage === 'reauthentication') return new WP_Error('lsd_oidc_reauthentication', __('The provider did not confirm a recent authentication. Use your current password or another connected provider to change account security.', 'listdom'));
        return new WP_Error('lsd_oidc_' . $stage, __('The identity provider could not be verified. Please try again or ask the administrator to check its connection settings.', 'listdom'));
    }
}
