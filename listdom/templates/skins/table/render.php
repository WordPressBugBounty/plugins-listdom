<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Skins_Table $this */

switch($this->style)
{
    case 'style3':
    case 'style2':
    case 'style1':

        include lsd_template('skins/'.$this->skin.'/style1/render.php');
        break;

    default:

        LSD_Styles::render($this);
        break;
}
