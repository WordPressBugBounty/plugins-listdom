<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom Socials - Facebook Class.
 *
 * @class LSD_Socials_Facebook
 * @version    1.0.0
 */
class LSD_Socials_Facebook extends LSD_Socials
{
    /**
     * Constructor method
     */
    public function __construct()
    {
        parent::__construct();

        $this->key = 'facebook';
        $this->label = esc_html__('Facebook', 'listdom');
    }

    public function share($post_id): string
    {
        $url = get_the_permalink($post_id);
        return '<a class="lsd-share-facebook" href="https://www.facebook.com/sharer/sharer.php?u=' . esc_attr(urlencode($url)) . '" onclick="window.open(this.href, \'\', \'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=500,width=600\'); return false;" title="' . esc_attr__('Share on Facebook', 'listdom') . '">
            <i class="lsd-icon fab fa-facebook-f"></i>
        </a>';
    }

    public function icon($url): string
    {
        return '<a class="lsd-share-facebook" href="' . esc_url($url) . '" target="_blank">
            <i class="lsd-icon fab fa-facebook-f"></i>
        </a>';
    }
}
