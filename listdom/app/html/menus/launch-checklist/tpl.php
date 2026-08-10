<?php
defined('ABSPATH') || die();

/** @var LSD_Checklist $checklist */
/** @var array $report */

$health_score = (int) ($report['score'] ?? 0);
$counts = $report['counts'] ?? [];
$sections = $report['sections'] ?? [];
$priority = $report['priority'] ?? [];
$has_priority = count($priority) > 0;
$total = (int) ($report['total'] ?? 0);
$checked_at = (int) ($report['checked_at'] ?? current_time('timestamp'));

$score = LSD_Checklist::get_score($health_score);
?>
<div class="lsd-wrap">
    <?php LSD_Menus::header(__('Help', 'listdom')); ?>

    <div class="lsd-admin-main-wrapper">
        <div class="lsd-launch-checklist-page lsd-admin-sections">
            <div class="lsd-launch-checklist-page-header">
                <div class="lsd-admin-section-heading">
                    <h1 class="lsd-admin-title"><?php esc_html_e('Directory Health & Launch Checklist', 'listdom'); ?></h1>
                    <p class="lsd-admin-description"><?php esc_html_e('Track your directory readiness and fix important items before going live.', 'listdom'); ?></p>
                </div>
                <div class="lsd-flex lsd-gap-3" aria-label="<?php echo esc_attr__('Help', 'listdom'); ?>">
                    <a class="lsd-secondary-button" href="<?php echo esc_url(LSD_Base::getListdomDocsURL()); ?>" target="_blank" rel="noopener noreferrer">
                        <?php esc_html_e('Documentation', 'listdom'); ?>
                        <span aria-hidden="true" class="dashicons dashicons-external"></span>
                    </a>
                    <a class="lsd-secondary-button" href="<?php echo esc_url(LSD_Base::getSupportURL()); ?>" target="_blank" rel="noopener noreferrer">
                        <?php esc_html_e('Support', 'listdom'); ?>
                        <span aria-hidden="true" class="dashicons dashicons-external"></span>
                    </a>
                </div>
            </div>
            <div class="lsd-launch-checklist-hero">
                <div class="lsd-launch-checklist-hero__header">
                    <h2 class="lsd-admin-title"><?php esc_html_e('Directory Health', 'listdom'); ?></h2>
                    <div class="lsd-launch-checklist-hero__meta">
                        <span><?php echo esc_html(sprintf(__('Last checked: %s', 'listdom'), LSD_Base::date($checked_at))); ?></span>
                        <a href="<?php echo esc_url(admin_url('admin.php?page=' . LSD_Checklist::MENU_SLUG . '&refresh=' . time())); ?>" class="lsd-text-button">
                            <i class="webilia-icon wbli-reset"></i>
                            <span><?php esc_html_e('Refresh health check', 'listdom'); ?></span>
                        </a>
                    </div>
                </div>

                <div class="lsd-launch-checklist-summary">
                    <div class="lsd-launch-checklist-score-card">
                        <div class="lsd-launch-checklist-gauge <?php echo esc_attr($score['class']); ?>" style="<?php echo esc_attr('--score:' . $health_score . ';'); ?>">
                            <div class="lsd-launch-checklist-gauge__inner">
                                <strong><?php echo esc_html($health_score); ?>%</strong>
                                <span><?php echo esc_html($score['label']); ?></span>
                            </div>
                        </div>
                        <div class="lsd-launch-checklist-score-copy">
                            <div class="lsd-admin-section-heading">
                                <h2 class="lsd-admin-title"><?php echo esc_html($score['title']); ?></h2>
                                <p class="lsd-admin-description"><?php echo esc_html(sprintf(__('%d important items that still need attention.', 'listdom'), (int) (($counts['warning'] ?? 0) + ($counts['incomplete'] ?? 0)))); ?></p>
                            </div>
                            <div class="lsd-launch-checklist-stats">
                                <div class="lsd-launch-checklist-stat lsd-admin-box-white">
                                    <span class="lsd-launch-checklist-stat__icon is-complete">
                                        <i class="webilia-icon wbli-checkmark-circle"></i>
                                    </span>
                                    <div class="lsd-admin-subsections">
                                        <strong class="lsd-admin-title"><?php echo esc_html((int) ($counts['complete'] ?? 0)); ?></strong>
                                        <p><?php esc_html_e('Complete', 'listdom'); ?></p>
                                    </div>
                                </div>
                                <div class="lsd-launch-checklist-stat lsd-admin-box-white">
                                    <span class="lsd-launch-checklist-stat__icon is-warning">
                                        <i class="webilia-icon wbli-alert"></i>
                                    </span>
                                    <div class="lsd-admin-subsections">
                                        <strong class="lsd-admin-title"><?php echo esc_html((int) ($counts['warning'] ?? 0)); ?></strong>
                                        <p><?php esc_html_e('Warning', 'listdom'); ?></p>
                                    </div>
                                </div>
                                <div class="lsd-launch-checklist-stat lsd-admin-box-white">
                                    <span class="lsd-launch-checklist-stat__icon is-incomplete">
                                        <i class="webilia-icon wbli-cross"></i>
                                    </span>
                                    <div class="lsd-admin-subsections">
                                        <strong class="lsd-admin-title"><?php echo esc_html((int) ($counts['incomplete'] ?? 0)); ?></strong>
                                        <p><?php esc_html_e('Incomplete', 'listdom'); ?></p>
                                    </div>
                                </div>
                                <div class="lsd-launch-checklist-stat lsd-admin-box-white">
                                    <span class="lsd-launch-checklist-stat__icon is-optional">
                                        <i class="webilia-icon wbli-first-aid-kit"></i>
                                    </span>
                                    <div class="lsd-admin-subsections">
                                        <strong class="lsd-admin-title"><?php echo esc_html((int) ($counts['optional'] ?? 0)); ?></strong>
                                        <p><?php esc_html_e('Optional', 'listdom'); ?></p>
                                    </div>
                                </div>
                                <div class="lsd-launch-checklist-stat lsd-admin-box-white">
                                    <span class="lsd-launch-checklist-stat__icon is-skipped">
                                        <i class="webilia-icon fas fa-ban"></i>
                                    </span>
                                    <div class="lsd-admin-subsections">
                                        <strong class="lsd-admin-title"><?php echo esc_html((int) ($counts['skipped'] ?? 0)); ?></strong>
                                        <p><?php esc_html_e('Skipped', 'listdom'); ?></p>
                                    </div>
                                </div>
                            </div>

                            <div class="lsd-launch-checklist-actions">
                                <?php if ($has_priority): ?>
                                    <a href="#lsd-launch-priority-fixes" class="lsd-primary-button lsd-launch-checklist-scroll-link">
                                        <i class="webilia-icon wbli-settings"></i>
                                        <span><?php esc_html_e('Fix Priority Items', 'listdom'); ?></span>
                                    </a>
                                <?php endif; ?>
                                <a href="<?php echo esc_url(LSD_Base::getListdomWelcomeWizardUrl()); ?>" class="lsd-secondary-button">
                                    <i class="webilia-icon wbli-wizard"></i>
                                    <span><?php esc_html_e('Open Setup Wizard', 'listdom'); ?></span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php $section_dom_ids = []; ?>
            <div class="lsd-launch-checklist-grid" id="lsd-launch-checklist-grid">
                <?php $section_index = 1; foreach ($sections as $section_title => $items): ?>
                    <?php $visible_count = 3; ?>
                    <?php
                    $section_dom_id = 'lsd-launch-checklist-section-' . $section_index . '-' . sanitize_title($section_title);
                    $section_dom_ids[$section_title] = $section_dom_id;
                    ?>
                    <section id="<?php echo esc_attr($section_dom_id); ?>" class="lsd-launch-checklist-section<?php echo count($items) > $visible_count ? ' has-more-items' : ''; ?>">
                        <div class="lsd-launch-checklist-section__header">
                            <h2 class="lsd-admin-title">
                                <span class="lsd-launch-checklist-section__header-icon">
                                    <i class="webilia-icon <?php echo esc_attr(LSD_Checklist::get_section_icon($section_title)); ?>"></i>
                                </span>
                                <span><?php echo esc_html($section_index . '. ' . $section_title); ?></span>
                            </h2>
                        </div>
                        <div class="lsd-launch-checklist-items">
                            <?php foreach ($items as $item_index => $item): ?>
                                <article
                                    id="<?php echo esc_attr('lsd-launch-checklist-item-' . sanitize_html_class($item['id'])); ?>"
                                    class="lsd-launch-checklist-item status-<?php echo esc_attr($item['status']); ?><?php echo $item_index >= $visible_count ? ' lsd-launch-checklist-item--extra' : ''; ?>"
                                >
                                    <div class="lsd-launch-checklist-item__row">
                                        <div class="lsd-launch-checklist-item__indicator lsd-fe-icon-button lsd-tooltip" data-lsd-tooltip="<?php echo esc_attr($item['description']); ?>">
                                            <span class="lsd-launch-checklist-item__status-icon status-<?php echo esc_attr($item['status']); ?>" tabindex="0" role="button" aria-label="<?php echo esc_attr($item['description']); ?>">
                                                <i class="webilia-icon <?php echo esc_attr(LSD_Checklist::get_status_icon($item['status'])); ?> lsd-launch-checklist-item__primary-icon"></i>
                                                <i class="webilia-icon wbli-question lsd-launch-checklist-item__question-icon" aria-hidden="true"></i>
                                            </span>
                                        </div>

                                        <div class="lsd-launch-checklist-item__content">
                                            <div class="lsd-launch-checklist-item__topline">
                                                <h3 class="lsd-fields-label">
                                                    <?php echo esc_html($item['title']); ?>
                                                </h3>
                                                <p class="lsd-launch-checklist-item__message">
                                                    <?php echo esc_html($item['message']); ?>
                                                </p>
                                            </div>

                                            <div class="lsd-launch-checklist-item__footer">
                                                <div class="lsd-launch-checklist-item__actions">
                                                    <?php if (!empty($item['action_url']) && !empty($item['action_label'])): ?>
                                                        <a href="<?php echo esc_url($checklist->build_action_url($item)); ?>" class="lsd-text-button lsd-launch-checklist-action-link" target="_blank"><?php echo esc_html($item['action_label']); ?></a>
                                                    <?php endif; ?>

                                                    <?php if (!empty($item['optional']) && $item['status'] !== LSD_Checklist_Result::STATUS_COMPLETE): ?>
                                                        <?php if (!empty($item['skipped'])): ?>
                                                            <a href="<?php echo esc_url($checklist->optional_action_url($item['id'], 'unskip')); ?>" class="lsd-text-button"><?php esc_html_e('Unskip', 'listdom'); ?></a>
                                                        <?php else: ?>
                                                            <a href="<?php echo esc_url($checklist->optional_action_url($item['id'], 'skip')); ?>" class="lsd-text-button"><?php esc_html_e('Skip', 'listdom'); ?></a>
                                                        <?php endif; ?>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                        <?php if (count($items) > $visible_count): ?>
                            <div class="lsd-launch-checklist-section__more">
                                <button type="button" class="lsd-text-button lsd-launch-checklist-more-button" data-show-more="<?php echo esc_attr($visible_count); ?>">
                                    <span data-less-label="<?php esc_attr_e('Show less', 'listdom'); ?>"><?php echo esc_html(sprintf(__('View %d more items', 'listdom'), count($items) - $visible_count)); ?></span>
                                    <i class="webilia-icon wbli-right-arrow"></i>
                                </button>
                            </div>
                        <?php endif; ?>
                    </section>
                <?php $section_index++; endforeach; ?>

                <section class="lsd-launch-checklist-section lsd-launch-checklist-section--priority" id="lsd-launch-priority-fixes">
                    <div class="lsd-launch-checklist-section__header">
                         <span class="lsd-launch-checklist-section__header-icon">
                                <i class="webilia-icon <?php echo esc_attr(LSD_Checklist::get_section_icon(esc_html__('Priority Fixes', 'listdom'))); ?>"></i>
                            </span>
                        <div class="lsd-admin-section-heading">
                            <h2 class="lsd-admin-title"><?php esc_html_e('Priority Fixes', 'listdom'); ?></h2>
                            <p class="lsd-admin-description"><?php esc_html_e('Focus on these critical items to get your directory launch-ready.', 'listdom'); ?></p>
                        </div>
                    </div>
                    <div class="lsd-launch-checklist-priority-list lsd-launch-checklist-items">
                        <?php if (count($priority)): ?>
                            <?php foreach ($priority as $item): ?>
                                <article class="lsd-launch-checklist-item status-<?php echo esc_attr($item['status']); ?>">
                                    <div class="lsd-launch-checklist-item__row">
                                        <div class="lsd-launch-checklist-item__indicator lsd-fe-icon-button lsd-tooltip" data-lsd-tooltip="<?php echo esc_attr($item['description']); ?>">
                                            <span class="lsd-launch-checklist-item__status-icon status-<?php echo esc_attr($item['status']); ?>" tabindex="0" role="button" aria-label="<?php echo esc_attr($item['description']); ?>">
                                                <i class="webilia-icon <?php echo esc_attr(LSD_Checklist::get_status_icon($item['status'])); ?> lsd-launch-checklist-item__primary-icon"></i>
                                                <i class="webilia-icon wbli-question lsd-launch-checklist-item__question-icon" aria-hidden="true"></i>
                                            </span>
                                        </div>
                                        <div class="lsd-launch-checklist-item__content">
                                            <div class="lsd-launch-checklist-item__topline">
                                                <h3 class="lsd-fields-label"><?php echo esc_html($item['title']); ?></h3>
                                                <p class="lsd-launch-checklist-item__message"><?php echo esc_html($item['message']); ?></p>
                                            </div>
                                            <div class="lsd-launch-checklist-item__footer">
                                                <div class="lsd-launch-checklist-item__actions">
                                                    <?php if (!empty($item['action_url'])): ?>
                                                        <a
                                                            href="<?php echo esc_url($checklist->build_action_url($item)); ?>"
                                                            class="lsd-secondary-button lsd-launch-checklist-action-button lsd-launch-checklist-action-link"
                                                            target="_blank"
                                                            data-related-section-id="<?php echo esc_attr($section_dom_ids[$item['category']] ?? ''); ?>"
                                                            data-related-item-id="<?php echo esc_attr('lsd-launch-checklist-item-' . sanitize_html_class($item['id'])); ?>"
                                                        ><?php esc_html_e('Resolve Now', 'listdom'); ?></a>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                            <div class="lsd-launch-checklist-section__more">
                                <a href="#lsd-launch-checklist-grid" class="lsd-text-button lsd-launch-checklist-scroll-link">
                                    <span><?php esc_html_e('View all incomplete items', 'listdom'); ?></span>
                                    <i class="webilia-icon wbli-right-arrow"></i>
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="lsd-alert lsd-success">
                                <?php esc_html_e('No priority fixes right now. Your required launch checklist items look healthy.', 'listdom'); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </section>

                <section class="lsd-launch-checklist-section lsd-launch-checklist-section--summary">
                    <div class="lsd-launch-checklist-section__header">
                        <h2 class="lsd-admin-title">
                            <span class="lsd-launch-checklist-section__header-icon">
                                <i class="webilia-icon <?php echo esc_attr(LSD_Checklist::get_section_icon(esc_html__('Health Check Summary', 'listdom'))); ?>"></i>
                            </span>
                            <span><?php esc_html_e('Health Check Summary', 'listdom'); ?></span>
                        </h2>
                    </div>
                    <div>
                        <div class="lsd-launch-summary-row lsd-launch-summary-row--intro">
                            <div class="lsd-launch-summary-intro">
                                <span><?php esc_html_e('Overall Health Score', 'listdom'); ?></span>
                                <p><?php echo esc_html($score['summary']); ?></p>
                            </div>
                            <strong><?php echo esc_html($health_score); ?>%</strong>
                        </div>
                        <div class="lsd-launch-summary-row">
                            <span><?php esc_html_e('Total items', 'listdom'); ?></span>
                            <strong><?php echo esc_html($total); ?></strong>
                        </div>
                        <div class="lsd-launch-summary-row">
                            <span><?php esc_html_e('Completed', 'listdom'); ?></span>
                            <strong class="is-complete"><?php echo esc_html((int) ($counts['complete'] ?? 0)); ?></strong>
                        </div>
                        <div class="lsd-launch-summary-row">
                            <span><?php esc_html_e('Needs attention', 'listdom'); ?></span>
                            <strong class="is-incomplete"><?php echo esc_html((int) (($counts['warning'] ?? 0) + ($counts['incomplete'] ?? 0))); ?></strong>
                        </div>
                        <div class="lsd-launch-summary-row">
                            <span><?php esc_html_e('Optional', 'listdom'); ?></span>
                            <strong><?php echo esc_html((int) ($counts['optional'] ?? 0)); ?></strong>
                        </div>
                        <div class="lsd-launch-summary-row">
                            <span><?php esc_html_e('Skipped', 'listdom'); ?></span>
                            <strong><?php echo esc_html((int) ($counts['skipped'] ?? 0)); ?></strong>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</div>
