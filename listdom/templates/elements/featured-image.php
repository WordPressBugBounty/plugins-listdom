<?php
// no direct access
defined('ABSPATH') || die();

/** @var int $post_id */
/** @var array $size */
/** @var string $itemprop */

$schema = [];
if (!isset($itemprop)) $itemprop = 'image';
if (trim($itemprop)) $schema['itemprop'] = $itemprop;

// Listing Image
$image = get_the_post_thumbnail($post_id, $size, $schema);

// No Image
$no_image = '<img alt="' . esc_attr__('No Image', 'listdom') . '" src="' . esc_url((new LSD_Assets())->lsd_asset_url('/img/no-image.jpg')) . '">';

// Print
echo trim($image) ? LSD_Kses::element($image) : LSD_Kses::element($no_image);
