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

            case 'separator':

                return '<div class="lsd-separator">' . esc_html($data) . '</div>';

            case 'textarea':

                $editor = get_term_meta($this->term_id, 'lsd_editor', true);

                return $editor ? wpautop($data) : esc_html($data);

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

    public function slug(): string
    {
        $term = get_term($this->term_id);
        return $term && isset($term->slug) ? $term->slug : '';
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
