<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_PTypes_Listing $this */
/** @var LSD_Shortcodes_Dashboard|null $dashboard */
/** @var WP_Post $post */

$gallery = get_post_meta($post->ID, 'lsd_gallery', true);
if (!is_array($gallery)) $gallery = [];

$gallery_alt = get_post_meta($post->ID, 'lsd_gallery_alt', true);
if (!is_array($gallery_alt)) $gallery_alt = [];

$gallery_custom_alt = LSD_Entity_Listing::custom_alt();

$gallery_method = $dashboard ? ($this->settings['submission_gallery_method'] ?? 'wp') : 'wp';
if (!is_user_logged_in()) $gallery_method = 'uploader';

$gallery_max_size = $dashboard ? ($this->settings['submission_max_image_upload_size'] ?? '') : '';
$gallery_aspect_ratio = $dashboard ? ($this->settings['submission_image_aspect_ratio'] ?? '') : '';
$gallery_aspect_ratio = is_string($gallery_aspect_ratio) ? trim($gallery_aspect_ratio) : '';
$gallery_aspect_ratio_message = $gallery_aspect_ratio !== ''
    ? sprintf(
        /* translators: %s: Required aspect ratio for images. */
        esc_html__('Please upload images with an aspect ratio close to %s.', 'listdom'),
        $gallery_aspect_ratio
    )
    : '';
$gallery_max_images = $dashboard ? (int) ($this->settings['submission_max_gallery_images'] ?? 0) : 0;
$gallery_max_images_message = $gallery_max_images
    ? esc_attr__('You can upload up to %s gallery images.', 'listdom')
    : '';
$gallery_max_images_remaining_message = $gallery_max_images
    ? esc_attr__('You can only upload %s more image(s).', 'listdom')
    : '';
$has_gallery_images = count($gallery) > 0;
$gallery_button_class = ($gallery_method === 'wp' && LSD_Capability::can('upload_files'))
    ? 'lsd-select-gallery-button'
    : 'lsd-upload-gallery-button';
$gallery_add_button_classes = trim(
    (is_admin() ? 'lsd-neutral-button' : 'lsd-light-button')
    . ' lsd-w-auto lsd-color-m-bg '
    . $gallery_button_class
    . ' '
    . $this->get_text_class()
);
$gallery_uploader_message_id = $dashboard instanceof LSD_Shortcodes_Dashboard
    ? $dashboard->form_id('lsd_listing_gallery_uploader_message')
    : 'lsd_listing_gallery_uploader_message';
$gallery_uploader_id = $dashboard instanceof LSD_Shortcodes_Dashboard
    ? $dashboard->form_id('lsd_listing_gallery_uploader')
    : 'lsd_listing_gallery_uploader';
$gallery_id = $dashboard instanceof LSD_Shortcodes_Dashboard
    ? $dashboard->form_id('lsd_listing_gallery')
    : 'lsd_listing_gallery';
$gallery_alt_id_suffix = $dashboard instanceof LSD_Shortcodes_Dashboard
    ? $dashboard->form_id('')
    : '';
?>
<div class="lsd-listing-gallery-container lsd-listing-module-gallery <?php echo LSD_Base::get_lsd_class('box-white'); ?>" data-aspect-ratio="<?php echo esc_attr($gallery_aspect_ratio); ?>"<?php echo $gallery_aspect_ratio_message !== '' ? ' data-aspect-message="' . esc_attr($gallery_aspect_ratio_message) . '"' : ''; ?>>
    <div class="lsd-form-row <?php echo LSD_Base::get_lsd_class('subsections'); ?> lsd-m-0">

        <div class="lsd-col-2 <?php echo LSD_Base::get_lsd_class('section-heading'); ?>">
            <h3 class="<?php echo \LSD_Base::get_lsd_class('title'); ?>"><?php esc_html_e('Gallery', 'listdom'); ?><?php $dashboard && $dashboard->required_html('_gallery'); ?></h3>
            <?php if ($dashboard && trim($gallery_max_size)): ?>
                <p class="<?php echo LSD_Base::get_lsd_class('description'); ?>"><?php echo sprintf(
                    /* translators: %s: Maximum allowed image size in kilobytes. */
                        esc_html__('You can upload multiple images for your listing gallery. Drag to reorder. Maximum Allowed Image Size:  %s KB', 'listdom'),
                        $gallery_max_size
                    ); ?></p>
            <?php endif; ?>
        </div>
    </div>
    <div class="lsd-form-row <?php echo LSD_Base::get_lsd_class('subsections'); ?> lsd-m-0">
        <div class="lsd-col-12" id="<?php echo esc_attr($gallery_uploader_message_id); ?>"></div>
    </div>
    <?php if ($gallery_method === 'uploader'): ?>
    <div class="lsd-form-row <?php echo LSD_Base::get_lsd_class('subsections'); ?> lsd-m-0">
        <input type="file" class="lsd-util-hide" id="<?php echo esc_attr($gallery_uploader_id); ?>" multiple data-for="#<?php echo esc_attr($gallery_id); ?>" data-name="lsd[_gallery][]" data-aspect-ratio="<?php echo esc_attr($gallery_aspect_ratio); ?>"<?php echo $gallery_aspect_ratio_message !== '' ? ' data-aspect-message="' . esc_attr($gallery_aspect_ratio_message) . '"' : ''; ?>
            <?php if ($gallery_max_images): ?>
                data-max-images="<?php echo esc_attr($gallery_max_images); ?>"
                data-max-images-message="<?php echo $gallery_max_images_message; ?>"
                data-max-images-remaining-message="<?php echo $gallery_max_images_remaining_message; ?>"
            <?php endif; ?>>
    </div>
    <?php endif; ?>
    <div class="lsd-form-row <?php echo LSD_Base::get_lsd_class('subsections'); ?> lsd-m-0">

        <div class="lsd-col-10">
            <div class="lsd-image-placeholder lsd-gallery-placeholder<?php echo $has_gallery_images ? ' lsd-util-hide' : ''; ?>">
                <div class="lsd-image-placeholder-inner">
                    <div class="lsd-image-placeholder-empty">
                        <p class="lsd-image-placeholder-text"><?php esc_html_e('No gallery images selected yet.', 'listdom'); ?></p>
                        <button class="<?php echo esc_attr($gallery_add_button_classes); ?>" data-for="#<?php echo esc_attr($gallery_id); ?>" data-name="lsd[_gallery][]" type="button"><?php esc_html_e('Add Images', 'listdom'); ?></button>
                    </div>
                </div>
            </div>
            <ul id="<?php echo esc_attr($gallery_id); ?>" class="lsd-listing-gallery lsd-sortable<?php echo $has_gallery_images ? '' : ' lsd-util-hide'; ?>" data-custom-alt="<?php echo $gallery_custom_alt ? '1' : '0'; ?>" data-alt-label="<?php echo esc_attr__('Alt Text', 'listdom'); ?>" data-alt-placeholder="<?php echo esc_attr__('Optional image alt text', 'listdom'); ?>" data-alt-id-suffix="<?php echo esc_attr($gallery_alt_id_suffix); ?>">
                <?php foreach ($gallery as $id): $image = wp_get_attachment_image_src($id, [160, 160]); if (!$image) continue; ?>
                <li data-id="<?php echo esc_attr($id); ?>">
                    <input type="hidden" name="lsd[_gallery][]" value="<?php echo esc_attr($id); ?>">
                    <div class="lsd-gallery-image">
                        <img src="<?php echo esc_url($image[0]); ?>" alt="<?php echo esc_attr($id); ?>">
                        <div class="lsd-gallery-actions"><i class="lsd-icon fas fa-trash-alt lsd-remove-gallery-single-button"></i> <i class="lsd-icon fas fa-arrows-alt lsd-handler"></i></div>
                    </div>
                    <?php if ($gallery_custom_alt): ?>
                    <div class="lsd-gallery-alt-fields">
                        <label for="<?php echo esc_attr($this->form_id('lsd_gallery_alt_' . $id)); ?>"><?php esc_html_e('Alt Text', 'listdom'); ?></label>
                        <input class="lsd-admin-input lsd-gallery-alt-text" type="text" name="lsd[_gallery_alt][<?php echo esc_attr($id); ?>]" id="<?php echo esc_attr($this->form_id('lsd_gallery_alt_' . $id)); ?>" value="<?php echo esc_attr($gallery_alt[$id] ?? ''); ?>" placeholder="<?php echo esc_attr(get_post_meta($id, '_wp_attachment_image_alt', true) ?: __('Optional image alt text', 'listdom')); ?>">
                    </div>
                    <?php endif; ?>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    <div class="lsd-form-row <?php echo LSD_Base::get_lsd_class('subsections'); ?> lsd-m-0">

        <div class="lsd-col-10 lsd-gallery-buttons">
            <button class="<?php echo esc_attr($gallery_add_button_classes); ?> lsd-gallery-add-more-button<?php echo $has_gallery_images ? '' : ' lsd-util-hide'; ?>" data-for="#<?php echo esc_attr($gallery_id); ?>" data-name="lsd[_gallery][]" type="button"><?php esc_html_e('Add More Images', 'listdom'); ?></button>
            <button class="lsd-text-button lsd-w-auto lsd-remove-gallery-button <?php echo count($gallery) ? '' : 'lsd-util-hide'; ?>" data-for="#<?php echo esc_attr($gallery_id); ?>" type="button"><?php esc_html_e('Remove All Images', 'listdom'); ?></button>
        </div>
    </div>
</div>
