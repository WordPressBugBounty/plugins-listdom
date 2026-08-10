<?php
/**
 * @var LSD_PTypes_Template $this
 * @var array $types
 * @var string $nonce
 * @var string $modal_id
 */

$has_checked_type = false;
$visible_types = is_array($types) ? $types : [];
?>
<div id="<?php echo esc_attr($modal_id); ?>" class="lsd-template-modal" aria-hidden="true">
    <div class="lsd-template-modal-backdrop" role="presentation">
        <div class="lsd-template-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="<?php echo esc_attr($modal_id); ?>-title">
            <div class="lsd-template-modal-header">
                <div class="lsd-admin-section-heading">
                    <div class="lsd-admin-title-icon">
                        <i class="listdom-icon wbli-frontend-dashboard"></i>
                        <h2 id="<?php echo esc_attr($modal_id); ?>-title" class="lsd-admin-title lsd-m-0">
                            <?php echo esc_html__('Create New Template', 'listdom'); ?>
                        </h2>
                    </div>
                    <p class="lsd-admin-description lsd-m-0"><?php esc_html_e('Choose a template type to get started' , 'listdom'); ?></p>
                </div>
            </div>
            <form id="lsd-template-modal-form" data-required-message="<?php echo esc_attr__( 'Please provide a template name.', 'listdom' ); ?>" data-failed-message="<?php echo esc_attr__( 'We could not create the template. Please try again.', 'listdom' ); ?>">
                <div class="lsd-template-modal-body">
                    <div class="lsd-template-modal-error" role="alert"></div>
                    <?php LSD_Form::nonce( 'lsd-create-template', 'nonce' ); ?>
                    <?php $field_label_id = 'lsd-template-type-label'; ?>
                    <div class="form-field">
                        <label class="lsd-fields-label" for="lsd-template-name">
                            <?php esc_html_e( 'Enter Template Name', 'listdom' ); ?>
                        </label>
                        <input type="text" class="lsd-admin-input" id="lsd-template-name" name="lsd_template_name" required>
                    </div>

                    <div class="form-field">
                        <label class="lsd-fields-label" for="lsd-template-name">
                            <?php esc_html_e( 'Select Template Type', 'listdom' ); ?>
                        </label>

                        <div class="lsd-template-type-options"
                             role="radiogroup"
                             aria-labelledby="<?php echo esc_attr( $field_label_id ); ?>">
                            <?php foreach ( $visible_types as $key => $type ) :
                                if ( ! is_array( $type ) ) continue;
                                $is_checked = isset( $default_type ) && $default_type === $key;
                                if ( ! $has_checked_type && ! $is_checked ) $is_checked = true;
                                if ( $is_checked ) $has_checked_type = true;
                                ?>
                                <div class="lsd-template-type-item">
                                    <input type="radio"
                                           id="<?php echo esc_attr( 'lsd-template-type-' . sanitize_html_class( $key ) ); ?>"
                                           name="lsd_template_type"
                                           value="<?php echo esc_attr( $key ); ?>"
                                        <?php checked( $is_checked ); ?>>
                                    <label for="<?php echo esc_attr( 'lsd-template-type-' . sanitize_html_class( $key ) ); ?>"
                                           class="lsd-template-type-option">
                            <span class="lsd-template-type-option-box">
                                <?php if ( isset( $type['icon'] ) ) : ?>
                                    <span class="lsd-template-type-icon">
                                        <i class="<?php echo esc_attr( 'listdom-icon ' . trim( $type['icon'] ) ); ?>" aria-hidden="true"></i>
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
                <div class="lsd-template-modal-footer">
                    <p class="lsd-admin-description"><?php esc_html_e( 'You can change template settings later', 'listdom' ); ?></p>
                    <div class="lsd-template-modal-footer-button">
                        <button type="button" class="lsd-text-button lsd-template-modal-cancel">
                            <?php esc_html_e( 'Cancel', 'listdom' ); ?>
                        </button>
                        <button type="submit" class="lsd-primary-button" data-lsd-loading-text="<?php echo esc_attr__( 'Creating Template', 'listdom' ); ?>">
                            <?php esc_html_e( 'Create Template', 'listdom' ); ?>
                            <i class="listdom-icon wbli-right-arrow"></i>
                        </button>
                    </div>
                </div>

            </form>
        </div>
    </div>
</div>
