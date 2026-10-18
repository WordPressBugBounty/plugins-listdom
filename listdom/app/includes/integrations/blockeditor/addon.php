<?php

/** Rendering shared by independently registered add-on blocks. */
class LSD_Integrations_Blockeditor_Addon
{
    public static function element(string $class, array $attributes, string $name, string $message, array $args = []): string
    {
        $listing = LSD_Integrations_Blockeditor_Listing::get();

        return self::output(function () use ($class, $listing, $args)
        {
            if (!$listing || !class_exists($class) || !method_exists($class, 'get')) return '';

            return LSD_Integrations_Blockeditor_Listing::with_post($listing, function () use ($class, $listing, $args)
            {
                $element = new $class();
                return $args ? $element->get($listing->id(), $args) : $element->get($listing->id());
            });
        }, $attributes, $name, $message);
    }

    public static function output(callable $callback, array $attributes, string $name, string $message, bool $preview = true): string
    {
        if (strpos($name, '/listing-') !== false)
        {
            $listing = LSD_Integrations_Blockeditor_Listing::get();
            if ($listing)
            {
                $access = LSD_Integrations_Blockeditor_Listing::prepare($listing);
                if ($access !== null) return $access;
            }
        }

        $editor = defined('REST_REQUEST') && REST_REQUEST && !empty($_REQUEST['lsd_editor_preview']);
        if ($editor && !$preview) return self::placeholder($message);

        $before = wp_styles()->queue;
        $html = (string) call_user_func($callback);
        if (trim($html) === '') return $editor ? self::placeholder($message) : '';

        $class = 'wp-block-' . str_replace('/', '-', $name) . ' lsd-addon-block';
        if ($editor)
        {
            // The selectable editor wrapper already carries block-support styles.
            $wrapper = 'class="lsd-addon-block"';
        }
        elseif (function_exists('get_block_wrapper_attributes')) $wrapper = get_block_wrapper_attributes(['class' => $class]);
        else
        {
            $class .= !empty($attributes['className']) ? ' ' . $attributes['className'] : '';
            $wrapper = 'class="' . esc_attr($class) . '"';
        }

        return LSD_Integrations_Blockeditor_Assets::frontend($before) . '<div ' . $wrapper . '>' . $html . '</div>';
    }

    private static function placeholder(string $message): string
    {
        return '<p class="lsd-listing-block-placeholder">' . esc_html($message) . '</p>';
    }
}
