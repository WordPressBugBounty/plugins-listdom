<?php

class LSD_LifeCycle extends LSD_Base
{
    public static $body_started = false;
    public static $content_printed = false;

    public function init()
    {
        add_filter('body_class', function ($class)
        {
            self::setBodyStarted(true);
            return $class;
        });

        add_action('wp_body_open', function ()
        {
            self::setBodyStarted(true);
        });

        if (function_exists('wp_is_block_theme') && wp_is_block_theme())
        {
            self::setBodyStarted(true);
        }
    }

    public static function setBodyStarted(bool $status)
    {
        self::$body_started = $status;
    }

    public static function setContentPrinted(bool $status)
    {
        self::$content_printed = $status;
    }

    public static function isBodyStarted(): bool
    {
        return apply_filters('lsd_lifecycle_is_body_started', self::$body_started);
    }

    public static function isContentPrinted(): bool
    {
        return apply_filters('lsd_lifecycle_is_content_printed', self::$content_printed);
    }

    public static function post($post_id): bool
    {
        global $post;
        $post = get_post($post_id);
        return setup_postdata($post);
    }

    public static function reset()
    {
        wp_reset_postdata();
    }
}
