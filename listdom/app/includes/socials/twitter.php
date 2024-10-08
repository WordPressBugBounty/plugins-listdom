<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom Socials - Twitter Class.
 *
 * @class LSD_Socials_Twitter
 * @version    1.0.0
 */
class LSD_Socials_Twitter extends LSD_Socials
{
    /**
     * Constructor method
     */
    public function __construct()
    {
        parent::__construct();

        $this->key = 'twitter';
        $this->label = esc_html__('Twitter', 'listdom');
    }

    public function share($post_id): string
    {
        $url = get_the_permalink($post_id);
        return '<a class="lsd-share-twitter" href="https://twitter.com/share?url=' . esc_attr(urlencode($url)) . '" onclick="window.open(this.href, \'\', \'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=500\'); return false;" target="_blank" title="' . esc_attr__('Tweet', 'listdom') . '">
            <i class="lsd-icon fab fa-twitter"></i>
        </a>';
    }

    public function icon($url): string
    {
        return '<a class="lsd-share-twitter" href="' . esc_url($url) . '" target="_blank">
            <i class="lsd-icon fab fa-twitter"></i>
        </a>';
    }
}
