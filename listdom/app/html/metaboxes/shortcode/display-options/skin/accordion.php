<?php
// no direct access
defined('ABSPATH') || die();
?>
<p class="lsd-admin-description lsd-m-0"><?php echo sprintf(
    /* translators: %s: Skin name. */
    esc_html__('With the %s skin, you can display directories and listings in a accordion format. Additionally, you have the option to include a map.', 'listdom'),
    '<strong>'.esc_html__('Accordion', 'listdom').'</strong>'
); ?></p>

<?php if ($this->isLite()): ?>
    <div class="lsd-form-row lsd-mb-3">
        <div class="lsd-col-12">
            <p class="lsd-alert lsd-warning"><?php echo LSD_Base::missFeatureMessage(esc_html__('Accordion Skin', 'listdom')); ?></p>
        </div>
    </div>
<?php endif;
