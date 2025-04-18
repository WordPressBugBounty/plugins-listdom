<?php
// no direct access
defined('ABSPATH') || die();

/** @var array $params */
/** @var int $post_id */
/** @var LSD_Element_Gallery $this */

$width = $params['width'] ?? 1920;
$height = $params['height'] ?? 500;
$lightbox = !isset($params['lightbox']) || $params['lightbox'];
$thumbnail_status = $params['thumbnail_status'] ?? 'image';
$include_thumbnail = $params['include_thumbnail'] ?? false;

$gallery = $this->get_gallery($post_id , $include_thumbnail);

// There is no Gallery!
if (!count($gallery)) return '';

$assets = new LSD_Assets();
$assets->footerOrPreview('<script>
jQuery(document).ready(function()
{
    jQuery(".lsd-gallery-slider").listdomGallerySlider({
        items: 1,
    });
    
    const totalItems = jQuery(".lsd-gallery-slider .lsd-gallery-item").length;

    jQuery(".lsd-gallery-slider-thumbs").listdomGallerySliderThumbnail({
        items: Math.min(4, totalItems),
    });
});
</script>');
?>
<div class="<?php echo $thumbnail_status === 'list' ? 'lsd-gallery-slider-wrapper-list' : 'lsd-gallery-slider-wrapper'; ?>">
    <div class="lsd-gallery-slider lsd-owl-carousel <?php echo $lightbox ? 'lsd-image-lightbox' : ''; ?>" <?php echo lsd_schema()->scope()->type('https://schema.org/ImageGallery'); ?>>
        <?php
            foreach($gallery as $id)
            {
                $thumb = wp_get_attachment_image_src($id, [$width, $height]);
                $full = wp_get_attachment_image_src($id, 'full');

                if(!$thumb || !$full) continue;

                echo '<div class="lsd-gallery-item">
                    <a href="' . esc_url($full[0]) . '" ' . lsd_schema()->associatedMedia() . '>
                        <img alt="" src="' . esc_url($thumb[0]) . '" width="' . esc_attr($width) . '" height="' . esc_attr($height) . '" itemprop="https://schema.org/image">
                    </a>
                </div>';
            }
        ?>
    </div>

    <?php if($thumbnail_status === 'image' || $thumbnail_status == 'list'): ?>
        <!-- Thumbnails -->
        <div class="lsd-gallery-slider-thumbs lsd-owl-carousel">
            <?php
            foreach($gallery as $id)
            {
                $thumb = wp_get_attachment_image_src($id, 'full');
                if(!$thumb) continue;

                echo '<div class="lsd-gallery-thumb-item">
                    <img alt="" src="' . esc_url($thumb[0]) . '" width="300" height="200">
                </div>';
            }
            ?>
        </div>
    <?php endif; ?>
</div>
