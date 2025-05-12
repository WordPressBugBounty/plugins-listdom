<?php
// no direct access
defined('ABSPATH') || die();

/** @var array $params */
/** @var int $post_id */
/** @var LSD_Element_Gallery $this */

$width = $params['width'] ?? 1920;
$height = $params['height'] ?? 500;
$link_method = isset($params['link_method']) && trim($params['link_method']) ? $params['link_method'] : 'normal';
$lightbox = !isset($params['lightbox']) || $params['lightbox'];
$autoplay = !isset($params['autoplay']) || $params['autoplay'];
$auto_height = !isset($params['auto_height']) || $params['auto_height'];
$loop = isset($params['loop']) && $params['loop'];
$thumbnail_status = $params['thumbnail_status'] ?? 'image';
$navigation_method = $params['navigation_method'] ?? 'dots';
$include_thumbnail = $params['include_thumbnail'] ?? false;

$gallery = $this->get_gallery($post_id , $include_thumbnail);

// There is no Gallery!
if (!count($gallery)) return '';

$assets = new LSD_Assets();
$assets->footerOrPreview('<script>
function listdomGallerySliderInit()
{
    jQuery(".lsd-gallery-slider").listdomGallerySlider({
        items: 1,
        dots: '.($navigation_method === 'dots' ? 'true' : 'false').',
        nav: '.($navigation_method === 'nav' ? 'true' : 'false').',
        autoplay: '.($autoplay ? 'true' : 'false').',
        loop: '.($loop ? 'true' : 'false').',
        autoHeight: '.($auto_height ? 'true' : 'false').',
    });
    
    const totalItems = jQuery(".lsd-gallery-slider .lsd-gallery-item").length;

    jQuery(".lsd-gallery-slider-thumbs").listdomGallerySliderThumbnail({
        items: Math.min(4, totalItems),
    });
};

jQuery(document).ready(listdomGallerySliderInit);
jQuery(document).on("listdom:onload", () => {
    setTimeout(listdomGallerySliderInit, 10);
});
</script>');
?>
<div class="<?php echo $thumbnail_status === 'list' ? 'lsd-gallery-slider-wrapper-list' : 'lsd-gallery-slider-wrapper'; ?>">
    <div class="lsd-gallery-slider lsd-owl-carousel <?php echo $lightbox ? 'lsd-image-lightbox' : ''; ?>" <?php echo lsd_schema()->scope()->type('https://schema.org/ImageGallery'); ?>>
        <?php
            foreach ($gallery as $id)
            {
                $thumb = wp_get_attachment_image_src($id, [$width, $height]);
                $full = wp_get_attachment_image_src($id, 'full');

                if (!$thumb || !$full) continue;
        ?>
            <div class="lsd-gallery-item">
                <?php if (!$lightbox && in_array($link_method, ['normal', 'blank', 'lightbox', 'left-panel', 'right-panel', 'bottom-panel'])): ?>
                    <a
                        data-listing-id="<?php echo esc_attr($post_id); ?>"
                        <?php echo $link_method === 'lightbox' ? 'data-listdom-lightbox' : ''; ?>
                        <?php echo $link_method === 'left-panel' ? 'data-listdom-panel="left"' : ''; ?>
                        <?php echo $link_method === 'right-panel' ? 'data-listdom-panel="right"' : ''; ?>
                        <?php echo $link_method === 'bottom-panel' ? 'data-listdom-panel="bottom"' : ''; ?>
                        href="<?php echo esc_url(get_the_permalink($post_id)); ?>"
                        <?php echo $link_method === 'blank' ? 'target="_blank"' : ''; ?>
                        <?php echo lsd_schema()->associatedMedia(); ?>
                    >
                        <?php echo '<img alt="" src="' . esc_url($thumb[0]) . '" width="' . esc_attr($width) . '" height="' . esc_attr($height) . '" itemprop="https://schema.org/image">'; ?>
                    </a>
                <?php elseif($lightbox): echo '<a href="'.esc_url($full[0]).'"><img alt="" src="' . esc_url($thumb[0]) . '" width="' . esc_attr($width) . '" height="' . esc_attr($height) . '" itemprop="https://schema.org/image"></a>'; ?>
                <?php else: echo '<img alt="" src="' . esc_url($thumb[0]) . '" width="' . esc_attr($width) . '" height="' . esc_attr($height) . '" itemprop="https://schema.org/image">'; ?>
                <?php endif; ?>
            </div>
        <?php
            }
        ?>
    </div>

    <?php if ($thumbnail_status === 'image' || $thumbnail_status === 'list'): ?>
        <!-- Thumbnails -->
        <div class="lsd-gallery-slider-thumbs lsd-owl-carousel">
            <?php
            foreach ($gallery as $id)
            {
                $thumb = wp_get_attachment_image_src($id, 'full');
                if (!$thumb) continue;

                echo '<div class="lsd-gallery-thumb-item">
                    <img alt="" src="' . esc_url($thumb[0]) . '" width="300" height="200">
                </div>';
            }
            ?>
        </div>
    <?php endif; ?>
</div>
