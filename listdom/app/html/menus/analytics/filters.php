<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Menus_Analytics $this */
/** @var array $activity_filters */
/** @var array $activity_terms */
/** @var bool $activity_export_available */
/** @var string $period */
/** @var bool $all_listings */
/** @var array $status */
?>
<div class="lsd-analytics-activity-toolbar">
    <p class="screen-reader-text" id="lsd-analytics-activity-scope"><?php esc_html_e('The date range applies throughout Analytics. Location, category, and title filters apply to listing activity. Search activity cannot be attributed to a listing and is unavailable with listing filters.', 'listdom'); ?></p>
    <form class="lsd-analytics-activity-filters" id="lsd-analytics-activity-filters" method="get" action="<?php echo esc_url(admin_url('admin.php')); ?>" aria-describedby="lsd-analytics-activity-scope">
        <input class="lsd-analytics-activity-hidden" type="hidden" name="page" value="listdom-analytics">
        <input class="lsd-analytics-activity-hidden" type="hidden" name="analytics_period" value="<?php echo esc_attr($period); ?>">
        <input class="lsd-analytics-activity-hidden" type="hidden" name="_wpnonce" value="<?php echo esc_attr(wp_create_nonce('lsd_analytics_export')); ?>">
        <?php if ($all_listings): ?>
            <input class="lsd-analytics-activity-hidden" type="hidden" name="analytics_view" value="all">
        <?php endif; ?>

        <label class="screen-reader-text lsd-analytics-activity-label" for="lsd-analytics-activity-range"><?php esc_html_e('Analytics date range', 'listdom'); ?></label>
        <span class="lsd-analytics-activity-control">
            <i class="lsd-analytics-activity-icon far fa-calendar-alt" aria-hidden="true"></i>
            <select class="lsd-admin-input lsd-analytics-activity-select" id="lsd-analytics-activity-range" name="analytics_range">
                <option value="7"<?php selected($activity_filters['days'], 7); ?>><?php esc_html_e('Last 7 Days', 'listdom'); ?></option>
                <option value="30"<?php selected($activity_filters['days'], 30); ?>><?php esc_html_e('Last 30 Days', 'listdom'); ?></option>
                <option value="90"<?php selected($activity_filters['days'], 90); ?>><?php esc_html_e('Last 90 Days', 'listdom'); ?></option>
            </select>
        </span>

        <label class="screen-reader-text lsd-analytics-activity-label" for="lsd-analytics-activity-location"><?php esc_html_e('Tracked listing report location', 'listdom'); ?></label>
        <span class="lsd-analytics-activity-control">
            <i class="lsd-analytics-activity-icon webilia-icon wbli-map-marker" aria-hidden="true"></i>
            <select class="lsd-admin-input lsd-analytics-activity-select" id="lsd-analytics-activity-location" name="analytics_location">
                <option value="0"><?php esc_html_e('All Locations', 'listdom'); ?></option>
                <?php foreach ($activity_terms['location'] as $term): ?>
                    <option value="<?php echo esc_attr($term->term_id); ?>"<?php selected($activity_filters['location'], (int) $term->term_id); ?>><?php echo esc_html($term->name); ?></option>
                <?php endforeach; ?>
            </select>
        </span>

        <label class="screen-reader-text lsd-analytics-activity-label" for="lsd-analytics-activity-category"><?php esc_html_e('Tracked listing report category', 'listdom'); ?></label>
        <span class="lsd-analytics-activity-control">
            <i class="lsd-analytics-activity-icon far fa-folder" aria-hidden="true"></i>
            <select class="lsd-admin-input lsd-analytics-activity-select" id="lsd-analytics-activity-category" name="analytics_category">
                <option value="0"><?php esc_html_e('All Categories', 'listdom'); ?></option>
                <?php foreach ($activity_terms['category'] as $term): ?>
                    <option value="<?php echo esc_attr($term->term_id); ?>"<?php selected($activity_filters['category'], (int) $term->term_id); ?>><?php echo esc_html($term->name); ?></option>
                <?php endforeach; ?>
            </select>
        </span>

        <label class="screen-reader-text lsd-analytics-activity-label" for="lsd-analytics-activity-search"><?php esc_html_e('Search tracked listings by title', 'listdom'); ?></label>
        <span class="lsd-analytics-activity-control lsd-analytics-activity-search-control">
            <i class="lsd-analytics-activity-icon webilia-icon wbli-search-magnifier" aria-hidden="true"></i>
            <input class="lsd-admin-input lsd-analytics-activity-search" id="lsd-analytics-activity-search" type="search" name="analytics_search" value="<?php echo esc_attr($activity_filters['search']); ?>" placeholder="<?php esc_attr_e('Search listings...', 'listdom'); ?>">
            <button class="lsd-analytics-activity-search-submit" type="submit"><?php esc_html_e('Go', 'listdom'); ?></button>
        </span>
        <button class="screen-reader-text lsd-analytics-activity-apply" type="submit"><?php esc_html_e('Apply Analytics filters', 'listdom'); ?></button>
    </form>
    <div class="lsd-analytics-activity-actions">
        <?php if ($status['code'] === 'active'): ?>
            <a class="lsd-secondary-button lsd-analytics-activity-settings" href="<?php echo esc_url($status['url']); ?>"><?php esc_html_e('Manage Stats settings', 'listdom'); ?></a>
        <?php endif; ?>
        <button class="lsd-secondary-button lsd-analytics-activity-export" type="submit" form="lsd-analytics-activity-filters" formaction="<?php echo esc_url(admin_url('admin-post.php')); ?>" name="action" value="lsd_analytics_export" aria-label="<?php esc_attr_e('Export Report', 'listdom'); ?>" title="<?php esc_attr_e('Export Report', 'listdom'); ?>"<?php if (!$activity_export_available): ?> disabled<?php endif; ?>>
            <i class="lsd-analytics-activity-export-icon webilia-icon wbli-file-download" aria-hidden="true"></i>
        </button>
    </div>
</div>
