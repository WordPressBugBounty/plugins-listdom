<?php
// no direct access
defined('ABSPATH') || die();

$visibility = LSD_Options::addons('visibility');
?>
<div id="lsd_panel_addons_visibility" class="lsd-tab-content">
    <h3 class="lsd-mt-0 lsd-admin-title"><?php esc_html_e('Listing Visibility', 'listdom-visibility'); ?></h3>
    <div class="lsd-settings-group-wrapper">
        <div class="lsd-settings-fields-wrapper">
            <div class="lsd-row">
                <div class="lsd-col-12">
                    <p class="description lsd-my-0"><?php esc_html_e('Set the listing visibility date range in the Add/Edit form.', 'listdom-visibility'); ?></p>
                </div>
            </div>
            <div class="lsd-form-row lsd-mt-4">
                <div class="lsd-col-2"><?php echo LSD_Form::label([
                    'title' => esc_html__('Maximum Visits', 'listdom-visibility'),
                    'for' => 'lsd_addons_visibility_max_visits',
                ]); ?></div>
                <div class="lsd-col-4">
                    <?php echo LSD_Form::number([
                        'id' => 'lsd_addons_visibility_max_visits',
                        'name' => 'addons[visibility][max_visits]',
                        'value' => $visibility['max_visits'] ?? null,
                        'attributes' => [
                            'min' => '0',
                            'step' => '1',
                        ],
                    ]); ?>
                    <p class="description lsd-mb-0"><?php esc_html_e('Leave blank for unlimited visits.', 'listdom-visibility'); ?></p>
                </div>
            </div>
        </div>
    </div>
</div>
