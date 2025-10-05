<?php
// no direct access
defined('ABSPATH') || die();
/** @var string $subtab */

$visibility = LSD_Options::addons('visibility');
?>
<div id="lsd_panel_addons_visibility" class="lsd-tab-content<?php echo $subtab === 'visibility' ? ' lsd-tab-content-active' : ''; ?>"<?php echo $subtab === 'visibility' ? '' : ' hidden'; ?>>
    <h3 class="lsd-mt-0 lsd-admin-title"><?php esc_html_e('Listing Visibility', 'listdom-visibility'); ?></h3>
    <div class="lsd-settings-group-wrapper">
        <div class="lsd-settings-fields-wrapper">
            <div class="lsd-row">
                <div class="lsd-col-12">
                    <p class="lsd-admin-description lsd-m-0"><?php esc_html_e('Set the listing visibility date range in the Add/Edit form.', 'listdom-visibility'); ?></p>
                </div>
            </div>
            <div class="lsd-form-row">
                <div class="lsd-col-3"><?php echo LSD_Form::label([
                    'class' => 'lsd-fields-label',
                    'title' => esc_html__('Maximum Visits', 'listdom-visibility'),
                    'for' => 'lsd_addons_visibility_max_visits',
                ]); ?></div>
                <div class="lsd-col-5">
                    <?php echo LSD_Form::number([
                        'class' => 'lsd-admin-input',
                        'id' => 'lsd_addons_visibility_max_visits',
                        'name' => 'addons[visibility][max_visits]',
                        'value' => $visibility['max_visits'] ?? null,
                        'attributes' => [
                            'min' => '0',
                            'step' => '1',
                        ],
                    ]); ?>
                    <p class="lsd-admin-description-tiny lsd-mb-0 lsd-mt-2"><?php esc_html_e('Leave blank for unlimited visits.', 'listdom-visibility'); ?></p>
                </div>
            </div>
        </div>
    </div>
</div>
