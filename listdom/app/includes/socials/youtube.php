<?php

class LSD_Socials_Youtube extends LSD_Socials
{
    public function __construct()
    {
        parent::__construct();

        $this->key = 'youtube';
        $this->label = esc_html__('Youtube', 'listdom');
    }

    public function icon($url): string
    {
        return '<a class="lsd-share-youtube" href="' . esc_url($url) . '" target="_blank">
            <i class="lsd-fe-icon fab fa-youtube"></i>
        </a>';
    }

    public function supports(string $section = ''): bool
    {
        if (in_array($section, ['archive_share', 'single_share'])) return false;

        return true;
    }
}
