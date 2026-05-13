<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_PTypes_Listing $this */
/** @var LSD_Shortcodes_Dashboard|null $dashboard */
/** @var WP_Post $post */

$remark = get_post_meta($post->ID, 'lsd_remark', true);
?>
<div class="lsd-listing-module-remark <?php echo LSD_Base::get_lsd_class('box-white'); ?>">
    <div class="lsd-form-row">
        <div class="lsd-col-8">
            <div class="<?php echo LSD_Base::get_lsd_class('section-heading'); ?>">
                <h3 class="<?php echo LSD_Base::get_lsd_class('title'); ?>"><?php esc_html_e('Remark', 'listdom'); ?></h3>
                <p class="<?php echo LSD_Base::get_lsd_class('description'); ?>">
                    <?php esc_html_e('It will show to the visitors in a different style so you can use it as remark or an ad remark!', 'listdom'); ?>
                </p>
            </div>
        </div>
    </div>
    <div class="lsd-form-row lsd-remark-row">
        <div class="lsd-col-10">
            <?php wp_editor($remark, 'lsd_remark', [
                'textarea_name' => 'lsd[remark]',
                'textarea_rows' => 6,
                'quicktags' => false,
                'media_buttons' => true,
            ]); ?>
            <p class="<?php echo LSD_Base::get_lsd_class('description-tiny'); ?>"><?php esc_html_e("This message will be displayed to visitors in a distinct style. You can use it as a remark or promotional note.", 'listdom'); ?></p>
        </div>
    </div>
</div>
