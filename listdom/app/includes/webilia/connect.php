<?php

use Webilia\Connect\Client;
use Webilia\Connect\WordPress\WordPressHttpClient;
use Webilia\Connect\WordPress\WordPressStorage;

class LSD_Webilia_Connect
{
    private const BROKER_INTEGRATION = 'listdom';
    private const WINDOWS_STORAGE_KEY_OPTION = 'lsd_webilia_connect_windows_key';
    private const CONNECTION_STATUS_TRANSIENT = 'lsd_webilia_connect_status';
    private const CONNECTION_STATUS_CACHE_TTL = 300;
    private static ?Client $client = null;
    private static ?bool $connection_status = null;
    private static array $authorizations = [];
    private static string $error = '';

    public static function enabled(): bool
    {
        return (bool) apply_filters('lsd_webilia_connect_enabled', true);
    }

    public static function isConnected(): bool
    {
        if (!self::enabled()) return false;
        if (self::$connection_status !== null) return self::$connection_status;

        try
        {
            $client = self::client();
            if (!$client->isConnected()) return self::rememberConnectionStatus(false);

            if ((int) get_transient(self::CONNECTION_STATUS_TRANSIENT) === 1) return self::$connection_status = true;

            if (method_exists($client, 'verifyConnection')) {
                return self::rememberConnectionStatus($client->verifyConnection());
            }

            $connection = $client->connection();
            if (!$connection) return self::rememberConnectionStatus(false);

            $response = wp_remote_post(rtrim(defined('LSD_WEBILIA_CONNECT_API') ? LSD_WEBILIA_CONNECT_API : 'https://api.webilia.com', '/') . '/v1/connect/status', [
                'timeout' => 15,
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $connection->credential(),
                ],
                'body' => wp_json_encode([]),
            ]);

            if (is_wp_error($response))
            {
                self::$error = $response->get_error_message();
                return self::rememberConnectionStatus(true);
            }

            $status_code = (int) wp_remote_retrieve_response_code($response);
            if ($status_code === 401)
            {
                (new WordPressStorage())->forgetConnectionWithCredential($connection->credential());
                return self::rememberConnectionStatus(false);
            }

            if ($status_code < 200 || $status_code >= 300)
            {
                self::$error = 'Webilia Connect status verification failed.';
                return self::rememberConnectionStatus(true);
            }

            $payload = json_decode((string) wp_remote_retrieve_body($response), true);
            if (!is_array($payload) || (array_key_exists('success', $payload) && $payload['success'] !== true))
            {
                self::$error = is_array($payload) ? (string) ($payload['message'] ?? 'Webilia Connect status verification failed.') : 'Webilia Connect status verification failed.';
                return self::rememberConnectionStatus(true);
            }

            $data = is_array($payload) && isset($payload['data']) && is_array($payload['data']) ? $payload['data'] : [];
            $connected = ($data['status'] ?? '') === 'active' && (int) ($data['connection_id'] ?? 0) === (int) $connection->id();

            if (!$connected) (new WordPressStorage())->forgetConnectionWithCredential($connection->credential());

            return self::rememberConnectionStatus($connected);
        }
        catch (Throwable $e)
        {
            self::$error = $e->getMessage();
            return self::$connection_status = false;
        }
    }

    public static function isAllowed(string $basename, string $type = 'use'): bool
    {
        if (!self::enabled() || !self::integration($basename) || !self::isConnected()) return false;
        $cache_key = $basename . '|' . $type;
        if (isset(self::$authorizations[$cache_key])) return self::$authorizations[$cache_key];
        try
        {
            $authorization = self::client()->authorize(self::integration($basename), self::capability($basename, $type));
            self::$authorizations[$cache_key] = $authorization->allowed();
        }
        catch (Throwable $e)
        {
            self::$error = $e->getMessage();
            self::$authorizations[$cache_key] = false;
        }

        return self::$authorizations[$cache_key];
    }

    public static function connectUrl(string $return_url = ''): string
    {
        $url = add_query_arg('action', 'lsd_webilia_connect_start', admin_url('admin-post.php'));

        return wp_nonce_url(
            add_query_arg('return_to', self::returnUrl($return_url), $url),
            'lsd_webilia_connect_start'
        );
    }

    public static function begin(string $return_url = ''): string
    {
        $return_url = self::returnUrl($return_url);
        self::rememberReturnUrl($return_url);

        try
        {
            return self::client()->begin(self::BROKER_INTEGRATION, get_site_url(), $return_url);
        }
        catch (Throwable $e)
        {
            self::forgetReturnUrl();

            throw $e;
        }
    }

    public static function returnUrl(string $return_url = ''): string
    {
        $default = admin_url('admin.php?page=listdom-connect&tab=connect');
        $return_url = trim($return_url) ?: $default;
        $return_url = wp_validate_redirect($return_url, $default);

        return strpos($return_url, admin_url()) === 0 ? $return_url : $default;
    }

    public static function currentReturnUrl(): string
    {
        $request_uri = isset($_SERVER['REQUEST_URI']) ? (string) wp_unslash($_SERVER['REQUEST_URI']) : '';
        if ($request_uri === '') return self::returnUrl();

        return self::returnUrl(home_url(remove_query_arg(['webilia_connect_code', 'webilia_connect_state'], $request_uri)));
    }

    public static function pendingReturnUrl(): string
    {
        $return_url = get_transient(self::returnUrlTransientKey());
        self::forgetReturnUrl();

        return self::returnUrl(is_string($return_url) ? $return_url : self::currentReturnUrl());
    }

    public static function notice(): array
    {
        $key = 'lsd_webilia_connect_notice_' . get_current_user_id();
        $notice = get_transient($key);
        delete_transient($key);

        return is_array($notice) ? $notice : [];
    }

    public static function setNotice(string $type, string $message): void
    {
        set_transient('lsd_webilia_connect_notice_' . get_current_user_id(), compact('type', 'message'), MINUTE_IN_SECONDS);
    }

    private static function rememberReturnUrl(string $return_url): void
    {
        set_transient(self::returnUrlTransientKey(), $return_url, 10 * MINUTE_IN_SECONDS);
    }

    private static function forgetReturnUrl(): void
    {
        delete_transient(self::returnUrlTransientKey());
    }

    private static function returnUrlTransientKey(): string
    {
        return 'lsd_webilia_connect_return_url_' . get_current_user_id();
    }

    /**
     * @throws Throwable
     */
    public static function complete(string $code, string $state): void
    {
        self::client()->complete($code, $state);

        delete_transient(self::CONNECTION_STATUS_TRANSIENT);
        self::$connection_status = null;
        self::$authorizations = [];
        self::$error = '';
    }

    /**
     * @throws Throwable
     */
    public static function disconnect(): void
    {
        try
        {
            self::client()->disconnect();
            delete_transient(self::CONNECTION_STATUS_TRANSIENT);
            self::$connection_status = null;
            self::$authorizations = [];
            self::$error = '';
        }
        catch (Throwable $e)
        {
            // Keep the local credential intact when the API cannot confirm a
            // revocation. This is intentional: a failed disconnect must never
            // make the UI claim that a website was disconnected when it was not.
            self::$error = $e->getMessage();

            throw $e;
        }
    }

    public static function error(): string
    {
        return self::$error;
    }

    private static function rememberConnectionStatus(bool $connected): bool
    {
        self::$connection_status = $connected;

        if ($connected) set_transient(self::CONNECTION_STATUS_TRANSIENT, 1, self::CONNECTION_STATUS_CACHE_TTL);
        else delete_transient(self::CONNECTION_STATUS_TRANSIENT);

        return $connected;
    }

    public static function forgetAuthorization(string $basename): void
    {
        unset(self::$authorizations[$basename . '|use'], self::$authorizations[$basename . '|update']);
    }

    public static function integration(string $basename): string
    {
        if (preg_match('#^(listdom-[^/]+)/.+\.php$#', $basename, $matches) !== 1) return '';
        return 'listdom:' . $matches[1];
    }

    public static function capability(string $basename, string $type): string
    {
        $integration = self::integration($basename);
        return $integration === '' ? '' : $integration . '.' . $type;
    }

    public static function updateClient(string $basename, string $version): void
    {
        if (!self::enabled() || self::integration($basename) === '') return;

        // Update delivery is an enhancement to the existing licensing path.
        // Never let a missing or incompatible SDK build prevent Listdom (or a
        // legacy license activation) from loading.
        $update_client = '\\Webilia\\Connect\\WordPress\\UpdateClient';
        if (!class_exists($update_client))
        {
            self::$error = 'Webilia Connect update support is unavailable.';
            return;
        }

        try
        {
            new $update_client(self::client(), self::integration($basename), $version, $basename, LSD_VERSION, self::capability($basename, 'update'));
        }
        catch (Throwable $e)
        {
            // Keep the established update client and all legacy licensing
            // behaviour available when Connect cannot initialize.
            self::$error = $e->getMessage();
        }
    }

    /**
     * Get a signed package for an add-on that the connected account can use.
     *
     * The update endpoint performs its own update-capability check. Checking
     * the use capability first keeps install authorization explicit and avoids
     * treating update delivery as feature access.
     *
     * @throws RuntimeException
     */
    public static function package(string $basename): array
    {
        $integration = self::integration($basename);
        if (!self::enabled() || $integration === '' || !self::isConnected())
        {
            throw new RuntimeException('Webilia Connect is unavailable.');
        }

        if (!self::isAllowed($basename))
        {
            throw new RuntimeException('Your connected Webilia account is not entitled to this product.');
        }

        try
        {
            $package = self::client()->update($integration, $basename, '', LSD_VERSION);
        }
        catch (Throwable $e)
        {
            self::$error = $e->getMessage();
            throw new RuntimeException('Webilia could not provide an installation package.', 0, $e);
        }

        if (($package['allowed'] ?? null) !== true || empty($package['download_link']) || !is_string($package['download_link']))
        {
            throw new RuntimeException('No installation package is available for this product.');
        }

        return $package;
    }

    /**
     * Retrieve the API-owned Overture Places taxonomy for the connected website.
     * The later search UI owns the short-lived WordPress transient cache.
     *
     * @throws RuntimeException
     */
    public static function overtureCategories(array $query = []): array
    {
        if (!self::enabled() || !self::isConnected())
        {
            throw new RuntimeException('Webilia Connect is unavailable.');
        }

        $client = self::client();
        if (!method_exists($client, 'overturePlaceCategories'))
        {
            throw new RuntimeException('Overture Places requires Webilia Connect SDK 1.1.0 or newer.');
        }

        try
        {
            return $client->overturePlaceCategories($query);
        }
        catch (Throwable $e)
        {
            self::$error = $e->getMessage();
            throw new RuntimeException('Webilia could not provide Overture categories.', 0, $e);
        }
    }

    /**
     * Execute one bounded, metered Overture Places search for the connected website.
     *
     * @throws RuntimeException
     */
    public static function overturePlacesSearch(array $request): array
    {
        if (!self::enabled() || !self::isConnected())
        {
            throw new RuntimeException('Webilia Connect is unavailable.');
        }

        $client = self::client();
        if (!method_exists($client, 'overturePlacesSearch'))
        {
            throw new RuntimeException('Overture Places requires Webilia Connect SDK 1.1.0 or newer.');
        }

        try
        {
            return $client->overturePlacesSearch($request);
        }
        catch (Throwable $e)
        {
            self::$error = $e->getMessage();
            throw new RuntimeException('Webilia could not complete the Overture Places search.', 0, $e);
        }
    }

    private static function client(): Client
    {
        if (self::$client instanceof Client) return self::$client;

        self::ensureStorageKey();
        self::$client = new Client(new WordPressHttpClient(), new WordPressStorage(), defined('LSD_WEBILIA_CONNECT_API') ? LSD_WEBILIA_CONNECT_API : 'https://api.webilia.com', get_site_url());
        return self::$client;
    }

    private static function ensureStorageKey(): void
    {
        if (defined('WEBILIA_CONNECT_KEY') || PHP_OS_FAMILY !== 'Windows') return;

        // Windows does not expose the POSIX permissions required by the SDK's
        // file-based key storage. Keep a dedicated key in the database so a
        // WordPress salt rotation does not invalidate the saved connection.
        $key = get_option(self::WINDOWS_STORAGE_KEY_OPTION, '');
        if (!is_string($key) || $key === '')
        {
            $key = wp_generate_password(64, true, true);
            add_option(self::WINDOWS_STORAGE_KEY_OPTION, $key, '', false);
        }

        define('WEBILIA_CONNECT_KEY', $key);
    }
}
