<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom Socials - Linkedin Class.
 *
 * @class LSD_Socials_Linkedin
 * @version    1.0.0
 */
class LSD_Socials_Linkedin extends LSD_Socials
{
    /**
     * Constructor method
     */
    public function __construct()
    {
        parent::__construct();

        $this->key = 'linkedin';
        $this->label = esc_html__('Linkedin', 'listdom');
    }

    public function share($post_id): string
    {
        $url = get_the_permalink($post_id);
        return '<a class="lsd-share-linkedin" href="https://www.linkedin.com/shareArticle?mini=true&url=' . esc_attr(urlencode($url)) . '" onclick="window.open(this.href, \'\', \'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=500\'); return false;" target="_blank" title="' . esc_attr__('Linkedin', 'listdom') . '">
            <i class="lsd-icon fab fa-linkedin-in"></i>
        </a>';
    }

    public function icon($url): string
    {
        return '<a class="lsd-share-linkedin" href="' . esc_url($url) . '" target="_blank">
            <i class="lsd-icon fab fa-linkedin-in"></i>
        </a>';
    }
}
