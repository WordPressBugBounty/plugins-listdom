<?php
// no direct access
defined('ABSPATH') || die();

/** @var array|null $search_summary */
/** @var array|null $zero_terms_report */
/** @var bool $all_zero_terms */
/** @var string $report_url */
/** @var bool $listing_filter_active */

// Zero-Result Rows
$rows = $zero_terms_report['rows'] ?? [];
$has_more_zero_terms = ($zero_terms_report['total'] ?? 0) > count($rows);
$term_coverage = $zero_terms_report['coverage'] ?? '';
$coverage_note = $term_coverage === 'partial' ? esc_attr__('Partial range: zero-result rankings may be incomplete.', 'listdom') : esc_attr__('Zero-result terms were not collected in this range.', 'listdom');

// Report Links
$view_url = add_query_arg('analytics_zero_terms', 'all', $report_url) . '#lsd-analytics-zero-results-title';
$overview_url = remove_query_arg(['analytics_zero_terms', 'analytics_zero_terms_page'], $report_url) . '#lsd-analytics-zero-results-title';
?>
<section class="lsd-admin-box-white lsd-analytics-report lsd-analytics-zero-results" aria-labelledby="lsd-analytics-zero-results-title">
    <div class="lsd-analytics-report-header">
        <div class="lsd-analytics-report-heading">
            <h2 class="lsd-admin-title lsd-analytics-report-title" id="lsd-analytics-zero-results-title">
                <?php esc_html_e('Zero-result Searches', 'listdom'); ?>
                <?php if ($term_coverage === 'partial' || $term_coverage === 'unavailable'): ?>
                    <button class="lsd-analytics-report-partial lsd-tooltip" type="button" aria-label="<?php echo $coverage_note; ?>" data-lsd-tooltip="<?php echo $coverage_note; ?>">
                        <i class="webilia-icon wbli-question" aria-hidden="true"></i>
                    </button>
                <?php endif; ?>
            </h2>
        </div>
        <?php if (!$listing_filter_active && ($all_zero_terms || $has_more_zero_terms)): ?>
            <a class="lsd-analytics-report-view-all lsd-text-button<?php echo $all_zero_terms ? '' : ' lsd-analytics-expand-toggle'; ?>" href="<?php echo esc_url($all_zero_terms ? $overview_url : $view_url); ?>"<?php if (!$all_zero_terms): ?> data-lsd-expand="zero" aria-controls="lsd-analytics-zero-expanded" aria-expanded="false" data-hide-label="<?php echo esc_attr__('View Less', 'listdom'); ?>" data-lsd-load-error="<?php echo esc_attr__('Could not load more rows. Open the next page.', 'listdom'); ?>" data-lsd-nonce="<?php echo esc_attr(wp_create_nonce('lsd_analytics_terms')); ?>" data-lsd-from="<?php echo esc_attr($zero_terms_report['from'] ?? ''); ?>" data-lsd-total="<?php echo esc_attr($zero_terms_report['total']); ?>"<?php endif; ?>>
                <?php echo $all_zero_terms ? esc_html__('Overview', 'listdom') : esc_html__('View All', 'listdom'); ?>
                <i class="lsd-analytics-report-view-all-arrow webilia-icon wbli-right-arrow" aria-hidden="true"></i>
            </a>
        <?php endif; ?>
    </div>
    <?php if (is_array($search_summary) && $search_summary['zero_results'] !== null): ?>
        <div class="lsd-analytics-zero-results-table-wrap">
            <table class="lsd-analytics-zero-results-table lsd-admin-table lsd-admin-table-compact">
                <caption class="screen-reader-text"><?php echo esc_html(sprintf(__('Total zero-result searches in the chart range: %s. Only searches with saved text appear in the table.', 'listdom'), number_format_i18n($search_summary['zero_results']))); ?></caption>
                <thead>
                    <tr>
                        <th scope="col"><?php esc_html_e('Search Term', 'listdom'); ?></th>
                        <th scope="col"><?php esc_html_e('Searches', 'listdom'); ?></th>
                        <th scope="col"><?php esc_html_e('Action', 'listdom'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($rows): ?>
                        <?php $this->include_html_file('menus/analytics/zero-term-rows.php', ['parameters' => compact('rows')]); ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3"><?php esc_html_e('No saved zero-result search terms in this range yet.', 'listdom'); ?></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if ($all_zero_terms && $zero_terms_report && $zero_terms_report['pages'] > 1): ?>
            <nav class="lsd-analytics-report-pagination" aria-label="<?php esc_attr_e('Zero-result search term pages', 'listdom'); ?>">
                <?php if ($zero_terms_report['page'] > 1): ?>
                    <a class="lsd-analytics-report-page-link" href="<?php echo esc_url(add_query_arg(['analytics_zero_terms' => 'all', 'analytics_zero_terms_page' => $zero_terms_report['page'] - 1], $report_url) . '#lsd-analytics-zero-results-title'); ?>"><?php esc_html_e('Previous', 'listdom'); ?></a>
                <?php endif; ?>
                <span class="lsd-analytics-report-page-current" aria-current="page"><?php echo esc_html(number_format_i18n($zero_terms_report['page'])); ?></span>
                <?php if ($zero_terms_report['page'] < $zero_terms_report['pages']): ?>
                    <a class="lsd-analytics-report-page-link" href="<?php echo esc_url(add_query_arg(['analytics_zero_terms' => 'all', 'analytics_zero_terms_page' => $zero_terms_report['page'] + 1], $report_url) . '#lsd-analytics-zero-results-title'); ?>"><?php esc_html_e('Next', 'listdom'); ?></a>
                <?php endif; ?>
            </nav>
        <?php endif; ?>
    <?php else: ?>
        <div class="lsd-analytics-report-empty lsd-analytics-zero-results-empty" role="status">
            <span class="lsd-analytics-report-empty-icon" aria-hidden="true">
                <i class="lsd-analytics-report-empty-image webilia-icon wbli-search-magnifier"></i>
            </span>
            <h3 class="lsd-admin-title-small lsd-analytics-report-empty-title"><?php echo $listing_filter_active ? esc_html__('Zero-result searches unavailable for listing filters', 'listdom') : esc_html__('Zero-result searches unavailable', 'listdom'); ?></h3>
            <p class="lsd-admin-description-tiny lsd-analytics-report-empty-copy"><?php echo $listing_filter_active ? esc_html__('Clear the location, category, and title filters to see zero-result searches.', 'listdom') : esc_html__('Zero-result searches have not been collected in this chart range.', 'listdom'); ?></p>
        </div>
    <?php endif; ?>
</section>
