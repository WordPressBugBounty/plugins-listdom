<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Plugin_Notice $this */
/** @var bool $home */
/** @var string $notice */
?>
<?php if ($this->can_display_notice($notice)): ?>
    <div class="lsd-ask-review-wrapper <?php echo $home ? 'lsd-my-0' : 'notice notice-info'; ?> lsd-flex lsd-flex-row lsd-gap-5 lsd-flex-content-start lsd-flex-items-start">
        <img src="<?php echo esc_url($this->lsd_asset_url('img/rating.svg')); ?>" alt="">
        <div class="lsd-flex lsd-flex-col lsd-gap-3 lsd-flex-items-start">
            <h3 class="lsd-m-0 lsd-admin-title"><?php echo esc_html__('Looks like you\'ve been using Listdom for a while.' ,'listdom'); ?></h3>
            <p class="lsd-m-0"><?php echo esc_html__('Could you give a 5-star rating on the WordPress repository? It helps the Listdom team know their efforts are useful to you.', 'listdom'); ?></p>
            <div class="lsd-ask-review-buttons lsd-flex lsd-flex-row lsd-gap-3 lsd-mt-3 lsd-flex-items-center">
                <a class="lsd-primary-button" href="https://api.webilia.com/go/wp-review" target="_blank">
                    <span><?php echo esc_html__('Sure, you deserve it', 'listdom'); ?></span>
                    <i class="listdom-icon lsdi-right-arrow"></i>
                </a>
                <a class="lsd-text-button" href="<?php echo esc_url(add_query_arg('lsd-review', 'later')); ?>"><?php echo esc_html__('Maybe later', 'listdom'); ?></a>
                <a class="lsd-text-button" href="<?php echo esc_url(add_query_arg('lsd-review', 'done')); ?>"><?php echo esc_html__('Already done :)', 'listdom'); ?></a>
            </div>
        </div>
    </div>
<?php endif;
