<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom Skins Cover Class.
 *
 * @class LSD_Skins_Cover
 * @version    1.0.0
 */
class LSD_Skins_Cover extends LSD_Skins
{
    public $skin = 'cover';
    public $default_style = 'style1';

    /**
     * Constructor method
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function init()
    {
    }

    public function query()
    {
        $this->args['post_type'] = LSD_Base::PTYPE_LISTING;

        // Post ID
        $this->post_id = $this->skin_options['listing'] ?? 0;
        $this->args['post__in'] = [$this->post_id];
    }
}
