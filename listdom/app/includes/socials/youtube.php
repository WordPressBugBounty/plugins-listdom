<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom Socials - YouTube Class.
 *
 * @class LSD_Socials_Youtube
 * @version    1.0.0
 */
class LSD_Socials_Youtube extends LSD_Socials
{
    /**
     * Constructor method
     */
    public function __construct()
    {
        parent::__construct();

        $this->key = 'youtube';
        $this->label = esc_html__('Youtube', 'listdom');
    }

    public function icon($url): string
    {
        return '<a class="lsd-share-youtube" href="' . esc_url($url) . '" target="_blank">
            <i class="lsd-icon fab fa-youtube"></i>
        </a>';
    }
}
