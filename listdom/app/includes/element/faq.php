<?php

class LSD_Element_Faq extends LSD_Element
{
    public $key = 'faq';
    public $label;

    public function __construct()
    {
        parent::__construct();

        $this->label = esc_html__('FAQs', 'listdom');
    }

    public function get($post_id = null, $limit = 0)
    {
        if (is_null($post_id))
        {
            global $post;
            $post_id = $post->ID;
        }

        $limit = (int) $limit;

        // Generate output
        ob_start();
        include lsd_template('elements/faq.php');

        return $this->content(
            ob_get_clean(),
            $this,
            [
                'post_id' => $post_id,
                'limit' => $limit,
            ]
        );
    }

    protected function general_settings(array $data): string
    {
        $count = isset($data['count']) ? (int) $data['count'] : 0;

        return '<div>
            <label class="lsd-fields-label-tiny" for="lsd_elements_' . esc_attr($this->key) . '_count">' . esc_html__('Question Count', 'listdom') . '</label>
            <input class="lsd-admin-input" type="number" min="0" step="1" name="lsd[elements][' . esc_attr($this->key) . '][count]" id="lsd_elements_' . esc_attr($this->key) . '_count" value="' . esc_attr($count) . '">
            <p class="lsd-admin-description-tiny lsd-mb-0 lsd-mt-2">' . esc_html__('Use 0 to show all questions.', 'listdom') . '</p>
        </div>';
    }
}
