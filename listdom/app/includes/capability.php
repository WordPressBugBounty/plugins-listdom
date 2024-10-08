<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom Capability Class.
 *
 * @class LSD_Capability
 * @version    1.0.0
 */
class LSD_Capability extends LSD_Base
{
    /**
     * Constructor method
     */
    public function __construct()
    {
        parent::__construct();
    }

    public static function can(string $capability, string $second = '', ...$args): bool
    {
        return current_user_can($capability, ...$args) || (trim($second) && current_user_can($second, ...$args));
    }
}
