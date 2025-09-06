<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_PTypes_Shortcode $this */
/** @var array $options */

$table = $options['table'] ?? [];
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
                    'for' => 'lsd_display_options_skin_table_limit',
                ]); ?></div>
            <div class="lsd-col-7">
                <?php echo LSD_Form::text([
                    'class' => 'lsd-admin-input',
                    'id' => 'lsd_display_options_skin_table_limit',
                    'name' => 'lsd[display][table][limit]',
                    'value' => $table['limit'] ?? '12',
                ]); ?>
                <p class="lsd-admin-description-tiny lsd-mb-0 lsd-mt-2"><?php esc_html_e("Number of the Listings (table rows) per page", 'listdom'); ?></p>
            </div>
        </div>
        <div class="lsd-form-row">
            <div class="lsd-col-3"><?php echo LSD_Form::label([
                    'class' => 'lsd-fields-label',
                    'title' => esc_html__('Pagination Method', 'listdom'),
                    'for' => 'lsd_display_options_skin_table_pagination',
                ]); ?></div>
            <div class="lsd-col-7">
                <?php echo LSD_Form::select([
                    'class' => 'lsd-admin-input',
                    'id' => 'lsd_display_options_skin_table_pagination',
                    'name' => 'lsd[display][table][pagination]',
                    'value' => $table['pagination'] ?? (isset($table['load_more']) && $table['load_more'] == 0 ? 'disabled' : 'loadmore'),
                    'options' => LSD_Base::get_pagination_methods(),
                ]); ?>
                <p class="lsd-admin-description-tiny lsd-mb-0 lsd-mt-2"><?php esc_html_e('Choose how to load additional listings more than the default limit.', 'listdom'); ?></p>
            </div>
        </div>

        <?php $this->field_listing_link('table', $table); ?>
    </div>
</div>
