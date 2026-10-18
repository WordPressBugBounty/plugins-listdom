<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Menus_Analytics $this */
/** @var array $preview */
/** @var array $preview_metrics */

// Preview Action Rows
$preview_actions = [
    ['icon' => 'preview-phone', 'label' => __('Phone Clicks', 'listdom')],
    ['icon' => 'preview-link', 'label' => __('Website Clicks', 'listdom')],
    ['icon' => 'preview-directions', 'label' => __('Direction Requests', 'listdom')],
    ['icon' => 'preview-share', 'label' => __('Share Clicks', 'listdom')],
];
?>
<section class="lsd-analytics-preview" aria-labelledby="lsd-analytics-preview-cta-title">
    <div class="lsd-analytics-preview-sample lsd-admin-box-white" aria-hidden="true" inert>
        <h2 class="lsd-admin-subtitle lsd-analytics-preview-heading"><?php esc_html_e('Advanced Analytics Preview', 'listdom'); ?></h2>

        <div class="lsd-analytics-preview-metrics">
            <?php foreach ($preview_metrics as $metric): ?>
                <div class="lsd-analytics-preview-metric lsd-analytics-preview-metric-<?php echo esc_attr($metric['icon']); ?>">
                    <span class="lsd-analytics-preview-metric-icon">
                        <i class="lsd-analytics-preview-metric-image <?php echo esc_attr($this->iconClass('preview-' . $metric['icon'])); ?>"></i>
                    </span>
                    <span class="lsd-analytics-preview-metric-copy">
                        <span class="lsd-analytics-preview-metric-label"><?php echo esc_html($metric['label']); ?></span>
                        <span class="lsd-analytics-preview-metric-placeholder"></span>
                    </span>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="lsd-analytics-preview-reports">
            <div class="lsd-admin-box-white lsd-analytics-preview-report lsd-analytics-preview-report-views">
                <h3 class="lsd-admin-subtitle lsd-analytics-preview-report-title"><?php esc_html_e('Views & Engagement', 'listdom'); ?></h3>
                <img class="lsd-analytics-preview-chart-image" src="<?php echo esc_url($this->lsd_asset_url('img/analytics/preview-waves.svg')); ?>" alt="">
                <div class="lsd-analytics-preview-chart-legend">
                    <span class="lsd-analytics-preview-legend-dot"></span>
                    <span class="lsd-analytics-preview-legend-line"></span>
                    <span class="lsd-analytics-preview-legend-dot lsd-analytics-preview-legend-dot-light"></span>
                    <span class="lsd-analytics-preview-legend-line lsd-analytics-preview-legend-line-short"></span>
                </div>
            </div>

            <div class="lsd-admin-box-white lsd-analytics-preview-report lsd-analytics-preview-report-listings">
                <h3 class="lsd-admin-subtitle lsd-analytics-preview-report-title"><?php esc_html_e('Top Performing Listings', 'listdom'); ?></h3>
                <div class="lsd-analytics-preview-listings">
                    <?php for ($row = 1; $row <= 4; $row++): ?>
                        <div class="lsd-analytics-preview-listing">
                            <span class="lsd-analytics-preview-listing-image lsd-analytics-preview-listing-image-<?php echo esc_attr($row); ?>"></span>
                            <span class="lsd-analytics-preview-listing-text"></span>
                            <i class="lsd-analytics-preview-listing-eye webilia-icon wbli-eye"></i>
                            <span class="lsd-analytics-preview-listing-count"></span>
                        </div>
                    <?php endfor; ?>
                </div>
            </div>

            <div class="lsd-admin-box-white lsd-analytics-preview-report lsd-analytics-preview-report-searches">
                <h3 class="lsd-admin-subtitle lsd-analytics-preview-report-title"><?php esc_html_e('Search Trends', 'listdom'); ?></h3>
                <div class="lsd-analytics-preview-bars">
                    <?php for ($bar = 1; $bar <= 14; $bar++): ?>
                        <span class="lsd-analytics-preview-bar lsd-analytics-preview-bar-<?php echo esc_attr($bar); ?>"></span>
                    <?php endfor; ?>
                </div>
            </div>

            <div class="lsd-admin-box-white lsd-analytics-preview-report lsd-analytics-preview-report-terms">
                <h3 class="lsd-admin-subtitle lsd-analytics-preview-report-title"><?php esc_html_e('Popular Search Terms', 'listdom'); ?></h3>
                <?php for ($term = 1; $term <= 5; $term++): ?>
                    <div class="lsd-analytics-preview-term">
                        <span class="lsd-analytics-preview-term-label"></span>
                        <span class="lsd-analytics-preview-term-track">
                            <span class="lsd-analytics-preview-term-fill lsd-analytics-preview-term-fill-<?php echo esc_attr($term); ?>"></span>
                        </span>
                    </div>
                <?php endfor; ?>
            </div>

            <div class="lsd-admin-box-white lsd-analytics-preview-report lsd-analytics-preview-report-conversions">
                <h3 class="lsd-admin-subtitle lsd-analytics-preview-report-title"><?php esc_html_e('Conversion Insights', 'listdom'); ?></h3>
                <div class="lsd-analytics-preview-conversion-body">
                    <span class="lsd-analytics-preview-donut"></span>
                    <span class="lsd-analytics-preview-conversion-legend">
                        <?php for ($slice = 1; $slice <= 4; $slice++): ?>
                            <span class="lsd-analytics-preview-conversion-line lsd-analytics-preview-conversion-line-<?php echo esc_attr($slice); ?>">
                                <span class="lsd-analytics-preview-conversion-dot"></span>
                                <span class="lsd-analytics-preview-conversion-text"></span>
                            </span>
                        <?php endfor; ?>
                    </span>
                </div>
            </div>

            <div class="lsd-admin-box-white lsd-analytics-preview-report lsd-analytics-preview-report-actions">
                <h3 class="lsd-admin-subtitle lsd-analytics-preview-report-title"><?php esc_html_e('Action Click Breakdown', 'listdom'); ?></h3>
                <div class="lsd-analytics-preview-breakdown">
                    <?php foreach ($preview_actions as $action): ?>
                        <div class="lsd-analytics-preview-breakdown-row">
                            <span class="lsd-analytics-preview-breakdown-icon">
                                <i class="lsd-analytics-preview-breakdown-image <?php echo esc_attr($this->iconClass($action['icon'])); ?>"></i>
                            </span>
                            <span class="lsd-analytics-preview-breakdown-label"><?php echo esc_html($action['label']); ?></span>
                            <span class="lsd-analytics-preview-breakdown-track">
                                <span class="lsd-analytics-preview-breakdown-fill"></span>
                            </span>
                            <span class="lsd-analytics-preview-breakdown-value"></span>
                            <span class="lsd-analytics-preview-breakdown-share"></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="lsd-analytics-preview-cta-wrap">
        <aside class="lsd-admin-box-white lsd-analytics-preview-cta" aria-labelledby="lsd-analytics-preview-cta-title">
            <span class="lsd-analytics-preview-lock" aria-hidden="true">
                <i class="lsd-analytics-preview-lock-image fas fa-lock"></i>
            </span>
            <h2 class="lsd-admin-title lsd-analytics-preview-cta-title" id="lsd-analytics-preview-cta-title"><?php echo esc_html($preview['title']); ?></h2>
            <p class="lsd-admin-description lsd-analytics-preview-cta-copy"><?php echo esc_html($preview['body']); ?></p>
            <div class="lsd-analytics-preview-cta-actions">
                <?php if (in_array(($preview['mode'] ?? ''), ['install', 'activate'], true)): ?>
                    <?php $activate = $preview['mode'] === 'activate'; ?>
                    <button type="button" class="lsd-primary-button lsd-analytics-preview-primary lsd-analytics-install"
                        data-action="<?php echo esc_attr($activate ? 'lsd_analytics_activate_stats' : 'lsd_install_addon_via_connect'); ?>"
                        data-basename="listdom-stats/listdom-stats.php"
                        data-nonce="<?php echo esc_attr(wp_create_nonce($activate ? 'lsd_analytics_activate_stats' : 'lsd_install_addon_via_connect')); ?>"
                        data-loading="<?php echo $activate ? esc_attr__('Activating', 'listdom') : esc_attr__('Installing', 'listdom'); ?>"
                        data-failed="<?php echo $activate ? esc_attr__('Stats Addon could not be activated. Please try again.', 'listdom') : esc_attr__('Stats Addon could not be installed or activated. Please try again.', 'listdom'); ?>">
                        <span class="lsd-analytics-preview-action-label"><?php echo esc_html($preview['action']); ?></span>
                        <i class="lsd-analytics-preview-action-arrow webilia-icon wbli-right-arrow" aria-hidden="true"></i>
                    </button>
                <?php else: ?>
                    <a class="lsd-primary-button lsd-analytics-preview-primary" href="<?php echo esc_url($preview['url']); ?>">
                        <i class="webilia-icon wbli-link-square lsd-analytics-preview-action-icon" aria-hidden="true"></i>
                        <span class="lsd-analytics-preview-action-label"><?php echo esc_html($preview['action']); ?></span>
                        <i class="lsd-analytics-preview-action-arrow webilia-icon wbli-right-arrow" aria-hidden="true"></i>
                    </a>
                <?php endif; ?>
                <a class="lsd-secondary-button lsd-analytics-preview-secondary" href="<?php echo esc_url($preview['learn_url']); ?>"><?php esc_html_e('Learn More', 'listdom'); ?></a>
            </div>
        </aside>
    </div>

    <div class="lsd-analytics-preview-note">
        <span class="lsd-analytics-preview-note-icon" aria-hidden="true">
            <i class="lsd-analytics-preview-note-image fas fa-info-circle"></i>
        </span>
        <div class="lsd-analytics-preview-note-copy">
            <p class="lsd-admin-description lsd-analytics-preview-note-main"><?php echo esc_html($preview['note_main'] ?? __('Advanced contacts, searches, click tracking, and conversion insights require Stats Addon and supported tracking.', 'listdom')); ?></p>
            <p class="lsd-admin-description lsd-analytics-preview-note-extra"><?php esc_html_e('Some metrics also require optional Listdom add-ons to be installed and active.', 'listdom'); ?></p>
        </div>
        <a class="lsd-analytics-preview-note-link" href="<?php echo esc_url($preview['learn_url']); ?>">
            <span class="lsd-analytics-preview-note-link-label"><?php esc_html_e('Learn More', 'listdom'); ?></span>
            <i class="lsd-analytics-preview-note-arrow webilia-icon wbli-right-arrow" aria-hidden="true"></i>
        </a>
    </div>
</section>
