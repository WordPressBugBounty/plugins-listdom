<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Menus_Analytics $this */
/** @var array|null $events_series */
/** @var array|null $chart_search_summary */
/** @var string $period */
/** @var bool $all_listings */
/** @var bool $tracked_report */
/** @var bool $rich_data */
/** @var array $activity_filters */
$search_coverage = $events_series['event_coverage']['search_executed'] ?? $events_series['coverage'] ?? [];
$search_available = in_array('full', $search_coverage, true) || in_array('partial', $search_coverage, true);
?>
<section class="lsd-admin-box-white lsd-analytics-report lsd-analytics-search-report" aria-labelledby="lsd-analytics-search-title">
    <div class="lsd-analytics-report-header">
        <h2 class="lsd-admin-title lsd-analytics-report-title" id="lsd-analytics-search-title"><?php esc_html_e('Search Trends', 'listdom'); ?></h2>
        <?php if ($rich_data): ?>
            <form class="lsd-analytics-report-period-form" method="get" action="<?php echo esc_url(admin_url('admin.php')); ?>">
                <input class="lsd-analytics-report-period-page" type="hidden" name="page" value="listdom-analytics">
                <?php if ($all_listings): ?>
                    <input class="lsd-analytics-report-period-view" type="hidden" name="analytics_view" value="all">
                <?php endif; ?>
                <?php if ($tracked_report): ?>
                    <input class="lsd-analytics-report-period-filter" type="hidden" name="analytics_range" value="<?php echo esc_attr($activity_filters['days']); ?>">
                    <input class="lsd-analytics-report-period-filter" type="hidden" name="analytics_location" value="<?php echo esc_attr($activity_filters['location']); ?>">
                    <input class="lsd-analytics-report-period-filter" type="hidden" name="analytics_category" value="<?php echo esc_attr($activity_filters['category']); ?>">
                    <input class="lsd-analytics-report-period-filter" type="hidden" name="analytics_search" value="<?php echo esc_attr($activity_filters['search']); ?>">
                <?php endif; ?>
                <label class="screen-reader-text lsd-analytics-search-period-label" for="lsd-analytics-search-chart-period"><?php esc_html_e('Search chart time period', 'listdom'); ?></label>
                <select class="lsd-analytics-report-chart-period lsd-analytics-search-chart-period lsd-admin-input" id="lsd-analytics-search-chart-period" name="analytics_period">
                    <option value="daily"<?php selected($period === 'old' ? 'daily' : $period, 'daily'); ?>><?php esc_html_e('Daily', 'listdom'); ?></option>
                    <option value="weekly"<?php selected($period, 'weekly'); ?>><?php esc_html_e('Weekly', 'listdom'); ?></option>
                    <option value="monthly"<?php selected($period, 'monthly'); ?>><?php esc_html_e('Monthly', 'listdom'); ?></option>
                    <option value="yearly"<?php selected($period, 'yearly'); ?>><?php esc_html_e('Yearly', 'listdom'); ?></option>
                </select>
                <button class="screen-reader-text lsd-analytics-search-period-submit" type="submit"><?php esc_html_e('Apply search chart period', 'listdom'); ?></button>
            </form>
        <?php endif; ?>
    </div>

    <?php if (is_array($events_series) && $events_series['has_search_activity']): ?>
        <div class="lsd-analytics-report-chart-wrap lsd-analytics-search-chart-wrap">
            <canvas class="lsd-analytics-report-chart lsd-analytics-search-chart" id="lsd-analytics-search-chart" role="img" aria-label="<?php esc_attr_e('Search submissions by date', 'listdom'); ?>" data-series="<?php echo esc_attr(wp_json_encode($events_series)); ?>" data-searches-label="<?php esc_attr_e('Searches', 'listdom'); ?>" data-partial-label="<?php esc_attr_e('Partial period', 'listdom'); ?>" data-unavailable-label="<?php esc_attr_e('Not collected', 'listdom'); ?>"></canvas>
        </div>
        <table class="screen-reader-text lsd-analytics-search-chart-table">
            <caption class="lsd-analytics-search-chart-caption"><?php esc_html_e('Search submissions by date', 'listdom'); ?></caption>
            <thead class="lsd-analytics-search-chart-thead">
                <tr class="lsd-analytics-search-chart-row">
                    <th class="lsd-analytics-search-chart-cell" scope="col"><?php esc_html_e('Date', 'listdom'); ?></th>
                    <th class="lsd-analytics-search-chart-cell" scope="col"><?php esc_html_e('Searches', 'listdom'); ?></th>
                </tr>
            </thead>
            <tbody class="lsd-analytics-search-chart-tbody">
                <?php foreach ($events_series['full_labels'] as $index => $label): ?>
                    <tr class="lsd-analytics-search-chart-row">
                        <th class="lsd-analytics-search-chart-cell" scope="row">
                            <?php echo esc_html($label); ?>
                            <?php if (($search_coverage[$index] ?? '') === 'partial'): ?>
                                <span class="screen-reader-text lsd-analytics-search-chart-partial"><?php esc_html_e('Partial period', 'listdom'); ?></span>
                            <?php endif; ?>
                        </th>
                        <td class="lsd-analytics-search-chart-cell"><?php echo $events_series['searches'][$index] === null ? esc_html__('Unavailable', 'listdom') : esc_html(number_format_i18n($events_series['searches'][$index])); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="lsd-analytics-report-empty lsd-analytics-search-empty" role="status">
            <span class="lsd-analytics-report-empty-icon" aria-hidden="true">
                <i class="lsd-analytics-report-empty-image webilia-icon wbli-search-magnifier"></i>
            </span>
            <h3 class="lsd-admin-title-small lsd-analytics-report-empty-title"><?php echo !empty($events_series['search_filtered_out']) ? esc_html__('Search data unavailable for listing filters', 'listdom') : (!$search_available && is_array($events_series) && $events_series['started'] !== '' ? esc_html__('Search tracking unavailable', 'listdom') : (is_array($events_series) ? esc_html__('Search analytics will appear soon', 'listdom') : esc_html__('Search report unavailable', 'listdom'))); ?></h3>
            <p class="lsd-admin-description-tiny lsd-analytics-report-empty-copy"><?php echo !empty($events_series['search_filtered_out']) ? esc_html__('Searches are not tied to a listing. Clear the location, category, and title filters to see search activity.', 'listdom') : (!$search_available && is_array($events_series) && $events_series['started'] !== '' ? esc_html__('Search collection was unavailable in this period.', 'listdom') : (is_array($events_series) ? esc_html__('This chart will show search activity after visitors start searching your directory.', 'listdom') : esc_html__('Stored search events could not be read.', 'listdom'))); ?></p>
            <?php if (is_array($events_series) && empty($events_series['search_filtered_out'])): ?>
                <span class="lsd-analytics-report-empty-guidance">
                    <i class="lsd-analytics-report-guidance-icon fas fa-info-circle" aria-hidden="true"></i>
                    <?php esc_html_e('Share your site and encourage visitors to search.', 'listdom'); ?>
                </span>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    <?php if (is_array($chart_search_summary) && $chart_search_summary['filter_use'] !== null): ?>
        <?php $filter_help = !empty($chart_search_summary['filter_partial'])
            ? __('One search can use several filter types, so the breakdown counts may overlap. Filter usage tracking covered only part of the selected range, so these totals may be incomplete.', 'listdom')
            : __('One search can use several filter types, so the breakdown counts may overlap.', 'listdom'); ?>
        <details class="lsd-analytics-search-filters">
            <summary class="lsd-analytics-search-filters-toggle">
                <?php esc_html_e('Filters Used', 'listdom'); ?> (<?php echo esc_html(number_format_i18n($chart_search_summary['filter_use'])); ?>)
                <span class="lsd-analytics-search-filters-help lsd-tooltip" tabindex="0" aria-label="<?php echo esc_attr($filter_help); ?>" data-lsd-tooltip="<?php echo esc_attr($filter_help); ?>">
                    <i class="webilia-icon wbli-question lsd-analytics-tooltip-icon" aria-hidden="true"></i>
                </span>
            </summary>
            <dl class="lsd-analytics-search-filters-list">
                <?php foreach (['category_uses' => __('Categories', 'listdom'), 'location_uses' => __('Locations', 'listdom'), 'attribute_uses' => __('Attributes', 'listdom'), 'other_uses' => __('Other Filters', 'listdom')] as $key => $label): ?>
                    <div class="lsd-analytics-search-filters-row">
                        <dt><?php echo esc_html($label); ?></dt>
                        <dd><?php echo esc_html(number_format_i18n($chart_search_summary[$key])); ?></dd>
                    </div>
                <?php endforeach; ?>
            </dl>
        </details>
    <?php endif; ?>
</section>
