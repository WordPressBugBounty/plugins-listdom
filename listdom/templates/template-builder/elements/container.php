<?php
defined('ABSPATH') || die();
/**
 * Container element template.
 *
 * @var LSD_Template_Elements_Container $this
 * @var $content
 */
?>
<div class="lsd-template-editor-canvas__container-item">
    <?php if (is_admin() && (!isset($context) || $context !== 'frontend')) include lsd_template('template-builder/controls.php'); ?>
    <div class="lsd-template-editor-canvas__container">
        <div class="lsd-template-editor-canvas__container-inner lsd-flex">
            <?php if (is_admin() && (!isset($context) || $context !== 'frontend')): ?>
                <div class="lsd-template-editor-canvas__container-placeholder">
                  <span><i class="listdom-icon wbli-add-plus-circle" aria-hidden="true"></i></span>
                </div>
            <?php endif; ?>
            <?php if(isset($content)): ?>
                <?php echo $content; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
