<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_PTypes_Shortcode $this */
/** @var array $options */

$timeline = $options['timeline'] ?? [];
$vertical_alignment_value = $timeline['vertical_alignment'] ?? 'zigzag';
$vertical_wrapper_classes = 'lsd-settings-fields-sub-wrapper';
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
                    'for' => 'lsd_display_options_skin_timeline_limit',
                ]); ?></div>
            <div class="lsd-col-7">
                <?php echo LSD_Form::text([
                    'class' => 'lsd-admin-input',
                    'id' => 'lsd_display_options_skin_timeline_limit',
                    'name' => 'lsd[display][timeline][limit]',
                    'value' => $timeline['limit'] ?? '12'
                ]); ?>
                <p class="lsd-admin-description-tiny lsd-mb-0 lsd-mt-2"><?php esc_html_e('Number of the listings per page.', 'listdom'); ?></p>
            </div>
        </div>
        <div id="lsd_timeline_vertical_options" class="<?php echo esc_attr($vertical_wrapper_classes); ?>">
            <div class="lsd-form-row">
                <div class="lsd-col-3"><?php echo LSD_Form::label([
                        'class' => 'lsd-fields-label',
                        'title' => esc_html__('Vertical Alignment', 'listdom'),
                        'for' => 'lsd_display_options_skin_timeline_vertical_alignment',
                    ]); ?></div>
                <div class="lsd-col-7">
                    <?php echo LSD_Form::select([
                        'class' => 'lsd-admin-input',
                        'id' => 'lsd_display_options_skin_timeline_vertical_alignment',
                        'name' => 'lsd[display][timeline][vertical_alignment]',
                        'options' => [
                            'zigzag' => esc_html__('Zigzag', 'listdom'),
                            'left' => esc_html__('Left', 'listdom'),
                            'right' => esc_html__('Right', 'listdom'),
                        ],
                        'value' => $vertical_alignment_value,
                    ]); ?>
                </div>
            </div>
        </div>
        <div class="lsd-form-row">
            <div class="lsd-col-3"><?php echo LSD_Form::label([
                    'class' => 'lsd-fields-label',
                    'title' => esc_html__('Pagination Method', 'listdom'),
                    'for' => 'lsd_display_options_skin_timeline_pagination',
                ]); ?></div>
            <div class="lsd-col-7">
                <?php echo LSD_Form::select([
                    'class' => 'lsd-admin-input',
                    'id' => 'lsd_display_options_skin_timeline_pagination',
                    'name' => 'lsd[display][timeline][pagination]',
                    'value' => $timeline['pagination'] ?? (isset($timeline['load_more']) && $timeline['load_more'] == 0 ? 'disabled' : 'loadmore'),
                    'options' => LSD_Base::get_pagination_methods(),
                ]); ?>
                <p class="lsd-admin-description-tiny lsd-mb-0 lsd-mt-2"><?php esc_html_e('Choose how to load additional listings more than the default limit.', 'listdom'); ?></p>
            </div>
        </div>
    </div>
</div>

