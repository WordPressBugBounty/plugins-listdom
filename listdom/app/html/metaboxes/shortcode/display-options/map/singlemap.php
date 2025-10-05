<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_PTypes_Shortcode $this */
/** @var array $options */

$singlemap = $options['singlemap'] ?? [];
$mapsearch = $singlemap['mapsearch'] ?? '1';
?>
<div class="lsd-settings-group-wrapper">
    <div class="lsd-settings-fields-wrapper">
        <h3 class="lsd-my-0 lsd-admin-title"><?php echo esc_html__("Map Structure", 'listdom'); ?></h3>

        <div class="lsd-form-row">
            <div class="lsd-col-3"><?php echo LSD_Form::label([
                'class' => 'lsd-fields-label',
                'title' => esc_html__('Map Provider', 'listdom'),
                'for' => 'lsd_display_options_skin_singlemap_map_provider',
            ]); ?></div>
            <div class="lsd-col-7">
                <?php echo LSD_Form::providers([
                    'id' => 'lsd_display_options_skin_singlemap_map_provider',
                    'name' => 'lsd[display][singlemap][map_provider]',
                    'value' => $singlemap['map_provider'] ?? LSD_Map_Provider::def(),
                    'class' => 'lsd-map-provider-toggle lsd-admin-input',
                    'attributes' => [
                        'data-parent' => '#lsd_skin_display_options_map_singlemap'
                    ]
                ]); ?>
            </div>
        </div>
        <div class="lsd-form-row lsd-map-provider-dependency lsd-map-provider-dependency-googlemap">
            <div class="lsd-col-3"><?php echo LSD_Form::label([
                'class' => 'lsd-fields-label',
                'title' => esc_html__('Map Style', 'listdom'),
                'for' => 'lsd_display_options_skin_singlemap_mapstyle',
            ]); ?></div>
            <div class="lsd-col-7">
                <?php echo LSD_Form::mapstyle([
                    'class' => 'lsd-admin-input',
                    'id' => 'lsd_display_options_skin_singlemap_mapstyle',
                    'name' => 'lsd[display][singlemap][mapstyle]',
                    'value' => $singlemap['mapstyle'] ?? ''
                ]); ?>
            </div>
        </div>

        <?php if ($this->isPro()): ?>
            <div class="lsd-map-provider-dependency lsd-map-provider-dependency-googlemap">
                <div class="<?php echo $mapsearch ? '' : 'lsd-util-hide'; ?>" id="lsd_skin_singlemap_connected_shortcodes">
                    <div class="lsd-form-row">
                        <div class="lsd-col-3"><?php echo LSD_Form::label([
                            'class' => 'lsd-fields-label',
                            'title' => esc_html__('Connected Shortcodes', 'listdom'),
                            'for' => 'lsd_display_options_skin_singlemap_connected_shortcodes_input',
                        ]); ?></div>
                        <div class="lsd-col-7">
                            <?php echo LSD_Form::autosuggest([
                                'source' => LSD_Base::PTYPE_SHORTCODE.'-searchable',
                                'name' => 'lsd[display][singlemap][connected_shortcodes]',
                                'id' => 'lsd_display_options_skin_singlemap_connected_shortcodes',
                                'input_id' => 'lsd_display_options_skin_singlemap_connected_shortcodes_input',
                                'suggestions' => 'lsd_display_options_skin_singlemap_connected_shortcodes_suggestions',
                                'values' => $singlemap['connected_shortcodes'] ?? [],
                                'max_items' => 3,
                                'placeholder' => esc_attr__("Enter at least 3 characters of the shortcode's title ...", 'listdom'),
                                'description' => esc_html__('You should select up to 3 search-able skin shortcodes e.g. List, Grid, Masonry, List + Grid, Half Map, Side By Side, etc.', 'listdom'),
                            ]); ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        <?php
        // Action for KML Layers
        do_action('lsd_shortcode_kml_layers_map_options', 'singlemap', $options);
        ?>
    </div>

    <div class="lsd-settings-fields-wrapper">
        <h3 class="lsd-my-0 lsd-admin-title"><?php echo esc_html__("Markers", 'listdom'); ?></h3>

        <div class="lsd-form-row">
            <div class="lsd-col-3"><?php echo LSD_Form::label([
                'class' => 'lsd-fields-label',
                'title' => esc_html__('Marker/Shape On Click', 'listdom'),
                'for' => 'lsd_display_options_skin_singlemap_mapobject_onclick',
            ]); ?></div>
            <div class="lsd-col-7">
                <?php echo LSD_Form::select([
                    'class' => 'lsd-admin-input',
                    'id' => 'lsd_display_options_skin_singlemap_mapobject_onclick',
                    'name' => 'lsd[display][singlemap][mapobject_onclick]',
                    'options' => [
                        'infowindow' => esc_html__('Open Infowindow', 'listdom'),
                        'redirect' => esc_html__('Redirect to Single Listing Page', 'listdom'),
                        'lightbox' => esc_html__('Open Single Listing in a Lightbox', 'listdom'),
                        'none' => esc_html__('None', 'listdom')
                    ],
                    'value' => $singlemap['mapobject_onclick'] ?? 'infowindow'
                ]); ?>
                <p class="lsd-admin-description-tiny lsd-mb-0 lsd-mt-2"><?php esc_html_e("You can choose to display an info window when someone clicks on a marker or shape on the map, open the single listing page directly, or show the details in a lightbox without reloading the page.", 'listdom'); ?></p>
            </div>
        </div>


        <?php
        // Action for Third Party Plugins
        do_action('lsd_shortcode_map_options', 'singlemap', $options);
        ?>
    </div>

    <?php
        // Action for Third Party Plugins
        do_action('lsd_shortcode_singlemap_options', $singlemap);
    ?>

    <div class="lsd-settings-fields-wrapper">
        <h3 class="lsd-my-0 lsd-admin-title"><?php echo esc_html__("Clustering", 'listdom'); ?></h3>

        <div class="lsd-form-row lsd-flex-align-items-center">
            <div class="lsd-col-3"><?php echo LSD_Form::label([
                'class' => 'lsd-fields-label',
                'title' => esc_html__('Enable', 'listdom'),
                'for' => 'lsd_display_options_skin_singlemap_clustering',
            ]); ?></div>
            <div class="lsd-col-7">
                <?php echo LSD_Form::switcher([
                    'id' => 'lsd_display_options_skin_singlemap_clustering',
                    'toggle' => '#lsd_display_options_skin_singlemap_clustering_options',
                    'name' => 'lsd[display][singlemap][clustering]',
                    'value' => $singlemap['clustering'] ?? '1'
                ]); ?>
            </div>
        </div>
        <div class="lsd-map-provider-dependency lsd-map-provider-dependency-googlemap">
            <div id="lsd_display_options_skin_singlemap_clustering_options" <?php echo ((!isset($singlemap['clustering']) || $singlemap['clustering']) ? '' : 'style="display: none;"'); ?>>
                <div class="lsd-form-row">
                    <div class="lsd-col-3"><?php echo LSD_Form::label([
                        'class' => 'lsd-fields-label',
                        'title' => esc_html__('Bubbles', 'listdom'),
                        'for' => 'lsd_display_options_skin_singlemap_clustering_images',
                    ]); ?></div>
                    <div class="lsd-col-7">
                        <?php echo LSD_Form::select([
                            'class' => 'lsd-admin-input',
                            'id' => 'lsd_display_options_skin_singlemap_clustering_images',
                            'name' => 'lsd[display][singlemap][clustering_images]',
                            'options' => LSD_Base::get_clustering_icons(),
                            'value' => $singlemap['clustering_images'] ?? 'img/cluster1/m'
                        ]); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="lsd-settings-fields-wrapper">
        <h3 class="lsd-my-0 lsd-admin-title"><?php echo esc_html__("Search & Location", 'listdom'); ?></h3>
        <div class="lsd-form-row lsd-flex-align-items-center lsd-map-provider-dependency lsd-map-provider-dependency-googlemap lsd-map-provider-dependency-leaflet">
            <div class="lsd-col-3"><?php echo LSD_Form::label([
                'class' => 'lsd-fields-label',
                'title' => esc_html__('Map Search', 'listdom'),
                'for' => 'lsd_display_options_skin_singlemap_mapsearch',
            ]); ?></div>
            <div class="lsd-col-7">
                <?php if ($this->isPro()): ?>
                    <?php echo LSD_Form::switcher([
                        'id' => 'lsd_display_options_skin_singlemap_mapsearch',
                        'name' => 'lsd[display][singlemap][mapsearch]',
                        'toggle' => '#lsd_skin_singlemap_connected_shortcodes',
                        'value' => $mapsearch,
                    ]); ?>
                <?php else: ?>
                    <p class="lsd-alert lsd-warning lsd-mt-0"><?php echo LSD_Base::missFeatureMessage(esc_html__('Map Search', 'listdom')); ?></p>
                <?php endif; ?>
            </div>
            <p class="lsd-admin-description-tiny lsd-mb-0 lsd-mt-2"><?php esc_html_e("Provide ability to filter listings based on current map position.", 'listdom'); ?></p>
        </div>

        <?php
        // Action for Third Party Plugins
        do_action('lsd_shortcode_auto_gps_map_options', 'singlemap', $options);
        ?>
    </div>
</div>
