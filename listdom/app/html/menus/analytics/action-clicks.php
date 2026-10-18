<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Menus_Analytics $this */
/** @var array $action_groups */
/** @var int $action_range */
/** @var bool $action_range_supported */
/** @var string $period */
/** @var bool $all_listings */
/** @var bool $tracked_report */
/** @var array $activity_filters */

$has_more_actions = count($action_groups) > 1;
$expanded = isset($_GET['analytics_actions_expanded']) && $_GET['analytics_actions_expanded'] === '1';
?>
<section class="lsd-admin-box-white lsd-analytics-report lsd-analytics-action-clicks" aria-labelledby="lsd-analytics-action-clicks-title">
    <div class="lsd-analytics-report-header">
        <h2 class="lsd-admin-title lsd-analytics-report-title" id="lsd-analytics-action-clicks-title"><?php esc_html_e('Action Click Breakdown', 'listdom'); ?></h2>
        <div class="lsd-analytics-action-clicks-controls">
            <?php if ($action_range_supported): ?>
                <form class="lsd-analytics-report-period-form lsd-analytics-action-clicks-range-form" method="get" action="<?php echo esc_url(admin_url('admin.php')); ?>"<?php if (!$expanded): ?> hidden<?php endif; ?>>
                    <input type="hidden" name="page" value="listdom-analytics">
                    <input type="hidden" name="analytics_period" value="<?php echo esc_attr($period); ?>">
                    <input type="hidden" name="analytics_actions_expanded" value="1">
                    <?php if ($all_listings): ?>
                        <input type="hidden" name="analytics_view" value="all">
                    <?php endif; ?>
                    <?php if ($tracked_report): ?>
                        <input type="hidden" name="analytics_location" value="<?php echo esc_attr($activity_filters['location']); ?>">
                        <input type="hidden" name="analytics_category" value="<?php echo esc_attr($activity_filters['category']); ?>">
                        <input type="hidden" name="analytics_search" value="<?php echo esc_attr($activity_filters['search']); ?>">
                    <?php endif; ?>
                    <label class="screen-reader-text" for="lsd-analytics-action-range"><?php esc_html_e('Action breakdown date range', 'listdom'); ?></label>
                    <select class="lsd-analytics-report-chart-period lsd-admin-input lsd-analytics-action-range" id="lsd-analytics-action-range" name="analytics_range">
                        <option value="7"<?php selected($action_range, 7); ?>><?php esc_html_e('Last 7 Days', 'listdom'); ?></option>
                        <option value="30"<?php selected($action_range, 30); ?>><?php esc_html_e('Last 30 Days', 'listdom'); ?></option>
                        <option value="90"<?php selected($action_range, 90); ?>><?php esc_html_e('Last 90 Days', 'listdom'); ?></option>
                    </select>
                    <button class="screen-reader-text" type="submit"><?php esc_html_e('Apply action date range', 'listdom'); ?></button>
                </form>
            <?php endif; ?>
            <?php if ($has_more_actions): ?>
                <button class="lsd-analytics-report-view-all lsd-text-button lsd-analytics-action-clicks-toggle" type="button" aria-controls="lsd-analytics-action-clicks-more" aria-expanded="<?php echo $expanded ? 'true' : 'false'; ?>" data-show-label="<?php echo esc_attr__('View All', 'listdom'); ?>" data-hide-label="<?php echo esc_attr__('View Less', 'listdom'); ?>">
                    <span class="lsd-analytics-action-clicks-toggle-label"><?php echo $expanded ? esc_html__('View Less', 'listdom') : esc_html__('View All', 'listdom'); ?></span>
                    <i class="lsd-analytics-report-view-all-arrow webilia-icon wbli-right-arrow" aria-hidden="true"></i>
                </button>
            <?php endif; ?>
        </div>
    </div>
    <div class="lsd-analytics-action-clicks-metrics">
        <?php foreach ($action_groups as $index => $group): ?>
            <?php if ($index === 1): ?>
                <div class="lsd-analytics-action-clicks-more" id="lsd-analytics-action-clicks-more"<?php if (!$expanded): ?> hidden<?php endif; ?>>
            <?php endif; ?>
            <div class="lsd-analytics-action-clicks-group">
                <h3 class="lsd-admin-title-small lsd-analytics-action-clicks-group-title"><?php echo esc_html($group['label']); ?></h3>
                <div class="lsd-analytics-action-clicks-group-metrics">
                    <?php foreach ($group['actions'] as $action): ?>
                        <?php
                            $share = is_int($action['value'])
                                ? ($group['total'] > 0 ? round($action['value'] * 100 / $group['total']) : 0) : null;
                            $bar_width = $share !== null ? $share
                                : (is_int($action['value']) && $group['largest'] > 0 ? round($action['value'] * 100 / $group['largest']) : 0);
                        ?>
                        <div class="lsd-analytics-action-clicks-metric">
                            <span class="lsd-analytics-action-clicks-icon" aria-hidden="true">
                                <?php if (isset($action['icon_class'])): ?>
                                    <i class="lsd-analytics-action-clicks-brand <?php echo esc_attr($action['icon_class']); ?>"></i>
                                <?php else: ?>
                                    <i class="lsd-analytics-action-clicks-image lsd-analytics-action-clicks-image-<?php echo esc_attr($action['icon']); ?> <?php echo esc_attr($this->iconClass($action['icon'])); ?>"></i>
                                <?php endif; ?>
                            </span>
                            <span class="lsd-admin-description-tiny lsd-m-0 lsd-analytics-action-clicks-label"><?php echo esc_html($action['label']); ?></span>
                            <span class="lsd-analytics-action-clicks-track" aria-hidden="true">
                                <span class="lsd-analytics-action-clicks-fill" style="width: <?php echo esc_attr($bar_width); ?>%"></span>
                            </span>
                            <span class="lsd-admin-title-small lsd-analytics-action-clicks-value"><?php echo $action['value'] === null ? esc_html__('Unavailable', 'listdom') : esc_html(number_format_i18n($action['value'])); ?></span>
                            <span class="lsd-admin-description-tiny lsd-analytics-action-clicks-share lsd-tooltip" tabindex="0" data-lsd-tooltip="<?php echo esc_attr($share === null ? __('This action is unavailable in the shown range', 'listdom') : ($group['comparable'] ? __('Share of available actions in this group', 'listdom') : __('Share of available actions in this group. Tracking was partial or differed between actions, so this share may be inaccurate.', 'listdom'))); ?>">
                                <?php echo $share === null ? '—' : esc_html(number_format_i18n($share) . '%'); ?>
                                <i class="webilia-icon wbli-question lsd-analytics-tooltip-icon" aria-hidden="true"></i>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
        <?php if ($has_more_actions): ?>
                </div>
        <?php endif; ?>
    </div>
</section>
