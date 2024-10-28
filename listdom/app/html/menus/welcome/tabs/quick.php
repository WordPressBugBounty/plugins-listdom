<?php
// no direct access
defined('ABSPATH') || die();

$settings = LSD_Options::settings();
?>
<div class="lsd-welcome-step-content lsd-util-hide" id="step-2">
    <h2 class="text-xl mb-4"><?php echo esc_html__('Quick Setting', 'listdom'); ?></h2>
    <form id="lsd_quick_settings_form">
        <div class="lsd-quick-settings">
            <div class="lsd-flex lsd-flex-row lsd-py-2">
                <h3><?php esc_html_e('Date & Time:', 'listdom'); ?></h3>
            </div>
            <div class="lsd-flex lsd-gap-4">
                <div class="lsd-form-row">
                    <div class="lsd-col-6"><?php echo LSD_Form::label([
                        'title' => esc_html__('Date Picker Format', 'listdom'),
                        'for' => 'lsd_settings_datepicker_format',
                    ]); ?></div>
                    <div class="lsd-col-6">
                        <?php echo LSD_Form::select([
                            'id' => 'lsd_settings_datepicker_format',
                            'name' => 'lsd[datepicker_format]',
                            'options' => [
                                'yyyy-mm-dd' => esc_html(current_time('Y-m-d') . ' ' . esc_html__('(Y-m-d)', 'listdom')),
                                'dd-mm-yyyy' => esc_html(current_time('d-m-Y') . ' ' . esc_html__('(d-m-Y)', 'listdom')),
                                'yyyy/mm/dd' => esc_html(current_time('Y/m/d') . ' ' . esc_html__('(Y/m/d)', 'listdom')),
                                'dd/mm/yyyy' => esc_html(current_time('d/m/Y') . ' ' . esc_html__('(d/m/Y)', 'listdom')),
                                'yyyy.mm.dd' => esc_html(current_time('Y.m.d') . ' ' . esc_html__('(Y.m.d)', 'listdom')),
                                'dd.mm.yyyy' => esc_html(current_time('d.m.Y') . ' ' . esc_html__('(d.m.Y)', 'listdom')),
                            ],
                            'value' => $settings['datepicker_format'] ?? 'yyyy-mm-dd'
                        ]); ?>
                    </div>
                </div>
                <div class="lsd-form-row">
                    <div class="lsd-col-6"><?php echo LSD_Form::label([
                        'title' => esc_html__('Time Picker Format', 'listdom'),
                        'for' => 'lsd_settings_timepicker_format',
                    ]); ?></div>
                    <div class="lsd-col-6">
                        <?php echo LSD_Form::select([
                            'id' => 'lsd_settings_timepicker_format',
                            'name' => 'lsd[timepicker_format]',
                            'options' => [
                                24 => esc_html__('24 Hours', 'listdom'),
                                12 => esc_html__('12 Hours (AM / PM)', 'listdom'),
                            ],
                            'value' => $settings['timepicker_format'] ?? 24
                        ]); ?>
                    </div>
                </div>
            </div>

            <div class="lsd-flex lsd-flex-row lsd-py-2">
                <h3><?php esc_html_e('Currency:', 'listdom'); ?></h3>
            </div>
            <div class="lsd-flex lsd-gap-5">
                <div class="lsd-form-row">
                    <div class="lsd-col-6"><?php echo LSD_Form::label([
                        'title' => esc_html__('Currency Position', 'listdom'),
                        'for' => 'lsd_settings_currency_position',
                    ]); ?></div>
                    <div class="lsd-col-6">
                        <?php echo LSD_Form::select([
                            'id' => 'lsd_settings_currency_position',
                            'name' => 'lsd[currency_position]',
                            'value' => $settings['currency_position'] ?? 'before',
                            'options' => [
                                'before' => esc_html__('Before ($100)', 'listdom'),
                                'before_ws' => esc_html__('Before With Space ($ 100)', 'listdom'),
                                'after' => esc_html__('After (100$)', 'listdom'),
                                'after_ws' => esc_html__('After With Space (100 $)', 'listdom'),
                            ],
                        ]); ?>
                    </div>
                </div>
                <div class="lsd-form-row">
                    <div class="lsd-col-7"><?php echo LSD_Form::label([
                        'title' => esc_html__('Default Currency', 'listdom'),
                        'for' => 'lsd_settings_default_currency',
                    ]); ?></div>
                    <div class="lsd-col-5">
                        <?php echo LSD_Form::currency([
                            'id' => 'lsd_settings_default_currency',
                            'name' => 'lsd[default_currency]',
                            'value' => $settings['default_currency'] ?? ''
                        ]); ?>
                    </div>
                </div>
            </div>

            <div class="lsd-flex lsd-flex-row lsd-py-2">
                <h3><?php esc_html_e('Google recaptcha:', 'listdom'); ?></h3>
            </div>
            <div class="lsd-settings-form-group">
                <div class="lsd-form-row">
                    <div class="lsd-col-2"><?php echo LSD_Form::label([
                        'title' => esc_html__('Status', 'listdom'),
                        'for' => 'lsd_settings_grecaptcha_status',
                    ]); ?></div>
                    <div class="lsd-col-10">
                        <?php echo LSD_Form::switcher([
                            'id' => 'lsd_settings_grecaptcha_status',
                            'value' => $settings['grecaptcha_status'] ?? false,
                            'name' => 'lsd[grecaptcha_status]',
                            'toggle' => '#lsd_settings_grecaptcha_options'
                        ]); ?>
                    </div>
                </div>
                <div id="lsd_settings_grecaptcha_options" <?php echo isset($settings['grecaptcha_status']) && $settings['grecaptcha_status'] ? '' : 'style="display: none;"'; ?>>
                    <div class="lsd-form-row">
                        <div class="lsd-col-2"><?php echo LSD_Form::label([
                            'title' => esc_html__('Site Key', 'listdom'),
                            'for' => 'lsd_settings_grecaptcha_sitekey',
                        ]); ?></div>
                        <div class="lsd-col-10">
                            <?php echo LSD_Form::text([
                                'id' => 'lsd_settings_grecaptcha_sitekey',
                                'value' => $settings['grecaptcha_sitekey'] ?? '',
                                'name' => 'lsd[grecaptcha_sitekey]'
                            ]); ?>
                        </div>
                    </div>
                    <div class="lsd-form-row">
                        <div class="lsd-col-2"><?php echo LSD_Form::label([
                            'title' => esc_html__('Secret Key', 'listdom'),
                            'for' => 'lsd_settings_grecaptcha_secretkey',
                        ]); ?></div>
                        <div class="lsd-col-10">
                            <?php echo LSD_Form::text([
                                'id' => 'lsd_settings_grecaptcha_secretkey',
                                'value' => $settings['grecaptcha_secretkey'] ?? '',
                                'name' => 'lsd[grecaptcha_secretkey]'
                            ]); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php LSD_Form::nonce('lsd_settings_form'); ?>
    </form>
    <div class="lsd-skip-wizard">
        <button class="lsd-skip-step"><?php echo esc_html__('Skip This step', 'listdom'); ?></button>
        <div class="lsd-flex lsd-gap-2">
            <button class="lsd-prev-step-link button button-hero button-primary">
                <img src="<?php echo esc_url_raw($this->lsd_asset_url('img/arrow-right.svg')); ?>" alt="">
                <?php echo esc_html__('Prev Step', 'listdom'); ?>
            </button>
            <button class="lsd-step-link button button-hero button-primary" id="lsd_settings_quick_save_button">
                <?php echo esc_html__('Continue', 'listdom'); ?>
                <img src="<?php echo esc_url_raw($this->lsd_asset_url('img/arrow-right.svg')); ?>" alt="">
            </button>
        </div>
    </div>
</div>

<script>
jQuery('#lsd_settings_quick_save_button').on('click', function (e)
{
    e.preventDefault();
    const $welcomeWizard = jQuery(".lsd-welcome-wizard");

    // Loading Styles
    $welcomeWizard.addClass('lsd-loading-wrapper');
    jQuery('head').append('<style>.lsd-loading-wrapper:after { content: "\\f7d9" !important; }</style>');

    const settings = jQuery("#lsd_quick_settings_form").serialize();
    jQuery.ajax(
    {
        type: "POST",
        url: ajaxurl,
        data: "action=lsd_save_settings&" + settings,
        success: function ()
        {
            // Loading Styles
            $welcomeWizard.removeClass('lsd-loading-wrapper');
            handleStepNavigation(3);
        },
        error: function ()
        {
            // Loading Styles
            $welcomeWizard.removeClass('lsd-loading-wrapper');
        }
    });
});
</script>
