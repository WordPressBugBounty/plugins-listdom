<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom Flash Message Class.
 *
 * @class LSD_Flash
 * @version    1.0.0
 */
class LSD_Flash extends LSD_Base
{
    /**
     * Constructor method
     */
    public function __construct()
    {
        parent::__construct();
    }

    public static function add($message, $class = 'info')
    {
        $classes = ['error', 'info', 'success', 'warning'];
        if (!in_array($class, $classes)) $class = 'info';

        $flash_messages = maybe_unserialize(get_option('lsd_flash_messages', []));
        $flash_messages[$class][] = $message;

        update_option('lsd_flash_messages', $flash_messages);
    }

    public static function show()
    {
        $flash_messages = maybe_unserialize(get_option('lsd_flash_messages', ''));
        if (!is_array($flash_messages)) return;

        foreach ($flash_messages as $class => $messages)
        {
            foreach ($messages as $message) echo '<div class="notice notice-' . esc_attr($class) . ' is-dismissible"><p>' . $message . '</p></div>';
        }

        // Clear flash messages
        delete_option('lsd_flash_messages');
    }
}
