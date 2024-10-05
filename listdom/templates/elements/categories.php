<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Element_Categories $this */
/** @var WP_Term $category */
/** @var array $categories */

if(!$this->multiple_categories) echo '<span><a href="'.esc_url(get_term_link($category->term_id)).'" '.($this->show_color ? LSD_Element_Categories::styles($category->term_id, $this->color_method) : '').' '.lsd_schema()->category().'>'.esc_html($category->name).'</a></span>';
else
{
    foreach($categories as $category)
    {
        echo '<span><a href="'.esc_url(get_term_link($category->term_id)).'" '.($this->show_color ? LSD_Element_Categories::styles($category->term_id, $this->color_method) : '').' '.lsd_schema()->category().'>'.esc_html($category->name).'</a></span>';
    }
}