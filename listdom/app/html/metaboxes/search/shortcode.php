<?php
// no direct access
defined('ABSPATH') || die();

/** @var WP_Post $post */
?>
<div class="lsd-metabox lsd-metabox-search-shortcode">
    <div class="lsd-shortcode"><?php echo '[listdom-search id="'.esc_html($post->ID).'"]'; ?></div>
    <p class="description"><?php esc_html_e("To use this search and filter form, either insert the shortcode into any page or widget, or select this form in the desired skin shortcode settings.", 'listdom'); ?></p>
    <?php /* Security Nonce */ LSD_Form::nonce('lsd_search_cpt', '_lsdnonce'); ?>
</div>
