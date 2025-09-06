<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_PTypes_Shortcode $this */
/** @var array $options */

$list = $options['list'] ?? [];
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
                    'for' => 'lsd_display_options_skin_list_limit',
                ]); ?></div>
            <div class="lsd-col-7">
                <?php echo LSD_Form::text([
                    'class' => 'lsd-admin-input',
                    'id' => 'lsd_display_options_skin_list_limit',
                    'name' => 'lsd[display][list][limit]',
                    'value' => $list['limit'] ?? '12'
                ]); ?>
                <p class="lsd-admin-description-tiny lsd-mb-0 lsd-mt-2"><?php esc_html_e("Number of the Listings per page.", 'listdom'); ?></p>
            </div>
        </div>
        <div class="lsd-form-row">
              <div class="lsd-col-3"><?php echo LSD_Form::label([
                    'class' => 'lsd-fields-label',
                    'title' => esc_html__('Pagination Method', 'listdom'),
                    'for' => 'lsd_display_options_skin_list_pagination',
                ]); ?></div>
            <div class="lsd-col-7">
                <?php echo LSD_Form::select([
                    'class' => 'lsd-admin-input',
                    'id' => 'lsd_display_options_skin_list_pagination',
                    'name' => 'lsd[display][list][pagination]',
                    'value' => $list['pagination'] ?? (isset($list['load_more']) && $list['load_more'] == 0 ? 'disabled' : 'loadmore'),
                    'options' => LSD_Base::get_pagination_methods(),
                ]); ?>
                <p class="lsd-admin-description-tiny lsd-mb-0 lsd-mt-2"><?php esc_html_e('Choose how to load additional listings more than the default limit.', 'listdom'); ?></p>
            </div>
        </div>

        <?php $this->field_listing_link('list', $list); ?>
    </div>
</div>
