<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Menus_Settings $this */
[, $default] = $this->get_addons_default('toolkit');
?>
<div class="lsd-settings-wrap">
    <form id="lsd_toolkits_form" class="lsd-addons-form">

        <?php do_action('lsd_toolkit_form', $default); ?>

        <div class="lsd-spacer-30"></div>
        <div class="lsd-form-row lsd-settings-submit-wrapper">
            <div class="lsd-col-12 lsd-flex lsd-gap-3 lsd-flex-content-end">
                <?php LSD_Form::nonce('lsd_addons_form'); ?>
                <button type="submit" id="lsd_toolkits_save_button" class="lsd-primary-button lsd-addons-save-button">
                    <?php esc_html_e('Save The Changes', 'listdom'); ?>
                    <i class='lsdi lsdi-checkmark-circle'></i>
                </button>
            </div>
        </div>
    </form>
</div>
<?php $this->include_html_file('menus/settings/tabs/partials/addons-form-script.php'); ?>
