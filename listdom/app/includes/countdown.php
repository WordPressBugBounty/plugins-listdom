<?php

/** Reusable, presentation-only countdown messages. */
class LSD_Countdown extends LSD_Base
{
    public static function enqueue(): void
    {
        $assets = new LSD_Assets();
        wp_enqueue_script('lsd-countdown', $assets->lsd_asset_url('js/countdown.min.js'), [], LSD_Assets::version(), true);
    }

    public static function data(int $seconds, string $template, string $complete = ''): array
    {
        return [
            'seconds' => max(0, $seconds),
            'template' => $template,
            /* translators: Keep the {hours}, {minutes}, and {seconds} placeholders. */
            'format' => __('{hours}h {minutes}m {seconds}s', 'listdom'),
            'complete' => $complete,
        ];
    }

    public static function html(array $data): string
    {
        $seconds = max(0, (int) $data['seconds']);
        $duration = strtr($data['format'], [
            '{hours}' => (string) intdiv($seconds, 3600),
            '{minutes}' => str_pad((string) (intdiv($seconds, 60) % 60), 2, '0', STR_PAD_LEFT),
            '{seconds}' => str_pad((string) ($seconds % 60), 2, '0', STR_PAD_LEFT),
        ]);
        $text = $seconds === 0 && $data['complete'] !== '' ? $data['complete'] : str_replace('{time}', $duration, $data['template']);
        return '<span data-lsd-countdown="' . esc_attr(wp_json_encode($data)) . '">' . esc_html($text) . '</span>';
    }

    public static function error(WP_Error $error): string
    {
        $data = $error->get_error_data();
        return is_array($data) && !empty($data['countdown']) ? self::html($data['countdown']) : esc_html($error->get_error_message());
    }
}
