<?php
// no direct access
defined('ABSPATH') || die();

/** @var WP_Post $post */

$visible_from = get_post_meta($post->ID, 'lsd_visible_from', true);
$visible_until = get_post_meta($post->ID, 'lsd_visible_until', true);

$visible_from_id = 'lsd_listing_visible_from_' . $post->ID;
$visible_until_id = 'lsd_listing_visible_until_' . $post->ID;
?>
<div class="lsd-listing-module-visibility <?php echo \LSD_Base::get_lsd_class('box-white'); ?>">
    <div class="lsd-form-row">
        <div class="lsd-col-8">
            <h3 class="<?php echo \LSD_Base::get_lsd_class('title'); ?>"><?php esc_html_e('Expiration', 'listdom-visibility'); ?></h3>
        </div>
    </div>
    <div class="lsd-form-row">
        <div class="lsd-col-2">
            <label class="lsd-fields-label" for="<?php echo esc_attr($visible_from_id); ?>"><?php esc_html_e('Visible From', 'listdom-visibility'); ?></label>
        </div>
        <div class="lsd-col-8">
            <?php echo LSD_Form::datepicker([
                'class' => 'lsd-admin-input',
                'name' => 'lsd[visible_from]',
                'id' => $visible_from_id,
                'value' => trim($visible_from) ? lsd_date('Y-m-d', $visible_from) : '',
            ]); ?>
            <p class="<?php echo \LSD_Base::get_lsd_class('description-tiny'); ?>"><?php esc_html_e('Leave blank to display immediately', 'listdom-visibility'); ?></p>
        </div>
    </div>
    <div class="lsd-form-row">
        <div class="lsd-col-2">
            <label class="lsd-fields-label" for="<?php echo esc_attr($visible_until_id); ?>"><?php esc_html_e('Visible Until', 'listdom-visibility'); ?></label>
        </div>
        <div class="lsd-col-8">
            <?php echo LSD_Form::datepicker([
                'class' => 'lsd-admin-input',
                'name' => 'lsd[visible_until]',
                'id' => $visible_until_id,
                'value' => trim($visible_until) ? lsd_date('Y-m-d', $visible_until) : '',
            ]); ?>
            <p class="<?php echo \LSD_Base::get_lsd_class('description-tiny'); ?>"><?php esc_html_e('Leave blank for unlimited visibility', 'listdom-visibility'); ?></p>
        </div>
    </div>
</div>
