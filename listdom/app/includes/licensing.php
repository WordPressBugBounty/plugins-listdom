<?php

use Webilia\WP\Plugin\Licensing;

class LSD_Licensing extends LSD_Base
{
    public const STATUS_INVALID = 0;
    public const STATUS_VALID = 1;
    public const STATUS_TRIAL = 2;
    public const STATUS_GRACE = 3;

    /**
     * Runtime cache to avoid multiple checks in a single request
     *
     * @var array<string,mixed>
     */
    private static $runtime = [];

    /**
     * @param string $basename
     * @return string
     */
    private static function getProductKey(string $basename): string
    {
        return 'lsd_product_validation_' . str_replace(['/', '-'], '_', $basename);
    }

    /**
     * @param string $basename
     * @return string
     */
    private static function getOptionKey(string $basename): string
    {
        return self::getProductKey($basename) . '_opt';
    }

    /**
     * @param string $basename
     * @param string $prefix
     * @return int
     */
    public static function isValid(string $basename, string $prefix): int
    {
        // Product Key
        $key = self::getProductKey($basename);

        // Already checked in this request
        if (isset(self::$runtime[$key])) return self::$runtime[$key];

        $option_key = self::getOptionKey($basename);

        $license_key_option = $prefix . '_purchase_code';
        $activation_id_option = $prefix . '_activation_id';

        // Validation Status
        $valid = self::STATUS_INVALID;

        // Cached Status (transient)
        $cached = get_transient($key);

        // Fallback to options when transients are disabled
        if (!is_numeric($cached))
        {
            $opt_cache = get_option($option_key, []);

            if (
                is_array($opt_cache) &&
                isset($opt_cache['value'], $opt_cache['expires']) &&
                $opt_cache['expires'] > current_time('timestamp')
            )
            {
                $cached = $opt_cache['value'];
            }
        }

        // Already Checked
        if (is_numeric($cached)) $valid = (int) $cached;
        // Check Validation
        else
        {
            // Webilia Licensing Server
            $licensing = new Licensing(
                $license_key_option,
                $activation_id_option,
                $basename,
                LSD_LICENSING_SERVER
            );

            // License is valid
            if ($licensing->isValid()) $valid = self::STATUS_VALID;

            // Check Trial
            if ($valid === self::STATUS_INVALID && self::isTrial($prefix)) $valid = self::STATUS_TRIAL;

            // Grace Period
            if ($valid === self::STATUS_INVALID && self::isGracePeriod($prefix)) $valid = self::STATUS_GRACE;

            // Status valid
            if ($valid === self::STATUS_VALID)
            {
                $expiry = 10 * DAY_IN_SECONDS;

                // Remove Grace Period
                delete_option($prefix . '_invalidated_at');
            }
            // Trial Period
            else if ($valid === self::STATUS_TRIAL) $expiry = DAY_IN_SECONDS;
            // Invalid
            else
            {
                $expiry = DAY_IN_SECONDS;

                // Start Grace Period
                $grace_started = add_option($prefix . '_invalidated_at', current_time('timestamp'));

                // Grace Period
                if ($grace_started) $valid = self::STATUS_GRACE;
            }

            // Persist cache in transients and options
            set_transient($key, $valid, $expiry);
            update_option(
                $option_key,
                ['value' => $valid, 'expires' => current_time('timestamp') + $expiry],
                false
            );
        }

        // Store in runtime cache
        self::$runtime[$key] = $valid;

        // Filter Validation
        return (int) apply_filters('lsd_licensing_validation', $valid, $basename, $prefix);
    }

    /**
     * Validate the license and return detailed result
     *
     * @param string $basename
     * @param string $prefix
     * @return array
     */
    public static function validate(string $basename, string $prefix): array
    {
        // Product Key for validation details
        $key = self::getProductKey($basename) . '_data';

        // Already checked in this request
        if (isset(self::$runtime[$key])) return self::$runtime[$key];

        // Option Key
        $option_key = $key . '_opt';

        $license_key_option = $prefix . '_purchase_code';
        $activation_id_option = $prefix . '_activation_id';

        // Cached Response (transient)
        $data = get_transient($key);

        // Fallback to options when transients are disabled
        if (!is_array($data))
        {
            $opt_cache = get_option($option_key, []);

            if (
                is_array($opt_cache) &&
                isset($opt_cache['value'], $opt_cache['expires']) &&
                $opt_cache['expires'] > current_time('timestamp')
            )
            {
                $data = $opt_cache['value'];
            }
        }

        // Already Checked
        if (!is_array($data))
        {
            // Webilia Licensing Server
            $licensing = new Licensing(
                $license_key_option,
                $activation_id_option,
                $basename,
                LSD_LICENSING_SERVER
            );

            // Validate License and get response
            $data = $licensing->validate();

            // Persist cache in transients and options
            set_transient($key, $data, WEEK_IN_SECONDS);
            update_option(
                $option_key,
                ['value' => $data, 'expires' => current_time('timestamp') + WEEK_IN_SECONDS],
                false
            );
        }

        // Store in runtime cache
        self::$runtime[$key] = $data;

        // Filter Validation Response
        return (array) apply_filters('lsd_licensing_validation_data', $data, $basename, $prefix);
    }

    /**
     * @param string $basename
     * @param string $prefix
     * @param Closure $callable
     * @return void
     */
    public static function runIfValid(string $basename, string $prefix, Closure $callable)
    {
        // Check Validity
        $valid = self::isValid($basename, $prefix);

        // Run the Callback
        if ($valid !== self::STATUS_INVALID) call_user_func($callable);

        // Add to Listdom Notifications when
        // the license is either invalid
        // or in a trial or grace period
        if ($valid === self::STATUS_INVALID || in_array($valid, [self::STATUS_TRIAL, self::STATUS_GRACE], true))
        {
            add_filter('lsd_license_activation_required', function (int $counter)
            {
                return ++$counter;
            });
        }
    }

    /**
     * @param string $prefix
     * @return bool
     */
    public static function isTrial(string $prefix): bool
    {
        // Pro Addon
        if ($prefix === 'lsd') $prefix = 'lsdaddpro';

        // Installation Time
        $installed_at = (int) get_option($prefix . '_installed_at', 0);

        // Just Installed
        if (!$installed_at) return true;

        // Trial Period?
        if (current_time('timestamp') - $installed_at <= (WEEK_IN_SECONDS * 2))
        {
            return true;
        }

        return false;
    }

    /**
     * @param string $prefix
     * @return bool
     */
    public static function isGracePeriod(string $prefix): bool
    {
        // Pro Addon
        if ($prefix === 'lsd') $prefix = 'lsdaddpro';

        // Invalidation Time
        $invalidated_at = (int) get_option($prefix . '_invalidated_at', 0);

        // Grace Period?
        if ($invalidated_at && current_time('timestamp') - $invalidated_at <= WEEK_IN_SECONDS)
        {
            return true;
        }

        return false;
    }

    /**
     * @param string $prefix
     * @return int
     */
    public static function remainingTrialPeriod(string $prefix): int
    {
        // Pro Addon
        if ($prefix === 'lsd') $prefix = 'lsdaddpro';

        // Installation Time
        $installed_at = (int) get_option($prefix . '_installed_at', 0);

        // Expiry Time
        $expiry = $installed_at + (WEEK_IN_SECONDS * 2);

        // Now
        $now = current_time('timestamp');

        // Trial Finished
        if ($now >= $expiry) return 0;

        // Diff Time
        $diff = $expiry - $now;

        // Remaining Days
        return (int) ceil($diff / DAY_IN_SECONDS);
    }

    /**
     * @param string $prefix
     * @return int
     */
    public static function remainingGracePeriod(string $prefix): int
    {
        // Pro Addon
        if ($prefix === 'lsd') $prefix = 'lsdaddpro';

        // Invalidation Time
        $invalidated_at = (int) get_option($prefix . '_invalidated_at', 0);

        // Expiry Time
        $expiry = $invalidated_at + WEEK_IN_SECONDS;

        // Now
        $now = current_time('timestamp');

        // Grace Finished
        if ($now >= $expiry) return 0;

        // Diff Time
        $diff = $expiry - $now;

        // Remaining Days
        return (int) ceil($diff / DAY_IN_SECONDS);
    }

    public static function remainingDays(int $expiry, int $installed): array
    {
        $now = current_time('timestamp');
        $total = max(0, $expiry - $installed);
        $remaining = $expiry - $now;

        // Progress %
        $progress = $total > 0 ? max(0, min(100, round(($remaining / $total) * 100))) : 0;

        // Remaining days
        $days_remaining = max(0, (int) ceil($remaining / DAY_IN_SECONDS));

        // Flags
        $expired = $days_remaining === 0;
        $expiring = !$expired && $days_remaining <= 30;

        return [
            'progress' => $progress,
            'days_remaining' => $days_remaining,
            'expired' => $expired,
            'expiring' => $expiring,
        ];
    }

    public static function getStatus(string $basename, string $prefix): array
    {
        $valid = self::isValid($basename, $prefix);
        $validation_status = self::validate($basename, $prefix);
        $installed_at = (int) get_option($prefix . '_installed_at', 0);
        $expiry_time = isset($validation_status['expiry_timestamp'])
            ? (int) $validation_status['expiry_timestamp']
            : (isset($validation_status['expiry']) ? (int) strtotime($validation_status['expiry']) : 0);

        $remaining = ($expiry_time > 0 && $installed_at > 0)
            ? self::remainingDays($expiry_time, $installed_at)
            : ['progress' => 100, 'days_remaining' => 0, 'expired' => false, 'expiring' => false];
        $has_license_key = trim((string) get_option($prefix . '_purchase_code', '')) !== '';
        $state = self::getState($valid, $remaining);
        $view = self::getStateViewConfig($state);
        $remaining['progress'] = in_array($valid, [self::STATUS_INVALID, self::STATUS_GRACE], true) ? 0 : $remaining['progress'];
        $show_valid_from = in_array($state, ['trial', 'active', 'expiring', 'expired'], true);
        $show_status = $state !== 'trial' || isset($validation_status['status']);
        $valid_license = $valid === self::STATUS_VALID && !$remaining['expiring'] && !$remaining['expired'];

        return [
            'valid' => $valid,
            'validation_status' => $validation_status,
            'installed_date' => $installed_at ? lsd_date('M j, Y', $installed_at) : '',
            'expired' => $remaining['expired'],
            'expiring' => $remaining['expiring'],
            'progress' => $remaining['progress'],
            'days_remaining' => $remaining['days_remaining'],
            'valid_license' => $valid_license,
            'has_license_key' => $has_license_key,
            'state' => $state,
            'badge' => $view['badge'],
            'progress_class' => $view['progress_class'],
            'card_class' => $view['card_class'],
            'action' => $view['action'],
            'show_status' => $show_status,
            'show_valid_from' => $show_valid_from,
            'status_text' => $state === 'inactive' ? esc_html__('Not Registered', 'listdom') : '',
            'expiry_mode' => self::getExpiryMode($state, $expiry_time, $validation_status),
        ];
    }

    public static function getMessagePayload(array $status, array $product, string $slot, string $prefix, string $shop_url): array
    {
        $state = (string) ($status['state'] ?? '');
        $message_state = $state === 'inactive' && !empty($status['has_license_key']) ? 'expired' : $state;
        $days_remaining = (int) ($status['days_remaining'] ?? 0);
        $product_name = '<strong>' . esc_html((string) ($product['name'] ?? '')) . '</strong>';
        $webilia_link = $shop_url
            ? '<a href="' . esc_url($shop_url) . '" target="_blank"><strong>Webilia</strong></a>'
            : '<strong>Webilia</strong>';

        switch ($slot)
        {
            case 'primary':
                if ($message_state === 'expired')
                {
                    return [
                        'type' => 'error',
                        'message' => esc_html__('The license Key / Purchase Code is expired. It is required for functionality, auto update, and customer service!', 'listdom'),
                        'cta' => [],
                    ];
                }

                if ($message_state === 'expiring')
                {
                    return [
                        'type' => 'warning',
                        'message' => sprintf(
                            /* translators: 1: Number of days remaining on the license. 2: Add-on name. */
                            esc_html__("Your license will expire in %1\$s days. Please renew it to avoid any interruption in using %2\$s Addon.", 'listdom'),
                            $days_remaining,
                            $product_name
                        ),
                        'cta' => [
                            'label' => esc_html__('Renew License', 'listdom'),
                            'url' => $shop_url,
                            'icon' => 'lsdi-key',
                        ],
                    ];
                }

                if ($message_state === 'inactive')
                {
                    return [
                        'type' => 'warning',
                        'message' => sprintf(
                            /* translators: 1: Add-on name, 2: Vendor website HTML link. */
                            esc_html__("To use %1\$s addon you need to activate it first. If you don't have a valid license key or yours has expired, you can obtain one from the %2\$s website.", 'listdom'),
                            $product_name,
                            $webilia_link
                        ),
                        'cta' => [
                            'label' => esc_html__('Get License', 'listdom'),
                            'url' => $shop_url,
                            'icon' => 'lsdi-key',
                        ],
                    ];
                }

                if ($message_state === 'grace')
                {
                    return [
                        'type' => 'warning',
                        'message' => sprintf(
                            /* translators: 1: Remaining grace period in days, 2: Add-on name. */
                            esc_html__("There seems to be an issue verifying your license, which may be due to a connection problem between our server and yours, or because your license has expired. You are now in a 7-day grace period. If your license is expired, please renew or activate your license within the next %1\$s days to avoid any disruption in using %2\$s. If you believe this is an error, kindly check your server connection or contact Webilia support for assistance.", 'listdom'),
                            '<strong style="color: red;">' . esc_html(self::remainingGracePeriod($prefix)) . '</strong>',
                            $product_name
                        ),
                        'cta' => [
                            'label' => esc_html__('Renew License', 'listdom'),
                            'url' => $shop_url,
                            'icon' => 'lsdi-key',
                        ],
                    ];
                }

                return ['type' => '', 'message' => '', 'cta' => []];
            case 'trial_notice':
                return [
                    'type' => 'info',
                    'message' => sprintf(
                        /* translators: 1: Remaining trial period in days, 2: Add-on name. */
                        esc_html__("Please activate your license promptly. You have less than %1\$s days remaining to activate %2\$s; after that, %2\$s will no longer be operational.", 'listdom'),
                        '<strong style="color: red;">' . esc_html(self::remainingTrialPeriod($prefix)) . '</strong>',
                        $product_name
                    ),
                    'cta' => [
                        'label' => esc_html__('Get License', 'listdom'),
                        'url' => $shop_url,
                        'icon' => 'lsdi-key',
                    ],
                ];
            case 'trial_inline':
                return [
                    'type' => 'info',
                    'message' => esc_html__("License Key / Purchase Code is required for functionality, auto update, and customer service!", 'listdom'),
                    'cta' => [],
                ];
            case 'expiring_notice':
                return [
                    'type' => 'warning',
                    'message' => sprintf(
                        /* translators: 1: Remaining days, 2: Add-on name. */
                        esc_html__("Your license will expire in %1\$s days. Please renew it to avoid any interruption in using %2\$s.", 'listdom'),
                        '<strong style="color: red;">' . esc_html($days_remaining) . '</strong>',
                        $product_name
                    ),
                    'cta' => [
                        'label' => esc_html__('Renew License', 'listdom'),
                        'url' => $shop_url,
                        'icon' => 'lsdi-key',
                    ],
                ];
            default:
                return ['type' => '', 'message' => '', 'cta' => []];
        }
    }

    private static function getState(int $valid, array $remaining): string
    {
        if ($valid === self::STATUS_TRIAL) return 'trial';
        if ($valid === self::STATUS_GRACE) return 'grace';
        if (!empty($remaining['expired'])) return 'expired';
        if (!empty($remaining['expiring'])) return 'expiring';
        if ($valid === self::STATUS_INVALID) return 'inactive';

        return 'active';
    }

    private static function getExpiryMode(string $state, int $expiry_time, array $validation_status): string
    {
        if ($state === 'grace') return 'error';
        if (in_array($state, ['expiring', 'expired'], true)) return 'days_remaining';
        if ($state === 'active' && $expiry_time > 0) return 'days_remaining';
        if (isset($validation_status['expiry'])) return 'expiry_date';

        return 'none';
    }

    private static function getStateViewConfig(string $state): array
    {
        $map = [
            'inactive' => [
                'badge' => ['class' => '', 'icon' => 'lsdi-key', 'text' => esc_html__('Inactive', 'listdom')],
                'progress_class' => 'lsd-error',
                'card_class' => '',
                'action' => 'get_license',
            ],
            'trial' => [
                'badge' => ['class' => '', 'icon' => 'lsdi-key', 'text' => esc_html__('Inactive', 'listdom')],
                'progress_class' => 'lsd-warning',
                'card_class' => '',
                'action' => 'get_license',
            ],
            'grace' => [
                'badge' => ['class' => 'lsd-grace', 'icon' => 'lsdi-time-half-pass', 'text' => esc_html__('Grace', 'listdom')],
                'progress_class' => 'lsd-warning',
                'card_class' => 'lsd-activation-grace',
                'action' => 'renew',
            ],
            'active' => [
                'badge' => ['class' => 'lsd-success', 'icon' => 'lsdi-checkmark-circle', 'text' => esc_html__('Active', 'listdom')],
                'progress_class' => 'lsd-success',
                'card_class' => 'lsd-activation-valid',
                'action' => '',
            ],
            'expiring' => [
                'badge' => ['class' => 'lsd-warning', 'icon' => 'lsdi-alert', 'text' => esc_html__('Expiring', 'listdom')],
                'progress_class' => 'lsd-warning',
                'card_class' => 'lsd-activation-expiring',
                'action' => 'renew',
            ],
            'expired' => [
                'badge' => ['class' => 'lsd-error', 'icon' => 'lsdi-alert', 'text' => esc_html__('Expired', 'listdom')],
                'progress_class' => 'lsd-error',
                'card_class' => 'lsd-activation-expired',
                'action' => 'renew',
            ],
        ];

        return $map[$state] ?? $map['inactive'];
    }

    /**
     * @param string $basename
     * @return void
     */
    public static function reset(string $basename)
    {
        // Base Key
        $key = self::getProductKey($basename);

        // Transient Cache
        delete_transient($key);
        delete_transient($key . '_data');

        // Option Cache
        delete_option(self::getOptionKey($basename));
        delete_option($key . '_data_opt');

        // Runtime Cache
        unset(self::$runtime[$key], self::$runtime[$key . '_data']);
    }
}
