<?php
// no direct access
defined('ABSPATH') || die();

/** @var string $skin */
/** @var array $settings */

$id = 'lsd_display_options_skin_' . $skin . '_clustering_text_color';
?>
<div class="lsd-form-row">
    <div class="lsd-col-3">
        <?php echo LSD_Form::label(['class' => 'lsd-fields-label', 'title' => esc_html__('Cluster Text Color', 'listdom'), 'for' => $id]); ?>
    </div>
    <div class="lsd-col-7">
        <?php echo LSD_Form::colorpicker([
            'id' => $id,
            'name' => 'lsd[display][' . $skin . '][clustering_text_color]',
            'value' => sanitize_hex_color($settings['clustering_text_color'] ?? '') ?: '',
            'default' => '',
        ]); ?>
        <p class="lsd-admin-description-tiny"><?php esc_html_e('Leave empty for automatic contrast on colored bubbles or the selected image style default.', 'listdom'); ?></p>
    </div>
</div>
