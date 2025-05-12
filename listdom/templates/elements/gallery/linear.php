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

$gallery = $this->get_gallery($post_id, $include_thumbnail);

// There is no Gallery!
if (!count($gallery)) return '';
?>
<div class="lsd-gallery-linear" <?php echo lsd_schema()->scope()->type('https://schema.org/ImageGallery'); ?>>
    <?php
        foreach ($gallery as $id)
        {
            $thumb = wp_get_attachment_image_src($id, [$width, $height]);
            $full = wp_get_attachment_image_src($id, 'full');

            if (!$thumb || !$full) continue;
            ?>
            <div class="lsd-gallery-grid-item">
                <img alt="" src="<?php echo esc_url($thumb[0]); ?>" width="<?php echo esc_attr($width); ?>" height="<?php echo esc_attr($height); ?>" itemprop="https://schema.org/image">
            </div>
            <?php
        }
    ?>

    <button class="lsd-all-photos-button">
        <?php printf(esc_html__('See all %d photos', 'listdom'), count($gallery)); ?>
    </button>

    <div id="lsd-gallery-modal" class="lsd-gallery-modal">
        <div class="lsd-gallery-modal-content">
            <span class="lsd-gallery-modal-close">&times;</span>
            <div class="lsd-gallery-modal-images lsd-image-lightbox">
                <?php foreach ($gallery as $id): ?>
                    <?php
                        $thumb = wp_get_attachment_image_src($id, [$width, $height]);
                        $full = wp_get_attachment_image_src($id, 'full');
                        if (!$full) continue;
                    ?>
                    <div class="lsd-gallery-item">
                        <?php if ($lightbox): echo '<a href="' . esc_url($full[0]) . '"><img alt="" src="' . esc_url($thumb[0]) . '" width="' . esc_attr($width) . '" height="' . esc_attr($height) . '" itemprop="https://schema.org/image"></a>'; ?>
                        <?php else: echo '<img alt="" src="' . esc_url($thumb[0]) . '" width="' . esc_attr($width) . '" height="' . esc_attr($height) . '" itemprop="https://schema.org/image">'; ?>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

</div>
