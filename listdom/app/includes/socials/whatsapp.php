<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom Socials - Whatsapp Class.
 *
 * @class LSD_Socials_Whatsapp
 * @version    1.0.0
 */
class LSD_Socials_Whatsapp extends LSD_Socials
{
    /**
     * Constructor method
     */
    public function __construct()
    {
        parent::__construct();

        $this->key = 'whatsapp';
        $this->label = esc_html__('WhatsApp', 'listdom');
    }

    public function get_input_type(): string
    {
        return 'tel';
    }

    public function share($post_id): string
    {
        $url = get_the_permalink($post_id);
        return '<a class="lsd-share-whatsapp" href="https://wa.me/?text=' . esc_attr(urlencode($url)) . '" target="_blank" title="' . esc_attr__('WhatsApp', 'listdom') . '">
            <i class="lsd-icon fab fa-whatsapp"></i>
        </a>';
    }

    public function icon($url): string
    {
        $tel = str_replace([' ', '-', '(', ')'], '', $url);
        return '<a class="lsd-share-whatsapp" href="https://wa.me/' . esc_attr($tel) . '" target="_blank">
            <i class="lsd-icon fab fa-whatsapp"></i>
        </a>';
    }
}
