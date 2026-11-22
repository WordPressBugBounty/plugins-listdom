<?php
defined('ABSPATH') || die();
?>
<?php // Keep saved page-builder submit styles from overriding the provider palette. ?>
<button class="<?php echo esc_attr($classes); ?>" type="<?php echo $preview ? 'button' : 'submit'; ?>" aria-label="<?php echo esc_attr($text); ?>" style="background: var(--lsd-provider-background) !important; color: var(--lsd-provider-color) !important; border-width: var(--lsd-provider-border-width) !important; border-style: var(--lsd-provider-border-style) !important; border-color: var(--lsd-provider-border-color) !important;">
    <?php if ($logo !== ''): ?>
        <span class="lsd-provider-logo" aria-hidden="true"<?php echo $design['show_logo'] ? '' : ' hidden'; ?>>
            <img src="<?php echo esc_url($logo); ?>" alt="">
        </span>
    <?php endif; ?>
    <span class="lsd-provider-text"<?php echo $show_text ? '' : ' hidden'; ?>><?php echo esc_html($label); ?></span>
</button>
