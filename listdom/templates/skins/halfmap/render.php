<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Skins_Halfmap $this */

switch($this->style)
{
    case 'style3':

        include lsd_template('skins/'.$this->skin.'/style3/render.php');
        break;

    case 'style2':

        include lsd_template('skins/'.$this->skin.'/style2/render.php');
        break;

    case 'style1':

        include lsd_template('skins/'.$this->skin.'/style1/render.php');
        break;

    default:

        LSD_Styles::render($this);
        break;
}