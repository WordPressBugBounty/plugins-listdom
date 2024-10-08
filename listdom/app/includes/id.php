<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom ID class.
 */
class LSD_id
{
    /**
     * The single instance of the class.
     *
     * @var LSD_id
     * @since 1.0.0
     */
    protected static $instance = null;

    /**
     * All IDs that are loaded
     * @var array
     */
    protected static $IDs = [];

    /**
     * Main LSD_id Instance.
     * Ensures only one instance of LSD_id is loaded or can be loaded.
     * @return LSD_id
     * @since 1.0.0
     * @static
     */
    public static function instance()
    {
        // Get an instance of Class
        if (is_null(self::$instance)) self::$instance = new self();

        // Return the instance
        return self::$instance;
    }

    /**
     * Listdom Constructor.
     */
    protected function __construct()
    {
    }

    public static function get($id)
    {
        $instance = self::instance();
        if ($instance->duplicated($id))
        {
            $id = $instance->unique();
        }

        $instance->add($id);
        return $id;
    }

    public function duplicated($id): bool
    {
        return in_array($id, self::$IDs);
    }

    public function add($id)
    {
        self::$IDs[] = $id;
    }

    public function unique()
    {
        $id = mt_rand(1000, 9999);
        if ($this->duplicated($id)) $id = $this->unique();

        return $id;
    }

    public static function code($length = 10): string
    {
        $keys = array_merge(range(0, 9), range('A', 'Z'), range('a', 'z'));

        $key = '';
        for ($i = 0; $i < $length; $i++) $key .= $keys[array_rand($keys)];

        return $key;
    }
}
