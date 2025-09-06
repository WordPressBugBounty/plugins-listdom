<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_PTypes_Shortcode $this */
/** @var WP_Post $post */

// Search Options
$search = get_post_meta($post->ID, 'lsd_search', true);

// Searchable
$searchable = $search['searchable'] ?? 1;
?>
<div class="lsd-settings-group-wrapper">
    <div class="lsd-settings-fields-wrapper">
        <div class="lsd-admin-section-heading">
            <h3 class="lsd-my-0 lsd-admin-title"><?php echo esc_html__("Search", 'listdom'); ?></h3>
            <p class="lsd-admin-description lsd-m-0"><?php echo esc_html__("Add a search form and define its position and behavior.", 'listdom'); ?> </p>
        </div>

        <div class="lsd-form-row lsd-flex-align-items-center lsd-searchable">
            <div class="lsd-col-3">
                <?php echo LSD_Form::label([
                    'class' => 'lsd-fields-label',
                    'title' => esc_html__('Searchable', 'listdom'),
                    'for' => 'lsd_search_searchable',
                ]); ?>
            </div>
            <div class="lsd-col-7">
                <?php echo LSD_Form::switcher([
                    'id' => 'lsd_search_searchable',
                    'name' => 'lsd[search][searchable]',
                    'value' => $searchable,
                    'toggle' => '.lsd_search_searchable_options',
                    'toggle2' => '#lsd_search_non_searchable'
                ]); ?>
            </div>
            <div class="<?php echo !$searchable ? 'lsd-util-hide' : ''; ?> lsd_search_searchable_options">
                <p class="lsd-mb-0 lsd-mt-2 lsd-admin-description-tiny"><?php esc_html_e("Enable this option to make it searchable and filterable through Listdom’s search and filter forms.", 'listdom'); ?></p>
            </div>
            <div class="<?php echo $searchable ? 'lsd-util-hide' : ''; ?>" id="lsd_search_non_searchable">
                <p class="lsd-mb-0 lsd-mt-2 lsd-admin-description-tiny"><?php esc_html_e("Non-searchable shortcodes display fixed results that do not change based on search queries.", 'listdom'); ?></p>
            </div>
        </div>

        <div id="lsd_metabox_search" class="lsd-metabox lsd-metabox-search lsd-flex lsd-flex-col lsd-flex-items-stretch lsd-gap-3 lsd-pt-2">

            <div class="lsd-flex lsd-flex-col lsd-flex-items-stretch lsd-gap-3 lsd-pt-2 <?php echo $searchable ? '' : 'lsd-util-hide'; ?> lsd_search_searchable_options">
                <div class="lsd-form-row">
                    <div class="lsd-col-3">
                        <?php echo LSD_Form::label([
                            'class' => 'lsd-fields-label',
                            'title' => esc_html__('Search Form', 'listdom'),
                            'for' => 'lsd_search_shortcode',
                        ]); ?>
                    </div>

                    <div class="lsd-col-7">
                        <?php echo LSD_Form::searches([
                            'class' => 'lsd-admin-input',
                            'id' => 'lsd_search_shortcode',
                            'name' => 'lsd[search][shortcode]',
                            'show_empty' => true,
                            'value' => $search['shortcode'] ?? ''
                        ]); ?>
                        <p class="lsd-admin-description-tiny lsd-mb-0 lsd-mt-2"><?php esc_html_e("Add a search form to this shortcode. It is disabled by default.", 'listdom'); ?></p>
                    </div>
                </div>
                <div class="lsd-form-row">
                    <div class="lsd-col-3">
                        <?php echo LSD_Form::label([
                            'class' => 'lsd-fields-label',
                            'title' => esc_html__('Search Form Position', 'listdom'),
                            'for' => 'lsd_search_position',
                        ]); ?>
                    </div>

                    <div class="lsd-col-7">
                        <?php echo LSD_Form::select([
                            'class' => 'lsd-admin-input',
                            'id' => 'lsd_search_position',
                            'name' => 'lsd[search][position]',
                            'options' => [
                                'top' => esc_html__('Show on top', 'listdom'),
                                'bottom' => esc_html__('Show on bottom', 'listdom'),
                                'left' => esc_html__('Show on left', 'listdom'),
                                'right' => esc_html__('Show on right', 'listdom'),
                                'before_listings' => esc_html__('Show before the listings', 'listdom')
                            ],
                            'value' => $search['position'] ?? 'top'
                        ]); ?>
                    </div>
                </div>

                <?php do_action('lsd_shortcode_search_metabox_end', $post, $search); ?>
            </div>
        </div>
    </div>
</div>

