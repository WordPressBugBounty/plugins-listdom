<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Template $this */
?>
<div class="lsd-template-editor-canvas__controls">
    <a href="#" class="lsd-template-editor-canvas__control lsd-template-editor-canvas__control--settings" data-lsd-element-control="settings" aria-label="<?php esc_attr_e('Settings', 'listdom'); ?>" title="<?php esc_attr_e('Settings', 'listdom'); ?>">
        <span class="wbli wbli-general" aria-hidden="true"></span>
        <span class="screen-reader-text"><?php esc_html_e('Settings', 'listdom'); ?></span>
    </a>

    <a href="#" class="lsd-template-editor-canvas__control lsd-template-editor-canvas__control--remove" data-lsd-element-control="remove" aria-label="<?php esc_attr_e('Remove', 'listdom'); ?>" title="<?php esc_attr_e('Remove', 'listdom'); ?>">
        <span class="wbli wbli-cross" aria-hidden="true"></span>
        <span class="screen-reader-text"><?php esc_html_e('Remove', 'listdom'); ?></span>
    </a>
</div>
