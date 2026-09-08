<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Menus_Addons $this */

$grouped_addons = $this->get_grouped_addons();
$connect_notice = LSD_Webilia_Connect::notice();
$connect_notice_type = in_array($connect_notice['type'] ?? '', ['error', 'info', 'success', 'warning'], true) ? $connect_notice['type'] : 'info';

$type_descriptions = $this->get_type_descriptions();
$type_labels = $this->get_download_labels();
$connect_enabled = LSD_Webilia_Connect::enabled();
$connect_connected = $connect_enabled && LSD_Webilia_Connect::isConnected();
$install_nonce = wp_create_nonce('lsd_install_addon_via_connect');
$connect_url = admin_url('admin.php?page=listdom-connect&return_to=' . rawurlencode(admin_url('admin.php?page=listdom-connect&tab=addons')));
?>
<div class="wrap lsd-wrap lsd-addons-wrap">
    <?php LSD_Menus::header(esc_html__('Addons, Toolkits, & Apps', 'listdom'), null, LSD_Menus::webiliaTabs('addons')); ?>

    <div class="lsd-admin-main-wrapper">
        <?php echo lsd_ads('addons-top'); ?>
        <?php if (!empty($connect_notice['message'])): ?>
            <div class="lsd-alert lsd-<?php echo esc_attr($connect_notice_type); ?> lsd-my-0"><?php echo esc_html($connect_notice['message']); ?></div>
        <?php endif; ?>
        <?php $this->include_html_file('menus/connect/status.php', ['parameters' => ['connect_url' => $connect_url]]); ?>

        <?php if (!$grouped_addons): ?>
            <p><?php echo LSD_Main::alert(sprintf(
                /* translators: %s: Link to the Listdom website. */
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
                                    <div class="lsd-addon-actions lsd-flex-wrap">
                                        <p class="lsd-addon-description lsd-m-0"><?php echo LSD_Kses::element($description); ?></p>
                                        <?php if ($addon->status === 'active'): ?>
                                            <a class="lsd-secondary-button" href="<?php echo esc_url(LSD_Base::addUtmParameters($addon->documentation)); ?>" target="_blank">
                                                <?php esc_html_e('Documentation', 'listdom'); ?>
                                            </a>
                                        <?php else: ?>
                                            <?php $installable = !empty($addon->basename) && LSD_Webilia_Connect::integration((string) $addon->basename) !== ''; ?>
                                            <?php if ($connect_enabled && $installable): ?>
                                                <button type="button" class="lsd-primary-button lsd-connect-addon-install" data-basename="<?php echo esc_attr($addon->basename); ?>" data-nonce="<?php echo esc_attr($install_nonce); ?>" data-connected="<?php echo $connect_connected ? '1' : '0'; ?>" data-connect-url="<?php echo esc_url($connect_url); ?>">
                                                    <?php esc_html_e('Auto Install', 'listdom'); ?>
                                                    <i class="webilia-icon wbli-link-square" aria-hidden="true"></i>
                                                </button>
                                                <a class="lsd-secondary-button" href="<?php echo esc_url($addon->url); ?>" target="_blank">
                                                    <?php echo esc_html($type_labels[$type] ?? esc_html__('Get This Addon', 'listdom')); ?>
                                                </a>
                                            <?php else: ?>
                                                <a class="lsd-primary-button" href="<?php echo esc_url($addon->url); ?>" target="_blank">
                                                    <?php echo esc_html($type_labels[$type] ?? esc_html__('Get This Addon', 'listdom')); ?>
                                                </a>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>

            <p class="lsd-mt-5"><?php echo sprintf(
                /* translators: %s: Support email address link. */
                esc_html__('Not sure which addon is suitable for your need? You can always send us an email at %s', 'listdom'),
                '<a href="mailto:hello@webilia.com"><strong>hello@webilia.com</strong></a>'
            ); ?></p>

        <?php endif; ?>

        <?php echo lsd_ads('addons-bottom'); ?>
    </div>
</div>
<script>
function lsdBindConnectAddonInstallButtons()
{
    jQuery('.lsd-connect-addon-install').off('click').on('click', function()
    {
        const $button = jQuery(this);
        if ($button.prop('disabled')) return;

        if ($button.data('connected') !== 1 && $button.data('connected') !== '1')
        {
            window.location.href = $button.data('connect-url');
            return;
        }

        const $existingAlert = $button.siblings('.lsd-connect-addon-install-alert');
        if ($existingAlert.length) $existingAlert.removeClass('lsd-alert lsd-error lsd-success').empty();

        const getAlert = function()
        {
            let $alert = $button.siblings('.lsd-connect-addon-install-alert');
            if (!$alert.length)
            {
                $alert = jQuery('<div>', {class: 'lsd-w-full lsd-connect-addon-install-alert'}).appendTo($button.parent());
            }
            return $alert;
        };

        const loading = new ListdomButtonLoader($button);
        loading.start("<?php echo esc_js(esc_html__('Installing', 'listdom')); ?>");

        jQuery.post(ajaxurl, {
            action: 'lsd_install_addon_via_connect',
            basename: $button.data('basename'),
            _ajax_nonce: $button.data('nonce')
        }, function(response)
        {
            const showAlert = function(type, message, ctaUrl, ctaLabel)
            {
                const $alert = getAlert();
                $alert.addClass('lsd-alert lsd-' + type).html(message);
                if (ctaUrl)
                {
                    jQuery('<a>', {
                        class: 'lsd-connect-addon-upgrade-cta',
                        href: ctaUrl,
                        target: '_blank',
                        rel: 'noopener noreferrer',
                        text: ' ' + (ctaLabel || '<?php echo esc_js(__('View plans and upgrade', 'listdom')); ?>')
                    }).append(jQuery('<i>', {class: 'webilia-icon wbli-right-arrow', 'aria-hidden': 'true'})).appendTo($alert);
                }
            };

            if (response.success && response.reload)
            {
                showAlert('success', response.message || '<?php echo esc_js(__('This product was installed and activated with Webilia Connect.', 'listdom')); ?>');
                window.setTimeout(function() { window.location.reload(); }, 1500);
                return;
            }

            showAlert('error', response.message || '<?php echo esc_js(__('Unable to install this product. Please try again.', 'listdom')); ?>', response.cta_url, response.cta_label);
            loading.stop();
        }, 'json').fail(function()
        {
            getAlert().addClass('lsd-alert lsd-error').text('<?php echo esc_js(__('Unable to install this product. Please try again.', 'listdom')); ?>');
            loading.stop();
        });
    });
}

lsdBindConnectAddonInstallButtons();
</script>
