<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Plugin_Notice $this */
/** @var bool $home */
/** @var string $notice */

$listdomer_theme = wp_get_theme('listdomer');
$listdomer_installed = $listdomer_theme->exists();

$current_theme = wp_get_theme();
?>
<?php if ($current_theme->get_template() !== 'listdomer' && !$listdomer_installed && $this->can_display_notice($notice)): ?>
    <div class="lsd-ask-review-wrapper <?php echo $home ? 'lsd-my-0' : 'notice notice-info'; ?> lsd-flex lsd-flex-row lsd-gap-5 lsd-flex-content-start lsd-flex-items-start">
        <img src="<?php echo esc_url_raw($this->lsd_asset_url('img/logo.svg')); ?>" alt="">
        <div class="lsd-flex lsd-flex-col lsd-gap-3 lsd-flex-items-start">
            <h3 class="lsd-m-0 lsd-bold"><?php echo esc_html__('Enjoy the Listdom more with Listdomer theme', 'listdom'); ?></h3>
            <p class="lsd-m-0"><?php echo esc_html__("Enhance your website's styling with the Listdomer theme! In addition to Listdom's impressive features, the Listdomer theme provides you with greater control over your site's design and customization. Thinking about upgrading your website's look? Try the Listdomer theme today!", 'listdom'); ?></p>
            <div class="lsd-ask-review-buttons lsd-flex lsd-flex-row lsd-gap-4 lsd-mt-3 lsd-flex-items-center">
                <a class="button button-primary" href="<?php echo esc_url(admin_url('update.php?action=install-theme&theme=listdomer&_wpnonce='.wp_create_nonce('install-theme_listdomer'))); ?>" target="_blank">
                    <span><?php echo esc_html__('YES, Install Listdomer Theme', 'listdom'); ?></span>
                    <img src="<?php echo esc_url_raw($this->lsd_asset_url('img/arrow-right.svg')); ?>" alt="">
                </a>
                <a href="<?php echo esc_url(add_query_arg('lsd-listdomer', 'later')); ?>"><?php echo esc_html__('Maybe, Later', 'listdom'); ?></a>
                <a href="<?php echo esc_url(add_query_arg('lsd-listdomer', 'done')); ?>"><?php echo esc_html__('No, Thanks', 'listdom'); ?></a>
            </div>
        </div>
    </div>
<?php endif;
