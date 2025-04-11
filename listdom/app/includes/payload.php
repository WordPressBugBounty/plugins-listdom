<?php

class LSD_Payload extends LSD_Base
{
    protected static $vars;

    public static function set($key, $value)
    {
        self::$vars[$key] = $value;
    }

    /**
     * @param $key
     * @return mixed
     */
    public static function get($key)
    {
        return self::$vars[$key] ?? null;
    }

    public static function remove($key): bool
    {
        if (isset(self::$vars[$key]))
        {
            unset(self::$vars[$key]);
            return true;
        }

        return false;
    }
}
