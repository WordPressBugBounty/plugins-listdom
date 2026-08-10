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
        $faqs = $this->items($post_id, $limit);
        $suppress_schema = $this->suppress_schema($post_id);

        // Generate output
        ob_start();
        include lsd_template('elements/faq.php');

        return $this->content(
            ob_get_clean(),
            $this,
            [
                'post_id' => $post_id,
                'faqs' => $faqs,
                'limit' => $limit,
            ]
        );
    }

    /**
     * Get visible FAQ items for a listing.
     * @param int $post_id
     * @param int $limit
     * @return array
     */
    public function items(int $post_id, int $limit = 0): array
    {
        // Stored FAQs
        $stored = get_post_meta($post_id, 'lsd_faqs', true);
        if (!is_array($stored)) return [];

        // Valid FAQs
        $faqs = [];
        foreach ($stored as $faq)
        {
            if (!is_array($faq)) continue;

            $question = isset($faq['question']) && is_scalar($faq['question']) ? trim((string) $faq['question']) : '';
            $answer = isset($faq['answer']) && is_scalar($faq['answer']) ? trim((string) $faq['answer']) : '';
            if ($question === '' || $answer === '') continue;

            $faq['question'] = $question;
            $faq['answer'] = $answer;
            $faqs[] = $faq;
        }

        // Limit
        if ($limit > 0) $faqs = array_slice($faqs, 0, $limit);

        return $faqs;
    }

    /**
     * Check whether replacement JSON-LD covers FAQ schema.
     * @param int $post_id
     * @return bool
     */
    protected function suppress_schema(int $post_id): bool
    {
        // AI Visibility Service
        if (!class_exists('LSD_AI_Visibility')) return false;

        // Replacement Status
        $ai_visibility = LSD_AI_Visibility::instance();
        if (!$ai_visibility instanceof LSD_AI_Visibility) return false;

        return $ai_visibility->schema_service()->replaces_listing_faq_schema($post_id);
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
