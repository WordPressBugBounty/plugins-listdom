<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Menus_Analytics $this */
/** @var array $listings */
/** @var int $listing_offset */
/** @var bool $tracked_report */
/** @var bool $unique_views_available */
/** @var bool $detail_cells_hidden */
foreach ($listings as $index => $listing):
    $thumbnail = get_the_post_thumbnail_url($listing['id'], 'thumbnail');
    ?>
    <tr class="lsd-analytics-report-row">
        <td class="lsd-analytics-report-cell lsd-analytics-report-rank"><?php echo esc_html(number_format_i18n($listing_offset + $index + 1)); ?></td>
        <td class="lsd-analytics-report-cell lsd-analytics-report-listing">
            <a class="lsd-analytics-report-listing-link" href="<?php echo esc_url(get_permalink($listing['id'])); ?>">
                <span class="lsd-analytics-report-thumbnail<?php echo $thumbnail ? '' : ' lsd-analytics-report-thumbnail-empty'; ?>" aria-hidden="true">
                    <?php if ($thumbnail): ?>
                        <img class="lsd-analytics-report-thumbnail-image" src="<?php echo esc_url($thumbnail); ?>" alt="">
                    <?php else: ?>
                        <i class="lsd-analytics-report-thumbnail-image far fa-building"></i>
                    <?php endif; ?>
                </span>
                <span class="lsd-analytics-report-listing-title"><?php echo esc_html($listing['title'] !== '' ? $listing['title'] : __('(no title)', 'listdom')); ?></span>
            </a>
        </td>
        <td class="lsd-analytics-report-cell lsd-analytics-report-number">
            <?php echo $listing['views'] === null ? esc_html__('Unavailable', 'listdom') : esc_html(number_format_i18n($listing['views'])); ?>
        </td>
        <?php if ($unique_views_available): ?>
            <td class="lsd-analytics-report-cell lsd-analytics-report-number" data-lsd-detail-column<?php if ($detail_cells_hidden): ?> hidden<?php endif; ?>><?php echo ($listing['unique_views'] ?? null) === null ? esc_html__('Unavailable', 'listdom') : esc_html(number_format_i18n($listing['unique_views'])); ?></td>
        <?php endif; ?>
        <?php if ($tracked_report): ?>
            <td class="lsd-analytics-report-cell lsd-analytics-report-number" data-lsd-detail-column<?php if ($detail_cells_hidden): ?> hidden<?php endif; ?>><?php echo ($listing['card_impressions'] ?? null) === null ? esc_html__('Unavailable', 'listdom') : esc_html(number_format_i18n($listing['card_impressions'])); ?></td>
            <td class="lsd-analytics-report-cell lsd-analytics-report-number" data-lsd-detail-column<?php if ($detail_cells_hidden): ?> hidden<?php endif; ?>><?php echo ($listing['card_clicks'] ?? null) === null ? esc_html__('Unavailable', 'listdom') : esc_html(number_format_i18n($listing['card_clicks'])); ?></td>
            <td class="lsd-analytics-report-cell lsd-analytics-report-number" data-lsd-detail-column<?php if ($detail_cells_hidden): ?> hidden<?php endif; ?>><?php echo ($listing['card_ctr'] ?? null) === null ? esc_html__('Unavailable', 'listdom') : esc_html(number_format_i18n($listing['card_ctr'], 1) . '%'); ?></td>
        <?php endif; ?>
        <td class="lsd-analytics-report-cell lsd-analytics-report-number"><?php echo $listing['contacts'] === null ? esc_html__('Unavailable', 'listdom') : esc_html(number_format_i18n($listing['contacts'])); ?></td>
        <?php foreach (['phone', 'website', 'directions'] as $action): ?>
            <td class="lsd-analytics-report-cell lsd-analytics-report-number<?php echo $tracked_report ? '' : ' lsd-analytics-report-untracked'; ?>"><?php echo $tracked_report ? (($listing[$action] ?? null) === null ? esc_html__('Unavailable', 'listdom') : esc_html(number_format_i18n($listing[$action]))) : '—'; ?></td>
        <?php endforeach; ?>
    </tr>
<?php endforeach; ?>
