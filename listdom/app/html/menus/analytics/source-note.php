<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Menus_Analytics $this */
/** @var bool $filters_supported */
?>
<div class="lsd-analytics-source-note lsd-admin-box-white">
    <span class="lsd-analytics-source-note-icon" aria-hidden="true">
        <i class="lsd-analytics-source-note-image fas fa-info-circle"></i>
    </span>
    <div class="lsd-analytics-source-note-copy">
        <p class="lsd-admin-description lsd-analytics-source-note-main"><?php echo $filters_supported
            ? esc_html__('Analytics reports use locally collected Stats data in the selected date range. Search data cannot be attributed to an individual listing.', 'listdom')
            : esc_html__('Analytics reports use locally collected Stats data. Lifetime totals include earlier stored listing activity.', 'listdom'); ?></p>
        <p class="lsd-admin-description-tiny lsd-analytics-source-note-extra"><?php esc_html_e('Some metrics require optional Listdom add-ons or additional tracking.', 'listdom'); ?></p>
    </div>
    <a class="lsd-analytics-source-note-link lsd-text-button" href="https://api.webilia.com/go/stats-menu-cta">
        <?php esc_html_e('Learn More', 'listdom'); ?>
        <i class="lsd-analytics-report-view-all-arrow webilia-icon wbli-right-arrow" aria-hidden="true"></i>
    </a>
</div>
