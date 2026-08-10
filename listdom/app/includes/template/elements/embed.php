<?php

class LSD_Template_Elements_Embed extends LSD_Template
{
    public function __construct($settings = [])
    {
        $this->key            = 'embed';
        $this->label          = $this->label();
        $this->category       = 'media';
        $this->template_types = ['single_listing', 'listing_card', 'info_window'];
        $this->icon           = self::icons($this->key);

        parent::__construct($settings);
    }

    protected function label()
    {
        return esc_html__('Listing Embeds', 'listdom');
    }

    public function render(array $args = []): string
    {
        $listing_id = isset($args['listing_id']) ? (int) $args['listing_id'] : 0;
        if (!$listing_id) return '';

        $listing = new LSD_Entity_Listing($listing_id);
        $output = $listing->get_embeds();
        if (trim($output) === '') return '';

        return '<div class="lsd-template-element-embed lsdtb-card-embeds">' . $output . '</div>';
    }
}
