<?php
// no direct access
defined('ABSPATH') || die();

/** @var string $url */
/** @var string $label */
/** @var string $button_class */
?>
<div class="lsd-single-element lsd-single-backbutton">
    <a class="lsd-back-button <?php echo esc_attr($button_class); ?>" href="<?php echo esc_url($url); ?>">
        <i class="lsd-icon fa fa-arrow-left" aria-hidden="true"></i>
        <span><?php echo esc_html($label); ?></span>
    </a>
</div>
