<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_PTypes_Shortcode $this */
/** @var WP_Post $post */

// Number of Users
$number_of_users = count_users()['total_users'];

// Filter Options
$options = get_post_meta($post->ID, 'lsd_filter', true);
$exclude = get_post_meta($post->ID, 'lsd_exclude', true);

// Attributes
$attributes = LSD_Main::get_attributes_details();

// Walker
$walker = new LSD_Walker_Taxonomy();
?>
<div class="lsd-metabox lsd-metabox-filter-options">
    <p class="description lsd-m-4"><?php esc_html_e('Use the options below to filter the listings you want to display with this shortcode.', 'listdom'); ?></p>

    <?php if (!class_exists(LSDADDAPS::class) && !class_exists(\LSDPACAPS\Base::class)): ?>
    <div class="lsd-m-4"><?php echo LSD_Base::alert(sprintf(esc_html__('Did you know that with the %s add-on, you can customize the matching logic for taxonomies?', 'listdom'), '<strong>'.esc_html__('Advanced Portal Search', 'listdom').'</strong>')); ?></div>
    <?php endif; ?>

    <div class="lsd-accordion-title lsd-accordion-active">
        <div class="lsd-flex lsd-flex-row lsd-py-2">
            <h3><?php esc_html_e('Categories', 'listdom'); ?></h3>
            <div class="lsd-accordion-icons">
                <i class="lsd-icon fa fa-plus"></i>
                <i class="lsd-icon fa fa-minus"></i>
            </div>
        </div>
    </div>
    <div class="lsd-accordion-panel lsd-accordion-open">
        <div class="lsd-row">
            <div class="lsd-col-12">
                <ul class="lsd-tab-switcher lsd-sub-tabs lsd-flex lsd-gap-3" data-for=".lsd-tab-switcher-content-category">
                    <li data-tab="include" class="lsd-sub-tabs-active"><a href="#"><?php esc_html_e('Include', 'listdom'); ?></a></li>
                    <li data-tab="exclude" class=""><a href="#"><?php esc_html_e('Exclude', 'listdom'); ?></a></li>
                </ul>

                <div class="lsd-tab-switcher-content lsd-tab-switcher-content-category lsd-tab-switcher-content-active" id="lsd-tab-switcher-include-content">
                    <p class="description lsd-mt-4 lsd-mb-2"><?php esc_html_e("If you don't want to filter the listings by category, simply leave the options unselected.", 'listdom'); ?></p>
                    <div class="lsd-categories">
                        <?php echo LSD_Form::taxonomy(LSD_Base::TAX_CATEGORY, [
                            'id' => 'lsd_include_' . LSD_Base::TAX_CATEGORY,
                            'value' => $options[LSD_Base::TAX_CATEGORY] ?? [],
                            'name' => 'lsd[filter][' . LSD_Base::TAX_CATEGORY . '][]',
                            'attributes' => [
                                'multiple' => 'multiple',
                            ]
                        ]); ?>
                    </div>
                </div>
                <div class="lsd-tab-switcher-content lsd-tab-switcher-content-category lsd-alert-no-mb" id="lsd-tab-switcher-exclude-content">
                    <?php if (LSD_Base::isPro()): ?>
                    <p class="description lsd-mt-4 lsd-mb-2"><?php esc_html_e("If you add a category it will be excluded from shortcode results.", 'listdom'); ?></p>
                    <div class="lsd-categories">
                        <?php echo LSD_Form::taxonomy(LSD_Base::TAX_CATEGORY, [
                            'id' => 'lsd_exclude_'.LSD_Base::TAX_CATEGORY,
                            'value' => $exclude[LSD_Base::TAX_CATEGORY] ?? [],
                            'name' => 'lsd[exclude]['.LSD_Base::TAX_CATEGORY.'][]',
                            'attributes' =>[
                                'multiple' => 'multiple',
                            ]
                        ]); ?>
                    </div>
                    <?php else: echo LSD_Base::alert(LSD_Base::missFeatureMessage(esc_html__('Exclusion Filter', 'listdom')), 'warning'); ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <div class="lsd-accordion-title">
        <div class="lsd-flex lsd-flex-row lsd-py-2">
            <h3><?php esc_html_e('Locations', 'listdom'); ?></h3>
            <div class="lsd-accordion-icons">
                <i class="lsd-icon fa fa-plus"></i>
                <i class="lsd-icon fa fa-minus"></i>
            </div>
        </div>
    </div>
    <div class="lsd-accordion-panel">
        <div class="lsd-row">
            <div class="lsd-col-12">
                <ul class="lsd-tab-switcher lsd-sub-tabs lsd-flex lsd-gap-3" data-for=".lsd-tab-switcher-content-location">
                    <li data-tab="include-locations" class="lsd-sub-tabs-active"><a href="#"><?php esc_html_e('Include', 'listdom'); ?></a></li>
                    <li data-tab="exclude-locations" class=""><a href="#"><?php esc_html_e('Exclude', 'listdom'); ?></a></li>
                </ul>

                <div class="lsd-tab-switcher-content lsd-tab-switcher-content-location lsd-tab-switcher-content-active" id="lsd-tab-switcher-include-locations-content">
                    <p class="description lsd-mt-4 lsd-mb-2"><?php esc_html_e("If you add a location it will be included in the shortcode results. Leave it empty to include all of them.", 'listdom'); ?></p>
                    <div class="lsd-locations">
                        <?php echo LSD_Form::taxonomy(LSD_Base::TAX_LOCATION, [
                            'id' => 'lsd_include_'.LSD_Base::TAX_LOCATION,
                            'value' => $options[LSD_Base::TAX_LOCATION] ?? [],
                            'name' => 'lsd[filter]['.LSD_Base::TAX_LOCATION.'][]',
                            'attributes' =>[
                                'multiple' => 'multiple',
                            ]
                        ]); ?>
                    </div>
                </div>
                <div class="lsd-tab-switcher-content lsd-tab-switcher-content-location lsd-alert-no-mb" id="lsd-tab-switcher-exclude-locations-content">
                    <?php if(LSD_Base::isPro()): ?>
                    <p class="description lsd-mt-4 lsd-mb-2"><?php esc_html_e("If you add a location it will be excluded from shortcode results.", 'listdom'); ?></p>
                    <div class="lsd-locations">
                        <?php echo LSD_Form::taxonomy(LSD_Base::TAX_LOCATION, [
                            'id' => 'lsd_exclude_'.LSD_Base::TAX_LOCATION,
                            'value' => $exclude[LSD_Base::TAX_LOCATION] ?? [],
                            'name' => 'lsd[exclude]['.LSD_Base::TAX_LOCATION.'][]',
                            'attributes' =>[
                                'multiple' => 'multiple',
                            ]
                        ]); ?>
                    </div>
                    <?php else: echo LSD_Base::alert(LSD_Base::missFeatureMessage(esc_html__('Exclusion Filter', 'listdom')), 'warning'); ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <div class="lsd-accordion-title">
        <div class="lsd-flex lsd-flex-row lsd-py-2">
            <h3><?php esc_html_e('Tags', 'listdom'); ?></h3>
            <div class="lsd-accordion-icons">
                <i class="lsd-icon fa fa-plus"></i>
                <i class="lsd-icon fa fa-minus"></i>
            </div>
        </div>
    </div>
    <div class="lsd-accordion-panel">
        <div class="lsd-form-row">
            <div class="lsd-col-12">
                <ul class="lsd-tab-switcher lsd-sub-tabs lsd-flex lsd-gap-3" data-for=".lsd-tab-switcher-content-tag">
                    <li data-tab="include-tags" class="lsd-sub-tabs-active"><a href="#"><?php esc_html_e('Include', 'listdom'); ?></a></li>
                    <li data-tab="exclude-tags" class=""><a href="#"><?php esc_html_e('Exclude', 'listdom'); ?></a></li>
                </ul>

                <div class="lsd-tab-switcher-content lsd-tab-switcher-content-tag lsd-tab-switcher-content-active" id="lsd-tab-switcher-include-tags-content">
                    <p class="description lsd-mt-4 lsd-mb-2"><?php esc_html_e("If you add a tag it will be included in the shortcode results. Leave it empty to include all of them.", 'listdom'); ?></p>
                    <div class="lsd-tags">
                        <?php echo LSD_Form::taxonomy(LSD_Base::TAX_TAG, [
                            'id' => 'lsd_include_' . LSD_Base::TAX_TAG,
                            'value' => $options[LSD_Base::TAX_TAG] ?? [],
                            'name' => 'lsd[filter][' . LSD_Base::TAX_TAG . '][]',
                            'attributes' => [
                                'multiple' => 'multiple',
                            ]
                        ]); ?>
                    </div>
                </div>
                <div class="lsd-tab-switcher-content lsd-tab-switcher-content-tag lsd-alert-no-mb" id="lsd-tab-switcher-exclude-tags-content">
                    <?php if (LSD_Base::isPro()): ?>
                        <p class="description lsd-mt-4 lsd-mb-2"><?php esc_html_e("If you add a tag it will be excluded from shortcode results.", 'listdom'); ?></p>
                        <div class="lsd-tags">
                            <?php echo LSD_Form::taxonomy(LSD_Base::TAX_TAG, [
                                'id' => 'lsd_exclude_' . LSD_Base::TAX_TAG,
                                'value' => $exclude[LSD_Base::TAX_TAG] ?? [],
                                'name' => 'lsd[exclude][' . LSD_Base::TAX_TAG . '][]',
                                'attributes' => [
                                    'multiple' => 'multiple',
                                ]
                            ]); ?>
                        </div>
                    <?php else: echo LSD_Base::alert(LSD_Base::missFeatureMessage(esc_html__('Exclusion Filter', 'listdom')), 'warning'); ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <div class="lsd-accordion-title">
        <div class="lsd-flex lsd-flex-row lsd-py-2">
            <h3><?php esc_html_e('Features', 'listdom'); ?></h3>
            <div class="lsd-accordion-icons">
                <i class="lsd-icon fa fa-plus"></i>
                <i class="lsd-icon fa fa-minus"></i>
            </div>
        </div>
    </div>
    <div class="lsd-accordion-panel">
        <div class="lsd-row">
            <div class="lsd-col-12">
                <ul class="lsd-tab-switcher lsd-sub-tabs lsd-flex lsd-gap-3" data-for=".lsd-tab-switcher-content-feature">
                    <li data-tab="include-features" class="lsd-sub-tabs-active"><a href="#"><?php esc_html_e('Include', 'listdom'); ?></a></li>
                    <li data-tab="exclude-features" class=""><a href="#"><?php esc_html_e('Exclude', 'listdom'); ?></a></li>
                </ul>
                <div class="lsd-tab-switcher-content lsd-tab-switcher-content-feature lsd-tab-switcher-content-active" id="lsd-tab-switcher-include-features-content">
                    <p class="description lsd-mt-4 lsd-mb-2"><?php esc_html_e("If you add a feature it will be included in the shortcode results. Leave it empty to include all of them.", 'listdom'); ?></p>
                    <div class="lsd-features">
                        <?php echo LSD_Form::taxonomy(LSD_Base::TAX_FEATURE, [
                            'id' => 'lsd_include_' . LSD_Base::TAX_FEATURE,
                            'value' => $options[LSD_Base::TAX_FEATURE] ?? [],
                            'name' => 'lsd[filter][' . LSD_Base::TAX_FEATURE . '][]',
                            'attributes' => [
                                'multiple' => 'multiple',
                            ]
                        ]); ?>
                    </div>
                </div>
                <div class="lsd-tab-switcher-content lsd-tab-switcher-content-feature lsd-alert-no-mb" id="lsd-tab-switcher-exclude-features-content">
                <?php if (LSD_Base::isPro()): ?>
                    <p class="description lsd-mt-4 lsd-mb-2"><?php esc_html_e("If you add a feature it will be excluded from shortcode results.", 'listdom'); ?></p>
                    <div class="lsd-features">
                        <?php echo LSD_Form::taxonomy(LSD_Base::TAX_FEATURE, [
                            'id' => 'lsd_exclude_' . LSD_Base::TAX_FEATURE,
                            'value' => $exclude[LSD_Base::TAX_FEATURE] ?? [],
                            'name' => 'lsd[exclude][' . LSD_Base::TAX_FEATURE . '][]',
                            'attributes' => [
                                'multiple' => 'multiple',
                            ]
                        ]); ?>
                    </div>
                    <?php else: echo LSD_Base::alert(LSD_Base::missFeatureMessage(esc_html__('Exclusion Filter', 'listdom')), 'warning'); ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <div class="lsd-accordion-title">
        <div class="lsd-flex lsd-flex-row lsd-py-2">
            <h3><?php esc_html_e('Labels', 'listdom'); ?></h3>
            <div class="lsd-accordion-icons">
                <i class="lsd-icon fa fa-plus"></i>
                <i class="lsd-icon fa fa-minus"></i>
            </div>
        </div>
    </div>
    <div class="lsd-accordion-panel">
        <div class="lsd-row">
            <div class="lsd-col-12">
                <ul class="lsd-tab-switcher lsd-sub-tabs lsd-flex lsd-gap-3" data-for=".lsd-tab-switcher-content-label">
                    <li data-tab="include-labels" class="lsd-sub-tabs-active"><a href="#"><?php esc_html_e('Include', 'listdom'); ?></a></li>
                    <li data-tab="exclude-labels" class=""><a href="#"><?php esc_html_e('Exclude', 'listdom'); ?></a></li>
                </ul>
                <div class="lsd-tab-switcher-content lsd-tab-switcher-content-label lsd-tab-switcher-content-active" id="lsd-tab-switcher-include-labels-content">
                    <p class="description lsd-mt-4 lsd-mb-2"><?php esc_html_e("If you add a label it will be included in the shortcode results. Leave it empty to include all of them.", 'listdom'); ?></p>
                    <div class="lsd-labels">
                        <?php echo LSD_Form::taxonomy(LSD_Base::TAX_LABEL, [
                            'id' => 'lsd_include_' . LSD_Base::TAX_LABEL,
                            'value' => $options[LSD_Base::TAX_LABEL] ?? [],
                            'name' => 'lsd[filter][' . LSD_Base::TAX_LABEL . '][]',
                            'attributes' => [
                                'multiple' => 'multiple',
                            ]
                        ]); ?>
                    </div>
                </div>
                <div class="lsd-tab-switcher-content lsd-tab-switcher-content-label lsd-alert-no-mb" id="lsd-tab-switcher-exclude-labels-content">
                    <?php if (LSD_Base::isPro()): ?>
                    <p class="description lsd-mt-4 lsd-mb-2"><?php esc_html_e("If you add a label it will be excluded from shortcode results.", 'listdom'); ?></p>
                    <div class="lsd-locations">
                        <?php echo LSD_Form::taxonomy(LSD_Base::TAX_LABEL, [
                            'id' => 'lsd_exclude_' . LSD_Base::TAX_LABEL,
                            'value' => $exclude[LSD_Base::TAX_LABEL] ?? [],
                            'name' => 'lsd[exclude][' . LSD_Base::TAX_LABEL . '][]',
                            'attributes' => [
                                'multiple' => 'multiple',
                            ]
                        ]); ?>
                    </div>
                    <?php else: echo LSD_Base::alert(LSD_Base::missFeatureMessage(esc_html__('Exclusion Filter', 'listdom')), 'warning'); ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="lsd-accordion-title">
        <div class="lsd-flex lsd-flex-row lsd-py-2">
            <h3><?php esc_html_e('Custom Fields', 'listdom'); ?></h3>
            <div class="lsd-accordion-icons">
                <i class="lsd-icon fa fa-plus"></i>
                <i class="lsd-icon fa fa-minus"></i>
            </div>
        </div>
    </div>
    <div class="lsd-accordion-panel">
        <div class="lsd-row">
            <div class="lsd-col-12">
                <p class="description lsd-mt-0 lsd-mb-4"><?php esc_html_e("If you want to filter listings based on custom fields, fill in the following fields. Otherwise, leave them empty to skip filtering.", 'listdom'); ?></p>
                <div class="lsd-attributes">
                    <?php if (count($attributes)): ?>
                        <?php foreach ($attributes as $attr): ?>
                            <div class="lsd-form-row">
                                <div class="lsd-col-2 lsd-text-right"><?php echo LSD_Form::label([
                                    'title' => esc_html($attr['name']),
                                    'for' => 'lsd_attribute_'.esc_attr($attr['id']),
                                ]); ?></div>

                                <div class="lsd-col-6">
                                    <?php if ($attr['field_type'] === 'dropdown'): ?>
                                        <?php
                                            echo LSD_Form::select([
                                                'id' => 'lsd_attribute_'.esc_attr($attr['id']),
                                                'name' => 'lsd[filter][attributes][' . esc_attr($attr['id']) .'-in][]',
                                                'value' => $options['attributes'][$attr['id'] . '-in'] ?? [],
                                                'options' => $attr['values'],
                                                'attributes' => [
                                                    'multiple' => true,
                                                ]
                                            ]);
                                        ?>
                                    <?php elseif ($attr['field_type'] === 'number'): ?>
                                        <div class="lsd-flex lsd-gap-3 lsd-mm-input">
                                            <?php
                                                $min = $options[LSD_Base::TAX_ATTRIBUTE][$attr['id'] . '-bt-min'] ?? '';
                                                $max = $options[LSD_Base::TAX_ATTRIBUTE][$attr['id'] . '-bt-max'] ?? '';

                                                echo LSD_Form::number([
                                                    'name' => 'lsd[filter]['.LSD_Base::TAX_ATTRIBUTE .'][' . esc_attr($attr['id']) .'-bt-min]',
                                                    'value' => $min,
                                                    'placeholder' => esc_html__('Min number', 'listdom'),
                                                    'id' => 'lsd_attribute_'.esc_attr($attr['id'])  . '-min',
                                                ]);

                                                echo LSD_Form::number([
                                                    'name' => 'lsd[filter]['.LSD_Base::TAX_ATTRIBUTE .'][' . esc_attr($attr['id']) .'-bt-max]',
                                                    'value' => $max,
                                                    'placeholder' => esc_html__('Max number', 'listdom'),
                                                    'id' => 'lsd_attribute_'.esc_attr($attr['id'])  . '-max',
                                                ]);

                                                echo LSD_Form::hidden([
                                                    'name' => 'lsd[filter][attributes][' . esc_attr($attr['id']) .'-bt]',
                                                    'id' => 'lsd_attribute_'.esc_attr($attr['id']) . '-hidden',
                                                    'value' => $min && $max ? $min.':'.$max : '',
                                                ]);
                                            ?>
                                            <script>
                                            jQuery(document).ready(function()
                                            {
                                                const minInput = jQuery('#lsd_attribute_<?php echo esc_attr($attr['id']); ?>-min');
                                                const maxInput = jQuery('#lsd_attribute_<?php echo esc_attr($attr['id']); ?>-max');
                                                const hiddenInput = jQuery('#lsd_attribute_<?php echo esc_attr($attr['id']); ?>-hidden');

                                                minInput.add(maxInput).on('input', () => {
                                                    const minValue = minInput.val().trim();
                                                    const maxValue = maxInput.val().trim();

                                                    hiddenInput.val(minValue && maxValue ? `${minValue}:${maxValue}` : '');
                                                });
                                            });
                                            </script>
                                        </div>
                                    <?php else: ?>
                                        <?php
                                            echo LSD_Form::text([
                                                'id' => 'lsd_attribute_'.esc_attr($attr['id']),
                                                'name' => 'lsd[filter][attributes][' . esc_attr($attr['id']) .'-lk]',
                                                'value' => $options['attributes'][$attr['id'] . '-lk'] ?? '',
                                                'placeholder' => sprintf(esc_html__('Enter %s', 'listdom'), esc_html($attr['name'])),
                                            ]);
                                        ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="lsd-accordion-title">
        <div class="lsd-flex lsd-flex-row lsd-py-2">
            <h3><?php esc_html_e('Authors', 'listdom'); ?></h3>
            <div class="lsd-accordion-icons">
                <i class="lsd-icon fa fa-plus"></i>
                <i class="lsd-icon fa fa-minus"></i>
            </div>
        </div>
    </div>
    <div class="lsd-accordion-panel">
        <div class="lsd-row">
            <div class="lsd-col-12">
                <ul class="lsd-tab-switcher lsd-sub-tabs lsd-flex lsd-gap-3" data-for=".lsd-tab-switcher-content-authors">
                    <li data-tab="include-authors" class="lsd-sub-tabs-active"><a href="#"><?php esc_html_e('Include', 'listdom'); ?></a></li>
                    <li data-tab="exclude-authors"><a href="#"><?php esc_html_e('Exclude', 'listdom'); ?></a></li>
                </ul>

                <div class="lsd-tab-switcher-content lsd-tab-switcher-content-authors lsd-tab-switcher-content-active" id="lsd-tab-switcher-include-authors-content">
                    <p class="description lsd-mt-4 lsd-mb-2"><?php esc_html_e("Don't select any option if you don't want to filter the listings by authors.", 'listdom'); ?></p>
                    <?php echo LSD_Form::autosuggest([
                        'source' => 'users',
                        'name' => 'lsd[filter][authors]',
                        'id' => 'lsd_filter_author',
                        'input_id' => 'in_lsd_author',
                        'suggestions' => 'lsd_filter_author_suggestions',
                        'values' => $options['authors'] ?? [],
                        'placeholder' => esc_html__("Enter at least 3 characters of the author's name ...", 'listdom'),
                        'description' => esc_html__('You can select multiple authors.', 'listdom'),
                        'description_class' => 'lsd-mb-0',
                    ]); ?>
                </div>

                <div class="lsd-tab-switcher-content lsd-tab-switcher-content-authors lsd-alert-no-mb" id="lsd-tab-switcher-exclude-authors-content">
                    <?php if (LSD_Base::isPro()): ?>
                        <p class="description lsd-mt-4 lsd-mb-2"><?php esc_html_e("If you add an author, their listings will be excluded from the results.", 'listdom'); ?></p>
                        <?php echo LSD_Form::autosuggest([
                            'source' => 'users',
                            'name' => 'lsd[exclude][authors]',
                            'id' => 'lsd_exclude_author',
                            'input_id' => 'ex_lsd_author',
                            'suggestions' => 'lsd_exclude_author_suggestions',
                            'values' => $exclude['authors'] ?? [],
                            'placeholder' => esc_html__("Enter at least 3 characters of the author's name ...", 'listdom'),
                            'description' => esc_html__('You can select multiple authors.', 'listdom'),
                            'description_class' => 'lsd-mb-0',
                        ]); ?>
                    <?php else: ?>
                        <?php echo LSD_Base::alert(LSD_Base::missFeatureMessage(esc_html__('Exclusion Filter', 'listdom')), 'warning'); ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php
        // Action for Third Party Plugins
        do_action('lsd_shortcode_filter_options', $options);
    ?>
</div>
