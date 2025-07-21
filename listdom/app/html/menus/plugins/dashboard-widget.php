<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Admin $this */
/** @var array $items */
/** @var string|null $error */
?>
<div class="lsd-dashboard-widget lsd-wrap">
    <?php echo lsd_ads('dashboard-widget'); ?>

    <h3 class="lsd-heading"><?php esc_html_e('News & Updates', 'listdom'); ?></h3>
    <?php if (!empty($error)): ?>
        <p><?php echo esc_html($error); ?></p>
    <?php else: ?>
        <ul>
            <?php foreach ($items as $item): ?>
                <?php
                $date  = $item->get_date('U') ? '<div class="rss-date">' . esc_html($item->get_date(LSD_Main::date_format())) . '</div>' : '';
                $desc  = wp_trim_words(strip_tags($item->get_description()), 20, '...');
                ?>
                <li>
                    <a href="<?php echo esc_url($item->get_link()); ?>" target="_blank"><?php echo esc_html($item->get_title()); ?></a><?php echo $date; ?>
                    <p><?php echo esc_html($desc); ?></p>
                </li>
            <?php endforeach; ?>
        </ul>
        <div class="lsd-news-links">
            <a href="<?php echo esc_url_raw(LSD_Base::getListdomDocsURL()); ?>" target="_blank">
                <?php esc_html_e('Documentation', 'listdom'); ?>
                <span aria-hidden="true" class="dashicons dashicons-external"></span>
            </a>
            <a href="<?php echo esc_url_raw(LSD_Base::getSupportURL()); ?>" target="_blank">
                <?php esc_html_e('Support', 'listdom'); ?>
                <span aria-hidden="true" class="dashicons dashicons-external"></span>

            </a>
            <?php if (!LSD_Base::isPro()): ?>
                <a href="<?php echo esc_url_raw(LSD_Base::getWebiliaShopURL()); ?>" target="_blank">
                    <?php esc_html_e('Go Pro', 'listdom'); ?>
                    <span aria-hidden="true" class="dashicons dashicons-external"></span>
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
