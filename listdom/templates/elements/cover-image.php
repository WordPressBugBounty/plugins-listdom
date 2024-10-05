<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Skins $shortcode */
/** @var int $post_id */
/** @var string $link_method */
/** @var array $size */

$shortcode = LSD_Payload::get('shortcode');

// Listing Image
$image = get_the_post_thumbnail($post_id, $size, (string) lsd_schema()->prop('contentUrl'));
?>
<?php if(in_array($link_method, ['normal', 'blank', 'lightbox'])): ?>
<a data-listing-id="<?php echo esc_attr($post_id); ?>" <?php echo $link_method === 'lightbox' ? 'data-listdom-lightbox' : ''; ?> class="lsd-cover-img-wrapper <?php echo (trim($image) ? 'lsd-has-image' : ''); ?>" href="<?php echo esc_url(get_the_permalink($post_id)); ?>" <?php echo $link_method === 'blank' ? 'target="_blank"' : ''; ?> <?php echo lsd_schema()->url()->scope()->type('https://schema.org/ImageObject'); ?>>
    <?php echo (trim($image) ? LSD_Kses::element($image) : '<div class="lsd-no-image"><i class="lsd-icon fa fa-camera fa-5x"></i></div>'); ?>
</a>
<?php else: echo (trim($image) ? LSD_Kses::element($image) : '<div class="lsd-no-image"><i class="lsd-icon fa fa-camera fa-5x"></i></div>'); ?>
<?php endif;