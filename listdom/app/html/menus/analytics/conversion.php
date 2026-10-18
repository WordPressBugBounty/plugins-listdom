<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Menus_Analytics $this */
/** @var array|null $conversion */

// Conversion Metrics
$actions = is_array($conversion) ? ($conversion['actions'] ?? []) : [];
$action_total = array_sum(array_map(static function ($action)
{
    return is_int($action['value']) ? $action['value'] : 0;
}, $actions));

$views = is_array($conversion) ? ($conversion['views'] ?? null) : null;
$chart_note = '';
if (is_array($conversion))
{
    if (($views === null || $views === 0) && $action_total === 0)
        $chart_note = __('No tracked view or action counts are available in this range. The ring is a placeholder.', 'listdom');
    elseif ($conversion['rate'] === null)
        $chart_note = __('An action rate needs at least one listing view and a tracked action total in this range.', 'listdom');
    elseif (!$conversion['comparable'])
        $chart_note = __('The action rate uses available views and actions. Tracking covered only part of the range or differed between actions, so the rate may be inaccurate.', 'listdom');
    else
        $chart_note = __('The ring shows tracked action totals. The action rate compares their sum with listing views; repeated actions can make it exceed 100 percent.', 'listdom');
}
?>
<section class="lsd-admin-box-white lsd-analytics-report lsd-analytics-conversion-report<?php echo !is_array($conversion) ? ' lsd-analytics-conversion-report-empty' : ''; ?>" aria-labelledby="lsd-analytics-conversion-title">
    <div class="lsd-analytics-report-header">
        <h2 class="lsd-admin-title lsd-analytics-report-title" id="lsd-analytics-conversion-title">
            <?php esc_html_e('Conversion Insights', 'listdom'); ?>
            <?php if (is_array($conversion)): ?>
                <button class="lsd-analytics-report-help lsd-tooltip" type="button" aria-label="<?php echo esc_attr($chart_note); ?>" data-lsd-tooltip="<?php echo esc_attr($chart_note); ?>">
                    <i class="webilia-icon wbli-question" aria-hidden="true"></i>
                </button>
            <?php endif; ?>
        </h2>
    </div>

    <?php if (is_array($conversion)): ?>
        <div class="lsd-analytics-conversion-body">
            <div class="lsd-analytics-conversion-chart-wrap" role="group" aria-label="<?php esc_attr_e('Conversion chart', 'listdom'); ?>" aria-describedby="lsd-analytics-conversion-chart-note">
                <canvas class="lsd-analytics-conversion-chart" id="lsd-analytics-conversion-chart" role="img" aria-label="<?php esc_attr_e('Tracked engagement action totals', 'listdom'); ?>" data-values="<?php echo esc_attr(wp_json_encode(['actions' => $actions])); ?>"></canvas>
                <div class="lsd-analytics-conversion-center" aria-hidden="true">
                    <span class="lsd-admin-title-small lsd-analytics-conversion-rate"><?php echo esc_html($conversion['rate'] === null ? '—' : number_format_i18n($conversion['rate'], 1) . '%'); ?></span>
                    <span class="lsd-admin-description-tiny lsd-analytics-conversion-rate-label"><?php esc_html_e('Action Rate', 'listdom'); ?></span>
                </div>
            </div>
            <span class="screen-reader-text" id="lsd-analytics-conversion-chart-note"><?php echo esc_html($chart_note); ?></span>
            <div class="lsd-analytics-conversion-legend">
                <?php foreach ($actions as $action): ?>
                    <div class="lsd-analytics-conversion-legend-row">
                        <span class="lsd-analytics-conversion-dot" style="background: <?php echo esc_attr($action['color']); ?>" aria-hidden="true"></span>
                        <span class="lsd-admin-description-tiny lsd-analytics-conversion-legend-label"><?php echo esc_html($action['label']); ?></span>
                        <span class="lsd-admin-description-tiny lsd-analytics-conversion-legend-value"><?php echo $action['value'] === null ? esc_html__('Unavailable', 'listdom') : esc_html(number_format_i18n($action['value'])); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <table class="screen-reader-text lsd-analytics-conversion-table">
            <caption class="lsd-analytics-conversion-caption"><?php esc_html_e('Tracked engagement actions and action rate in the shown chart range', 'listdom'); ?></caption>
            <tbody class="lsd-analytics-conversion-tbody">
                <tr class="lsd-analytics-conversion-row">
                    <th class="lsd-analytics-conversion-cell" scope="row"><?php esc_html_e('Listing Views', 'listdom'); ?></th>
                    <td class="lsd-analytics-conversion-cell"><?php echo $views === null ? esc_html__('Unavailable', 'listdom') : esc_html(number_format_i18n($views)); ?></td>
                </tr>
                <?php foreach ($actions as $action): ?>
                    <tr class="lsd-analytics-conversion-row">
                        <th class="lsd-analytics-conversion-cell" scope="row"><?php echo esc_html($action['label']); ?></th>
                        <td class="lsd-analytics-conversion-cell"><?php echo $action['value'] === null ? esc_html__('Unavailable', 'listdom') : esc_html(number_format_i18n($action['value'])); ?></td>
                    </tr>
                <?php endforeach; ?>
                <tr class="lsd-analytics-conversion-row">
                    <th class="lsd-analytics-conversion-cell" scope="row"><?php esc_html_e('Action Rate', 'listdom'); ?></th>
                    <td class="lsd-analytics-conversion-cell"><?php echo $conversion['rate'] === null ? esc_html__('Unavailable', 'listdom') : esc_html(number_format_i18n($conversion['rate'], 1) . '%'); ?></td>
                </tr>
            </tbody>
        </table>
        <p class="screen-reader-text lsd-analytics-conversion-definition"><?php esc_html_e('When available, the ring compares tracked action event volumes. Action rate is the total of the four shown actions divided by listing views in this range. Repeated actions can make the rate exceed 100 percent.', 'listdom'); ?></p>
        <?php if ($conversion['partial']): ?>
            <p class="screen-reader-text"><?php esc_html_e('Tracking coverage varies across the shown metrics.', 'listdom'); ?></p>
        <?php endif; ?>
    <?php else: ?>
        <div class="lsd-analytics-report-empty lsd-analytics-conversion-empty" role="status">
            <span class="lsd-analytics-report-empty-icon" aria-hidden="true">
                <i class="lsd-analytics-report-empty-image fas fa-chart-pie"></i>
            </span>
            <h3 class="lsd-admin-title-small lsd-analytics-report-empty-title"><?php echo is_array($conversion) ? esc_html__('Not enough data yet', 'listdom') : esc_html__('Conversion report unavailable', 'listdom'); ?></h3>
            <p class="lsd-admin-description-tiny lsd-analytics-report-empty-copy"><?php echo is_array($conversion) ? esc_html__('Interaction metrics will appear after visitors view your listings and use tracked actions.', 'listdom') : esc_html__('Tracked listing views and actions could not be read for this Stats version.', 'listdom'); ?></p>
            <?php if (is_array($conversion)): ?>
                <span class="lsd-analytics-report-empty-guidance">
                    <i class="lsd-analytics-report-guidance-icon fas fa-info-circle" aria-hidden="true"></i>
                    <?php esc_html_e('Enable listing view and action tracking to calculate the rate.', 'listdom'); ?>
                </span>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</section>
