<?php

class LSD_RewriteRules extends LSD_Base
{
    public static function todo()
    {
        update_option('lsd_todo_flush', 1);
    }

    public static function flush()
    {
        // if flush is not needed
        if (!get_option('lsd_todo_flush', 0)) return;

        // Perform the flush on WordPress init hook
        add_action('init', ['LSD_RewriteRules', 'perform']);
    }

    public static function perform()
    {
        // Flush the rules
        global $wp_rewrite;
        $wp_rewrite->flush_rules(false);

        // remove the to do
        delete_option('lsd_todo_flush');
    }
}
