<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Menus_Analytics $this */
/** @var array $onboarding_steps */
/** @var bool $filters_supported */
?>
<section class="lsd-analytics-onboarding" aria-labelledby="lsd-analytics-onboarding-title">
    <div class="lsd-analytics-onboarding-main">
        <div class="lsd-analytics-onboarding-intro">
            <span class="lsd-analytics-onboarding-icon" aria-hidden="true">
                <i class="lsd-analytics-onboarding-icon-image fas fa-rocket"></i>
            </span>
            <div class="lsd-analytics-onboarding-intro-copy">
                <h2 class="lsd-admin-title lsd-analytics-onboarding-title" id="lsd-analytics-onboarding-title"><?php esc_html_e('Start collecting insights', 'listdom'); ?></h2>
                <p class="lsd-admin-description lsd-analytics-onboarding-description"><?php esc_html_e('Analytics collection is enabled. Visitor activity will appear in the selected date range as it is recorded. Follow the steps on the right to encourage activity.', 'listdom'); ?></p>
            </div>
        </div>
        <div class="lsd-analytics-onboarding-next">
            <h3 class="lsd-admin-title lsd-analytics-onboarding-next-title"><?php esc_html_e('What can you do next?', 'listdom'); ?></h3>
            <ol class="lsd-analytics-onboarding-steps">
                <?php foreach ($onboarding_steps as $index => $step): ?>
                    <li class="lsd-analytics-onboarding-step">
                        <span class="lsd-analytics-onboarding-step-number" aria-hidden="true"><?php echo esc_html(number_format_i18n($index + 1)); ?></span>
                        <div class="lsd-analytics-onboarding-step-copy">
                            <div class="lsd-admin-section-heading">
                                <?php if ($step['url'] !== ''): ?>
                                    <a class="lsd-analytics-onboarding-step-link lsd-admin-title-small" href="<?php echo esc_url($step['url']); ?>">
                                        <?php echo esc_html($step['title']); ?>
                                        <i class="lsd-analytics-onboarding-step-link-icon webilia-icon wbli-link-square" aria-hidden="true"></i>
                                    </a>
                                <?php else: ?>
                                    <span class="lsd-analytics-onboarding-step-title lsd-admin-title-small"><?php echo esc_html($step['title']); ?></span>
                                <?php endif; ?>
                                <span class="lsd-admin-description-tiny lsd-analytics-onboarding-step-description"><?php echo esc_html($step['description']); ?></span>
                            </div>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ol>
        </div>
    </div>
    <?php $this->include_html_file('menus/analytics/source-note.php', ['parameters' => compact('filters_supported')]); ?>
</section>
