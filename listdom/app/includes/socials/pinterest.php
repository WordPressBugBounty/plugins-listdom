<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom Socials - Pinterest Class.
 *
 * @class LSD_Socials_Pinterest
 * @version    1.0.0
 */
class LSD_Socials_Pinterest extends LSD_Socials
{
    /**
     * Constructor method
     */
    public function __construct()
    {
        parent::__construct();

        $this->key = 'pinterest';
        $this->label = esc_html__('Pinterest', 'listdom');
    }

    public function share($post_id): string
    {
        $url = get_the_permalink($post_id);
        return '<a class="lsd-share-pinterest" href="https://pinterest.com/pin/create/link/?url=' . esc_attr(urlencode($url)) . '&description=' . get_the_title($post_id) . '" onclick="window.open(this.href, \'\', \'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=500\'); return false;" target="_blank" title="' . esc_attr__('Pin it', 'listdom') . '">
            <i class="lsd-icon fab fa-pinterest-p"></i>
        </a>';
    }

    public function icon($url): string
    {
        return '<a class="lsd-share-pinterest" href="' . esc_url($url) . '" target="_blank">
            <i class="lsd-icon fab fa-pinterest-p"></i>
        </a>';
    }
}
