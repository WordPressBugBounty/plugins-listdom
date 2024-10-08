<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_PTypes_Shortcode $this */
/** @var array $options */

$table = $options['table'] ?? [];

// Fields
$fields = new LSD_Fields();

$fields_data = $fields->get();
$titles = $fields->titles();
$columns = isset($table['columns']) && is_array($table['columns']) && count($table['columns']) ? $table['columns'] : $fields_data;

$missAddonMessages = [];

foreach ($fields_data as $key => $field) if (!isset($columns[$key])) $columns[$key] = $field;
foreach ($columns as $key => $row) if (!isset($fields_data[$key])) unset($columns[$key]);
?>
<div class="lsd-form-row lsd-form-row-separator"></div>
<div class="lsd-form-row">
    <div class="lsd-col-2"></div>
    <div class="lsd-col-10">
        <p class="description"><?php echo sprintf(esc_html__("Using the %s skin, you can show your desired directories and listings in a clean table. It doesn't show any map.", 'listdom'), '<strong>' . esc_html__('Table', 'listdom') . '</strong>'); ?></p>
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

<?php if ($this->isPro()): ?>
<div class="lsd-form-row">
    <div class="lsd-col-2"><?php echo LSD_Form::label([
        'title' => esc_html__('Listing Link', 'listdom'),
        'for' => 'lsd_display_options_skin_table_listing_link',
    ]); ?></div>
    <div class="lsd-col-6">
        <?php echo LSD_Form::select([
            'id' => 'lsd_display_options_skin_table_listing_link',
            'name' => 'lsd[display][table][listing_link]',
            'value' => $table['listing_link'] ?? 'normal',
            'options' => LSD_Base::get_listing_link_methods(),
        ]); ?>
        <p class="description"><?php esc_html_e("Link to listing detail page.", 'listdom'); ?></p>
    </div>
</div>
<?php else: ?>
<div class="lsd-form-row">
    <div class="lsd-col-2"></div>
    <div class="lsd-col-6">
        <p class="lsd-alert lsd-warning lsd-mt-0"><?php echo LSD_Base::missFeatureMessage(esc_html__('Listing Link', 'listdom')); ?></p>
    </div>
</div>
<?php endif; ?>

<div class="lsd-form-group lsd-py-5 <?php echo isset($table['style']) && $table['style'] ? '' : 'lsd-util-hide'; ?>" id="lsd_display_options_style">
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
                if (LSD_Base::isLite()) $missAddonMessages[] = LSD_Base::missFeatureMessage(esc_html__('Attributes', 'listdom'), true);

                if (!class_exists('LSDADDACF_Base')) $missAddonMessages[] = LSD_Base::missAddonMessage('ACF', esc_html__('ACF Fields', 'listdom'));
                else if (!class_exists('ACF')) $missAddonMessages[] = LSDADDACF_Base::get_acf_message();

                if (!class_exists('LSDADDCMP_Base')) $missAddonMessages[] = LSD_Base::missAddonMessage('Compare', esc_html__('Compare icon', 'listdom'));
                if (!class_exists('LSDADDFAV_Base')) $missAddonMessages[] = LSD_Base::missAddonMessage('Favorite', esc_html__('Favorite icon', 'listdom'));
                if (!class_exists('LSDADDCLM_Base')) $missAddonMessages[] = LSD_Base::missAddonMessage('Claim', esc_html__('Is claimed', 'listdom'));
                if (!class_exists('LSDADDREV_Base')) $missAddonMessages[] = LSD_Base::missAddonMessage('Reviews', esc_html__('Reviews Rate', 'listdom'));
            ?>

            <div class="lsd-addon-alert lsd-mt-4">
                <?php foreach ($missAddonMessages as $alert) echo LSD_Base::alert($alert, 'warning'); ?>
            </div>
        </div>
    </div>
</div>
