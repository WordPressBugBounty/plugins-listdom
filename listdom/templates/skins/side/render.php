<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Skins_Side $this */

switch($this->style)
{
    case 'style1':

        include lsd_template('skins/'.$this->skin.'/style1/render.php');
        break;

    case is_numeric($this->style):

        include lsd_template('skins/'.$this->skin.'/builder/render.php');
        break;

    default:

        LSD_Styles::render($this);
        break;
}