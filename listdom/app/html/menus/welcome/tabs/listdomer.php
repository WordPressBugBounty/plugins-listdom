<?php
// no direct access
defined('ABSPATH') || die();
?>
<div class="lsd-welcome-step-content lsd-util-hide" id="step-4">
    <img src="<?php echo esc_url_raw($this->lsd_asset_url('img/install-listdomer-banner.png')); ?>" alt="">
    <h2 class="text-xl mb-4"><?php esc_html_e('Enjoy the Listdom more with Listdomer theme', 'listdom'); ?></h2>
    <div class="lsd-listdomer-installation-wrap">
        <form id="lsd_listdomer_installation_form">
            <p class="description"><?php esc_html_e("Enhance your website's styling with the Listdomer theme! In addition to Listdom's impressive features, the Listdomer theme provides you with greater control over your site's design and customization. Thinking about upgrading your website's look? Try the Listdomer theme today!", 'listdom'); ?></p>

            <?php LSD_Form::nonce('lsd_listdomer_installation_form'); ?>

            <div class="installation-theme-message"></div>
            <div class="lsd-skip-wizard lsd-mt-4">
                <button class="lsd-skip-step"><?php echo esc_html__('Skip', 'listdom'); ?></button>
                <div class="lsd-flex lsd-gap-2">
                    <button type="button" class="lsd-prev-step-link button button-hero button-primary">
                        <img src="<?php echo esc_url_raw($this->lsd_asset_url('img/arrow-right.svg')); ?>" alt="">
                        <?php echo esc_html__('Prev Step', 'listdom'); ?>
                    </button>
                    <button type="submit" class="lsd-step-link button button-hero button-primary" id="lsd_listdomer_installation_save_button">
                        <?php echo esc_html__('YES, Install Listdomer Theme', 'listdom'); ?>
                        <img src="<?php echo esc_url_raw($this->lsd_asset_url('img/arrow-right.svg')); ?>" alt="">
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
<script>
jQuery('#lsd_listdomer_installation_form').on('submit', function (event)
{
    event.preventDefault();

    const $welcomeWizard = jQuery(".lsd-welcome-wizard");
    const $alert = jQuery(".installation-theme-message");
    const $submitButton = jQuery("#lsd_listdomer_installation_save_button");

    $welcomeWizard.addClass('lsd-loading-wrapper');

    jQuery.ajax(
    {
        type: "POST",
        url: ajaxurl,
        dataType: 'json',
        data:
        {
            action: 'install_listdomer_theme',
            data: jQuery(this).serialize()
        },
        success: function (response)
        {
            $welcomeWizard.removeClass('lsd-loading-wrapper');

            if (response.success)
            {
                $alert.html(listdom_alertify(response.message, response.status === 'activated' ? 'lsd-success' : 'lsd-warning'));

                if (response.status === 'activated')
                {
                    $submitButton.text('<?php echo esc_html__('Next', 'listdom'); ?>');
                    $submitButton.on('click', function(e)
                    {
                        e.preventDefault();
                        handleStepNavigation(5)
                    });
                }
                else if (response.status === 'installed')
                {
                    lsd_activate_theme_response();
                }
            }
            else $alert.html(listdom_alertify(response.message, 'lsd-error'));
        },
        error: function (jqXHR)
        {
            // Theme is installed but requires activation
            if(jqXHR.status === 200 && jqXHR.responseText.includes('activatelink'))
            {
                $alert.html(listdom_alertify("<?php echo esc_js(esc_html__('Listdomer theme is installed but not activated.', 'listdom')); ?>", 'lsd-warning'));
                lsd_activate_theme_response();
            }

            $welcomeWizard.removeClass('lsd-loading-wrapper');
        }
    });

    function lsd_activate_theme_response()
    {
        $submitButton.text('<?php echo esc_html__('Next', 'listdom'); ?>');

        jQuery(".installation-theme-message div").append(`
            <button id="activate-theme" class="button button-primary">
                <?php echo esc_html__('Activate Listdomer', 'listdom'); ?>
            </button>
        `);

        $submitButton.on('click', function(e) {
            e.preventDefault();
            handleStepNavigation(5)
        });

        jQuery("#activate-theme").on("click", function ()
        {
            jQuery.ajax(
            {
                type: "POST",
                url: ajaxurl,
                dataType: 'json',
                data: {
                    action: 'activate_listdomer_theme'
                },
                success: function (response)
                {
                    if (response.success) $alert.html(listdom_alertify(response.message, 'lsd-success'));
                    else $alert.html(listdom_alertify(response.message, 'lsd-error'));
                }
            });
        });
    }
});
</script>
