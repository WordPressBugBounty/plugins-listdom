<?php
// no direct access
defined('ABSPATH') || die();

/**
 * @var LSD_Menus_Welcome $this
 * @var array $directory_types
 * @var array $questions
 * @var array $answers
 */
?>
<main class="lsd-admin-wizard" data-dashboard-url="<?php echo esc_url(admin_url('admin.php?page=listdom')); ?>">
    <div class="lsd-admin-wizard__ambient lsd-admin-wizard__ambient--one"></div>
    <div class="lsd-admin-wizard__ambient lsd-admin-wizard__ambient--two"></div>
    <div class="lsd-admin-wizard__ambient lsd-admin-wizard__ambient--three"></div>

    <header class="lsd-admin-wizard__header">
        <a class="lsd-admin-wizard__brand" href="<?php echo esc_url(admin_url('admin.php?page=listdom-welcome')); ?>">
            <img src="<?php echo esc_url($this->lsd_asset_url('img/listdom-logo.svg')); ?>" alt="<?php echo esc_attr(LSD_Branding::name()); ?>">
            <span><?php esc_html_e('Setup Wizard', 'listdom'); ?></span>
        </a>
        <button class="lsd-admin-wizard__close" id="lsd-admin-wizard-close" type="button" aria-label="<?php esc_attr_e('Close setup wizard', 'listdom'); ?>">
            <i class="webilia-icon wbli-cross"></i>
        </button>
    </header>

    <section class="lsd-admin-wizard__card" aria-live="polite">
        <div class="lsd-admin-wizard__progress">
            <span id="lsd-admin-wizard-progress"></span>
        </div>

        <div class="lsd-admin-wizard__content" id="lsd-admin-wizard-content">
            <section class="lsd-admin-wizard__step is-current" data-step="directory">
                <div class="lsd-admin-subsections">
                    <div class="lsd-admin-wizard__question lsd-admin-section-heading">
                        <h1 class="lsd-admin-title"><?php esc_html_e('What would you like to build?', 'listdom'); ?></h1>
                        <p class="lsd-admin-description"><?php esc_html_e('Choose a directory type. We will shape the settings, pages, fields, and sample content around your answer.', 'listdom'); ?></p>
                    </div>

                    <div class="lsd-admin-wizard__choice-grid">
                        <?php foreach ($directory_types as $type): ?>
                            <button class="lsd-admin-wizard__choice<?php echo $type['id'] === 'business_directory' ? ' is-selected' : ''; ?>" type="button" data-blueprint="<?php echo esc_attr($type['id']); ?>">
                                <span class="lsd-admin-wizard__choice-mark">
                                    <i class="webilia-icon <?php echo esc_attr($type['icon']); ?>"></i>
                                </span>
                                <div class="lsd-admin-section-heading">
                                    <h4 class="lsd-admin-title"><?php echo esc_html($type['label']); ?></h4>
                                    <p class="lsd-admin-description-tiny"><?php echo esc_html($type['description']); ?></p>
                                </div>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>

            <?php foreach ($questions as $question): ?>
                <section class="lsd-admin-wizard__step" data-step="question" data-key="<?php echo esc_attr($question['key']); ?>"<?php if (!empty($question['conditional'])): ?> data-conditional="<?php echo esc_attr($question['conditional']); ?>"<?php endif; ?><?php if (!empty($question['blueprints'])): ?> data-blueprints="<?php echo esc_attr(implode(',', $question['blueprints'])); ?>"<?php endif; ?>>
                    <div class="lsd-admin-subsections">
                        <div class="lsd-admin-wizard__question">
                            <div class="lsd-admin-wizard__question-icon">
                                <img src="<?php echo esc_url($this->lsd_asset_url('img/wizard/' . $question['visual'] . '.svg')); ?>" alt="">
                            </div>
                            <div class="lsd-admin-section-heading">
                                <h1 class="lsd-admin-title"><?php echo esc_html($question['title']); ?></h1>
                                <p class="lsd-admin-description"><?php echo esc_html($question['copy']); ?></p>
                            </div>
                        </div>

                        <div class="lsd-admin-wizard__answer-grid">
                            <button class="lsd-admin-wizard__answer-card<?php echo !empty($answers[$question['key']]) ? ' is-selected' : ''; ?>" type="button" data-answer="yes">
                                <span><?php esc_html_e('Yes', 'listdom'); ?></span>
                                <small><?php echo esc_html($question['yes']); ?></small>
                            </button>
                            <button class="lsd-admin-wizard__answer-card<?php echo empty($answers[$question['key']]) ? ' is-selected' : ''; ?>" type="button" data-answer="no">
                                <span><?php esc_html_e('No', 'listdom'); ?></span>
                                <small><?php echo esc_html($question['no']); ?></small>
                            </button>
                        </div>
                    </div>
                </section>
            <?php endforeach; ?>

            <section class="lsd-admin-wizard__step" data-step="complete">
                <div class="lsd-admin-subsections">
                    <div class="lsd-admin-wizard__question">
                        <div class="lsd-admin-wizard__question-icon">
                            <img src="<?php echo esc_url($this->lsd_asset_url('img/wizard/complete.svg')); ?>" alt="">
                        </div>
                        <h1 class="lsd-admin-title"><?php esc_html_e('Your directory is ready', 'listdom'); ?></h1>
                    </div>
                    <div class="lsd-admin-wizard__server-markup" data-completion-markup></div>
                </div>
            </section>
        </div>

        <footer class="lsd-admin-wizard__footer">
            <div class="lsd-admin-wizard__directory-actions lsd-util-hide" id="lsd-admin-wizard-directory-actions">
                <div class="lsd-admin-wizard__sample-listings">
                    <label for="lsd-admin-wizard-demo"><?php esc_html_e('Generate sample listings', 'listdom'); ?></label>
                    <?php echo LSD_Form::switcher([
                        'id' => 'lsd-admin-wizard-demo',
                        'name' => 'lsd_setup_wizard_demo',
                        'value' => 1,
                    ]); ?>
                </div>
                <a class="lsd-admin-wizard__text-button lsd-text-button" href="<?php echo esc_url(admin_url('admin.php?page=listdom')); ?>"><?php esc_html_e('Skip setup', 'listdom'); ?></a>
            </div>
            <button class="lsd-admin-wizard__text-button lsd-util-hide lsd-text-button" id="lsd-admin-wizard-back" type="button"><?php esc_html_e('Back', 'listdom'); ?></button>
            <button class="lsd-admin-wizard__secondary-button lsd-util-hide lsd-secondary-button" id="lsd-admin-wizard-skip" type="button"><?php esc_html_e('Skip for now', 'listdom'); ?></button>
        </footer>
    </section>

    <div class="lsd-admin-wizard__modal" id="lsd-admin-wizard-modal" hidden>
        <div class="lsd-admin-wizard__modal-dialog" role="dialog" aria-modal="true" aria-labelledby="lsd-admin-wizard-modal-title">
            <button class="lsd-admin-wizard__modal-close" id="lsd-admin-wizard-cancel" type="button" aria-label="<?php esc_attr_e('Close confirmation', 'listdom'); ?>">
                <i class="webilia-icon wbli-cross"></i>
            </button>
            <div class="lsd-admin-wizard__modal-icon">
                <i class="webilia-icon wbli-question"></i>
            </div>
            <div class="lsd-admin-section-heading">
                <h2 id="lsd-admin-wizard-modal-title" class="lsd-admin-title"><?php esc_html_e('Leave the setup wizard?', 'listdom'); ?></h2>
                <p class="lsd-admin-description"><?php esc_html_e('Your answers will stay available until you close this browser tab.', 'listdom'); ?></p>
            </div>
            <div class="lsd-admin-wizard__modal-actions">
                <button class="lsd-admin-wizard__secondary-button lsd-secondary-button" id="lsd-admin-wizard-stay" type="button"><?php esc_html_e('Stay here', 'listdom'); ?></button>
                <a class="lsd-admin-wizard__primary-button lsd-primary-button" href="<?php echo esc_url(admin_url('admin.php?page=listdom')); ?>"><?php esc_html_e('Leave wizard', 'listdom'); ?></a>
            </div>
        </div>
    </div>
</main>
