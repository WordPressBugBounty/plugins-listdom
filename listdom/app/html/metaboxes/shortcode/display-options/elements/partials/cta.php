<?php
// no direct access
defined('ABSPATH') || die();

/** @var string $skin */
/** @var array $settings */

if (!LSD_Components::cta()) return;
?>
<div class="lsd-elements-subsection">
    <h4 class="lsd-admin-subtitle lsd-m-0"><?php esc_html_e('Call To Action', 'listdom'); ?></h4>
    <div class="lsd-elements-fields lsd-elements-3-column" data-lsd-cta-root>
        <div class="lsd-cta-row">
            <div>
                <?php echo LSD_Form::label([
                    'class' => 'lsd-fields-label',
                    'for' => 'lsd_display_options_skin_' . $skin . '_cta_enable',
                    'title' => esc_html__('Enable', 'listdom'),
                ]); ?>
            </div>
            <div>
                <div data-lsd-cta-enable-toggle>
                    <?php echo LSD_Form::switcher([
                        'id' => 'lsd_display_options_skin_' . $skin . '_cta_enable',
                        'name' => 'lsd[display][' . $skin . '][display_cta]',
                        'value' => $settings['display_cta'] ?? '0',
                    ]); ?>
                </div>
            </div>
        </div>

        <div class="lsd-cta-settings <?php echo !empty($settings['cta']['enabled']) ? '' : 'lsd-util-hide'; ?>"
             id="<?php echo esc_attr('lsd_display_options_skin_' . $skin . '_cta_settings'); ?>"
             data-lsd-cta-settings>

            <div class="lsd-cta-row">
                <div>
                    <?php echo LSD_Form::label([
                        'class' => 'lsd-fields-label-tiny',
                        'for' => 'lsd_display_options_skin_' . $skin . '_cta_inherit',
                        'title' => esc_html__('Inherit', 'listdom'),
                    ]); ?>
                </div>
                <div>
                    <div data-lsd-cta-mode-toggle>
                        <?php echo LSD_Form::switcher([
                            'id' => 'lsd_display_options_skin_' . $skin . '_cta_inherit',
                            'name' => 'lsd[display][' . $skin . '][cta][inherit]',
                            'value' => $settings['cta']['inherit'] ?? '1',
                        ]); ?>
                    </div>
                    <p class="lsd-admin-description-tiny lsd-mb-0 lsd-mt-2">
                        <?php esc_html_e('Inherit call to action settings from listing or global settings', 'listdom'); ?>
                    </p>
                </div>
            </div>

            <div class="lsd-cta-custom-fields <?php echo empty($settings['cta']['inherit']) && !empty($settings['cta']['enabled']) ? '' : 'lsd-util-hide'; ?>"
                 id="<?php echo esc_attr('lsd_display_options_skin_' . $skin . '_cta_custom_fields'); ?>"
                 data-lsd-cta-custom-fields>

                <div class="lsd-cta-row">
                    <div>
                        <?php echo LSD_Form::label([
                            'class' => 'lsd-fields-label-tiny',
                            'for' => 'lsd_display_options_skin_' . $skin . '_cta_text',
                            'title' => esc_html__('Text', 'listdom'),
                        ]); ?>
                    </div>
                    <div>
                        <?php echo LSD_Form::text([
                            'class' => 'lsd-admin-input',
                            'id' => 'lsd_display_options_skin_' . $skin . '_cta_text',
                            'name' => 'lsd[display][' . $skin . '][cta][text]',
                            'value' => $settings['cta']['text'] ?? '',
                        ]); ?>
                        <p class="lsd-admin-description-tiny lsd-mb-0 lsd-mt-2">
                            <?php esc_html_e('Leave empty to use the default "Click Here" button text for this shortcode.', 'listdom'); ?>
                        </p>
                    </div>
                </div>

                <div class="lsd-cta-row">
                    <div>
                        <?php echo LSD_Form::label([
                            'class' => 'lsd-fields-label-tiny',
                            'for' => 'lsd_display_options_skin_' . $skin . '_cta_target',
                            'title' => esc_html__('Action', 'listdom'),
                        ]); ?>
                    </div>
                    <div>
                        <?php echo LSD_Form::select([
                            'class' => 'lsd-admin-input',
                            'id' => 'lsd_display_options_skin_' . $skin . '_cta_target',
                            'name' => 'lsd[display][' . $skin . '][cta][target]',
                            'value' => $settings['cta']['target'] ?? 'details',
                            'options' => array_merge([
                                'details'  => esc_html__('Open listing details page', 'listdom'),
                                'lightbox' => esc_html__('Open listing details in lightbox', 'listdom'),
                                'custom'   => esc_html__('Open a custom link', 'listdom'),
                            ], LSD_Base::isPro() ? [
                                'popup' => esc_html__('Popup with custom content', 'listdom'),
                            ] : []),
                            'attributes' => ['data-lsd-cta-target' => '1'],
                        ]);
                        ?>
                        <?php if (!LSD_Base::isPro()): ?>
                            <div class="lsd-alert-no-my lsd-mt-2">
                                <?php echo LSD_Base::alert(
                                    LSD_Base::missFeatureMessage(esc_html__('Popup CTA', 'listdom'))
                                ); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="lsd-cta-row lsd-cta-target-field <?php echo ($settings['cta']['target'] ?? '') === 'custom' ? '' : 'lsd-util-hide'; ?>"
                     id="<?php echo esc_attr('lsd_display_options_skin_' . $skin . '_cta_target_custom'); ?>"
                     data-lsd-cta-target-field="custom">
                    <div>
                        <?php echo LSD_Form::label([
                            'class' => 'lsd-fields-label-tiny',
                            'for' => 'lsd_display_options_skin_' . $skin . '_cta_url',
                            'title' => esc_html__('Custom Link', 'listdom'),
                        ]); ?>
                    </div>
                    <div>
                        <?php echo LSD_Form::url([
                            'class' => 'lsd-admin-input',
                            'id' => 'lsd_display_options_skin_' . $skin . '_cta_url',
                            'name' => 'lsd[display][' . $skin . '][cta][url]',
                            'value' => $settings['cta']['url'] ?? '',
                            'placeholder' => esc_attr__('https://example.com', 'listdom'),
                        ]); ?>
                    </div>
                </div>

                <?php if (LSD_Base::isPro()): ?>
                    <div class="lsd-cta-row lsd-cta-target-field <?php echo ($settings['cta']['target'] ?? '') === 'popup' ? '' : 'lsd-util-hide'; ?>"
                         id="<?php echo esc_attr('lsd_display_options_skin_' . $skin . '_cta_target_popup'); ?>"
                         data-lsd-cta-target-field="popup">
                        <div>
                            <button type="button" class="lsd-secondary-button"
                                    data-lsd-cta-open-modal="<?php echo esc_attr('lsd_display_options_skin_' . $skin . '_cta_modal'); ?>">
                                <?php esc_html_e('Edit Popup Content', 'listdom'); ?>
                            </button>
                            <p class="lsd-admin-description-tiny lsd-mb-0 lsd-mt-2">
                                <?php esc_html_e('HTML and shortcodes are supported.', 'listdom'); ?>
                            </p>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="lsd-cta-row">
                    <div>
                        <?php echo LSD_Form::label([
                            'class' => 'lsd-fields-label-tiny',
                            'for' => 'lsd_display_options_skin_' . $skin . '_cta_alignment',
                            'title' => esc_html__('Alignment', 'listdom'),
                        ]); ?>
                    </div>
                    <div>
                        <?php echo LSD_Form::select([
                            'class' => 'lsd-admin-input',
                            'id' => 'lsd_display_options_skin_' . $skin . '_cta_alignment',
                            'name' => 'lsd[display][' . $skin . '][cta][alignment]',
                            'value' => $settings['cta']['alignment'] ?? 'left',
                            'options' => [
                                'left'    => esc_html__('Left', 'listdom'),
                                'center'  => esc_html__('Center', 'listdom'),
                                'right'   => esc_html__('Right', 'listdom'),
                                'stretch' => esc_html__('Stretch', 'listdom'),
                            ],
                        ]); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if (LSD_Base::isPro()): ?>
    <div class="lsd-modal lsd-cta-modal lsd-util-hide" id="<?php echo esc_attr('lsd_display_options_skin_' . $skin . '_cta_modal'); ?>">
        <div class="lsd-modal-content">
            <a href="#" class="lsd-modal-close" aria-label="<?php esc_attr_e('Close popup', 'listdom'); ?>">
                <i class="listdom-icon fa fa-times"></i>
            </a>
            <div class="lsd-modal-body">
                <?php echo LSD_Form::editor([
                    'id' => 'lsd_display_options_skin_' . $skin . '_cta_content',
                    'name' => 'lsd[display][' . $skin . '][cta][content]',
                    'value' => $settings['cta']['content'] ?? '',
                    'rows' => 6,
                    'media_buttons' => true,
                    'quicktags' => false,
                ]); ?>
            </div>
        </div>
    </div>
    <textarea class="lsd-util-hide"
          data-lsd-cta-storage="<?php echo esc_attr('lsd_display_options_skin_' . $skin . '_cta_content'); ?>"
          name="<?php echo esc_attr('lsd[display][' . $skin . '][cta][content]'); ?>"><?php echo esc_textarea($settings['cta']['content'] ?? ''); ?></textarea>
<?php else: ?>
    <input type="hidden" name="<?php echo esc_attr('lsd[display][' . $skin . '][cta][content]'); ?>" value="<?php echo esc_attr($settings['cta']['content'] ?? ''); ?>">
<?php endif; ?>

<input type="hidden" name="<?php echo esc_attr('lsd[display][' . $skin . '][cta][mode]'); ?>" id="<?php echo esc_attr('lsd_display_options_skin_' . $skin . '_cta_mode'); ?>" value="<?php echo esc_attr($settings['cta']['mode'] ?? ''); ?>" data-lsd-cta-mode="input">
<input type="hidden" value="<?php echo esc_attr(!empty($settings['cta']['inherit']) ? 'inherit' : 'custom'); ?>" data-lsd-cta-mode-preference>
