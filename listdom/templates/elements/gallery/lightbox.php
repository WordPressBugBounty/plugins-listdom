<?php
// no direct access
defined('ABSPATH') || die();

/** @var array $params */
/** @var int $post_id */

$width = $params['width'] ?? 200;
$height = $params['height'] ?? 200;
$lightbox = !isset($params['lightbox']) || $params['lightbox'];

$gallery = get_post_meta($post_id, 'lsd_gallery', true);
if(!is_array($gallery)) $gallery = [];

// There is no Gallery!
if(!count($gallery)) return '';

$imageItemProp = 'itemprop="https://schema.org/image"';
?>
<div class="lsd-image-gallery <?php echo $lightbox ? 'lsd-image-lightbox' : ''; ?>" <?php echo lsd_schema()->scope()->type('https://schema.org/ImageGallery'); ?>>
    <?php
        foreach($gallery as $id)
        {
            $thumb = wp_get_attachment_image_src($id, [$width, $height]);
            $full = wp_get_attachment_image_src($id, 'full');

            if(!$thumb || !$full) continue;

            echo '<a href="'.esc_url($full[0]).'" '.lsd_schema()->associatedMedia().'>
                <img alt="" src="'.esc_url($thumb[0]).'" width="'.esc_attr($width).'" height="'.esc_attr($height).'" '.$imageItemProp.'>
            </a>';
        }
    ?>
</div>
