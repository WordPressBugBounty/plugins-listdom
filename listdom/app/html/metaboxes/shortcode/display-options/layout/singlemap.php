<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_PTypes_Shortcode $this */
/** @var array $options */

$singlemap = $options['singlemap'] ?? [];
?>
<div class="lsd-settings-group-wrapper">
    <div class="lsd-settings-fields-wrapper">
        <div class="lsd-admin-section-heading">
            <h3 class="lsd-my-0 lsd-admin-title"><?php echo esc_html__("Layout", 'listdom'); ?></h3>
            <p class="lsd-admin-description lsd-m-0"><?php echo esc_html__("Control listing limit, pagination, and how single listing pages open from the results.", 'listdom'); ?> </p>
        </div>

        <div class="lsd-form-row">
            <div class="lsd-col-3"><?php echo LSD_Form::label([
                    'class' => 'lsd-fields-label',
                    'title' => esc_html__('Limit', 'listdom'),
                    'for' => 'lsd_display_options_skin_singlemap_limit',
                ]); ?></div>
            <div class="lsd-col-7">
                <?php echo LSD_Form::text([
                    'class' => 'lsd-admin-input',
                    'id' => 'lsd_display_options_skin_singlemap_limit',
                    'name' => 'lsd[display][singlemap][limit]',
                    'value' => $singlemap['limit'] ?? '300'
                ]); ?>
                <p class="lsd-admin-description"><?php esc_html_e("This option controls the number of items displayed on the map. Increasing the limit beyond 300 may significantly slow down the page loading time. We recommend using filter options to include only the listings you want in this shortcode.", 'listdom'); ?></p>
            </div>
        </div>
        <div class="lsd-form-row">
            <div class="lsd-col-3"><?php echo LSD_Form::label([
                    'class' => 'lsd-fields-label',
                    'title' => esc_html__('Map Height', 'listdom'),
                    'for' => 'lsd_display_options_skin_singlemap_map_height',
                ]); ?></div>
            <div class="lsd-col-7">
                <?php echo LSD_Form::text([
                    'class' => 'lsd-admin-input',
                    'id' => 'lsd_display_options_skin_singlemap_map_height',
                    'name' => 'lsd[display][singlemap][map_height]',
                    'value' => $singlemap['map_height'] ?? ''
                ]); ?>
                <p class="lsd-admin-description-tiny lsd-mb-0 lsd-mt-2"><?php esc_html_e("Use this option to set the map height. Enter a value with units, such as 500px or 100vh. If you're unsure, leave it blank.", 'listdom'); ?></p>
            </div>
        </div>
    </div>
</div>
