<?php

class LSD_Socials_Tiktok extends LSD_Socials
{
    public function __construct()
    {
        parent::__construct();

        $this->key = 'tiktok';
        $this->label = esc_html__('Tiktok', 'listdom');
    }

    public function icon($url): string
    {
        return '<a class="lsd-share-tiktok" href="' . esc_url($url) . '" target="_blank">
            <i class="lsd-icon fab fa-tiktok"></i>
        </a>';
    }
}
