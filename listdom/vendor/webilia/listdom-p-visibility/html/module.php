<?php
// no direct access
defined('ABSPATH') || die();

/** @var WP_Post $post */

$visible_from = get_post_meta($post->ID, 'lsd_visible_from', true);
$visible_until = get_post_meta($post->ID, 'lsd_visible_until', true);
?>
<div class="lsd-form-group lsd-no-border lsd-mt-0 lsd-listing-module-visibility">
    <div class="lsd-form-row">
        <div class="lsd-col-2"></div>
        <div class="lsd-col-8">
            <h3 class="lsd-my-0"><?php esc_html_e('Visible From', 'listdom-visibility'); ?></h3>
        </div>
    </div>
    <div class="lsd-form-row">
        <div class="lsd-col-2"></div>
        <div class="lsd-col-8">
            <?php echo LSD_Form::datepicker([
                'name' => 'lsd[visible_from]',
                'id' => 'lsd_listing_visible_from',
                'value' => trim($visible_from) ? date('Y-m-d', $visible_from) : '',
            ]); ?>
            <p class="description"><?php esc_html_e('Leave blank to display immediately', 'listdom-visibility'); ?></p>
        </div>
    </div>
    <div class="lsd-form-row lsd-mt-3">
        <div class="lsd-col-2"></div>
        <div class="lsd-col-8">
            <h3 class="lsd-my-0"><?php esc_html_e('Visible Until', 'listdom-visibility'); ?></h3>
        </div>
    </div>
    <div class="lsd-form-row">
        <div class="lsd-col-2"></div>
        <div class="lsd-col-8">
            <?php echo LSD_Form::datepicker([
                'name' => 'lsd[visible_until]',
                'id' => 'lsd_listing_visible_until',
                'value' => trim($visible_until) ? date('Y-m-d', $visible_until) : '',
            ]); ?>
            <p class="description"><?php esc_html_e('Leave blank for unlimited visibility', 'listdom-visibility'); ?></p>
        </div>
    </div>
</div>
