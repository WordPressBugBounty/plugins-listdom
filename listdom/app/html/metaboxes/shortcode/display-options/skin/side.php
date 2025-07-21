<?php
// no direct access
defined('ABSPATH') || die();
?>
<p class="description"><?php echo sprintf(esc_html__('With the %s skin, you can display a list of directories and listings on one side and the single listing page on the other.', 'listdom'), '<strong>'.esc_html__('Side by Side', 'listdom').'</strong>'); ?></p>

<?php if ($this->isLite()): ?>
<div class="lsd-form-row lsd-mb-3">
    <div class="lsd-col-12">
        <p class="lsd-alert lsd-warning"><?php echo LSD_Base::missFeatureMessage(esc_html__('Side by Side Skin', 'listdom')); ?></p>
    </div>
</div>
<?php endif;
