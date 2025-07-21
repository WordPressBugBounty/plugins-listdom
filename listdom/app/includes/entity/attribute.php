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

                return '<a href="mailto:' . esc_attr($data) . '">' . esc_html($data) . '</a>';

            case 'url':

                return '<a href="' . esc_attr($data) . '">' . esc_html($data) . '</a>';

            case 'separator':

                return '<div class="lsd-separator">' . esc_html($data) . '</div>';

            case 'textarea':

                $editor = get_term_meta($this->term_id, 'lsd_editor', true);

                return $editor ? wpautop($data) : esc_html($data);

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
