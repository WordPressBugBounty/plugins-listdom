<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom Folder Class.
 *
 * @class LSD_Folder
 * @version    1.0.0
 */
class LSD_Folder extends LSD_Base
{
    /**
     * Constructor method
     */
    public function __construct()
    {
        parent::__construct();
    }

    public static function files($path, $filter = '.')
    {
        // Path doesn't exists
        if (!self::exists($path)) return false;

        $files = [];
        if ($handle = opendir($path))
        {
            while (false !== ($entry = readdir($handle)))
            {
                if ($entry == '.' or $entry == '..' or is_dir($entry)) continue;
                if (!preg_match("/$filter/", $entry)) continue;

                $files[] = $entry;
            }

            closedir($handle);
        }

        return $files;
    }

    public static function exists($path): bool
    {
        return is_dir($path);
    }

    public static function create($path): bool
    {
        // Directory Exists Already
        if (LSD_Folder::exists($path)) return true;

        // Check Parent Directory
        $parent = substr($path, 0, strrpos($path, '/', -2) + 1);
        $return = LSD_Folder::create($parent);

        // Create Directory
        return $return && is_writable($parent) && mkdir($path, 0755);
    }
}
