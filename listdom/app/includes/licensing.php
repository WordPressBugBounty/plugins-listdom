<?php

use Webilia\WP\Plugin\Licensing;

class LSD_Licensing extends LSD_Base
{
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
        $valid = 0;

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
            if ($licensing->isValid()) $valid = 1;

            // Check Trial
            if (!$valid && LSD_Licensing::isTrial($prefix)) $valid = 2;

            // Grace Period
            if (!$valid && LSD_Licensing::isGracePeriod($prefix)) $valid = 3;

            // Valid
            if ($valid === 1)
            {
                $expiry = 10 * DAY_IN_SECONDS;

                // Remove Grace Period
                delete_option($prefix . '_invalidated_at');
            }
            // Trial Period
            else if ($valid === 2) $expiry = DAY_IN_SECONDS;
            // Invalid
            else
            {
                $expiry = DAY_IN_SECONDS;

                // Start Grace Period
                $grace_started = add_option($prefix . '_invalidated_at', current_time('timestamp'));

                // Grace Period
                if ($grace_started) $valid = 3;
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
        if ($valid) call_user_func($callable);

        // Add to Listdom Notifications when
        // the license is either invalid
        // or in a trial or grace period
        if (!$valid || in_array($valid, [2, 3]))
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
