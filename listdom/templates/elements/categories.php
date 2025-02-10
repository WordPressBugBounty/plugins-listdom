<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Element_Categories $this */
/** @var WP_Term $category */
/** @var array $categories */

if (!$this->multiple_categories)
{
    echo LSD_Kses::element($this->display($category));
}
else
{
    foreach ($categories as $category)
    {
        echo LSD_Kses::element($this->display($category));
    }
}
