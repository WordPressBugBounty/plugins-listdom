<?php

class LSD_Element_Tags extends LSD_Element
{
    public $key = 'tags';
    public $label;
    public $enable_link;

    public function __construct($enable_link = true)
    {
        parent::__construct();

        $this->label = esc_html__('Tags', 'listdom');
        $this->inline_title = true;
        $this->enable_link = $enable_link;
    }

    public function get($post_id = null)
    {
        if (is_null($post_id))
        {
            global $post;
            $post_id = $post->ID;
        }

        $terms = wp_get_post_terms($post_id, LSD_Base::TAX_TAG);
        if (!count($terms)) return '';

        $output = '<ul>';
        foreach ($terms as $term)
        {
            if ($this->enable_link) $output .= '<li><a href="' . esc_url(get_term_link($term->term_id)) . '">' . esc_html($term->name) . '</a></li>';
            else $output .= '<li><span class="lsd-single-term">' . esc_html($term->name) . '</span></li>';
        }

        $output .= '</ul>';

        return $this->content(
            $output,
            $this,
            [
                'post_id' => $post_id,
            ]
        );
    }

    protected function general_settings(array $data): string
    {
        $enable_link = isset($data['enable_link']) ? (int) $data['enable_link'] : 1;

        return '<div>
            <label for="lsd_elements_' . esc_attr($this->key) . '_enable_link">' . esc_html__('Enable Link To Archive', 'listdom') . '</label>
            <select name="lsd[elements][' . esc_attr($this->key) . '][enable_link]" id="lsd_elements_' . esc_attr($this->key) . '_enable_link">
                <option value="1" ' . ($enable_link === 1 ? 'selected="selected"' : '') . '>' . esc_html__('Yes', 'listdom') . '</option>
                <option value="0" ' . ($enable_link === 0 ? 'selected="selected"' : '') . '>' . esc_html__('No', 'listdom') . '</option>
            </select>
        </div>';
    }
}
