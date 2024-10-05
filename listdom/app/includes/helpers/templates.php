<?php
// no direct access
defined('ABSPATH') || die();

function lsd_template($tpl, $override = true)
{
    $lsd = new LSD_Main();
    $path = $lsd->get_listdom_path().'/templates/'.ltrim($tpl, '/');

    if($override)
    {
        // Main Theme
        $theme = get_template_directory();
        $theme_path = $theme.'/listdom/'.ltrim($tpl, '/');

        if(is_file($theme_path)) $path = $theme_path;

        // Child Theme
        if(is_child_theme())
        {
            $child_theme = get_stylesheet_directory();
            $child_theme_path = $child_theme.'/listdom/'.ltrim($tpl, '/');

            if(is_file($child_theme_path)) $path = $child_theme_path;
        }
    }

    // Apply Filter
    return apply_filters('lsd_template', $path, $tpl, $override);
}