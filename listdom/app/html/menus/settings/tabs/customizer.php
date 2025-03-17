<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Menus_Settings $this */

// Customizer
$values = LSD_Options::customizer();

// Options
$options = LSD_Customizer::options();
?>
<div class="lsd-settings-wrap" id="lsd_settings_display_options">
    <form id="lsd_settings_reset" class="lsd-flex lsd-flex-row lsd-flex-items-stretch lsd-flex-content-end lsd-gap-3 lsd-mt-4">
        <input id="lsd_reset_confirm" class="lsd-min-w-300 lsd-px-3" placeholder="<?php esc_attr_e("Type 'reset' to confirm your action.", 'listdom'); ?>" title="<?php esc_attr_e('Confirm'); ?>">
        <?php LSD_Form::nonce('lsd_settings_form'); ?>
        <button type="submit" class="button button-secondary" id="lsd_settings_reset_button"><?php esc_html_e('Reset all options', 'listdom'); ?></button>
    </form>
    <form id="lsd_settings_form">

        <?php $c = 0; foreach ($options as $ck => $category): $c++; ?>
        <div class="lsd-accordion-title <?php echo $c === 1 ? 'lsd-accordion-active' : ''; ?>">
            <div class="lsd-flex lsd-flex-row lsd-py-2">
                <h3><?php echo esc_html($category['title'] ?? ''); ?></h3>
                <div class="lsd-flex lsd-flex-row lsd-gap-4">
                    <div class="lsd-tooltip lsd-tooltip-left lsd-cursor-pointer lsd-customizer-reset-category" data-confirm="0" data-category="<?php echo esc_attr($ck); ?>" data-nonce="<?php echo wp_create_nonce('lsd_settings_form'); ?>" data-lsd-tooltip="<?php esc_attr_e('Click twice to reset section', 'listdom'); ?>">
                        <i class="lsd-icon fa fa-retweet"></i>
                    </div>
                    <div class="lsd-accordion-icons">
                        <i class="lsd-icon fa fa-plus"></i>
                        <i class="lsd-icon fa fa-minus"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="lsd-accordion-panel <?php echo $c === 1 ? 'lsd-accordion-open' : ''; ?>">
            <?php if (isset($category['sections']) && is_array($category['sections']) && (count($category['sections']) > 1 || (isset($category['display_sections_force']) && $category['display_sections_force']))): ?>
                <ul class="lsd-tab-switcher lsd-sub-tabs lsd-flex lsd-gap-3 lsd-mb-4" data-for=".lsd-customizer-<?php echo esc_attr($ck); ?>-tab-switcher-content">
                    <?php $s = 0; foreach ($category['sections'] as $sk => $section): $s++; ?>
                        <li data-tab="customizer-<?php echo esc_attr($ck); ?>-<?php echo esc_attr($sk); ?>" class="<?php echo $s === 1 ? 'lsd-sub-tabs-active' : ''; ?>"><a href="#"><?php echo esc_html($section['title'] ?? ''); ?></a></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <?php if (isset($category['sections']) && is_array($category['sections'])): ?>
                <?php $inherit = 0; $s = 0; foreach ($category['sections'] as $sk => $section): $s++; ?>
                    <div class="lsd-tab-switcher-content lsd-customizer-<?php echo esc_attr($ck); ?>-tab-switcher-content <?php echo $s === 1 ? 'lsd-tab-switcher-content-active' : ''; ?>" id="lsd-tab-switcher-customizer-<?php echo esc_attr($ck); ?>-<?php echo esc_attr($sk); ?>-content">
                        <div class="lsd-flex lsd-flex-col lsd-flex-items-stretch lsd-gap-4">
                            <?php if (isset($section['description']) && trim($section['description'])): ?>
                                <p class="description lsd-my-0"><?php echo esc_html($section['description']); ?></p>
                            <?php endif; ?>

                            <?php if (isset($section['inherit']) && is_array($section['inherit'])): $inherit = $values[$ck][$sk]['inherit'] ?? (int) ($section['inherit']['enabled'] ?? 0) ?>
                                <div class="lsd-flex lsd-flex-row lsd-flex-content-start lsd-gap-4">
                                    <div><?php echo LSD_Form::switcher([
                                        'id' => 'lsd-customizer-'.$ck.'-'.$sk.'-inherit',
                                        'name' => 'lsd['.$ck.']['.$sk.'][inherit]',
                                        'toggle' => '#lsd-customizer-'.$ck.'-'.$sk.'-divisions',
                                        'value' => $inherit
                                    ]); ?></div>
                                    <label for="<?php echo esc_attr('lsd-customizer-'.$ck.'-'.$sk.'-inherit'); ?>"><?php echo esc_html($section['inherit']['text']); ?></label>
                                    <?php echo LSD_Form::hidden([
                                        'name' => 'lsd['.$ck.']['.$sk.'][inherit_from]',
                                        'value' => $section['inherit']['key'] ?? ''
                                    ]); ?>
                                </div>
                            <?php endif; ?>

                            <div id="<?php echo esc_attr('lsd-customizer-'.$ck.'-'.$sk.'-divisions'); ?>" class="<?php echo $inherit ? 'lsd-util-hide' : ''; ?>">
                                <?php if (isset($section['groups']) && is_array($section['groups']) && count($section['groups'])): ?>
                                    <div class="lsd-customizer-groups-wrapper lsd-flex lsd-flex-col lsd-flex-items-stretch lsd-gap-4">
                                        <?php $g = 0; foreach ($section['groups'] as $gk => $group): $g++; ?>
                                            <div class="lsd-customizer-fields-wrapper lsd-form-group lsd-featured-form-group lsd-px-4 lsd-my-0 lsd-flex lsd-flex-col lsd-flex-items-stretch lsd-gap-4">
                                                <div class="lsd-flex lsd-flex-row lsd-flex-items-start">
                                                    <div class="<?php echo isset($group['sub_title']) && trim($group['sub_title']) ? 'lsd-mb-4' : ''; ?>">
                                                        <?php if (isset($group['title']) && trim($group['title'])): ?>
                                                            <h3 class="lsd-my-0"><?php echo esc_html($group['title']); ?></h3>
                                                        <?php endif; ?>
                                                        <?php if (isset($group['sub_title']) && trim($group['sub_title'])): ?>
                                                            <p class="description lsd-my-0"><?php echo esc_html($group['sub_title']); ?></p>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="lsd-tooltip lsd-tooltip-left lsd-cursor-pointer lsd-customizer-reset-category" data-confirm="0" data-category="<?php echo esc_attr($ck.'.'.$sk.'.'.$gk); ?>" data-nonce="<?php echo wp_create_nonce('lsd_settings_form'); ?>" data-lsd-tooltip="<?php esc_attr_e('Click twice to reset section', 'listdom'); ?>">
                                                        <i class="lsd-icon fa fa-retweet"></i>
                                                    </div>
                                                </div>

                                                <?php if (isset($group['divisions']) && is_array($group['divisions']) && count($group['divisions'])): ?>
                                                    <?php if(count($group['divisions']) > 1): ?>
                                                    <ul class="lsd-tab-switcher lsd-sub-tabs lsd-flex lsd-gap-3 lsd-mb-4" data-for=".lsd-customizer-<?php echo esc_attr($gk); ?>-tab-switcher-content">
                                                        <?php $d = 0; foreach ($group['divisions'] as $dk => $division): if (!isset($division['title']) || trim($division['title']) === '') continue; $d++; ?>
                                                            <li data-tab="customizer-<?php echo esc_attr($gk); ?>-<?php echo esc_attr($dk); ?>" class="<?php echo $d === 1 ? 'lsd-sub-tabs-active' : ''; ?>"><a href="#"><?php echo esc_html($division['title'] ?? ''); ?></a></li>
                                                        <?php endforeach; ?>
                                                    </ul>
                                                    <?php endif; ?>

                                                    <div class="lsd-customizer-division-wrapper">
                                                        <?php $d = 0; foreach ($group['divisions'] as $dk => $division): $d++; ?>
                                                            <div class="lsd-tab-switcher-content lsd-customizer-<?php echo esc_attr($gk); ?>-tab-switcher-content <?php echo $d === 1 ? 'lsd-tab-switcher-content-active' : ''; ?>" id="lsd-tab-switcher-customizer-<?php echo esc_attr($gk); ?>-<?php echo esc_attr($dk); ?>-content">
                                                                <?php if (isset($division['fields']) && is_array($division['fields'])): ?>
                                                                    <div class="lsd-flex lsd-flex-col lsd-flex-items-stretch lsd-gap-4">
                                                                        <?php foreach ($division['fields'] as $fk => $field): ?>
                                                                            <?php
                                                                            // Parameters
                                                                            $type = $field['type'] ?? 'text';
                                                                            $id = 'lsd-customizer-'.$ck.'-'.$sk.'-'.$gk.'-'.$dk.'-'.$fk;
                                                                            $default = $field['default'] ?? '';

                                                                            // Field Args
                                                                            $args = [
                                                                                'id' => $id,
                                                                                'name' => 'lsd['.$ck.']['.$sk.']['.$gk.']['.$dk.']['.$fk.']',
                                                                                'value' => $values[$ck][$sk][$gk][$dk][$fk] ?? $default,
                                                                            ];

                                                                            $f = '';

                                                                            // Color
                                                                            if ($type === 'color') $f = LSD_Form::colorpicker(array_merge($field, $args));
                                                                            // Border
                                                                            else if ($type === 'border') $f = LSD_Form::border(array_merge($field, $args));
                                                                            // padding
                                                                            else if ($type === 'padding') $f = LSD_Form::padding(array_merge($field, $args));
                                                                            // Typography
                                                                            else if ($type === 'typography') $f = LSD_Form::typography(array_merge($field, $args));
                                                                            // Select
                                                                            else if ($type === 'select') $f = LSD_Form::select(array_merge($field, $args));
                                                                            // Text
                                                                            else if ($type === 'text') $f = LSD_Form::text(array_merge($field, $args));
                                                                            // Number
                                                                            else if ($type === 'number') $f = LSD_Form::number(array_merge($field, $args));
                                                                            // Image
                                                                            else if ($type === 'image') $f = LSD_Form::imagepicker(array_merge($field, $args));
                                                                            // Icon
                                                                            else if ($type === 'icon') $f = LSD_Form::iconpicker(array_merge($field, $args));
                                                                            ?>
                                                                            <div>
                                                                                <div class="lsd-flex lsd-flex-row lsd-flex-items-start lsd-gap-4">
                                                                                    <div class="lsd-flex-1"><?php echo LSD_Form::label(array_merge($field, ['for' => $id])); ?></div>
                                                                                    <div class="lsd-flex-5"><?php echo LSD_Kses::form($f); ?></div>
                                                                                </div>
                                                                                <?php if (isset($field['description']) && trim($field['description'])): ?>
                                                                                    <p class="description lsd-mt-4 lsd-mb-0"><?php echo esc_html($field['description']); ?></p>
                                                                                <?php endif; ?>
                                                                            </div>
                                                                        <?php endforeach; ?>
                                                                    </div>
                                                                <?php endif; ?>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>

        <div class="lsd-spacer-10"></div>
        <div class="lsd-form-row">
            <div class="lsd-col-12 lsd-flex lsd-gap-3">
                <?php LSD_Form::nonce('lsd_settings_form'); ?>
                <?php echo LSD_Form::submit([
                    'label' => esc_html__('Save', 'listdom'),
                    'id' => 'lsd_settings_save_button',
                    'class' => 'button button-hero button-primary',
                ]); ?>
                <div>
                    <p class="lsd-util-hide lsd-settings-success-message lsd-alert lsd-success lsd-m-0"><?php esc_html_e('Options saved successfully.', 'listdom'); ?></p>
                    <p class="lsd-util-hide lsd-settings-error-message lsd-alert lsd-error lsd-m-0"><?php esc_html_e('Error: Unable to save options.', 'listdom'); ?></p>
                </div>
            </div>
        </div>
    </form>
</div>
<script>
jQuery('#lsd_settings_form').on('submit', function (e)
{
    e.preventDefault();

    // Remove Existing Errors
    jQuery('.lsd-simple-error-message').remove();

    // Elements
    const $button = jQuery("#lsd_settings_save_button");
    const $success = jQuery(".lsd-settings-success-message");
    const $error = jQuery(".lsd-settings-error-message");
    const $tab = jQuery('.nav-tab-active');

    // Loading Styles
    $button.addClass('loading').html('<i class="lsd-icon fa fa-spinner fa-pulse fa-fw"></i>');

    // Loading Wrapper
    const loading = (new ListdomLoadingWrapper());

    // Loading
    loading.start();

    const settings = jQuery(this).serialize();
    jQuery.ajax(
    {
        type: "POST",
        url: ajaxurl,
        data: "action=lsd_save_customizer&" + settings,
        success: function ()
        {
            $tab.attr('data-saved', 'true');

            // Loading Styles
            $button.removeClass('loading').html("<?php echo esc_js(esc_attr__('Save', 'listdom')); ?>");

            // Unloading
            loading.stop($success, 2000);
        },
        error: function ()
        {
            $tab.attr('data-saved', 'false');

            // Loading Styles
            $button.removeClass('loading').html("<?php echo esc_js(esc_attr__('Save', 'listdom')); ?>");

            // Unloading
            loading.stop($error, 2000);
        }
    });
});

jQuery('.lsd-customizer-reset-category').on('click', function (e)
{
    e.stopPropagation();

    // Elements
    const $button = jQuery(this);

    // Confirm
    const confirm = $button.data('confirm');

    if(!confirm)
    {
        $button.data('confirm', 1);
        $button.addClass('lsd-need-confirm');

        setTimeout(function()
        {
            $button.data('confirm', 0);
            $button.removeClass('lsd-need-confirm');
        }, 5000);

        return false;
    }
    else $button.data('confirm', 0).removeClass('lsd-need-confirm');

    // Loading Styles
    $button.addClass('loading').html('<i class="lsd-icon fa fa-spinner fa-pulse fa-fw"></i>');

    jQuery.ajax(
    {
        type: "POST",
        url: ajaxurl,
        data: "action=lsd_reset_customizer&_wpnonce="+$button.data('nonce')+'&category='+$button.data('category'),
        success: function ()
        {
            // Loading Styles
            $button.removeClass('loading').html('<i class="lsd-icon fa fa-retweet"></i>');

            // Reload
            window.location.reload();
        },
        error: function ()
        {
            // Loading Styles
            $button.removeClass('loading').html('<i class="lsd-icon fa fa-retweet"></i>');
        }
    });
});

jQuery('#lsd_settings_reset').on('submit', function (e)
{
    e.preventDefault();

    // Elements
    const $button = jQuery("#lsd_settings_reset_button");
    const $confirm = jQuery("#lsd_reset_confirm");

    // Not confirmed
    if ($confirm.val() !== "reset" && $confirm.val() !== "'reset'") return;

    // Loading Styles
    $button.addClass('loading').html('<i class="lsd-icon fa fa-spinner fa-pulse fa-fw"></i>');

    const data = jQuery(this).serialize();
    jQuery.ajax(
    {
        type: "POST",
        url: ajaxurl,
        data: "action=lsd_reset_customizer&" + data,
        success: function ()
        {
            // Loading Styles
            $button.removeClass('loading').html("<?php echo esc_js(esc_attr__('Reset display options', 'listdom')); ?>");

            // Reload
            window.location.reload();
        },
        error: function ()
        {
            // Loading Styles
            $button.removeClass('loading').html("<?php echo esc_js(esc_attr__('Reset display options', 'listdom')); ?>");
        }
    });
});
</script>
