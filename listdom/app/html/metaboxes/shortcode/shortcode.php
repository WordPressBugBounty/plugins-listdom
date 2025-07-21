<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_PTypes_Shortcode $this */
/** @var WP_Post $post */
?>
<div class="lsd-metabox lsd-metabox-shortcode">
    <div id="lsd-shortcode-<?php echo esc_html($post->ID); ?>" class="lsd-shortcode"><?php echo '[listdom id="'.esc_html($post->ID).'"]'; ?></div>
    <button data-copied="<?php echo esc_html__('Copied!', 'listdom'); ?>" type="button" class="lsd-copy-shortcode lsd-neutral-button lsd-mb-3" data-target="lsd-shortcode-<?php echo esc_html($post->ID); ?>">
        <?php esc_html_e( 'Copy', 'listdom' ); ?>
    </button>
    <p class="description"><?php esc_html_e("Insert this shortcode inside your desired pages to show filtered listings with your selected skin and style.", 'listdom'); ?></p>
    <?php /* Security Nonce */ LSD_Form::nonce('lsd_shortcode_cpt', '_lsdnonce'); ?>
</div>
