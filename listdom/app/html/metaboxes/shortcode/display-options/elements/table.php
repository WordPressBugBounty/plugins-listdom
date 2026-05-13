<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_PTypes_Shortcode $this */
/** @var array $options */
/** @var array $price_components */

$table = $options['table'] ?? [];

// Fields
$fields = new LSD_Fields();

$fields_data = $fields->get();
$titles = $fields->titles();
$devices = isset($table['devices']) && is_array($table['devices']) ? $table['devices'] : [];
$skin = new LSD_Skins_Table();
$device_configs = $skin->get_device_configs($fields_data, $titles, $table);

$render_columns = function ($device, $columns, $name_prefix) use ($titles)
{
    ?>
    <div class="lsd-sortable lsd-display-options-table-columns lsd-settings-fields-sub-wrapper">
        <?php foreach ($columns as $key => $column): ?>
            <?php
            if ($key === '') continue;

            $label = $column['label'] ?? ($titles[$key] ?? '');
            $enabled = $column['enabled'] ?? 0;
            $width = $column['width'] ?? '';
            $safe_key = sanitize_key((string) $key);
            ?>
            <div class="lsd-form-row lsd-cursor-move">
                <div class="lsd-col-6 lsd-flex lsd-gap-3 lsd-flex-align-items-center">
                    <i class="lsd-icon fas fa-arrows-alt lsd-handler lsd-gray-badge"></i>
                    <?php echo LSD_Form::text([
                        'class' => 'lsd-admin-input',
                        'id' => 'lsd_display_options_skin_table_' . $device . '_' . $safe_key . '_label',
                        'name' => $name_prefix . '[' . $safe_key . '][label]',
                        'placeholder' => $titles[$key] ?? '',
                        'value' => esc_attr($label),
                    ]); ?>
                </div>
                <div class="lsd-col-6 lsd-flex lsd-gap-3 lsd-flex-align-items-center">
                    <?php echo LSD_Form::switcher([
                        'id' => 'lsd_display_options_skin_table_' . $device . '_' . $safe_key . '_enabled',
                        'name' => $name_prefix . '[' . $safe_key . '][enabled]',
                        'value' => esc_attr($enabled),
                        'toggle' => '.lsd-display-options-skin-table-' . $device . '-' . $safe_key,
                    ]); ?>
                    <div class="lsd-tooltip lsd-display-options-skin-table-<?php echo esc_attr($device . '-' . $safe_key); ?> <?php echo $enabled ? '' : 'lsd-util-hide'; ?>" data-lsd-tooltip="<?php esc_attr_e('Column Width in px', 'listdom'); ?>">
                        <?php echo LSD_Form::number([
                            'class' => 'lsd-admin-input',
                            'id' => 'lsd_display_options_skin_table_' . $device . '_' . $safe_key . '_width',
                            'name' => $name_prefix . '[' . $safe_key . '][width]',
                            'placeholder' => 150,
                            'value' => esc_attr($width),
                            'attributes' => [
                                'min' => 0,
                                'step' => 1,
                            ],
                        ]); ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php
};

$optional_addons = [];
?>

<div class="lsd-settings-group-wrapper">
    <div class="lsd-settings-fields-wrapper">
        <div class="lsd-admin-section-heading">
            <h3 class="lsd-my-0 lsd-admin-title"><?php echo esc_html__("Select the Style", 'listdom'); ?></h3>
            <p class="lsd-admin-description lsd-m-0"><?php echo esc_html__("Pick a style variation of the selected skin or adjust listing elements (if not using Elementor/Divi).", 'listdom'); ?> </p>
        </div>

        <div class="lsd-col-12 lsd-p-0">
            <?php echo LSD_Form::select([
                'id' => 'lsd_display_options_skin_table_style',
                'class' => 'lsd-display-options-style-selector lsd-display-options-style-toggle lsd-admin-input',
                'name' => 'lsd[display][table][style]',
                'options' => LSD_Styles::table(),
                'value' => 'style1',
                'attributes' => [
                    'data-parent' => '#lsd_skin_display_options_table',
                ],
            ]); ?>
        </div>
    </div>

    <div class="lsd-settings-fields-wrapper">
        <div id="lsd_display_options_style">
            <div class="lsd-row">
                <div class="lsd-col-12 lsd-settings-fields-sub-wrapper">
                    <div class="lsd-admin-section-heading">
                        <h3 class="lsd-admin-title lsd-m-0"><?php echo esc_html__("Rows Display Options", 'listdom'); ?></h3>
                        <p class="lsd-admin-description lsd-m-0"><?php echo esc_html__("You can easily customize the table columns and rearrange items by using the drag-and-drop feature.", 'listdom'); ?> </p>
                    </div>
                    <div class="lsd-display-options-table-devices">
                        <?php if (!$this->isPro()): ?>
                            <p class="lsd-alert lsd-warning lsd-mt-0"><?php echo LSD_Base::missFeatureMessage(esc_html__('Responsive Controls', 'listdom'), true); ?></p>
                        <?php endif; ?>
                        <ul class="lsd-display-options-table-device-tabs lsd-level-3-menu" data-active-device="desktop">
                            <?php foreach ($device_configs as $device => $config): ?>
                                <li class="<?php echo $device === 'desktop' ? 'lsd-sub-tabs-active' : ''; ?>" data-device="<?php echo esc_attr($device); ?>">
                                    <i class="lsd-icon <?php echo esc_attr($config['icon']); ?>" aria-hidden="true"></i>
                                    <span><?php echo esc_html($config['label']); ?></span>
                                </li>
                            <?php endforeach; ?>
                            <?php if (!empty($locked_device_configs)): ?>
                                <?php foreach ($locked_device_configs as $device => $config): ?>
                                    <li class="lsd-disabled">
                                        <i class="lsd-icon <?php echo esc_attr($config['icon']); ?>" aria-hidden="true"></i>
                                        <span><?php echo esc_html($config['label']); ?> (<?php esc_html_e('Pro', 'listdom'); ?>)</span>
                                    </li>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </ul>

                        <?php foreach ($device_configs as $device => $config): ?>
                            <?php $device_classes = ['lsd-display-options-table-device']; if ($device !== 'desktop') $device_classes[] = 'lsd-util-hide'; ?>
                            <div class="<?php echo esc_attr(implode(' ', $device_classes)); ?>" data-device="<?php echo esc_attr($device); ?>">
                                <?php if (!empty($config['inherit_field'])): ?>
                                    <div class="lsd-flex lsd-gap-3 lsd-flex-align-items-center">
                                        <div><?php echo LSD_Form::switcher([
                                                'id' => $config['inherit_field']['id'],
                                                'name' => $config['inherit_field']['name'],
                                                'value' => $config['inherit_value'],
                                                'toggle' => $config['inherit_field']['target'],
                                            ]); ?></div>
                                        <label for="<?php echo esc_attr($config['inherit_field']['id']); ?>"><?php echo esc_html($config['inherit_field']['label']); ?></label>
                                    </div>
                                <?php endif; ?>

                                <?php
                                $settings_id = $config['settings_id'] ?? '';
                                $settings_classes = ['lsd-display-options-table-device-settings'];
                                if (!empty($config['inherit_field']) && $config['inherit_value']) $settings_classes[] = 'lsd-util-hide';
                                ?>

                                <?php if ($settings_id): ?>
                                    <div id="<?php echo esc_attr($settings_id); ?>" class="<?php echo esc_attr(implode(' ', $settings_classes)); ?>">
                                        <?php $render_columns($device, $config['columns'], $config['name_prefix']); ?>
                                    </div>
                                <?php else: ?>
                                    <?php $render_columns($device, $config['columns'], $config['name_prefix']); ?>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php
                    if (!class_exists(LSDADDACF::class) && !class_exists(\LSDPACACF\Base::class)) $optional_addons[] = ['acf', esc_html__('ACF Fields', 'listdom')];
                    if (!class_exists(\LSDPACCMP\Base::class)) $optional_addons[] = ['compare', esc_html__('Compare Rate', 'listdom')];
                    if (!class_exists(\LSDPACFAV\Base::class)) $optional_addons[] = ['favorites', esc_html__('Favorite Icon', 'listdom')];
                    if (!class_exists(\LSDPACCLM\Base::class)) $optional_addons[] = ['claim', esc_html__('Claim Status', 'listdom')];
                    if (!class_exists(LSDADDREV::class) && !class_exists(\LSDPACREV\Base::class)) $optional_addons[] = ['reviews', esc_html__('Reviews Rate', 'listdom')];
                    ?>

                    <?php if (count($optional_addons)): ?>
                        <div class="lsd-alert-no-my">
                            <?php echo LSD_Base::alert(LSD_Base::optionalAddonsMessage($optional_addons),'warning'); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
