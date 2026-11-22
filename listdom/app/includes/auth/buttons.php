<?php

/** Shared social sign-in button settings and presentation. */
class LSD_Auth_Buttons
{
    public static function sanitize(array $settings): array
    {
        $result = [];
        foreach (['show_text', 'show_logo', 'branded'] as $key)
            $result[$key] = array_key_exists($key, $settings) ? (int) !empty($settings[$key]) : 1;
        if (!$result['show_text'] && !$result['show_logo']) $result['show_text'] = 1;
        return $result;
    }

    public static function settings(): array
    {
        return self::sanitize((array) (LSD_Options::auth()['social_login_buttons'] ?? []));
    }

    public static function render(string $provider, string $name, array $design, bool $preview = false, bool $compact = false): string
    {
        $design = self::sanitize($design);
        $files = ['google' => 'google.png', 'yahoo' => 'yahoo.svg', 'apple' => 'apple.svg', 'microsoft' => 'microsoft.svg'];
        $logo = isset($files[$provider]) ? plugins_url('assets/img/auth/' . $files[$provider], LSD_ABSPATH . '/listdom.php') : '';
        $text = sprintf(__('Continue with %s', 'listdom'), $name);
        $label = $compact ? $name : $text;
        // Extension providers without a bundled logo must still have visible content.
        $show_text = $design['show_text'] || $logo === '';
        $classes = 'lsd-light-button lsd-provider-button lsd-provider-' . sanitize_html_class($provider);
        if ($design['branded']) $classes .= ' lsd-provider-branded';
        if (!$show_text) $classes .= ' lsd-provider-icon-only';
        ob_start();
        include lsd_template('auth/provider-button.php');
        return ob_get_clean();
    }
}
