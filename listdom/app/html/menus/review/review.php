<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Plugin_Review $this */
/** @var bool $home */
?>
<?php if ($this->can_display_review()): ?>
    <div class="lsd-ask-review-wrapper lsd-flex lsd-flex-row lsd-gap-5 lsd-flex-content-start lsd-flex-items-start
        <?php echo !$home ? 'notice notice-info' : ''; ?>">
        <img src="<?php echo esc_url_raw($this->lsd_asset_url('img/rating.svg')); ?>" alt="">
        <div class="lsd-flex lsd-flex-col lsd-gap-3 lsd-flex-items-start">
            <h3 class="lsd-m-0 lsd-bold"><?php echo esc_html__('Looks like you\'ve been using Listdom for a while.' ,'listdom'); ?></h3>
            <p class="lsd-m-0"><?php echo esc_html__('Could you give a 5-star rating on the WordPress repository? It helps the Listdom team know their efforts are useful to you.', 'listdom'); ?></p>
            <div class="lsd-ask-review-buttons lsd-flex lsd-flex-row lsd-gap-4 lsd-mt-3 lsd-flex-items-center">
                <a class="button button-primary" href="https://api.webilia.com/go/wp-review" target="_blank">
                    <span><?php echo esc_html__('Sure, you deserve it', 'listdom'); ?></span>
                    <img src="<?php echo esc_url_raw($this->lsd_asset_url('img/arrow-right.svg')); ?>" alt="">
                </a>
                <a href="<?php echo esc_url(add_query_arg('lsd-review', 'later')); ?>"><?php echo esc_html__('Maybe later.', 'listdom'); ?></a>
                <a href="<?php echo esc_url(add_query_arg('lsd-review', 'done')); ?>"><?php echo esc_html__('Already done :)', 'listdom'); ?></a>
            </div>
        </div>
    </div>
<?php endif;
