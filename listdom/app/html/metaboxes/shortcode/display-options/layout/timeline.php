<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_PTypes_Shortcode $this */
/** @var array $options */

$timeline = $options['timeline'] ?? [];
$horizontal_enabled = isset($timeline['horizontal']) && $timeline['horizontal'];
$vertical_alignment_value = $timeline['vertical_alignment'] ?? 'zigzag';
$vertical_wrapper_classes = 'lsd-settings-fields-sub-wrapper' . ($horizontal_enabled ? ' lsd-util-hide' : '');
$horizontal_wrapper_classes = 'lsd-settings-fields-sub-wrapper' . ($horizontal_enabled ? '' : ' lsd-util-hide');
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
                    'title' => esc_html__('Horizontal Layout', 'listdom'),
                    'for' => 'lsd_display_options_skin_timeline_horizontal',
                ]); ?></div>
            <div class="lsd-col-7">
                <?php echo LSD_Form::switcher([
                    'class' => 'lsd-admin-input',
                    'id' => 'lsd_display_options_skin_timeline_horizontal',
                    'name' => 'lsd[display][timeline][horizontal]',
                    'toggle' => '#lsd_timeline_horizontal_options',
                    'toggle2' => '#lsd_timeline_vertical_options',
                    'value' => $horizontal_enabled ? 1 : 0
                ]); ?>
            </div>
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
                        'title' => esc_html__('Alignment', 'listdom'),
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
        <div id="lsd_timeline_horizontal_options" class="<?php echo esc_attr($horizontal_wrapper_classes); ?>">
            <div class="lsd-form-row">
                <div class="lsd-col-3"><?php echo LSD_Form::label([
                        'class' => 'lsd-fields-label',
                        'title' => esc_html__('Alignment', 'listdom'),
                        'for' => 'lsd_display_options_skin_timeline_horizontal_alignment',
                    ]); ?></div>
                <div class="lsd-col-7">
                    <?php echo LSD_Form::select([
                        'class' => 'lsd-admin-input',
                        'id' => 'lsd_display_options_skin_timeline_horizontal_alignment',
                        'name' => 'lsd[display][timeline][horizontal_alignment]',
                        'options' => [
                            'zigzag' => esc_html__('Zigzag', 'listdom'),
                            'top' => esc_html__('Top', 'listdom'),
                            'bottom' => esc_html__('Bottom', 'listdom'),
                        ],
                        'value' => $timeline['horizontal_alignment'] ?? 'zigzag',
                    ]); ?>
                </div>
            </div>
            <div class="lsd-form-row">
                <div class="lsd-col-3"><?php echo LSD_Form::label([
                        'class' => 'lsd-fields-label',
                        'title' => esc_html__('Listings Per Row', 'listdom'),
                        'for' => 'lsd_display_options_skin_timeline_columns',
                    ]); ?></div>
                <div class="lsd-col-7">
                    <?php echo LSD_Form::select([
                        'class' => 'lsd-admin-input',
                        'id' => 'lsd_display_options_skin_timeline_columns',
                        'name' => 'lsd[display][timeline][columns]',
                        'options' => ['1'=>1, '2'=>2, '3'=>3, '4'=>4],
                        'value' => $timeline['columns'] ?? '3'
                    ]); ?>
                    <p class="lsd-admin-description-tiny lsd-mb-0 lsd-mt-2"><?php esc_html_e('Set how many listings are visible at the same time when the horizontal carousel is enabled.', 'listdom'); ?></p>
                </div>
            </div>
            <div class="lsd-form-row">
                <div class="lsd-col-3"><?php echo LSD_Form::label([
                        'class' => 'lsd-fields-label',
                        'title' => esc_html__('Autoplay', 'listdom'),
                        'for' => 'lsd_display_options_skin_timeline_autoplay',
                    ]); ?></div>
                <div class="lsd-col-7">
                    <?php echo LSD_Form::switcher([
                        'class' => 'lsd-admin-input',
                        'id' => 'lsd_display_options_skin_timeline_autoplay',
                        'name' => 'lsd[display][timeline][autoplay]',
                        'value' => !isset($timeline['autoplay']) || $timeline['autoplay'] ? 1 : 0
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
        <?php $this->field_listing_link('timeline', $timeline); ?>
    </div>
</div>
