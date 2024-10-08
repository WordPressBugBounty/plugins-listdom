<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom Socials - Instagram Class.
 *
 * @class LSD_Socials_Instagram
 * @version    1.0.0
 */
class LSD_Socials_Instagram extends LSD_Socials
{
    /**
     * Constructor method
     */
    public function __construct()
    {
        parent::__construct();

        $this->key = 'instagram';
        $this->label = esc_html__('Instagram', 'listdom');
    }

    public function icon($url): string
    {
        return '<a class="lsd-share-instagram" href="' . esc_url($url) . '" target="_blank">
            <i class="lsd-icon fab fa-instagram"></i>
        </a>';
    }
}
