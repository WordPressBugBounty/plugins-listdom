<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Menus_Settings $this */

$details_page = LSD_Options::details_page();

$styles = LSD_Styles::details();
$detail_types = LSD_Styles::detail_types();

$current_style = $details_page['general']['style'] ?? 'style1';
$current_detail_type = $details_page['general']['detail_type'] ?? 'premade';

$templates = [];
$templates[''] = esc_html__('Default Layout', 'listdom');
foreach(get_page_templates() as $label => $tpl) $templates[$tpl] = $label;

// Dynamic Style
$box_method = $details_page['builder']['box_method'] ?? '1';

$head1_elements = $details_page['builder']['head1']['elements'] ?? [];
$head2_elements = $details_page['builder']['head2']['elements'] ?? [];
$head3_elements = $details_page['builder']['head3']['elements'] ?? [];
$col1_elements = $details_page['builder']['col1']['elements'] ?? [];
$col2_elements = $details_page['builder']['col2']['elements'] ?? [];
$col3_elements = $details_page['builder']['col3']['elements'] ?? [];
$foot1_elements = $details_page['builder']['foot1']['elements'] ?? [];
$foot2_elements = $details_page['builder']['foot2']['elements'] ?? [];
$foot3_elements = $details_page['builder']['foot3']['elements'] ?? [];

foreach($details_page['elements'] as $key => $element)
{
    foreach(['head', 'col', 'foot'] as $s)
    {
        for($i = 1; $i <= 3; $i++) if(!isset(${$s.$i.'_elements'}[$key])) ${$s.$i.'_elements'}[$key] = ['enabled' => 0];
    }
}

// Style 3 Sections
$sections = [
    'head1' => [
        'label' => esc_html__('Header 1', 'listdom'),
        'width' => $details_page['builder']['head1']['width'] ?? '2-4',
        'elements' => $head1_elements
    ],
    'head2' => [
        'label' => esc_html__('Header 2', 'listdom'),
        'width' => $details_page['builder']['head2']['width'] ?? '1-4',
        'elements' => $head2_elements
    ],
    'head3' => [
        'label' => esc_html__('Header 3', 'listdom'),
        'width' => $details_page['builder']['head3']['width'] ?? '1-4',
        'elements' => $head3_elements
    ],
    'col1' => [
        'label' => esc_html__('Column 1', 'listdom'),
        'width' => $details_page['builder']['col1']['width'] ?? '1-4',
        'elements' => $col1_elements
    ],
    'col2' => [
        'label' => esc_html__('Column 2', 'listdom'),
        'width' => $details_page['builder']['col2']['width'] ?? '2-4',
        'elements' => $col2_elements
    ],
    'col3' => [
        'label' => esc_html__('Column 3', 'listdom'),
        'width' => $details_page['builder']['col3']['width'] ?? '1-4',
        'elements' => $col3_elements
    ],
    'foot1' => [
        'label' => esc_html__('Footer 1', 'listdom'),
        'width' => $details_page['builder']['foot1']['width'] ?? '2-4',
        'elements' => $foot1_elements
    ],
    'foot2' => [
        'label' => esc_html__('Footer 2', 'listdom'),
        'width' => $details_page['builder']['foot2']['width'] ?? '1-4',
        'elements' => $foot2_elements
    ],
    'foot3' => [
        'label' => esc_html__('Footer 3', 'listdom'),
        'width' => $details_page['builder']['foot3']['width'] ?? '1-4',
        'elements' => $foot3_elements
    ],
];

$pro_elements = [];

$optional_addons = [];
if (!class_exists(\LSDPACELM\Base::class) && !class_exists(\LSDPACDIV\Base::class))
{
    $optional_addons[] = sprintf(
        esc_html__('Activate the Listdom %s or %s add-on to design custom single listing pages using Elementor or Divi Builders.', 'listdom'),
        '<a href="' . LSD_Base::getAddonURL('Elementor') . '"><strong>' . esc_html__('Elementor', 'listdom') . '</strong></a>',
        '<a href="' . LSD_Base::getAddonURL('Divi') . '"><strong>' . esc_html__('Divi', 'listdom') . '</strong></a>'
    );
}
?>
<div class="lsd-settings-wrap" id="lsd_settings_details_page_wp">
    <form id="lsd_settings_form">
        <div class="lsd-form-row">
            <div class="lsd-col-4 lsd-pr-4 lsd-flex lsd-flex-col lsd-flex-items-stretch lsd-flex-content-start lsd-gap-3 lsd-alert-no-my">
                <h3 class="lsd-my-0 lsd-admin-title"><?php esc_html_e('General', 'listdom'); ?></h3>
                <div class="lsd-row">
                    <div class="lsd-col-6"><?php echo LSD_Form::label([
                        'title' => esc_html__('Style Category', 'listdom'),
                        'for' => 'lsd_details_page_style',
                    ]); ?></div>
                    <div class="lsd-col-6"><?php echo LSD_Form::select([
                        'id' => 'lsd_details_page_detail_type',
                        'name' => 'lsd[general][detail_type]',
                        'value' => $current_detail_type,
                        'options' => $detail_types,
                    ]); ?></div>
                </div>
                <div class="lsd-row">
                    <div class="lsd-col-6"><?php echo LSD_Form::label([
                        'title' => esc_html__('Style', 'listdom'),
                        'for' => 'lsd_details_page_style',
                    ]); ?></div>
                    <div class="lsd-col-6"><?php echo LSD_Form::select([
                        'id' => 'lsd_details_page_style',
                        'name' => 'lsd[general][style]',
                        'value' => $current_style,
                        'options' => $styles,
                    ]); ?></div>
                </div>
                <div class="lsd-row lsd-style-dependency lsd-style-dependency-style1 <?php echo $current_style === 'style1' ? '' : 'lsd-util-hide'; ?>">
                    <div class="lsd-col-12">
                        <p class="lsd-alert lsd-info">
                            <?php esc_html_e('You can rearrange and configure the page elements using the elements panel on the right side.', 'listdom'); ?>
                        </p>
                    </div>
                </div>
                <div class="lsd-row lsd-style-dependency lsd-style-dependency-style2 lsd-style-dependency-style3 lsd-style-dependency-style4 <?php echo in_array($current_style, ['style2', 'style3', 'style4']) ? '' : 'lsd-util-hide'; ?>">
                    <div class="lsd-col-12">
                        <p class="lsd-alert lsd-info">
                            <?php esc_html_e("You can enable or disable elements and adjust their configuration using the panel on the right. The order of elements won’t be affected; they will appear in their designated positions if enabled.", 'listdom'); ?>
                        </p>
                    </div>
                </div>
                <div class="lsd-row lsd-style-dependency lsd-style-dependency-dynamic <?php echo $current_style === 'dynamic' ? '' : 'lsd-util-hide'; ?>">
                    <div class="lsd-col-12">
                        <p class="lsd-alert lsd-info">
                            <?php esc_html_e("You can adjust the configuration of elements using the panel on the right. Additionally, you can modify the position and order of elements using the design builder on the right side.", 'listdom'); ?>
                        </p>
                    </div>
                </div>

                <?php if (class_exists('ET_Builder_Element')): ?>
                <div class="lsd-flex lsd-flex-col lsd-gap-3 lsd-my-0">
                    <p class="lsd-alert lsd-info lsd-my-0">
                        <?php echo esc_html__('You can design and manage styles with Divi builder in Divi → Theme Builder.', 'listdom'); ?>
                    </p>
                </div>
                <?php endif; ?>

                <div class="lsd-row">
                    <div class="lsd-col-6"><?php echo LSD_Form::label([
                        'title' => esc_html__('Theme Layout', 'listdom'),
                        'for' => 'lsd_theme_template',
                    ]); ?></div>
                    <div class="lsd-col-6"><?php echo LSD_Form::select([
                        'id' => 'lsd_theme_template',
                        'name' => 'lsd[general][theme_template]',
                        'value' => $details_page['general']['theme_template'] ?? '',
                        'options' => $templates
                    ]); ?></div>
                </div>
                <div class="lsd-row">
                    <div class="lsd-col-12">
                        <p class="description lsd-my-0">
                            <?php echo sprintf(esc_html__("If the website theme supports multiple layouts for %s, you can select your desired layout for single listing page. If you're not sure about this option, just select the Default Layout. Also, don't select an archive layout if your theme has added this option.", 'listdom'), '<strong>'.esc_html__('single pages', 'listdom').'</strong>'); ?>
                        </p>
                    </div>
                </div>
                <?php if ($this->isPro()): ?>
                <div class="lsd-row">
                    <div class="lsd-col-6"><?php echo LSD_Form::label([
                        'title' => esc_html__('Display Options Per Category', 'listdom'),
                        'for' => 'lsd_dispc',
                    ]); ?></div>
                    <div class="lsd-col-6"><?php echo LSD_Form::select([
                        'id' => 'lsd_dispc',
                        'name' => 'lsd[general][dispc]',
                        'options' => [
                            0 => esc_html__('Disabled', 'listdom'),
                            1 => esc_html__('Enabled', 'listdom'),
                        ],
                        'value' => $details_page['general']['dispc'] ?? 0,
                    ]); ?></div>
                </div>
                <div class="lsd-row">
                    <div class="lsd-col-12">
                        <p class="description lsd-mt-0">
                            <?php echo esc_html__("When enabled, you can assign a unique single listing style to each category.", 'listdom'); ?>
                        </p>
                    </div>
                </div>
                <?php endif; ?>

                <?php if (!class_exists(LSDADDSUB::class) && !class_exists(\LSDPACSUB\Base::class)): ?>
                    <?php $optional_addons[] = $this->missAddonMessage(esc_html__('Subscription', 'listdom'), '<strong>'.esc_html__('Display Options Per Membership', 'listdom').'</strong>'); ?>
                <?php else: ?>
                    <div class="lsd-row">
                        <div class="lsd-col-6"><?php echo LSD_Form::label([
                            'title' => esc_html__('Display Options Per Package', 'listdom'),
                            'for' => 'lsd_dispp',
                        ]); ?></div>
                        <div class="lsd-col-6"><?php echo LSD_Form::select([
                            'id' => 'lsd_dispp',
                            'name' => 'lsd[general][dispp]',
                            'options' => [
                                0 => esc_html__('Disabled', 'listdom'),
                                1 => esc_html__('Enabled', 'listdom'),
                            ],
                            'value' => $details_page['general']['dispp'] ?? 0,
                        ]); ?></div>
                    </div>
                    <div class="lsd-row">
                        <div class="lsd-col-12">
                            <p class="description lsd-mt-0">
                                <?php echo esc_html__("When enabled, you can assign a unique single listing style to each package.", 'listdom'); ?>
                            </p>
                        </div>
                    </div>
                <?php endif; ?>
                <?php if ($this->isLite()): $optional_addons[] = $this->missFeatureMessage(esc_html__('Display Options Per Category & Per Listing', 'listdom'), true); ?>
                <?php else: ?>
                <div class="lsd-row">
                    <div class="lsd-col-6"><?php echo LSD_Form::label([
                        'title' => esc_html__('Display Options Per Listing', 'listdom'),
                        'for' => 'lsd_displ',
                    ]); ?></div>
                    <div class="lsd-col-6"><?php echo LSD_Form::select([
                        'id' => 'lsd_displ',
                        'name' => 'lsd[general][displ]',
                        'options' => [
                            0 => esc_html__('Disabled', 'listdom'),
                            'admin' => esc_html__('Admin Only', 'listdom'),
                            'owner' => esc_html__('Admin & Listing Owner', 'listdom'),
                        ],
                        'value' => $details_page['general']['displ'] ?? 0,
                    ]); ?></div>
                </div>
                <div class="lsd-row">
                    <div class="lsd-col-12">
                        <p class="description lsd-mt-0">
                            <?php echo esc_html__("Select who has access to disable or enable the Elements for each listing and can select a certain Style for that.", 'listdom'); ?>
                        </p>
                    </div>
                </div>
                <?php endif; ?>
                <div class="lsd-row">
                    <div class="lsd-col-6"><?php echo LSD_Form::label([
                        'title' => esc_html__('Comments', 'listdom'),
                        'for' => 'lsd_comments',
                    ]); ?></div>
                    <div class="lsd-col-6"><?php echo LSD_Form::switcher([
                        'id' => 'lsd_comments',
                        'name' => 'lsd[general][comments]',
                        'value' => $details_page['general']['comments'] ?? 0,
                    ]); ?></div>
                </div>
                <?php if (count($optional_addons)): ?>
                <div class="lsd-alert lsd-warning lsd-mt-3">
                    <ul class="lsd-p-0 lsd-m-0 lsd-flex lsd-flex-col lsd-gap-3">
                        <?php foreach ($optional_addons as $optional_addon): ?>
                        <li><?php echo LSD_Kses::element($optional_addon); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>
            </div>
            <div class="lsd-col-8">
                <ul class="lsd-tab-switcher <?php echo $current_style === 'dynamic' ? '' : 'lsd-util-hide'; ?> lsd-sub-tabs lsd-flex lsd-gap-3 lsd-mb-4" id="lsd_dynamic_switcher" data-for=".lsd-tab-switcher-content-details">
                    <li data-tab="config" class="<?php echo $current_style !== 'dynamic' ? 'lsd-sub-tabs-active' : ''; ?>"><a href="#"><?php esc_html_e('Configuration', 'listdom'); ?></a></li>
                    <li data-tab="builder" class="<?php echo $current_style === 'dynamic' ? 'lsd-sub-tabs-active' : ''; ?>"><a href="#"><?php esc_html_e('Design Builder', 'listdom'); ?></a></li>
                </ul>
                <div class="lsd-tab-switcher-content lsd-tab-switcher-content-details <?php echo $current_style !== 'dynamic' ? 'lsd-tab-switcher-content-active' : ''; ?>" id="lsd-tab-switcher-config-content">
                    <h3 class="lsd-mt-0"><?php esc_html_e('Elements', 'listdom'); ?></h3>
                    <div class="lsd-flex lsd-flex-col lsd-flex-items-stretch">
                        <ul class="lsd-elements lsd-sortable lsd-m-0 lsd-flex-o-2">
                            <?php foreach($details_page['elements'] as $key => $element): ?>
                                <?php
                                $elm = LSD_Element::instance($key);
                                if (!is_object($elm)) continue;

                                if ($elm->pro() && $this->isLite())
                                {
                                    $pro_elements[] = $elm->label;
                                    continue;
                                }
                                ?>
                                <li id="lsd_element_<?php echo esc_attr($key); ?>" class="<?php echo !$element['enabled'] ? 'lsd-element-disabled' : ''; ?>">
                                    <?php echo LSD_Kses::form($elm->form($element)); ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <?php if (count($pro_elements)): ?>
                            <div class="lsd-alert lsd-warning lsd-mt-0 lsd-flex-o-1">
                                <?php echo $this->missFeatureMessage($this->human_comma_separate($pro_elements), true, false); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="lsd-tab-switcher-content lsd-tab-switcher-content-details <?php echo $current_style === 'dynamic' ? 'lsd-tab-switcher-content-active' : ''; ?>" id="lsd-tab-switcher-builder-content">
                    <h3 class="lsd-mt-0"><?php esc_html_e('Design Builder', 'listdom'); ?></h3>
                    <div class="lsd-form-row lsd-mt-4">
                        <div class="lsd-col-2 lsd-flex lsd-flex-content-end lsd-flex-align-items-center">
                            <label for="lsd-dynamic-box-method"><?php echo esc_html__('Box Method', 'listdom'); ?></label>
                        </div>
                        <div class="lsd-col-2">
                            <select class="lsd-mt-2" name="lsd[builder][box_method]" id="lsd-dynamic-box-method">
                                <option value="1" <?php echo $box_method === '1' ? 'selected' : ''; ?>><?php esc_html_e('Whole Box', 'listdom'); ?></option>
                                <option value="2" <?php echo $box_method === '2' ? 'selected' : ''; ?>><?php esc_html_e('Row Box', 'listdom'); ?></option>
                                <option value="3" <?php echo $box_method === '3' ? 'selected' : ''; ?>><?php esc_html_e('Section Box', 'listdom'); ?></option>
                                <option value="4" <?php echo $box_method === '4' ? 'selected' : ''; ?>><?php esc_html_e('Element Box', 'listdom'); ?></option>
                                <option value="5" <?php echo $box_method === '5' ? 'selected' : ''; ?>><?php esc_html_e('No Box', 'listdom'); ?></option>
                            </select>
                        </div>
                    </div>
                    <div class="lsd-alert lsd-info">
                        <ul class="lsd-unordered lsd-m-0 lsd-ml-4">
                            <li><?php esc_html_e("You don't need to use all the sections. Use the sections that fit the design you want to create. To disable a section, simply leave all elements unchecked.", 'listdom'); ?></li>
                            <li><?php esc_html_e("Elements will not appear on the frontend if they are disabled in the configuration tab or listing options.", 'listdom'); ?></li>
                            <li><?php esc_html_e('The Map element can only be enabled once.', 'listdom'); ?></li>
                            <li><?php esc_html_e('You can rearrange the order of elements in each section by simply dragging and dropping them.', 'listdom'); ?></li>
                        </ul>
                    </div>
                    <div class="lsd-builder-column-wrapper">
                        <?php foreach($sections as $sec => $section): ?>
                        <div class="lsd-builder-column lsd-builder-column-<?php echo esc_attr($sec); ?>">
                            <h3><?php echo esc_html($section['label']); ?></h3>
                            <div class="lsd-mb-4">
                                <label for="lsd-dynamic-<?php echo esc_attr($sec); ?>-width"><?php echo esc_html__('Section Width', 'listdom'); ?></label>
                                <select class="lsd-mt-2" name="lsd[builder][<?php echo esc_attr($sec); ?>][width]" id="lsd-dynamic-<?php echo esc_attr($sec); ?>-width">
                                    <option value="1-4" <?php echo isset($section['width']) && $section['width'] === '1-4' ? 'selected' : ''; ?>>1/4</option>
                                    <option value="2-4" <?php echo isset($section['width']) && $section['width'] === '2-4' ? 'selected' : ''; ?>>2/4</option>
                                    <option value="3-4" <?php echo isset($section['width']) && $section['width'] === '3-4' ? 'selected' : ''; ?>>3/4</option>
                                    <option value="4-4" <?php echo isset($section['width']) && $section['width'] === '4-4' ? 'selected' : ''; ?>>4/4</option>
                                </select>
                            </div>
                            <ul class="lsd-sortable">
                                <?php foreach($section['elements'] as $key => $element): $elm = LSD_Element::instance($key); if(!is_object($elm)) continue; $enabled = isset($element['enabled']) && $element['enabled']; ?>
                                <li class="<?php echo $enabled ? 'lsd-builder-element-enabled' : 'lsd-builder-element-disabled'; ?>" data-key="<?php echo esc_attr($key); ?>">
                                    <input type="hidden" name="lsd[builder][<?php echo esc_attr($sec); ?>][elements][<?php echo esc_attr($key); ?>][enabled]" value="0">
                                    <input type="checkbox" name="lsd[builder][<?php echo esc_attr($sec); ?>][elements][<?php echo esc_attr($key); ?>][enabled]" value="1" id="lsd-dynamic-<?php echo esc_attr($sec); ?>-<?php echo esc_attr($key); ?>" <?php echo $enabled ? 'checked' : ''; ?>>
                                    <label class="lsd-handler" for="lsd-dynamic-<?php echo esc_attr($sec); ?>-<?php echo esc_attr($key); ?>"><?php echo esc_html($elm->label); ?></label>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="lsd-spacer-30"></div>
        <div class="lsd-form-row lsd-settings-submit-wrapper">
			<div class="lsd-col-12 lsd-flex lsd-gap-3 lsd-flex-content-end">
				<?php LSD_Form::nonce('lsd_settings_form'); ?>
                <button type="submit" id="lsd_settings_save_button" class="lsd-primary-button">
                    <?php esc_html_e('Save The Changes', 'listdom'); ?>
                    <i class='lsdi lsdi-checkmark-circle'></i>
                </button>
                <div>
                    <p class="lsd-util-hide lsd-settings-success-message lsd-alert lsd-success lsd-m-0"><?php esc_html_e('Options saved successfully.', 'listdom'); ?></p>
                    <p class="lsd-util-hide lsd-settings-error-message lsd-alert lsd-error lsd-m-0"><?php esc_html_e('Error: Unable to save options.', 'listdom'); ?></p>
                </div>
			</div>
        </div>
    </form>
</div>
<script>
jQuery('#lsd_settings_form').on('submit', function(e)
{
    e.preventDefault();

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
        data: "action=lsd_save_details_page&" + settings,
        success: function()
        {
            $tab.attr('data-saved', 'true');

            // Loading Styles
            $button.removeClass('loading').html("<?php echo esc_js(esc_attr__('Save The Changes', 'listdom')); ?><i class='lsdi lsdi-checkmark-circle'></i>");

            // Unloading
            loading.stop($success, 2000);
        },
        error: function()
        {
            $tab.attr('data-saved', 'false');

            // Loading Styles
            $button.removeClass('loading').html("<?php echo esc_js(esc_attr__('Save The Changes', 'listdom')); ?><i class='lsdi lsdi-checkmark-circle'></i>");

            // Unloading
            loading.stop($error, 2000);
        }
    });
});
</script>
