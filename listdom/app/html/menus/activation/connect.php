<?php
// no direct access
defined('ABSPATH') || die();

/** @var string $connect_return_url */
/** @var array $connect_notice */
/** @var string $connect_panel_classes */

$connect_connected = LSD_Webilia_Connect::isConnected();
$connect_panel_classes = $connect_panel_classes ?? 'lsd-admin-wrapper lsd-m-0';
$notice_type = in_array($connect_notice['type'] ?? '', ['success', 'info', 'warning', 'error'], true) ? $connect_notice['type'] : 'info';
?>
<section class="<?php echo esc_attr($connect_panel_classes); ?> lsd-wbl-connection-wrapper" aria-labelledby="lsd-connect-title">
    <div class="lsd-wbl-connection-layout">
        <div class="lsd-wbl-connection-content">
            <div class="lsd-flex lsd-flex-align-items-center lsd-gap-3 lsd-w-full">
                <h3 id="lsd-connect-title" class="lsd-my-0 lsd-admin-title"><?php esc_html_e('Connect Your Site', 'listdom'); ?></h3>
                <div class="lsd-badge <?php echo $connect_connected ? 'lsd-success' : ''; ?>"><i class="webilia-icon <?php echo $connect_connected ? 'wbli-checkmark-circle' : 'wbli-alert'; ?>"></i><?php echo $connect_connected ? esc_html__('Connected', 'listdom') : esc_html__('Not connected', 'listdom'); ?></div>
            </div>
            <?php if (!empty($connect_notice['message']) && !($connect_connected && $notice_type === 'success')): ?><div class="lsd-alert lsd-<?php echo esc_attr($notice_type); ?> lsd-w-full"><?php echo esc_html($connect_notice['message']); ?></div><?php endif; ?>
            <?php if ($connect_connected): ?>
                <div class="lsd-alert lsd-success lsd-w-full"><?php esc_html_e('This website is connected to your Webilia account. Installed Listdom products are checked automatically, so eligible products can work and receive updates without entering a license key for each add-on.', 'listdom'); ?></div>
                <details class="lsd-connect-disclosure">
                    <summary class="lsd-connect-disclosure-summary">
                        <span class="lsd-connect-disclosure-icon" aria-hidden="true"><i class="webilia-icon wbli-down-arrow-s lsd-connect-disclosure-closed"></i><i class="webilia-icon wbli-up-arrow-s lsd-connect-disclosure-open"></i></span>
                        <span><?php esc_html_e('Disconnect this website', 'listdom'); ?></span>
                    </summary>
                    <div class="lsd-connect-disclosure-content">
                        <p class="lsd-admin-description lsd-m-0"><?php esc_html_e('Disconnecting removes Webilia Connect access only. Existing license-key activations are not changed.', 'listdom'); ?></p>
                        <form class="lsd-webilia-connect-disconnect-form lsd-flex lsd-flex-wrap lsd-flex-align-items-center lsd-gap-3 lsd-alert-no-my">
                            <div class="lsd-flex-1"><?php echo LSD_Form::text(['name' => 'confirmation', 'class' => 'lsd-admin-input', 'placeholder' => esc_attr__('Type disconnect to confirm ...', 'listdom')]); ?></div>
                            <?php echo LSD_Form::hidden(['name' => '_wpnonce', 'value' => wp_create_nonce('lsd_webilia_connect_disconnect')]); ?>
                            <?php echo LSD_Form::submit(['label' => esc_html__('Disconnect Website', 'listdom'), 'class' => 'lsd-secondary-button']); ?>
                            <div class="lsd-w-full lsd-activation-form-alert"></div>
                        </form>
                    </div>
                </details>
            <?php else: ?>
                <p class="lsd-admin-description"><?php esc_html_e('Connect this website once to let Listdom automatically use the eligible products in your Webilia account. Your existing license keys remain available as a fallback.', 'listdom'); ?></p>
                <a class="lsd-primary-button" href="<?php echo esc_url(LSD_Webilia_Connect::connectUrl($connect_return_url ?? '')); ?>"><?php esc_html_e('Connect to Webilia', 'listdom'); ?><i class="webilia-icon wbli-link-square"></i></a>
            <?php endif; ?>
        </div>
        <div class="lsd-wbl-connection-art" aria-hidden="true">
            <img class="lsd-wbl-connection-artwork" src="<?php echo esc_url($this->lsd_asset_url('img/webilia-connect-illustration.png')); ?>" alt="">
        </div>
    </div>
</section>
<script>
function lsdBindWebiliaConnectDisconnectForms()
{
    jQuery('.lsd-webilia-connect-disconnect-form').off('submit').on('submit', function(event)
    {
        event.preventDefault();
        const $form = jQuery(this), $alert = $form.find('.lsd-activation-form-alert'), $button = $form.find('button[type="submit"], input[type="submit"]');
        const loading = new ListdomButtonLoader($button);
        loading.start("<?php echo esc_js(esc_html__('Disconnecting', 'listdom')); ?>");
        $alert.removeClass('lsd-error lsd-success lsd-alert').empty();
        jQuery.post(ajaxurl, $form.serialize() + '&action=lsd_webilia_connect_disconnect', function(response)
        {
            if (response.success) { window.location.reload(); return; }
            loading.stop();
            $alert.addClass('lsd-alert lsd-error').text(response.message || '<?php echo esc_js(__('Unable to disconnect this website.', 'listdom')); ?>');
        }, 'json').fail(function() { loading.stop(); $alert.addClass('lsd-alert lsd-error').text('<?php echo esc_js(__('Unable to disconnect this website.', 'listdom')); ?>'); });
    });
}

lsdBindWebiliaConnectDisconnectForms();
</script>
