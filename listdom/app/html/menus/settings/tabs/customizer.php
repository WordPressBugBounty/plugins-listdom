<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Menus_Settings $this */

// Customizer
$values = LSD_Options::customizer();

// Options
$options = (new LSD_Customizer())->options();
?>
<div class="lsd-settings-wrap lsd-settings-customizer" id="lsd_settings_display_options">

    <?php if (class_exists(\LSDPACELM\Base::class) || class_exists(\LSDPACDIV\Base::class)): ?>
    <div class="lsd-m-0">
        <div class="lsd-alert lsd-warning">
            <strong><?php esc_html_e('Heads Up!', 'listdom'); ?></strong>
            <?php echo sprintf(
                /* translators: 1: Elementor link, 2: Bricks link, 3: Divi link. */
                esc_html__("If you're using a page builder like %1\$s, %2\$s or %3\$s, we recommend managing your styles (colors, typography, spacing, etc.) directly through the page builder’s controls. Changes made in this Customizer may not affect elements created with a page builder but can still apply to parts of the site not built with one.", 'listdom'),
                '<strong>'.esc_html__('Elementor', 'listdom').'</strong>',
                '<strong>'.esc_html__('Bricks', 'listdom').'</strong>',
                '<strong>'.esc_html__('Divi', 'listdom').'</strong>'
            ); ?>
        </div>
    </div>
    <?php endif; ?>

    <div class="lsd-title-reset-section">
        <div class="lsd-flex lsd-gap-3">
            <h3 id="lsd_customizer_tab_title" class="lsd-m-0 lsd-admin-title"></h3>
            <div class="lsd-tooltip lsd-tooltip-right lsd-cursor-pointer lsd-customizer-reset-category" data-confirm="0" data-nonce="<?php echo wp_create_nonce('lsd_settings_form'); ?>" data-lsd-tooltip="<?php esc_attr_e('Click twice to reset section', 'listdom'); ?>">
                <i class="lsdi lsdi-reset"></i>
            </div>
        </div>
        <form id="lsd_settings_reset" class="lsd-flex lsd-flex-row lsd-flex-items-stretch lsd-flex-content-end lsd-gap-3 lsd-p-0">
            <input id="lsd_reset_confirm" placeholder="<?php esc_attr_e("Type 'reset' to confirm your action.", 'listdom'); ?>" title="<?php esc_attr_e('Confirm', 'listdom'); ?>">
            <?php LSD_Form::nonce('lsd_settings_form'); ?>
            <button type="submit" class="lsd-neutral-button" id="lsd_settings_reset_button"><?php esc_html_e('Reset Customizer', 'listdom'); ?></button>
        </form>
    </div>

    <form id="lsd_settings_form">
        <?php $c = 0; foreach ($options as $ck => $category): $c++; ?>
        <div id="lsd_panel_customizer_<?php echo esc_html(strtolower(str_replace(' ', '-', $category['title'] ?? ''))); ?>" data-category="<?php echo esc_attr($ck); ?>" data-title="<?php echo esc_html($category['title']); ?>" class="lsd-settings-form-group lsd-tab-content <?php echo ($this->subtab ? $this->subtab === $ck : $c === 1) ? 'lsd-tab-content-active' : ''; ?>">
            <?php if (isset($category['sections']) && is_array($category['sections']) && (count($category['sections']) > 1 || (isset($category['display_sections_force']) && $category['display_sections_force']))): ?>
                <ul class="lsd-tab-switcher lsd-level-3-menu lsd-sub-tabs lsd-flex lsd-mb-4" data-for=".lsd-customizer-<?php echo esc_attr($ck); ?>-category-tab-switcher-content">
                    <?php $s = 0; foreach ($category['sections'] as $sk => $section): $s++; ?>
                        <li data-tab="customizer-<?php echo esc_attr($ck); ?>-<?php echo esc_attr($sk); ?>" class="<?php echo $s === 1 ? 'lsd-sub-tabs-active' : ''; ?>"><a href="#"><?php echo esc_html($section['title'] ?? ''); ?></a></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <?php if (isset($category['sections']) && is_array($category['sections'])): ?>
                <?php $inherit = 0; $s = 0; foreach ($category['sections'] as $sk => $section): $s++; ?>
                    <div class="lsd-tab-switcher-content lsd-customizer-<?php echo esc_attr($ck); ?>-category-tab-switcher-content <?php echo $s === 1 ? 'lsd-tab-switcher-content-active' : ''; ?>" id="lsd-tab-switcher-customizer-<?php echo esc_attr($ck); ?>-<?php echo esc_attr($sk); ?>-content">
                        <div class="lsd-flex lsd-flex-col lsd-flex-items-stretch lsd-gap-4">
                            <?php if (isset($section['description']) && trim($section['description'])): ?>
                                <p class="lsd-admin-description lsd-my-0"><?php echo esc_html($section['description']); ?></p>
                            <?php endif; ?>

                            <?php if (isset($section['inherit']) && is_array($section['inherit'])): $inherit = $values[$ck][$sk]['inherit'] ?? (int) $section['inherit']['enabled'] ?? 0 ?>
                                <div class="lsd-flex lsd-flex-row lsd-flex-content-start lsd-gap-4">
                                    <div><?php echo LSD_Form::switcher([
                                        'id' => 'lsd-customizer-'.$ck.'-'.$sk.'-inherit',
                                        'name' => 'lsd['.$ck.']['.$sk.'][inherit]',
                                        'toggle' => '#lsd-customizer-'.$ck.'-'.$sk.'-divisions',
                                        'value' => $inherit
                                    ]); ?></div>
                                    <label class="lsd-fields-label" for="<?php echo esc_attr('lsd-customizer-'.$ck.'-'.$sk.'-inherit'); ?>"><?php echo esc_html($section['inherit']['text']); ?></label>
                                    <?php echo LSD_Form::hidden([
                                        'name' => 'lsd['.$ck.']['.$sk.'][inherit_from]',
                                        'value' => $section['inherit']['key'] ?? ''
                                    ]); ?>
                                </div>
                            <?php endif; ?>

                            <div id="<?php echo esc_attr('lsd-customizer-'.$ck.'-'.$sk.'-divisions'); ?>" class="<?php echo $inherit ? 'lsd-util-hide' : ''; ?>">
                                <?php if (isset($section['groups']) && is_array($section['groups']) && count($section['groups'])): ?>
                                    <div class="lsd-settings-group-wrapper">
                                        <?php $g = 0; foreach ($section['groups'] as $gk => $group): $g++; ?>
                                            <div class="lsd-settings-fields-wrapper lsd-form-group lsd-featured-form-group lsd-px-4 lsd-my-0 lsd-flex lsd-flex-col lsd-flex-items-stretch lsd-gap-4">
                                                <div class="lsd-flex lsd-flex-row lsd-flex-items-start">
                                                    <div class="<?php echo isset($group['sub_title']) && trim($group['sub_title']) ? 'lsd-admin-section-heading' : ''; ?>">
                                                        <div class="lsd-flex lsd-gap-3 lsd-flex-align-items-baseline">
                                                        <?php if (isset($group['title']) && trim($group['title'])): ?>
                                                            <h3 class="lsd-my-0 lsd-admin-title"><?php echo esc_html($group['title']); ?></h3>
                                                        <?php endif; ?>
                                                            <div class="lsd-tooltip lsd-tooltip-right lsd-cursor-pointer lsd-customizer-reset-category" data-confirm="0" data-category="<?php echo esc_attr($ck.'.'.$sk.'.'.$gk); ?>" data-nonce="<?php echo wp_create_nonce('lsd_settings_form'); ?>" data-lsd-tooltip="<?php esc_attr_e('Click twice to reset section', 'listdom'); ?>">
                                                                <i class="lsdi lsdi-reset"></i>
                                                            </div>
                                                        </div>
                                                        <?php if (isset($group['sub_title']) && trim($group['sub_title'])): ?>
                                                            <p class="lsd-admin-description lsd-my-0"><?php echo esc_html($group['sub_title']); ?></p>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>

                                                <?php if (isset($group['divisions']) && is_array($group['divisions']) && count($group['divisions'])): ?>
                                                    <?php if(count($group['divisions']) > 1): ?>
                                                    <ul class="lsd-tab-switcher lsd-level-5-menu lsd-sub-tabs lsd-flex lsd-mb-4" data-for=".lsd-customizer-<?php echo esc_attr($ck); ?>-<?php echo esc_attr($sk); ?>-<?php echo esc_attr($gk); ?>-division-tab-switcher-content">
                                                        <?php $d = 0; foreach ($group['divisions'] as $dk => $division): if (!isset($division['title']) || trim($division['title']) === '') continue; $d++; ?>
                                                            <li data-tab="customizer-<?php echo esc_attr($ck); ?>-<?php echo esc_attr($sk); ?>-<?php echo esc_attr($gk); ?>-<?php echo esc_attr($dk); ?>" class="<?php echo $d === 1 ? 'lsd-sub-tabs-active' : ''; ?>"><a href="#"><?php echo esc_html($division['title']); ?></a></li>
                                                        <?php endforeach; ?>
                                                    </ul>
                                                    <?php endif; ?>

                                                    <div class="lsd-customizer-division-wrapper">
                                                        <?php $d = 0; foreach ($group['divisions'] as $dk => $division): $d++; ?>
                                                            <div class="lsd-tab-switcher-content lsd-customizer-<?php echo esc_attr($ck); ?>-<?php echo esc_attr($sk); ?>-<?php echo esc_attr($gk); ?>-division-tab-switcher-content <?php echo $d === 1 ? 'lsd-tab-switcher-content-active' : ''; ?>" id="lsd-tab-switcher-customizer-<?php echo esc_attr($ck); ?>-<?php echo esc_attr($sk); ?>-<?php echo esc_attr($gk); ?>-<?php echo esc_attr($dk); ?>-content">
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
                                                                            else if ($type === 'select') $f = LSD_Form::select(array_merge($field, array_merge($args , ['class' => 'lsd-admin-input'])));
                                                                            // Text
                                                                            else if ($type === 'text') $f = LSD_Form::text(array_merge($field, array_merge($args , ['class' => 'lsd-admin-input'])));
                                                                            // Number
                                                                            else if ($type === 'number') $f = LSD_Form::number(array_merge($field, array_merge($args , ['class' => 'lsd-admin-input'])));

                                                                            else if ($type === 'unit_number') $f = LSD_Form::unit_number(array_merge($field, $args));
                                                                            // Image
                                                                            else if ($type === 'image') $f = LSD_Form::imagepicker(array_merge($field, $args));
                                                                            // Icon
                                                                            else if ($type === 'icon') $f = LSD_Form::iconpicker(array_merge($field, $args));
                                                                            ?>
                                                                            <div>
                                                                                <div class="lsd-flex lsd-flex-row lsd-flex-items-start lsd-gap-4">
                                                                                    <div class="lsd-flex-1"><?php echo LSD_Form::label(array_merge($field, ['for' => $id, 'class' => 'lsd-fields-label'])); ?></div>
                                                                                    <div class="lsd-flex-5"><?php echo LSD_Kses::form($f); ?></div>
                                                                                </div>
                                                                                <?php if (isset($field['description']) && trim($field['description'])): ?>
                                                                                    <p class="lsd-admin-description-tiny lsd-mt-2 lsd-mb-0"><?php echo esc_html($field['description']); ?></p>
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

        <div class="lsd-spacer-30"></div>
        <div class="lsd-form-row lsd-settings-submit-wrapper">
            <div class="lsd-col-12 lsd-flex lsd-gap-3 lsd-flex-content-end">
                <?php LSD_Form::nonce('lsd_settings_form'); ?>
                <button type="submit" id="lsd_settings_save_button" class="lsd-primary-button">
                    <?php esc_html_e('Save The Changes', 'listdom'); ?>
                    <i class='lsdi lsdi-checkmark-circle'></i>
                </button>
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
    const $tab = jQuery('.lsd-nav-tab-active');

    // Loading Wrapper
    const loading = new ListdomButtonLoader($button);
    loading.start("<?php echo esc_js( esc_html__('Saving', 'listdom') ); ?>");

    const settings = jQuery(this).serialize();
    jQuery.ajax(
    {
        type: "POST",
        url: ajaxurl,
        data: "action=lsd_save_customizer&" + settings,
        success: function ()
        {
            $tab.attr('data-saved', 'true');

            listdom_toastify("<?php echo esc_js(esc_html__('Options saved successfully.', 'listdom')); ?>", 'lsd-success');

            // Unloading
            loading.stop();
        },
        error: function ()
        {
            $tab.attr('data-saved', 'false');

            listdom_toastify("<?php echo esc_js(esc_html__('Error: Unable to save options.', 'listdom')); ?>", 'lsd-error');

            // Unloading
            loading.stop();
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

    // Loading Wrapper
    const loading = new ListdomButtonLoader($button);
    loading.start("");

    jQuery.ajax(
    {
        type: "POST",
        url: ajaxurl,
        data: "action=lsd_reset_customizer&_wpnonce="+$button.data('nonce')+'&category='+$button.data('category'),
        success: function ()
        {
            // Unloading
            loading.stop();

            // Reload
            window.location.reload();
        },
        error: function ()
        {
            // Unloading
            loading.stop();
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

    // Loading Wrapper
    const loading = new ListdomButtonLoader($button);
    loading.start("<?php echo esc_js( esc_html__('Reset Customizer', 'listdom') ); ?>");

    const data = jQuery(this).serialize();
    jQuery.ajax(
    {
        type: "POST",
        url: ajaxurl,
        data: "action=lsd_reset_customizer&" + data,
        success: function ()
        {
            // Unloading
            loading.stop();

            // Reload
            window.location.reload();
        },
        error: function ()
        {
            // Unloading
            loading.stop();
        }
    });
});
</script>
