<?php

class LSD_Socials_Telegram extends LSD_Socials
{
    public function __construct()
    {
        parent::__construct();

        $this->key = 'telegram';
        $this->label = esc_html__('Telegram', 'listdom');
    }

    public function share($post_id): string
    {
        $url = get_the_permalink($post_id);
        $title = get_the_title($post_id);

        return '<a class="lsd-share-telegram" href="https://t.me/share/url?url=' . esc_attr(urlencode($url)) . '&text=' . esc_attr(urlencode($title)) . '" target="_blank" title="' . esc_attr__('Share in Telegram', 'listdom') . '">
            <i class="lsd-icon fab fa-telegram"></i>
        </a>';
    }

    public function icon($url): string
    {
        return '<a class="lsd-share-telegram" href="' . esc_url($url) . '" target="_blank">
            <i class="lsd-icon fab fa-telegram"></i>
        </a>';
    }
}
