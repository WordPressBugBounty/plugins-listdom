<?php

class LSD_Folder extends LSD_Base
{
    protected static function filesystem()
    {
        global $wp_filesystem;

        if (!function_exists('WP_Filesystem')) require_once ABSPATH . 'wp-admin/includes/file.php';

        if (!is_object($wp_filesystem) || !is_a($wp_filesystem, 'WP_Filesystem_Base')) WP_Filesystem();

        if (!is_object($wp_filesystem) || !is_a($wp_filesystem, 'WP_Filesystem_Base')) return null;

        return $wp_filesystem;
    }

    protected static function directory_permissions(): int
    {
        return defined('FS_CHMOD_DIR') ? FS_CHMOD_DIR : 0755;
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
        $filesystem = self::filesystem();
        if ($filesystem) return $filesystem->is_dir($path);

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
        if (!$return) return false;

        $filesystem = self::filesystem();
        if (!$filesystem) return false;

        if (!$filesystem->is_dir($parent) || !$filesystem->is_writable($parent)) return false;

        if ($filesystem->is_dir($path)) return true;

        return $filesystem->mkdir($path, self::directory_permissions());
    }
}
