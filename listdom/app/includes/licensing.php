<?php

use Webilia\WP\Plugin\Licensing;

/**
 * Listdom Licensing Class.
 *
 * @class LSD_Licensing
 * @version	1.0.0
 */
class LSD_Licensing extends LSD_Base
{
    /**
     * @param string $basename
     * @return string
     */
    private static function getProductKey(string $basename): string
    {
        return 'lsd_product_validation_'.str_replace(['/', '-'], '_', $basename);
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

        $license_key_option = $prefix.'_purchase_code';
        $activation_id_option = $prefix.'_activation_id';

        // Validation Status
        $valid = 0;

        // Cached Status
        $cached = get_transient($key);

        // Already Checked
        if(is_numeric($cached)) $valid = (int) $cached;
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
            if($licensing->isValid()) $valid = 1;

            // Check Trial
            if(!$valid && LSD_Licensing::isTrial($prefix)) $valid = 2;

            // Set to Transient
            set_transient($key, $valid, ($valid === 2 ? DAY_IN_SECONDS : 3 * DAY_IN_SECONDS));
        }

        // Filter Validation
        return (int) apply_filters('lsd_licensing_validation', $valid, $basename, $prefix);
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
        if($valid) call_user_func($callable);

        // Add to Listdom Notifications when
        // the license is either invalid
        // or in a trial period
        if(!$valid || $valid === 2)
        {
            add_filter('lsd_license_activation_required', function(int $counter)
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
        if($prefix === 'lsd') $prefix = 'lsdaddpro';

        // Installation Time
        $installed_at = (int) get_option($prefix.'_installed_at', 0);

        // Trial Period?
        if($installed_at && current_time('timestamp') - $installed_at <= (WEEK_IN_SECONDS * 2))
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
        if($prefix === 'lsd') $prefix = 'lsdaddpro';

        // Installation Time
        $installed_at = (int) get_option($prefix.'_installed_at', 0);

        // Expiry Time
        $expiry = $installed_at + (WEEK_IN_SECONDS * 2);

        // Now
        $now = current_time('timestamp');

        // Trial Finished
        if($now >= $expiry) return 0;

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
        // Product Key
        $key = self::getProductKey($basename);

        delete_transient($key);
    }
}
