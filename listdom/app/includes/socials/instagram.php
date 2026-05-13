<?php

class LSD_Socials_Instagram extends LSD_Socials
{
    public function __construct()
    {
        parent::__construct();

        $this->key = 'instagram';
        $this->label = esc_html__('Instagram', 'listdom');
    }

    public function icon($url): string
    {
        return '<a class="lsd-share-instagram" href="' . esc_url($url) . '" target="_blank">
            <i class="lsd-fe-icon fab fa-instagram"></i>
        </a>';
    }

    public function supports(string $section = ''): bool
    {
        if (in_array($section, ['archive_share', 'single_share'])) return false;

        return true;
    }
}
