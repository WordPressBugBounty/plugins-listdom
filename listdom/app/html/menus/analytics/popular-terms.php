<?php
// no direct access
defined('ABSPATH') || die();

/** @var array|null $terms_report */
/** @var bool $all_terms */
/** @var string $report_url */
/** @var bool $listing_filter_active */

// Search Term Rows
$rows = $terms_report['rows'] ?? [];
$has_more_terms = ($terms_report['total'] ?? 0) > count($rows);
$term_coverage = $terms_report['coverage'] ?? '';
$coverage_note = $term_coverage === 'partial' ? esc_attr__('Partial range: search-term rankings may be incomplete.', 'listdom') : esc_attr__('Search terms were not collected in this range.', 'listdom');
$maximum = $rows ? max(1, (int) $rows[0]['searches']) : 1;

// Report Links
$view_url = add_query_arg('analytics_terms', 'all', $report_url) . '#lsd-analytics-popular-terms-title';
$overview_url = remove_query_arg(['analytics_terms', 'analytics_terms_page'], $report_url) . '#lsd-analytics-popular-terms-title';
?>
<section class="lsd-admin-box-white lsd-analytics-report lsd-analytics-popular-terms" aria-labelledby="lsd-analytics-popular-terms-title">
    <div class="lsd-analytics-report-header">
        <div class="lsd-analytics-report-heading">
            <h2 class="lsd-admin-title lsd-analytics-report-title" id="lsd-analytics-popular-terms-title">
                <?php esc_html_e('Popular Search Terms', 'listdom'); ?>
                <?php if ($term_coverage === 'partial' || $term_coverage === 'unavailable'): ?>
                    <button class="lsd-analytics-report-partial lsd-tooltip" type="button" aria-label="<?php echo $coverage_note; ?>" data-lsd-tooltip="<?php echo $coverage_note; ?>">
                        <i class="webilia-icon wbli-question" aria-hidden="true"></i>
                    </button>
                <?php endif; ?>
            </h2>
        </div>
        <?php if (!$listing_filter_active && ($all_terms || $has_more_terms)): ?>
            <a class="lsd-analytics-report-view-all lsd-text-button<?php echo $all_terms ? '' : ' lsd-analytics-expand-toggle'; ?>" href="<?php echo esc_url($all_terms ? $overview_url : $view_url); ?>"<?php if (!$all_terms): ?> data-lsd-expand="terms" aria-controls="lsd-analytics-terms-expanded" aria-expanded="false" data-hide-label="<?php echo esc_attr__('View Less', 'listdom'); ?>" data-lsd-load-error="<?php echo esc_attr__('Could not load more rows. Open the next page.', 'listdom'); ?>" data-lsd-nonce="<?php echo esc_attr(wp_create_nonce('lsd_analytics_terms')); ?>" data-lsd-from="<?php echo esc_attr($terms_report['from'] ?? ''); ?>" data-lsd-total="<?php echo esc_attr($terms_report['total']); ?>" data-lsd-maximum="<?php echo esc_attr($maximum); ?>"<?php endif; ?>>
                <?php echo $all_terms ? esc_html__('Overview', 'listdom') : esc_html__('View All', 'listdom'); ?>
                <i class="lsd-analytics-report-view-all-arrow webilia-icon wbli-right-arrow" aria-hidden="true"></i>
            </a>
        <?php endif; ?>
    </div>
    <?php if ($rows): ?>
        <dl class="lsd-analytics-popular-terms-list">
            <?php $this->include_html_file('menus/analytics/term-rows.php', ['parameters' => compact('rows', 'maximum')]); ?>
        </dl>
        <?php if ($all_terms && $terms_report['pages'] > 1): ?>
            <nav class="lsd-analytics-report-pagination" aria-label="<?php esc_attr_e('Popular search term pages', 'listdom'); ?>">
                <?php if ($terms_report['page'] > 1): ?>
                    <a class="lsd-analytics-report-page-link" href="<?php echo esc_url(add_query_arg(['analytics_terms' => 'all', 'analytics_terms_page' => $terms_report['page'] - 1], $report_url) . '#lsd-analytics-popular-terms-title'); ?>"><?php esc_html_e('Previous', 'listdom'); ?></a>
                <?php endif; ?>
                <span class="lsd-analytics-report-page-current" aria-current="page"><?php echo esc_html(number_format_i18n($terms_report['page'])); ?></span>
                <?php if ($terms_report['page'] < $terms_report['pages']): ?>
                    <a class="lsd-analytics-report-page-link" href="<?php echo esc_url(add_query_arg(['analytics_terms' => 'all', 'analytics_terms_page' => $terms_report['page'] + 1], $report_url) . '#lsd-analytics-popular-terms-title'); ?>"><?php esc_html_e('Next', 'listdom'); ?></a>
                <?php endif; ?>
            </nav>
        <?php endif; ?>
    <?php else: ?>
        <div class="lsd-analytics-report-empty lsd-analytics-popular-terms-empty" role="status">
            <span class="lsd-analytics-report-empty-icon" aria-hidden="true">
                <i class="lsd-analytics-report-empty-image webilia-icon wbli-search-magnifier"></i>
            </span>
            <h3 class="lsd-admin-title-small lsd-analytics-report-empty-title"><?php echo $listing_filter_active ? esc_html__('Search terms unavailable for listing filters', 'listdom') : esc_html__('No search terms collected yet', 'listdom'); ?></h3>
            <p class="screen-reader-text"><?php echo $listing_filter_active ? esc_html__('Clear the location, category, and title filters to see search terms.', 'listdom') : esc_html__('Terms appear after visitors submit text searches while Search Terms tracking is enabled in Stats settings.', 'listdom'); ?></p>
        </div>
    <?php endif; ?>
</section>
