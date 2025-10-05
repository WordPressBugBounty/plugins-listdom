<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Plugin_Notice $this */
/** @var bool $home */
/** @var string $notice */
?>
<?php if (!LSD_Base::is_current_theme('listdomer') && !LSD_Base::is_theme_installed('listdomer') && $this->can_display_notice($notice)): ?>
    <div class="lsd-ask-review-wrapper <?php echo $home ? 'lsd-my-0' : 'notice notice-info'; ?> lsd-flex lsd-flex-row lsd-gap-5 lsd-flex-content-start lsd-flex-items-start">
        <img src="<?php echo esc_url($this->lsd_asset_url('img/listdomer.svg')); ?>" alt="">
        <div class="lsd-flex lsd-flex-col lsd-gap-3 lsd-flex-items-start">
            <h3 class="lsd-m-0 lsd-admin-title"><?php echo esc_html__('Enjoy the Listdom more with Listdomer theme', 'listdom'); ?></h3>
            <p class="lsd-m-0"><?php echo esc_html__("Enhance your website's styling with the Listdomer theme! In addition to Listdom's impressive features, the Listdomer theme gives you greater control over your site's design and customization. Install and activate it to customize your directory site more.", 'listdom'); ?></p>
            <div class="lsd-ask-review-buttons lsd-flex lsd-flex-row lsd-gap-3 lsd-mt-3 lsd-flex-items-center">
                <a class="lsd-primary-button" href="<?php echo esc_url(admin_url('update.php?action=install-theme&theme=listdomer&_wpnonce='.wp_create_nonce('install-theme_listdomer'))); ?>" target="_blank">
                    <span><?php echo esc_html__('YES, Install Listdomer Theme', 'listdom'); ?></span>
                    <i class="listdom-icon lsdi-right-arrow"></i>
                </a>
                <a class="lsd-text-button" href="<?php echo esc_url(add_query_arg('lsd-listdomer', 'later')); ?>"><?php echo esc_html__('Maybe, Later', 'listdom'); ?></a>
                <a class="lsd-text-button" href="<?php echo esc_url(add_query_arg('lsd-listdomer', 'done')); ?>"><?php echo esc_html__('No, Thanks', 'listdom'); ?></a>
            </div>
        </div>
    </div>
<?php endif;
