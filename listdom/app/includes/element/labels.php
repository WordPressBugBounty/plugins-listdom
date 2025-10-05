<?php

class LSD_Element_Labels extends LSD_Element
{
    public $key = 'labels';
    public $style = 'tags';
    public $enable_link;
    public $label;

    public function __construct($style = 'tags', $enable_link = true)
    {
        parent::__construct();

        $this->label = esc_html__('Labels', 'listdom');
        $this->style = $style;
        $this->enable_link = $enable_link;
    }

    public function get($post_id = null)
    {
        if (is_null($post_id))
        {
            global $post;
            $post_id = $post->ID;
        }

        // Generate output
        ob_start();
        include lsd_template('elements/labels.php');

        return $this->content(
            ob_get_clean(),
            $this,
            [
                'post_id' => $post_id,
            ]
        );
    }

    public static function styles($label_id): string
    {
        $color = get_term_meta($label_id, 'lsd_color', true);
        $text = LSD_Base::get_text_color($color);

        return 'style="background-color: ' . esc_attr($color) . '; color: ' . esc_attr($text) . ';"';
    }

    protected function general_settings(array $data): string
    {
        $enable_link = isset($data['enable_link']) ? (int) $data['enable_link'] : 1;

        return '<div>
            <label class="lsd-fields-label-tiny" for="lsd_elements_' . esc_attr($this->key) . '_enable_link">' . esc_html__('Enable Link To Archive', 'listdom') . '</label>
            <select class="lsd-admin-input" name="lsd[elements][' . esc_attr($this->key) . '][enable_link]" id="lsd_elements_' . esc_attr($this->key) . '_enable_link">
                <option value="1" ' . ($enable_link === 1 ? 'selected="selected"' : '') . '>' . esc_html__('Yes', 'listdom') . '</option>
                <option value="0" ' . ($enable_link === 0 ? 'selected="selected"' : '') . '>' . esc_html__('No', 'listdom') . '</option>
            </select>
        </div>';
    }
}
