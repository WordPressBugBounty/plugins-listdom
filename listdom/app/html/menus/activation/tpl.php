<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Activation $this */
/** @var array $products */
/** @var array $connect_notice */
$connect_url = admin_url('admin.php?page=listdom-connect&return_to=' . rawurlencode(admin_url('admin.php?page=listdom-connect&tab=licenses')));
$connect_notice_type = in_array($connect_notice['type'] ?? '', ['error', 'info', 'success', 'warning'], true) ? $connect_notice['type'] : 'info';
?>
<div class="wrap lsd-wrap lsd-licenses-wrap">
    <?php LSD_Menus::header(esc_html__('Licenses', 'listdom'), null, LSD_Menus::webiliaTabs('licenses')); ?>

    <div class="lsd-admin-main-wrapper">
        <?php echo lsd_ads('licenses-top'); ?>
        <?php if (!empty($connect_notice['message'])): ?>
            <div class="lsd-alert lsd-<?php echo esc_attr($connect_notice_type); ?> lsd-my-0"><?php echo esc_html($connect_notice['message']); ?></div>
        <?php endif; ?>
        <?php $this->include_html_file('menus/connect/status.php', ['parameters' => ['connect_url' => $connect_url]]); ?>
        <div class="lsd-activation-wrap lsd-flex lsd-flex-col lsd-gap-4">
            <section class="lsd-admin-wrapper lsd-m-0 lsd-license-section" aria-labelledby="lsd-all-licenses-title">
                <div class="lsd-admin-section-heading">
                    <h3 id="lsd-all-licenses-title" class="lsd-admin-title"><?php echo esc_html__('Activate with License Keys', 'listdom'); ?></h3>
                    <p class="lsd-admin-description"><?php esc_html_e('Activate and manage each product separately. These license-key activations remain independent from Webilia Connect.', 'listdom'); ?></p>
                </div>
                <div class="lsd-accordion-section">
                    <p class="lsd-admin-description"><?php esc_html_e('Prefer individual license keys?', 'listdom'); ?></p>
                    <button type="button" class="lsd-accordion-title lsd-secondary-button lsd-license-section-toggle" aria-expanded="false" aria-controls="lsd-all-licenses-content" aria-label="<?php esc_attr_e('Open license key details', 'listdom'); ?>">
                        <span class="lsd-accordion-icons" aria-hidden="true"><i class="webilia-icon wbli-add-plus lsd-license-toggle-closed"></i><i class="webilia-icon wbli-up-arrow-s lsd-license-toggle-open"></i></span>
                        <span class="screen-reader-text"><?php esc_html_e('Open or close all license details', 'listdom'); ?></span>
                    </button>
                </div>

                <div id="lsd-all-licenses-content" class="lsd-accordion-panel lsd-license-section-content" hidden>
                    <div class="lsd-licenses lsd-my-0">
                        <?php if (!count($products)): ?>
                            <h3 class="lsd-mt-0"><?php esc_html_e('No Premium Add-ons Activated Yet', 'listdom'); ?></h3>
                            <div class="lsd-alert lsd-info lsd-my-0"><?php esc_html_e("This is where you'll activate your paid Listdom add-ons using their license keys. Activating an add-on unlocks its premium features and extended functionality. Once you install a paid add-on, you can register its license here.", 'listdom'); ?></div>
                        <?php else: ?>
                            <?php foreach ($products as $key => $product) $this->include_html_file('menus/activation/addon.php', ['parameters' => ['key' => $key, 'product' => $product]]); ?>
                        <?php endif; ?>
                    </div>
                </div>
            </section>
        </div>
        <?php echo lsd_ads('licenses-bottom'); ?>
    </div>
</div>
<script>
function lsdBindLicenseSections()
{
    jQuery('.lsd-license-section-toggle').off('click').on('click', function(event)
    {
        event.stopImmediatePropagation();

        const $toggle = jQuery(this);
        const $content = jQuery('#' + $toggle.attr('aria-controls'));
        const willExpand = $content.prop('hidden');

        $toggle.attr('aria-expanded', String(willExpand));
        $toggle.attr('aria-label', willExpand ? '<?php echo esc_js(__('Close license key details', 'listdom')); ?>' : '<?php echo esc_js(__('Open license key details', 'listdom')); ?>');
        $toggle.toggleClass('lsd-accordion-active', willExpand);

        $content.stop(true, true).off('transitionend.lsdLicense');
        if (willExpand)
        {
            $content.prop('hidden', false).css({height: 0, opacity: 0});
            $content.addClass('lsd-accordion-open');
            window.requestAnimationFrame(function()
            {
                $content.css({height: $content[0].scrollHeight + 'px', opacity: 1});
            });
            $content.on('transitionend.lsdLicense', function(event)
            {
                if (event.originalEvent.propertyName === 'height') $content.css('height', 'auto').off('transitionend.lsdLicense');
            });
        }
        else
        {
            $content.css({height: $content[0].scrollHeight + 'px', opacity: 1});
            window.requestAnimationFrame(function()
            {
                $content.removeClass('lsd-accordion-open').css({height: 0, opacity: 0});
            });
            $content.on('transitionend.lsdLicense', function(event)
            {
                if (event.originalEvent.propertyName === 'height') $content.prop('hidden', true).off('transitionend.lsdLicense');
            });
        }
    });
}

function lsdBindLicenseForms()
{
    // Activation Form
    jQuery('.lsd-activation-form').off('submit').on('submit', function(event)
    {
        event.preventDefault();

        // Form
        const $form = jQuery(this);

        // Product Key
        const key = $form.data('key');

        // DOM Elements
        const $alert = jQuery(`#${key}_activation_alert`);
        const $button = jQuery(`#${key}_activation_button`);
        const $licenseBadge = jQuery('#toplevel_page_listdom .wp-submenu a[href="admin.php?page=listdom-connect"] .update-count');
        const $mainBadge = jQuery('#toplevel_page_listdom > a .update-count');
        const $headerBadge = jQuery('.lsd-header-submenu a[href*="tab=licenses"] .update-count');

        // Remove Existing Alert
        $alert.removeClass('lsd-error lsd-success lsd-alert').html('');

        // Add loading Class to the button
        $button.addClass('loading').html('<i class="lsd-icon fa fa-spinner fa-pulse fa-fw"></i>');

        const activation = $form.serialize();
        jQuery.ajax(
        {
            type: "POST",
            url: ajaxurl,
            data: "action=lsd_activation&" + activation,
            dataType: "json",
            success: function(response)
            {
                if (response.success)
                {
                    const $panel = jQuery(`#lsd-license-card-${key}`);
                    $panel.replaceWith(response.content);

                    setTimeout(() => lsdBindLicenseForms(), 2000);

                    const reduceBadge = function($badge)
                    {
                        const count = parseInt($badge.html(), 10);
                        if (isNaN(count)) return;

                        if (count > 1) $badge.html(count - 1);
                        else $badge.closest('.update-plugins').remove();
                    };

                    if (response.reduce_license_badges)
                    {
                        reduceBadge($licenseBadge);
                        reduceBadge($mainBadge);
                        reduceBadge($headerBadge);
                    }
                }
                else
                {
                    $alert.removeClass('lsd-error lsd-success lsd-alert').addClass('lsd-alert lsd-error').html(response.message);
                }

                // Remove loading Class from the button
                $button.removeClass('loading').html("<?php echo esc_js(esc_attr__('Activate', 'listdom')); ?>");
            },
            error: function()
            {
                // Remove loading Class from the button
                $button.removeClass('loading').html("<?php echo esc_js(esc_attr__('Activate', 'listdom')); ?>");
            }
        });
    });

    // Deactivation Form
    jQuery('.lsd-deactivation-form').off('submit').on('submit', function(event)
    {
        event.preventDefault();

        // Form
        const $form = jQuery(this);

        // Product Key
        const key = $form.data('key');

        // DOM Elements
        const $alert = jQuery(`#${key}_deactivation_alert`);
        const $button = jQuery(`#${key}_deactivation_button`);
        const $confirm = jQuery(`#${key}_deactivation_confirm`);

        // Not confirmed
        if($confirm.val().toLowerCase() !== 'deactivate') return;

        // Remove Existing Alert
        $alert.removeClass('lsd-error lsd-success lsd-alert').html('');

        // Add loading Class to the button
        $button.addClass('loading').html('<i class="lsd-icon fa fa-spinner fa-pulse fa-fw"></i>');

        const deactivation = $form.serialize();
        jQuery.ajax(
        {
            type: "POST",
            url: ajaxurl,
            data: "action=lsd_deactivation&" + deactivation,
            dataType: "json",
            success: function(response)
            {
                if(response.success)
                {
                    const $panel = jQuery(`#lsd-license-card-${key}`);
                    $panel.replaceWith(response.content);

                    setTimeout(() => lsdBindLicenseForms(), 2000);
                }
                else
                {
                    $alert.removeClass('lsd-error lsd-success lsd-alert').addClass('lsd-alert lsd-error').html(response.message);
                }

                // Remove loading Class from the button
                $button.removeClass('loading').html("<?php echo esc_js(esc_attr__('Deactivate', 'listdom')); ?>");
            },
            error: function()
            {
                // Remove loading Class from the button
                $button.removeClass('loading').html("<?php echo esc_js(esc_attr__('Deactivate', 'listdom')); ?>");
            }
        });
    });

}

lsdBindLicenseSections();
lsdBindLicenseForms();
</script>
