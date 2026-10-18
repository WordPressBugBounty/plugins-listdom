<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Menus_Analytics $this */
/** @var array|null $report */
/** @var array|null $activity */
/** @var array $activity_filters */
/** @var bool $all_listings */
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
/** @var array|null $old_stats */
/** @var bool $old_stats_supported */
/** @var string $period */
/** @var bool $low_data */
/** @var bool $rich_data */
/** @var bool $tracked_report */
/** @var string $report_url */
/** @var bool $listing_filter_active */

// Listing Rows
$listing_report = $tracked_report ? $activity : $report;
$listings = $listing_report['listings'] ?? null;
$has_more_listings = is_array($listings) && ($listing_report['total'] ?? 0) > count($listings);
$unique_views_available = $tracked_report && isset($listings[0]['unique_views']);
$detail_cells_hidden = !$all_listings;

// Full Report Link
$all_url = add_query_arg('analytics_view', 'all', $report_url) . '#lsd-analytics-report-title';
?>
<section class="lsd-admin-box-white lsd-analytics-report lsd-analytics-report-listings" aria-labelledby="lsd-analytics-report-title">
    <div class="lsd-analytics-report-header">
        <div class="lsd-analytics-report-heading">
            <h2 class="lsd-admin-title lsd-analytics-report-title" id="lsd-analytics-report-title">
                <?php echo $all_listings ? ($tracked_report ? esc_html__('All Tracked Listings', 'listdom') : esc_html__('All Published Listings', 'listdom')) : esc_html__('Top Performing Listings', 'listdom'); ?>
                <?php if ($tracked_report && !empty($listing_report['partial'])): ?>
                    <button class="lsd-analytics-report-partial lsd-tooltip" type="button" aria-label="<?php esc_attr_e('Tracking was active for only part of the selected range. Listing totals may be incomplete.', 'listdom'); ?>" data-lsd-tooltip="<?php esc_attr_e('Tracking was active for only part of the selected range. Listing totals may be incomplete.', 'listdom'); ?>">
                        <i class="webilia-icon wbli-question" aria-hidden="true"></i>
                    </button>
                <?php endif; ?>
            </h2>
        </div>
        <?php if ($all_listings || $has_more_listings || ($tracked_report && $listings)): ?>
            <a class="lsd-analytics-report-view-all lsd-text-button<?php echo $all_listings ? '' : ' lsd-analytics-expand-toggle'; ?>" href="<?php echo esc_url($all_listings ? $report_url . '#lsd-analytics-report-title' : $all_url); ?>"<?php if (!$all_listings): ?> data-lsd-expand="listings" data-lsd-nonce="<?php echo esc_attr(wp_create_nonce('lsd_analytics_listings')); ?>" data-lsd-tracked="<?php echo $tracked_report ? '1' : '0'; ?>" data-lsd-total="<?php echo esc_attr($listing_report['total']); ?>" data-lsd-load-error="<?php echo esc_attr__('Could not load more listings. Try again.', 'listdom'); ?>" aria-controls="lsd-analytics-listings-expanded" aria-expanded="false" data-hide-label="<?php echo esc_attr__('View Less', 'listdom'); ?>"<?php endif; ?>>
                <?php echo $all_listings ? esc_html__('Overview', 'listdom') : esc_html__('View All', 'listdom'); ?>
                <i class="lsd-analytics-report-view-all-arrow webilia-icon wbli-right-arrow" aria-hidden="true"></i>
            </a>
        <?php endif; ?>
    </div>

    <?php if (!is_array($listings)): ?>
        <div class="lsd-analytics-report-empty lsd-analytics-report-unavailable" role="status">
            <span class="lsd-analytics-report-empty-icon" aria-hidden="true">
                <i class="lsd-analytics-report-empty-image webilia-icon wbli-list-view"></i>
            </span>
            <h3 class="lsd-admin-title-small lsd-analytics-report-empty-title"><?php esc_html_e('Listing report unavailable', 'listdom'); ?></h3>
            <p class="lsd-admin-description-tiny lsd-analytics-report-empty-copy"><?php esc_html_e('This Stats version cannot provide the lifetime listing report, or the stored counters could not be read.', 'listdom'); ?></p>
        </div>
    <?php elseif (!$listings): ?>
        <div class="lsd-analytics-report-empty" role="status">
            <span class="lsd-analytics-report-empty-icon" aria-hidden="true">
                <i class="lsd-analytics-report-empty-image webilia-icon wbli-list-view"></i>
            </span>
            <h3 class="lsd-admin-title-small lsd-analytics-report-empty-title"><?php echo $tracked_report ? esc_html__('No tracked listings in this selection', 'listdom') : esc_html__('No data available yet', 'listdom'); ?></h3>
            <p class="lsd-admin-description-tiny lsd-analytics-report-empty-copy"><?php echo $tracked_report ? esc_html__('Try a wider date range or clear the listing filters.', 'listdom') : esc_html__('Your top performing listings will appear here after visitors start interacting with your content.', 'listdom'); ?></p>
            <?php if (!$tracked_report): ?>
                <span class="lsd-analytics-report-empty-guidance">
                    <i class="lsd-analytics-report-guidance-icon fas fa-info-circle" aria-hidden="true"></i>
                    <?php esc_html_e('Publish more listings to attract visitors.', 'listdom'); ?>
                </span>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="lsd-analytics-report-table-wrap">
            <table class="lsd-analytics-report-table lsd-admin-table lsd-admin-table-compact">
                <thead class="lsd-analytics-report-thead">
                    <tr class="lsd-analytics-report-row">
                        <th class="lsd-analytics-report-cell lsd-analytics-report-rank" scope="col"><?php esc_html_e('#', 'listdom'); ?></th>
                        <th class="lsd-analytics-report-cell lsd-analytics-report-listing" scope="col"><?php esc_html_e('Listing', 'listdom'); ?></th>
                        <th class="lsd-analytics-report-cell lsd-analytics-report-number lsd-tooltip lsd-tooltip-bottom" scope="col" tabindex="0" data-lsd-tooltip="<?php echo esc_attr($tracked_report ? __('Tracked views in the selected range', 'listdom') : __('Stored lifetime views', 'listdom')); ?>">
                            <?php esc_html_e('Views', 'listdom'); ?>
                            <i class="webilia-icon wbli-question lsd-analytics-tooltip-icon" aria-hidden="true"></i>
                        </th>
                        <?php if ($unique_views_available): ?>
                            <th class="lsd-analytics-report-cell lsd-analytics-report-number lsd-tooltip lsd-tooltip-bottom" scope="col" tabindex="0" data-lsd-detail-column<?php if ($detail_cells_hidden): ?> hidden<?php endif; ?> data-lsd-tooltip="<?php esc_attr_e('One view per browser, listing, and UTC day in the selected range', 'listdom'); ?>">
                                <?php esc_html_e('Daily Unique Views', 'listdom'); ?>
                                <i class="webilia-icon wbli-question lsd-analytics-tooltip-icon" aria-hidden="true"></i>
                            </th>
                        <?php endif; ?>
                        <?php if ($tracked_report): ?>
                            <th class="lsd-analytics-report-cell lsd-analytics-report-number" scope="col" data-lsd-detail-column<?php if ($detail_cells_hidden): ?> hidden<?php endif; ?>><?php esc_html_e('Card Impressions', 'listdom'); ?></th>
                            <th class="lsd-analytics-report-cell lsd-analytics-report-number" scope="col" data-lsd-detail-column<?php if ($detail_cells_hidden): ?> hidden<?php endif; ?>><?php esc_html_e('Card Clicks', 'listdom'); ?></th>
                            <th class="lsd-analytics-report-cell lsd-analytics-report-number lsd-tooltip lsd-tooltip-bottom" scope="col" tabindex="0" data-lsd-detail-column<?php if ($detail_cells_hidden): ?> hidden<?php endif; ?> data-lsd-tooltip="<?php echo esc_attr(!empty($listing_report['card_ctr_partial']) ? __('Card clicks divided by card impressions in the selected range. Tracking for clicks or impressions was incomplete, so this rate may be inaccurate.', 'listdom') : __('Card clicks divided by card impressions in the selected range', 'listdom')); ?>">
                                <?php esc_html_e('Card CTR', 'listdom'); ?>
                                <i class="webilia-icon wbli-question lsd-analytics-tooltip-icon" aria-hidden="true"></i>
                            </th>
                        <?php endif; ?>
                        <th class="lsd-analytics-report-cell lsd-analytics-report-number lsd-tooltip lsd-tooltip-bottom" scope="col" tabindex="0" data-lsd-tooltip="<?php echo esc_attr($tracked_report ? __('Tracked contacts in the selected range', 'listdom') : __('Stored lifetime contact submissions', 'listdom')); ?>">
                            <?php esc_html_e('Contacts', 'listdom'); ?>
                            <i class="webilia-icon wbli-question lsd-analytics-tooltip-icon" aria-hidden="true"></i>
                        </th>
                        <th class="lsd-analytics-report-cell lsd-analytics-report-number" scope="col"><?php esc_html_e('Phone', 'listdom'); ?></th>
                        <th class="lsd-analytics-report-cell lsd-analytics-report-number" scope="col"><?php esc_html_e('Website', 'listdom'); ?></th>
                        <th class="lsd-analytics-report-cell lsd-analytics-report-number" scope="col"><?php esc_html_e('Directions', 'listdom'); ?></th>
                    </tr>
                </thead>
                <tbody class="lsd-analytics-report-tbody">
                    <?php $listing_offset = ($listing_report['page'] - 1) * $listing_report['per_page']; ?>
                    <?php $this->include_html_file('menus/analytics/listing-rows.php', ['parameters' => compact('listings', 'listing_offset', 'tracked_report', 'unique_views_available', 'detail_cells_hidden')]); ?>
                </tbody>
            </table>
        </div>
        <?php if ($all_listings && $listing_report['pages'] > 1): ?>
            <nav class="lsd-analytics-report-pagination" aria-label="<?php esc_attr_e('Top listings pages', 'listdom'); ?>">
                <?php if ($listing_report['page'] > 1): ?>
                    <a class="lsd-analytics-report-page-link" href="<?php echo esc_url(add_query_arg(['analytics_page' => $listing_report['page'] - 1, 'analytics_view' => 'all'], $report_url) . '#lsd-analytics-report-title'); ?>"><?php esc_html_e('Previous', 'listdom'); ?></a>
                <?php endif; ?>
                <?php for ($page = max(1, $listing_report['page'] - 2); $page <= min($listing_report['pages'], $listing_report['page'] + 2); $page++): ?>
                    <?php if ($page === $listing_report['page']): ?>
                        <span class="lsd-analytics-report-page-current" aria-current="page"><?php echo esc_html(number_format_i18n($page)); ?></span>
                    <?php else: ?>
                        <a class="lsd-analytics-report-page-link" href="<?php echo esc_url(add_query_arg(['analytics_page' => $page, 'analytics_view' => 'all'], $report_url) . '#lsd-analytics-report-title'); ?>"><?php echo esc_html(number_format_i18n($page)); ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
                <?php if ($listing_report['page'] < $listing_report['pages']): ?>
                    <a class="lsd-analytics-report-page-link" href="<?php echo esc_url(add_query_arg(['analytics_page' => $listing_report['page'] + 1, 'analytics_view' => 'all'], $report_url) . '#lsd-analytics-report-title'); ?>"><?php esc_html_e('Next', 'listdom'); ?></a>
                <?php endif; ?>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
</section>
<div class="lsd-analytics-report-grid<?php echo $low_data ? ' lsd-analytics-report-grid-low-data' : ''; ?><?php echo !$events_supported ? ' lsd-analytics-report-grid-no-conversion' : ''; ?>">
    <section class="lsd-admin-box-white lsd-analytics-report lsd-analytics-report-history" aria-labelledby="lsd-analytics-history-title">
        <div class="lsd-analytics-report-header">
            <h2 class="lsd-admin-title lsd-analytics-report-title" id="lsd-analytics-history-title"><?php esc_html_e('Views & Engagement', 'listdom'); ?></h2>
            <?php if ($old_stats_supported || (is_array($history) && ($history['started'] ?? '') !== '')): ?>
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
                    <label class="screen-reader-text lsd-analytics-report-period-label" for="lsd-analytics-report-chart-period"><?php esc_html_e('Chart time period or old stats', 'listdom'); ?></label>
                    <select class="lsd-analytics-report-chart-period lsd-admin-input" id="lsd-analytics-report-chart-period" name="analytics_period">
                        <option value="daily"<?php selected($period, 'daily'); ?>><?php esc_html_e('Daily', 'listdom'); ?></option>
                        <option value="weekly"<?php selected($period, 'weekly'); ?>><?php esc_html_e('Weekly', 'listdom'); ?></option>
                        <option value="monthly"<?php selected($period, 'monthly'); ?>><?php esc_html_e('Monthly', 'listdom'); ?></option>
                        <?php if ($events_supported): ?>
                            <option value="yearly"<?php selected($period, 'yearly'); ?>><?php esc_html_e('Yearly', 'listdom'); ?></option>
                        <?php endif; ?>
                        <?php if ($old_stats_supported): ?>
                            <option value="old"<?php selected($period, 'old'); ?>><?php esc_html_e('Old stats before tracking', 'listdom'); ?></option>
                        <?php endif; ?>
                    </select>
                    <button class="screen-reader-text lsd-analytics-report-period-submit" type="submit"><?php esc_html_e('Apply chart period', 'listdom'); ?></button>
                </form>
            <?php endif; ?>
        </div>
        <?php if ($period === 'old' && is_array($old_stats)): ?>
            <div class="lsd-analytics-report-old-stats">
                <div class="lsd-analytics-report-old-stat">
                    <span class="lsd-admin-description-tiny lsd-analytics-report-old-label"><?php esc_html_e('Listing Views', 'listdom'); ?></span>
                    <span class="lsd-admin-title-small lsd-analytics-report-old-value"><?php echo $old_stats['views'] === null ? esc_html__('Unavailable', 'listdom') : esc_html(number_format_i18n($old_stats['views'])); ?></span>
                </div>
                <div class="lsd-analytics-report-old-stat">
                    <span class="lsd-admin-description-tiny lsd-analytics-report-old-label"><?php esc_html_e('Contact Submissions', 'listdom'); ?></span>
                    <span class="lsd-admin-title-small lsd-analytics-report-old-value"><?php echo $old_stats['contacts'] === null ? esc_html__('Unavailable', 'listdom') : esc_html(number_format_i18n($old_stats['contacts'])); ?></span>
                </div>
            </div>
            <p class="lsd-admin-description-tiny lsd-analytics-report-old-note"><?php esc_html_e('These totals were saved before event tracking began. Their original activity dates are unknown.', 'listdom'); ?></p>
        <?php elseif ($period !== 'old' && is_array($history) && !empty($history['has_activity'])): ?>
            <div class="lsd-analytics-report-chart-wrap">
                <canvas class="lsd-analytics-report-chart" id="lsd-analytics-report-chart" role="img" aria-label="<?php esc_attr_e('Dated listing views and contact submissions chart', 'listdom'); ?>" data-series="<?php echo esc_attr(wp_json_encode($history)); ?>" data-views-label="<?php esc_attr_e('Listing Views', 'listdom'); ?>" data-contacts-label="<?php esc_attr_e('Contact Submissions', 'listdom'); ?>" data-partial-label="<?php esc_attr_e('Partial period', 'listdom'); ?>" data-unavailable-label="<?php esc_attr_e('Not collected', 'listdom'); ?>"></canvas>
            </div>
            <table class="screen-reader-text lsd-analytics-report-chart-table">
                <caption class="lsd-analytics-report-chart-caption"><?php esc_html_e('Views and contact submissions by date', 'listdom'); ?></caption>
                <thead class="lsd-analytics-report-chart-thead">
                    <tr class="lsd-analytics-report-chart-row">
                        <th class="lsd-analytics-report-chart-cell" scope="col"><?php esc_html_e('Date', 'listdom'); ?></th>
                        <th class="lsd-analytics-report-chart-cell" scope="col"><?php esc_html_e('Listing Views', 'listdom'); ?></th>
                        <th class="lsd-analytics-report-chart-cell" scope="col"><?php esc_html_e('Contact Submissions', 'listdom'); ?></th>
                    </tr>
                </thead>
                <tbody class="lsd-analytics-report-chart-tbody">
                    <?php foreach ($history['full_labels'] as $index => $label): ?>
                        <tr class="lsd-analytics-report-chart-row">
                            <th class="lsd-analytics-report-chart-cell" scope="row">
                                <?php echo esc_html($label); ?>
                                <?php if (($history['coverage'][$index] ?? '') === 'partial'): ?>
                                    <span class="screen-reader-text lsd-analytics-report-chart-partial"><?php esc_html_e('Partial period', 'listdom'); ?></span>
                                <?php endif; ?>
                            </th>
                            <td class="lsd-analytics-report-chart-cell"><?php echo $history['views'][$index] === null ? esc_html__('Unavailable', 'listdom') : esc_html(number_format_i18n($history['views'][$index])); ?></td>
                            <td class="lsd-analytics-report-chart-cell"><?php echo $history['contacts'][$index] === null ? esc_html__('Unavailable', 'listdom') : esc_html(number_format_i18n($history['contacts'][$index])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="lsd-analytics-report-empty" role="status">
                <span class="lsd-analytics-report-empty-icon" aria-hidden="true">
                    <i class="lsd-analytics-report-empty-image fas fa-chart-bar"></i>
                </span>
                <h3 class="lsd-admin-title-small lsd-analytics-report-empty-title"><?php echo $period === 'old' ? esc_html__('Old stats unavailable', 'listdom') : esc_html__('Not enough data yet', 'listdom'); ?></h3>
                <p class="lsd-admin-description-tiny lsd-analytics-report-empty-copy"><?php echo $period === 'old' ? esc_html__('A pre-tracking baseline was not saved for this site.', 'listdom') : (is_array($history) ? esc_html__('Chart data will appear after new tracking begins and visitors view your listings.', 'listdom') : esc_html__('Dated chart data is unavailable for this Stats version or could not be read.', 'listdom')); ?></p>
                <?php if ($period !== 'old' && is_array($history)): ?>
                    <span class="lsd-analytics-report-empty-guidance">
                        <i class="lsd-analytics-report-guidance-icon fas fa-info-circle" aria-hidden="true"></i>
                        <?php esc_html_e('Keep promoting your directory to get more views!', 'listdom'); ?>
                    </span>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </section>
    <?php if ($events_supported): ?>
        <?php $this->include_html_file('menus/analytics/conversion.php', ['parameters' => compact('conversion')]); ?>
    <?php endif; ?>
</div>
<?php if ($events_supported): ?>
    <div class="lsd-analytics-search-row<?php echo $low_data ? ' lsd-analytics-search-row-low-data' : ' lsd-analytics-search-row-rich'; ?>">
        <?php $this->include_html_file('menus/analytics/search.php', ['parameters' => compact('events_series', 'chart_search_summary', 'period', 'all_listings', 'tracked_report', 'activity_filters', 'rich_data')]); ?>
        <?php if ($rich_data): ?>
            <?php $this->include_html_file('menus/analytics/popular-terms.php', ['parameters' => compact('terms_report', 'all_terms', 'report_url', 'listing_filter_active')]); ?>
        <?php endif; ?>
    </div>
<?php endif; ?>
<?php if ($rich_data): ?>
    <div class="lsd-analytics-breakdown-row">
        <?php $this->include_html_file('menus/analytics/zero-results.php', ['parameters' => compact('search_summary', 'zero_terms_report', 'all_zero_terms', 'report_url', 'listing_filter_active')]); ?>
        <?php $this->include_html_file('menus/analytics/action-clicks.php', ['parameters' => compact('action_groups', 'action_range', 'action_range_supported', 'period', 'all_listings', 'tracked_report', 'activity_filters')]); ?>
    </div>
<?php endif; ?>
