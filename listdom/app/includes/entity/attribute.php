<?php

class LSD_Entity_Attribute extends LSD_Base
{
    protected $term_id;
    public $type;

    public function __construct($term_id)
    {
        $this->term_id = $term_id;
        $this->type = get_term_meta($this->term_id, 'lsd_field_type', true);
    }

    public function render($data, $args = [])
    {
        switch ($this->type)
        {
            case 'number':

                return (int) $data;

            case 'email':

                $label = get_term_meta($this->term_id, 'lsd_link_label', true);
                $text = trim($label) === '' ? $data : $label;

                return '<a href="mailto:' . esc_attr($data) . '">' . esc_html($text) . '</a>';

            case 'tel':

                $label = get_term_meta($this->term_id, 'lsd_link_label', true);
                $text = trim($label) === '' ? $data : $label;

                return '<a href="tel:' . esc_attr($data) . '">' . esc_html($text) . '</a>';

            case 'url':

                $label = get_term_meta($this->term_id, 'lsd_link_label', true);
                $text = trim($label) === '' ? $data : $label;

                return '<a href="' . esc_attr($data) . '">' . esc_html($text) . '</a>';

            case 'image':

                if (is_numeric($data))
                {
                    $image = wp_get_attachment_image($data, 'medium');
                    return $image ?: '';
                }

                $data = trim($data);
                return $data ? '<img src="' . esc_url($data) . '" alt="">' : '';

            case 'file':

                $download_url = '';
                $download_title = '';

                if (is_numeric($data))
                {
                    $attachment_id = (int) $data;
                    $download_url = wp_get_attachment_url($attachment_id) ?: '';

                    if ($download_url)
                    {
                        $attached_file = get_attached_file($attachment_id);
                        if (is_string($attached_file) && trim($attached_file) !== '') $download_title = wp_basename($attached_file);
                    }
                }
                else if (is_string($data))
                {
                    $raw_url = trim($data);
                    if (!filter_var($raw_url, FILTER_VALIDATE_URL)) return '';

                    $download_url = esc_url_raw($raw_url);

                    $path = wp_parse_url($download_url, PHP_URL_PATH);
                    if (is_string($path) && trim($path) !== '') $download_title = wp_basename($path);
                }

                if (trim($download_url) === '') return '';
                $safe_download_url = esc_url($download_url);
                if (trim($safe_download_url) === '') return '';

                if (trim($download_title) === '')
                {
                    $download_title = esc_html__('Download', 'listdom');
                }

                return '<a href="' . $safe_download_url . '" class="lsd-attribute-file-download" target="_blank" rel="noopener" download>' . esc_html($download_title) . '</a>';

            case 'separator':

                return '<div class="lsd-separator">' . esc_html($data) . '</div>';

            case 'textarea':

                return $this->is_rich_editor() ? wpautop((string) $data) : nl2br(esc_html((string) $data));

            case 'date':

                $timestamp = strtotime((string) $data);
                if ($timestamp !== false) return esc_html(date_i18n(get_option('date_format'), $timestamp));

                return esc_html($data);

            case 'time':

                $time_value = is_string($data) ? trim($data) : '';
                if ($time_value !== '')
                {
                    $timestamp = strtotime('1970-01-01 ' . str_replace('T', ' ', $time_value));
                    if ($timestamp !== false) return esc_html(date_i18n(get_option('time_format'), $timestamp));
                }

                return esc_html($time_value);

            case 'datetime':

                $datetime_value = is_string($data) ? str_replace('T', ' ', $data) : '';
                if ($datetime_value !== '')
                {
                    $timestamp = strtotime($datetime_value);
                    if ($timestamp !== false)
                    {
                        $format = trim(get_option('date_format') . ' ' . get_option('time_format'));
                        return esc_html(date_i18n($format, $timestamp));
                    }
                }

                return esc_html($datetime_value);

            case 'checkbox':

                if (is_array($data))
                {
                    $escaped = array_map('esc_html', $data);
                    return implode(', ', $escaped);
                }

                return esc_html($data);

            case 'dropdown':
            case 'radio':
            default:

                return esc_html($data);
        }
    }

    public function is_rich_editor(): bool
    {
        return $this->type === 'textarea' && (bool) get_term_meta($this->term_id, 'lsd_editor', true);
    }

    public function slug(): string
    {
        $term = get_term($this->term_id);
        return $term && isset($term->slug) ? $term->slug : '';
    }

    public function value(int $listing_id)
    {
        $attributes = get_post_meta($listing_id, 'lsd_attributes', true);
        if (!is_array($attributes)) return '';
        if (!LSD_Taxonomies_Attribute::applies($this->term_id, ['post_id' => $listing_id])) return '';

        $slug = $this->slug();
        if ($slug === '' || !array_key_exists($slug, $attributes)) return '';

        return $attributes[$slug];
    }

    public function icon()
    {
        return LSD_Taxonomies::icon($this->term_id);
    }

    public static function schema($term_id)
    {
        $itemprop = get_term_meta($term_id, 'lsd_itemprop', true);
        if (!trim($itemprop)) return '';

        return lsd_schema()->prop($itemprop);
    }
}
