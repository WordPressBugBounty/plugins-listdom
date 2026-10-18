<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Menus_Analytics $this */
/** @var array $cards */
/** @var array $status */
/** @var array $notice */
/** @var array $preview */
/** @var array $preview_metrics */
/** @var array|null $report */
/** @var array|null $history */
/** @var array|null $events_series */
/** @var bool $events_supported */
/** @var array|null $conversion */
/** @var array|null $search_summary */
/** @var array|null $chart_search_summary */
/** @var array|null $terms_report */
/** @var array|null $zero_terms_report */
/** @var bool $all_terms */
/** @var bool $all_zero_terms */
/** @var array $action_groups */
/** @var int $action_range */
/** @var bool $action_range_supported */
/** @var bool $rich_data */
/** @var bool $low_data */
/** @var bool $tracked_report */
/** @var string $report_url */
/** @var array $onboarding_steps */
/** @var array|null $activity */
/** @var bool $activity_supported */
/** @var array $activity_filters */
/** @var bool $filters_supported */
/** @var bool $listing_filter_active */
/** @var array $activity_terms */
/** @var bool $activity_export_available */
/** @var array|null $old_stats */
/** @var bool $old_stats_supported */
/** @var string $period */
/** @var string $events_period */
/** @var bool $all_listings */
?>
<div class="wrap lsd-wrap lsd-analytics-page<?php echo $low_data ? ' lsd-analytics-page-low-data' : ''; ?><?php echo $rich_data ? ' lsd-analytics-page-rich' : ''; ?>">
    <?php LSD_Menus::header(esc_html__('Analytics', 'listdom')); ?>

    <div class="lsd-admin-main-wrapper">
        <?php if (!empty($notice['message'])): ?>
            <div class="lsd-alert lsd-analytics-connect-notice lsd-<?php echo esc_attr(in_array($notice['type'] ?? '', ['error', 'info', 'success', 'warning'], true) ? $notice['type'] : 'info'); ?>" role="status"><?php echo esc_html($notice['message']); ?></div>
        <?php endif; ?>

        <?php if ($status['code'] !== 'disconnected'): ?>
            <section class="lsd-admin-wrapper lsd-analytics-status lsd-analytics-status-<?php echo esc_attr($status['tone']); ?><?php echo $rich_data ? ' lsd-analytics-status-rich' : ''; ?>" aria-labelledby="lsd-analytics-status-title">
                <span class="lsd-analytics-status-icon" aria-hidden="true">
                    <i class="lsd-analytics-status-icon-image webilia-icon <?php echo $status['tone'] === 'success' ? 'wbli-checkmark-circle-fill' : 'wbli-alert'; ?>"></i>
                </span>
                <div class="lsd-analytics-status-copy lsd-admin-subsections">
                    <h2 class="lsd-admin-title lsd-analytics-status-title" id="lsd-analytics-status-title"><?php echo esc_html($status['title']); ?></h2>
                    <p class="lsd-admin-description lsd-analytics-status-description">
                        <?php echo esc_html($status['body']); ?>
                    </p>
                    <?php if (!empty($status['settings_url'])): ?>
                        <a class="lsd-secondary-button" href="<?php echo esc_url($status['settings_url']); ?>"><?php esc_html_e('Open Stats settings', 'listdom'); ?></a>
                    <?php endif; ?>
                </div>
                <?php if ($rich_data && !empty($status['connected']) && $status['tone'] === 'success'): ?>
                    <button class="lsd-analytics-status-dismiss" type="button" aria-label="<?php esc_attr_e('Dismiss analytics status', 'listdom'); ?>">
                        <i class="webilia-icon wbli-cross" aria-hidden="true"></i>
                    </button>
                <?php endif; ?>
                <?php if ($status['tone'] === 'success' && !$rich_data): ?>
                    <span class="lsd-analytics-status-art" aria-hidden="true">
                        <img class="lsd-analytics-status-art-chart" src="<?php echo esc_url($this->lsd_asset_url('img/analytics/summary-illustration.svg')); ?>" alt="">
                        <i class="lsd-analytics-status-art-check webilia-icon wbli-checkmark-circle-fill"></i>
                    </span>
                <?php endif; ?>
            </section>
        <?php else: ?>
            <div class="lsd-admin-wrapper lsd-analytics-intro">
                <div class="lsd-analytics-intro-content">
                    <span class="lsd-analytics-intro-icon" aria-hidden="true">
                        <i class="lsd-analytics-icon-image fas fa-chart-bar"></i>
                    </span>
                    <div class="lsd-analytics-intro-copy">
                        <h2 class="lsd-admin-title">
                            <?php
                                // Translators: %s is the highlighted word "performance".
                                printf(esc_html__('Understand your directory %s', 'listdom'), '<span class="lsd-analytics-intro-emphasis">' . esc_html__('performance', 'listdom') . '</span>');
                            ?>
                        </h2>
                        <p class="lsd-admin-description"><?php esc_html_e('See how your listings perform with the real totals below. Connect your Webilia account to check access to the free Stats Addon and unlock detailed reports, charts, and visitor engagement insights.', 'listdom'); ?></p>
                    </div>
                </div>
                <img class="lsd-analytics-intro-art" src="<?php echo esc_url($this->lsd_asset_url('img/analytics/summary-illustration.svg')); ?>" alt="" aria-hidden="true">
            </div>
        <?php endif; ?>

        <?php if ($filters_supported): ?>
            <?php $this->include_html_file('menus/analytics/filters.php', ['parameters' => compact('activity_filters', 'activity_terms', 'activity_export_available', 'period', 'all_listings', 'status')]); ?>
        <?php endif; ?>

        <div class="lsd-grid lsd-g-4-columns lsd-analytics-cards<?php echo $status['code'] === 'active' ? ' lsd-analytics-cards-active' : ''; ?><?php echo $rich_data ? ' lsd-analytics-cards-rich' : ''; ?><?php echo $low_data ? ' lsd-analytics-cards-low-data' : ''; ?>">
            <?php foreach ($cards as $card): ?>
                <?php $change = $card['change'] ?? null; ?>
                <?php $note = (string) ($card['note'] ?? ''); ?>
                <?php $tooltip_parts = $note !== '' ? [$note] : []; ?>
                <?php $tooltip_parts = array_merge($tooltip_parts, preg_split('/(?<=[.!?;])\s+/u', $change ? $change['tooltip'] : __('A comparable earlier range is unavailable', 'listdom'))); ?>
                <?php if ($card['value'] === null): ?>
                    <?php $tooltip_parts[] = __('This value is unavailable.', 'listdom'); ?>
                <?php elseif (!empty($card['partial'])): ?>
                    <?php $tooltip_parts[] = __('Tracking covered only part of the selected range, so this value may be incomplete.', 'listdom'); ?>
                <?php endif; ?>
                <?php $tooltip = implode("\n────────────\n", $tooltip_parts); ?>
                <div class="lsd-admin-box-white lsd-analytics-card lsd-analytics-card-<?php echo esc_attr($card['icon']); ?>">
                    <span class="lsd-analytics-card-icon" aria-hidden="true">
                        <i class="lsd-analytics-icon-image <?php echo esc_attr($this->iconClass($card['icon'])); ?>"></i>
                    </span>
                    <div class="lsd-analytics-card-content">
                        <h3 class="lsd-admin-subtitle-tiny lsd-analytics-card-label"><?php echo esc_html($card['label']); ?></h3>
                        <span class="lsd-admin-title lsd-analytics-card-value<?php echo $card['value'] === null ? ' lsd-analytics-card-value-unavailable' : ''; ?>">
                            <?php echo $card['value'] === null ? '&mdash;' : esc_html(number_format_i18n($card['value'])); ?>
                        </span>
                        <div class="lsd-analytics-card-stat<?php echo $change ? ' lsd-analytics-card-stat-' . esc_attr($change['direction']) : ''; ?>">
                            <?php if ($change && $change['direction'] !== 'flat'): ?>
                                <i class="webilia-icon wbli-right-arrow lsd-analytics-card-stat-indicator" aria-hidden="true"></i>
                            <?php endif; ?>
                            <span class="lsd-analytics-card-stat-label"><?php echo $change ? esc_html($change['value']) : '&mdash;'; ?></span>
                            <?php if ($change): ?>
                                <span class="lsd-analytics-card-stat-label lsd-analytics-card-stat-period">
                                    <?php echo esc_html($change['period']); ?>
                                </span>
                            <?php endif; ?>
                            <button class="lsd-tooltip" type="button" aria-label="<?php echo esc_attr(implode(' ', $tooltip_parts)); ?>" data-lsd-tooltip="<?php echo esc_attr($tooltip); ?>">
                                <i class="webilia-icon wbli-question lsd-analytics-tooltip-icon" aria-hidden="true"></i>
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if ($status['code'] === 'active'): ?>
            <?php $this->include_html_file('menus/analytics/report.php', ['parameters' => compact('report', 'activity', 'activity_filters', 'all_listings', 'history', 'events_series', 'events_supported', 'conversion', 'search_summary', 'chart_search_summary', 'terms_report', 'zero_terms_report', 'all_terms', 'all_zero_terms', 'action_groups', 'action_range', 'action_range_supported', 'old_stats', 'old_stats_supported', 'period', 'low_data', 'rich_data', 'tracked_report', 'report_url', 'listing_filter_active')]); ?>
            <?php if ($low_data): ?>
                <?php $this->include_html_file('menus/analytics/onboarding.php', ['parameters' => compact('onboarding_steps', 'filters_supported')]); ?>
            <?php endif; ?>
            <?php if (!$low_data): ?>
                <?php $this->include_html_file('menus/analytics/source-note.php', ['parameters' => compact('filters_supported')]); ?>
            <?php endif; ?>
        <?php endif; ?>

        <?php if ($preview): ?>
            <?php $this->include_html_file('menus/analytics/preview.php', ['parameters' => compact('preview', 'preview_metrics')]); ?>
        <?php endif; ?>
    </div>
</div>
