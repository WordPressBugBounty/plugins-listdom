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
$columns = isset($table['columns']) && is_array($table['columns']) && count($table['columns']) ? $table['columns'] : $fields_data;

$optional_addons = [];

foreach ($fields_data as $key => $field) if (!isset($columns[$key])) $columns[$key] = $field;
foreach ($columns as $key => $row) if (!isset($fields_data[$key])) unset($columns[$key]);
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
                'value' => $table['style'] ?? 'style1',
                'attributes' => [
                    'data-parent' => '#lsd_skin_display_options_table',
                ],
            ]); ?>
        </div>
    </div>

    <div class="lsd-settings-fields-wrapper">
        <div id="lsd_display_options_style">
            <div class="lsd-row">
                <div class="lsd-col-12">
                    <div class="lsd-admin-section-heading">
                        <h3 class="lsd-admin-title lsd-m-0"><?php echo esc_html__("Rows Display Options", 'listdom'); ?></h3>
                        <p class="lsd-admin-description lsd-m-0"><?php echo esc_html__("You can easily customize the table columns and rearrange items by using the drag-and-drop feature.", 'listdom'); ?> </p>
                    </div>
                    <div class="lsd-sortable lsd-display-options-table-columns">
                        <?php foreach ($columns as $key => $column): ?>
                            <?php
                            if ($key === '') continue;

                            $label = $column['label'] ?? $titles[$key];
                            $enabled = $column['enabled'] ?? 0;
                            $width = $column['width'] ?? '';
                            ?>
                            <div class="lsd-form-row lsd-cursor-move">
                                <div class="lsd-col-6 lsd-flex lsd-gap-3 lsd-flex-align-items-center">
                                    <i class="lsd-icon fas fa-arrows-alt lsd-handler lsd-gray-badge"></i>
                                    <?php echo LSD_Form::text([
                                        'class' => 'lsd-admin-input',
                                        'id' => 'lsd_display_options_skin_table_' . esc_attr($key) . '_label',
                                        'name' => 'lsd[display][table][columns][' . esc_attr($key) . '][label]',
                                        'placeholder' => $titles[$key] ?? '',
                                        'value' => esc_attr($label),
                                    ]); ?>
                                </div>
                                <div class="lsd-col-6 lsd-flex lsd-gap-3 lsd-flex-align-items-center">
                                    <?php echo LSD_Form::switcher([
                                        'id' => 'lsd_display_options_skin_table_' . esc_attr($key) . '_enabled',
                                        'name' => 'lsd[display][table][columns][' . esc_attr($key) . '][enabled]',
                                        'value' => esc_attr($enabled),
                                        'toggle' => '.lsd-display-options-skin-table-'.esc_attr($key),
                                    ]); ?>
                                    <div class="lsd-tooltip lsd-display-options-skin-table-<?php echo esc_attr($key); ?> <?php echo $enabled ? '' : 'lsd-util-hide'; ?>" data-lsd-tooltip="<?php esc_attr_e('Column Width', 'listdom'); ?>">
                                        <?php echo LSD_Form::number([
                                            'class' => 'lsd-admin-input',
                                            'id' => 'lsd_display_options_skin_table_' . esc_attr($key) . '_width',
                                            'name' => 'lsd[display][table][columns][' . esc_attr($key) . '][width]',
                                            'placeholder' => 150,
                                            'value' => esc_attr($width),
                                            'attributes' => [
                                                'min' => 0,
                                                'step' => 1
                                            ]
                                        ]); ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <?php
                    if (!class_exists(LSDADDACF::class) && !class_exists(\LSDPACACF\Base::class)) $optional_addons[] = ['acf', esc_html__('ACF Fields', 'listdom')];
                    if (!class_exists(LSDADDCMP::class) && !class_exists(\LSDPACCMP\Base::class)) $optional_addons[] = ['compare', esc_html__('Compare Rate', 'listdom')];
                    if (!class_exists(LSDADDFAV::class) && !class_exists(\LSDPACFAV\Base::class)) $optional_addons[] = ['favorites', esc_html__('Favorite Icon', 'listdom')];
                    if (!class_exists(LSDADDCLM::class) && !class_exists(\LSDPACCLM\Base::class)) $optional_addons[] = ['claim', esc_html__('Claim Status', 'listdom')];
                    if (!class_exists(LSDADDREV::class) && !class_exists(\LSDPACREV\Base::class)) $optional_addons[] = ['reviews', esc_html__('Reviews Rate', 'listdom')];
                    ?>

                    <?php if (count($optional_addons)): ?>
                        <div class="lsd-alert-no-my lsd-mt-5">
                            <?php echo LSD_Base::alert(LSD_Base::optionalAddonsMessage($optional_addons),'warning'); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

