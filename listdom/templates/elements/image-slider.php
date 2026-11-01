<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Skins $shortcode */
/** @var int $post_id */
/** @var string $link_method */
/** @var string $style */
/** @var array $size */
/** @var LSD_Element_Image $this */

// Resolve the same sources used by native media diagnostics.
$sources = LSD_Media_Sources::gallery((int) $post_id, $this->settings);
$gallery = $sources['ids'];
$featured_image_id = $sources['featured_id'];
$featured_image_added = $sources['featured_added'];
$image_attributes = LSD_Schema::suppressing_markup() ? [] : ['itemprop' => 'image'];
?>
<div class="lsd-image-slider-wrapper <?php echo (count($gallery) ? 'lsd-has-image' : ''); ?>">
    <?php if (count($gallery)): ?>
    <ul class="lsd-image-slider-slider">
        <?php foreach($gallery as $image_id): ?>
        <?php
            $image_attributes['alt'] = LSD_Entity_Listing::image_alt($post_id, $image_id, $featured_image_added && (int) $image_id === (int) $featured_image_id);
            $image = wp_get_attachment_image($image_id, $size, false, $image_attributes);
            if (!$image) continue;
        ?>
        <li>
            <?php if (in_array($link_method, ['normal', 'blank', 'map', 'lightbox', 'left-panel', 'right-panel', 'bottom-panel'])): ?>
            <a
                data-listing-id="<?php echo esc_attr($post_id); ?>"
                data-listdom-style="<?php echo esc_attr($style); ?>"
                <?php echo $link_method === 'map' ? 'data-listdom-map' : ''; ?>
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
    <div class="lsd-no-image"><i class="lsd-fe-icon fa fa-camera fa-5x"></i></div>
    <?php endif; ?>
</div>
