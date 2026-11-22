<?php
/**
 * @var LSD_PTypes_Template $this
 * @var array $types
 * @var string $nonce
 * @var string $modal_id
 */

$visible_types = is_array($types) ? $types : [];
?>
<div id="<?php echo esc_attr($modal_id); ?>" class="lsd-template-modal wui" aria-hidden="true">
    <div class="lsd-template-modal-backdrop" role="presentation">
        <div class="lsd-template-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="<?php echo esc_attr($modal_id); ?>-title">
            <div class="lsd-template-modal-header">
                <div class="lsd-admin-section-heading">
                    <div class="lsd-admin-title-icon">
                        <i class="wui-icon listdom-icon wui-icon--frontend-dashboard"></i>
                        <h2 id="<?php echo esc_attr($modal_id); ?>-title" class="lsd-admin-title lsd-m-0">
                            <?php echo esc_html__('Create New Template', 'listdom'); ?>
                        </h2>
                    </div>
                    <p class="lsd-admin-description lsd-m-0"><?php esc_html_e('Choose how to build your template, then select its type.', 'listdom'); ?></p>
                </div>
            </div>
            <form id="lsd-template-modal-form" data-required-message="<?php echo esc_attr__( 'Please provide a template name.', 'listdom' ); ?>" data-builder-message="<?php echo esc_attr__('Choose how to build the template.', 'listdom'); ?>" data-type-message="<?php echo esc_attr__('Choose a template type.', 'listdom'); ?>" data-failed-message="<?php echo esc_attr__( 'We could not create the template. Please try again.', 'listdom' ); ?>">
                <div class="lsd-template-modal-body">
                    <div class="lsd-template-modal-error" role="alert"></div>
                    <?php LSD_Form::nonce( 'lsd-create-template', 'nonce' ); ?>
                    <div class="form-field">
                        <label class="lsd-fields-label" for="lsd-template-name">
                            <?php esc_html_e( 'Enter Template Name', 'listdom' ); ?>
                        </label>
                        <input type="text" class="lsd-admin-input" id="lsd-template-name" name="lsd_template_name" required>
                    </div>

                    <div class="lsd-template-step-content">
                        <div class="form-field lsd-template-builder-field" aria-hidden="false">
                            <span class="lsd-fields-label" id="lsd-template-builder-label">
                                <?php esc_html_e('Build With', 'listdom'); ?>
                            </span>
                            <div class="lsd-template-type-options lsd-template-type-options--builders" role="radiogroup" aria-labelledby="lsd-template-builder-label">
                                <div class="lsd-template-type-item">
                                    <input type="radio" id="lsd-template-builder-listdom" name="lsd_template_builder" value="listdom" checked>
                                    <label for="lsd-template-builder-listdom" class="lsd-template-type-option">
                                        <span class="lsd-template-type-option-box">
                                            <span class="lsd-template-type-icon"><span class="dashicons dashicons-layout" aria-hidden="true"></span></span>
                                            <h3 class="lsd-admin-subtitle"><?php esc_html_e('Listdom Template Builder', 'listdom'); ?></h3>
                                            <p class="lsd-admin-description"><?php esc_html_e('Build a layout with Listdom elements.', 'listdom'); ?></p>
                                            <span class="lsd-admin-description-tiny"><span class="lsd-circle"></span><?php esc_html_e('Selected', 'listdom'); ?></span>
                                        </span>
                                    </label>
                                </div>
                                <div class="lsd-template-type-item">
                                    <input type="radio" id="lsd-template-builder-gutenberg" name="lsd_template_builder" value="gutenberg">
                                    <label for="lsd-template-builder-gutenberg" class="lsd-template-type-option">
                                        <span class="lsd-template-type-option-box">
                                            <span class="lsd-template-type-icon"><span class="dashicons dashicons-block-default" aria-hidden="true"></span></span>
                                            <h3 class="lsd-admin-subtitle"><?php esc_html_e('WordPress Block Editor', 'listdom'); ?></h3>
                                            <p class="lsd-admin-description"><?php esc_html_e('Build with Listdom listing blocks.', 'listdom'); ?></p>
                                            <span class="lsd-admin-description-tiny"><span class="lsd-circle"></span><?php esc_html_e('Selected', 'listdom'); ?></span>
                                        </span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="form-field lsd-template-type-field is-inactive" aria-hidden="true" inert>
                            <div class="lsd-template-step-heading">
                                <span class="lsd-fields-label" id="lsd-template-type-label">
                                    <?php esc_html_e('Select Template Type', 'listdom'); ?>
                                </span>
                                <button type="button" class="lsd-text-button lsd-template-change-builder">
                                    <span class="dashicons dashicons-arrow-left-alt2" aria-hidden="true"></span>
                                    <?php esc_html_e('Change builder', 'listdom'); ?>
                                </button>
                            </div>

                            <div class="lsd-template-type-options"
                                 role="radiogroup"
                                 aria-labelledby="lsd-template-type-label">
                                <?php foreach ( $visible_types as $key => $type ) :
                                    if ( ! is_array( $type ) ) continue;
                                    ?>
                                    <div class="lsd-template-type-item">
                                        <input type="radio"
                                               id="<?php echo esc_attr( 'lsd-template-type-' . sanitize_html_class( $key ) ); ?>"
                                               name="lsd_template_type"
                                               value="<?php echo esc_attr( $key ); ?>">
                                        <label for="<?php echo esc_attr( 'lsd-template-type-' . sanitize_html_class( $key ) ); ?>"
                                               class="lsd-template-type-option">
                                            <span class="lsd-template-type-option-box">
                                                <?php if ( isset( $type['icon'] ) ) : ?>
                                                    <span class="lsd-template-type-icon">
                                                        <i class="<?php echo esc_attr( 'wui-icon listdom-icon ' . trim( $type['icon'] ) ); ?>" aria-hidden="true"></i>
                                                    </span>
                                                <?php endif; ?>
                                                <h3 class="lsd-admin-subtitle">
                                                    <?php echo esc_html( $type['label'] ?? '' ); ?>
                                                </h3>
                                                <p class="lsd-admin-description">
                                                    <?php echo esc_html( $type['description'] ?? '' ); ?>
                                                </p>
                                                <span class="lsd-admin-description-tiny">
                                                    <span class="lsd-circle"></span>
                                                    <?php esc_html_e( 'Selected', 'listdom' ); ?>
                                                </span>
                                            </span>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="lsd-template-modal-footer">
                    <p class="lsd-admin-description"><?php esc_html_e( 'You can change template settings later', 'listdom' ); ?></p>
                    <div class="lsd-template-modal-footer-button">
                        <button type="button" class="lsd-text-button lsd-template-modal-cancel">
                            <?php esc_html_e( 'Cancel', 'listdom' ); ?>
                        </button>
                        <button type="submit" class="lsd-primary-button" data-lsd-loading-text="<?php echo esc_attr__( 'Creating Template', 'listdom' ); ?>">
                            <?php esc_html_e( 'Create Template', 'listdom' ); ?>
                            <i class="wui-icon listdom-icon wui-icon--right-arrow"></i>
                        </button>
                    </div>
                </div>

            </form>
        </div>
    </div>
</div>
