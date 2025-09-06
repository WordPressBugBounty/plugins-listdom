<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Menus_Addons $this */

$grouped_addons = $this->get_grouped_addons();

$type_descriptions = $this->get_type_descriptions();
$type_labels = $this->get_download_labels();
?>
<div class="wrap lsd-wrap">
    <?php LSD_Menus::header(esc_html__('Addons, Toolkits, & Apps', 'listdom')); ?>

    <div class="lsd-admin-main-wrapper">
        <?php echo lsd_ads('addons-top'); ?>

        <?php if (!$grouped_addons): ?>
            <p><?php echo LSD_Main::alert(sprintf(
                esc_html__('It seems there is a problem to get list of addons from Webilia server. Please try again later or check our website at %s', 'listdom'),
                '<a href="https://listdom.net" target="_blank"><strong>listdom.net</strong></a>'
            ), 'warning'); ?></p>
        <?php else: ?>

            <?php foreach ($grouped_addons as $type => $addons): ?>
                <div class="lsd-admin-wrapper lsd-addons-wrapper">
                    <div class="lsd-addons-title">
                        <h3 class="lsd-mt-0 lsd-mb-3 lsd-admin-title">
                            <?php echo esc_html($this->get_type_label($type)); ?>
                        </h3>
                        <p><?php echo esc_html($type_descriptions[$type] ?? ''); ?></p>
                    </div>
                    <div class="lsd-addons">
                        <?php foreach ($addons as $addon): ?>
                            <div class="lsd-addons-card">
                                <?php
                                    $recommended  = !empty($addon->recommended);
                                    $classes      = $recommended ? 'lsd-addons-recommended' : '';
                                    $description  = strlen($addon->promotional) > 192
                                        ? substr($addon->promotional, 0, 192) . ' ...'
                                        : $addon->promotional;
                                    $badge     = $this->get_badge($addon->status);
                                ?>
                                <div class="lsd-addon-wrapper <?php echo trim($classes); ?>">
                                    <div class="lsd-addon-title-section">
                                        <div class="lsd-addon-icon">
                                            <?php if (!empty($addon->image)): ?>
                                                <img src="<?php echo esc_url($addon->image); ?>" alt="<?php echo esc_attr($addon->name); ?>">
                                            <?php endif; ?>
                                        </div>
                                        <a href="<?php echo esc_url($addon->url ?? LSD_Base::addUtmParameters($addon->documentation ?? '')); ?>" target="_blank">
                                            <h3 class="lsd-admin-title lsd-my-0">
                                                <?php echo esc_html($addon->name); ?>
                                            </h3>
                                        </a>
                                        <span class="lsd-badge <?php echo esc_attr($badge['class']); ?>">
                                            <i class="listdom-icon <?php echo esc_attr($badge['icon']); ?>"></i>
                                            <?php echo esc_html($badge['text']); ?>
                                        </span>
                                    </div>
                                    <div class="lsd-addon-actions">
                                        <p class="lsd-addon-description lsd-m-0"><?php echo LSD_Kses::element($description); ?></p>
                                        <?php if ($addon->status === 'installed'): ?>
                                            <a class="lsd-secondary-button" href="<?php echo esc_url(LSD_Base::addUtmParameters($addon->documentation)); ?>" target="_blank">
                                                <?php esc_html_e('Documentation', 'listdom'); ?>
                                            </a>
                                        <?php else: ?>
                                            <a class="lsd-primary-button" href="<?php echo esc_url($addon->url); ?>" target="_blank">
                                                <?php echo esc_html($type_labels[$type] ?? esc_html__('Get This Addon', 'listdom')); ?>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>

            <p class="lsd-mt-5"><?php echo sprintf(
                esc_html__('Not sure which addon is suitable for your need? You can always send us an email at %s', 'listdom'),
                '<a href="mailto:hello@webilia.com"><strong>hello@webilia.com</strong></a>'
            ); ?></p>

        <?php endif; ?>

        <?php echo lsd_ads('addons-bottom'); ?>
    </div>
</div>
