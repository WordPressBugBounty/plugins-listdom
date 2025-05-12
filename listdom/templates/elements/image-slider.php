<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Skins $shortcode */
/** @var int $post_id */
/** @var string $link_method */
/** @var string $style */
/** @var array $size */
/** @var LSD_Element_Image $this */

// Get Listing Gallery
$gallery = get_post_meta($post_id, 'lsd_gallery', true);
if (!is_array($gallery)) $gallery = [];

// Include Featured Image
$include_featured_image = isset($this->settings['gallery_featured_image']) && trim($this->settings['gallery_featured_image'])
    ? $this->settings['gallery_featured_image']
    : 'always';

// Add Featured Image to Gallery
if (($include_featured_image === 'always' || ($include_featured_image === 'fallback' && !count($gallery))) && has_post_thumbnail($post_id)) array_unshift($gallery, get_post_thumbnail_id($post_id));

// Unique Gallery
$gallery = array_unique($gallery);
?>
<div class="lsd-image-slider-wrapper <?php echo (count($gallery) ? 'lsd-has-image' : ''); ?>">
    <?php if (count($gallery)): ?>
    <ul class="lsd-image-slider-slider">
        <?php foreach($gallery as $image_id): ?>
        <?php
            $image = wp_get_attachment_image($image_id, $size, false, (string) lsd_schema()->prop('contentUrl'));
            if (!$image) continue;
        ?>
        <li>
            <?php if (in_array($link_method, ['normal', 'blank', 'lightbox', 'left-panel', 'right-panel', 'bottom-panel'])): ?>
            <a
                data-listing-id="<?php echo esc_attr($post_id); ?>"
                data-listdom-style="<?php echo esc_attr($style); ?>"
                <?php echo $link_method === 'lightbox' ? 'data-listdom-lightbox' : ''; ?>
                <?php echo $link_method === 'left-panel' ? 'data-listdom-panel="left"' : ''; ?>
                <?php echo $link_method === 'right-panel' ? 'data-listdom-panel="right"' : ''; ?>
                <?php echo $link_method === 'bottom-panel' ? 'data-listdom-panel="bottom"' : ''; ?>
                href="<?php echo esc_url(get_the_permalink($post_id)); ?>"
                <?php echo $link_method === 'blank' ? 'target="_blank"' : ''; ?>
                <?php echo lsd_schema()->url()->scope()->type('https://schema.org/ImageObject'); ?>
            >
                <?php echo LSD_Kses::element($image); ?>
            </a>
            <?php else: echo LSD_Kses::element($image); ?>
            <?php endif; ?>
        </li>
        <?php endforeach; ?>
    </ul>
    <?php else: ?>
    <div class="lsd-no-image"><i class="lsd-icon fa fa-camera fa-5x"></i></div>
    <?php endif; ?>
</div>
