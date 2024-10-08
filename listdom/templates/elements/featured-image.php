<?php
// no direct access
defined('ABSPATH') || die();

/** @var int $post_id */
/** @var array $size */

$assets = new LSD_Assets();

// Listing Image
$image = get_the_post_thumbnail($post_id, $size, (string) lsd_schema()->prop('contentUrl'));

// No Image
$no_image = '<img alt="' . esc_attr__('No Image', 'listdom') . '" src="' . esc_url_raw($assets->lsd_asset_url('/img/no-image.jpg')) . '">';

// Print
echo trim($image) ? LSD_Kses::element($image) : LSD_Kses::element($no_image);
