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
<div class="lsd-form-row">
    <div class="lsd-col-2"></div>
    <div class="lsd-col-10">
        <p class="description"><?php echo sprintf(esc_html__("With the %s skin, you can display your selected directories and listings in a clean table format. This skin does not include a map.", 'listdom'), '<strong>' . esc_html__('Table', 'listdom') . '</strong>'); ?></p>
    </div>
</div>
<div class="lsd-form-row">
    <div class="lsd-col-2"><?php echo LSD_Form::label([
        'title' => esc_html__('Style', 'listdom'),
        'for' => 'lsd_display_options_skin_table_style',
    ]); ?></div>
    <div class="lsd-col-6">
        <?php echo LSD_Form::select([
            'id' => 'lsd_display_options_skin_table_style',
            'class' => 'lsd-display-options-style-selector lsd-display-options-style-toggle',
            'name' => 'lsd[display][table][style]',
            'options' => LSD_Styles::table(),
            'value' => $table['style'] ?? 'style1',
            'attributes' => [
                'data-parent' => '#lsd_skin_display_options_table',
            ],
        ]); ?>
    </div>
</div>

<div class="lsd-form-row">
    <div class="lsd-col-2"><?php echo LSD_Form::label([
        'title' => esc_html__('Limit', 'listdom'),
        'for' => 'lsd_display_options_skin_table_limit',
    ]); ?></div>
    <div class="lsd-col-6">
        <?php echo LSD_Form::text([
            'id' => 'lsd_display_options_skin_table_limit',
            'name' => 'lsd[display][table][limit]',
            'value' => $table['limit'] ?? '12',
        ]); ?>
        <p class="description"><?php esc_html_e("Number of the Listings (table rows) per page", 'listdom'); ?></p>
    </div>
</div>
<div class="lsd-form-row">
    <div class="lsd-col-2"><?php echo LSD_Form::label([
        'title' => esc_html__('Pagination Method', 'listdom'),
        'for' => 'lsd_display_options_skin_table_pagination',
    ]); ?></div>
    <div class="lsd-col-6">
        <?php echo LSD_Form::select([
            'id' => 'lsd_display_options_skin_table_pagination',
            'name' => 'lsd[display][table][pagination]',
            'value' => $table['pagination'] ?? (isset($table['load_more']) && $table['load_more'] == 0 ? 'disabled' : 'loadmore'),
            'options' => [
                'loadmore' => esc_html__('Load More Button', 'listdom'),
                'scroll' => esc_html__('Infinite Scroll', 'listdom'),
                'disabled' => esc_html__('Disabled', 'listdom'),
            ],
        ]); ?>
        <p class="description"><?php esc_html_e('Choose how to load additional listings more than the default limit.', 'listdom'); ?></p>
    </div>
</div>

<?php $this->field_listing_link('table', $table); ?>

<div class="lsd-form-group lsd-py-5" id="lsd_display_options_style">
    <div class="lsd-row">
        <div class="lsd-col-2"></div>
        <div class="lsd-col-6">
            <h3 class="lsd-mb-0 lsd-mt-1"><?php echo esc_html__("Rows Display Options", 'listdom'); ?></h3>
            <p class="description lsd-mb-4"><?php echo esc_html__("You can easily customize the table columns and rearrange items by using the drag-and-drop feature.", 'listdom'); ?> </p>
            <div class="lsd-sortable">
                <?php foreach ($columns as $key => $column): ?>
                    <?php
                        $label = $column['label'] ?? $titles[$key];
                        $enabled = $column['enabled'];
                        if ($key === '') continue;
                    ?>
                    <div class="lsd-form-row lsd-cursor-move">
                        <div class="lsd-col-6 lsd-flex lsd-gap-3 lsd-flex-align-items-center">
                            <i class="lsd-icon fas fa-arrows-alt lsd-handler lsd-gray-badge"></i>
                            <?php echo LSD_Form::text([
                                'id' => 'lsd_display_options_skin_table_' . esc_attr($key),
                                'name' => 'lsd[display][table][columns][' . esc_attr($key) . '][label]',
                                'placeholder' => $titles[$key] ?? '',
                                'value' => esc_html($label),
                            ]); ?>
                        </div>
                        <div class="lsd-col-6">
                            <?php echo LSD_Form::switcher([
                                'id' => 'lsd_display_options_skin_table_' . esc_attr($key),
                                'name' => 'lsd[display][table][columns][' . esc_attr($key) . '][enabled]',
                                'value' => esc_attr($enabled),
                            ]); ?>
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
