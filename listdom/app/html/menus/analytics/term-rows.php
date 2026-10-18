<?php
// no direct access
defined('ABSPATH') || die();

/** @var array $rows */
/** @var int $maximum */
?>
<?php foreach ($rows as $row): ?>
    <?php $width = round((int) $row['searches'] * 84 / $maximum); ?>
    <?php $intensity = round(0.4 + 0.6 * (int) $row['searches'] / $maximum, 2); ?>
    <div class="lsd-analytics-popular-terms-row">
        <dt class="lsd-admin-description-tiny lsd-analytics-popular-terms-label"><?php echo esc_html($row['term']); ?></dt>
        <dd class="lsd-analytics-popular-terms-track" aria-hidden="true">
            <span class="lsd-analytics-popular-terms-fill" style="width: <?php echo esc_attr($width); ?>%; opacity: <?php echo esc_attr($intensity); ?>"></span>
        </dd>
        <dd class="lsd-admin-description-tiny lsd-analytics-popular-terms-value"><?php echo esc_html(number_format_i18n($row['searches'])); ?></dd>
    </div>
<?php endforeach; ?>
