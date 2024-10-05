<?php
// no direct access
defined('ABSPATH') || die();

/** @var int $post_id */
/** @var array $size */

echo LSD_Kses::element(get_the_post_thumbnail($post_id, $size, (string) lsd_schema()->prop('contentUrl')));