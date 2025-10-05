<?php
// no direct access
defined('ABSPATH') || die();
?>
<p class="lsd-admin-description lsd-m-0"><?php echo sprintf(
    /* translators: %s: Skin name. */
    esc_html__('Use the %s skin to create an album of the listings\' images. Additionally, you have the option to include a map.', 'listdom'), '<strong>'.esc_html__('Gallery', 'listdom').'</strong>'
); ?></p>

<?php if ($this->isLite()): ?>
    <div class="lsd-form-row lsd-mb-3">
        <div class="lsd-col-12">
            <p class="lsd-alert lsd-warning"><?php echo LSD_Base::missFeatureMessage(esc_html__('Gallery Skin', 'listdom')); ?></p>
        </div>
    </div>
<?php endif;
