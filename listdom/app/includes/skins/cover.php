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

    public function init()
    {
    }

    public function query()
    {
        $this->args['post_type'] = LSD_Base::PTYPE_LISTING;

        // Post ID
        $this->post_id = is_array($this->skin_options['listing'])
            ? $this->skin_options['listing'][0]
            : ($this->skin_options['listing'] ?: 0);

        // Query
        $this->args['post__in'] = [$this->post_id];
    }
}
