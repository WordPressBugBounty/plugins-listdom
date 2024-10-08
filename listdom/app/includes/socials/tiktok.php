<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom Socials - Tiktok Class.
 *
 * @class LSD_Socials_Tiktok
 * @version    1.0.0
 */
class LSD_Socials_Tiktok extends LSD_Socials
{
    /**
     * Constructor method
     */
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
