<?php

class LSD_Capability extends LSD_Base
{
    public static function can(string $capability, string $second = '', ...$args): bool
    {
        return current_user_can($capability, ...$args) || (trim($second) && current_user_can($second, ...$args));
    }
}
