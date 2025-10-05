<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_PTypes_Shortcode $this */
/** @var array $options */

$grid = $options['grid'] ?? [];
?>
<div class="lsd-settings-group-wrapper">
    <div class="lsd-settings-fields-wrapper">
        <div class="lsd-admin-section-heading">
            <h3 class="lsd-my-0 lsd-admin-title"><?php echo esc_html__("Layout", 'listdom'); ?></h3>
            <p class="lsd-admin-description lsd-m-0"><?php echo esc_html__("Control listing limit, pagination, and how single listing pages open from the results.", 'listdom'); ?> </p>
        </div>

        <div class="lsd-form-row lsd-display-options-style-not-for lsd-display-options-style-not-for-style4">
            <div class="lsd-col-3"><?php echo LSD_Form::label([
                    'class' => 'lsd-fields-label',
                    'title' => esc_html__('Listings Per Row', 'listdom'),
                    'for' => 'lsd_display_options_skin_grid_columns',
                ]); ?></div>
            <div class="lsd-col-7">
                <?php echo LSD_Form::select([
                    'class' => 'lsd-admin-input',
                    'id' => 'lsd_display_options_skin_grid_columns',
                    'name' => 'lsd[display][grid][columns]',
                    'options' => ['2'=>2, '3'=>3, '4'=>4, '6'=>6],
                    'value' => $grid['columns'] ?? '3'
                ]); ?>
            </div>
        </div>
        <div class="lsd-form-row">
            <div class="lsd-col-3"><?php echo LSD_Form::label([
                    'class' => 'lsd-fields-label',
                    'title' => esc_html__('Limit', 'listdom'),
                    'for' => 'lsd_display_options_skin_grid_limit',
                ]); ?></div>
            <div class="lsd-col-7">
                <?php echo LSD_Form::text([
                    'class' => 'lsd-admin-input',
                    'id' => 'lsd_display_options_skin_grid_limit',
                    'name' => 'lsd[display][grid][limit]',
                    'value' => $grid['limit'] ?? '12'
                ]); ?>
                <p class="lsd-admin-description-tiny lsd-mb-0 lsd-mt-2"><?php echo sprintf(
                    /* translators: 1: Listings per row label, 2: Listings per row label repeated for explanation. */
                    esc_html__("Number of the Listings per page. It should be a multiple of the %1\$s option. For example if the %2\$s is set to 3, then you should set the limit to 3, 6, 9, 12, 30, etc.", 'listdom'),
                    '<strong>'.esc_html__('Listings Per Row', 'listdom').'</strong>',
                    '<strong>'.esc_html__('Listings Per Row', 'listdom').'</strong>'
                ); ?></p>
            </div>
        </div>
        <div class="lsd-form-row">
            <div class="lsd-col-3"><?php echo LSD_Form::label([
                    'class' => 'lsd-fields-label',
                    'title' => esc_html__('Pagination Method', 'listdom'),
                    'for' => 'lsd_display_options_skin_grid_pagination',
                ]); ?></div>
            <div class="lsd-col-7">
                <?php echo LSD_Form::select([
                    'class' => 'lsd-admin-input',
                    'id' => 'lsd_display_options_skin_grid_pagination',
                    'name' => 'lsd[display][grid][pagination]',
                    'value' => $grid['pagination'] ?? (isset($grid['load_more']) && $grid['load_more'] == 0 ? 'disabled' : 'loadmore'),
                    'options' => LSD_Base::get_pagination_methods(),
                ]); ?>
                <p class="lsd-admin-description-tiny lsd-mb-0 lsd-mt-2"><?php esc_html_e('Choose how to load additional listings more than the default limit.', 'listdom'); ?></p>
            </div>
        </div>

        <?php $this->field_listing_link('grid', $grid); ?>
    </div>
</div>

