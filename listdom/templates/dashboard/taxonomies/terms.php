<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Dashboard_Taxonomies_Terms $this */
/** @var array $args */

$settings = LSD_Options::settings();
$taxonomy = $args['taxonomy'] ?? LSD_Base::TAX_CATEGORY;

$mode = $settings['submission_term_builder_'. $taxonomy] ?? 'express';
if($mode === 'disabled') return;

$custom_fields = LSD_Dashboard_Taxonomies_Terms::taxonomy_fields($taxonomy);
$tax_name = ucfirst(str_replace('listdom-', '', $taxonomy));

$assets = new LSD_Assets();
$assets->footer('<script>
jQuery(document).ready(function($)
{
    $(".lsd-new-tax-wrapper").each(function()
    {
        let $wrapper = $(this);
        let tax = $wrapper.data("tax");
        let $form = $("#lsd_dashboard_new_term_" + tax);

        $form.listdomDashboardTaxForm({
            ajax_url: "' . admin_url('admin-ajax.php') . '",
            nonce: "' . wp_create_nonce('lsd_dashboard_new_term') . '"
        });
    });
});
</script>');
?>
<div data-tax="<?php echo esc_attr($taxonomy); ?>" class="lsd-new-tax-wrapper">
    <div class="lsd-new-tax-link">
        <?php
        echo sprintf(
            esc_html__('Select the %s or %s', 'listdom'),
            esc_html($tax_name),
            '<a href="#" id="lsd_show_create_taxonomy_form_' . esc_attr($taxonomy) . '">' . esc_html__('Add a new one', 'listdom') . '</a>'
        );
        ?>
    </div>
    <div class="lsd-dashboard-new-term-form lsd-modal" id="lsd_dashboard_new_term_<?php echo esc_attr($taxonomy); ?>">
        <div class="lsd-modal-content">
            <h3 class="lsd-tax-title lsd-fd-title"><?php echo sprintf(esc_html__('New %s', 'listdom') , $tax_name); ?></h3>
            <?php if ($mode === 'express'): ?>
                <div class="lsd-add-term-express">
                    <div class="lsd-new-tax-inputs">
                        <?php echo LSD_Form::label([
                            'title' => sprintf(esc_attr__('%s Name', 'listdom'), $tax_name),
                            'for' => 'lsd_express_term_name_'. $taxonomy,
                        ]); ?>
                        <?php echo LSD_Form::input([
                            'name' => 'term_name',
                            'id' => 'lsd_express_term_name_'. $taxonomy,
                            'class' => 'lsd_express_term_name',
                            'placeholder' => sprintf(esc_attr__('%s Name', 'listdom'), $tax_name),
                        ]);
                        ?>
                    </div>
                    <div class="lsd-flex lsd-flex-content-center lsd-mt-3">
                        <?php echo LSD_Form::submit([
                            'class' => 'lsd_add_express_term_btn',
                            'label' => sprintf(esc_html__('Add %s', 'listdom'), $tax_name),
                        ]); ?>
                    </div>
                    <div id="lsd_new_term_message_<?php echo esc_attr($taxonomy); ?>"></div>
                </div>
            <?php else: ?>
                <div class="lsd-add-term-detailed lsd-new-term-<?php echo esc_attr($taxonomy); ?>">
                        <div class="lsd-w-full lsd-new-tax-inputs">
                            <?php echo LSD_Form::label([
                                'title' => sprintf(esc_attr__('%s Name', 'listdom'), $tax_name),
                                'for' => 'lsd_detailed_term_name_'. $taxonomy,
                            ]); ?>
                            <?php echo LSD_Form::input([
                                'name' => 'term_name',
                                'id' => 'lsd_detailed_term_name_'. $taxonomy,
                                'class' => 'lsd_detailed_term_name',
                                'placeholder' => sprintf(esc_attr__('%s Name', 'listdom'), $tax_name),
                            ]); ?>
                        </div>
                        <?php if ($taxonomy !== LSD_Base::TAX_TAG && $taxonomy !== LSD_Base::TAX_FEATURE && $taxonomy !== LSD_Base::TAX_LABEL): ?>
                            <div class="lsd-w-full lsd-new-tax-inputs">
                                <?php echo LSD_Form::label([
                                    'title' => esc_html__('Parent', 'listdom') . ' ' . $tax_name,
                                    'for' => 'lsd_detailed_term_parent_' . $taxonomy,
                                ]); ?>
                                <?php echo LSD_Form::taxonomy($taxonomy , [
                                    'name' => 'term_parent',
                                    'id' => 'lsd_detailed_term_parent_' . $taxonomy,
                                    'class' => 'lsd_detailed_term_parent',
                                    'show_empty' => true,
                                ]); ?>
                            </div>
                        <?php endif; ?>
                    <div class="lsd-flex lsd-gap-3">
                        <?php if (in_array('color', $custom_fields)): ?>
                            <div class="lsd-w-full lsd-new-tax-inputs lsd-flex lsd-gap-2">
                                <?php echo LSD_Form::label([
                                    'title' => esc_attr__('Pick the color', 'listdom'),
                                    'for' => 'lsd_color_'  . $taxonomy,
                                ]); ?>
                                <?php echo LSD_Form::input([
                                    'name' => 'lsd_color',
                                    'id' => 'lsd_color_' . $taxonomy,
                                    'class' => 'lsd_color',
                                    'default' => '#1d7ed3',
                                    'value' => '#1d7ed3',
                                ], 'hidden'); ?>
                                <div class="lsd-color-picker"></div>
                                <input class="lsd-color-picker-input" type="text" disabled>
                            </div>
                        <?php endif; ?>
                        <?php if (in_array('icon', $custom_fields)): ?>
                            <div class="lsd-w-full lsd-new-tax-inputs">
                                <?php echo LSD_Form::label([
                                    'title' => esc_attr__('Select the icon', 'listdom'),
                                    'for' => 'lsd_icon_' . $taxonomy,
                                    'class' => 'lsd-m-0',
                                ]); ?>
                                <div>
                                    <?php echo LSD_Form::iconpicker([
                                        'name' => 'lsd_icon',
                                        'id' => 'lsd_icon_' . $taxonomy,
                                        'class' => 'lsd_icon lsd-iconpicker',
                                        'value' => '',
                                    ]); ?>
                                </div>
                            </div>
                        <?php endif; ?>

                    </div>
                    <div class="lsd-w-full lsd-new-tax-inputs">
                        <?php echo LSD_Form::label([
                            'title' => esc_html__('Description', 'listdom'),
                            'for' => 'lsd_detailed_term_description_'. $taxonomy,
                        ]); ?>
                        <?php echo LSD_Form::textarea([
                            'name' => 'term_description',
                            'id' => 'lsd_detailed_term_description_'. $taxonomy,
                            'placeholder' => esc_attr__('Description', 'listdom'),
                            'attributes' => [
                                'class' => 'lsd_detailed_term_description',
                            ]
                        ]); ?>
                    </div>
                    <div class="lsd-flex lsd-flex-content-center lsd-mt-3">
                        <?php echo LSD_Form::submit([
                            'class' => 'lsd_add_term_btn',
                            'label' => sprintf(esc_html__('Add %s', 'listdom'), $tax_name),
                        ]); ?>
                    </div>
                    <div id="lsd_new_term_message_<?php echo esc_attr($taxonomy); ?>" class="lsd-m-0"></div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
